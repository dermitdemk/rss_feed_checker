<?php

function get_random_articel_out_of_news_sitemap($webseite, $anzahl_an_artikel = 5)
// sucht sich zufällige artikel aus der Sitemap und gibt sie als array zurück
{

    $zufällige_articel = array();
    // testing if news sitemap is callable 
    $newssitemap_url = $webseite . 'news-sitemap.xml';
    $ch = curl_init($newssitemap_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($responseCode == '200') {

        //gets all articles out of sitemap
        $newssitemap_webseite = file_get_contents($newssitemap_url);
        $newssitemap_xml  = simplexml_load_string($newssitemap_webseite);
        $number_of_articels =  count($newssitemap_xml->url);
        if ($anzahl_an_artikel > $number_of_articels) {
            $anzahl_an_artikel = $number_of_articels;
            echo 'es gibt nicht ausreichend Artikel in der Newssitemap';
        }
        //if no articles are found in the sitemap
        if (($number_of_articels) < 1) {
            echo 'es wurden keine Artikel in der Sitemap gefunden';
            throw new Exception("es wurden keine Artikel in der Sitemap gefunden");
        }
        //pickes the random articles  
        $random_nubers = array_rand(range(0, $number_of_articels - 1), $anzahl_an_artikel);
        foreach ($random_nubers as $index) {
            $zufällige_articel[] = (string) $newssitemap_xml->url[$index]->loc;
            $artikel = (string) $newssitemap_xml->url[$index]->loc;
        }
        //test again if articles are found
        if (count($zufällige_articel) < 1) {
            echo 'es wurden keine Artikel in der Sitemap gefunden';
            throw new Exception("es wurden keine Artikel in der Sitemap gefunden");
        }
        //returns the random articles
        return $zufällige_articel;
    }
}

function navBarForTest()
// baut das html für die nav bar, hier wird die url angepasst zu local host oder server
{
    echo '
    <ul class="nav nav-pills nav-fill">
  <li class="nav-item">
    <a class="nav-link active" href="' . getMyDomain() . '/rss_feed_checker/dashbord.php?url=&anzahl=">RSS-Feed Tester</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="' . getMyDomain() . '/vg_wort_test/dashbord.php">VG-Wort Tester</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="' . getMyDomain() . '/autorinnen_bilder_checken/dashbord.php">Autor*innen Bild Tester</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="' . getMyDomain() . '/lighthoustest.php">Lighthouse Test</a>
  </li>
</ul>';
}

function getMyDomain()
// funktion testet ob sie auf local host läuft oder auf dem server und gibt dann die richtige url zurück
{
    $domain = $_SERVER['SERVER_NAME'];
    if ($domain == 'localhost') {
        return 'http://localhost';
    } else {
        return 'https://zgtnet.de/eriks-tests';
    }
}
function didTestPass($json)
// teste einfach nur ob der test bestanden wurde
{
    if ($json['did_pass'] == true) {
        return true;
    } else {
        return false;
    }
}


function ensureTrailingSlash($url)
// sorgt dafür das die url die eingegeben wird ein / am ende hat
{
    // Überprüfen, ob das letzte Zeichen der URL ein "/" ist
    if (substr($url, -1) !== '/') {
        // Wenn nicht, ein "/" hinzufügen
        $url .= '/';
    }

    return $url;
}

function sitename_to_cluster($site)
{
    $rwp = array('derwesten', 'moin', 'news38', 'thueringen24', 'berlin-live', 'schlager');
    if (in_array($site, $rwp)) {
        return "rwp";
    }
    $vrt = array('wmn', 'futurezone', 'imtest', 'selfies');
    if (in_array($site, $vrt)) {
        return "vrt";
    }
    $mp = array('heftig', "einfachschoen", "leckerschmecker", "genialetricks");
    if (in_array($site, $mp)) {
        return "mp";
    }
    return "unknown";
}
function get_Site_and_Cluster_info_out_of_url($url)
{

    $array = parse_url($url);
    $baseUrl = $array['host'];
    $splitUrl = explode('.', $baseUrl);
    $site = $splitUrl[1];
    $env = $splitUrl[0];
    $cluster = sitename_to_cluster($site);
    return array($site, $env, $cluster);
}

function getUrlOutOfUrl($default_url = 'https://www.derwesten.de/')
{
    if (isset($_GET['url']) == false) {
        return $default_url;
    }
    if ($_GET['url'] == "") {
        return $default_url;
    }
    $inputUrl = filter_input(INPUT_GET, 'url', FILTER_VALIDATE_URL);
    if (!$inputUrl) {
        echo 'URL ist nicht valide';
        return $default_url;
    }

    return $_GET['url'];
}
