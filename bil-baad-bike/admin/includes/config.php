<?php
session_start();

define('DEVELOPER_STATUS', false);

require 'functions.php';

if(isset($_GET['logud'])){
    logout();
}

// Hvis developer_status er true
if(DEVELOPER_STATUS){
    // Error_reporting sættes til E_ALL (default for XAMPP), der viser alle fejl
    error_reporting(E_ALL);
}
else{
    // Ellers sættes den til 0 (default på de fleste servere)
    error_reporting(0);
}

// Oplysninger til forbindelse af database
$db_host = "mysql71.unoeuro.com";
$db_user = "michellerenee_dk";
$db_pass = "***REMOVED***";
$db_name = "michellerenee_dk_db";

// Opretter forbindelse til databasen
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Hvis der er fejl ved forbindelsen til databasen
if($mysqli->connect_error){
    // Der kaldes på funktionen connect_error, der skal bruge nogle informationer, til at udskrive hvor der er fejl
   connect_error($mysqli->connect_errno, $mysqli->connect_error, __LINE__, __FILE__);
}

$mysqli->set_charset('utf8');

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

/*  Array med oplysninger om siderne
    title: Det navn der bliver vist til siden
    nav: Om siden skal vises i menuen eller ej. true = ja, false = nej
    lvl: Hvem der har adgang til siden (10 = Moderator, 100 = Admin, 1000 = Super admin)
*/
$vis_sider = [
    'forside' =>
    [
        'title' => 'Forside',
        'nav'   => true,
        'lvl'   => 10
    ],

    'brugere' =>
    [
        'title' => 'Redaktører',
        'nav'   => true,
        'lvl'   => 10
    ],

        'opret-bruger' =>
        [
            'title' => 'Opret bruger',
            'nav'   => false,
            'lvl'   => 10
        ],

        'ret-bruger' =>
        [
            'title' =>'Ret bruger',
            'nav'   => false,
            'lvl'   => 10
        ],

    'artikler' =>
    [
        'title' => 'Artikler',
        'nav'   => true,
        'lvl'   => 10
    ],
        'opret-artikel' =>
        [
            'title' => 'Opret artikel',
            'nav'   => false,
            'lvl'   => 10
        ],

        'ret-artikel' =>
        [
            'title' => 'Ret artikel',
            'nav'   => false,
            'lvl'   => 10
        ],

        'artikel-kommentarer' =>
        [
            'title' => 'Artikel kommentarer',
            'nav'   => false,
            'lvl'   => 10
        ],

        'artikel-log' =>
        [
            'title' => 'Artikel log',
            'nav'   => false,
            'lvl'   => 10
        ],

    'kategorier' =>
    [
        'title' => 'Kategorier',
        'nav'   => true,
        'lvl'   => 100
    ],

        'opret-kategori' =>
        [
            'title' => 'Opret kategori',
            'nav'   => false,
            'lvl'   => 100
        ],

        'ret-kategori' =>
        [
            'title' => 'Ret kategori',
            'nav'   => false,
            'lvl'   => 100
        ],

    'nyhedsbrev' =>
    [
        'title' => 'Nyhedsbrev',
        'nav'   => true,
        'lvl'   => 10
    ],

    'kontakt' =>
    [
        'title' => 'Kontakt',
        'nav'   => true,
        'lvl'   => 100
    ],

    'sponsor' =>
    [
        'title' => 'Sponsorer',
        'nav'   => true,
        'lvl'   => 100
    ],

        'ret-sponsor' =>
        [
            'title' => 'Ret sponsorer',
            'nav'   => false,
            'lvl'   => 100
        ],

        'opret-sponsor' =>
        [
            'title' => 'Opret sponsorer',
            'nav'   => false,
            'lvl'   => 100
        ],

    'logbog' =>
    [
        'title' => 'Logbog',
        'nav'   => true,
        'lvl'   => 10
    ]
];