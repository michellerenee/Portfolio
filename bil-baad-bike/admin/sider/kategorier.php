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

// Hvis vis_pr_side er defineret i URL params
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

if(isset($_GET['slet']) && isset($_GET['kategori']) && !empty($_GET['kategori'])){
    $kategori_id = intval($_GET['kategori']);

    $kategori_query = "SELECT kategori_navn, kategori_raekkefolge
                     FROM kategorier 
                     WHERE kategori_id = $kategori_id";
    $kategori_result = $mysqli->query($kategori_query);
    // If result return false, user the function query_error to show debugging info
    if(!$kategori_result){
        query_error($kategori_query, __LINE__, __FILE__);
    }
    $kategori_row = $kategori_result->fetch_object();

    // Alle de tilhørende artikler til kategorien bliver slettet
    $query = "DELETE FROM artikler WHERE fk_kategori_id = $kategori_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    // Sorg for at, rækkefølgen stadig giver mening, hvis der bliver slettet en kategori midt i rækken
    $query = "UPDATE kategorier SET kategori_raekkefolge = kategori_raekkefolge -1 WHERE kategori_raekkefolge > $kategori_row->kategori_raekkefolge";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    // Kategorien bliver slettet
    $query  ="DELETE FROM kategorier WHERE kategori_id = $kategori_id";
    //echo $query;
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    // Det gemmes i loggen, og man sendes til oversigten igen
    create_log_event('sletning', 'Kategorien '.$kategori_row->kategori_navn.' er blevet slettet');
    header('Location: index.php?side=kategorier');
}

if(isset($_GET['status']) && isset($_GET['kategori']) && !empty($_GET['kategori'])){
    $status = intval($_GET['status']);
    $kategori_id = intval($_GET['kategori']);

    //echo $status;
    $query = "UPDATE kategorier SET kategori_status = $status WHERE kategori_id = $kategori_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    $kategori_query = "SELECT kategori_navn FROM kategorier WHERE kategori_id = $kategori_id";
    $kategori_result = $mysqli->query($kategori_query);
    // If result return false, user the function query_error to show debugging info
    if(!$kategori_result){
        query_error($kategori_query, __LINE__, __FILE__);
    }
    $kategori_row = $kategori_result->fetch_object();
    $status = ($_GET['status'] == 0) ? 'Deaktivering' : 'Aktivering';

    create_log_event('opdatering', $status.' af kategorien '.$kategori_row->kategori_navn);
    header('Location: index.php?side=kategorier');
}
?>
<div class="row">
    <div class="element bread twelve columns">
        <nav class="breadcrumb">
            <span><a href="./"><?php echo $vis_sider['forside']['title'] ?></a></span>
            <span><?php echo $vis_sider[$side]['title'] ?></span>
        </nav>
    </div><!--element slut-->
</div><!--row slut-->

<div class="row">
    <div class="element twelve columns">
        <h1>Alle kategorier</h1>
        <div class="row">
            <?php
            // Hvis der er trykket på søg, og der soeg_submit er defineret i URL params
            if(isset($_GET['soeg_submit'])){
                // Det der er søgt på, gemmes i session for den aktuelle side
                $_SESSION[$side]['soeg'] = $_GET['soeg_felt'];
            }

            // Hvis ikke session er defineret, gemmes den tom
            if(!isset($_SESSION[$side]['soeg'])){
                $_SESSION[$side]['soeg'] = '';
                // Indholdet fra session gemmes og escapes for at sikre imod SQL-injections
                $search_sess_sql = $mysqli->escape_string($_SESSION[$side]['soeg']);
                // Query-where sætningen defines som tom, så der ikke bliver søgt efter noget
                $search_sql = "";
            }
            else{
                // Ellers gemmes indholdet fra session og sikres imod SQL-injections
                $search_sess_sql = $mysqli->escape_string($_SESSION[$side]['soeg']);
                // Der laves en tilføjelse til query, med hvad der søges efter
                $search_sql = " AND (kategori_navn LIKE '%$search_sess_sql%'
                                OR kategori_url_navn LIKE '%$search_sess_sql%')";
            }
            ?>
            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=opret-kategori" class="opret"><p>Opret kategori</p><i class="material-icons">add</i></a>
                <!--<a href="#" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>-->
            </div><!--opret_tilbage slut-->

            <?php
            vis_pr_side($side, $page_length);
            ?>

            <form class="oversigt_soeg four columns">
                <input hidden name="side" value="kategorier">
                <div class="row">
                    <input class="ten columns" type="search" name="soeg_felt" value="<?php echo $search_sess_sql ?>" placeholder="Søg efter..">
                    <input class="two columns" type="submit" name="soeg_submit" value="Søg">
                </div><!--row slut-->
            </form>

            <table class="twelve columns">
                <tr>
                    <th>Rækkefølge</th>
                    <th>Navn</th>
                    <th>Url navn</th>
                    <th class="hide-on-small">Status</th>
                    <th class="ret_slet">Ret</th>
                    <th class="ret_slet">Slet</th>
                </tr>
                <tbody id="sortable" data-type="kategorier">
                    <?php
                    $query = "SELECT kategori_navn, kategori_url_navn, kategori_raekkefolge, kategori_status, kategori_id 
                              FROM kategorier 
                              WHERE 1=1 $search_sql";
                    // Sender forespørgsel til db
                    $result = $mysqli->query($query);

                    // Tæller hvor mange resultater der kommer ud
                    $items_total = $result->num_rows;

                    // Det antal sider der skal springes over, alt efter hvilken side man er på. -1 gange med side
                    // længden for at få den til at springe 0 over på første side, men springe over på de andre sider

                    $offset = ($page_no - 1) * $page_length;

                    // Tilføj order by og limit på db udtræk
                    $query .= "
                              ORDER BY kategori_raekkefolge 
                              LIMIT $page_length
                              OFFSET $offset";

                    // Sender forespørgsel igen
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    while($row = $result->fetch_object()){
                        ?>
                        <tr class="sortable-item" id="<?php echo $row->kategori_id ?>">
                            <td class="sortable-handle"><?php echo $row->kategori_raekkefolge ?></td>
                            <td><?php echo $row->kategori_navn ?></td>
                            <td><?php echo $row->kategori_url_navn ?></td>

                            <!-- Status -->
                            <?php
                            if($row->kategori_status == 1){
                                echo '<td class="ret_slet hide-on-small"><a href="index.php?side=kategorier&kategori='.$row->kategori_id. '&status=0" title="Deaktiver kategori"><img src="../img/iconmonstr-check-mark.png"></a></td>';
                            }
                            else{
                                echo '<td class="ret_slet hide-on-small"><a href="index.php?side=kategorier&kategori='.$row->kategori_id. '&status=1" title="Aktiver kategori"><img src="../img/iconmonstr-x-mark.png"></a></td>';
                            }
                            ?>

                            <td class="ret_slet"><a href="index.php?side=ret-kategori&kategori=<?php echo $row->kategori_id ?>"><i class="material-icons">edit</i></a></td>

                            <td class="ret_slet"><a href="index.php?side=kategorier&slet&kategori=<?php echo $row->kategori_id ?>" onclick="return(confirm('Er du sikker på, at du vil slette kategorien <?php echo $row->kategori_navn ?> og alle de tilhørende artikler?'))"><i class="material-icons">delete_forever</i></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>