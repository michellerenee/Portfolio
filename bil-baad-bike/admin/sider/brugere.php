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

if(isset($_GET['slet']) && isset($_GET['bruger']) && !empty($_GET['bruger'])){
    $bruger_id = intval($_GET['bruger']);

    $bruger_query = "SELECT bruger_navn, rolle_adgangsniveau, bruger_billede 
                     FROM brugere 
                     INNER JOIN roller ON brugere.fk_rolle_id = roller.rolle_id
                     WHERE bruger_id = $bruger_id";
    $bruger_result = $mysqli->query($bruger_query);
    // If result return false, user the function query_error to show debugging info
    if(!$bruger_result){
        query_error($bruger_query, __LINE__, __FILE__);
    }
    $bruger_row = $bruger_result->fetch_object();

    // Hvis der er et billede, slettes det fra img mappen
    if(file_exists('../img/'.$bruger_row->bruger_billede) && $bruger_row->bruger_billede != 'no_img.png'){
        unlink('../img/'.$bruger_row->bruger_billede);
    }
    // Hvis der er et billede, slettes det fra thumbs mappen
    if(file_exists('../img/thumbs/'.$bruger_row->bruger_billede) && $bruger_row->bruger_billede != 'no_img.png'){
        unlink('../img/thumbs/'.$bruger_row->bruger_billede);
    }

    $query = "SELECT artikel_id FROM artikler WHERE fk_bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    while($row = $result->fetch_object()){
        $del_query = "DELETE FROM kommentarer WHERE fk_artikel_id = $row->artikel_id";
        $del_result = $mysqli->query($del_query);
        // If result return false, user the function query_error to show debugging info
        if(!$del_result){
            query_error($del_query, __LINE__, __FILE__);
        }

        // Sletter alle artikler redaktøren har skrevet
        $del2_query = "DELETE FROM artikel_log WHERE fk_artikel_id = $row->artikel_id";
        $del2_result = $mysqli->query($del2_query);
        // If result return false, user the function query_error to show debugging info
        if(!$del2_result){
            query_error($del2_query, __LINE__, __FILE__);
        }
    }

    // Sletter alle artikler redaktøren har skrevet
    $query = "DELETE FROM artikler WHERE fk_bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    // Sletter alle artikler redaktøren har skrevet
    $query = "DELETE FROM bruger_kat WHERE fk_bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    // Sletter brugeren fra databasen
    $query = "DELETE FROM brugere WHERE bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    create_log_event('sletning', 'Sletning af brugeren '.$bruger_row->bruger_navn.', og alle tilhørende artikler m. kommentarer');
    header('Location: index.php?side=brugere');
}

