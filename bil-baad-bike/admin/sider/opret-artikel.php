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
            <span><a href="index.php?side=artikler"><?php echo $vis_sider['artikler']['title'] ?></a></span>
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
                <a href="index.php?side=artikler" class="tilbage"><i class="material-icons">keyboard_arrow_left</i><p>Tilbage til oversigt</p></a>
            </div><!--opret_tilbage slut-->
        </div><!--row slut-->
        <div class="row">
            <?php
            $fejl = $overskrift = $tekst = '';
            if(isset($_POST['gem_bruger'])){
                if(empty($_POST['overskrift']) || empty($_POST['tekst']) || empty($_POST['kategori'])){
                    $fejl .= '<p class="fejlbesked">Du skal udfylde alle felterne</p>';
                }
                else{
                    $overskrift = $mysqli->escape_string($_POST['overskrift']);
                    $tekst = $mysqli->escape_string($_POST['tekst']);
                    $kategori = intval($_POST['kategori']);

                    $bruger = $_SESSION['bruger']['bruger_id'];
                    $query = "INSERT INTO artikler (artikel_overskrift, artikel_tekst, fk_kategori_id, fk_bruger_id) VALUES ('$overskrift', '$tekst', $kategori, $bruger)";
                    $result = $mysqli->query($query);
                    // If result return false, user the function query_error to show debugging info
                    if(!$result){
                        query_error($query, __LINE__, __FILE__);
                    }

                    create_log_event('oprettelse', "Oprettelse af artiklen '".$overskrift."'");

                    header('Location: index.php?side=artikler');
                }
            }
            ?>
            <form method="post" class="twelve columns">
                <?php echo $fejl; ?>
                <div class="row form_row">
                    <div class="six columns">
                        <label for="overskrift">Overskrift</label>
                        <input type="text" name="overskrift" id="overskrift" value="<?php echo $overskrift ?>">

                        <label for="tekst">Tekst</label>
                        <textarea name="tekst" id="tekst"><?php echo $tekst ?></textarea>
                        <script>
                            CKEDITOR.replace('tekst', {
                                toolbar: 'Full'
                            })
                        </script>

                        <label for="kategori">Kategori</label>
                        <select name="kategori" id="kategori">
                            <option value="" selected disabled>Vælg en kategori</option>
                            <?php
                            $query = "SELECT kategori_navn, kategori_id 
                                      FROM kategorier 
                                      WHERE kategori_status = 1
                                      ORDER BY kategori_raekkefolge";
                            $result = $mysqli->query($query);
                            // If result return false, user the function query_error to show debugging info
                            if(!$result){
                                query_error($query, __LINE__, __FILE__);
                            }

                            while($row = $result->fetch_object()){
                                ?>
                                <option value="<?php echo $row->kategori_id ?>"><?php echo $row->kategori_navn ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <button type="submit" name="gem_bruger">Gem</button>
                    </div>
                </div><!--form_row slut-->
            </form>
        </div><!--row slut-->
    </div><!--element slut-->
</div><!--row slut-->
