<?php
  
  $dbname = "skycaptainsdb";
  $dbhost = "127.0.0.1";
  $dbuser = "root";
  $dbpassword = "hockeytime";

  $title = "SkyCaptains";

  $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);

  date_default_timezone_set('America/Phoenix');

  function generate_salt($length = 32) {
    $salt = "";
    $letters = "abcdefghijklmnopqrstuvwxyz0123456789";
    for ($i = 0; $i < $length; $i++) {
      $last = strlen($letters) - 1;
      $salt .= $letters[mt_rand(0, $last)];
    }
    return $salt;
  }
?>
