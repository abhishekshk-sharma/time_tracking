<?php
require_once "../includes/config.php";



$userid = isset($_SESSION['id'])?$_SESSION['id']:null;
if($userid == null){
    header("location: ../login.php");
}

$error = '';
$output = '';

$click = isset($_POST['click'])?$_POST['click']:null;
$error = "error";
if($click == null){
    echo $error;
}


if($click == "changeemail"){
    $newemail = isset($_POST['newEmail'])?$_POST['newEmail']:"";

    if($newemail == ""){
        echo "error";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE employees SET email = ? WHERE emp_id = ?");
    
    if($stmt->execute([$newemail, $userid])){
        echo "success";
        exit;
    }
    else{
        echo "error";
        exit;
    }
}


if($click == "changdob"){
    $newDob = isset($_POST['newDob'])?$_POST['newDob']:"";

    if($newDob == ""){
        echo "error";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE employees SET dob = ? WHERE emp_id = ?");
    
    if($stmt->execute([$newDob, $userid])){
        echo "success";
        exit;
    }
    else{
        echo "error";
        exit;
    }
}


if($click == "changepass"){
    $newPassword = isset($_POST['newPassword'])?$_POST['newPassword']:"";
    $currentPassword = isset($_POST['currentPassword'])?$_POST['currentPassword']:"";

    if($newPassword == ""){
        echo "error";
        exit;
    }
    if($currentPassword == ""){
        echo "error";
        exit;
    }

    $stmt = $pdo->prepare("SELECT password_hash FROM employees WHERE emp_id = ?");
    $stmt->execute([$userid]);
    $currpass = $stmt->fetch(PDO::FETCH_ASSOC);

    if(password_verify($currentPassword, $currpass['password_hash'])){
        $password = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE employees SET password_hash = ? WHERE emp_id = ?");
        
        if($stmt->execute([$password, $userid])){
            echo "success";
            exit;
        }
        else{
            echo "error";
            exit;
        }
    }
    else{
        echo "error else";
    }

}

?>