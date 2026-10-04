<?php

require_once __DIR__ . '/security.php';

function elodie_cms_markup_inline(string $text, string $format): string
{
    $tokens = [];
    $tokenPrefix = 'ELD' . bin2hex(random_bytes(8)) . 'X';
    $protect = static function (string $html) use (&$tokens, $tokenPrefix): string {
        $token = $tokenPrefix . count($tokens) . 'X';
        $tokens[$token] = $html;

        return $token;
    };

    if ($format === 'markdown') {
        $text = preg_replace_callback(
            '/!\[([^\]]*)\]\(([^)\s]+)\)/u',
            static function (array $matches) use ($protect): string {
                $url = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (!elodie_cms_valid_url_setting($url)
                    || !preg_match('~^(https?://|/|\\./)~i', $url)) {
                    return $matches[0];
                }

                return $protect(
                    '<img src="' . elodie_cms_escape($url) . '" alt="'
                    . elodie_cms_escape($matches[1]) . '">'
                );
            },
            $text
        );
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+)\)/u',
            static function (array $matches) use ($protect): string {
                $url = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (!elodie_cms_valid_url_setting($url)
                    || !preg_match('~^(https?://|mailto:|/|#|\\./)~i', $url)) {
                    return $matches[0];
                }

                return $protect(
                    '<a href="' . elodie_cms_escape($url) . '">'
                    . elodie_cms_escape($matches[1]) . '</a>'
                );
            },
            $text
        );
    } else {
        $text = preg_replace_callback(
            '/\[img\](.*?)\[\/img\]/is',
            static function (array $matches) use ($protect): string {
                $url = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if (!elodie_cms_valid_url_setting($url)
                    || !preg_match('~^(https?://|/|\\./)~i', $url)) {
                    return $matches[0];
                }

                return $protect('<img src="' . elodie_cms_escape($url) . '" alt="">');
            },
            $text
        );
        $text = preg_replace_callback(
            '/\[url=(https?:\/\/[^\]\s]+|mailto:[^\]\s]+|\/[^\]\s]+|#?[^\]\s]*)\](.*?)\[\/url\]/is',
            static function (array $matches) use ($protect): string {
                $url = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if (!elodie_cms_valid_url_setting($url)
                    || !preg_match('~^(https?://|mailto:|/|#|\\./)~i', $url)) {
                    return $matches[0];
                }

                return $protect(
                    '<a href="' . elodie_cms_escape($url) . '">'
                    . elodie_cms_escape($matches[2]) . '</a>'
                );
            },
            $text
        );
    }

    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $text = preg_replace_callback(
        '/`([^`\n]+)`/u',
        static fn(array $matches): string => $protect('<code>' . $matches[1] . '</code>'),
        $text
    );

    if ($format === 'markdown') {
        $text = preg_replace('/\*\*(.+?)\*\*/us', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/u', '<em>$1</em>', $text);
        $text = preg_replace('/~~(.+?)~~/us', '<s>$1</s>', $text);
    } else {
        $bbcodeTags = [
            'b' => 'strong',
            'i' => 'em',
            'u' => 'u',
            's' => 's',
            'code' => 'code',
        ];
        foreach ($bbcodeTags as $bbcode => $htmlTag) {
            $text = preg_replace(
                '/\[' . $bbcode . '\](.*?)\[\/' . $bbcode . '\]/is',
                '<' . $htmlTag . '>$1</' . $htmlTag . '>',
                $text
            );
        }
    }

    foreach ($tokens as $token => $html) {
        $text = str_replace($token, $html, $text);
    }

    return $text;
}

