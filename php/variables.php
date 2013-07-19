<?php
  
  $dbname = "[your db]";
  $dbhost = "[your db host]";
  $dbuser = "[your db user]";
  $dbpassword = "[your db password]";

  $title = "SkyCaptains";

  $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);

  date_default_timezone_set('[your timezone]');

  function generate_salt($length = 32) {
    $salt = "";
    $letters = "abcdefghijklmnopqrstuvwxyz0123456789";
    for ($i = 0; $i < $length; $i++) {
      $last = strlen($letters) - 1;
      $salt .= $letters[mt_rand(0, $last)];
    }
    return $salt;
  }

  putenv("SKYCAPTAINS_SERVER=[same as variables.bash]");
  putenv("SKYCAPTAINS_PORT=[same as variables.bash]");
?>
