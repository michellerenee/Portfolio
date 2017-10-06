<?php
// Hvis der ikke er valgt et artikel-id, får en besked, og ellers gemmes id'et
if(!isset($_GET['artikel']) || empty($_GET['artikel'])){
    die('Du skal vælge en artikel for at læse den');
}
else{
    $artikel_id = intval($_GET['artikel']);
}

// Hvis session/arrayet viste_nyheder ikke er defineret, så defineres det, som et tomt array
if(!isset($_SESSION['viste_artikler'])){
    $_SESSION['viste_artikler'] = [];
}

// Tæller visninger på artiklerne

// Hvis den aktuelle nyheds id ikke findes i arrayet/session, så opdateres visningen i databasen, så der lægges en til
if(!in_array($artikel_id, $_SESSION['viste_artikler'])){
    $query = "UPDATE artikler 
              SET artikel_antal_visninger = artikel_antal_visninger + 1 
              WHERE artikel_id = $artikel_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    // Og id'et gemmes i arrayet/session
    $_SESSION['viste_artikler'][] = $artikel_id;
}

// Artiklen hentes fra databasen
$query = "SELECT artikel_overskrift, DATE_FORMAT(artikel_dato, '%e. %M %Y KL. %H:%i') AS artikel_datotid, artikel_antal_visninger, artikel_tekst, bruger_navn, bruger_billede, bruger_profiltekst, rolle_navn, kategori_navn, kategori_url_navn 
          FROM artikler 
          INNER JOIN brugere ON artikler.fk_bruger_id = brugere.bruger_id
          INNER JOIN roller ON brugere.fk_rolle_id = roller.rolle_id
          INNER JOIN kategorier ON artikler.fk_kategori_id = kategorier.kategori_id
          WHERE artikel_id = $artikel_id";
$result = $mysqli->query($query);
// If result return false, user the function query_error to show debugging info
if(!$result){
    query_error($query, __LINE__, __FILE__);
}
$row = $result->fetch_object();

// Der hentes en count på, hvor mange kommentarer der hører til artiklen
$count_query = "SELECT COUNT(kommentar_id) as kommentar_antal FROM kommentarer WHERE fk_artikel_id = $artikel_id";
$count_result = $mysqli->query($count_query);
// If result return false, user the function query_error to show debugging info
if(!$count_result){
    query_error($count_query, __LINE__, __FILE__);
}
$count_row = $count_result->fetch_object();
?>
<div class="breadcrumb">
    <span><a href="./">Forside</a></span>
    <span><a href="index.php?side=kategori&type=<?php echo $row->kategori_url_navn ?>"><?php echo $row->kategori_navn ?></a></span>
    <span>Vis artikel</span>
</div><!--breadcrumb slut-->

<h1><?php echo $row->artikel_overskrift ?></h1>
<hr>
<p class="info"><span><i class="material-icons tiny">access_time</i><?php echo $row->artikel_datotid ?></span></p>
<p class="info darker"><span><i class="material-icons tiny">forum</i><?php echo $count_row->kommentar_antal ?> Kommentarer</span></p>
<p class="info"><span><i class="material-icons tiny">remove_red_eye</i><?php echo $row->artikel_antal_visninger ?> visninger</span></p>
<p class="artikel_tekst"><?php echo $row->artikel_tekst ?></p>

<div class="redaktor row">
    <img src="img/<?php echo $row->bruger_billede ?>" class="col s2">
    <p class="navn">af <?php echo $row->bruger_navn ?> <span><?php echo $row->rolle_navn ?></span></p>
    <p><?php echo $row->bruger_profiltekst ?></p>
</div><!--redaktor slut-->

<h1>Kommentarer</h1>
<hr>
<?php
$query = "SELECT kommentar_navn, kommentar_tekst, date_format(kommentar_dato, '%e. %M %Y KL. %H:%i') as kommentar_datotid FROM kommentarer WHERE fk_artikel_id = $artikel_id";
$result = $mysqli->query($query);
// If result return false, user the function query_error to show debugging info
if(!$result){
    query_error($query, __LINE__, __FILE__);
}

if($result->num_rows < 1){
    echo '<p>Der er endnu ikke nogle kommentarer</p>';
}
while($row = $result->fetch_object()){
    ?>
    <div class="kommentar">
        <div class="icon">
            <i class="material-icons">mode_comment</i>
        </div><!--icon slut-->
        <div class="besked">
            <p class="navn"><?php echo $row->kommentar_navn ?></p>
            <p class="info"><span><i class="material-icons tiny">access_time</i><?php echo $row->kommentar_datotid ?></span></p>
            <p><?php echo $row->kommentar_tekst ?></p>
        </div>
        <hr class="thin">
    </div><!--kommentar slut-->
    <?php
}
?>

<h2 class="kommentar_h2">Din kommentar</h2>
<form method="post">
    <?php
    $fejl = $navn = $email = $kommentar = '';
    if(isset($_POST['gem'])){
        if(empty($_POST['navn']) || empty($_POST['email']) || empty($_POST['kommentar'])){
            $fejl = '<p class="fejlbesked">Du skal udfylde alle felterne for at sende en kommentar</p>';
        }
        else{
            $navn = $mysqli->escape_string($_POST['navn']);
            $email = $mysqli->escape_string($_POST['email']);
            $kommentar = $mysqli->escape_string($_POST['kommentar']);

            $query = "INSERT INTO kommentarer (kommentar_navn, kommentar_email, kommentar_tekst, fk_artikel_id) VALUES ('$navn', '$email', '$kommentar', $artikel_id)";
            $result = $mysqli->query($query);
            // If result return false, user the function query_error to show debugging info
            if(!$result){
                query_error($query, __LINE__, __FILE__);
            }

            create_log_event('information', 'En bruger har skrevet en kommentar til en artikel');
            header('Location: index.php?side=vis-artikel&artikel='.$artikel_id);
        }
    }
    echo $fejl;
    ?>
    <div class="row form_row">
        <div class="col s6">
            <label for="navn">Dit navn</label>
            <input type="text" name="navn" id="navn" value="<?php echo $navn ?>">
        </div>
        <div class="col s6">
            <label for="email">Din e-mailadresse</label>
            <input type="email" name="email" id="email" value="<?php echo $email ?>">
        </div>
        <div class="col s12">
            <label for="kommentar">Kommentar</label>
            <textarea name="kommentar" id="kommentar"><?php echo $kommentar ?></textarea>
        </div>
    </div><!--form_row slut-->
    <button type="submit" name="gem">Udfør</button>
</form>

