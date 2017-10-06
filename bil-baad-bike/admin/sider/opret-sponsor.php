<div class="row">
    <div class="element bread twelve columns">
        <nav class="breadcrumb">
            <span><a href="./"><?php echo $vis_sider['forside']['title'] ?></a></span>
            <span><a href="index.php?side=sponsor"><?php echo $vis_sider['sponsor']['title'] ?></a></span>
            <span><?php echo $vis_sider[$side]['title'] ?></span>
        </nav>
    </div><!--element slut-->
</div><!--row slut-->

<div class="row">
    <div class="element twelve columns">
        <?php
        if(!isset($_GET['type']) || empty($_GET['type'])){
            die('Siden kan ikke vises. Prøv igen');
        }
        else{
            switch ($_GET['type']){
                case 'sponsor':
                    $fejl = $navn = $kategori = '';
                    if(isset($_POST['gem'])){
                        if($_FILES['billede']['error'] == 4 || empty($_POST['navn']) || empty($_POST['kategori'])){
                            $fejl = '<p class="fejlbesked">Alle felterne skal være udfyldt</p>';
                        }
                        else{
                            $navn = $mysqli->escape_string($_POST['navn']);
                            $kategori = intval($_POST['kategori']);

                            $query = "INSERT INTO sponsorer (sponsor_navn, fk_kategori_id) VALUES ('$navn', $kategori)";
                            //echo $query;
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }
                            $sponsor = $mysqli->insert_id;

                            sponsor_billede_upload_update(150, $sponsor);

                            create_log_event('oprettelse', 'Sponsoren '.$navn.' er blevet oprettet');
                            header('Location: index.php?side=sponsor');
                        }
                    }
                    ?>
                    <h1>Opret sponsor</h1>
                    <div class="row">
                        <div class="opret_tilbage twelve columns">
                            <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                            <a href="index.php?side=sponsor" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
                        </div><!--opret_tilbage slut-->
                    </div><!--row slut-->
                    <div class="row">
                        <form class="six columns" method="post" enctype="multipart/form-data">
                            <?php echo $fejl ?>
                            <label for="billede">Sponsors logo</label>
                            <input type="file" name="billede" id="billede" accept="image/*">

                            <label for="navn">Sponsors navn</label>
                            <input type="text" name="navn" id="navn" value="<?php echo $navn ?>">

                            <label for="kategori">Tilhører kategori</label>
                            <select name="kategori">
                                <option selected disabled>Vælg kategori</option>
                                <?php
                                $kat_query = "SELECT kategori_navn, kategori_id 
                                          FROM kategorier 
                                          WHERE kategori_status = 1 
                                          ORDER BY kategori_raekkefolge";
                                $kat_result = $mysqli->query($kat_query);
                                // If result return false, user the function query_error to show debugging info
                                if(!$kat_result){
                                    query_error($kat_query, __LINE__, __FILE__);
                                }
                                while($kat_row = $kat_result->fetch_object()){
                                    ?>
                                    <option value="<?php echo $kat_row->kategori_id ?>"><?php echo $kat_row->kategori_navn ?></option>
                                    <?php
                                }
                                ?>
                            </select>

                            <button type="submit" name="gem">Opret</button>
                        </form>
                    </div>
                    <?php
                    break;

                case 'pris':
                    $visninger = $pris = '';
                    if(isset($_POST['gem_pris'])){
                        if(empty($_POST['visninger']) ||empty($_POST['pris'])){
                            echo '<p class="fejlbesked">Begge felter skal være udfyldt</p>';
                        }
                        else{
                            $visninger = intval($_POST['visninger']);
                            $pris = $mysqli->escape_string($_POST['pris']);

                            $query = "INSERT INTO sponsor_info (info_visninger, info_pris) VALUES ($visninger, '$pris')";
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }

                            header('Location: index.php?side=sponsor');
                        }
                    }
                    ?>
                    <h1>Opret sponsorpris</h1>
                    <div class="row">
                        <div class="opret_tilbage twelve columns">
                            <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                            <a href="index.php?side=sponsor" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
                        </div><!--opret_tilbage slut-->
                    </div><!--row slut-->
                    <div class="row">
                        <form class="six columns" method="post">
                            <label for="visninger">Visninger</label>
                            <input type="number" name="visninger" id="visninger">

                            <label for="pris">Pris per visning</label>
                            <input type="number" step="0.01" name="pris" id="pris">

                            <button type="submit" name="gem_pris">Opret</button>
                        </form>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
    </div>
</div>