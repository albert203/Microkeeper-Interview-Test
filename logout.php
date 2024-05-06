<?php 
    session_destroy();
    header('Location: ../index.php');  
    echo 'You have been logged out';
?>