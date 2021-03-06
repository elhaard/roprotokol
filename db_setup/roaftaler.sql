CREATE TABLE event_category (
  name                   VARCHAR(255),
  description            VARCHAR(255),
  priority               INTEGER
);

INSERT INTO event_category VALUE('rotur','rotur',1);
INSERT INTO event_category VALUE('langtur','langtur i Danmark eller udlandet',2);
INSERT INTO event_category VALUE('fest','vilde fester i DSR',10);

--    DROP TABLE event;
CREATE TABLE event (
  id                     INTEGER  NOT NULL AUTO_INCREMENT,
  owner                  INTEGER,  
  boat_category          INTEGER,
  start_time             DATETIME,
  end_time               DATETIME,
  distance               INTEGER, -- Planned distance
  trip_type              INTEGER,
  last_email             DATETIME,
  max_participants       INTEGER,
  location               VARCHAR(255),
  name                   VARCHAR(255),
  category               VARCHAR(255),
  preferred_intensity    VARCHAR(300),
  comment                VARCHAR(5000),
  FOREIGN KEY (owner) REFERENCES Member(id), 
  FOREIGN KEY (boat_category) REFERENCES BoatCategory(id),
  FOREIGN KEY (trip_type) REFERENCES TripType(id),
  PRIMARY KEY(id)
);

CREATE TABLE event_role (
  name       VARCHAR(255) PRIMARY KEY,
  can_post   BOOLEAN,
  is_leader  BOOLEAN,
  is_cox     BOOLEAN  
);

CREATE TABLE event_boat_type (
  event      INTEGER,
  boat_type  INTEGER,
  FOREIGN KEY (boat_type) REFERENCES BoatType(id)
);

CREATE TABLE event_member (
  member     INTEGER,
  event      INTEGER,
  enter_time DATETIME, -- default NOW(),
  role       VARCHAR(255), -- waiting, cox, any, leader, admin
  FOREIGN KEY (member) REFERENCES Member(id),
  FOREIGN KEY (event) REFERENCES event(id),
  PRIMARY KEY (member,event)
);

CREATE TABLE event_invitees (
  member     INTEGER,
  event      INTEGER,
  comment    VARCHAR(255),
  role       VARCHAR(255), -- waiting, cox, any, leader, admin
  FOREIGN KEY (member) REFERENCES Member(id),
  FOREIGN KEY (event) REFERENCES event(id)
);


CREATE TABLE forum (
  name   VARCHAR(255) PRIMARY KEY NOT NULL,
  description VARCHAR(255),
  owner     INTEGER,
  is_open      BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (owner) REFERENCES Member(id)
);

-- INSERT INTO forum VALUE('roaftaler','generelle roaftaler');
-- INSERT INTO forum VALUE('kaproning','for kaproere');


CREATE TABLE forum_subscription (
  member     INTEGER,
  forum      VARCHAR(255),
  role       VARCHAR(255) NOT NULL, -- waiting, cox, any, leader, admin
  FOREIGN KEY (forum) REFERENCES forum(name) ON UPDATE CASCADE,
  FOREIGN KEY (member) REFERENCES Member(id),
  PRIMARY KEY(member,forum)
);

-- drop table forum_message;

CREATE TABLE forum_message (
  member_from  INTEGER,
  created    DATETIME,
  forum      VARCHAR(255),
  subject    VARCHAR(1000),
  message    VARCHAR(10000),
  FOREIGN KEY (forum) REFERENCES forum(name) ON UPDATE CASCADE,
  FOREIGN KEY (member_from) REFERENCES Member(id)
);

CREATE TABLE event_message (
  id         INTEGER  NOT NULL AUTO_INCREMENT,
  member_from  INTEGER,
  created    DATETIME,
  event      INTEGER,
  subject    VARCHAR(1000),
  message    VARCHAR(10000),
  FOREIGN KEY (member_from) REFERENCES Member(id),
  FOREIGN KEY (event)       REFERENCES event(id),
  PRIMARY KEY (id)
);

CREATE TABLE member_message (
 member  INTEGER,
 message INTEGER,
  FOREIGN KEY (message) REFERENCES event_message(id),
  FOREIGN KEY (member) REFERENCES Member(id),
  PRIMARY KEY(member,message)
 );


-- drop TABLE member_setting;
CREATE TABLE member_setting (
 member  INTEGER,
  is_public BOOLEAN NOT NULL DEFAULT FALSE,
  show_status BOOLEAN NOT NULL DEFAULT FALSE,
  show_activities BOOLEAN NOT NULL DEFAULT FALSE,
  FOREIGN KEY (member) REFERENCES Member(id),
  PRIMARY KEY(member)
 );

-- DROP TABLE forum_file;
CREATE TABLE forum_file (
  member_from  INTEGER,
  created      DATETIME,
  forum        VARCHAR(255),
  filename     VARCHAR(1000),
  mime_type    VARCHAR(255),
  file         MEDIUMBLOB,
  expire       DATETIME,
  FOREIGN KEY (forum) REFERENCES forum(name) ON UPDATE CASCADE,
  FOREIGN KEY (member_from) REFERENCES Member(id)
);
