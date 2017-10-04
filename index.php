<!doctype html>
<html lang="da-DK">
<?php
// Hvis variablen side er defineret, gemmes den i en variable
if(isset($_GET['side'])){
  $side = $_GET['side'];
}
else{
  $side = 'forside';
}
// Den fulde sti til filen gemmes i variablen $side_sti
$side_sti = 'sider/' . strtolower($side) . '.php';
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Min portfolio</title>
  <link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body>
<nav>
  <div class="content">
    <a class="logo" href="./"><img src="img/logo.png"></a>
    <span>
            <a class="nav1 <?php if(!isset($_GET['side'])){ echo 'active1'; } ?>" href="./">Hjem</a>
            <a class="nav2 <?php if($_GET['side'] == 'projekter'){ echo 'active2'; } ?>" href="
            ./?side=projekter">Projekter</a>
            <a class="nav3 <?php if($_GET['side'] == 'ommig'){ echo 'active3'; } ?>" href="./?side=ommig">Om mig</a>
            <a class="nav4 <?php if($_GET['side'] == 'kontakt'){ echo 'active4'; } ?>" href="
            ./?side=kontakt">Kontakt</a>
        </span>
  </div><!-- content slut -->
</nav>

<main>
  <?php
  if (file_exists($side_sti)){
    include $side_sti;
  }
  else{
    echo 'Fejl. Siden findes ikke';
  }
  ?>
</main>

<footer>
  <p>Michelle Renee Jensen</p>
</footer>

<div class="body_content"></div>
</body>
</html>