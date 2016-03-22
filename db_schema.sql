DROP SCHEMA IF EXISTS watchedthatmovie;
CREATE DATABASE IF NOT EXISTS watchedthatmovie;
CREATE SCHEMA IF NOT EXISTS watchedthatmovie DEFAULT CHARACTER SET UTF8;
USE watchedthatmovie;

DROP TABLE IF EXISTS movieInfo;
DROP TABLE IF EXISTS movieList;
DROP TABLE IF EXISTS friends;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS movie;

CREATE TABLE IF NOT EXISTS user (
  `userID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `points` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`userID`))
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS friends (
  `userID` INT UNSIGNED NOT NULL,
  `friendID` INT UNSIGNED NOT NULL,
  `since` DATE,
  `status` ENUM('requested','accepted','denied') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`userID`,`friendID`),
  CONSTRAINT fk_userID
      FOREIGN KEY (`userID`)
      REFERENCES user(`userID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_friendID
      FOREIGN KEY (`friendID`)
      REFERENCES user(`userID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS movie (
  `movieID` INT UNSIGNED NOT NULL,
  `original_title` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `watchers` INT UNSIGNED NOT NULL DEFAULT 0,
  `ratings` INT UNSIGNED NOT NULL DEFAULT 0,
  `rating_points` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`movieID`))
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS movieList (
  `movieID` INT UNSIGNED NOT NULL,
  `userID` INT UNSIGNED NOT NULL,
  `user_rating` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('watched','watchlist', 'deleted') NOT NULL,
  `watched_date` DATE NOT NULL,
  PRIMARY KEY (`movieID`,`userID`),
  CONSTRAINT fk_movieID_ml
      FOREIGN KEY (`movieID`)
      REFERENCES movie(`movieID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT fk_userID_ml
      FOREIGN KEY (`userID`)
      REFERENCES user(`userID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS movieInfo (
  `movieID` INT UNSIGNED NOT NULL,
  `language` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plot` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `title` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `release_date` DATE,
  `poster` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (movieID,language),
  CONSTRAINT fk_movieID_mi
      FOREIGN KEY (`movieID`)
      REFERENCES movie(`movieID`)
      ON DELETE CASCADE
      ON UPDATE CASCADE)
  ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
