<?php
  session_start();
  require_once('php/variables.php');
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
      #chat-bar {
	position: fixed;
	bottom: 0px;
	left: 0px;
      }
      #chat-box-trigger {
	border: 1px solid #000;
	background-color: white;
	color: black;
	margin-left: 0px;
      }
      #chat-box-trigger:hover {
	background-color: #999;
	cursor: pointer;
      }
      #chat-box {
	margin-left: 0px;
	height: 180px;
	color: black;
	padding: 3px;
	background-color: white;
	opacity: 0.8;
      }
      #messages, #chat-users {
	border: 1px solid #000;
	height: 130px;
	overflow: auto;
	padding: 3px;
      }
      #input-box {
	line-height: 15px;
	padding-top: 5px;
	padding-bottom: 0px;
      }
      #input-form {
	margin: 0px;
      }
      #send-message {
	width: 6.382978723404255%;
      }
      .time {
	color: #17A333;
      }
      .user {
	color: blue;
      }
      .message {
	color: black;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
	<a class="btn btn-danger pull-right" href="php/logout.php">Logout</a>
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
		  <a type="button" class="btn btn-danger" href="php/deletegame.php?game=<?php echo $row["uuid"]; ?>">Decline</a>
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
		  <a type="button" class="btn btn-danger" href="php/deletegame.php?game=<?php echo $row['uuid']; ?>">Delete</a>
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
      <div class="row-fluid" id="chat-bar">
	<div class="hide span12" id="chat-box">
	  <div class="row-fluid">
	    <div class="span9" id="messages">
	    <?php
	      //Get messages from last 5 minutes
	      $s = $db->prepare("select * from messages where message_time >= date_sub(utc_timestamp(), interval 5 minute)");
	      $s->execute();
	      $rows = $s->fetchAll();
	      foreach ($rows as $row) {
		$u = $row['user'];
		$t = $row['message_time'];
		$m = $row['message'];
	    ?>
	      <span class="time"><?php echo $t; ?> </span>
	      <span class="user">[<?php echo $u; ?>] </span>
	      <span class="message"><?php echo $m ?></span>
	      <br />
	    <?php } ?>
	    </div>
	    <div class="span3" id="chat-users">
	      <strong>Online Users</strong><br/>
	      <?php
		//Get logged in users
		$s = $db->prepare("select username from users where loggedin=TRUE order by username asc");
		$s->execute();
		$rows = $s->fetchAll();
		foreach($rows as $row) {
		  if ($row['username'] != $_SESSION['username']) {
	      ?>
		<span class='online'><i class='icon-circle'></i></span>&nbsp; <?php echo $row['username']; ?><br />
	      <?php } } ?>
	    </div>
	  </div>
	  <div class="row-fluid">
	    <div class="span12" id="input-box">
	      <form class="form-inline" id="input-form">
		<input type="text" class="span11" id="message" autocomplete="off" placeholder="Press Enter to send" />
		<button type="submit" class="btn" id="send-message">Send</button>
	      </form>
	    </div>
	  </div>
	</div>
	<div class="span12 text-center" id="chat-box-trigger">
	  <p><strong>SkyCaptains Chat</strong></p>
	</div>
      </div>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>
    <script type="text/javascript">

      var sess = null;
      var me = "<?php echo $_SESSION['username']; ?>";
      
      function updateUsers() {
	var chat = $("#chat-users");
	var me = "<?php echo $_SESSION['username']; ?>";
	$.get("php/getusers.php", {}, function(data) {
	  data = $.parseJSON(data);
	  $(chat).empty();
	  $(chat).append('<strong>Online Users</strong><br/>');
	  for (var i = 0; i < data.length; i++) {
	    var user = data[i];
	    if (user[0] != me) {
	      $(chat).append("<span class='online'><i class='icon-circle'>" +
		"</i></span>&nbsp; " + user[0] + "<br />");
	    }
	  }
	});
      }

      function messageReceived(topicUri, event) {
	var m = event["message"];
	var t = event["time"];
	var u = event["from"];
	$("#messages").append('' +
	      '<span class="time">' + t + ' </span>' +
	      '<span class="user">[' + u + '] </span>' +
	      '<span class="message">' + m + '</span><br />');
      }

      $(document).ready(function() {

	$("#chat-box-trigger").click(function() {
	  $("#chat-box").slideToggle();
	});
	//Websocket
	var wsuri = "ws://localhost:9000";
	ab.connect(wsuri,
	  function(session) {
	    console.log("connected");
	    sess = session;
	    sess.subscribe("http://skycaptains.com/chat", messageReceived);
	  },
	  function(code, reason) {
	    //Connection lost
	    console.log("connection lost");
	    $("#messages").append(
	    "The chat server could not be reached. Please try again later.<br />");
	    //Disable input
	    $("#message").attr("disabled", "disabled");
	    sess = null;
	  }
	);

	//Update logged in users every 8 seconds
	setInterval(updateUsers, 8000);

	//Prevent blank submission for game requests
	$("#challenge-form").submit(function() {
	  //Can't challenge empty and can't challenge yourself
	  if ($("#username").val() == "" || $("#username").val() == me) {
	    return false;
	  }
	});

	$("#input-form").submit(function() {
	  //send message via websockets
	  var m = $("#message").val();
	  if (sess && m != "") {
	    var time = new Date().toISOString().replace("T", " ");
	    time = time.substring(0, time.length - 5);
	    sess.publish("http://skycaptains.com/chat",
	    {"from" : me,
	    "time" : time,
	    "message" : m});
	    //send message to db
	    $.post("php/sendmessage.php", {"from" : me, "time" : time, "message": m}, function() { });
	    //add message to messages div
	    $("#messages").append('' +
	      '<span class="time">' + time + ' </span>' +
	      '<span class="user">[' + me + '] </span>' +
	      '<span class="message">' + m + '</span><br />');
	    //Clear old message
	    $("#message").val("");
	  }
	  return false;
	});
      });
    </script>
  </body>
</html>
