<div class="breadcrumb">
    <span><a href="./">Forside</a></span>
    <span>Sponsor</span>
</div><!--breadcrumb slut-->

<h1>Sponsor</h1>
<hr>

<?php
$query = "SELECT tekst_tekst FROM tekster WHERE tekst_side = 'sponsor'";
$result = $mysqli->query($query);
// If result return false, user the function query_error to show debugging info
if(!$result){
    query_error($query, __LINE__, __FILE__);
}
$row = $result->fetch_object();
?>
<p><?php echo $row->tekst_tekst ?></p>

<table>
    <tr>
        <th><span><i class="material-icons">remove_red_eye</i></span>Visninger</th>
        <th>Pris per visning</th>
    </tr>
    <?php
    // Format på visinger sørger for, at der kommer tusinde-seperering med punktum, og på pris, skiller den decimalerne med et komma
    $query = "SELECT FORMAT(info_visninger, 0, 'de_DE') as visninger, FORMAT(info_pris, 2, 'de_DE') as pris 
              FROM sponsor_info
              ORDER BY info_visninger";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    while($row = $result->fetch_object()){
        ?>
        <tr>
            <td><?php echo $row->visninger ?></td>
            <td><?php echo $row->pris ?> kr.</td>
        </tr>
        <?php
    }
    ?>
</table>


