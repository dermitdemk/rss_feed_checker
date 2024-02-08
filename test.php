<?php
require_once 'vendor/autoload.php';

include 'simple_html_dom.php';
// liste an urls welche getestet werden sollen
function urlEingabe()
// baut das html für die url eingabe 
{
    echo '
    <form action="" method="get">
        <label for="url">URL eingeben:</label>
        <input type="url" name="url" id="url">
        <label for="url">Anzahl an Artikel:</label>
        <input type="number" name="anzahl" id="anzahl">
        
        <button type="submit">Diese Seite Testen</button>  
        <h5>hier einfach webseite eingeben z.b. https://www.moin.de/ es werden dann automatisch Zufällige seiten aus der sitemap gesucht</h5>
        <h5>Hier der link zum feed cheker um einzelne seiten zu testen <a href="https://validator.w3.org/feed">validator.w3.org</a> </h5>
    </form>
';
}
urlEingabe();

function getAnzahlArticelOutOfUrl()
{
    if (isset($_GET['anzahl']) && is_numeric($_GET['anzahl'])) {
        $anzahl = (int)$_GET['anzahl'];
        return $anzahl;
    }

    return 5;
}
echo getAnzahlArticelOutOfUrl();
