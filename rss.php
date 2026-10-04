<?php

require_once __DIR__ . '/admin/security.php';

$tableau = elodie_cms_read_encoded_configuration();
if (count($tableau) < 8) {
    http_response_code(503);
    exit('Le CMS n’est pas encore installé.');
}

header("Content-type: application/rss+xml; charset=UTF-8");
echo "<?".'xml version="1.0" encoding="UTF-8"'."?>"."\n";

echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
echo '<channel>'."\n";

$siteTitle = base64_decode($tableau[0] ?? '', true);
$siteUrl = base64_decode($tableau[5] ?? '', true);
$siteTitle = $siteTitle === false ? '' : $siteTitle;
$siteUrl = $siteUrl === false ? '' : $siteUrl;
$siteTitle = html_entity_decode($siteTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$xml = '<title>'.elodie_cms_escape_xml($siteTitle).'</title>'."\n";
$xml .= '<link>'.elodie_cms_escape_xml($siteUrl).'</link>'."\n";
$xml .= '<atom:link href="'.elodie_cms_escape_xml($siteUrl.'/rss.php').'" rel="self" type="application/rss+xml" />'."\n";
$xml .= '<description></description>'."\n"; 
$xml .= '<language>fr</language>'."\n"; 
$xml .= '<copyright></copyright>'."\n";

$liste = elodie_cms_read_news(__DIR__ . '/news.php');
$articleIds = array_keys($liste);
krsort($liste);

foreach ($liste as $file => $article) {

if (!is_array($article)) {
    continue;
}
$year = $article['annee'] ?? '';
$month = $article['mois'] ?? '';
$day = $article['jour'] ?? '';
if (!is_string($year) || !is_string($month) || !is_string($day)
    || !ctype_digit($year) || !ctype_digit($month) || !ctype_digit($day)) {
    continue;
}
$date = DateTimeImmutable::createFromFormat('!Y-m-d', sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day));
if ($date === false || $date->format('Y-m-d') !== sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day)) {
    continue;
}

$item = '<item>'."\n";
$articlePosition = array_search($file, $articleIds, true);
$articlePosition = $articlePosition === false ? 1 : $articlePosition + 1;
$title = is_string($article['titre'] ?? null) ? html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
$content = is_string($article['contenu'] ?? null) ? elodie_cms_sanitize_article_html($article['contenu']) : '';
$item .= '<title>'.elodie_cms_escape_xml($title).'</title>'."\n";
$item .= '<guid isPermaLink="false">article-'.((int) $file + 1).'</guid>'."\n";
$item .= '<link>'.elodie_cms_escape_xml(rtrim($siteUrl, '/').'/index2.php?module=articles&page='.$articlePosition).'</link>'."\n";
$item .= '<pubDate>'.$date->format(DATE_RSS).'</pubDate>'."\n";
$item .= '<description>'.elodie_cms_escape_xml($content).'</description>'."\n";
$xml .= $item.'</item>'."\n";
			
}

echo $xml;

echo '</channel>'."\n";
echo '</rss>';

?>
