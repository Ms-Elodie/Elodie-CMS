<?php

require_once __DIR__ . '/security.php';

function elodie_cms_http_status_code(array $headers): int
{
    $statusCode = 0;
    foreach ($headers as $header) {
        if (preg_match('/^HTTP\/\S+\s+([0-9]{3})\b/', $header, $matches) === 1) {
            $statusCode = (int) $matches[1];
        }
    }

    return $statusCode;
}

function elodie_cms_fetch_latest_release(): array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => "Accept: application/vnd.github+json\r\n"
                . 'User-Agent: ElodieCMS/' . elodie_cms_release_version() . "\r\n"
                . "X-GitHub-Api-Version: 2022-11-28\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $response = @file_get_contents(
        'https://api.github.com/repos/Ms-Elodie/Elodie-CMS/releases/latest',
        false,
        $context
    );
    $statusCode = elodie_cms_http_status_code($http_response_header ?? []);

    if ($statusCode === 404) {
        throw new RuntimeException(elodie_cms_ui('no_release'));
    }
    if (!is_string($response) || $statusCode !== 200) {
        throw new RuntimeException(elodie_cms_ui('check_failed'));
    }

    $release = json_decode($response, true);
    $tag = is_array($release) && is_string($release['tag_name'] ?? null)
        ? $release['tag_name']
        : '';
    if (!preg_match('/^v?([0-9]+(?:\.[0-9]+){1,2})$/', $tag, $matches)) {
        throw new RuntimeException(elodie_cms_ui('invalid_release'));
    }

    return [
        'tag' => $tag,
        'version' => $matches[1],
        'url' => 'https://api.github.com/repos/Ms-Elodie/Elodie-CMS/zipball/' . rawurlencode($tag),
    ];
}

function elodie_cms_update_create_directory(string $directory): void
{
    if (is_link($directory)) {
        throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
    }
    if (is_dir($directory)) {
        return;
    }
    if (file_exists($directory) || !mkdir($directory, 0700, true)) {
        throw new RuntimeException(elodie_cms_ui('update_permissions'));
    }
}

function elodie_cms_update_remove_tree(string $directory): bool
{
    if (is_link($directory) || is_file($directory)) {
        return unlink($directory);
    }
    if (!is_dir($directory)) {
        return true;
    }

    $entries = scandir($directory);
    if ($entries === false) {
        return false;
    }
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        if (!elodie_cms_update_remove_tree($directory . DIRECTORY_SEPARATOR . $entry)) {
            return false;
        }
    }

    return rmdir($directory);
}

