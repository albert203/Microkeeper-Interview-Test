<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        session_start();
        echo "Hello, " . $_SESSION['email'] . " you have been logged out" . "<br>";
        echo "If the above variable produces an error, your logout 
        has destroyed your session."


    ?>
    <p>if you would like to go back to the login page, click <a href="./index.php">here</a></p>
</body>
</html>
<?php

