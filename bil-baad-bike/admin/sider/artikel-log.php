<?php
if(!isset($vis_sider)){
    require '../includes/config.php';
    $side = $_GET['side'];
}
// Tjekker om man har adgang til siden. Hvis man ikke har, smides man væk
side_adgang($side);

// Hvis artiklens id ikke er defineret i url params, får man en fejlbesked, og ellers gemmes id'et
if(!isset($_GET['artikel']) || empty($_GET['artikel'])){
    die('Der er ikke valgt en artikel');
}
else{
    $artikel_id = intval($_GET['artikel']);

    $query = "SELECT artikel_overskrift FROM artikler WHERE artikel_id = $artikel_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    $artikel_row = $result->fetch_object();
    $artikel_overskrift = $artikel_row->artikel_overskrift;
}

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

if(isset($_GET['slet']) && isset($_GET['kommentar']) && !empty($_GET['kommentar'])){
    $kommentar_id = intval($_GET['kommentar']);

    $artikel_query = "SELECT artikel_overskrift 
                     FROM artikler 
                     WHERE artikel_id = $artikel_id";
    $artikel_result = $mysqli->query($artikel_query);
    // If result return false, user the function query_error to show debugging info
    if(!$artikel_result){
        query_error($artikel_query, __LINE__, __FILE__);
    }
    $artikel_row = $artikel_result->fetch_object();

    $query = "DELETE FROM kommentarer WHERE kommentar_id = $kommentar_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    create_log_event('sletning', 'Der blev slettet en kommentar til artiklen '.$artikel_row->artikel_overskrift);
    header('Location: index.php?side=artikel-kommentarer&artikel='.$artikel_id);
}

?>
<div class="row">
    <div class="element bread twelve columns">
        <nav class="breadcrumb">
            <span><a href="./"><?php echo $vis_sider['forside']['title'] ?></a></span>
            <span><a href="index.php?side=artikler"><?php echo $vis_sider['artikler']['title'] ?></a></span>
            <span><?php echo $vis_sider[$side]['title'] ?></span>
        </nav>
    </div><!--element slut-->
</div><!--row slut-->

<div class="row">
    <div class="element twelve columns">
        <div class="mini_kommentar_overskrift">
            <h1>Log over artiklen</h1>
            <p><?php echo $artikel_overskrift ?></p>
        </div><!--mini_kommentar_overskrift slut-->
        <div class="row">
            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=artikler" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
            </div><!--opret_tilbage slut-->

            <?php
            vis_pr_side($side, $page_length);
            ?>

            <form class="oversigt_soeg four columns">
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
                    $search_sql = " AND bruger_navn LIKE '%$search_sess_sql%'";
                }
                ?>
                <input hidden name="side" value="artikel-log">
                <div class="row">
                    <input class="ten columns" type="search" name="soeg_felt" value="<?php echo $search_sess_sql ?>" placeholder="Søg efter..">
                    <input class="two columns" type="submit" name="soeg_submit" value="Søg">
                </div><!--row slut-->
            </form>

            <table class="twelve columns">
                <tr>
                    <th>Rettet den</th>
                    <th>Bruger</th>
                    <?php
                    if(is_super_admin()) {
                        ?>
                        <th class="ret_slet">Slet</th>
                        <?php
                    }
                    ?>
                </tr>
                <?php
                $super_admin_sql = is_super_admin() ? "" : " AND rolle_adgangsniveau < 1000";
                $query = "SELECT DATE_FORMAT(log_dato, '%e. %b %Y kl. %H.%i') as log_datotid, bruger_navn, log_id
                          FROM artikel_log 
                          INNER JOIN brugere ON artikel_log.fk_bruger_id = brugere.bruger_id
                          INNER JOIN roller ON brugere.fk_rolle_id = roller.rolle_id
                          WHERE fk_artikel_id = $artikel_id $super_admin_sql $search_sql";
                // Sender forespørgsel til db
                $result = $mysqli->query($query);

                // Tæller hvor mange resultater der kommer ud
                $items_total = $result->num_rows;

                // Det antal sider der skal springes over, alt efter hvilken side man er på. -1 gange med side
                // længden for at få den til at springe 0 over på første side, men springe over på de andre sider

                $offset = ($page_no - 1) * $page_length;

                // Tilføj order by og limit på db udtræk
                $query .= "
                          ORDER BY log_dato DESC 
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
                    <tr>
                        <td><?php echo $row->log_datotid ?></td>
                        <td>Rettet af brugeren <?php echo $row->bruger_navn ?></td>
                        <?php
                        if(is_super_admin()) {
                            ?>
                            <td class="ret_slet"><a href="index.php?side=artikel-log&artikel=<?php echo $artikel_id ?>&slet&log=<?php echo $row->log_id ?>" onclick="return confirm('Er du sikker på, at du vil slette denne begivenhed?')"><i class="material-icons">close</i></a></td>
                            <?php
                        }
                        ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
            vis_antal_af_hvor_mange($offset, $page_length, $items_total, 'brugere');
            ?>
            <div class="pagination twelve columns">
                <?php
                pagination($side, $page_no, $items_total, $page_length, 2);
                ?>
            </div><!--pagination slut-->
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->