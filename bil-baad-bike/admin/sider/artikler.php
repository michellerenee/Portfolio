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

if(isset($_GET['slet']) && isset($_GET['artikel']) && !empty($_GET['artikel'])){
    $artikel_id = intval($_GET['artikel']);

    $artikel_query = "SELECT artikel_overskrift 
                     FROM artikler 
                     WHERE artikel_id = $artikel_id";
    $artikel_result = $mysqli->query($artikel_query);
    // If result return false, user the function query_error to show debugging info
    if(!$artikel_result){
        query_error($artikel_query, __LINE__, __FILE__);
    }
    $artikel_row = $artikel_result->fetch_object();

    if(is_super_admin()){
        // Sletter hele loggen der hører til den slettede artikel
        $query = "DELETE FROM artikel_log WHERE fk_artikel_id = $artikel_id";
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }

        // Sletter artiklen fra databasen
        $query = "DELETE FROM artikler WHERE artikel_id = $artikel_id";
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }
    }
    // Hvis den aktuelle bruger ikke er super admin, slettes artiklen kun midlertidigt, og super admin har mulighed for at gendanne den
    else{
        $query = "UPDATE artikler SET artikel_slettet = 1, artikel_slettet_dato = now() WHERE artikel_id = $artikel_id";
        echo $query;
        $result = $mysqli->query($query);
        // If result return false, user the function query_error to show debugging info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }
    }

    create_log_event('sletning', 'Sletning af artiklen '.$artikel_row->artikel_overskrift);
    header('Location: index.php?side=artikler');
}

if(isset($_GET['status']) && isset($_GET['artikel']) && !empty($_GET['artikel'])){
    $status = intval($_GET['status']);
    $artikel_id = intval($_GET['artikel']);

    //echo $status;
    $query = "UPDATE artikler SET artikel_status = $status WHERE artikel_id = $artikel_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    $artikel_query = "SELECT artikel_overskrift FROM artikler WHERE artikel_id = $artikel_id";
    $artikel_result = $mysqli->query($artikel_query);
    // If result return false, user the function query_error to show debugging info
    if(!$artikel_result){
        query_error($artikel_query, __LINE__, __FILE__);
    }
    $artikel_row = $artikel_result->fetch_object();
    $status = ($_GET['status'] == 0) ? 'Deaktivering' : 'Aktivering';

    create_log_event('opdatering', $status.' af artiklen '.$artikel_row->artikel_overskrift);
    header('Location: index.php?side=artikler');
}

