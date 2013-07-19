create table users (
  username VARCHAR(20) PRIMARY KEY,
  password VARCHAR(64),
  salt VARCHAR(32),
  loggedin BOOL DEFAULT FALSE
);

create table games (
  user1 VARCHAR(20),
  user2 VARCHAR(20),
  uuid VARCHAR(64) PRIMARY KEY,
  winner VARCHAR(20),
  completed DATETIME DEFAULT NULL
);

create table messages (
  message_id INT(10) AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(20),
  message_time TIMESTAMP,
  message TEXT
);
