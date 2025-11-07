<?php
session_start();

session_unset();

    if(session_destroy()){
        header("location: admin_login.php");
    }
    else{
        
        header("location: index.php");
    }

    exit;

?>