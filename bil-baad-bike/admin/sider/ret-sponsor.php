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
                    ?>
                    <h1>Ret sponsor</h1>
                    <?php
                    if(!isset($_GET['sponsor']) || empty($_GET['sponsor'])){
                        die('Der er ikke valgt en sponsor');
                    }
                    else{
                        $sponsor = intval($_GET['sponsor']);
                    }

                    $fejl = '';
                    if(isset($_POST['gem'])){
                        if(empty($_POST['navn']) || empty($_POST['kategori'])){
                            $fejl = '<p class="fejlbesked">Navn og kategori skal være valgt</p>';
                        }
                        else{
                            $navn = $mysqli->escape_string($_POST['navn']);
                            $kategori = intval($_POST['kategori']);

                            $query = "UPDATE sponsorer SET sponsor_navn = '$navn', fk_kategori_id = $kategori WHERE sponsor_id = $sponsor";
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }
                            if(!$_FILES['billede']['error'] == 4){
                                sponsor_billede_upload_update(150, $sponsor);
                            }

                            create_log_event('opdatering', 'Sponsoren '.$navn.' er blevet ændret');
                            header('Location: index.php?side=sponsor');
                        }
                    }

                    $query = "SELECT sponsor_navn, sponsor_logo, fk_kategori_id FROM sponsorer WHERE sponsor_id = $sponsor";
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    $row = $result->fetch_object();
                    ?>
                    <div class="row">
                        <div class="opret_tilbage twelve columns">
                            <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                            <a href="index.php?side=sponsor" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
                        </div><!--opret_tilbage slut-->
                    </div><!--row slut-->
                    <div class="row">
                        <form class="six columns" method="post" enctype="multipart/form-data">
                            <?php echo $fejl ?>
                            <div class="row">
                                <div class="nine columns">
                                    <label for="billede">Nyt sponsor logo</label>
                                    <input type="file" name="billede" id="billede" accept="image/*">
                                </div>
                                <div class="three columns">
                                    <img src="../img/thumbs/<?php echo $row->sponsor_logo ?>">
                                </div>
                            </div>

                            <label for="navn">Sponsors navn</label>
                            <input type="text" name="navn" id="navn" value="<?php echo $row->sponsor_navn ?>">

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
                                    $selected = $row->fk_kategori_id == $kat_row->kategori_id ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $kat_row->kategori_id ?>" <?php echo $selected ?>><?php echo $kat_row->kategori_navn ?></option>
                                    <?php
                                }
                                ?>
                            </select>

                            <button type="submit" name="gem">Gem</button>
                        </form>
                    </div>
                    <?php
                    break;

                case 'tekst':
                    if(isset($_POST['gem_tekst'])){
                        $tekst = $mysqli->escape_string($_POST['tekst']);

                        $query = "UPDATE tekster SET tekst_tekst = '$tekst' WHERE tekst_side = 'sponsor'";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if(!$result){
                            query_error($query, __LINE__, __FILE__);
                        }

                        create_log_event('opdatering', 'Sponsor informationerne er blevet rettet');
                        header('Location: index.php?side=sponsor');
                    }

                    $query = "SELECT tekst_tekst FROM tekster WHERE tekst_side = 'sponsor'";
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    $row = $result->fetch_object();
                    ?>
                    <h1>Ret sponsor-informationer</h1>
                    <div class="row">
                        <div class="opret_tilbage twelve columns">
                            <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                            <a href="index.php?side=sponsor" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
                        </div><!--opret_tilbage slut-->
                    </div><!--row slut-->
                    <div class="row">
                        <form class="six columns" method="post">
                            <label for="tekst">Sponsor informationstekst</label>
                            <textarea name="tekst" id="tekst"><?php echo $row->tekst_tekst ?></textarea>
                            <script>
                                CKEDITOR.replace('tekst', {
                                    toolbar: 'Full'
                                })
                            </script>

                            <button type="submit" name="gem_tekst">Gem</button>
                        </form>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
    </div>
</div>