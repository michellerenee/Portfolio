<?php
ob_start();
require 'includes/config.php';
?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--Import Google Icon Font-->
<!--        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">-->
        <!--Skeleton styling-->
        <link rel="stylesheet" href="../css/normalize.css">
        <link rel="stylesheet" href="../css/skeleton.css">
        <!--Egen basic styling-->
        <link rel="stylesheet" href="../css/admin_basic.css">
        <title>Log ind - Renee Admin</title>
        <link rel="icon" href="../img/fav.jpg">
    </head>
    <body>

    <div class="main">
        <div class="container">
            <div class="row">
                <div class="login six columns offset-by-three">
                    <img src="../img/logo-light.png" alt="Firmas admin logo">
                    <?php
                    //echo password_hash('1234', PASSWORD_DEFAULT);
                    ?>
                    <div class="row">
                        <form method="post" class="six columns offset-by-three">
                            <?php
                            if(isset($_POST['login'])){
                                if(isset($_POST['email'])){
                                    if(login($_POST['email'], $_POST['adgangskode'])){
                                        header('Location: ./index.php');
                                    }
                                }
                            }
                            ?>
                            <label for="email">Email</label>
                            <input type="email" name="email" value="" required autofocus>

                            <label for="adgangskode">Adgangskode</label>
                            <input type="password" name="adgangskode" value="" required>

                            <input type="submit" name="login" value="Log ind">
                        </form>
                    </div><!--row slut-->
                </div><!--login slut-->
            </div><!--row slut-->
        </div><!--container slut-->
    </div><!--main slut-->

    <footer>
        <div class="container">
            <p>Renee Admin - 2016</p>
        </div><!--container slut-->
    </footer>
    </body>
    </html>
<?php
if (DEVELOPER_STATUS) show_developer_info();
ob_end_flush();