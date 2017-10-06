<?php
if(isset($_GET['type'])){
    $kategori_type = $mysqli->escape_string($_GET['type']);

    $kat_query = "SELECT kategori_navn, kategori_id FROM kategorier WHERE kategori_url_navn = '$kategori_type'";
    $kat_result = $mysqli->query($kat_query);
    // If result return false, user the function query_error to show debugging info
    if(!$kat_result){
        query_error($query, __LINE__, __FILE__);
    }
    $kat_row = $kat_result->fetch_object();

    $kategori_id    = $kat_row->kategori_id;
    $kategori       = $kat_row->kategori_navn;
}
else{
    die('Siden kan ikke vises');
}
?>
<div class="breadcrumb">
    <span><a href="./">Forside</a></span>
    <span><?php echo $kategori ?></span>
</div><!--breadcrumb slut-->

<h1><?php echo $kategori ?></h1>
<hr>

<div class="artikler row">
    <?php
    $query = "SELECT artikel_overskrift, DATE_FORMAT(artikel_dato, '%e. %M %Y KL. %H:%i') as artikel_datotid, artikel_antal_visninger, artikel_tekst, kategori_navn, artikel_id 
              FROM artikler 
              INNER JOIN kategorier ON artikler.fk_kategori_id = kategorier.kategori_id
              WHERE fk_kategori_id = $kategori_id
              ORDER BY artikel_dato DESC
              LIMIT 3";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    if($result->num_rows < 1){
        echo '<p class="col s12">Der er ingen artikler i denne kategori</p>';
    }

    while($row = $result->fetch_object()){
        $count_query = "SELECT COUNT(kommentar_id) as kommentar_antal FROM kommentarer WHERE fk_artikel_id = $row->artikel_id";
        $count_result = $mysqli->query($count_query);
        // If result return false, user the function query_error to show debugging info
        if(!$count_result){
            query_error($count_query, __LINE__, __FILE__);
        }
        $count_row = $count_result->fetch_object();
        ?>
        <div class="artikel col s12">
            <h2><?php echo $row->artikel_overskrift ?></h2>
            <p class="info"><span><i class="material-icons tiny">access_time</i><?php echo $row->artikel_datotid ?></span></p>
            <p class="info"><span><i class="material-icons tiny">forum</i><?php echo $count_row->kommentar_antal ?> Kommentarer</span></p>
            <p class="info"><span><i class="material-icons tiny">remove_red_eye</i><?php echo $row->artikel_antal_visninger ?> visninger</span></p>

            <p><?php echo forkort_tekst($row->artikel_tekst, 500) ?></p>
            <p class="artikel_kategori info"><span><i class="material-icons tiny">local_offer</i><?php echo $row->kategori_navn ?></span></p>

            <span class="laes_mere"><a href="index.php?side=vis-artikel&artikel=<?php echo $row->artikel_id ?>" class="">Læs mere</a></span>
        </div><!--artikel slut-->
        <?php
    }
    ?>
</div><!--artikler/row slut-->

