DROP TABLE IF EXISTS `DamageComment`;

CREATE TABLE `DamageHistory` (
   id int auto_increment primary key,
   Damage int not null,
   ResponsibleMember int,
   Description varchar(1000),
   Updated datetime,
   Degree int
);


ALTER TABLE `Damage` ADD COLUMN RepairComment VARCHAR(1000);