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
    <link href='http://fonts.googleapis.com/css?family=Jura:400,600' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/skycaptains.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
    <style>
      .online {
	color: green;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
	<a class="btn btn-danger pull-right" href="logout.php">Logout</a>
      </div>
      <div class="row-fluid">
	<div class="span7">
	  <h2>Hello, <?php echo $_SESSION['username']; ?></h2>
	</div>
	<div class="span5">
	  <form class="form-inline" id="challenge-form" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
	    <h3>Send a game request</h3>
	    <label class="control-label" for="username">Challenger Username:</label>
	    <br />
	    <input type="text" id="username" name="username" />
	    <button class="btn btn-primary" type="submit">Send Request</button>
	  </form> 
	  <?php if (isset($error['message'])) { ?>
	  <div class="alert alert-error">
	    <p><?php echo $error['message']; ?></p>
	  </div>
	  <?php } ?>
	</div>
      </div>
      <div class="row-fluid">
	<div class="span6">
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
	      $s = $db->prepare('select * from games where user2=:username and winner is null');
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
	      </tr>
	    <?php
	      }
	    ?>
	    </tbody>
	  </table>
	</div>
	<div class="span6">
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
	      $s = $db->prepare('select * from games where user1=:username and winner is null');
	      $s->execute(array(':username' => $_SESSION['username']));
	      $rows = $s->fetchAll();
	      foreach($rows as $row) {
	    ?>
	      <tr>
		<td><?php echo $row['user2']; ?></td>
		<td>
		  <a type="button" class="btn" href="play.php?game=<?php echo $row['uuid']; ?>">Play</a>
		  <a type="button" class="btn btn-danger" href="deletegame.php?game=<?php echo $row['uuid']; ?>">Delete</a>
		</td>
	      </tr>
	    <?php
	      }
	    ?>
	    </tbody>
	  </table>
	</div>
      </div>
      <div class="row-fluid">
	<div class="span12">
	  <h3>Completed Games</h3>
	  <table class="table">
	    <thead>
	      <tr>
		<th>Date</th>
		<th>Opponent</th>
		<th>Winner</th>
	      </tr>
	    </thead>
	    <tbody>
	    <?php
	      //Get all completed games
	      $s = $db->prepare('select * from games where (user1=:username or user2=:username) and winner is not null order by completed desc');
	      $s->execute(array(":username" => $_SESSION['username']));
	      $rows = $s->fetchAll();
	      foreach($rows as $row) {
		$winner = $row["winner"] == $_SESSION['username'];
		$opponent = null;
		if ($row["user1"] != $_SESSION['username']) {
		  $opponent = $row["user1"];
		} else {
		  $opponent = $row["user2"];
		}
	    ?>
	      <tr class="<?php if ($winner) { ?>success<?php } else { ?>error<?php } ?>">
		<td><?php echo $row["completed"]; ?></td>
		<td><?php echo $opponent; ?></td>
		<td><?php echo $row["winner"]; ?></td>
	      </tr>
	    <?php } ?>
	    </tbody>
	  </table>
	</div>
      </div>
      <div class="row-fluid">
	<div class="span12">
	  <div class="navbar navbar-fixed-bottom pull-right">
	    <ul class="nav pull-right">
	      <li class="dropup"><a href="#" class="dropdown-toggle" data-toggle="dropdown">
		<span class="online"><i class="icon-circle"></i></span>&nbsp; SkyCaptains Chat</a>
		<ul id="chat-users" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
		<?php
		  $s = $db->prepare("select * from users where loggedin=TRUE");
		  $s->execute();
		  $rows = $s->fetchAll();
		  foreach($rows as $row) {
		    if ($row['username'] != $_SESSION['username']) {
		?>
		  <li><a href="#"><span class="online"><i class="icon-circle"></i></span>&nbsp; <?php echo $row["username"]; ?></a></li>
		<?php } } ?>
		</ul>
	      </li>
	    </ul>
	  </div>
	</div>
      </div>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript">
      
      function updateUsers() {
	var chat = $("#chat-users");
	$(chat).empty();
	var me = "<?php echo $_SESSION['username']; ?>";
	$.get("getusers.php", {}, function(data) {
	  data = $.parseJSON(data);
	  for (var i = 0; i < data.length; i++) {
	    var user = data[i];
	    if (user[0] != me) {
	      $(chat).append("<li><a href='#'>" +
		"<span class='online'><i class='icon-circle'>" +
		"</i></span>&nbsp; " + user[0] + "</a></li>");
	    }
	  }
	});
      }

      $(document).ready(function() {
	//Update logged in users every 8 seconds
	setInterval(updateUsers, 8000);

	//Prevent blank submission
	$("#challenge-form").submit(function() {
	  if ($("#username").val() == "") {
	    return false;
	  }
	});
      });
    </script>
  </body>
</html>
