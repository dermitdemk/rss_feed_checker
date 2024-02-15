<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Verbindung zur Datenbank herstellen
$localTable = $_ENV['rss_checker_tabelle_local'];
$localUser = $_ENV['rss_checker_user_local'];
$localPassword = $_ENV['rss_checker_pw_local'];

$hosteEuropTable = $_ENV['rss_checker_tabelle_hoste_europ'];
$hosteEuropUser = $_ENV['rss_checker_user_hoste_europ'];
$hosteEuropPassword = $_ENV['rss_checker_pw_hoste_europ'];

// Hier kannst du die Verbindungsparameter verwenden
// Beispiel: $conn = new mysqli($host, $user, $password, $database);

// Beispiel: Ausgabe der Verbindungsparameter

echo "Local Password: $localPassword ist\n";
