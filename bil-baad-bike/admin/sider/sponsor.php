<div class="row">
    <div class="element bread twelve columns">
        <nav class="breadcrumb">
            <span><a href="./"><?php echo $vis_sider['forside']['title'] ?></a></span>
            <span><?php echo $vis_sider[$side]['title'] ?></span>
        </nav>
    </div><!--element slut-->
</div><!--row slut-->

<?php
if(isset($_GET['slet']) && isset($_GET['sponsor']) && !empty($_GET['sponsor'])){
    $sponsor = intval($_GET['sponsor']);

    // Henter og gemmer sponsorens navn til loggen
    $query = "SELECT sponsor_navn FROM sponsorer WHERE sponsor_id = $sponsor";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
    $row = $result->fetch_object();

    $query = "DELETE FROM sponsorer WHERE sponsor_id = $sponsor";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    create_log_event('sletning', 'Sponsoren '.$row->sponsor_navn.' er blevet slettet');
    header('Location: index.php?side=sponsor');
}

if(isset($_GET['slet']) && isset($_GET['sponsorpris']) && !empty($_GET['sponsorpris'])){
    $sponsor_pris = intval($_GET['sponsorpris']);

    $query = "DELETE FROM sponsor_info WHERE info_id = $sponsor_pris";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }

    create_log_event('sletning', 'Der er blevet slettet i sponsorpriser/visninger');
    header('Location: index.php?side=sponsor');
}
?>

<div class="row">
    <div class="element four columns">
        <h1>Sponsorer</h1>
        <div class="row">
            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=opret-sponsor&type=sponsor" class="tilbage"><i class="material-icons">add</i><p>Tilføj sponsor</p></a>
            </div><!--opret_tilbage slut-->
            <?php
            $query = "SELECT sponsor_logo, sponsor_navn, kategori_navn, sponsor_id 
                      FROM sponsorer 
                      INNER JOIN kategorier ON sponsorer.fk_kategori_id = kategorier.kategori_id
                      ORDER BY kategori_raekkefolge, sponsor_navn";
            $result = $mysqli->query($query);
            // If result return false, user the function query_error to show debugging info
            if(!$result){
                query_error($query, __LINE__, __FILE__);
            }
            while($row = $result->fetch_object() ){
                ?>
                <div class="sponsor row">
                    <div class="ten columns">
                        <img src="../img/thumbs/<?php echo $row->sponsor_logo ?>">
                        <p><?php echo $row->sponsor_navn ?> - <span><?php echo $row->kategori_navn ?></span></p>
                    </div>
                    <div class="two columns">
                        <a href="index.php?side=ret-sponsor&type=sponsor&sponsor=<?php echo $row->sponsor_id ?>"><i class="material-icons">edit</i></a>
                        <a href="index.php?side=sponsor&slet&sponsor=<?php echo $row->sponsor_id ?>" onclick="return confirm('Er du sikker på, at du vil slette sponsoren <?php echo $row->sponsor_navn ?>?')"><i class="material-icons">close</i></a>
                    </div>
                </div><!--sponsor slut-->
                <hr class="sponsor_hr">
                <?php
            }
            ?>
        </div>
    </div>

    <div class="element four columns">
        <h1>Sponsor tilmelding</h1>
        <div class="row">
            <?php
            $query = "SELECT tekst_tekst FROM tekster WHERE tekst_side = 'sponsor'";
            $result = $mysqli->query($query);
            // If result return false, user the function query_error to show debugging info
            if(!$result){
                query_error($query, __LINE__, __FILE__);
            }
            $row = $result->fetch_object();
            ?>

            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=ret-sponsor&type=tekst" class="tilbage"><i class="material-icons">edit</i><p>Ret tekst</p></a>
            </div><!--opret_tilbage slut-->
            <p><?php echo $row->tekst_tekst ?></p>
        </div>
    </div>

    <div class="element four columns">
        <h1>Sponsor priser</h1>
        <div  class="row">
            <div class="opret_tilbage twelve columns">
                <a href="index.php?side=opret-sponsor&type=pris" class="tilbage"><i class="material-icons">add</i><p>Tilføj pris</p></a>
            </div><!--opret_tilbage slut-->
            <table class="twelve columns sponsor_table">
                <tr>
                    <th>Visninger</th>
                    <th>Pris per visning</th>
                    <th class="ret_slet">Slet</th>
                </tr>
                <?php
                $query = "SELECT  FORMAT(info_visninger, 0, 'de_DE') as visninger, FORMAT(info_pris, 2, 'de_DE') as pris, info_id FROM sponsor_info ORDER BY info_visninger";
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
                        <td class="ret_slet"><a href="index.php?side=sponsor&slet&sponsorpris=<?php echo $row->info_id ?>" title="Slet denne visningspris fra listen" onclick="return confirm('Er ud sikker på, at du vil slette denne visningspris?')"><i class="material-icons">close</i></a></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
    </div>
</div>