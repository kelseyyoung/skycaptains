<?php
  session_start();
  require_once("variables.php");
  if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
  }

  $error = array();
  $game = $_GET['game'];
  $s = $db->prepare("delete from games where uuid=:uuid");
  $s->execute(array(":uuid" => $game));
  header("Location: ../home.php");

?>
