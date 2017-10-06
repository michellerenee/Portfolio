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

if(isset($_GET['slet']) && isset($_GET['tilmelding']) && !empty($_GET['tilmelding'])){
    $tilmelding_id = intval($_GET['tilmelding']);

    // Emailadressen hentes og gemmes til loggen
    $query = "SELECT tilmelding_email FROM nyhedsbrev WHERE tilmelding_id = $tilmelding_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    $row = $result->fetch_object();

    // Emailen slettes fra databasen
    $query = "DELETE FROM nyhedsbrev WHERE tilmelding_id = $tilmelding_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    create_log_event('sletning', 'Emailadressen '.$row->tilmelding_email.' er blevet slettet fra listen');
    header('Location: index.php?side=nyhedsbrev');
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
        <h1>Tilmeldinger til nyhedsbrev</h1>
        <div class="row">
            <?php
            // Variabler defineres på forhånd, så query kaldet ved hvad der skal sorteres efter
            $order_sql = ' tilmelding_dato DESC';
            $order_link = '&order-by=asc';

            // Hvis der er defineret order-by i URL params, gør følgende
            if(isset($_GET['order-by'])){
                // Tjekker på om der står navn i URL params
                if(isset($_GET['dato'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " tilmelding_dato DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " tilmelding_dato ASC";
                        $order_link = "&order-by=desc";
                    }
                }

                // Tjekker på om der står email i URL params
                if(isset($_GET['email'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " tilmelding_email DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " tilmelding_email ASC";
                        $order_link = "&order-by=desc";
                    }
                }
            }

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
                $search_sql = " AND tilmelding_email LIKE '%$search_sess_sql%'";
            }

            vis_pr_side($side, $page_length);
            ?>

            <form class="oversigt_soeg four columns">
                <input hidden name="side" value="nyhedsbrev">
                <div class="row">
                    <input class="ten columns" type="search" name="soeg_felt" value="<?php echo $search_sess_sql ?>" placeholder="Søg efter..">
                    <input class="two columns" type="submit" name="soeg_submit" value="Søg">
                </div><!--row slut-->
            </form>

            <table class="twelve columns">
                <tr>
                    <th class="sortable">
                        <a href="index.php?side=nyhedsbrev&dato<?php echo $order_link ?>" title="Dato nyeste/ældste">
                            <i class="material-icons">swap_vert</i> Tilmeldt
                        </a>
                    </th>
                    <th class="sortable">
                        <a href="index.php?side=nyhedsbrev&email<?php echo $order_link ?>" title="Email a-å/å-a">
                            <i class="material-icons">swap_vert</i> Email
                        </a>
                    </th>
                    <th class="ret_slet">Slet</th>
                </tr>
                <?php
                $query = "SELECT tilmelding_email, DATE_FORMAT(tilmelding_dato, '%d. %b %Y %H:%i') as tilmelding_datotid, tilmelding_id
                          FROM nyhedsbrev 
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
                          ORDER BY $order_sql 
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
                        <td><?php echo $row->tilmelding_datotid ?></td>
                        <td><?php echo $row->tilmelding_email ?></td>
                        <td class="ret_slet"><a href="index.php?side=nyhedsbrev&slet&tilmelding=<?php echo $row->tilmelding_id ?>" onclick="return confirm('Er du sikker på, at du vil slette <?php echo $row->tilmelding_email ?> fra listen?')"><i class="material-icons">delete_forever</i></a></td>
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
