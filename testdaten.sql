-- DELETE
DELETE FROM `movie`;
DELETE FROM `movieinfo`;
DELETE FROM `user`;
DELETE FROM `movielist`;
DELETE FROM `friends`;

-- movie TESTDATA
INSERT INTO `movie`(`movieID`,`watchers`,`ratings`,`rating_points`)
VALUES (177572, 3, 3, 6);
INSERT INTO `movie`(`movieID`,`watchers`,`ratings`,`rating_points`)
VALUES (293660, 2, 2, 5);


-- movieinfo TESTDATA
INSERT INTO `movieinfo`(`movieID`,`language`,`plot`,`title`,`release_date`,`poster`)
VALUES (177572, 'de', 'Hiro Hamada ist ein brillanter Teenager und lebt in der futuristischen Stadt San Fransokyo ein relativ normales Leben. Zumindest solange, bis ihm sein Bruder Tadashi den aufblasbaren Roboter Baymax baut, zu dem Hiro schnell eine ganz besondere Beziehung entwickelt. Baymax'' tatkräftige Unterstützung kann der aufgeweckte Junge definitiv gut gebrauchen, droht doch schon bald eine kriminelle Organisation damit, seine Heimatstadt zu zerstören. Mit der Hilfe von Baymax und seinen Freunden Go Go Tomago, Wasabi, Honey Lemon und Fred beschließt Hiro, alles zu tun, um die Katastrophe zu verhindern.',
'Baymax - Riesiges Robowabohu', '2014-10-22','https://image.tmdb.org/t/p/w185/16g5Yqa1PpgoMU4Uzvmy6zSJv0W.jpg');
INSERT INTO `movieinfo`(`movieID`,`language`,`plot`,`title`,`release_date`,`poster`)
VALUES (293660, 'de', 'Basierend auf Marvels unkonventionellstem Anti-Helden, erzählt DEADPOOL die Geschichte des ehemaligen Special Forces Soldaten und Söldners Wade Wilson, der - nachdem er sich einem skrupellosen Experiment unterzieht - unglaubliche Selbstheilungskräfte erlangt und sein Alter Ego Deadpool annimmt.  Mit schwarzem, schrägen Sinn für Humor und ausgestattet mit neuen Fähigkeiten begibt sich Deadpool auf die unerbittliche Jagd nach seinen Peinigern, die beinahe sein Leben zerstörten.',
'Deadpool', '2016-02-09','https://image.tmdb.org/t/p/w185/inVq3FRqcYIRl2la8iZikYYxFNR.jpg');


-- user TESTDATA
INSERT INTO `user`(`userID`,`name`,`email`,`password`,`points`)
VALUES (1,'test', 't@t.de', '$2y$10$LV8cO6nmKku0bEVJ6IALQuMx.fFkAq2.Wvz15VwlbPx9usDg7ofWS', 1 );
INSERT INTO `user`(`userID`,`name`,`email`,`password`,`points`)
VALUES (2,'test2', 'test2@t.de', '$2y$10$LV8cO6nmKku0bEVJ6IALQuMx.fFkAq2.Wvz15VwlbPx9usDg7ofWS', 2 );
INSERT INTO `user`(`userID`,`name`,`email`,`password`,`points`)
VALUES (3,'test3', 'test3@t.de', '$2y$10$LV8cO6nmKku0bEVJ6IALQuMx.fFkAq2.Wvz15VwlbPx9usDg7ofWS', 3 );
INSERT INTO `user`(`userID`,`name`,`email`,`password`,`points`)
VALUES (4,'test4', 'test4@t.de', '$2y$10$LV8cO6nmKku0bEVJ6IALQuMx.fFkAq2.Wvz15VwlbPx9usDg7ofWS', 4 );


-- movielist TESTDATA
INSERT INTO `movielist`(`movieID`,`userID`,`user_rating`,`status`,`watched_date`)
VALUES (177572,1,1,'watched','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`user_rating`,`status`,`watched_date`)
VALUES (177572,2,2,'watched','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`user_rating`,`status`,`watched_date`)
VALUES (177572,3,3,'watched','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`user_rating`,`status`,`watched_date`)
VALUES (177572,4,4,'watchlist','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`status`,`watched_date`)
VALUES (293660,1,'watchlist','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`status`,`watched_date`)
VALUES (293660,2,'watchlist','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`user_rating`,`status`,`watched_date`)
VALUES (293660,3,2,'watched','2000-01-01');
INSERT INTO `movielist`(`movieID`,`userID`,`user_rating`,`status`,`watched_date`)
VALUES (293660,4,3,'watched','2000-01-01');


-- friends TESTDATA
INSERT INTO `friends`(`userID`,`friendID`,`since`,`status`)
VALUES (1,2,'2000-01-01','accepted');
INSERT INTO `friends`(`userID`,`friendID`,`since`,`status`)
VALUES (2,3,'2000-01-01','requested');
INSERT INTO `friends`(`userID`,`friendID`,`since`,`status`)
VALUES (3,4,'2000-01-01','denied');