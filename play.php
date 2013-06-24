<?php 
  session_start();
  require_once('php/variables.php');
  if (!isset($_SESSION['username'])) {
    header('Location: index.php');
  }

  $db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
  $s = $db->prepare('select * from games where uuid=:uuid');
  $s->execute(array(":uuid" => $_GET['game']));
  $game = $s->fetch();
  $myPlane = 0;
  $otherPlane = 0;
  if ($game["user1"] == $_SESSION['username']) {
    //You are plane one if you made the request
    $myPlane = 1;
    $otherPlane = 2;
  } else {
    $myPlane = 2;
    $otherPlane = 1;
  }
  $player1 = $game["user1"];
  $player2 = $game["user2"];

?>
<!doctype html>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Jura:400,600">
    <link rel="stylesheet" href="css/skycaptains.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row" id="loading">
	<div class="span12">
	  <h1>Waiting for other player...</h1>
	  <div class="progress progress-striped active">
	    <div class="bar" style="width: 100%"></div>
	  </div>
	</div>
      </div>
      <div class="row hide" id="canvas-row">
	<div id="canvas-wrap" class="span12">
	  <canvas id="canvas" height="500" width="940"></canvas>
	</div>
      </div>
      <div class="row hide" id="score-row">
	<div class="span12">
	  <div class="row">
	    <div class="span4" style="text-align: left;">
	      <div id="plane-1-health" class="progress progress-success">
		<div class="bar" style="width: 100%"></div>
	      </div>
	    </div>
	    <div class="span4">
	      <span class="pull-left">
		<?php echo $player1; ?>
	      </span>
	      <span class="pull-right">
		<?php echo $player2; ?>
	      </span>
	    </div>
	    <div class="span4" style="text-alight: right;">
	      <div id="plane-2-health" class="progress progress-success">
		<div class="bar" style="width: 100%"></div>
	      </div>
	    </div>
	  </div>
	</div>
      </div>
    </div>

    <script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="js/skycaptains.js"></script>
    <script type="text/javascript">

    Array.prototype.remove = function(from, to) {
      var rest = this.slice((to || from) + 1 || this.length);
      this.length = from < 0 ? this.length + from : from;
      return this.push.apply(this, rest);
    }

    var planeDim = 50;
    var canvas, context, plane1, plane2, background, bullet1, bullet2, medicine;
    var canvasHeight = 500;
    var canvasWidth = 940;
    var canFire = true;
    var sess = null;
    var plane1Img = "img/plane1.png";
    var plane2Img = "img/plane2.png";
    var bullet1Img = "img/bullet1.png";
    var bullet2Img = "img/bullet2.png";
    var medicineImg = "img/medicine.png";
    var game;
    var myPlane = <?php echo $myPlane; ?>;
    var otherPlane = <?php echo $otherPlane; ?>;
    var uuid = "<?php echo $game['uuid']; ?>";
    var keysDown = {};
    var ready = false;
    var updateInterval;
    var medTimeout;

    function generateMedicine() {
      sess.publish("http://skycaptains.com/event#" + uuid,
	{"to" : "server",
	"id" : uuid,
	"type" : "medicine"}); 
      //Generate medicine in next 1 - 10 seconds
      medTimeout = setTimeout(generateMedicine, Math.floor((Math.random() * 10000) + 1000));
    }

    function onMessageRecv(topicUri, event) {
      var to = event["to"];
      if (to != "server") {
	//Got game update
	if (event["ready"]) {
	  //Remove loading div
	  $("div#loading").remove();
	  $("#canvas-row").show();
	  $("#score-row").show();
	  if (myPlane == 1) {
	    //Player 1 sets update interval
	    updateInterval = setInterval(function() { sess.publish("http://skycaptains.com/event#" + uuid, {"to" : "server", "id" : uuid, "type" : "update"}); }, 10);
	    //Player 1 sets generate medicine timeout
	    medTimeout = setTimeout(generateMedicine, Math.floor((Math.random() * 10000) + 1000));
	  }
	  //Both start updating
	  setInterval(update, 10);
	} else if (event["type"] && event["type"] == "game over") {
	  if (myPlane == 1) {
	    //Clear events
	    clearInterval(updateInterval);
	    clearTimeout(medTimeout)
	    //Player 1 records game;
	    $.post("php/recordgame.php", {"id" : uuid, "winner" : event["winner"]}, function(data) {
	      console.log(data);
	      window.location = "home.php";
	    });
	  } else {
	    window.location = "home.php";
	  }
	} else {
	  render(event);
	}
      }
    }

    addEventListener("keydown", function(e) {
      keysDown[e.keyCode] = true;
    }, false);

    addEventListener("keyup", function(e) {
      delete keysDown[e.keyCode];
    }, false);

    function detectCollision(plane, o) {
      return plane.x < o.x + o.width &&
	plane.x + plane.width > o.x &&
	plane.y < o.y + o.height &&
	plane.y + plane.height > o.y;
    }

    function update() {
      if (87 in keysDown) {
	if (myPlane == 1) {
	  game.plane1.y -= 5;
	  if (game.plane1.y < 0) { game.plane1.y = 0; }
	  sess.publish("http://skycaptains.com/event#" + uuid,
	    {"type" : "plane",
	    "to" : "server",
	    "plane" : 1,
	    "id" : uuid,
	    "y": game.plane1.y});
	} else {
	  game.plane2.y -= 5;
	  if (game.plane2.y < 0) { game.plane2.y = 0; }
	  sess.publish("http://skycaptains.com/event#" + uuid,
	    {"type" : "plane",
	    "to" : "server",
	    "plane" : 2,
	    "id" : uuid,
	    "y": game.plane2.y});
	}
      }
      if (83 in keysDown) {
	if (myPlane == 1) {
	  game.plane1.y += 5;
	  if (game.plane1.y > 450) { game.plane1.y = 450; }
	  sess.publish("http://skycaptains.com/event#" + uuid,
	    {"type" : "plane",
	    "to" : "server",
	    "plane" : 1,
	    "id" : uuid,
	    "y":game.plane1.y});
	} else {
	  game.plane2.y += 5;
	  if (game.plane2.y > 450) { game.plane2.y = 450; }
	  sess.publish("http://skycaptains.com/event#" + uuid,
	    {"type" : "plane",
	    "to" : "server",
	    "plane" : 2,
	    "id" : uuid,
	    "y": game.plane2.y});
	}
      }
      if (13 in keysDown && canFire) {
	if (myPlane == 1) {
	  game.add_bullet(50, game.plane1.y + 12, 1);
	  sess.publish("http://skycaptains.com/event#" + uuid,
	    {"type" : "bullet",
	    "to" : "server",
	    "id" : uuid,
	    "x": 50,
	    "y": game.plane1.y});
	} else {
	  game.add_bullet(canvasWidth - 50, game.plane2.y + 12, 2);
	  sess.publish("http://skycaptains.com/event#" + uuid,
	    {"type" : "bullet",
	    "to" : "server",
	    "id" : uuid,
	    "x": canvasWidth - 50,
	    "y": game.plane2.y});
	}
	canFire = false;
	setTimeout(function() { canFire = true; }, 200);
      }
    }

    var render = function(data) {
      data = $.parseJSON(data["game"]);
      var medicineData = data["medicines"];
      var bulletData = data["bullets"];
      var plane1Data = data["plane1"];
      var plane2Data = data["plane2"];
      //Clear canvas
      context.save();
      context.setTransform(1, 0, 0, 1, 0, 0);
      context.clearRect(0, 0, canvas.width, canvas.height);
      //Redraw
      context.drawImage(background, 0, 0);
      context.drawImage(plane1, plane1Data["x"], plane1Data["y"], 50, 50);
      context.drawImage(plane2, plane2Data["x"], plane2Data["y"], 50, 50);
      for (var i = 0; i < bulletData.length; i++) {
	var b = bulletData[i];
	if (b["type"] == 1) {
	  context.drawImage(bullet1, b["x"], b["y"], 25, 25);
	} else {
	  context.drawImage(bullet2, b["x"], b["y"], 25, 25);
	}
      }
      for (var i = 0; i < medicineData.length; i++) {
	context.drawImage(medicine, medicineData[i]["x"], medicineData[i]["y"], 25, 25);
      }
      //Update health bars
      $("#plane-1-health").attr("class", "progress");
      $("#plane-2-health").attr("class", "progress");
      $("#plane-1-health > .bar").width(plane1Data["health"] + "%");
      $("#plane-2-health > .bar").width(plane2Data["health"] + "%");
      if (plane1Data["health"] <= 25) {
	$("#plane-1-health").addClass("progress-danger");
      } else if (plane1Data["health"] <= 50) {
	$("#plane-1-health").addClass("progress-warning");
      } else {
	$("#plane-1-health").addClass("progress-success");
      }
      if (plane2Data["health"] <= 25) {
	$("#plane-2-health").addClass("progress-danger");
      } else if (plane2Data["health"] <= 50) {
	$("#plane-2-health").addClass("progress-warning");
      } else {
	$("#plane-2-health").addClass("progress-success");
      }
    }
    
    $(document).ready(function() {
      //Get canvas context
      canvas = document.getElementById('canvas');
      context = canvas.getContext('2d');
      //Create game
      game = new Game();
      //Load background
      background = new Image();
      background.onload = function () {
	context.drawImage(background, 0, 0);
      };
      background.src = "img/background.png";
      //Load plane1
      plane1 = new Image();
      plane1.onload = function() {
	context.drawImage(plane1, 0, 225, planeDim, planeDim);
      }
      plane1.src = plane1Img;
      //Load plane2
      plane2 = new Image();
      plane2.onload = function() {
	context.drawImage(plane2, canvasWidth - 50, 225, planeDim, planeDim);
      }
      plane2.src = plane2Img;
      //Load bullet1
      bullet1 = new Image();
      bullet1.onload = function() {
      }
      bullet1.src = bullet1Img;
      //Load bullet2
      bullet2 = new Image();
      bullet2.onload = function() {
      }
      bullet2.src = bullet2Img;
      //Load medicine
      medicine = new Image();
      medicine.onload = function() {
      }
      medicine.src = medicineImg;

      //Set up websocket
      var wsuri = "ws://localhost:9000";
      ab.connect(wsuri,
	function(session) {
	  //Session established
	  sess = session;
	  sess.subscribe("http://skycaptains.com/event#" + uuid, onMessageRecv);
	  sess.publish("http://skycaptains.com/game", {"to" : "server", "id" : uuid, "user" : myPlane});
	}, 
	function (code, reason) {
	  //Connection lost
	  window.location("home.php");
	  sess = null;
	}
      );
    }); //End document ready

    </script>
  </body>
</html>
