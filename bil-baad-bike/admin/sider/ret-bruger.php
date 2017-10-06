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
        <h1>Ret brugere</h1>
            <div class="row">
                <div class="opret_tilbage twelve columns">
                    <!--<a href="#" class="opret"><p>Opret bruger</p><i class="material-icons">add</i></a>-->
                    <a href="index.php?side=brugere" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
                </div><!--opret_tilbage slut-->

                <?php
                if(!isset($_GET['bruger']) || empty($_GET['bruger'])){
                    die('Du kan ikke redigere en bruger, der ikke er valgt');
                }
                else {
                    $bruger_id = intval($_GET['bruger']);
                }
                // Henter adgangsniveau for at tjekke om den aktuelle bruger har adgang til at rette på den valgte bruger
                $query = "SELECT rolle_adgangsniveau 
                          FROM roller
                          INNER JOIN brugere ON roller.rolle_id = brugere.fk_rolle_id
                          WHERE bruger_id = $bruger_id";
                $result = $mysqli->query($query);
                // If result return false, user the function query_error to show debugging info
                if(!$result){
                    query_error($query, __LINE__, __FILE__);
                }
                $row = $result->fetch_object();

                // Hvis brugeren prøver at rette en bruger, de ikke har rettighed til at rette i, vil de få en fejlbesked
                if($row->rolle_adgangsniveau > $_SESSION['bruger']['rolle_adgangsniveau']){
                    die('<p class="fejlbesked">Du har ikke rettighed til at rette på denne bruger</p>');
                }

                $fejl = '';

                if(isset($_POST['gem_bruger'])) {
                    if (empty($_POST['navn']) || empty($_POST['email']) || empty($_POST['rolle']) || empty($_POST['profiltekst'])) {
                        $fejl .= '<p class="fejlbesked">Du skal udfylde alle de påkrævede felter</p>';
                    }

                    // Den indtastede email gemmes
                    $email = $mysqli->escape_string($_POST['email']);
                    // Der tjekkes om email findes i databasen i forvejen
                    $query = "SELECT bruger_email 
                              FROM brugere 
                              WHERE bruger_email = '$email' 
                              AND bruger_id != $bruger_id";
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if (!$result) {
                        query_error($query, __LINE__, __FILE__);
                    }

                    // Hvis der allerede er en mail i databasen der hedder det samme, får man en fejlbesked
                    if ($result->num_rows > 0) {
                        $fejl .= '<p class="fejlbesked">Den indtastede email er allerede i brug</p>';
                    }
                    else {
                        // Det indtastede gemmes i variabler
                        $navn = $mysqli->escape_string($_POST['navn']);
                        $email = $mysqli->escape_string($_POST['email']);
                        $profil_tekst = $mysqli->escape_string($_POST['profiltekst']);
                        $password_sql = '';

                        $rolle = intval($_POST['rolle']);
                        // Henter adgangsniveauet for den rolle der er valgt til brugeren
                        // Henter adgangsniveauet for den valgte rolle, for at tjekke på, om det er højere end den bruger's adgangsnivneau man er logget ind som
                        $query = "SELECT rolle_adgangsniveau FROM roller WHERE rolle_id = $rolle";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if (!$result) {
                            query_error($query, __LINE__, __FILE__);
                        }
                        $row = $result->fetch_object();
                        // Hvis det adgangsniveauet er lavere eller lig med det niveau den aktive bruger har, så må den gemme rollen, ellers ikke
                        $rolle_sql = $row->rolle_adgangsniveau <= $_SESSION['bruger']['rolle_adgangsniveau'] || $_SESSION['bruger']['rolle_adgangsniveau'] == 1000 ? ", fk_rolle_id = $rolle" : "";

                        if (!empty($_POST['adgangskode']) && !empty($_POST['gentag_adgangskode'])) {
                            if ($_POST['adgangskode'] != $_POST['gentag_adgangskode']) {
                                $fejl .= '<p class="fejlbesked">De indtastede adgangskoder er ikke ens</p>';
                            } else {
                                $adgangskode = $mysqli->escape_string($_POST['adgangskode']);
                                $hashed = password_hash($adgangskode, PASSWORD_DEFAULT);
                                $password_sql = ", bruger_adgangskode = '$hashed'";
                            }
                        }

                        $query = "UPDATE brugere SET bruger_navn = '$navn', bruger_email = '$email'$password_sql$rolle_sql WHERE bruger_id = $bruger_id";
                        $result = $mysqli->query($query);
                        // If result return false, user the function query_error to show debugging info
                        if (!$result) {
                            query_error($query, __LINE__, __FILE__);
                        }

                        // Hvis der er uploadet et nyt billede, gemmes det i databasen, via. funktionen
                        if(!$_FILES['billede']['error'] == 4){
                            profil_billede_upload_update(128, $bruger_id);
                        }

                        // Hvis der er valgt en eller flere kategorier/møbelserier
                        if(isset($_POST['kategori'])){
                            // Sletter de forbindelser der er i forvejen
                            $query = "DELETE FROM bruger_kat WHERE fk_bruger_id = $bruger_id";
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }
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

                        // Lav evt. $navn om til $email, da der godt kan være flere med samme navn, men ikke med samme mail
                        create_log_event('opdatering', 'Opdatering af brugeren ' . $navn);
                        header('Location: index.php?side=brugere');
                    }
                }


                $query = "SELECT bruger_navn, bruger_email, bruger_billede, bruger_profiltekst, fk_rolle_id 
                              FROM brugere 
                              WHERE bruger_id = $bruger_id";
                $result = $mysqli->query($query);
                // If result return false, user the function query_error to show debugging info
                if(!$result){
                    query_error($query, __LINE__, __FILE__);
                }
                $row = $result->fetch_object();
            ?>
            <form method="post" class="twelve columns" enctype="multipart/form-data">
                <?php echo $fejl; ?>
                <div class="row form_row">
                    <div class="six columns">
                        <label for="navn">Navn</label>
                        <input type="text" name="navn" id="navn" value="<?php echo $row->bruger_navn ?>">

                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo $row->bruger_email ?>">

                        <label for="adgangskode">Adgangskode</label>
                        <input type="password" name="adgangskode" id="adgangskode" value="">

                        <label for="gentag_adgangskode">Gentag adgangskode</label>
                        <input type="password" name="gentag_adgangskode" id="gentag_adgangskode" value="">
                    </div>
                    <div class="six columns">
                        <div class="row">
                            <div class="nine columns">
                                <input type="hidden" name="tidligere_billede" value="<?php echo $row->bruger_billede ?>">
                                <label for="billede">Profilbillede</label>

                                <input type="file" accept="image/*" name="billede" id="billede" value="">
                                <label for="rolle">Rolle</label>

                                <select name="rolle" id="rolle">
                                    <option value="" selected disabled>Vælg en bruger-rettighed</option>
                                    <?php
                                    $bruger = intval($_SESSION['bruger']['rolle_adgangsniveau']);
                                    $rolle_query = "SELECT rolle_navn, rolle_id 
                                                    FROM roller 
                                                    WHERE rolle_adgangsniveau <= $bruger 
                                                    ORDER BY rolle_adgangsniveau";
                                    $rolle_result = $mysqli->query($rolle_query);
                                    // If result return false, user the function query_error to show debugging info
                                    if(!$rolle_result){
                                        query_error($rolle_query, __LINE__, __FILE__);
                                    }
                                    while($rolle_row = $rolle_result->fetch_object()){
                                        $selected = $row->fk_rolle_id == $rolle_row->rolle_id ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $rolle_row->rolle_id ?>" <?php echo $selected ?>><?php echo $rolle_row->rolle_navn ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="three columns">
                                <img src="../img/thumbs/<?php echo $row->bruger_billede ?>" class="nuvaerende_profilbillede">
                            </div>
                        </div>


                        <div class="row">
                            <div class="nine columns">
                                <label for="profiltekst">Profiltekst</label>
                                <textarea name="profiltekst" id="profiltekst"><?php echo $row->bruger_profiltekst ?></textarea>
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
                                    // Henter id'er ud på kategorier, der er knyttet til brugeren
                                    $bkat_query = "SELECT fk_kategori_id FROM bruger_kat WHERE fk_bruger_id = $bruger_id";
                                    $bkat_result = $mysqli->query($bkat_query);
                                    // If result return false, user the function query_error to show debugging info
                                    if(!$bkat_result){
                                        query_error($bkat_query, __LINE__, __FILE__);
                                    }
                                    while($bkat_row = $bkat_result->fetch_object()){
                                        $valgte_kategorier[] = $bkat_row->fk_kategori_id;
                                    }

                                    print_r($bkat_row);
                                    if(in_array($kat_row->kategori_id, $valgte_kategorier)){
                                        $checked = "checked";
                                    }
                                    else {
                                        $checked = "";
                                    }
                                    ?>
                                    <div>
                                        <input type="checkbox" id="<?php echo $kat_row->kategori_url_navn ?>" value="<?php echo $kat_row->kategori_id ?>" name="kategori[]" <?php echo $checked ?>>
                                        <label for="<?php echo $kat_row->kategori_url_navn ?>"><?php echo $kat_row->kategori_navn ?></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                    </div>
                </div><!--row slut-->
            <button type="submit" name="gem_bruger">Gem</button>
            </form>
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->


