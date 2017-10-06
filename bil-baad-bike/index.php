<!doctype html>
<html lang="en">
<?php
require 'includes/config.php';

if(isset($_GET['side']))  {// Tjekker om variablen 'side' står i adressebaren
    $side = $_GET['side']; // Variablen 'side' indeholder værdien fra 'side' i adressebaren
}
else {
    $side = 'forside'; // Hvis der ikke er en variablen kaldt 'side' i adressebaren, er variablen 'side' lig med 'Forside'
}
$side_sti = 'sider/' . strtolower($side) . '.php'; // Variablen 'side_sti' indeholder den fulde sti til hvor siderne/filerne ligger - strtolower() sørger for, at alle bogstaverne er små

// Hvis den aktuelle side findes i arrayet $vis_sider, udskrives sidens title, ellers udskrives HTTP 404 fejl
//$vis_title = isset($alle_sider[$side]) ? $alle_sider[$side]['title'] . ' - Bil Båd & Bike' : 'HTTP 404';

if(isset($alle_sider[$side])){
    if(isset($_GET['type'])){
        $type = $mysqli->escape_string($_GET['type']);
        $query = "SELECT kategori_navn FROM kategorier WHERE kategori_url_navn = '$type'";
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }
        $row = $result->fetch_object();
        $vis_title = $row->kategori_navn . ' - Bil Båd & Bike';
    }
    else{
        $vis_title = $alle_sider[$side]['title'] . ' - Bil Båd & Bike';
    }
}
else{
    $vis_title = 'HTTP 404';
}


if(!isset($_SESSION['soeg'])){
    $_SESSION['soeg'] = '';
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $vis_title ?></title>
    <link rel="icon" href="img/fav.jpg">
    <link href="assets/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="assets/google-code-prettify/prettify.js"></script>
    <!--Import Google Icon Font-->
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
    <link rel="stylesheet" type="text/css" href="css/front-css.css">
</head>
<body onload="prettyPrint()">
<div class="container">
    <header>
        <a href="./">
            <img src="img/logo.png" alt="Bil Båd og Bikes logo" title="Til forsiden">
        </a>
    </header>

    <nav>
        <?php
        if(isset($_GET['type'])){
            $type = $_GET['type'];
        }
        else{
            $type = '';
        }
        ?>
        <a href="./" <?php echo $side == 'forside' ? 'class="active"' : ''; ?>><i class="material-icons">home</i><span>Forside</span></a>
        <?php
        $query = "SELECT kategori_url_navn, kategori_navn FROM kategorier WHERE kategori_status = 1 ORDER BY kategori_raekkefolge";
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }
        while($row = $result->fetch_object()){
            ?>
            <a href="index.php?side=kategori&type=<?php echo $row->kategori_url_navn ?>" <?php echo $type == $row->kategori_url_navn ? 'class="active"' : ''; ?>><?php echo $row->kategori_navn ?></a>
            <?php
        }
        ?>
        <a href="index.php?side=arkivet" <?php echo $side == 'arkivet' ? 'class="active"' : ''; ?>>Arkivet</a>
        <a href="index.php?side=kontakt" <?php echo $side == 'kontakt' ? 'class="active"' : ''; ?>>Kontakt</a>
        <a href="index.php?side=redaktionen" <?php echo $side == 'redaktionen' ? 'class="active"' : ''; ?>>Redaktionen</a>
    </nav>

    <div class="row">
        <main class="col s8">
            <div>
                <?php
                // Vil typisk blive sat ind i .main
                if(file_exists($side_sti)){ // Tjekker om filen eksisterer
                    include $side_sti; // Hvis filen eksisterer, bliver den includedet på siden
                }
                else {
                    echo 'Fejl: Siden findes ikke'; // Hvis den ikke eksisterer, så får man denne fejlbesked i stedet
                }

                // Viser developer informationer, hvis den er defineret som true (Cookies, sessions, arrays, get osv.)
                if (DEVELOPER_STATUS) { show_developer_info(); }
                ?>
            </div>
        </main>
        <aside class="col s4">
            <div>
                <form>
                    <div class="row">
                        <input type="hidden" name="side" value="arkivet">
                        <input type="search" name="soeg" value="<?php echo $_SESSION['soeg']; ?>" placeholder="Søg i arkivet..." class="col s8">
                        <button type="submit" class="col s3 offset-s1"><i class="material-icons">search</i></button>
                    </div>
                </form>

                <h3>Mest læste</h3>
                <hr>
                <ul>
                    <?php
                    $where_sql = "";

                    // Hvis man er på en side, hvor type er defineret i url params, så skal der kun vælges reklamer ud fra, hvilken kategori der er valgt
                    if(isset($_GET['type'])){
                        $kategori = $mysqli->escape_string($_GET['type']);
                        $query = "SELECT kategori_id FROM kategorier WHERE kategori_url_navn = '$kategori'";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if(!$result){
                            query_error($query, __LINE__, __FILE__);
                        }
                        $kat_row = $result->fetch_object();
                        $kategori_id = $kat_row->kategori_id;

                        $where_sql = " WHERE fk_kategori_id = $kategori_id";
                    }

                    $query = "SELECT artikel_overskrift, artikel_id 
                              FROM artikler 
                              $where_sql 
                              ORDER BY artikel_antal_visninger DESC, artikel_overskrift 
                              LIMIT 6";
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    while($row = $result->fetch_object()){
                        ?>
                        <li><a href="index.php?side=vis-artikel&artikel=<?php echo $row->artikel_id ?>"><?php echo $row->artikel_overskrift ?></a></li>
                        <?php
                    }
                    ?>
                </ul>

                <h3>Sponsor</h3>
                <hr>
                <div class="sponsorer">
                    <?php
                    $query = "SELECT sponsor_logo 
                              FROM sponsorer 
                              $where_sql 
                              ORDER BY RAND()
                              LIMIT 5";
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    while($row = $result->fetch_object()){
                        ?>
                        <img src="img/thumbs/<?php echo $row->sponsor_logo ?>">
                        <?php
                    }
                    ?>
                    <a href="index.php?side=sponsor">Din reklame her?</a>
                </div><!--sponsorer slut-->

            </div>
        </aside>
    </div><!--row slut-->
