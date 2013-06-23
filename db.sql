create table users (
  username VARCHAR(20) PRIMARY KEY,
  password VARCHAR(64),
  salt VARCHAR(32)
);

create table games (
  user1 VARCHAR(20),
  user2 VARCHAR(20),
  uuid VARCHAR(64) PRIMARY KEY,
  winner VARCHAR(20),
  completed DATETIME DEFAULT NULL
);