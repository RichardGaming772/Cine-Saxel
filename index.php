<?php

session_start();

include_once "db.php";
include_once "models/Model.php";
include_once "models/Film.php";
include_once "models/Genre.php";
include_once "models/Actor.php";
include_once "models/Director.php";
include_once "models/ApiManager.php";
include_once "models/User.php";
include_once "Controller.php";

if(!isset($_SESSION["films"])){
    $_SESSION["films"] = json_encode(Film::all()); 
}
if(!isset($_SESSION["users"])){
    $_SESSION["users"] = json_encode(User::all()); 
}
if(!isset($_GET["page"])){
    $_GET["page"] = "home"; 
}

global $db;
$st = $db->prepare("select location from films");
$st->execute();
$row = $st->fetch();

include_once "view.php";