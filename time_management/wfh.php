<?php
require_once "../includes/config.php";

// <<<<<<<========== this is for employee work from home ==========>>>>>>>

$click = isset($_POST['click'])?$_POST['click']:null;

if($click === "wfh_entry"){

    $empId = isset($_POST['empId'])?trim($_POST['empId']):null;
    $title = isset($_POST['title'])?$_POST['title']:null;
    $description = isset($_POST['description'])?$_POST['description']:null;

    $error = "";
    if($title == null){
        $error = "Title is required"; 
        die($error);
   
    }
    if($description == null){
        $error = "Description is required"; 
        die($error);
   
    }
    if($empId == null){
        $error = "Employee ID is required"; 
        die($error);
   
    }


    $stmt = $pdo->prepare("INSERT INTO wfh (employee_id, title, description) VALUES (?,?,?)");
    if($stmt->execute([$empId, $title, $description])){

        $_SESSION['WFHsuccess'] = true;
        $_SESSION['WFHsuccessId'] = $empId;
        $error = "success";
        echo $error;
        exit;
    }
    else{
        $error = "Some error occured While submitting your request. Please try again later.";
        echo $error;
        exit;
    }
}

?>