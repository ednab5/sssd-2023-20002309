-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 29, 2023 at 10:28 AM
-- Server version: 10.10.2-MariaDB
-- PHP Version: 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_200023090`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `verification_status` int(11) DEFAULT NULL,
  `twofactorAuthStatus` int(11) DEFAULT NULL,
  `fullName` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `attempts` int(11) DEFAULT NULL,
  `loginAttempts` int(11) DEFAULT NULL,
  `secret` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_croatian_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userId`, `username`, `password`, `phone`, `verification_status`, `twofactorAuthStatus`, `fullName`, `email`, `attempts`, `loginAttempts`, `secret`) VALUES
(30, 'demo', 'Tesr3423434@*dfdf@@q', '435435345', 1, 1, 'demo', 'edna.bogdanic@stu.ibu.edu.ba', NULL, 0, 'V3IXM3XETR43LMV6'),
(31, 'test1', 'Aokvjsdfn@dfs847', '6345345345345435', 1, 0, 'test', 'edna.bogdanic@stu.ibu.edu.ba', NULL, 0, 'GTZ3H2NJLJNOFAF2'),
(32, 'testtest', '123456@dfskdmfjdsf78/*', '54353465544363', 0, 0, 'testtest', 'edna.bogdanic@stu.ibu.edu.ba', NULL, 0, 'ZDEXFWM6VE55AMEV');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
