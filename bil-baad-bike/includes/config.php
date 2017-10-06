<?php
// Skal indsættes på alle sider
// if(!isset($alle_sider)){
//      require '../includes/config.php';
//      $side = $_GET['side'];
// }

session_start();
define('DEVELOPER_STATUS', false);

// Hvis DEVELOPER_STATUS er true
if(DEVELOPER_STATUS){
    // Error_reporting sættes til E_ALL (default for XAMPP), der viser alle fejl
    error_reporting(E_ALL);
}
else{
    // Ellers sættes den til 0 (default på de fleste servere)
    error_reporting(0);
}

require 'functions.php';

// Oplysninger til databasen
$db_host = "mysql71.unoeuro.com";
$db_user = "michellerenee_dk";
$db_pass = "***REMOVED***";
$db_name = "michellerenee_dk_db";
// Forbinder til databasen
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Hvis der er fejl ved forbindelsen til databasen
if($mysqli->connect_error){
    // Der kaldes på funktionen connect_error, der skal bruge nogle informationer, til at udskrive hvor der er fejl
    connect_error($mysqli->connect_errno, $mysqli->connect_error, __LINE__, __FILE__);
}
// Sætter charsettet til utf8, så der kan bruges æ, ø, å
$mysqli->set_charset('utf8');

// De muligheder en bruger har, for at kunne vælge hvor mange elementer man vil se på en side ad gangen
$vis_pr_side = [
    5     =>  5,
    10    =>  10,
    20    =>  20,
    30    =>  30,
    40    =>  40,
    50    =>  50,
    60    =>  60,
    70    =>  70,
    80    =>  80,
    90    =>  90,
    100   =>  100
];

//$side_title = $_GET['side'];





/**
 * Array med alle siderne på sitet
 * key = Navnet på filen til siden. Første: forside
 * title = Titlen på siden, der vises rundt omkring på sitet, fx i menuen. Første: Forside
 * nav = Om siden skal vises i menuen eller ej. True: ja, false: nej
 * lvl = Hvilken rolle adgangsniveau man skal have for at komme ind på siden. Er måske ikke nødvendig på frontend?
 */
$alle_sider = [
    'forside'   =>
    [
        'title' => 'Forside',
        'nav'   => true
    ],

    'kategori'  =>
    [
        'title' => 'Kategori',
        'nav'   => true,
    ],

    'arkivet'  =>
    [
        'title' => 'Arkivet',
        'nav'   => true,
    ],

    'kontakt'   =>
    [
        'title' => 'Kontakt',
        'nav'   => true
    ],

    'redaktionen'  =>
    [
        'title' => 'Redaktionen',
        'nav'   => true,
    ],

    'sponsor'   =>
    [
        'title' => 'Sponsor',
        'nav'   => false
    ],

    'vis-artikel' =>
    [
        'title' => 'Vis artikel',
        'nav'   => false
    ]
];






