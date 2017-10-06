<?php
function connect_error ($error_no, $error, $line_number, $file_name){
    if(DEVELOPER_STATUS){
        die('<p>Forbindelsesfejl (' . $error_no . '): ' . $error . '</p><p>Linje: ' . $line_number . '</p><p>Fil: ' .
            $file_name . '</p>');
    }
    else{
        die('Ingen forbindelse til databasen');
    }
}

//----------------------------------------------------------------------------------------------------------------------//

function is_super_admin(){
    if($_SESSION['bruger']['rolle_adgangsniveau'] == 1000){
        return true;
    }
    else{
        return false;
    }
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param $side = den enkelte side man befinder sig på
 */
function side_adgang($side){
    global $vis_sider;
    // Hvis session brugers adgangsniveau er mindre end minimumskravet til den enkelte side, smides man til forsiden
    if($_SESSION['bruger']['rolle_adgangsniveau'] < $vis_sider[$side]['lvl']) {
        header('Location: ./');
        exit();
    }
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param $type: Type af event der er sket, som skal registreres i logbogen
 * @param $beskrivelse: Beskrivelse af hvad der er sket, som skal gemmes i logbogen
 */
function create_log_event($type, $beskrivelse){
    switch ($type){
        case 'sletning':
            $log_type_id = 4;
            break;
        case 'oprettelse':
            $log_type_id = 3;
            break;
        case 'opdatering':
            $log_type_id = 2;
            break;
        default:
            $log_type_id = 1;
    }

    global $mysqli;
    $beskrivelse        = $mysqli->real_escape_string($beskrivelse);
    $bruger_id          = intval($_SESSION['bruger']['bruger_id']);

    $query = "INSERT INTO logbog (logbog_beskrivelse, fk_bruger_id, fk_type_id)
              VALUES ('$beskrivelse', $bruger_id, $log_type_id)";
    $result = $mysqli->query($query);

    // If result return false, run the function query_error do show debuggen info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param $artikel_id: id på artiklen, som gemmes i fk
 */
function create_artikel_log_event($artikel_id){
    global $mysqli;

    $bruger_id          = intval($_SESSION['bruger']['bruger_id']);
    $artikel_id         = intval($artikel_id);

    $query = "INSERT INTO artikel_log (fk_artikel_id, fk_bruger_id) VALUES ($artikel_id, $bruger_id)";
    $result = $mysqli->query($query);

    // If result return false, run the function query_error do show debuggen info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
}

//----------------------------------------------------------------------------------------------------------------------//

function show_developer_info(){
    ?>
    <br>
    <pre class="prettyprint"><code>GET <?php print_r($_GET) ?></code></pre>
    <pre class="prettyprint"><code>POST <?php print_r($_POST) ?></code></pre>
    <pre class="prettyprint"><code>FILES <?php print_r($_FILES) ?></code></pre>
    <pre class="prettyprint"><code>SESSION <?php print_r($_SESSION) ?></code></pre>
    <pre class="prettyprint"><code>COOKIE <?php print_r($_COOKIE) ?></code></pre>
    <?php
}

//----------------------------------------------------------------------------------------------------------------------//

function prettyprint($data){
    ?>
    <pre class="prettyprint lang-php"><code><?php print_r($data) ?></code></pre>
    <?php
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param $query: queryen til udskrivning med prettyPrint
 * @param $line_number: linjenummer hvor fejlen kan findes
 * @param $file_name: den fil der er fejl i
 */
function query_error($query, $line_number, $file_name){
    global $mysqli;
    if(DEVELOPER_STATUS){
        $message = '<p><strong>' . $mysqli->error .  '</strong></p>
	                <p><strong>Linje: ' . $line_number . '</strong></p>
	                <p><strong>Fil: ' . $file_name . '</strong></p>
	                <br>
	                <pre class="prettyprint lang-sql linenums"><code>' . $query . '</code></pre>';
        echo $message;
        $mysqli->close();
    }
    else{
        echo 'Der skete en fejl. Prøv igen';
        $mysqli->close();
    }
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param $email: den indtastede email ved login
 * @param $adgangskode: den indtastede adgangskode ved login
 * @return bool: returner true eller false, alt efter om login bliver godkendt eller ej
 */
function login($email, $adgangskode){
    // Hvis en af felterne ikke er udfyldt, vises en fejlbesked
    if(empty($email) || empty($adgangskode)){
        echo '<p class="fejlbesked">Begge felter skal udfyldes</p>';
    }
    // Hvis alle felter er udfyldt, fortsæt
    else{
        global $mysqli;
        $email = $mysqli->escape_string($email);

        $query = "SELECT bruger_id, bruger_navn, bruger_adgangskode, rolle_adgangsniveau 
                  FROM brugere
                  INNER JOIN roller ON brugere.fk_rolle_id = roller.rolle_id
                  WHERE bruger_email = '$email'
                  AND bruger_status = 1";
        $result = $mysqli->query($query);
        // Hvis result returner false, køres funktionen query_error, der viser fejlfindings info
        if(!$result){
            query_error($query, __LINE__, __FILE__);
        }
        // Hvis en bruger med den indtastede email blev fundet i databasen, gør det her
        if($result->num_rows == 1){
            $row = $result->fetch_object();
            //print_r($row);
            if (password_verify($adgangskode, $row->bruger_adgangskode)){
                // password_verify($adgangskode, $row->bruger_adgangskode -- når adgangskoder er blevet hashed, skal dette bruges i if checket i stedet for
                // Der genereres et nyt id, da der sker ændringer i session
                session_regenerate_id();
                // Brugerens adgangskode unsettes, da vi ikke skal have den gemt i session
                unset($row->bruger_adgangskode);
                // row gemmes i session bruger
                $_SESSION['bruger']['bruger_id'] = $row->bruger_id;
                $_SESSION['bruger']['bruger_navn'] = $row->bruger_navn;
                $_SESSION['bruger']['rolle_adgangsniveau'] = $row->rolle_adgangsniveau;
                // Use function to insert event in log
                create_log_event('information', $row->bruger_navn.' er logget ind');
                return true;
            }
            else{
                echo '<p class="fejlbesked">Fejl i email eller adgangskode</p>';
            }
        }
        // If no user with the typed email was found in the database, show this alert
        else{
            echo '<p class="fejlbesked">Fejl i email eller adgangskode</p>';
        }
    }
    return false;
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * Funktion til at logge ud med. Den sletter alle de sessions der er blevet sat på siden
 */
function logout(){
    global $vis_sider;
    // For hver session der er gemt, tjekkes der på om den er der, og hvis den er, så bliver den unset.
    foreach ($vis_sider as $side => $key){
        if(isset($_SESSION[$side]))  unset($_SESSION[$side]);
    }
    unset($_SESSION['bruger']);
    header('Location: login.php');
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param int $page: current page
 * @param int $page_no: current page number
 * @param int $items_total: the counted total amount of items
 * @param int $page_length: The desired amount of items per page
 * @param int $page_around: the desired amount of pages to skip before and after the current page
 * @param bool $show_disabled_arrows: show disabled next or previous links, or hide them
 */
function pagination($page, $page_no, $items_total, $page_length, $page_around = 2, $show_disabled_arrows = false){
    // Viser kun pagination hvis der er mere end 1 side (hvis det fulde antal elementer er større end
    // sidens længde)
    if($items_total > $page_length){
        // Det totale antal af sider er lig med antal elementer divideret med sidens længde. Ceil runder resultatet op til næste hele tal.
        $pages_total = ceil($items_total / $page_length);

        // Start page, som er mindst 3 mindre end nuværende side, der bruges som det mindste tal i loop'et
        $page_from = $page_no - $page_around;

        // Hvis nuværende side tal er større end det totale antal sider minus det antal sider man vil have på hver side af nuværende side
        if($page_no > $pages_total - $page_around * 2){
            $page_from = $pages_total - ($page_around * 2 + 2);
        }

        // Hvis start side er mindre end 2, skal den være sat til 2, da vi altid har side 1 fast.
        if($page_from < 2){
            $page_from = 2;
        }

        // 'page_to' indeholder det største tal i vores løkke
        $page_to = $page_no + $page_around;

        //
        if($page_no <= $page_around * 2){
            $page_to = $page_around * 2 + 3;
        }

        // Hvis 'page_to'  er større eller lig med det totale antal af sider, bliver 'page_to' sat til totalt antal
        // sider minus 1, da vi altid har den sidste for sig selv
        if($page_to >= $pages_total){
            $page_to = $pages_total - 1;
        }

        echo '<ul>';

        // Hvis den nuværende side er større end 1
        if($page_no > 1){
            // På alle sider bliver pilen vist, og vil tage den aktuelle side og trække 1 fra
            //echo '<li><a href="index.php?side='.$page.'&side-nr=1"><i class="material-icons">first_page</i></a></li>';
            echo '<li><a href="index.php?side='.$page.'&side-nr='.($page_no - 1).'"><i class="material-icons">keyboard_arrow_left</i></a></li>';
        }
        else if($show_disabled_arrows){
            // Pil tilbage bliver vist, men er deaktiveret
            //echo '<li class="disabled"><i class="material-icons">first_page</i></li>';
            echo '<li class="disabled"><i class="material-icons">keyboard_arrow_left</i></li>';
        }

        // Vis første sides link i pagination
        echo '<li'.($page_no == 1 ? ' class="active"' : '').'><a href="index.php?side='.$page.'&side-nr=1">1</a></li>';

        // Hvis 'page_from' er større end 2, hopper vi nogle sider frem, og viser 2 dots
        if($page_from > 2){
            echo '<li class="disabled"><span>&hellip;</span></li>';
        }

        // For-løkken starter fra 'page_from' og for hver gang 'page_from' er mindre end 'page_to' forøges
        // 'page_from' med 1
        for ($i = $page_from; $i <= $page_to; $i++){
            echo '<li '.($page_no == $i ? ' class="active"' : '').'><a href="index.php?side='.$page.'&side-nr='.$i .'">'.$i.'</a></li>';
        }

        // Hvis 'page_to' er mindre end anden sidste side, har vi skippet nogle sider i slutningen og viser i stedet
        // 3 dots
        if($page_to < $pages_total - 1){
            echo '<li class="disabled">&hellip;</li>';
        }

        // Viser link til sidste side i pagination
        echo '<li'.($page_no == $pages_total ? ' class="active"' : '').'><a href="index.php?side='.$page.'&side-nr='.$pages_total.'">'.$pages_total.'</a></li>';

        // Hvis den aktuelle side er mindre end det antal sider der er i alt
        if($page_no < $pages_total){
            // På alle sider bliver pilen vist, og den tager den aktuelle side, og lægger 1 til
            echo '<li><a href="index.php?side='.$page.'&side-nr='.($page_no + 1).'"><i class="material-icons">keyboard_arrow_right</i></a></li>';
            //echo '<li><a href="index.php?side='.$page.'&side-nr='.$pages_total.'"><i class="material-icons">last_page</i></a></li>';
        }
        else if($show_disabled_arrows){
            // Pil frem bliver vist, men er deaktiveret
            echo '<li class="disabled"><i class="material-icons">keyboard_arrow_right</i></li>';
            //echo '<li class="disabled"><i class="material-icons">last_page</i></li>';
        }
        echo '</ul>';
    }
}

//----------------------------------------------------------------------------------------------------------------------//

/**
 * @param $tekst: Den tekst der skal forkortes
 * @param $antal_tegn: Det max antal tegn der må være
 * @return string: Den forkortede tekst returneres uden tags
 */
function forkort_tekst($tekst, $antal_tegn) {
    // Fjerner tags, så man ikke risikerer at de bliver cuttet
    $tekst = strip_tags($tekst);
    // Tjekker om strings antal tegn er længere end det der er angivet i $antal_tegn der må være, hvis true, fortsæt
    if (mb_strlen($tekst, 'utf8') > $antal_tegn) {
        // Finder det sidste mellemrum i teksten, med start fra nul, og gemmes i variablen $sidste_mellemrum
        $sidste_mellemrum	= strrpos(substr($tekst, 0, $antal_tegn + 1), ' ');
        // Teksten bliver forkortet ned til ved det sidste mellemrum i teksten, og der tilføjes ... efter
        $tekst		= substr($tekst, 0, $sidste_mellemrum) . '&hellip;';
    }
    // Teksten returneres
    return $tekst;
}

//----------------------------------------------------------------------------------------------------------------------//

function billede_upload ($table, $column, $img_size){
    require '../assets/wideimage/WideImage.php';
    global $mysqli;

    $placering = '../img/'; //Hvor skal billedet ligge?
    $thumb_placering = '../img/thumbs/';

    $billede_billedenavn = time().'_'.substr($_FILES['billede']['name'], -30, 30); //Gem billedets aktuelle navn med prefix

    $destination = $placering . $billede_billedenavn; //Den fulde sti til billedet
    $thumbs_destination = $thumb_placering . $billede_billedenavn; //Den fulde sti til thumb-billedet

    //Flyt billede fra midlertidig placering til min mappe
    //move_uploaded_file($_FILES['billede']['tmp_name'], $destination);


    // Load billede i WideImage vha. 'name' på dit input-felt og gem object i variablen img
    $img = WideImage::load('billede');

    // Hvis billedet er større end 1500 px i bredden, vil det få ændret størrelse til, så det er 1500 px i bredden
    $img_stor = $img -> resizeDown(900);

    // Gem billede i fuld størrelse i ønsket mappe
    $img_stor -> saveToFile($destination);

    // Lav thumbnail med max-bredde på 120 pixels (Højde beregnes automatisk for at bevare proportioner)
    $thumb = $img -> resizeDown($img_size);

    // Gem thumbnail i ønsket mappe
    $thumb -> saveToFile($thumbs_destination);

    //Definer forespørgsel til at indsætte data i databasen
    $query = "INSERT INTO $table ($column) VALUES ('$billede_billedenavn')";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
}

//----------------------------------------------------------------------------------------------------------------------//

function sponsor_billede_upload_update ($img_size, $sponsor_id){
    require '../assets/wideimage/WideImage.php';
    global $mysqli;

    $placering = '../img/'; //Hvor skal billedet ligge?
    $thumb_placering = '../img/thumbs/';

    $billede_billedenavn = time().'_'.substr($_FILES['billede']['name'], -30, 30); //Gem billedets aktuelle navn med prefix

    $destination = $placering . $billede_billedenavn; //Den fulde sti til billedet
    $thumbs_destination = $thumb_placering . $billede_billedenavn; //Den fulde sti til thumb-billedet

    //Flyt billede fra midlertidig placering til min mappe
    //move_uploaded_file($_FILES['billede']['tmp_name'], $destination);


    // Load billede i WideImage vha. 'name' på dit input-felt og gem object i variablen img
    $img = WideImage::load('billede');

    // Hvis billedet er større end 1500 px i bredden, vil det få ændret størrelse til, så det er 1500 px i bredden
    $img_stor = $img -> resizeDown(900);

    // Gem billede i fuld størrelse i ønsket mappe
    $img_stor -> saveToFile($destination);

    // Lav thumbnail med max-bredde på 120 pixels (Højde beregnes automatisk for at bevare proportioner)
    $thumb = $img -> resizeDown($img_size);

    // Gem thumbnail i ønsket mappe
    $thumb -> saveToFile($thumbs_destination);

    //Definer forespørgsel til at indsætte data i databasen
    $query = "UPDATE sponsorer SET sponsor_logo = '$billede_billedenavn' WHERE sponsor_id = $sponsor_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
}

//----------------------------------------------------------------------------------------------------------------------//

function profil_billede_upload_update ($img_size, $bruger_id){
    require '../assets/wideimage/WideImage.php';
    global $mysqli;

    $placering = '../img/'; //Hvor skal billedet ligge?
    $thumb_placering = '../img/thumbs/';

    $billede_billedenavn = time().'_'.substr($_FILES['billede']['name'], -30, 30); //Gem billedets aktuelle navn med prefix

    $destination = $placering . $billede_billedenavn; //Den fulde sti til billedet
    $thumbs_destination = $thumb_placering . $billede_billedenavn; //Den fulde sti til thumb-billedet

    //Flyt billede fra midlertidig placering til min mappe
    //move_uploaded_file($_FILES['billede']['tmp_name'], $destination);


    // Load billede i WideImage vha. 'name' på dit input-felt og gem object i variablen img
    $img = WideImage::load('billede');

    // Hvis billedet er større end 1500 px i bredden, vil det få ændret størrelse til, så det er 1500 px i bredden
    $img_stor = $img -> resizeDown(900);

    // Gem billede i fuld størrelse i ønsket mappe
    $img_stor -> saveToFile($destination);

    // Lav thumbnail med max-bredde på 120 pixels (Højde beregnes automatisk for at bevare proportioner)
    $thumb = $img -> resizeDown($img_size);

    // Gem thumbnail i ønsket mappe
    $thumb -> saveToFile($thumbs_destination);

    //Definer forespørgsel til at indsætte data i databasen
    $query = "UPDATE brugere SET bruger_billede = '$billede_billedenavn' WHERE bruger_id = $bruger_id";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
}

//----------------------------------------------------------------------------------------------------------------------//

function vis_pr_side($side, $page_length){
    global $vis_pr_side;
    ?>
    <form class="two columns vis_pr_side_form">
        <input type="hidden" name="side" value="<?php echo $side ?>">
        <p>Vis</p>
        <select name="vis_pr_side" onchange="this.form.submit()">
            <?php
            foreach ($vis_pr_side as $key => $value){
                $selected = ($page_length == $key) ? "selected" : "";
                ?>
                <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php
            }
            ?>
        </select>
        <p>pr. side</p>
    </form>
    <?php
}

//----------------------------------------------------------------------------------------------------------------------//
function vis_antal_af_hvor_mange($offset, $page_length, $items_total, $tekst){
    $number_to = ($page_length + $offset > $items_total) ? $items_total : ($page_length + $offset);
    echo '<div class="vis_ud_af_hvor_mange twelve columns">
            <p>Viser <span>'.($offset + 1).'</span> - <span>'.$number_to.'</span> ud af <span>'.$items_total.'</span> '.$tekst.'</p>
          </div><!--vis_ud_af_hvor_mange slut-->';
}
//----------------------------------------------------------------------------------------------------------------------//

