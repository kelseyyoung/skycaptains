<?php
  session_start();
  require_once("variables.php");
  $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
  $s = $db->prepare("update users set loggedin=FALSE where username=:username");
  $s->execute(array(":username" => $_SESSION["username"]));
  unset($_SESSION['username']);
  header("Location: ../index.php");
?>
