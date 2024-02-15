

<?php

require_once 'vendor/autoload.php';
// Include the librarys
include 'simple_html_dom.php';





function getAllSitemaps($url)
{
    $url = ensureTrailingSlash($url);
    $indexSitemapUrl = $url . 'sitemap_index.xml';
    $html = file_get_contents($indexSitemapUrl);
    $xml = simplexml_load_string($html);
    $listOfSitemaps = array();
    foreach ($xml->sitemap as $sitemap) {
        $listOfSitemaps[] = $sitemap->loc;
    }
    return $listOfSitemaps;
}
function getAuthorCategoryPostSitemap($alleSitemaps, $sitemapInfo = 'alle')
{
    $tagSitemap = array();
    $categorySitemap = array();
    $authorSitemap = array();

    foreach ($alleSitemaps as $sitemapUrl) {
        if (strpos($sitemapUrl, 'post_tag-sitemap.xml') !== false) {
            $tagSitemap[] = $sitemapUrl;
        }
        if (strpos($sitemapUrl, 'category-sitemap.xml') !== false) {
            $categorySitemap[] = $sitemapUrl;
        }
        if (strpos($sitemapUrl, 'author-sitemap.xml') !== false) {
            $authorSitemap[] = $sitemapUrl;
        }
    }
    if ($sitemapInfo === 'tag') {
        return $tagSitemap;
    }
    if ($sitemapInfo === 'kategorie') {
        return $categorySitemap;
    }
    if ($sitemapInfo === 'autor*innen') {
        return $authorSitemap;
    }

    return array_merge($tagSitemap, $categorySitemap, $authorSitemap);
}

function getRandomSitemaps($url, $anzahl)
{

    $homeFeedUlr = $url . 'feed';
    $alleSitemaps = getAllSitemaps($url);
    $sitemapInfo = getSitemapInfoOutOfUrl();
    $relevantSitemaps = getAuthorCategoryPostSitemap($alleSitemaps, $sitemapInfo);
    $listOfUrls = array();
    foreach ($relevantSitemaps as $sitemapUrl) {
        $html = file_get_contents($sitemapUrl);
        $xml = simplexml_load_string($html);
        foreach ($xml->url as $url) {
            $listOfUrls[] = ensureTrailingSlash($url->loc);
        }
    }
    $listOfUrls = array_unique($listOfUrls);
    shuffle($listOfUrls);
    ## ad home feed url to the top of list 
    array_unshift($listOfUrls, $homeFeedUlr);
    $listOfUrls = array_slice($listOfUrls, 0, $anzahl);
    return $listOfUrls;
}
function urlEingabe()
// baut das html für die url eingabe 
{
    echo '
    <form action="" method="get">
        <label for="url">URL eingeben:</label>
        <input type="url" name="url" id="url">
        <label for="url">Anzahl an Artikel:</label>
        <input type="number" name="anzahl" id="anzahl">
         <label for="sitemap">aus Welchen Sitempas soll gezogen werden</label>
            <select name="sitemap" id="sitemap">
            <option value="alle">alle</option>
            <option value="autor*innen">autor*innen</option>
            <option value="kategorie">kategorie</option>
            <option value="tag">tag</option>
            </select> 
                    
        <button type="submit">Diese Seite Testen</button>  
        <h5>hier einfach webseite eingeben z.b. https://www.moin.de/ es werden dann automatisch Zufällige seiten aus der sitemap gesucht</h5>
        <h5>Hier der link zum feed cheker um einzelne seiten zu testen <a href="https://validator.w3.org/feed">validator.w3.org</a> </h5>
        <h5>Hier kann man einfach ein paar wichtige urls testen, das war der <a href="' . getMyDomain() . 'rss_feed_checker/rss_tester.php">alter Rssfeed tester</a> </h5>
        </form>
';
}



function buildDashbord($listOfUrls)
// bekommt die urll Liste an Artikel, ruft die API auf und baut das html für das dashbord 
{
    echo '<head><title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    </head>';
    echo '<body>';
    navBarForTest();
    echo '<h2>VG rss Feed tester </h2>';
    urlEingabe();
    echo "<table class='table'>";

    echo '<tr><th>url</th><th>Test</th></tr>';
    $baseUrl = getMyDomain() . '/rss_feed_checker/api.php?url=';

    foreach ($listOfUrls as $url) {
        $apiUrl = $baseUrl . $url;
        $html = file_get_html($apiUrl);
        $json = json_decode($html, true);
        echo '<tr>';
        echo '<td>' . $url . '</td>';

        if (didTestPass($json) === true) {
            echo '<td style="background-color: green;">' . $json['error_text'] . '</td>';
        } else {
            echo '<td style="background-color: red;"> error ' . $json['error_text'] . '</td>';
        }
    }
}



function getAnzahlArticelOutOfUrl()
{
    if (isset($_GET['anzahl']) && is_numeric($_GET['anzahl'])) {
        $anzahl = (int)$_GET['anzahl'];
        return $anzahl;
    }

    return 5;
}

function getSitemapInfoOutOfUrl()
{
    if (isset($_GET['sitemap']) && ($_GET['sitemap'] !== '')) {
        $sitmapInfo = $_GET['sitemap'];
        return $sitmapInfo;
    }
    return 'alle';
}

// hier werden die funktionen dann aufgerufen
$defaultUrl = 'https://www.wmn.de/';
$url = ensureTrailingSlash(getUrlOutOfUrl($defaultUrl));
$anzahl = getAnzahlArticelOutOfUrl();
$listOfUrls = getRandomSitemaps($url, $anzahl);
buildDashbord($listOfUrls);
