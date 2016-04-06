CREATE DATABASE IF NOT EXISTS `watchedthatmovie`;
CREATE SCHEMA IF NOT EXISTS `watchedthatmovie`;
USE `watchedthatmovie`;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `movieInfo`;
DROP TABLE IF EXISTS `movieList`;
DROP TABLE IF EXISTS `friends`;
DROP TABLE IF EXISTS `recommendations`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `movie`;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE IF NOT EXISTS `user` (
  `userID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `points` INT UNSIGNED DEFAULT 0,
  `color` ENUM('default','banana','apple','raspberry','plum') COLLATE utf8_unicode_ci DEFAULT 'default',
  `notifications` BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (`userID`))
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `friends` (
  `userID` INT UNSIGNED NOT NULL,
  `friendID` INT UNSIGNED NOT NULL,
  `since` DATE DEFAULT NULL,
  `status` ENUM('requested','accepted','denied') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`userID`,`friendID`),
  CONSTRAINT `fk_userID`
      FOREIGN KEY (`userID`)
      REFERENCES user(`userID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `fk_friendID`
      FOREIGN KEY (`friendID`)
      REFERENCES user(`userID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `movie` (
  `movieID` INT UNSIGNED NOT NULL,
  `original_title` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `watchers` INT UNSIGNED NOT NULL DEFAULT 0,
  `ratings` INT UNSIGNED NOT NULL DEFAULT 0,
  `rating_points` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`movieID`))
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `movielist` (
  `movieID` INT UNSIGNED NOT NULL,
  `userID` INT UNSIGNED NOT NULL,
  `user_rating` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('watched','watchlist', 'deleted') NOT NULL,
  `watched_date` DATE DEFAULT NULL,
  PRIMARY KEY (`movieID`,`userID`),
  CONSTRAINT fk_movieID_ml
      FOREIGN KEY (`movieID`)
      REFERENCES movie(`movieID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `fk_userID_ml`
      FOREIGN KEY (`userID`)
      REFERENCES user(`userID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `movieinfo` (
  `movieID` INT UNSIGNED NOT NULL,
  `language` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plot` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `title` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `release_date` DATE,
  `poster` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`movieID`,`language`),
  CONSTRAINT `fk_movieID_mi`
      FOREIGN KEY (`movieID`)
      REFERENCES movie(`movieID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `recommendations` (
  `recID` INT UNSIGNED AUTO_INCREMENT,
  `fromID` INT UNSIGNED NOT NULL,
  `toID` INT UNSIGNED NOT NULL,
  `movieID` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`recID`),
  CONSTRAINT `fk_fromID`
  FOREIGN KEY (`fromID`)
  REFERENCES user(`userID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_toID`
  FOREIGN KEY (`toID`)
  REFERENCES user(`userID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_movieID_rec`
  FOREIGN KEY (`movieID`)
  REFERENCES movie(`movieID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;