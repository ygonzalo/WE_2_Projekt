CREATE DATABASE IF NOT EXISTS filmsapp;

USE filmsapp;

--
-- Table structure for table `users_auth`
--

CREATE TABLE IF NOT EXISTS `users_auth` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(200) NOT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=187 ;
