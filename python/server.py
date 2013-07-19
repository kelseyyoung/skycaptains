from autobahn.websocket import listenWS, connectWS
from autobahn.wamp import WampServerFactory, WampServerProtocol
from autobahn.wamp import WampClientFactory, WampClientProtocol
from twisted.internet import reactor

from random import randint
import json

#Plane class
class Plane():
  
  def __init__(self, type):
    self.x = 0 if type == 1 else 940 - 50
    self.y = 225
    self.width = 50
    self.height = 50
    self.health = 100

  def update(self, y):
    self.y = y

  def to_dict(self):
    ret = {}
    ret["x"] = self.x
    ret["y"] = self.y
    ret["health"] = self.health
    return ret

#Bullet class
class Bullet():
  
  def __init__(self, x, y, btype):
    self.x = x
    self.y = y
    self.width = 25
    self.height = 25
    self.type = btype

  def to_dict(self):
    ret = {}
    ret["x"] = self.x
    ret["y"] = self.y
    ret["type"] = self.type
    return ret

  def update(self):
    if self.type == 1:
      self.x += 5
    if self.type == 2:
      self.x -= 5

#Medicine class
class Medicine():
  
  def __init__(self):
    self.type = randint(1, 2) #either 1 or 2 
    self.value = randint(10, 25)
    self.x = 0 if self.type == 1 else (940 - 25)
    self.y = randint(0, (940 - 25))
    self.width = 25
    self.height = 25

  def to_dict(self):
    ret = {}
    ret["type"] = self.type
    ret["value"] = self.value
    ret["x"] = self.x
    ret["y"] = self.y
    return ret

  def update(self):
    if (self.type == 1):
      self.x += 5
    else:
      self.x -= 5

#Game class
class SkyCaptainsGame():
  
  def __init__(self, id):
    self.plane_1 = Plane(1)
    self.plane_2 = Plane(2)
    #Initially no bullets
    self.bullets = []
    #Initially no medicines
    self.medicines = []
    self.user1_ready = False
    self.user2_ready = False
    self.id = id 
    self.winner = None

  def to_dict(self):
    ret = {}
    ret["plane1"] = self.plane_1.to_dict()
    ret["plane2"] = self.plane_2.to_dict()
    bullets = []
    for b in self.bullets:
      bullets.append(b.to_dict())
    ret["bullets"] = bullets
    medicines = []
    for m in self.medicines:
      medicines.append(m.to_dict())
    ret["medicines"] = medicines
    return ret

  def update(self):
    for bullet in self.bullets:
      bullet.update()
      if bullet.x < 0 or bullet.x > 940:
	self.remove_bullet(bullet)
      elif bullet.type == 1 and self.detect_collision(self.plane_2, bullet):
	self.plane_2.health -= 5
	self.remove_bullet(bullet)
      elif bullet.type == 2 and self.detect_collision(self.plane_1, bullet):
	self.plane_1.health -= 5
	self.remove_bullet(bullet)
    for medicine in self.medicines:
      medicine.update()
      if medicine.x < 0 or medicine.x > 940:
	self.remove_medicine(medicine)
      elif self.detect_collision(self.plane_1, medicine):
	self.plane_1.health += medicine.value
	if self.plane_1.health > 100:
	  self.plane_1.health = 100
	self.remove_medicine(medicine)
      elif self.detect_collision(self.plane_2, medicine):
	self.plane_2.health += medicine.value
	if self.plane_2.health > 100:
	  self.plane_2.health = 100
	self.remove_medicine(medicine)

  def detect_collision(self, p, o):
    return p.x < o.x + o.width and p.x + p.width > o.x and p.y < o.y + o.height and p.y + p.height > o.y

  def remove_bullet(self, bullet):
    self.bullets.remove(bullet)

  def remove_medicine(self, medicine):
    self.medicines.remove(medicine)

  def generate_bullet(self, x, y):
    if x == 50:
      self.bullets.append(Bullet(x, y, 1))
    else:
      self.bullets.append(Bullet(x, y, 2))

  def generate_medicine(self):
    self.medicines.append(Medicine())

  def is_over(self):
    if self.plane_1.health <= 0:
      self.winner = 2
      return True
    elif self.plane_2.health <= 0:
      self.winner = 1
      return True
    else:
      return False

  def get_winner(self):
    return self.winner 

#SkyCaptains Server handler (essentially)
class SkyCaptainsClient(WampClientProtocol):

  def onSessionOpen(self):
    #Initially empty array of games
    self.games = {}
    self.subscribe("http://skycaptains.com/game", self.onGameInit)

  def onGameInit(self, topicUri, event):
    game_id = event["id"]
    if not game_id in self.games:
      print "new game"
      self.games[game_id] = SkyCaptainsGame(game_id)
      #Register listener for that game
      self.subscribe("http://skycaptains.com/event#" + game_id, self.onGameEvent)
    user = event["user"]
    if user == 1:
      self.games[game_id].user1_ready = True
    else:
      self.games[game_id].user2_ready = True
    if self.games[game_id].user1_ready and self.games[game_id].user2_ready:
      print "game ready"
      #send message
      self.publish("http://skycaptains.com/event#" + game_id, {"to" : "client", "ready" : "ready"})

  def onGameEvent(self, topicUri, event):
    game_id = event["id"]
    type = event["type"]
    if type == "plane":
      plane = event["plane"]
      y = event["y"]
      if plane == 1:
	self.games[game_id].plane_1.y = int(y)
      else:
	self.games[game_id].plane_2.y = int(y)
    elif type == "bullet":
      x = event["x"]
      y = event["y"]
      self.games[game_id].generate_bullet(int(x), int(y))
    elif type == "medicine":
      self.games[game_id].generate_medicine()
    elif type == "update":
      if game_id in self.games:
	self.games[game_id].update()
	if not self.games[game_id].is_over():
	  self.publish("http://skycaptains.com/event#" + game_id, {"to": "client",
	    "game" : json.dumps(self.games[game_id].to_dict())})
	else:
	  self.publish("http://skycaptains.com/event#" + game_id, {"to": "client",
	    "type": "game over", "winner": self.games[game_id].get_winner()})
	  #Delete game from games
	  del self.games[game_id]

#SkyCaptains Server
class SkyCaptainsServer(WampServerProtocol):

  def onSessionOpen(self):
    #Init game
    self.registerForPubSub("http://skycaptains.com/game")
    #Game events
    self.registerForPubSub("http://skycaptains.com/event#", True)
    #Chat
    self.registerForPubSub("http://skycaptains.com/chat")


if __name__ == "__main__":
  factory = WampServerFactory("ws://173.254.39.110:9000", debugWamp = False)
  factory.protocol = SkyCaptainsServer
  factory.setProtocolOptions(allowHixie76 = True)
  listenWS(factory)

  factory = WampClientFactory("ws://173.254.39.110:9000", debugWamp = False)
  factory.protocol = SkyCaptainsClient
  connectWS(factory)

  reactor.run()

