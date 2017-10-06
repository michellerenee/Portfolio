<div class="breadcrumb">
    <span><a href="./">Forside</a></span>
    <span>Redaktionen</span>
</div><!--breadcrumb slut-->

<h1>Redaktionen</h1>
<hr>

<?php
$kat_query = "SELECT kategori_navn, kategori_id 
          FROM kategorier 
          WHERE kategori_status = 1 
          ORDER BY kategori_raekkefolge";
$kat_result = $mysqli->query($kat_query);
// If result return false, user the function query_error to show debugging info
if(!$kat_result){
    query_error($kat_query, __LINE__, __FILE__);
}
while($kat_row = $kat_result->fetch_object()){
    ?>
    <h3 class="redaktion_h3"><?php echo $kat_row->kategori_navn ?></h3>
    <?php
    $kategori_id = $kat_row->kategori_id;

    $query = "SELECT bruger_navn, bruger_billede, bruger_email, bruger_profiltekst 
          FROM bruger_kat
          INNER JOIN brugere ON bruger_kat.fk_bruger_id = brugere.bruger_id
          INNER JOIN kategorier ON bruger_kat.fk_kategori_id = kategorier.kategori_id
          WHERE fk_kategori_id = $kategori_id";
    $result = $mysqli->query($query);
// If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    while($row = $result->fetch_object()){
        ?>
        <div class="person">
            <div class="row">
                <img src="img/thumbs/<?php echo $row->bruger_billede ?>" class="col s2">
                <div class="person_info col s10">
                    <p class="navn"><?php echo $row->bruger_navn ?></p>
                    <p class="mail"><span><i class="material-icons tiny">mail</i></span><?php echo $row->bruger_email ?></p>
                    <p class="tekst"><?php echo $row->bruger_profiltekst ?></p>
                </div><!--person_info slut-->
            </div><!--row slut-->
        </div><!--person slut-->
        <?php
    }
}

?>
