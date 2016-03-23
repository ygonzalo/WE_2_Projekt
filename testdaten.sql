-- DELETE
DELETE FROM movie;
DELETE FROM movieInfo;
DELETE FROM user;
DELETE FROM movieList;
DELETE FROM friends;

-- movie TESTDATA
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1001', 10, 4, 16);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1002', 24, 4, 234);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1003', 435, 100, 345);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1004', 1, 1, 3);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1005', 104, 9, 45);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1006', 140, 70, 280);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1007', 45, 14, 14);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1008', 70, 16, 46);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1009', 89, 6, 22);
INSERT INTO movie(movieID,watchers,ratings,rating_points)
VALUES ('1010', 13, 4, 16);

-- movieInfo TESTDATA
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1001', 'ger', 'The plot from movie 001', 'movie 1', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1002', 'eng', 'The plot from movie 002', 'movie 2', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1003', 'ger', 'The plot from movie 003', 'movie 3', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1004', 'eng', 'The plot from movie 004', 'movie 4', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1005', 'ger', 'The plot from movie 005', 'movie 5', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1006', 'eng', 'The plot from movie 006', 'movie 6', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1007', 'ger', 'The plot from movie 007', 'movie 7', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1008', 'eng', 'The plot from movie 008', 'movie 8', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1009', 'ger', 'The plot from movie 009', 'movie 9', '2000-01-01','url');
INSERT INTO movieInfo(movieID,language,plot,title,release_date,poster)
VALUES ('1010', 'eng', 'The plot from movie 010', 'movie 10', '2000-01-01','url');

-- user TESTDATA
INSERT INTO user(userID,name,email,password,points)
VALUES (1,'user 1', 'user1@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 1 );
INSERT INTO user(userID,name,email,password,points)
VALUES (2,'user 2', 'user2@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 2 );
INSERT INTO user(userID,name,email,password,points)
VALUES (3,'user 3', 'user3@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 3 );
INSERT INTO user(userID,name,email,password,points)
VALUES (4,'user 4', 'user4@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 4 );
INSERT INTO user(userID,name,email,password,points)
VALUES (5,'user 5', 'user5@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 5 );
INSERT INTO user(userID,name,email,password,points)
VALUES (6,'user 6', 'user6@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 6 );
INSERT INTO user(userID,name,email,password,points)
VALUES (7,'user 7', 'user7@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 7 );
INSERT INTO user(userID,name,email,password,points)
VALUES (8,'user 8', 'user8@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 8 );
INSERT INTO user(userID,name,email,password,points)
VALUES (9,'user 9', 'user9@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 9 );
INSERT INTO user(userID,name,email,password,points)
VALUES (10,'user 10', 'user10@testdata.de', '$2y$10$i6K.TA1PrnqHeX.YPsGMLeWxpXF3gr80eYA3Vy.ZxBjvKP0eFGlkq', 10 );

-- movieList TESTDATA
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1001',1,1,'watched','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1002',2,2,'watchlist','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1003',3,3,'deleted','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1004',4,4,'watched','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1005',5,5,'watchlist','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1006',6,1,'deleted','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1007',7,2,'watched','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1008',8,3,'watchlist','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1009',9,4,'deleted','2000-01-01');
INSERT INTO movieList(movieID,userID,user_rating,status,watched_date)
VALUES ('1010',10,5,'watched','2000-01-01');

-- friends TESTDATA
INSERT INTO friends(userID,friendID,since,status)
VALUES (1,2,'2000-01-01','accepted');
INSERT INTO friends(userID,friendID,since,status)
VALUES (2,3,'2000-01-01','requested');
INSERT INTO friends(userID,friendID,since,status)
VALUES (3,4,'2000-01-01','denied');
INSERT INTO friends(userID,friendID,since,status)
VALUES (4,5,'2000-01-01','accepted');
INSERT INTO friends(userID,friendID,since,status)
VALUES (5,6,'2000-01-01','requested');
INSERT INTO friends(userID,friendID,since,status)
VALUES (6,7,'2000-01-01','denied');
INSERT INTO friends(userID,friendID,since,status)
VALUES (7,8,'2000-01-01','accepted');
INSERT INTO friends(userID,friendID,since,status)
VALUES (8,9,'2000-01-01','requested');
INSERT INTO friends(userID,friendID,since,status)
VALUES (9,10,'2000-01-01','denied');
INSERT INTO friends(userID,friendID,since,status)
VALUES (10,1,'2000-01-01','accepted');