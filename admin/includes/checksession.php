<?php
require_once "config.php";

$check = isset($_POST['info'])?$_POST['info']:'';
    // $_SESSION['CREATED'] = time();
    if($check == "check"){
        if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
        } elseif (time() - $_SESSION['CREATED'] > 10800) {
            // Session expired
            session_unset();
            session_destroy();
            $sessionFile = session_save_path() . '/sess_' . session_id();
            if (file_exists($sessionFile)) {
                unlink($sessionFile);
            }

            echo "expired"; 

            exit;
        }
        else{
            echo "valid";
        }
    }



?>