// Hvis der er trykket gendan på en artikel
if(isset($_GET['gendan']) && !empty($_GET['gendan'])){
    $artikel_id = intval($_GET['gendan']);

    // Henter og gemmer den gendannede artikels overskrift til loggen
    $query = "SELECT artikel_overskrift FROM artikler WHERE artikel_id = $artikel_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    $row = $result->fetch_object();

    $query = "UPDATE artikler SET artikel_slettet = 0, artikel_slettet_dato = NULL WHERE artikel_id = $artikel_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    create_log_event('rettelse', "Artiklen '".$row->artikel_overskrift."' er blevet gendannet");
    header('Location: index.php?side=artikler');
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
        <h1>Alle artikler</h1>
        <div class="row">
            <?php
            // Variabler defineres på forhånd, så query kaldet ved hvad der skal sorteres efter
            $order_sql = ' artikel_dato DESC';
            $order_link = '&order-by=desc';

            // Hvis der er defineret order-by i URL params, gør følgende
            if(isset($_GET['order-by'])){
                // Tjekker på om der står overskrift i URL params
                if(isset($_GET['overskrift'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " artikel_overskrift DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " artikel_overskrift ASC";
                        $order_link = "&order-by=desc";
                    }
                }

                // Tjekker på om der står redaktor i URL params
                if(isset($_GET['redaktor'])){
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

                // Tjekker på om der står dato i URL params
                if(isset($_GET['dato'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " artikel_dato DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " artikel_dato ASC";
                        $order_link = "&order-by=desc";
                    }
                }

                // Tjekker på om der står dato i URL params
                if(isset($_GET['gendan'])){
                    // Hvis order-by er defineret i URL params som desc, laves sql sætningen så den passer
                    if($_GET['order-by'] == 'desc'){
                        $order_sql = " artikel_slettet DESC, artikel_slettet_dato DESC";
                        $order_link = "&order-by=asc";
                    }
                    // Hvis order-by er defineret i URL params som asc, laves sql sætningen så den passer
                    else if ($_GET['order-by'] == 'asc'){
                        $order_sql = " artikel_slettet ASC, artikel_slettet_dato ASC";
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
                $search_sql = " AND (artikel_overskrift LIKE '%$search_sess_sql%'
                                OR artikel_tekst LIKE '%$search_sess_sql%'
                                OR bruger_navn LIKE '%$search_sess_sql%')";
            }
            ?>
            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=opret-artikel" class="opret"><p>Opret artikel</p><i class="material-icons">add</i></a>
                <!--<a href="#" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>-->
            </div><!--opret_tilbage slut-->

            <?php
            vis_pr_side($side, $page_length);
            ?>

            <form class="oversigt_soeg four columns">
                <input hidden name="side" value="artikler">
                <div class="row">
                    <input class="ten columns" type="search" name="soeg_felt" value="<?php echo $search_sess_sql ?>" placeholder="Søg efter..">
                    <input class="two columns" type="submit" name="soeg_submit" value="Søg">
                </div><!--row slut-->
            </form>

            <table class="twelve columns">
                <tr>
                    <th class="sortable"><a href="index.php?side=artikler&dato<?php echo $order_link ?>" title="Navn A-Å/Å-A"><i class="material-icons">swap_vert</i> Oprettet</a></th>
                    <th class="sortable"><a href="index.php?side=artikler&overskrift<?php echo $order_link ?>" title="Navn A-Å/Å-A"><i class="material-icons">swap_vert</i> Overskrift</a></th>
                    <th class="sortable"><a href="index.php?side=artikler&redaktor<?php echo $order_link ?>" title="Navn A-Å/Å-A"><i class="material-icons">swap_vert</i> Forfatter</a></th>
                    <th>Kategori</th>
                    <th>Visninger</th>
                    <th>Kommentarer</th>
                    <th>Rettelser</th>
                    <?php
                    if(is_super_admin()){
                        ?>
                        <th class="sortable super_admin_gendan"><a href="index.php?side=artikler&gendan<?php echo $order_link ?>" title="Gendan/ikke gendan"><i class="material-icons">swap_vert</i> Gendan</a></th>
                        <?php
                    }
                    ?>
                    <th class="hide-on-small">Status</th>
                    <th class="ret_slet">Ret</th>
                    <th class="ret_slet">Slet</th>
                </tr>
                <?php
                // $ikke_super = (is_super_admin()) ? "" : "AND rolle_adgangsniveau < 1000";
                $super_admin_gendan = (is_super_admin()) ? "" : "AND artikel_slettet = 0";
                $query = "SELECT artikel_overskrift, artikel_antal_visninger, artikel_status, DATE_FORMAT(artikel_dato, '%e. %b %Y kl. %H.%i') AS artikel_datotid, bruger_navn, kategori_navn, artikel_id, artikel_slettet, DATE_FORMAT(artikel_slettet_dato, '%e. %b %Y kl. %H.%i') as slettet_dato 
                          FROM artikler
                          INNER JOIN brugere ON artikler.fk_bruger_id = brugere.bruger_id
                          INNER JOIN kategorier ON artikler.fk_kategori_id = kategorier.kategori_id
                          WHERE 1=1 $super_admin_gendan $search_sql";
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
                    $kom_query = "SELECT COUNT(kommentar_id) as kommentarer FROM kommentarer WHERE fk_artikel_id = $row->artikel_id";
                    $kom_result = $mysqli->query($kom_query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$kom_result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    $kom_row = $kom_result->fetch_object();
                    ?>
                    <tr>
                        <td><?php echo $row->artikel_datotid ?></td>
                        <td><?php echo $row->artikel_overskrift ?></td>
                        <td><?php echo $row->bruger_navn ?></td>
                        <td><?php echo $row->kategori_navn ?></td>
                        <td><?php echo $row->artikel_antal_visninger ?></td>
                        <td><a href="index.php?side=artikel-kommentarer&artikel=<?php echo $row->artikel_id ?>">Vis alle (<?php echo $kom_row->kommentarer ?>) <i class="material-icons">chat</i></a></td>
                        <td><a href="index.php?side=artikel-log&artikel=<?php echo $row->artikel_id ?>">Vis <i class="material-icons">book</i></a></td>
                        <?php
                        if(is_super_admin()){
                            if($row->artikel_slettet == 1){
                                ?>
                                <td class="super_admin_gendan"><a href="index.php?side=artikler&gendan=<?php echo $row->artikel_id ?>" title="Slettet den <?php echo $row->slettet_dato ?>"><i class="material-icons">delete_sweep</i></a></td>
                                <?php
                            }
                            else{
                                ?>
                                <td></td>
                                <?php
                            }
                        }
                        ?>
                        <?php
                        if($row->artikel_status == 1){
                            echo '<td class="ret_slet hide-on-small"><a href="index.php?side=artikler&artikel='.$row->artikel_id. '&status=0" title="Deaktiver artikel"><img src="../img/iconmonstr-check-mark.png"></a></td>';
                        }
                        else{
                            echo '<td class="ret_slet hide-on-small"><a href="index.php?side=artikler&artikel='.$row->artikel_id. '&status=1" title="Aktiver artikel"><img src="../img/iconmonstr-x-mark.png"></a></td>';
                        }
                        ?>
                        <td class="ret_slet"><a href="index.php?side=ret-artikel&artikel=<?php echo $row->artikel_id ?>"><i class="material-icons">edit</i></a></td>

                        <td class="ret_slet"><a href="index.php?side=artikler&slet&artikel=<?php echo $row->artikel_id ?>" onclick="return(confirm('Er du sikker på, at du vil slette artiklen <?php echo $row->artikel_overskrift ?>?'))"><i class="material-icons">delete_forever</i></a></td>

                        <?php
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
