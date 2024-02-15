<?php

// ist ein script welchtes automatisch für die gegebenen urls den rss feed checkt und ein dashbord dafür ausgibt
// in dem daschbord wird es beim ersten fehler orange und ab 4 fehlern rot.
// es entstehen viele fasche fehler desahlb reicht es wenn man erst ab den 3. oder 4. fehler aktiv wird.

// die bibliothek welche mir das html parsed
include('simple_html_dom.php');

require_once './vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// verbindet sich mit der Datenbank
function conect_to_server()
{
    // login daten für die datenbank
    if (str_contains(getMyDomain(), 'localhost')) {
        $servername = "localhost";
        $username = $_ENV["rss_checker_user_local"];
        $password = $_ENV["rss_checker_pw_local"];
        $tabellen_name = $_ENV["rss_checker_tabelle_local"];
    } else {
        $servername = "localhost";
        $username = $_ENV["rss_checker_user_hoste_europ"];
        $password = $_ENV["rss_checker_pw_hoste_europ"];
        $tabellen_name = $_ENV["rss_checker_tabelle_hoste_europ"];
    }

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $tabellen_name);
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected successfully";
    return $conn;
}


// startet die html tabelle 
echo '<head><title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    </head>';
echo '<body>';
echo '<h2>RSS feed checker </h2>';
echo "<table class='table'>";
echo "<tr><th>Link</th><th>anzahl fehler</th><th>Ergebniss</th></tr>";

// Funktion zum testen der Urls, erwartet eine liste 
function test_urls($test_urls)
{
    //verbindet sich mit der datenbank
    $conn = conect_to_server();
    $rss_error = false;
    $error_array  = [];
    // für jede url die gegeben
    foreach ($test_urls as $test_url) {

        // bereitet test url vor.
        $checker_url = 'https://validator.w3.org/feed/check.cgi?url=';
        $url = $checker_url . $test_url . 'feed';
        check_if_url_exist_in_db($test_url, $conn);

        //ruft die testwebseite auf und speichert das ergebniss
        $html = file_get_html($url);

        // sucht sich das ergebniss raus
        foreach ($html->find('h2') as $elements) {
            // gibt die url in der tabelle aus
            echo "<tr><th>{$test_url}</th>";
            //wenn test nicht bestanden
            if ($elements->plaintext != 'Congratulations!') {
                // sucht sich den current counter raus und speichert den fehler in der datenbank
                $current_counter = write_to_database($test_url, false, $conn);
                // gibt die anzahl der fehler an
                echo "<th>{$current_counter}</th>";

                //wenn es 4 fehrle hintereinander gab
                if ($current_counter > 3) {
                    echo '<th style="background-color: red;">error</th>';
                    // speichert das es einen fehler gab
                    $rss_error = true;
                    array_push($error_array, $test_url);
                }
                // wenn es ein fehler gab aber keine mehrere hintereinander, nur waring 
                else {
                    echo '<th style="background-color: orange;">error</th>';
                }
            }
            //wenn test bestanden
            else {
                // holt sich den die anzahl der fehlr, sollte hier immer 0 sein, und speichert das es keinen fehler gab
                $current_counter = write_to_database($test_url, true, $conn);
                // gibt aus das es keinen fehler gab
                echo "<th>{$current_counter}</th>";
                echo '<th style="background-color: green;">pass</th>';
            }

            break;
        }
    }
}

// liste an urls welche getestet werden sollen
$urls = array(
    'https://www.derwesten.de/',
    'https://www.derwesten.de/staedte/essen/',
    'https://www.derwesten.de/autoren/charmaine-fischer/',
    'https://www.derwesten.de/staedte/',
    'https://www.moin.de/',
    'https://www.moin.de/hamburg/',
    'https://www.moin.de/autoren/stephan-wipperfeld',
    'https://www.moin.de/themen/karl-may-spiele',
    'https://www.news38.de/',
    'https://www.news38.de/autoren/redaktion-news38/',
    'https://www.news38.de/salzgitter/',
    'https://www.news38.de/themen/blaulicht/',
    'https://www.thueringen24.de/',
    'https://www.thueringen24.de/erfurt/',
    'https://www.thueringen24.de/autoren/vanessa-schubert/',
    'https://www.wmn.de/',
    'https://www.wmn.de/insights',
    'https://www.wmn.de/author/mona-schaeffer',
    'https://www.wmn.de/tag/hot'
);

