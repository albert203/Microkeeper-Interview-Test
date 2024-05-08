<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Welcome to your account</h1>
    <?php
        session_start();
        if(isset($_SESSION['email'])){
            echo "Hello, ".$_SESSION['email'];
        }else{
            echo "error";
        }   
    ?>
    <br>
    <button><a href="logout.php">Logout</a></button>
    
</body>
</html>