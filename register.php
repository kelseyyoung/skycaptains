<?php
  session_start();
  require_once("variables.php");
  $error = array();
  
  if (isset($_POST['username'])) {
    $db = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpassword);

    //See if username is taken
    $s = $db->prepare('select * from users where username=:username');
    $s->execute(array(':username' => $_POST['username']));
    $row = $s->fetch();
    $pass = true;
    if (!empty($row)) {
      $error["message"] = "That username is already taken";
      $pass = false;
    }
    //If not, make sure passwords match
    if ($_POST['password'] != $_POST['password-confirm']) {
      $error["message"] = "The given passwords do not match";
      $pass = false;
    }
    //Make sure password is >= 5 chars and <= 20
    if (strlen($_POST['password']) < 5 || strlen($_POST['password']) > 20) {
      $pass = false;
      $error["message"] = "Your password must be between 5 and 20 characters";
    }
    //Put into database
    if ($pass) {
      $salt = generate_salt();
      $hash = hash("sha256", $_POST['password'].$salt);
      $db->exec('insert into users(username, password, salt) values("' .$_POST['username'] .'","'.$hash.'","'.$salt. '")');
      header('Location: index.php');
    }
  }
?>
<!doctype html>
<html>
  <head>
    <title><?php echo $title; ?> - Register</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css">
    <link href="http://fonts.googleapis.com/css?family=Jura:400,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/skycaptains.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
      body {
	padding-top: 100px;
      }
      h1 {
	font-size: 40px;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
	<div class="span6 offset3 well">
	  <h1 class="text-center">SkyCaptains Registration</h1>
	  <form class="form-horizontal" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
	    <div class="control-group">
	      <label for="username" class="control-label">Username</label>
	      <div class="controls">
		<input type="text" id="username" name="username" />
	      </div>
	    </div>
	    <div class="control-group">
	      <label for="password" class="control-label">Password</label>
	      <div class="controls">
		<input type="password" id="password" name="password" />
		<span class="help-block">Must be between 5 and 20 characters</span>
	      </div>
	    </div>
	    <div class="control-group">
	      <label for="password-confirm" class="control-label">Confirm Password</label>
	      <div class="controls">
		<input type="password" id="password-confirm" name="password-confirm" />
	      </div>
	    </div>
	    <div class="form-actions">
	      <button type="submit" class="btn btn-primary">Register</button>
	    </div>
	  </form>
	  <?php if (isset($error["message"])) { ?>
	  <div class="alert alert-error">
	    <p><?php echo $error["message"]; ?></p>
	  </div>
	  <?php } ?>
	</div>
      </div>
    </div>
  </body>
</html>
