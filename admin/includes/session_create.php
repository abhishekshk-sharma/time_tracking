<?php
session_start();

$info = isset($_POST['info'])?$_POST['info']:'';

if($info == "index"){
    if (isset($_POST['userId'])) {
        $_SESSION['getUserById'] = $_POST['userId'];
        echo 0;
    } else {
        echo 1;
    }
}
if($info == "createdate"){
    if (isset($_POST['date'])) {
        $_SESSION['searchDate'] = $_POST['date'];
        echo 0;
    } else {
        echo 1;
    }
}


?>
