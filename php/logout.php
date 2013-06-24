<?php
  session_start();
  require_once("variables.php");
  $s = $db->prepare("update users set loggedin=FALSE where username=:username");
  $s->execute(array(":username" => $_SESSION["username"]));
  unset($_SESSION['username']);
  header("Location: ../index.php");
?>
