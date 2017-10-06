<?php
if(!isset($vis_sider)){
    require '../includes/config.php';
}
side_adgang('forside');

if(isset($_GET['slet']) && isset($_GET['bruger']) && !empty($_GET['bruger'])){
    $bruger_id = intval($_GET['bruger']);

    $bruger_query = "SELECT bruger_navn FROM brugere WHERE bruger_id = $bruger_id";
    $bruger_result = $mysqli->query($bruger_query);
    // If result return false, user the function query_error to show debugging info
    if(!$bruger_result){
        query_error($bruger_query, __LINE__, __FILE__);
    }
    $bruger_row = $bruger_result->fetch_object();

    $query = "DELETE FROM brugere WHERE bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    create_log_event('sletning', 'Sletning af brugeren '.$bruger_row->bruger_navn);
    header('Location: ./');
}

if(isset($_GET['status']) && isset($_GET['bruger']) && !empty($_GET['bruger'])){
    $status = intval($_GET['status']);
    $bruger_id = intval($_GET['bruger']);

    $bruger_query = "SELECT bruger_navn FROM brugere WHERE bruger_id = $bruger_id";
    $bruger_result = $mysqli->query($bruger_query);
    // If result return false, user the function query_error to show debugging info
    if(!$bruger_result){
        query_error($bruger_query, __LINE__, __FILE__);
    }
    $bruger_row = $bruger_result->fetch_object();

    //echo $status;
    $query = "UPDATE brugere SET bruger_status = $status WHERE bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    $status = ($status == 0) ? 'Deaktivering' : 'Aktivering';
    create_log_event('opdatering', $status.' af brugeren '.$bruger_row->bruger_navn);
    header('Location: ./');
}
?>
<div class="row">
    <a href="index.php?side=nyhedsbrev" class="forside_knap1 four columns">
        <div class="knap_icon"><i class="material-icons">mail_outline</i></div><!--icon slut-->
        <div class="knap_tekst hidden-phone"><p>Nyhedsbrev tilmeldinger</p></div><!--knap_tekst slut-->
    </a><!--forside_knap1 slut-->
    <a href="index.php?side=artikler" class="forside_knap2 four columns">
        <div class="knap_icon"><i class="material-icons">list</i></div><!--icon slut-->
        <div class="knap_tekst hidden-phone"><p>Artikler</p></div><!--knap_tekst slut-->
    </a><!--forside_knap2 slut-->
    <a href="index.php?side=logbog" class="forside_knap3 four columns">
        <div class="knap_icon"><i class="material-icons">book</i></div><!--icon slut-->
        <div class="knap_tekst hidden-phone"><p>Logbog</p></div><!--knap_tekst slut-->
    </a><!--forside_knap3 slut-->
</div><!--row slut-->
<div class="row">
    <div class="element twelve columns">
        <h1 class="h1_forside">Seneste oprettede artikler</h1>
        <div class="row">
            <table class="twelve columns">
                <tr>
                    <th>Oprettet</th>
                    <th>Overskrift</th>
                    <th>Forfatter</th>
                    <th>Kategori</th>
                    <th>Visninger</th>
                    <th>Kommentarer</th>
                    <th>Rettelser</th>
                    <?php
                    if(is_super_admin()){
                        ?>
                        <th>Gendan</th>
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
                          WHERE 1=1 $super_admin_gendan 
                          ORDER BY artikel_dato DESC
                          LIMIT 5";
                // Sender forespørgsel til db
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
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->
