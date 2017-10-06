<?php
ob_start();
require 'includes/config.php';

// Hvis bruger_id eller adgangsniveau ikke er defineret, smides man væk
// If user id or access level is not defined, or access level is less than 10, throw the user away
if(!isset($_SESSION['bruger']['bruger_id']) || !isset($_SESSION['bruger']['rolle_adgangsniveau']) || $_SESSION['bruger']['rolle_adgangsniveau'] < 10){
    header('Location: login.php');
    exit;
}


?>
<!doctype html>
<html lang="en">
<?php
if(isset($_GET['side']))  {// Tjekker om variablen 'side' står i adressebaren
    $side = $_GET['side']; // Variablen 'side' indeholder værdien fra 'side' i adressebaren
}
else {
    $side = 'forside'; // Hvis der ikke er en variablen kaldt 'side' i adressebaren, er variablen 'side' lig med 'Forside'
}

$side_sti = 'sider/' . strtolower($side) . '.php'; // Variablen 'side_sti' indeholder den fulde sti til hvor siderne/filerne ligger - strtolower() sørger for, at alle bogstaverne er små

// Hvis den aktuelle side findes i arrayet $vis_sider, udskrives sidens title, ellers udskrives HTTP 404 fejl
$vis_title = isset($vis_sider[$side]) ? $vis_sider[$side]['title'] . ' - Bil, Båd & Bike Admin' : 'HTTP 404';

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Skeleton styling-->
    <!-- CKEditor -->
    <script src="../assets/ckeditor-4.5.1/ckeditor.js"></script>
    <script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script> <!-- Sikrer at tomme spans ikke fjernes i editor, da de bruges til font awesome ikoner -->
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/skeleton.css">
    <!--Egen basic styling-->
    <link rel="stylesheet" href="../css/admin_basic.css">
    <title><?php echo $vis_title; ?></title>
    <link rel="icon" href="../img/fav.jpg">
    <link href="../assets/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="../assets/google-code-prettify/prettify.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

</head>
<body onload="prettyPrint()">
<header>
    <div class="container">
        <div class="row">
            <a href="./" title="Til forsiden" class="logo five columns"><img src="../img/logo-light.png" alt="Firmas admin logo"></a>
            <div class="log_info u-pull-right four columns">
                <p>Logget ind som <span><?php echo $_SESSION['bruger']['bruger_navn'] ?></span></p>
                <a href="../"><p>Gå til hjemmesiden</p><i class="material-icons">laptop_chromebook</i></a>
                <a href="?logud"><p>Log ud</p><i class="material-icons">power_settings_new</i></a>
            </div><!--log_info slut-->
        </div><!--row slut-->
    </div><!--container slut-->
</header>
<script type="text/javascript">
    $(document).ready(function(){

        var width = $(window).width();

        //console.log(width, 'width');

        if (width > 1024){
            $('#nav_scroll').css("overflow-x", "hidden").hover(
                function(){
                    $('#nav_scroll').css("overflow-x", "auto");
                },
                function () {
                    $('#nav_scroll').css("overflow-x", "hidden");
                }
            );
        }


    });
</script>
<nav>
    <div class="container" id="nav_scroll">
        <?php
        // Viser 'fejl' ved $vis_sider, fordi den er defineret i config, som bliver includet
        foreach ($vis_sider as $sider => $detaljer){
            if($detaljer['nav'] == true && $detaljer['lvl'] <= $_SESSION['bruger']['rolle_adgangsniveau']){

                $active = $side == $sider ? 'class="active"' : '';

                // Udskriver alle de sider i menuen, der er i $vis_sider er sat til at blive vist i menuen
                ?>
                <a <?php echo $active ?> href="<?php echo $sider == 'forside' ? './' : 'index.php?side='.$sider; ?>"><?php echo $detaljer['title']; ?></a>
                <?php
            }
        }
        ?>
        <!--<div id="nav_arrow"></div>-->
    </div><!--container slut-->
</nav><!--ekstra_nav slut-->

<div class="main">
    <div class="container">
        <?php
        // Vil typisk blive sat ind i .main
        if(file_exists($side_sti)){ // Tjekker om filen eksisterer
            include $side_sti; // Hvis filen eksisterer, bliver den includedet på siden
        }
        else {
            echo 'Fejl: Siden findes ikke'; // Hvis den ikke eksisterer, så får man denne fejlbesked i stedet
            //header('Location: index.php?side=error&status=404');
            //exit;
        }

        // Viser developer informationer, hvis den er defineret som true (Cookies, sessions, arrays, get osv.)
        if (DEVELOPER_STATUS) { show_developer_info(); }
        ?>
    </div><!--container slut-->

</div><!--main slut-->

<footer>
    <div class="container">
        <p>Renee Admin - 2016</p>
    </div><!--container slut-->
</footer>

<script
    src="https://code.jquery.com/jquery-3.1.1.min.js"
    integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
    crossorigin="anonymous">
</script>
<script type="text/javascript" src="../assets/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script><!-- jQuery UI Sortable -->
<script type="text/javascript" src="js/sortable.js"></script>
</body>
</html>
<?php
ob_end_flush();