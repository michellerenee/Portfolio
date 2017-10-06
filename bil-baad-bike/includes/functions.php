<?php
/**
 * Hvis developer status er sat til true, vil man få oplysninger om, hvor der er fejl henne, hvis der er fejl. Hvis false, vil man få en simpel fejlbesked
 * @param $error_no: Forbindelsesfejlnummer (fx $mysqli->connect_errno)
 * @param $error: Hvilken fejl det er (fx $mysqli->connect_error)
 * @param $line_number: Hvilken linje der er fejl på (der kan bruges __LINE__)
 * @param $file_name: I hvilken fil der er fejl i (der kan bruges __FILE__)
 */
function connect_error ($error_no, $error, $line_number, $file_name){
    if(DEVELOPER_STATUS){
        die('<p>Forbindelsesfejl (' . $error_no . '): ' . $error . '</p><p>Linje: ' . $line_number . '</p><p>Fil: ' .
            $file_name . '</p>');
    }
    else{
        die('Ingen forbindelse til databasen');
    }
}

//-------------------------------------------------------------------------------------//

/**
 * Hvis developer status er sat til true, vil der blive udskrevet alt indhold i get, post, files, sessions, og cookies, som er stillet pænt op med google code prettify (HUSK at have det liggende i i assets, og smide det på body tagget)
 */
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

//-------------------------------------------------------------------------------------//

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
 * Hvis developer status er true, vises detaljerede informationer om, hvor der er fejl i forbindelsen til databasen. Hvis false, en simpel fejlbesked
 * @param $query: Forespørgslen til databasen
 * @param $line_number: Hvilken linje der er fejl på (__LINE__)
 * @param $file_name: Hvilken fil der er fejl i (__FILE__)
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

//-------------------------------------------------------------------------------------//

/**
 * Funktion der kan stille noget data pænt op, hvis funktionen bliver kaldt
 * @param $data: Det data der skal stilles pænt op (fx $row)
 */
function prettyprint($data){
    ?>
    <pre class="prettyprint lang-php"><code><?php print_r($data) ?></code></pre>
    <?php
}

//-------------------------------------------------------------------------------------//

/**
 * Funktion til at forkorte teksten ned til ønsket længde, og som stopper efter det sidste mellemrum, i stedet for midt i et ord
 * @param $tekst: Den tekst der ønskes forkortet
 * @param $antal_tegn: Max antal tegn teksten må fylde
 * @return string: Den forkortede tekst bliver returned, hvor der er tilføjet ... efter teksten, og den er ryddet for tags
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

//-------------------------------------------------------------------------------------//

/**
 * Funktion til at uploade billeder
 * @param $fk_produkt_id: Hvilket produkt billedet tilhører
 * @param int $valgt_til_oversigt: 0 eller 1, alt efter om billedet er valgt til oversigter
 */
function billede_upload ($fk_produkt_id, $valgt_til_oversigt = 0){

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
    $img_stor = $img -> resizeDown(1500);

    // Gem billede i fuld størrelse i ønsket mappe
    $img_stor -> saveToFile($destination);

    // Lav thumbnail med max-bredde på 120 pixels (Højde beregnes automatisk for at bevare proportioner)
    $thumb = $img -> resizeDown(235);

    // Gem thumbnail i ønsket mappe
    $thumb -> saveToFile($thumbs_destination);

    //Definer forespørgsel til at indsætte data i databasen
    $query = "INSERT INTO bib_billeder (billede_filnavn, billede_valgt_oversigt, fk_produkt_id) VALUES ('$billede_billedenavn', $valgt_til_oversigt, $fk_produkt_id)";
    $result = $mysqli->query($query);
    // If result return false, user the function query_error to show debugging info
    if(!$result){
        query_error($query, __LINE__, __FILE__);
    }
}

//-------------------------------------------------------------------------------------//

/**
 * Funktion til hvor mange elementer der må vises pr. side
 * @param $side: Den side funktionen bliver kaldt fra
 * @param $page_length: Den længde på siden som den står til, inden funktionen bliver kørt
 */
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

//-------------------------------------------------------------------------------------//

/**
 * Funktion der viser hvor mange elementer der bliver vist, ud af hvor mange elementer
 * @param $offset: Det antal elementer der bliver sprunget over
 * @param $page_length: Det antal elementer der bliver vist pr. side
 * @param $items_total: Det totale antal af elementer
 * @param $tekst: Den tekst der står på siden (fx produktside: "Viser 1-10 ud af 200 produkter")
 */
function vis_antal_af_hvor_mange($offset, $page_length, $items_total, $tekst){
    $number_to = ($page_length + $offset > $items_total) ? $items_total : ($page_length + $offset);
    echo '<div class="vis_ud_af_hvor_mange twelve columns">
            <p>Viser <span>'.($offset + 1).'</span> - <span>'.$number_to.'</span> ud af <span>'.$items_total.'</span> '.$tekst.'</p>
          </div><!--vis_ud_af_hvor_mange slut-->';
}

//-------------------------------------------------------------------------------------//

/**
 * Funktion der udskriver en pagnination, hvis der er mere end 1 side til fx produkter, brugere osv.
 * @param $page: Den aktuelle side
 * @param $page_no: Det aktuelle sidenummer
 * @param $items_total: Det totale antal elementer
 * @param $page_length: Hvor mange elementer der bliver vist pr. side
 * @param int $page_around: Hvor mange sider der skal vises i pagnination, på hver side af den aktuelle side
 * @param bool $show_disabled_arrows: Om der skal vises pile der er disabled (true), eller om pilene bare slet ikke skal vises (false)
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
            //echo '<li><a href="index.php?side='.$page.'&side-nr='.($page_no - 1).'"><i class="material-icons">keyboard_arrow_left</i></a></li>';
        }
        else if($show_disabled_arrows){
            // Pil tilbage bliver vist, men er deaktiveret
            //echo '<li class="disabled"><i class="material-icons">first_page</i></li>';
            //echo '<li class="disabled"><i class="material-icons">keyboard_arrow_left</i></li>';
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
            //echo '<li><a href="index.php?side='.$page.'&side-nr='.($page_no + 1).'"><i class="material-icons">keyboard_arrow_right</i></a></li>';
            //echo '<li><a href="index.php?side='.$page.'&side-nr='.$pages_total.'"><i class="material-icons">last_page</i></a></li>';
        }
        else if($show_disabled_arrows){
            // Pil frem bliver vist, men er deaktiveret
            //echo '<li class="disabled"><i class="material-icons">keyboard_arrow_right</i></li>';
            //echo '<li class="disabled"><i class="material-icons">last_page</i></li>';
        }
        echo '</ul>';
    }
}

//-------------------------------------------------------------------------------------//


