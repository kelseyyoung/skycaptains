<?php
  session_start();
  require_once("variables.php");
  if (isset($_SESSION['username'])) {
    header("Location: home.php");
  }
  $error = array();
  if (isset($_POST['username'])) {
    $db = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpassword);

    $s = $db->prepare('select * from users where username=:username');
    $s->execute(array(':username' => $_POST['username']));
    $row = $s->fetch();
    $salt = $row['salt'];
    $password = $row['password'];
    if (hash("sha256", $_POST['password'].$salt) === $password) {
      $_SESSION['username'] = $_POST['username'];
      header("Location: home.php");
    } else {
      $error["message"] = "That username or password is incorrect";
    }
  }

?>

<!doctype html>
<html>
  <head>
    <title><?php echo $title; ?> - Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css">
    <link href='http://fonts.googleapis.com/css?family=Jura:400,600' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/skycaptains.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
      h4 {
	color: black !important;
      }
      a {
	color: white;
      }
      body {
	padding-top: 100px;
      }
      h1 {
	font-size: 50px;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
	<div class="span6 offset3 well">
	  <h1 class="text-center">SkyCaptains</h1>
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
	      </div>
	    </div>
	    <div class="form-actions">
	      <button type="submit" class="btn btn-large">Login</button>
	    </div>
	  </form>
	  <?php if (isset($error["message"])) { ?>
	  <div class="alert alert-error">
	    <p><?php echo $error["message"]; ?></p>
	  </div>
	  <?php } ?>
	</div>
      </div>
      <div class="row-fluid">
	<div class="span6 offset3">
	  <div class="alert alert-success">
	    <h4>Don't have an account? Click <a href="register.php">here</a> to register.</h4>
	  </div>
	</div>
      </div>
    </div>
  </body>
</html>
