<?php
  session_start();
  require_once("variables.php");
  if (isset($_SESSION['username'])) {

    $id = $_POST['id'];
    $winner = $_POST['winner'];
    $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
    $s = $db->prepare("select * from games where uuid=:id");
    $s->execute(array(":id" => $id));
    $row = $s->fetch();
    $winnerName = "";
    if ($winner == 1) {
      $winnerName = $row["user1"];
    } else {
      $winnerName = $row["user2"];
    }
    $s = $db->prepare("update games set winner=:winner where uuid=:id");
    $s->execute(array(":winner" => $winnerName, ":id" => $id));
    $s = $db->prepare("update games set completed=:date where uuid=:id");
    $s->execute(array(":date" => date("Y-m-d H:i:s"), ":id" => $id));
  }
?>
