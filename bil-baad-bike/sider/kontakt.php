<div class="breadcrumb">
    <span><a href="./">Forside</a></span>
    <span>Kontakt</span>
</div><!--breadcrumb slut-->

<h1>Kontakt magasinet</h1>
<hr>
<?php
$query = "SELECT kontakt_adresse, kontakt_postnummer, kontakt_by, kontakt_land, kontakt_tlf, kontakt_fax, kontakt_email, kontakt_email_til_henvendelser FROM kontakt WHERE kontakt_id = 1";
$result = $mysqli->query($query);
// If result return false, user the function query_error to show debugging info
if(!$result){
    query_error($query, __LINE__, __FILE__);
}
$row = $result->fetch_object();
?>
<div class="row kontakt_row">
    <div class="col s6">
        <h2><span><i class="material-icons">location_on</i></span> Adresse</h2>
        <p><?php echo $row->kontakt_adresse ?></p>
        <p><?php echo $row->kontakt_postnummer ?> <?php echo $row->kontakt_by ?></p>
        <p><?php echo $row->kontakt_land ?></p>
    </div><!--col slut-->
    <div class="col s6">
        <h2>Kontaktoplysninger</h2>
        <p><span><i class="material-icons">phone</i></span>Telefon: <?php echo $row->kontakt_tlf ?></p>
        <p><span><i class="material-icons">print</i></span>Fax: <?php echo $row->kontakt_fax ?></p>
        <p><span><i class="material-icons">mail</i></span> E-mail: <?php echo $row->kontakt_email ?></p>
    </div><!--col slut-->
</div><!--row slut-->

<hr class="thin">

<h2>Kontaktformular</h2>
<form method="post" class="kontakt_form">
    <?php
    $navn = $email = $emne = $besked = '';
    if(isset($_POST['send'])){
        if(empty($_POST['navn']) || empty($_POST['email']) || empty($_POST['emne']) || empty($_POST['besked'])){
            echo '<p class="fejlbesked">Du skal udfylde alle felterne før du kan sende en besked</p>';
        }
        else{
            $navn = $mysqli->escape_string($_POST['navn']);
            $email = $mysqli->escape_string($_POST['email']);
            $emne = $mysqli->escape_string($_POST['emne']);
            $besked = $mysqli->escape_string($_POST['besked']);

            mail($row->kontakt_email_til_henvendelser, $emne, $besked, 'From: ' . $navn . ', email: ' . $email);
            echo '<p class="success">Din besked er sendt afsted!</p>';
        }
    }
    ?>
    <div class="row">
        <div class=" col s6">
            <label for="navn">Dit navn</label>
            <input type="text" name="navn" id="navn" class="validate">
        </div>

        <div class=" col s6">
            <label for="email">Din e-mailadresse</label>
            <input type="email" name="email" id="email" class="validate">
        </div>

        <div class=" col s12">
            <label for="emne">Emne</label>
            <input type="text" name="emne" id="emne" class="validate">
        </div>

        <div class=" col s12">
            <label for="besked">Din besked</label>
            <textarea name="besked" id="besked" class="validate"></textarea>
        </div>
    </div><!--row slut-->

    <button type="submit" name="send" >Send besked</button>
</form>