</div><!--container slut-->
<div class="container footer_container">
    <footer>
        <div class="row">
            <?php
            $query = "SELECT kontakt_firmanavn, kontakt_adresse, kontakt_postnummer, kontakt_by, kontakt_tlf, kontakt_fax, kontakt_land, kontakt_email FROM kontakt WHERE kontakt_id = 1";
            $result = $mysqli->query($query);
            // If result return false, user the function query_error to show debugging info
            if(!$result){
                query_error($query, __LINE__, __FILE__);
            }
            $row = $result->fetch_object();
            ?>
            <div class="col s4">
                <h3>Adresse</h3>
                <address class="bold"><?php echo $row->kontakt_firmanavn ?></address>
                <address><?php echo $row->kontakt_adresse ?></address>
                <address><?php echo $row->kontakt_postnummer ?> <?php echo $row->kontakt_by ?></address>
                <address><?php echo $row->kontakt_land ?></address>
            </div>
            <div class="col s4">
                <h3>Kontakt</h3>
                <p>Telefon: <?php echo $row->kontakt_tlf ?></p>
                <p>Fax: <?php echo $row->kontakt_fax ?></p>
                <p>E-mail: <?php echo $row->kontakt_email ?></p>
            </div>
            <div class="col s4">
                <h3>Nyhedsbrev</h3>

                <form method="post">
                    <?php
                    $fejl = $success = $email = '';
                    if(isset($_POST['tilmeld']) && !empty($_POST['nyhedsbrev'])){
                        $email = $mysqli->escape_string($_POST['nyhedsbrev']);

                        $query = "SELECT tilmelding_email FROM nyhedsbrev WHERE tilmelding_email = '$email'";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if(!$result){
                            query_error($query, __LINE__, __FILE__);
                        }

                        if($result->num_rows > 0){
                            $fejl = "<p class='fejlbesked'>Den indtastede email er allerede tilmeldt</p>";
                        }
                        else{
                            $query = "INSERT INTO nyhedsbrev (tilmelding_email) VALUES ('$email')";
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }

                            $success = "<p class='success'>Du er nu tilmeldt nyhedsbrevet</p>";
                        }
                    }

                    if(isset($_POST['afmeld']) && !empty($_POST['nyhedsbrev'])){
                        $email = $mysqli->escape_string($_POST['nyhedsbrev']);

                        $query = "SELECT tilmelding_email FROM nyhedsbrev WHERE tilmelding_email = '$email'";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if(!$result){
                            query_error($query, __LINE__, __FILE__);
                        }

                        if($result->num_rows < 1){
                            $fejl = "<p class='fejlbesked'>Den indtastede email er ikke tilmeldt nyhedsbrevet</p>";
                        }
                        else if($result->num_rows == 1){
                            $query = "DELETE FROM nyhedsbrev WHERE tilmelding_email = '$email'";
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }

                            $success = "<p class='success'>Du er nu afmeldt nyhedsbrevet</p>";
                        }
                    }

                    echo $fejl;
                    echo $success;
                    ?>
                    <div class="row">
                        <div class="input-field col s12">
                            <input type="email" name="nyhedsbrev" value="" placeholder="E-mailadresse">
                            <!--<label for="first_name">First Name</label>-->
                        </div>
                        <div class="input-field col s4">
                            <button type="submit" name="tilmeld">Tilmeld</button>
                            <!--<label for="last_name">Last Name</label>-->
                        </div>
                        <div class="input-field col s4">
                            <button type="submit" name="afmeld" class="space_left">Afmeld</button>
                            <!--<label for="last_name">Last Name</label>-->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </footer>
</div><!--container slut-->
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>