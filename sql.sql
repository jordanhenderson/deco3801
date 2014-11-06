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

--
-- Dumping data for table `Assignments`
--

INSERT INTO `Assignments` (`AssignmentID`, `CourseID`, `AssignmentName`, `Weight`, `ReviewsNeeded`, `OpenTime`, `DueTime`, `ReviewsDue`, `Language`, `ReviewsAllocated`, `ResubmitAllowed`, `NumberTests`) VALUES
(00001, '2', 'Assignment 1', 12, 3, '2014-10-01 08:00:00', '2014-10-06 18:00:00', '2014-10-10 12:30:00', 'bash', 1, 1, 4),
(00002, '2', 'Practical 1', 5, 2, '2014-10-08 08:00:00', '2014-10-15 18:00:00', '2014-10-26 18:00:00', 'bash', 1, 0, 4),
(00003, '2', 'Assignment 2', 15, 3, '2014-10-10 06:30:00', '2014-10-17 20:00:00', '2014-10-30 16:15:00', 'bash', 1, 1, 4),
(00004, '2', 'Practical 2', 5, 2, '2014-10-15 08:00:00', '2014-10-29 12:00:00', '2014-11-04 10:30:00', 'bash', 1, 0, 4),
(00005, '2', 'Assignment 3', 20, 3, '2014-10-17 08:00:00', '2014-10-31 20:00:00', '2014-11-06 18:00:00', 'bash', 1, 1, 4),
(00006, '2', 'Practical 3', 5, 2, '2014-10-30 08:00:00', '2014-11-05 20:00:00', '2014-11-09 18:00:00', 'bash', 1, 0, 4);

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

--
-- Dumping data for table `Comment`
--

INSERT INTO `Comment` (`CommentID`, `QuestionID`, `StudentID`, `StudentName`, `Content`, `postdate`) VALUES
(00145, 00107, '7', 'Markus Firstius', '<p>asdf</p>\n', '2014-10-20 23:45:27'),
(00146, 00107, '7', 'Markus Firstius', '<p>asdf2</p>\n', '2014-10-20 23:48:06'),
(00147, 00107, '7', 'Markus Firstius', '<p>asdf2</p>\n', '2014-10-20 23:53:21'),
(00148, 00107, '7', 'Markus Firstius', '<p>asdf3</p>\n', '2014-10-20 23:48:08'),
(00150, 00107, '7', 'Markus Firstius', '<p>asdf5</p>\n', '2014-10-20 23:57:29'),
(00151, 00107, '7', 'Markus Firstius', '<p>asdf6</p>\n', '2014-10-21 00:00:06'),
(00172, 00115, '2', 'Admin User', '<pre>\nif(empty($lastpost)){\n                            echo &#39;No answers&#39;;\n                        }\n                        foreach ($lastpost as $last) {\n                            if(!$last-&gt;isValid()) continue;\n                            //Display last posts individually\n                            $last = &amp;$last-&gt;getRow();\n                                $CurrentTime = time();\n                                $date = date_create_from_format(&#39;Y-m-d G:i:s&#39;, $last[&#39;postdate&#39;]);\n                                $OpenTime = (int) date_format($date, &#39;U&#39;);\n                                $daysago = seconds2human($CurrentTime - $OpenTime);\n\n                                echo  $daysago.&quot; ago by &lt;strong&gt;&lt;br&gt;&quot;.$last[&#39;StudentName&#39;].&quot;&lt;/strong&gt;&quot;;\n                        }</pre>\n', '2014-10-21 05:47:56'),
(00173, 00115, '7', 'Markus Firstius', '<p>Here is my question response</p>\n\n<pre>\nif(empty($lastpost)){\n                            echo &#39;No answers&#39;;\n                        }\n                        foreach ($lastpost as $last) {\n                            if(!$last-&gt;isValid()) continue;\n                            //Display last posts individually\n                            $last = &amp;$last-&gt;getRow();\n                                $CurrentTime = time();\n                                $date = date_create_from_format(&#39;Y-m-d G:i:s&#39;, $last[&#39;postdate&#39;]);\n                                $OpenTime = (int) date_format($date, &#39;U&#39;);\n                                $daysago = seconds2human($CurrentTime - $OpenTime);\n\n                                echo  $daysago.&quot; ago by \n&quot;.$last[&#39;StudentName&#39;].&quot;&quot;;\n                        }</pre>\n', '2014-10-21 11:55:15'),
(00177, 00119, '2', 'Admin User', '<p>Response</p>\n\n<p>&nbsp;</p>\n', '2014-10-22 14:11:32'),
(00179, 00120, '8', 'Julianas Secondus', '<p>Test</p>\n', '2014-10-22 19:42:56'),
(00180, 00115, '8', 'Julianas Secondus', '<p>dfdf</p>\n', '2014-10-22 19:45:32'),
(00182, 00126, '8', 'Julianas Secondus', '<p>fgjgfhfgh</p>\n', '2014-10-27 15:28:16'),
(00183, 00127, '8', 'Julianas Secondus', '<p>sadfsadf</p>\n', '2014-10-27 15:36:37'),
(00186, 00128, '7', 'Markus Firstius', '<p>lk</p>\n', '2014-10-27 16:23:48'),
(00187, 00130, '7', 'Markus Firstius', '<p>this is a reply</p>\n', '2014-10-27 17:01:00'),
(00188, 00131, '7', 'Markus Firstius', '<p>reply</p>\n', '2014-10-27 17:30:39'),
(00189, 00120, '2', 'Admin User', '<pre>\njkbhjk</pre>\n', '2014-10-27 17:31:00'),
(00190, 00128, '2', 'Admin User', 'rgwgrhrthh', '2014-10-27 17:36:01'),
(00191, 00128, '2', 'Admin User', '<pre>\nthis is a comment</pre>\n', '2014-10-27 17:36:14');

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

