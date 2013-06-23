//Overwrite array remove
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
}

function Plane(type) {
  this.x = (type == 1 ? 0 : canvasWidth - 50);
  this.y = 225;
  this.width = 50;
  this.height = 50;
  this.health = 100;
}

Plane.prototype.update = function(y) {
  this.y = y;
}

function Bullet(x, y, type) {
  this.x = x;
  this.y = y;
  this.width = 25;
  this.height = 25;
  this.type = type;
}

Bullet.prototype.update = function() {
  if (this.type == 1) {
    this.x += 5;
  } else {
    this.x -= 5;
  }
}

function Medicine(values) {
  if (values) {
    this.type = values.type;
    this.value = values.value;
    this.x = values.x;
    this.y = values.y;
    this.width = 25;
    this.height = 25;
  } else {
    //Either 1 or 2 (1 = going right, 2 = going left)
    var type = Math.floor((Math.random() * 2) + 1);
    this.type = type; 
    //Random # between 10 and 25
    this.value = Math.floor((Math.random() * 16) + 10);
    this.x = type == 1 ? 0 : canvasWidth - 25;
    //Random # between 0 and height - 25
    this.y = Math.floor(Math.random() * (canvasHeight - 25)); 
    this.width = 25;
    this.height = 25;
  }
}

Medicine.prototype.update = function() {
  if (this.type == 1) {
    this.x += 5;
  } else {
    this.x -= 5;
  }
}

function Game() {
  this.plane1 = new Plane(1);
  this.plane2 = new Plane(2);
  this.bullets = [];
  this.medicines = [];
}

Game.prototype.update = function(y1, y2) {
  this.plane1.y = y1;
  this.plane2.y = y2;
  for (var i = 0; i < this.bullets.length; i++) {
    bullets[i].update();
  }
  for (var i = 0; i < this.medicines.length; i++) {
    medicines[i].update();
  }
}

Game.prototype.add_bullet = function(x, y, type) {
  this.bullets.push(new Bullet(x, y, type));
}

Game.prototype.remove_bullet = function(index) {
  this.bullets.remove(index);
}

Game.prototype.add_medicine = function(m) {
  this.medicines.push(m);
}

Game.prototype.remove_medicine = function(index) {
  this.medicines.remove(index);
}
