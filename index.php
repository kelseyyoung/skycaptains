<?php
  session_start();
  require_once("variables.php");
  if (isset($_SESSION['username'])) {
    header("Location: home.php");
  }
  $error = array();
  if (isset($_POST['username'])) {
    $db = new PDO("mysql:host=" . addslashes($dbhost) . ";dbname=" . $dbname, $dbuser, $dbpassword);

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
    <style>
      a {
	color: white;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
	<h1 class="text-center">SkyCaptains</h1>
	<div class="span6 offset3 well">
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
	      <button type="submit" class="btn btn-primary">Login</button>
	    </div>
	  </form>
	  <?php if (isset($error["message"])) { ?>
	  <div class="alert alert-error">
	    <p><?php echo $error["message"]; ?></p>
	  </div>
	  <?php } ?>
	  <div class="alert alert-info">
	    <h4>Don't have an account?</h4>
	    <p>Get one <a href="register.php">here</a></p>
	  </div>
	</div>
      </div>
    </div>
  </body>
</html>
