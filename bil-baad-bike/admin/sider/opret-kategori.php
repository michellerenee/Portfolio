<?php
if(!isset($vis_sider)){
    require '../includes/config.php';
    $side = $_GET['side'];
}

// Tjekker om man har adgang til siden. Hvis man ikke har, smides man væk
side_adgang($side);

?>
<div class="row">
    <div class="element bread twelve columns">
        <nav class="breadcrumb">
            <span><a href="./"><?php echo $vis_sider['forside']['title'] ?></a></span>
            <span><a href="index.php?side=kategorier"><?php echo $vis_sider['kategorier']['title'] ?></a></span>
            <span><?php echo $vis_sider[$side]['title'] ?></span>
        </nav>
    </div><!--element slut-->
</div><!--row slut-->

<div class="row">
    <div class="element twelve columns">
        <h1>Opret artikel</h1>
        <div class="row">
            <div class="opret_tilbage twelve columns">
                <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                <a href="index.php?side=kategorier" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
            </div><!--opret_tilbage slut-->
        </div><!--row slut-->
        <div class="row">
            <?php
            $fejl = $navn = $url_navn = '';
            if(isset($_POST['gem'])){
                if(empty($_POST['navn']) || empty($_POST['url_navn'])){
                    $fejl = '<p class="fejlbesked">Begge felter skal være udfyldt</p>';
                }
                else{
                    if (!preg_match('/^[a-z0-9 \-]+$/i', $_POST['url_navn'])){
                        echo '<p class="fejlbesked">Fejl! Url navnet må kun indeholde bogstaverne a-z og bindestreger</p>';
                    }
                    else{
                        $navn = $mysqli->escape_string($_POST['navn']);
                        $url_navn = $mysqli->escape_string($_POST['url_navn']);

                        $query = "SELECT kategori_raekkefolge
                              FROM kategorier 
                              ORDER BY kategori_raekkefolge DESC 
                              LIMIT 1";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if(!$result){
                            query_error($query, __LINE__, __FILE__);
                        }
                        $row = $result->fetch_object();
                        $raekkefolge = ($row->kategori_raekkefolge + 1);

                        $query = "INSERT INTO kategorier (kategori_navn, kategori_url_navn, kategori_raekkefolge) VALUES ('$navn', '$url_navn', $raekkefolge)";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if(!$result){
                            query_error($query, __LINE__, __FILE__);
                        }

                        create_log_event('oprettelse', 'Kategorien '.$navn.' blev oprettet');
                        header('Location: index.php?side=kategorier');
                    }
                }
            }
            ?>
            <form method="post" class="twelve columns">
                <?php echo $fejl ?>
                <div class="row form_row">
                    <div class="six columns">
                        <label for="navn">Kategorinavn</label>
                        <input type="text" name="navn" id="navn" value="<?php echo $navn ?>">

                        <label for="url_navn">Url navn *</label>
                        <input type="text" name="url_navn" id="url_navn" value="<?php echo $url_navn ?>">

                        <button type="submit" name="gem">Gem</button>
                    </div><!--six columns slut-->
                    <div class="six columns">
                        <p class="italic">* Url navnet må kun indeholde bogstaverne a-z og bindestreger</p>
                    </div><!--six columns slut-->
                </div><!--form_row slut-->
            </form>
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->