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
            <span><a href="index.php?side=brugere"><?php echo $vis_sider['brugere']['title'] ?></a></span>
            <span><?php echo $vis_sider[$side]['title'] ?></span>
        </nav>
    </div><!--element slut-->
</div><!--row slut-->

<div class="row">
    <div class="element twelve columns">
        <h1>Opret brugere</h1>
        <div class="row">
            <div class="opret_tilbage twelve columns">
                <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                <a href="index.php?side=brugere" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
            </div><!--opret_tilbage slut-->
        </div><!--row slut-->
        <div class="row">
            <?php
            $fejl = $navn = $email = $adgangskode = $rolle = $profilbillede = $profiltekst = '';
            if(isset($_POST['gem_bruger'])){
                if(empty($_POST['navn']) || empty($_POST['email']) || empty($_POST['adgangskode']) || empty($_POST['gentag_adgangskode']) || empty($_POST['rolle']) || empty($_POST['profiltekst'])){
                    $fejl .= '<p class="fejlbesked">Du skal udfylde alle de påkrævede felterne</p>';
                }
                else{
                    $email = $mysqli->escape_string($_POST['email']);
                    $query = "SELECT bruger_email FROM brugere WHERE bruger_email = '$email'";
                    $bruger_result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$bruger_result){
                        query_error($query, __LINE__, __FILE__);
                    }
                    $bruger_row = $bruger_result->fetch_object();

                    if($bruger_result->num_rows > 0){
                        $fejl .= '<p class="fejlbesked">Den valgte mail er allerede i brug</p>';
                    }
                    else{
                        if($_POST['adgangskode'] != $_POST['gentag_adgangskode']){
                            $fejl .= '<p class="fejlbesked">De indtastede adgangskoder er ikke ens</p>';
                        }
                        else{
                            $navn = $mysqli->escape_string($_POST['navn']);
                            $email = $mysqli->escape_string($_POST['email']);
                            $rolle = intval($_POST['rolle']);
                            $profiltekst = $mysqli->escape_string($_POST['profiltekst']);

                            $adgangskode = $mysqli->escape_string($_POST['adgangskode']);
                            $hashed = password_hash($adgangskode, PASSWORD_DEFAULT);

                            $query = "INSERT INTO brugere (bruger_navn, bruger_email, bruger_adgangskode, bruger_profiltekst, fk_rolle_id) VALUES ('$navn', '$email', '$hashed', '$profiltekst', $rolle)";
                            //echo $query;
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }

                            $bruger_id = $mysqli->insert_id;

                            // Hvis der er valgt en eller flere kategorier/møbelserier
                            if(isset($_POST['kategori'])){
                                // Køres en foreach, for hver kategori der er valgt
                                foreach ($_POST['kategori'] AS $kategori){
                                    $kategori_id = intval($kategori);
                                    // Kategori_id og bruger_id gemmes i tabellen
                                    $query = "INSERT INTO bruger_kat (fk_bruger_id, fk_kategori_id) VALUES ($bruger_id, $kategori_id)";
                                    $result = $mysqli->query($query);
                                    // If result return false, user the function query_error to show debugging info
                                    if(!$result){
                                        query_error($query, __LINE__, __FILE__);
                                    }
                                }
                            }

                            // Hvis der er valgt et billede, gemmes det i databasen
                            if(!empty($_FILES['billede']['name'])){
                                profil_billede_upload_update(128, $bruger_id);
                            }



                            create_log_event('oprettelse', 'Brugeren '.$navn.' er blevet oprettet');
                            header('Location: index.php?side=brugere');
                        }
                    }
                }
            }
            ?>
            <form method="post" class="twelve columns" enctype="multipart/form-data">
                <?php echo $fejl; ?>
                <div class="row form_row">
                    <div class="six columns">
                        <label for="navn">Navn</label>
                        <input type="text" name="navn" id="navn" value="<?php echo $navn ?>">

                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo $email ?>">

                        <label for="adgangskode">Adgangskode</label>
                        <input type="password" name="adgangskode" id="adgangskode" value="">

                        <label for="gentag_adgangskode">Gentag adgangskode</label>
                        <input type="password" name="gentag_adgangskode" id="gentag_adgangskode" value="">
                    </div>
                    <div class="six columns">
                        <div class="row">
                            <div class="ten columns">
                                <label for="billede">Profilbillede</label>
                                <input type="file" accept="image/*" name="billede" id="billede" value="">

                                <label for="rolle">Rolle</label>
                                <select name="rolle" id="rolle">
                                    <option value="" selected disabled>Vælg en bruger-rettighed</option>
                                    <?php
                                    $bruger = intval($_SESSION['bruger']['rolle_adgangsniveau']);
                                    $query = "SELECT rolle_navn, rolle_id 
                                      FROM roller 
                                      WHERE rolle_adgangsniveau <= $bruger 
                                      ORDER BY rolle_adgangsniveau";
                                    $result = $mysqli->query($query);
                                    // If result return false, user the function query_error to show debugging info
                                    if(!$result){
                                        query_error($query, __LINE__, __FILE__);
                                    }

                                    while($row = $result->fetch_object()){
                                        $selected = $rolle == $row->rolle_id ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $row->rolle_id ?>" <?php echo $selected ?>><?php echo $row->rolle_navn ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>

                            </div><!--nine columns slut-->
                            <div class="two columns checkboxes">
                                <label>Kategorier</label>
                                <?php
                                $kat_query = "SELECT kategori_navn, kategori_url_navn, kategori_id 
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
                                    <div>
                                        <input type="checkbox" id="<?php echo $kat_row->kategori_url_navn ?>" value="<?php echo $kat_row->kategori_id ?>" name="kategori[]">
                                        <label for="<?php echo $kat_row->kategori_url_navn ?>"><?php echo $kat_row->kategori_navn ?></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <label for="profiltekst">Profiltekst</label>
                        <textarea name="profiltekst" id="profiltekst"></textarea>
                    </div>
                </div><!--row slut-->
                <button type="submit" name="gem_bruger">Gem</button>
            </form>
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->
