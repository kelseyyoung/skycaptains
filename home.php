<?php
  session_start();
  require_once('variables.php');
  if (!isset($_SESSION['username'])) {
    header('Location: index.php');
  }
  
  $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
  $error = array();
  if (isset($_POST['username'])) {
    //Make sure user exists
    $s = $db->prepare('select * from users where username=:username');
    $s->execute(array(':username' => $_POST['username']));
    $row = $s->fetch();
    $pass = true;
    if (empty($row)) {
      $error["message"] = "That user does not exist";
      $pass = false;
    }
    if ($pass) {
      //Create new game
      $db->exec('insert into games(user1, user2, uuid, winner) values("'.$_SESSION['username'].'","'.$_POST['username'].'","'. microtime(true)*10000 .'", NULL)');
    }
  }
?>
<!doctype html>
<html>
  <head>
    <title><?php echo $title; ?> - Home</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css">
    <style>
      body {
	padding: 8px;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
	<a class="btn btn-danger pull-right" href="logout.php">Logout</a>
	<h2>Hello <?php echo $_SESSION['username']; ?></h2>
      </div>
      <div class="row-fluid">
	<div class="span10 offset1 well">
	  <form class="form-horizontal" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
	    <legend>Send a game request</legend>
	    <div class="control-group">
	      <label class="control-label" for="username">Challenger Username:</label>
	      <div class="controls">
		<input type="text" id="username" name="username" />
	      </div>
	    </div>
	    <div class="row-fluid" style="text-align: right;">
	      <button class="btn btn-primary" type="submit">Send Request</button>
	    </div>
	  </form> 
	  <?php if (isset($error['message'])) { ?>
	  <div class="alert alert-error">
	    <p><?php echo $error['message']; ?></p>
	  </div>
	  <?php } ?>
	</div>
      </div>
      <div class="row-fluid">
	<h3>Game Requests</h3>
	<table class="table table-hover">
	  <thead>
	    <tr>
	      <th>Challenger</th>
	      <th>Actions</th>
	    </tr>
	  </thead>
	  <tbody>
	  <?php
	    //Get all game requests
	    $s = $db->prepare('select * from games where user2=:username');
	    $s->execute(array(':username' => $_SESSION['username']));
	    $rows = $s->fetchAll();
	    foreach($rows as $row) {
	  ?>
	    <tr>
	      <td><?php echo $row['user1']; ?></td>
	      <td>
		<a type="button" class="btn" href="play.php?game=<?php echo $row['uuid']; ?>">Play</a>
		<a type="button" class="btn btn-danger" href="deletegame.php?game=<?php echo $row["uuid"]; ?>">Decline</a>
	      </td>
	  <?php
	    }
	  ?>
	  </tbody>
	</table>
      </div>
      <div class="row-fluid">
	<h3>Your Challenges</h3>
	<table class="table table-hover">
	  <thead>
	    <tr>
	      <th>Oppenent</th>
	      <th>Actions</th>
	    </tr>
	  </thead>
	  <tbody>
	  <?php
	    //Get all game requests
	    $s = $db->prepare('select * from games where user1=:username');
	    $s->execute(array(':username' => $_SESSION['username']));
	    $rows = $s->fetchAll();
	    foreach($rows as $row) {
	  ?>
	    <tr>
	      <td><?php echo $row['user2']; ?></td>
	      <td>
		<a type="button" class="btn" href="play.php?game=<?php echo $row['uuid']; ?>">Play</a>
		<a type="button" class="btn btn-danger" href="deletegame.php?game=<?php echo $row['uuid']; ?>">Decline</a>
	      </td>
	  <?php
	    }
	  ?>
	  </tbody>
	</table>
      </div>
    </div>
  </body>
</html>