function get_geranel_info_outof_url($url)
{
    $url_getrent = (explode(".", $url));
    if (count($url_getrent) == 3) {
        $seite = $url_getrent[1];
        $getrennt_nach_umgebung = explode("//", $url_getrent[0]);
        $umgebung = ($getrennt_nach_umgebung)[1];
        $umgebung = ($getrennt_nach_umgebung)[1];
        if ($umgebung == 'www') {
            $umgebung = 'production';
        } elseif ($umgebung == 'stage') {
            $umgebung = 'preprod';
        } elseif ($umgebung == 'dev') {
            $umgebung = 'develop';
        }
        if ($seite == 'derwesten' or $seite == 'moin' or $seite == 'news38' or $seite == 'thueringen24' or $seite == 'berlin-live') {
            $cluster = 'rwp';
        } elseif ($seite == 'wmn' or $seite == 'futurezone' or $seite == 'imtest') {
            $cluster = 'vrt';
        } elseif ($seite == 'heftig' or $seite == 'einfachschoen' or $seite == 'genialetricks') {
            $cluster = 'mp';
        } elseif ($seite == 'gofeminin') {
            $cluster = 'dbn';
        } else {
            $seite = 'seite nicht bekannt';
        }
    } else {
        $seite = 'error';
        $umgebung = 'error';
        $cluster = 'error';
    }
    return array($seite, $cluster, $umgebung);
}

// testet ob die url schon in der Datenbank vor kommt, wenn nicht legt es neuen eintrag an
function check_if_url_exist_in_db($url, $conn)
{
    $tabellen_name = 'rss_feed_checker';
    //sucht danach ob es unter dieser url schon einen eintrag gibt
    $sql = "SELECT COUNT(*) AS url_count FROM $tabellen_name WHERE url = '$url'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $urlCount = $row['url_count'];
    list($seite, $cluster, $umgebung) = get_geranel_info_outof_url($url);

    if ($urlCount  < 1) {
        //wenn es noch keinen eintrag gibt wird einer für diese url angelegt
        $insertSql = "INSERT INTO $tabellen_name (url, counter,site,cluster, 	umgebung) VALUES ('$url','0','$seite','$cluster','$umgebung')";
        $insertResult = mysqli_query($conn, $insertSql);

        if (!$insertResult) {
            die("Einfügefehler: " . mysqli_error($conn));
        }
    }
}


// speichert die fehler und resetet den conunter
function write_to_database($url, $counter, $conn)
{
    $error = false;
    $currentCounter = 0;
    $tabellen_name = 'rss_feed_checker';
    // wenn es keinen fehrler gab wird der counter resettet
    if ($counter == true) {
        $updateSql = "UPDATE $tabellen_name SET counter = 0 WHERE url = '$url'";
        $updateResult = mysqli_query($conn, $updateSql);
        return 0;
    }
    // wenn es einen fehler gab wir der counter um 1 erhöt, wenn diese größer 2 ist wird error zurückgegeben
    if ($counter == false) {
        $sql = "SELECT counter FROM $tabellen_name WHERE url = '$url'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $currentCounter = $row['counter'];
        if ($currentCounter > 2) {
            $error = true;
        } else {
            $error = false;
        }
        $currentCounter = $currentCounter + 1;

        $updateSql = "UPDATE $tabellen_name SET counter = $currentCounter WHERE url = '$url'";
        $updateResult = mysqli_query($conn, $updateSql);
        return ($currentCounter);
    }
}
// fürt funktion mit allen urls aus
test_urls($urls);
