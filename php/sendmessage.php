<?php
  session_start();
  require_once("variables.php");

  if (isset($_SESSION['username'])) {
    $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
    date_default_timezone_set("America/Phoenix");
    $s = $db->prepare("insert into messages(user, message_time, message) values(:user, :time, :message)");
    $s->bindParam(":user", $_POST['from']);
    $s->bindParam(":time", $_POST['time']);
    $s->bindParam(":message", $_POST['message']);
    $s->execute();
  }

?>
