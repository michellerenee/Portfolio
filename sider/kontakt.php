<!-- Hvis du kører alle undersiderne igennem en validator, kan den muligvis sige at der mangler en doctype og head,
men det er fordi indholdet bliver hentet ind på index-filen, hvor de to ting er -->
<div class="kontakt1">
  <section>
    <h1>Kontakt mig</h1>
    <p>Hvis du har lyst til at kontakte mig, kan du enten kontakte mig gennem en af følgende fire muligheder, eller
      gennem kontaktformularen til højre.</p>

    <p>Tlf: 53635338</p>
    <p>Mail: michellereneejensen@hotmail.com</p>
    <p><a href="https://www.linkedin.com/in/michelle-ren%C3%A9e-jensen-7b200678/" target="_blank">LinkedIn</a></p>
    <p><a href="https://github.com/michellerenee" target="_blank">GitHub</a></p>
  </section>
  <div class="bottom-stribe">
    <div class="first"></div>
    <div class="second"></div>
    <div class="third"></div>
    <div class="fourth"></div>
  </div><!-- bottom-stribe slut -->
</div><!-- kontakt1 slut -->

<div class="kontakt2">
  <section>
    <form method="post">
      <?php
      $fejlbesked = '';
      $success = '';

      if(isset($_POST['send'])){
        if(empty($_POST['navn']) || empty($_POST['emne']) || empty($_POST['mail']) || empty($_POST['besked'])){
          $fejlbesked = '<p class="kontakt-fejl-besked">Alle felterne skal være udfyldt</p>';
        }
        else{
          $navn = $_POST['navn'];
          $emne = $_POST['emne'];
          $mail = $_POST['mail'];
          $besked = $_POST['besked'];

          mail('michellereneejensen@hotmail.com', $emne, 'Besked: '. $besked . ' Fra: '. $navn, 'From: '.$mail);
          $success = '<p class="kontakt-success-besked">Beskeden blev sendt!</p>';
          $fejlbesked = '';
        }
      }

      echo $fejlbesked;
      echo $success;
      ?>
      <div class="form1">
        <label for="navn">Navn</label>
        <input id="navn" type="text" name="navn" value="">

        <label for="emne">Emne</label>
        <input id="emne" type="text" name="emne" value="">

        <label for="mail">Mail</label>
        <input id="mail" type="email" name="mail" value="">
      </div>
      <div class="form2">
        <label for="besked">Besked</label>
        <textarea id="besked" name="besked"></textarea>

        <input type="submit" name="send" value="Send">
      </div>
    </form>
    <div class="block"></div>
  </section>
  <div class="bottom-stribe">
    <div class="first"></div>
    <div class="second"></div>
    <div class="third"></div>
    <div class="fourth"></div>
  </div><!-- bottom-stribe slut -->
</div><!-- kontakt2 slut -->