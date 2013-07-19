<?php
  session_start();
  require_once("variables.php");

  if (isset($_SESSION['username'])) {
    $s = $db->prepare('select username from users where loggedin=TRUE order by username asc');
    $s->execute();
    $rows = $s->fetchAll();
    echo json_encode($rows);
  }

?>