--
-- Dumping data for table `Course`
--

INSERT INTO `Course` (`CourseID`, `HelpEnabled`) VALUES
('0', 0),
('1', 1),
('2', 1),
('3', 0),
('4', 0);

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

--
-- Dumping data for table `Question`
--

INSERT INTO `Question` (`QuestionID`, `StudentID`, `CourseID`, `StudentName`, `Opendate`, `Title`, `Content`, `Status`) VALUES
(00050, '2', '4', 'Admin User', '2014-09-15 02:40:50', 'Test', 'Question', 0),
(00091, '', '', '', '2014-10-20 05:13:38', '', '', 0),
(00092, '', '', '', '2014-10-20 05:12:56', '', '', 0),
(00093, '', '', '', '2014-10-20 05:12:45', '', '', 0),
(00107, '', '', '', '2014-10-20 13:30:16', '', '', 0),
(00115, '2', '2', 'Admin User', '2014-10-21 05:47:07', 'Test', '<p>test</p>\n', 1),
(00119, '2', '2', 'Admin User', '2014-10-22 14:11:28', 'Test', '<p>test</p>\n', 0),
(00120, '7', '2', 'Markus Firstius', '2014-10-22 16:15:46', 'student question', '<p>asdf</p>\n', 1),
(00121, '7', '2', 'Markus Firstius', '2014-10-22 16:16:14', 'question 1', '<p>what is the answer?</p>\n', 0),
(00122, '8', '2', 'Julianas Secondus', '2014-10-22 19:44:47', 'Comment', '<p>haha</p>\n', 0),
(00123, '8', '2', 'Julianas Secondus', '2014-10-22 19:45:24', 'test', '<p>dsdfs</p>\n', 0),
(00126, '8', '2', 'Julianas Secondus', '2014-10-27 15:27:59', 'question', '<p>hdfsdfdsa</p>\n', 1),
(00127, '8', '2', 'Julianas Secondus', '2014-10-27 15:36:18', 'test quetion', '<p>dsfdsafsd</p>\n', 1),
(00128, '7', '2', 'Markus Firstius', '2014-10-27 16:00:12', 'test', '<pre>\nfor int i = 0</pre>\n', 0),
(00129, '7', '2', 'Markus Firstius', '2014-10-27 16:43:39', ',oyhihg.i,h.u', '<p>u.uv ,uhti,ft&nbsp;</p>\n', 1),
(00130, '7', '2', 'Markus Firstius', '2014-10-27 17:00:31', 'another question', '<pre>\nquestion</pre>\n', 1),
(00131, '7', '2', 'Markus Firstius', '2014-10-27 17:30:24', 'this is a question', '<p>content</p>\n', 1);

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
