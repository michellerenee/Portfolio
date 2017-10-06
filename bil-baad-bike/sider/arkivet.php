<div class="breadcrumb">
    <span><a href="./">Forside</a></span>
    <span>Arkivet</span>
</div><!--breadcrumb slut-->

<h1>Arkivet</h1>
<hr>
<?php
$side = $_GET['side'];

// Hvis session 'pages' ikke eksisterer, bliver den oprettet uden indhold.
if(!isset($_SESSION[$side]))      $_SESSION[$side] = [];

// Hvis URL parametrene eksisterer gemmes deres value i variablerne
// Pssst - {} er ikke nødvendige når der kun er en enkelt linje kode, da den automatisk vil vælge det første stykke kode
if(isset($_GET['side-nr']))         $_SESSION[$side]['side_nr']       = $_GET['side-nr'];

$page_length = isset($_SESSION[$side]['side_laengde'])  ? $_SESSION[$side]['side_laengde'] : 5;
$page_no     = isset($_SESSION[$side]['side_nr'])       ? $_SESSION[$side]['side_nr']      : 1;

if(isset($_GET['soeg'])){
    $_SESSION['soeg'] = $_GET['soeg'];

    $soeg = $mysqli->escape_string($_SESSION['soeg']);

    $soeg_sql = " AND (artikel_overskrift LIKE '%$soeg%'
                  OR artikel_tekst LIKE '%$soeg%'
                  OR bruger_navn LIKE '%$soeg%'
                  OR kategori_navn LIKE '%$soeg%')";
}
else{
    $soeg = '';
    $soeg_sql = '';
}

?>
<div class="artikler row">
    <?php
    $query = "SELECT artikel_overskrift, artikel_tekst, DATE_FORMAT(artikel_dato, '%e. %M %Y KL. %H:%i') AS artikel_datotid, artikel_antal_visninger, kategori_navn, artikel_id 
              FROM artikler
              INNER JOIN kategorier ON artikler.fk_kategori_id = kategorier.kategori_id
              INNER JOIN brugere ON artikler.fk_bruger_id = brugere.bruger_id
              WHERE artikel_slettet = 0 $soeg_sql";
    $result = $mysqli->query($query);
    $result = $mysqli->query($query);
    // Tæller hvor mange resultater der kommer ud
    $items_total = $result->num_rows;
    // Det antal sider der skal springes over, alt efter hvilken side man er på. -1 gange med side
    // længden for at få den til at springe 0 over på første side, men springe over på de andre sider

    $offset = ($page_no - 1) * $page_length;

    // Tilføj order by og limit på db udtræk
    $query .= "
      ORDER BY artikel_dato DESC  
      LIMIT $page_length
      OFFSET $offset";

    // Sender forespørgsel igen
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    $soeg_besked = "<p class='soeg_besked'>Din søgning på <span>".$soeg."</span> returnede <span>".$items_total."</span> artikler</p>";

    if(isset($_GET['soeg']) && !empty($_GET['soeg'])){
        echo $soeg_besked;
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

            <p><?php echo forkort_tekst($row->artikel_tekst, 200) ?></p>
            <p class="artikel_kategori info"><span><i class="material-icons tiny">local_offer</i><?php echo $row->kategori_navn ?></span></p>

            <span class="laes_mere"><a href="index.php?side=vis-artikel&artikel=<?php echo $row->artikel_id ?>" class="">Læs mere</a></span>
        </div><!--artikel slut-->
        <?php
    }
    ?>
</div><!--artikler/row slut-->

<div class="paginations six columns offset-by-six">
    <?php
    pagination($side, $page_no, $items_total, $page_length);
    ?>
</div><!--pagination slut-->