if(isset($_GET['status']) && isset($_GET['bruger']) && !empty($_GET['bruger'])){
    $status = intval($_GET['status']);
    $bruger_id = intval($_GET['bruger']);

    //echo $status;
    $query = "UPDATE brugere SET bruger_status = $status WHERE bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    $bruger_query = "SELECT bruger_navn FROM brugere WHERE bruger_id = $bruger_id";
    $bruger_result = $mysqli->query($bruger_query);
    // If result return false, user the function query_error to show debugging info
    if(!$bruger_result){
        query_error($bruger_query, __LINE__, __FILE__);
    }
    $bruger_row = $bruger_result->fetch_object();
    $status = ($_GET['status'] == 0) ? 'Deaktivering' : 'Aktivering';

    create_log_event('opdatering', $status.' af brugeren '.$bruger_row->bruger_navn);
    header('Location: index.php?side=brugere');
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
        <h1>Alle brugere</h1>
        <div class="row">
            <?php
            // Variabler defineres på forhånd, så query kaldet ved hvad der skal sorteres efter
            $order_sql = ' bruger_navn DESC';
            $order_link = '&order-by=asc';

            // Hvis der er defineret order-by i URL params, gør følgende
            if(isset($_GET['order-by'])){
                // Tjekker på om der står navn i URL params
                if(isset($_GET['navn'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " bruger_navn DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " bruger_navn ASC";
                        $order_link = "&order-by=desc";
                    }
                }

                // Tjekker på om der står email i URL params
                if(isset($_GET['email'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " bruger_email DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " bruger_email ASC";
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
                $search_sql = " AND (bruger_navn LIKE '%$search_sess_sql%'
                                    OR bruger_email LIKE '%$search_sess_sql%'
                                    OR rolle_navn LIKE '%$search_sess_sql%')";
            }
            ?>
            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=opret-bruger" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>
                <!--<a href="#" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>-->
            </div><!--opret_tilbage slut-->

            <?php
            vis_pr_side($side, $page_length);
            ?>

            <form class="oversigt_soeg four columns">
                <input hidden name="side" value="brugere">
                <div class="row">
                    <input class="ten columns" type="search" name="soeg_felt" value="<?php echo $search_sess_sql ?>" placeholder="Søg efter..">
                    <input class="two columns" type="submit" name="soeg_submit" value="Søg">
                </div><!--row slut-->
            </form>

            <table class="twelve columns">
                <tr>
                    <th class="sortable hide-on-med-and-down"><a href="index.php?side=brugere&navn<?php echo $order_link ?>" title="Navn A-Å/Å-A"><i class="material-icons">swap_vert</i> Navn</a></th>
                    <th class="sortable"><a href="index.php?side=brugere&email<?php echo $order_link ?>" title="Email A-Å/Å-A"><i class="material-icons">swap_vert</i> Email</a></th>
                    <th class="hide-on-small">Rolle</th>
                    <th>Kategorier</th>
                    <th class="hide-on-small">Status</th>
                    <th class="ret_slet">Ret</th>
                    <th class="ret_slet">Slet</th>
                </tr>
                <?php
                $ikke_super = (is_super_admin()) ? "" : "AND rolle_adgangsniveau < 1000";
                $query = "SELECT bruger_id, bruger_navn, bruger_email, bruger_status, rolle_navn, rolle_adgangsniveau
                          FROM brugere 
                          INNER JOIN roller ON brugere.fk_rolle_id = roller.rolle_id
                          WHERE 1=1 $search_sql $ikke_super";
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
                    $count_query = "SELECT COUNT(bruger_kat_id) as antal_kategorier FROM bruger_kat WHERE fk_bruger_id = $row->bruger_id";
                    $count_result = $mysqli->query($count_query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$count_result){
                        query_error($count_query, __LINE__, __FILE__);
                    }
                    $count_row = $count_result->fetch_object();
                    ?>
                    <tr>
                        <td class="hide-on-med-and-down"><?php echo $row->bruger_navn ?></td>
                        <td><?php echo $row->bruger_email ?></td>
                        <td class="hide-on-small"><?php echo $row->rolle_navn ?></td>
                        <td>( <?php echo $count_row->antal_kategorier ?> )</td>
                        <?php
                        if($_SESSION['bruger']['rolle_adgangsniveau'] < $row->rolle_adgangsniveau){
                            if($row->bruger_status == 1){
                                echo '<td class="ret_slet deaktiver hide-on-small"><img src="../img/iconmonstr-check-mark.png"></td>';
                            }
                            else{
                                echo '<td class="ret_slet deaktiver hide-on-small"><img src="../img/iconmonstr-x-mark.png"></td>';
                            }

                            echo '<td class="ret_slet deaktiver"><i class="material-icons">edit</i></td>';
                            echo '<td class="ret_slet deaktiver"><i class="material-icons">delete_forever</i></td>';
                        }
                        else{
                            if($_SESSION['bruger']['bruger_id'] == $row->bruger_id){
                                if($row->bruger_status == 1){
                                    echo '<td class="ret_slet deaktiver hide-on-small"><img src="../img/iconmonstr-check-mark.png"></td>';
                                }
                                else{
                                    echo '<td class="ret_slet deaktiver hide-on-small"><img src="../img/iconmonstr-x-mark.png"></td>';
                                }

                                echo '<td class="ret_slet"><a href="index.php?side=ret-bruger&bruger='.$row->bruger_id.'"><i class="material-icons">edit</i></a></td>';
                                echo '<td class="ret_slet deaktiver"><i class="material-icons">delete_forever</i></td>';
                            }
                            else{
                                if($row->bruger_status == 1){
                                    echo '<td class="ret_slet hide-on-small"><a href="index.php?side=brugere&bruger='.$row->bruger_id. '&status=0" title="Aktiver bruger"><img src="../img/iconmonstr-check-mark.png"></a></td>';
                                }
                                else{
                                    echo '<td class="ret_slet hide-on-small"><a href="index.php?side=brugere&bruger='.$row->bruger_id. '&status=1" title="Aktiver bruger"><img src="../img/iconmonstr-x-mark.png"></a></td>';
                                }

                                ?>
                                <td class="ret_slet"><a href="index.php?side=ret-bruger&bruger=<?php echo $row->bruger_id ?>"><i class="material-icons">edit</i></a></td>

                                <td class="ret_slet"><a href="index.php?side=brugere&slet&bruger=<?php echo $row->bruger_id ?>" onclick="return confirm('Er du sikker på, at du vil slette brugeren <?php echo $row->bruger_navn ?>?')"><i class="material-icons">delete_forever</i></a></td>
                                <?php
                            }
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
