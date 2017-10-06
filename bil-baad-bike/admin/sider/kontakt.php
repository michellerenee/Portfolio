<?php
if(!isset($vis_sider)){
    require '../includes/config.php';
    $side = $_GET['side'];
}
// Tjekker om man har adgang til siden. Hvis man ikke har, smides man væk
side_adgang($side);
// Hvis session 'pages' ikke eksisterer, bliver den oprettet uden indhold.
if(!isset($_SESSION[$side]))      $_SESSION[$side] = [];

// Hvis URL parametrene eksisterer gemmes deres value i variablerne
// Pssst - {} er ikke nødvendige når der kun er en enkelt linje kode, da den automatisk vil vælge det første stykke kode
if(isset($_GET['side-nr']))         $_SESSION[$side]['side_nr']       = $_GET['side-nr'];

// Hvis vis_pr_side er defineret i URL params (bruges til hvor mange elementer der skal vises pr. side)
if(isset($_GET['vis_pr_side'])){
    // Session side_laengde gemmes med den nye værdi
    $_SESSION[$side]['side_laengde'] = intval($_GET['vis_pr_side']);
    // Session side_nr nulstilles (sættes til 1 igen lidt længere nede i koden)
    unset($_SESSION[$side]['side_nr']);
}

// User value from session if defined, or use default values
$page_length = isset($_SESSION[$side]['side_laengde'])  ? $_SESSION[$side]['side_laengde']                    : 10;
$page_no     = isset($_SESSION[$side]['side_nr'])       ? $_SESSION[$side]['side_nr']                         : 1;
$search      = isset($_SESSION[$side]['search'])        ? $mysqli->escape_string($_SESSION[$side]['search'])  : '';


$fejl = '';
if(isset($_POST['gem'])){
    if(empty($_POST['firmanavn']) || empty($_POST['adresse']) || empty($_POST['postnummer']) || empty($_POST['by']) || empty($_POST['land']) || empty($_POST['telefon']) || empty($_POST['fax']) || empty($_POST['email']) || empty($_POST['email_henvendelser'])){
        $fejl .= '<p>Alle felterne skal være udfyldt</p>';
    }
    else{
        $firmanavn              = $mysqli->escape_string($_POST['firmanavn']);
        $adresse                = $mysqli->escape_string($_POST['adresse']);
        $postnummer             = intval($_POST['postnummer']);
        $by                     = $mysqli->escape_string($_POST['by']);
        $land                   = $mysqli->escape_string($_POST['land']);
        $telefon                = $mysqli->escape_string($_POST['telefon']);
        $fax                    = $mysqli->escape_string($_POST['fax']);
        $email                  = $mysqli->escape_string($_POST['email']);
        $email_henvendelser     = $mysqli->escape_string($_POST['email_henvendelser']);

        $query = "UPDATE kontakt SET kontakt_firmanavn = '$firmanavn', kontakt_adresse = '$adresse', kontakt_postnummer = $postnummer, kontakt_by = '$by', kontakt_land = '$land', kontakt_tlf = '$telefon', kontakt_fax = '$fax', kontakt_email = '$email', kontakt_email_til_henvendelser = '$email_henvendelser' WHERE kontakt_id = 1";
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }

        create_log_event('opdatering', 'Opdatering af kontaktoplysningerne');
        header('Location: index.php?side=kontakt');
    }
}

?>
<div class="kontakt">
    <div class="row">
        <div class="element bread twelve columns">
            <nav class="breadcrumb">
                <span><a href="./"><?php echo $vis_sider['forside']['title'] ?></a></span>
                <span><?php echo $vis_sider[$side]['title'] ?></span>
            </nav>
        </div><!--element slut-->
    </div><!--row slut-->
    <div class="row">
        <?php
        $query = "SELECT kontakt_firmanavn, kontakt_adresse, kontakt_postnummer, kontakt_by, kontakt_land, kontakt_tlf, kontakt_fax, kontakt_email, kontakt_email_til_henvendelser
                  FROM kontakt 
                  WHERE kontakt_id = 1";
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }
        $row = $result->fetch_object();
        ?>
        <div class="element five columns">
            <h1>Kontaktoplysninger</h1>
            <div class="row kontakt">
                <p class="bold"><?php echo $row->kontakt_firmanavn ?></p>
                <p><?php echo $row->kontakt_adresse ?></p>
                <p><?php echo $row->kontakt_postnummer ?> <?php echo $row->kontakt_by ?></p>
                <p class="space"><?php echo $row->kontakt_land ?></p>

                <p>Telefon: <?php echo $row->kontakt_tlf ?></p>
                <p>Fax: <?php echo $row->kontakt_fax ?></p>
                <p>E-mail: <?php echo $row->kontakt_email ?></p>
                <p>E-mail til henvendelser: <?php echo $row->kontakt_email_til_henvendelser ?></p>
            </div><!--row slut-->
        </div><!--element slut-->
        <div class="element kontakt_element seven columns">
            <h1>Ret kontaktoplysningerne</h1>
            <div class="row kontakt">
                <form method="post">
                    <div class="row">
                        <div class="six columns">
                            <label for="firmanavn">Firmanavn</label>
                            <input type="text" name="firmanavn" id="firmanavn" value="<?php echo $row->kontakt_firmanavn ?>">

                            <label for="adresse">Adresse</label>
                            <input type="text" name="adresse" id="adresse" value="<?php echo $row->kontakt_adresse ?>">

                            <label for="postnummer">Postnummer</label>
                            <input type="number" name="postnummer" id="postnummer" value="<?php echo $row->kontakt_postnummer ?>">

                            <label for="by">By</label>
                            <input type="text" name="by" id="by" value="<?php echo $row->kontakt_by ?>">


                            <label for="land">Land</label>
                            <input type="text" name="land" id="land" value="<?php echo $row->kontakt_land ?>">

                        </div>
                        <div class="six columns">
                            <label for="telefon">Telefon</label>
                            <input type="tel" name="telefon" id="telefon" value="<?php echo $row->kontakt_tlf ?>">

                            <label for="fax">Fax</label>
                            <input type="tel" name="fax" id="fax" value="<?php echo $row->kontakt_fax ?>">

                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" value="<?php echo $row->kontakt_email ?>">

                            <label for="email_henvendelser">Email til henvendelser</label>
                            <input type="text" name="email_henvendelser" id="email_henvendelser" value="<?php echo $row->kontakt_email_til_henvendelser ?>">
                        </div>
                    </div><!--row slut-->
                    <button type="submit" name="gem">Gem</button>
                </form>
            </div><!--row slut-->
        </div><!--element slut-->
    </div><!--row slut-->
</div><!--kontakt slut-->