function elodie_cms_update_install_release(string $version, string $archiveUrl): string
{
    if (!class_exists(ZipArchive::class)) {
        throw new RuntimeException(elodie_cms_ui('update_zip_required'));
    }
    if (version_compare($version, elodie_cms_release_version(), '<=')) {
        throw new RuntimeException(elodie_cms_ui('update_no_update'));
    }
    $urlParts = parse_url($archiveUrl);
    if (!is_array($urlParts)
        || ($urlParts['scheme'] ?? '') !== 'https'
        || ($urlParts['host'] ?? '') !== 'api.github.com'
        || !preg_match(
            '#^/repos/Ms-Elodie/Elodie-CMS/zipball/v?' . preg_quote($version, '#') . '$#',
            $urlParts['path'] ?? ''
        )) {
        throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
    }

    $projectRoot = realpath(dirname(__DIR__));
    $dataDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
    if ($projectRoot === false || !is_dir($dataDirectory) || !is_writable($dataDirectory)) {
        throw new RuntimeException(elodie_cms_ui('update_permissions'));
    }

    $lock = @fopen($dataDirectory . DIRECTORY_SEPARATOR . '.update.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException(elodie_cms_ui('update_permissions'));
    }
    if (!flock($lock, LOCK_EX | LOCK_NB)) {
        fclose($lock);
        throw new RuntimeException(elodie_cms_ui('update_in_progress'));
    }

    $archivePath = '';
    $stagingDirectory = '';
    $backupDirectory = '';
    $zip = new ZipArchive();
    $zipOpened = false;

    try {
        $archivePath = tempnam($dataDirectory, 'elodie-update-');
        if (!is_string($archivePath)) {
            throw new RuntimeException(elodie_cms_ui('update_permissions'));
        }

        $downloadContext = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 30,
                'ignore_errors' => true,
                'follow_location' => 1,
                'max_redirects' => 3,
                'header' => "Accept: application/vnd.github+json\r\n"
                    . 'User-Agent: ElodieCMS/' . elodie_cms_release_version() . "\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $download = @fopen($archiveUrl, 'rb', false, $downloadContext);
        if ($download === false) {
            throw new RuntimeException(elodie_cms_ui('update_download_failed'));
        }
        if (elodie_cms_http_status_code($http_response_header ?? []) !== 200) {
            fclose($download);
            throw new RuntimeException(elodie_cms_ui('update_download_failed'));
        }
        $output = @fopen($archivePath, 'wb');
        if ($output === false) {
            fclose($download);
            throw new RuntimeException(elodie_cms_ui('update_permissions'));
        }
        $downloadedBytes = stream_copy_to_stream($download, $output, 30 * 1024 * 1024 + 1);
        $outputFlushed = fflush($output);
        fclose($download);
        fclose($output);
        if (!is_int($downloadedBytes) || !$outputFlushed || $downloadedBytes > 30 * 1024 * 1024) {
            throw new RuntimeException(elodie_cms_ui('update_download_failed'));
        }

        if ($zip->open($archivePath, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
        }
        $zipOpened = true;
        if ($zip->numFiles < 1 || $zip->numFiles > 5000) {
            throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
        }
        $stagingDirectory = $dataDirectory . DIRECTORY_SEPARATOR . '.update-stage-' . bin2hex(random_bytes(12));
        elodie_cms_update_create_directory($stagingDirectory);

        $rootPrefix = null;
        $totalUncompressedBytes = 0;
        $files = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entryName = $zip->getNameIndex($index);
            if (!is_string($entryName) || $entryName === '' || str_contains($entryName, "\0")
                || str_contains($entryName, '\\') || str_starts_with($entryName, '/')) {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }

            $isDirectory = str_ends_with($entryName, '/');
            $pathParts = explode('/', rtrim($entryName, '/'));
            if ($pathParts === [] || $pathParts[0] === '') {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }
            if ($rootPrefix === null) {
                $rootPrefix = $pathParts[0];
            }
            if ($pathParts[0] !== $rootPrefix
                || in_array('', $pathParts, true)
                || in_array('.', $pathParts, true)
                || in_array('..', $pathParts, true)
                || preg_match('/[\x00-\x1F\x7F:]/', $entryName) === 1) {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }

            $system = 0;
            $attributes = 0;
            if ($zip->getExternalAttributesIndex($index, $system, $attributes)
                && ($system === ZipArchive::OPSYS_UNIX)
                && ((($attributes >> 16) & 0170000) === 0120000)) {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }
            if ($isDirectory || count($pathParts) < 2) {
                continue;
            }

            $relativeParts = array_slice($pathParts, 1);
            $relativePath = implode('/', $relativeParts);
            $topLevel = strtolower($relativeParts[0]);
            if ($topLevel === 'data'
                || $topLevel === 'images'
                || strtolower($relativePath) === 'news.php'
                || strtolower($relativePath) === 'admin/configuration.txt') {
                continue;
            }
            if (isset($files[$relativePath])) {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }

            $stat = $zip->statIndex($index);
            $size = is_array($stat) && is_int($stat['size'] ?? null) ? $stat['size'] : -1;
            $totalUncompressedBytes += $size;
            if ($size < 0 || $size > 20 * 1024 * 1024 || $totalUncompressedBytes > 150 * 1024 * 1024) {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }

            $stagedPath = $stagingDirectory . DIRECTORY_SEPARATOR
                . implode(DIRECTORY_SEPARATOR, $relativeParts);
            elodie_cms_update_create_directory(dirname($stagedPath));
            $entryStream = $zip->getStream($entryName);
            $stagedFile = @fopen($stagedPath, 'xb');
            if ($entryStream === false || $stagedFile === false) {
                if (is_resource($entryStream)) {
                    fclose($entryStream);
                }
                if (is_resource($stagedFile)) {
                    fclose($stagedFile);
                }
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }
            $copiedBytes = stream_copy_to_stream($entryStream, $stagedFile, $size + 1);
            $flushed = fflush($stagedFile);
            fclose($entryStream);
            fclose($stagedFile);
            if ($copiedBytes !== $size || !$flushed) {
                throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
            }
            $files[$relativePath] = $stagedPath;
        }

        $zip->close();
        $zipOpened = false;
        if ($files === []) {
            throw new RuntimeException(elodie_cms_ui('update_files_missing'));
        }

        $updatesDirectory = $dataDirectory . DIRECTORY_SEPARATOR . 'updates';
        elodie_cms_update_create_directory($updatesDirectory);
        $backupDirectory = $updatesDirectory . DIRECTORY_SEPARATOR
            . 'update-' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $version)
            . '-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
        elodie_cms_update_create_directory($backupDirectory);

        $changedFiles = [];
        try {
            foreach ($files as $relativePath => $stagedPath) {
                $targetPath = $projectRoot . DIRECTORY_SEPARATOR
                    . implode(DIRECTORY_SEPARATOR, explode('/', $relativePath));
                $targetDirectory = dirname($targetPath);
                $relativeDirectory = dirname($relativePath);
                if ($relativeDirectory !== '.') {
                    $currentDirectory = $projectRoot;
                    foreach (explode('/', $relativeDirectory) as $directoryPart) {
                        $currentDirectory .= DIRECTORY_SEPARATOR . $directoryPart;
                        if (is_link($currentDirectory)) {
                            throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
                        }
                        if (!is_dir($currentDirectory) && !mkdir($currentDirectory, 0755)) {
                            throw new RuntimeException(elodie_cms_ui('update_permissions'));
                        }
                    }
                }
                if (is_link($targetPath) || (file_exists($targetPath) && !is_file($targetPath))) {
                    throw new RuntimeException(elodie_cms_ui('update_archive_invalid'));
                }

                $hadOriginal = is_file($targetPath);
                $permissions = $hadOriginal ? (fileperms($targetPath) & 0777) : 0644;
                $backupPath = $backupDirectory . DIRECTORY_SEPARATOR
                    . implode(DIRECTORY_SEPARATOR, explode('/', $relativePath));
                if ($hadOriginal) {
                    elodie_cms_update_create_directory(dirname($backupPath));
                    if (!copy($targetPath, $backupPath)) {
                        throw new RuntimeException(elodie_cms_ui('update_permissions'));
                    }
                }

                $changedFiles[$relativePath] = [
                    'target' => $targetPath,
                    'backup' => $backupPath,
                    'had_original' => $hadOriginal,
                    'permissions' => $permissions,
                ];
                $replacementPath = $targetPath . '.elodie-update-' . bin2hex(random_bytes(6));
                if (!copy($stagedPath, $replacementPath)) {
                    throw new RuntimeException(elodie_cms_ui('update_permissions'));
                }
                if (!chmod($replacementPath, $permissions)) {
                    unlink($replacementPath);
                    throw new RuntimeException(elodie_cms_ui('update_permissions'));
                }
                if (is_file($targetPath) && !unlink($targetPath)) {
                    unlink($replacementPath);
                    throw new RuntimeException(elodie_cms_ui('update_permissions'));
                }
                if (!rename($replacementPath, $targetPath)) {
                    if (is_file($replacementPath)) {
                        unlink($replacementPath);
                    }
                    throw new RuntimeException(elodie_cms_ui('update_permissions'));
                }
            }
        } catch (Throwable $exception) {
            $restored = true;
            foreach (array_reverse($changedFiles, true) as $original) {
                if ($original['had_original']) {
                    if (!copy($original['backup'], $original['target'])
                        || !chmod($original['target'], $original['permissions'])) {
                        $restored = false;
                    }
                } elseif (is_file($original['target']) && !unlink($original['target'])) {
                    $restored = false;
                }
            }
            throw new RuntimeException(
                $restored
                    ? elodie_cms_ui('update_failed')
                    : sprintf(elodie_cms_ui('update_restore_failed'), 'data/updates/' . basename($backupDirectory)),
                0,
                $exception
            );
        }

        return 'data/updates/' . basename($backupDirectory);
    } finally {
        if ($zipOpened) {
            $zip->close();
        }
        $cleanupSucceeded = true;
        if ($stagingDirectory !== '' && !elodie_cms_update_remove_tree($stagingDirectory)) {
            $cleanupSucceeded = false;
        }
        if ($archivePath !== '' && is_file($archivePath) && !unlink($archivePath)) {
            $cleanupSucceeded = false;
        }
        flock($lock, LOCK_UN);
        fclose($lock);
        if (!$cleanupSucceeded) {
            throw new RuntimeException(elodie_cms_ui('update_cleanup_failed'));
        }
    }
}
