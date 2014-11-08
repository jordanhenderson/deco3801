-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 06, 2014 at 11:18 PM
-- Server version: 5.5.33
-- PHP Version: 5.4.20

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `deco3801`
--

-- --------------------------------------------------------

--
-- Table structure for table `Assignments`
--

DROP TABLE IF EXISTS `Assignments`;
CREATE TABLE IF NOT EXISTS `Assignments` (
  `AssignmentID` smallint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `CourseID` varchar(32) NOT NULL,
  `AssignmentName` tinytext NOT NULL,
  `Weight` tinyint(3) unsigned NOT NULL,
  `ReviewsNeeded` tinyint(3) unsigned NOT NULL DEFAULT '3',
  `OpenTime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `DueTime` timestamp NULL DEFAULT NULL,
  `ReviewsDue` timestamp NULL DEFAULT NULL,
  `Language` text NOT NULL,
  `ReviewsAllocated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Boolean',
  `ResubmitAllowed` tinyint(3) DEFAULT '1',
  `NumberTests` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`AssignmentID`),
  KEY `CourseID` (`CourseID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;

-- --------------------------------------------------------

--
-- Table structure for table `Comment`
--

DROP TABLE IF EXISTS `Comment`;
CREATE TABLE IF NOT EXISTS `Comment` (
  `CommentID` smallint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `QuestionID` smallint(5) unsigned zerofill NOT NULL,
  `StudentID` varchar(32) NOT NULL,
  `StudentName` varchar(32) NOT NULL,
  `Content` text NOT NULL,
  `postdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CommentID`),
  KEY `QuestionID` (`QuestionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=192 ;

-- --------------------------------------------------------

--
-- Table structure for table `Course`
--

DROP TABLE IF EXISTS `Course`;
CREATE TABLE IF NOT EXISTS `Course` (
  `CourseID` varchar(32) NOT NULL,
  `HelpEnabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`CourseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Files`
--

DROP TABLE IF EXISTS `Files`;
CREATE TABLE IF NOT EXISTS `Files` (
  `FileID` smallint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `SubmissionID` smallint(5) unsigned zerofill NOT NULL,
  `FileName` text NOT NULL,
  PRIMARY KEY (`FileID`),
  KEY `SubmissionID` (`SubmissionID`),
  KEY `SubmissionID_2` (`SubmissionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `Question`
--

DROP TABLE IF EXISTS `Question`;
CREATE TABLE IF NOT EXISTS `Question` (
  `QuestionID` smallint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `StudentID` varchar(32) NOT NULL,
  `CourseID` varchar(32) NOT NULL,
  `StudentName` varchar(32) NOT NULL,
  `Opendate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Title` text NOT NULL,
  `Content` text NOT NULL,
  `Status` tinyint(4) NOT NULL,
  PRIMARY KEY (`QuestionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=132 ;

-- --------------------------------------------------------

--
-- Table structure for table `Review`
--

DROP TABLE IF EXISTS `Review`;
CREATE TABLE IF NOT EXISTS `Review` (
  `ReviewID` smallint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `SubmissionID` smallint(5) unsigned zerofill NOT NULL,
  `FileID` smallint(5) unsigned zerofill DEFAULT NULL,
  `ReviewerID` varchar(32) NOT NULL,
  `Comments` text NOT NULL,
  `startIndex` int(11) NOT NULL,
  `startLine` int(11) NOT NULL,
  `text` text NOT NULL,
  `Submitted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true, author cannot edit, and the reciever can view it.',
  PRIMARY KEY (`ReviewID`),
  UNIQUE KEY `FileID` (`FileID`),
  KEY `SubmissionID` (`SubmissionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=309 ;

-- --------------------------------------------------------

--
-- Table structure for table `Submission`
--

DROP TABLE IF EXISTS `Submission`;
CREATE TABLE IF NOT EXISTS `Submission` (
  `SubmissionID` smallint(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `AssignmentID` smallint(5) unsigned zerofill NOT NULL,
  `StudentID` varchar(32) NOT NULL,
  `Results` text NOT NULL,
  `SubmitTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SubmissionID`),
  KEY `AssignmentID` (`AssignmentID`,`StudentID`) COMMENT 'Students cannot have multiple submissions. A new submission will overwrite the old one.'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Comment`
--
ALTER TABLE `Comment`
  ADD CONSTRAINT `Comment_Question` FOREIGN KEY (`QuestionID`) REFERENCES `Question` (`QuestionID`) ON DELETE CASCADE;

--
-- Constraints for table `Files`
--
ALTER TABLE `Files`
  ADD CONSTRAINT `Files_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `Submission` (`SubmissionID`) ON DELETE CASCADE;

--
-- Constraints for table `Review`
--
ALTER TABLE `Review`
  ADD CONSTRAINT `Review_ibfk_2` FOREIGN KEY (`FileID`) REFERENCES `Files` (`FileID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `Submission` (`SubmissionID`) ON DELETE CASCADE;

--
-- Constraints for table `Submission`
--
ALTER TABLE `Submission`
  ADD CONSTRAINT `Submission_ibfk_2` FOREIGN KEY (`AssignmentID`) REFERENCES `Assignments` (`AssignmentID`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
