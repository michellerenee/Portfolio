<h1>Seneste artikler</h1>
<hr>

<div class="artikler row">
    <?php
    $query = "SELECT artikel_overskrift, artikel_tekst, artikel_antal_visninger, DATE_FORMAT(artikel_dato, '%e. %M %Y KL. %H:%i') as artikel_datotid, artikel_id, kategori_navn 
              FROM artikler
              INNER JOIN kategorier ON artikler.fk_kategori_id = kategorier.kategori_id
              ORDER BY artikel_dato DESC
              LIMIT 6";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    $count = 0;

    while($row = $result->fetch_object()){
        $count++;

        $count_query = "SELECT COUNT(kommentar_id) as kommentar_antal FROM kommentarer WHERE fk_artikel_id = $row->artikel_id";
        $count_result = $mysqli->query($count_query);
        // If result return false, user the function query_error to show debugging info
        if(!$count_result){
            query_error($count_query, __LINE__, __FILE__);
        }
        $count_row = $count_result->fetch_object();
        if(!is_int($count / 2)){
            echo '</div><!--artikler/row slut--><div class="artikler row">';
        }
        ?>
        <div class="artikel col s6">
            <h2><?php echo forkort_tekst($row->artikel_overskrift, 28) ?></h2>
            <p class="info"><span><i class="material-icons tiny">access_time</i><?php echo $row->artikel_datotid ?></span></p>
            <p class="info"><span><i class="material-icons tiny">forum</i><?php echo $count_row->kommentar_antal ?> Kommentarer</span></p>
            <p class="info"><span><i class="material-icons tiny">remove_red_eye</i><?php echo $row->artikel_antal_visninger ?> visninger</span></p>

            <p><?php echo forkort_tekst($row->artikel_tekst, 200) ?></p>
            <p class="artikel_kategori info"><span><i class="material-icons tiny">local_offer</i><?php echo $row->kategori_navn ?></span></p>

            <span class="laes_mere"><a href="index.php?side=vis-artikel&artikel=<?php echo $row->artikel_id ?>">Læs mere</a></span>
        </div><!--artikel slut-->
        <?php
    }
    ?>
</div><!--artikler/row slut-->