function elodie_cms_markdown_to_html(string $text): string
{
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    $html = [];
    $paragraph = [];
    $listType = '';
    $listItems = [];
    $quoteLines = [];
    $codeLines = [];
    $inCode = false;

    $flushParagraph = static function () use (&$paragraph, &$html): void {
        if ($paragraph !== []) {
            $html[] = '<p>' . implode('<br>', array_map(
                static fn(string $line): string => elodie_cms_markup_inline($line, 'markdown'),
                $paragraph
            )) . '</p>';
            $paragraph = [];
        }
    };
    $flushList = static function () use (&$listType, &$listItems, &$html): void {
        if ($listItems !== []) {
            $html[] = '<' . $listType . '><li>'
                . implode('</li><li>', array_map(
                    static fn(string $item): string => elodie_cms_markup_inline($item, 'markdown'),
                    $listItems
                ))
                . '</li></' . $listType . '>';
            $listItems = [];
            $listType = '';
        }
    };
    $flushQuote = static function () use (&$quoteLines, &$html): void {
        if ($quoteLines !== []) {
            $html[] = '<blockquote><p>' . implode('<br>', array_map(
                static fn(string $line): string => elodie_cms_markup_inline($line, 'markdown'),
                $quoteLines
            )) . '</p></blockquote>';
            $quoteLines = [];
        }
    };

    foreach ($lines as $line) {
        if (preg_match('/^\s*```/', $line) === 1) {
            $flushParagraph();
            $flushList();
            $flushQuote();
            if ($inCode) {
                $html[] = '<pre><code>' . htmlspecialchars(
                    implode("\n", $codeLines),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</code></pre>';
                $codeLines = [];
            }
            $inCode = !$inCode;
            continue;
        }
        if ($inCode) {
            $codeLines[] = $line;
            continue;
        }
        if (trim($line) === '') {
            $flushParagraph();
            $flushList();
            $flushQuote();
            continue;
        }

        if (preg_match('/^(#{1,3})\s+(.+)$/u', $line, $matches) === 1) {
            $flushParagraph();
            $flushList();
            $flushQuote();
            $level = strlen($matches[1]) + 1;
            $html[] = '<h' . $level . '>' . elodie_cms_markup_inline($matches[2], 'markdown')
                . '</h' . $level . '>';
            continue;
        }
        if (preg_match('/^\s*>\s?(.*)$/u', $line, $matches) === 1) {
            $flushParagraph();
            $flushList();
            $quoteLines[] = $matches[1];
            continue;
        }
        if (preg_match('/^\s*([-*+])\s+(.+)$/u', $line, $matches) === 1
            || preg_match('/^\s*[0-9]+\.\s+(.+)$/u', $line, $matches) === 1) {
            $flushParagraph();
            $flushQuote();
            $nextListType = preg_match('/^\s*[0-9]+\./', $line) === 1 ? 'ol' : 'ul';
            if ($listType !== '' && $listType !== $nextListType) {
                $flushList();
            }
            $listType = $nextListType;
            $listItems[] = $matches[array_key_last($matches)];
            continue;
        }

        $flushList();
        $flushQuote();
        $paragraph[] = $line;
    }

    if ($inCode) {
        $html[] = '<pre><code>' . htmlspecialchars(
            implode("\n", $codeLines),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) . '</code></pre>';
    }
    $flushParagraph();
    $flushList();
    $flushQuote();

    return implode("\n", $html);
}

function elodie_cms_bbcode_to_html(string $text): string
{
    $blocks = preg_split('/\n\s*\n/u', str_replace(["\r\n", "\r"], "\n", trim($text)));
    if (!is_array($blocks)) {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    $html = [];
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (preg_match('/^\[(h[2-4])\](.*?)\[\/\1\]$/is', $block, $matches) === 1) {
            $tag = strtolower($matches[1]);
            $html[] = '<' . $tag . '>' . elodie_cms_markup_inline($matches[2], 'bbcode') . '</' . $tag . '>';
            continue;
        }
        if (preg_match('/^\[(center|left|right|justify)\](.*?)\[\/\1\]$/is', $block, $matches) === 1) {
            $alignment = strtolower($matches[1]) === 'justify' ? 'justify' : strtolower($matches[1]);
            $html[] = '<p style="text-align: ' . $alignment . '">'
                . elodie_cms_markup_inline($matches[2], 'bbcode') . '</p>';
            continue;
        }
        if (preg_match('/^\[list\](.*?)\[\/list\]$/is', $block, $matches) === 1) {
            $items = preg_split('/\[\*\]/i', trim($matches[1]));
            if (is_array($items)) {
                $items = array_values(array_filter(array_map('trim', $items), static fn(string $item): bool => $item !== ''));
                if ($items !== []) {
                    $html[] = '<ul><li>' . implode('</li><li>', array_map(
                        static fn(string $item): string => elodie_cms_markup_inline($item, 'bbcode'),
                        $items
                    )) . '</li></ul>';
                    continue;
                }
            }
        }
        if (preg_match('/^\[quote\](.*?)\[\/quote\]$/is', $block, $matches) === 1) {
            $html[] = '<blockquote><p>' . nl2br(
                elodie_cms_markup_inline($matches[1], 'bbcode'),
                false
            ) . '</p></blockquote>';
            continue;
        }

        $html[] = '<p>' . nl2br(elodie_cms_markup_inline($block, 'bbcode'), false) . '</p>';
    }

    return implode("\n", $html);
}

function elodie_cms_render_article_content(string $content, string $format = 'visual'): string
{
    $html = match ($format) {
        'visual' => $content,
        'markdown' => elodie_cms_markdown_to_html($content),
        'bbcode' => elodie_cms_bbcode_to_html($content),
        default => throw new InvalidArgumentException('Le format de l’article est invalide.'),
    };

    return elodie_cms_sanitize_article_html($html);
}
