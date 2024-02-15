
<?php
require_once 'vendor/autoload.php';
// Include the librarys
include 'simple_html_dom.php';





function writejson($didpass, $errortext, $url)
{
    list($site, $env, $cluster) = get_Site_and_Cluster_info_out_of_url($url);
    $jsonOutput = array(
        'test_name' => 'RSS-Feed Tester',
        'did_pass' => $didpass,
        'error_text' => $errortext,
        'url' => $url,
        'site' => $site,
        'environment' => $env,
        'cluster' => $cluster,
        'test_descripton' => 'der Test prüft ob der RSS-feed valide ist oder nicht.'

    );
    echo json_encode($jsonOutput);
}

function test_url($url)
{
    $didpass = false;
    $errortext = 'RSS-Feed validator nicht bestanden';
    $checker_url = 'https://validator.w3.org/feed/check.cgi?url=';
    $url = ensureTrailingSlash($url);

    $url = $checker_url . $url . 'feed';

    $html = file_get_html($url);
    if (!$html) {
        echo "no html";
        return false;
    }
    foreach ($html->find('h2') as $elements) {
        if (stripos($elements->plaintext, 'Congratulations!') !== false) {
            $didpass = true;
            $errortext = 'RSS-Feed validator bestanden';
        }
    }
    writejson($didpass, $errortext, $url);
}
$default_url = 'https://www.derwesten.de/';
$url = getUrlOutOfUrl();
if ($url) {
    test_url($url);
} else {
    echo "no url";
}
