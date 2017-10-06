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
$page_length = isset($_SESSION[$side]['side_laengde'])  ? $_SESSION[$side]['side_laengde']                    : 20;
$page_no     = isset($_SESSION[$side]['side_nr'])       ? $_SESSION[$side]['side_nr']                         : 1;
$search      = isset($_SESSION[$side]['search'])        ? $mysqli->escape_string($_SESSION[$side]['search'])  : '';

// Det antal sider der skal springes over, alt efter hvilken side man er på. -1 gange med side
// længden for at få den til at springe 0 over på første side, men springe over på de andre sider
$offset = ($page_no - 1) * $page_length;

if(isset($_GET['slet']) && isset($_GET['log']) && !empty($_GET['log']) && is_super_admin()){
    $log_id = intval($_GET['log']);

    $query = "DELETE FROM logbog WHERE logbog_id =  $log_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    header('Location: index.php?side=logbog');
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
        <h1>Logbog</h1>
        <div class="row">
            <?php
            $order_sql = ' DESC';
            $order_link = '&order-by=asc';

            if(isset($_GET['order-by'])){
                if($_GET['order-by'] == 'desc'){
                    $order_sql = " DESC";
                    $order_link = "&order-by=asc";
                }
                else if ($_GET['order-by'] == 'asc'){
                    $order_sql = " ASC";
                    $order_link = "&order-by=desc";
                }
            }

            if(isset($_GET['soeg_submit'])){
                $_SESSION[$side]['soeg'] = $_GET['soeg_felt'];
            }

            if(!isset($_SESSION[$side]['soeg'])){
                $_SESSION[$side]['soeg'] = '';
                $search_sess_sql = $mysqli->escape_string($_SESSION[$side]['soeg']);
                $search_sql = "";
            }
            else{
                $search_sess_sql = $_SESSION[$side]['soeg'];
                $search_sql = " AND (logbog_beskrivelse LIKE '%$search_sess_sql%'
                                OR log_type_navn LIKE '%$search_sess_sql%'
                                OR bruger_navn LIKE '%$search_sess_sql%')";
            }
            ?>

            <?php
            vis_pr_side($side, $page_length);
            ?>



            <form class="oversigt_soeg four columns">
                <input hidden name="side" value="logbog">
                <div class="row">
                    <input class="ten columns" type="search" name="soeg_felt" value="<?php echo $search_sess_sql ?>" placeholder="Søg efter..">
                    <input class="two columns" type="submit" name="soeg_submit" value="Søg">
                </div><!--row slut-->
            </form>

            <table class="twelve columns">
                <tr>
                    <th class="sortable"><a href="index.php?side=logbog<?php echo $order_link ?>" title="Nyeste først/ældste først"><i class="material-icons">swap_vert</i> Dato</a></th>
                    <th class="hide-on-small">Type</th>
                    <th>Beskrivelse</th>
                    <th class="hide-on-small">Bruger</th>
                    <th class="hide-on-small">Rolle</th>
                    <?php
                    if(is_super_admin()){
                        echo '<th class="ret_slet">Slet</th>';
                    }
                    ?>
                </tr>
                <?php
                $rolle_adgang = $_SESSION['bruger']['rolle_adgangsniveau'];
                $query = "SELECT DATE_FORMAT(logbog_dato, '%d. %b %Y %H:%i') AS logbog_oprettet, logbog_beskrivelse, log_type_navn, log_type_class, bruger_navn, rolle_navn, logbog_id
                          FROM logbog
                          INNER JOIN log_type ON logbog.fk_type_id = log_type.log_type_id
                          LEFT JOIN brugere ON logbog.fk_bruger_id = brugere.bruger_id
                          LEFT JOIN roller ON brugere.fk_rolle_id = roller.rolle_id
                          WHERE rolle_adgangsniveau <= $rolle_adgang
                          $search_sql";
                // Sender forespørgsel til db
                $result = $mysqli->query($query);

                // Gemmer hvor mange resultater der kommer ud
                $items_total = $result->num_rows;

                // Tilføj order by og limit på db udtræk
                $query .= " 
                          ORDER BY  logbog_dato $order_sql
                          LIMIT $page_length
                          OFFSET $offset";
                // Sender forespørgsel igen
                $result = $mysqli->query($query);
                // If result return false, user the function query_error to show debugging info
                if(!$result){
                    query_error($query, __LINE__, __FILE__);
                }

                while($row = $result->fetch_object()) {
                    ?>
                    <tr>
                        <td><?php echo $row->logbog_oprettet ?></td>
                        <td class="hide-on-small"><span class="<?php echo $row->log_type_class ?>"><?php echo $row->log_type_navn ?></span></td>
                        <td><?php echo $row->logbog_beskrivelse ?></td>
                        <td class="hide-on-small"><?php echo $row->bruger_navn ?></td>
                        <td class="hide-on-small"><?php echo $row->rolle_navn ?></td>
                        <?php
                        if(is_super_admin()){
                            echo '<td class="ret_slet"><a href="index.php?side=logbog&slet&log='.$row->logbog_id.'"><i class="material-icons">close</i></a></td>';
                        }
                        ?>
                    </tr>
                    <?php
                }
                ?>
            </table>

            <?php
            vis_antal_af_hvor_mange($offset, $page_length, $items_total, 'handlinger');
            ?>

            <div class="pagination twelve columns">
                <?php
                pagination($side, $page_no, $items_total, $page_length, 2);
                ?>
            </div><!--pagination slut-->
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->
