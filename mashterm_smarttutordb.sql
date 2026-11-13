-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2026 at 02:39 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mashterm_smarttutordb`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Id` int(11) NOT NULL,
  `GroupId` int(11) NOT NULL,
  `Name` varchar(80) NOT NULL,
  `Slug` varchar(120) NOT NULL,
  `Icon` varchar(60) DEFAULT NULL,
  `ImageUrl` varchar(500) DEFAULT NULL,
  `BannerUrl` varchar(500) DEFAULT NULL,
  `Highlights` text DEFAULT NULL,
  `About` mediumtext DEFAULT NULL,
  `WhyChoose` mediumtext DEFAULT NULL,
  `MatchType` varchar(10) NOT NULL DEFAULT 'class',
  `Keywords` varchar(500) DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `SortOrder` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`Id`, `GroupId`, `Name`, `Slug`, `Icon`, `ImageUrl`, `BannerUrl`, `Highlights`, `About`, `WhyChoose`, `MatchType`, `Keywords`, `IsActive`, `SortOrder`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 1, 'Pre-Schooling', 'pre-schooling', 'fa-child-reaching', NULL, NULL, NULL, NULL, NULL, 'class', 'Play / Nursery, Pre-Schooling', 1, 1, '2026-11-08 10:31:37', NULL),
(2, 1, 'Play', 'play', 'fa-cubes', NULL, NULL, NULL, NULL, NULL, 'class', 'Play / Nursery, Play', 1, 2, '2026-11-08 10:31:37', NULL),
(3, 1, 'Nursery', 'nursery', 'fa-baby', NULL, NULL, NULL, NULL, NULL, 'class', 'Play / Nursery, Nursery', 1, 3, '2026-11-08 10:31:37', NULL),
(4, 1, 'KG', 'kg', 'fa-shapes', NULL, NULL, NULL, NULL, NULL, 'class', 'KG', 1, 4, '2026-11-08 10:31:37', NULL),
(5, 1, 'Standard 1', 'standard-1', 'fa-pencil', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 1, Standard 1', 1, 5, '2026-11-08 10:31:37', NULL),
(6, 1, 'Standard 2', 'standard-2', 'fa-book-open', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 2, Standard 2', 1, 6, '2026-11-08 10:31:37', NULL),
(7, 1, 'Standard 3', 'standard-3', 'fa-shapes', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 3, Standard 3', 1, 7, '2026-11-08 10:31:37', NULL),
(8, 1, 'Standard 4', 'standard-4', 'fa-ruler', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 4, Standard 4', 1, 8, '2026-11-08 10:31:37', NULL),
(9, 1, 'Standard 5', 'standard-5', 'fa-flask', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 5, Standard 5', 1, 9, '2026-11-08 10:31:37', NULL),
(10, 1, 'Standard 6', 'standard-6', 'fa-globe', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 6, Standard 6', 1, 10, '2026-11-08 10:31:37', NULL),
(11, 1, 'Standard 7', 'standard-7', 'fa-calculator', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 7, Standard 7', 1, 11, '2026-11-08 10:31:37', NULL),
(12, 1, 'Standard 8', 'standard-8', 'fa-microscope', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 8, Standard 8', 1, 12, '2026-11-08 10:31:37', NULL),
(13, 1, 'Standard 9', 'standard-9', 'fa-atom', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 9, Standard 9', 1, 13, '2026-11-08 10:31:37', NULL),
(14, 1, 'O Level', 'o-level', 'fa-graduation-cap', NULL, NULL, NULL, NULL, NULL, 'class', 'O Level', 1, 14, '2026-11-08 10:31:37', NULL),
(15, 1, 'A Level ( AS )', 'a-level-as', 'fa-user-graduate', NULL, NULL, NULL, NULL, NULL, 'class', 'A Level, AS Level', 1, 15, '2026-11-08 10:31:37', NULL),
(16, 1, 'A Level ( A2 )', 'a-level-a2', 'fa-user-graduate', NULL, NULL, NULL, NULL, NULL, 'class', 'A Level, A2 Level', 1, 16, '2026-11-08 10:31:37', NULL),
(17, 2, 'Pre-Schooling', 'bangla-medium-pre-schooling', 'fa-child-reaching', NULL, NULL, NULL, NULL, NULL, 'class', 'Play / Nursery, Pre-Schooling', 1, 1, '2026-11-08 10:31:37', NULL),
(18, 2, 'Play', 'bangla-medium-play', 'fa-cubes', NULL, NULL, NULL, NULL, NULL, 'class', 'Play / Nursery, Play', 1, 2, '2026-11-08 10:31:37', NULL),
(19, 2, 'Nursery', 'bangla-medium-nursery', 'fa-baby', NULL, NULL, NULL, NULL, NULL, 'class', 'Play / Nursery, Nursery', 1, 3, '2026-11-08 10:31:37', NULL),
(20, 2, 'KG', 'bangla-medium-kg', 'fa-shapes', NULL, NULL, NULL, NULL, NULL, 'class', 'KG', 1, 4, '2026-11-08 10:31:37', NULL),
(21, 2, 'Class 1', 'class-1', 'fa-pencil', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 1', 1, 5, '2026-11-08 10:31:37', NULL),
(22, 2, 'Class 2', 'class-2', 'fa-book-open', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 2', 1, 6, '2026-11-08 10:31:37', NULL),
(23, 2, 'Class 3', 'class-3', 'fa-shapes', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 3', 1, 7, '2026-11-08 10:31:37', NULL),
(24, 2, 'Class 4', 'class-4', 'fa-ruler', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 4', 1, 8, '2026-11-08 10:31:37', NULL),
(25, 2, 'Class 5', 'class-5', 'fa-flask', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 5', 1, 9, '2026-11-08 10:31:37', NULL),
(26, 2, 'Class 6', 'class-6', 'fa-globe', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 6', 1, 10, '2026-11-08 10:31:37', NULL),
(27, 2, 'Class 7', 'class-7', 'fa-calculator', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 7', 1, 11, '2026-11-08 10:31:37', NULL),
(28, 2, 'Class 8', 'class-8', 'fa-microscope', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 8', 1, 12, '2026-11-08 10:31:37', NULL),
(29, 2, 'Class 9', 'class-9', 'fa-atom', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 9', 1, 13, '2026-11-08 10:31:37', NULL),
(30, 2, 'Class 10', 'class-10', 'fa-school', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 10', 1, 14, '2026-11-08 10:31:37', NULL),
(31, 2, 'HSC 1st Year', 'hsc-1st-year', 'fa-building-columns', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 11, HSC', 1, 15, '2026-11-08 10:31:37', NULL),
(32, 2, 'HSC 2nd Year', 'hsc-2nd-year', 'fa-building-columns', NULL, NULL, NULL, NULL, NULL, 'class', 'Class 12, HSC', 1, 16, '2026-11-08 10:31:37', NULL),
(33, 3, 'Crafting', 'crafting', 'fa-scissors', NULL, NULL, NULL, NULL, NULL, 'subject', 'Crafting, Arts & Crafts', 1, 1, '2026-11-08 10:31:37', NULL),
(34, 3, 'Dance', 'dance', 'fa-person-running', NULL, NULL, NULL, NULL, NULL, 'subject', 'Dance', 1, 2, '2026-11-08 10:31:37', NULL),
(35, 3, 'Instrumental Music', 'instrumental-music', 'fa-drum', NULL, NULL, NULL, NULL, NULL, 'subject', 'Instrumental Music, Music', 1, 3, '2026-11-08 10:31:37', NULL),
(36, 3, 'Music', 'music', 'fa-music', NULL, NULL, NULL, NULL, NULL, 'subject', 'Music, Singing', 1, 4, '2026-11-08 10:31:37', NULL),
(37, 3, 'Drawing & Painting', 'drawing-and-painting', 'fa-palette', NULL, NULL, NULL, NULL, NULL, 'subject', 'Drawing, Painting, Drawing & Painting', 1, 5, '2026-11-08 10:31:37', NULL),
(38, 3, 'Handwriting', 'handwriting', 'fa-pen-nib', NULL, NULL, NULL, NULL, NULL, 'subject', 'Handwriting', 1, 6, '2026-11-08 10:31:37', NULL),
(39, 3, 'Guitar', 'guitar', 'fa-guitar', NULL, NULL, NULL, NULL, NULL, 'subject', 'Guitar', 1, 7, '2026-11-08 10:31:37', NULL),
(40, 3, 'Make Up Tutoring', 'make-up-tutoring', 'fa-wand-magic-sparkles', NULL, NULL, NULL, NULL, NULL, 'subject', 'Make Up, Makeup', 1, 8, '2026-11-08 10:31:37', NULL),
(41, 3, 'Miming', 'miming', 'fa-masks-theater', NULL, NULL, NULL, NULL, NULL, 'subject', 'Miming, Mime', 1, 9, '2026-11-08 10:31:37', NULL),
(42, 3, 'Film Direction', 'film-direction', 'fa-film', NULL, NULL, NULL, NULL, NULL, 'subject', 'Film Direction, Film Making', 1, 10, '2026-11-08 10:31:37', NULL),
(43, 3, 'Reciting', 'reciting', 'fa-microphone', NULL, NULL, NULL, NULL, NULL, 'subject', 'Recitation, Reciting', 1, 11, '2026-11-08 10:31:37', NULL),
(44, 3, 'Acting & Modeling', 'acting-and-modeling', 'fa-star', NULL, NULL, NULL, NULL, NULL, 'subject', 'Acting, Modeling', 1, 12, '2026-11-08 10:31:37', NULL),
(45, 4, 'Islamic Studies', 'islamic-studies', 'fa-moon', NULL, NULL, NULL, NULL, NULL, 'subject', 'Islam Studies, Islamic Studies, Quran, Arabic', 1, 1, '2026-11-08 10:31:37', NULL),
(46, 4, 'Hinduism Studies', 'hinduism-studies', 'fa-om', NULL, NULL, NULL, NULL, NULL, 'subject', 'Hindu Religion, Hinduism', 1, 2, '2026-11-08 10:31:37', NULL),
(47, 4, 'Buddhism Studies', 'buddhism-studies', 'fa-dharmachakra', NULL, NULL, NULL, NULL, NULL, 'subject', 'Buddhism, Buddhist Religion', 1, 3, '2026-11-08 10:31:37', NULL),
(48, 4, 'Christianity Studies', 'christianity-studies', 'fa-cross', NULL, NULL, NULL, NULL, NULL, 'subject', 'Christianity, Christian Religion', 1, 4, '2026-11-08 10:31:37', NULL),
(49, 5, 'Public University Admission', 'public-university-admission', 'fa-building-columns', NULL, NULL, NULL, NULL, NULL, 'class', 'University Admission, Public University Admission', 1, 1, '2026-11-08 10:31:37', NULL),
(50, 5, 'Private University Admission', 'private-university-admission', 'fa-building', NULL, NULL, NULL, NULL, NULL, 'class', 'University Admission, Private University Admission', 1, 2, '2026-11-08 10:31:37', NULL),
(51, 5, 'Medical College Admission', 'medical-college-admission', 'fa-user-doctor', NULL, NULL, NULL, NULL, NULL, 'class', 'Medical Admission', 1, 3, '2026-11-08 10:31:37', NULL),
(52, 5, 'Engineering University Admission', 'engineering-university-admission', 'fa-gears', NULL, NULL, NULL, NULL, NULL, 'class', 'Engineering Admission', 1, 4, '2026-11-08 10:31:37', NULL),
(53, 5, 'School Admission Test', 'school-admission-test', 'fa-school', NULL, NULL, NULL, NULL, NULL, 'class', 'School Admission', 1, 5, '2026-11-08 10:31:37', NULL),
(54, 5, 'IBA Admission Test', 'iba-admission-test', 'fa-chart-line', NULL, NULL, NULL, NULL, NULL, 'class', 'IBA Admission, IBA', 1, 6, '2026-11-08 10:31:37', NULL),
(55, 5, 'MBA Admission Help', 'mba-admission-help', 'fa-briefcase', NULL, NULL, NULL, NULL, NULL, 'class', 'MBA Admission, MBA', 1, 7, '2026-11-08 10:31:37', NULL),
(56, 5, 'Cadet Admission Help', 'cadet-admission-help', 'fa-person-military-rifle', NULL, NULL, NULL, NULL, NULL, 'class', 'Cadet Admission, Cadet College', 1, 8, '2026-11-08 10:31:37', NULL),
(57, 6, 'IELTS', 'ielts', 'fa-language', NULL, NULL, NULL, NULL, NULL, 'class', 'IELTS', 1, 1, '2026-11-08 10:31:37', NULL),
(58, 6, 'TOEFL', 'toefl', 'fa-comments', NULL, NULL, NULL, NULL, NULL, 'class', 'TOEFL', 1, 2, '2026-11-08 10:31:37', NULL),
(59, 6, 'GMAT', 'gmat', 'fa-chart-pie', NULL, NULL, NULL, NULL, NULL, 'class', 'GMAT', 1, 3, '2026-11-08 10:31:37', NULL),
(60, 6, 'GRE', 'gre', 'fa-award', NULL, NULL, NULL, NULL, NULL, 'class', 'GRE', 1, 4, '2026-11-08 10:31:37', NULL),
(61, 6, 'SAT', 'sat', 'fa-file-pen', NULL, NULL, NULL, NULL, NULL, 'class', 'SAT', 1, 5, '2026-11-08 10:31:37', NULL),
(62, 6, 'GED', 'ged', 'fa-certificate', NULL, NULL, NULL, NULL, NULL, 'class', 'GED', 1, 6, '2026-11-08 10:31:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categorygroups`
--

CREATE TABLE `categorygroups` (
  `Id` int(11) NOT NULL,
  `Name` varchar(80) NOT NULL,
  `SortOrder` int(11) NOT NULL DEFAULT 0,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorygroups`
--

INSERT INTO `categorygroups` (`Id`, `Name`, `SortOrder`, `IsActive`, `CreatedAt`) VALUES
(1, 'English Medium', 1, 1, '2026-11-08 10:31:37'),
(2, 'Bangla Medium', 2, 1, '2026-11-08 10:31:37'),
(3, 'Arts & Crafts', 3, 1, '2026-11-08 10:31:37'),
(4, 'Religious & Moral Studies', 4, 1, '2026-11-08 10:31:37'),
(5, 'Admission Help', 5, 1, '2026-11-08 10:31:37'),
(6, 'Int. Exam Preparation', 6, 1, '2026-11-08 10:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `chatclasses`
--

CREATE TABLE `chatclasses` (
  `Id` int(11) NOT NULL,
  `Kind` varchar(8) NOT NULL,
  `AppId` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Link` varchar(500) NOT NULL,
  `Platform` varchar(20) NOT NULL,
  `StartsAt` datetime NOT NULL,
  `DurationMin` int(11) NOT NULL DEFAULT 60,
  `Status` varchar(20) NOT NULL DEFAULT 'Scheduled',
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatdirect`
--

CREATE TABLE `chatdirect` (
  `Id` int(11) NOT NULL,
  `StudentId` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatgroupmembers`
--

CREATE TABLE `chatgroupmembers` (
  `GroupId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `LastReadId` int(11) NOT NULL DEFAULT 0,
  `AddedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatgroups`
--

CREATE TABLE `chatgroups` (
  `Id` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `Name` varchar(80) NOT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatmessages`
--

CREATE TABLE `chatmessages` (
  `Id` int(11) NOT NULL,
  `Kind` varchar(8) NOT NULL DEFAULT 'app',
  `AppId` int(11) NOT NULL,
  `SenderId` int(11) NOT NULL,
  `Body` text NOT NULL,
  `CreatedAt` datetime NOT NULL,
  `ReadAt` datetime DEFAULT NULL,
  `ClassId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `Id` int(11) NOT NULL,
  `Code` varchar(30) NOT NULL,
  `Audience` varchar(10) NOT NULL,
  `DurationMonths` int(11) NOT NULL DEFAULT 0,
  `Price` int(11) NOT NULL,
  `EndsAt` datetime DEFAULT NULL,
  `MaxUses` int(11) NOT NULL DEFAULT 0,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `Note` varchar(200) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`Id`, `Code`, `Audience`, `DurationMonths`, `Price`, `EndsAt`, `MaxUses`, `IsActive`, `Note`, `CreatedAt`) VALUES
(1, 'TEACHER500', 'Teacher', 6, 500, NULL, 0, 1, 'স্পেশাল অফার — টিচার ৬ মাস ৳500', '2026-11-12 16:54:37'),
(2, 'STUDENT200', 'Student', 6, 200, NULL, 0, 1, 'স্পেশাল অফার — শিক্ষার্থী ৬ মাস ৳200', '2026-11-12 16:54:37');

-- --------------------------------------------------------

--
-- Table structure for table `edupostcomments`
--

CREATE TABLE `edupostcomments` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edupostlikes`
--

CREATE TABLE `edupostlikes` (
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eduposts`
--

CREATE TABLE `eduposts` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text DEFAULT NULL,
  `Subject` varchar(60) DEFAULT NULL,
  `Media` text DEFAULT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'Pending',
  `RejectReason` varchar(300) DEFAULT NULL,
  `ReviewedBy` int(11) DEFAULT NULL,
  `ReviewedAt` datetime DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `ApprovedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emailotps`
--

CREATE TABLE `emailotps` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CodeHash` char(64) NOT NULL,
  `ExpiresAt` datetime NOT NULL,
  `Tries` int(11) NOT NULL DEFAULT 0,
  `UsedAt` datetime DEFAULT NULL,
  `Ip` varchar(64) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `Id` int(11) NOT NULL,
  `RequesterId` int(11) NOT NULL,
  `AddresseeId` int(11) NOT NULL,
  `UserLow` int(11) NOT NULL,
  `UserHigh` int(11) NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `RespondedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goaltasks`
--

CREATE TABLE `goaltasks` (
  `Id` int(11) NOT NULL,
  `GoalId` int(11) NOT NULL,
  `Title` varchar(150) NOT NULL,
  `IsDone` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `helpdeskcomments`
--

CREATE TABLE `helpdeskcomments` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `MediaUrl` varchar(500) DEFAULT NULL,
  `MediaType` varchar(20) DEFAULT NULL,
  `IsAdminReply` tinyint(1) NOT NULL DEFAULT 0,
  `ParentCommentId` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `helpdeskposts`
--

CREATE TABLE `helpdeskposts` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Title` varchar(300) NOT NULL,
  `Content` text NOT NULL,
  `MediaUrl` varchar(500) DEFAULT NULL,
  `MediaType` varchar(20) DEFAULT NULL,
  `IsAdminPost` tinyint(1) NOT NULL DEFAULT 0,
  `IsAcknowledged` tinyint(1) NOT NULL DEFAULT 0,
  `Status` varchar(20) NOT NULL DEFAULT 'Open',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kycrequests`
--

CREATE TABLE `kycrequests` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Kind` varchar(10) NOT NULL,
  `Status` varchar(12) NOT NULL DEFAULT 'Pending',
  `Data` text DEFAULT NULL,
  `FileFront` varchar(120) DEFAULT NULL,
  `FileBack` varchar(120) DEFAULT NULL,
  `TeacherRequestId` int(11) DEFAULT NULL,
  `AdminNote` varchar(500) DEFAULT NULL,
  `ReviewedBy` int(11) DEFAULT NULL,
  `ReviewedAt` datetime DEFAULT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `Id` int(11) NOT NULL,
  `Title` varchar(500) NOT NULL DEFAULT '',
  `Content` mediumtext NOT NULL,
  `Category` varchar(50) NOT NULL DEFAULT 'News',
  `ImageUrl` varchar(500) NOT NULL DEFAULT '',
  `PublishedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `IsAnnouncement` tinyint(1) NOT NULL DEFAULT 0,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`Id`, `Title`, `Content`, `Category`, `ImageUrl`, `PublishedDate`, `IsAnnouncement`, `IsActive`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Ut sit proident nec', 'Deleniti quae est c', 'Workshop', 'Odio error quaerat r', '2026-10-27 15:55:57', 0, 0, '2026-10-27 15:55:57', NULL),
(3, 'শিক্ষক খোঁজা হোক সহজ, শেখার পথ হোক সুন্দর!', 'সঠিক শিক্ষকের হাত ধরেই শুরু হতে পারে সুন্দর আগামীর পথচলা। \r\n\r\nশিক্ষার্থী ও অভিভাবকদের প্রয়োজন অনুযায়ী উপযুক্ত শিক্ষক খুঁজে পাওয়ার প্রক্রিয়াকে আরও সহজ, স্মার্ট ও নির্ভরযোগ্য করতে আসছে মাষ্টারমশাই।\r\n\r\nযেখানে শিক্ষার্থী ও শিক্ষকের মধ্যে তৈরি হবে সঠিক সংযোগ, আর শেখার প্রতিটি ধাপ হবে আরও সুন্দর।\r\n\r\nমাষ্টারমশাই — সঠিক শিক্ষক, সুন্দর ভবিষ্যৎ।', 'News', '/uploads/notices/notice_639287596040915570.png', '2026-10-28 00:00:00', 0, 1, '2026-10-28 10:46:45', NULL),
(5, 'শিক্ষক খোঁজা হোক সহজ, শেখার পথ হোক সুন্দর!', 'সঠিক শিক্ষকের হাত ধরেই শুরু হতে পারে সুন্দর আগামীর পথচলা। 🎓\r\n\r\nশিক্ষার্থী ও অভিভাবকদের প্রয়োজন অনুযায়ী উপযুক্ত শিক্ষক খুঁজে পাওয়ার প্রক্রিয়াকে আরও সহজ, স্মার্ট ও নির্ভরযোগ্য করতে আসছে মাষ্টারমশাই।\r\n\r\nযেখানে শিক্ষার্থী ও শিক্ষকের মধ্যে তৈরি হবে সঠিক সংযোগ, আর শেখার প্রতিটি ধাপ হবে আরও সুন্দর।\r\n\r\n✨ মাষ্টারমশাই — সঠিক শিক্ষক, সুন্দর ভবিষ্যৎ।', 'Event', '', '2026-10-28 00:00:00', 1, 1, '2026-10-28 10:48:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Title` varchar(300) DEFAULT NULL,
  `Message` text DEFAULT NULL,
  `Type` varchar(20) NOT NULL DEFAULT 'info',
  `Icon` varchar(50) NOT NULL DEFAULT 'fa-bell',
  `Link` varchar(500) DEFAULT NULL,
  `IsRead` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`Id`, `UserId`, `Title`, `Message`, `Type`, `Icon`, `Link`, `IsRead`, `CreatedAt`) VALUES
(1, 1, 'New Review ⭐', 'Md Ebtesham Azam একটি রিভিউ দিয়েছেন — Approve করুন।', 'info', 'fa-star', '/Admin/Reviews?status=Pending', 0, '2026-10-28 12:51:01'),
(2, 3, 'Review Approved 🎉', 'আপনার রিভিউ এখন হোম পেজে দেখানো হচ্ছে। ধন্যবাদ!', 'success', 'fa-star', '/User/Dashboard?section=review', 0, '2026-10-28 12:51:35'),
(3, 4, 'Account Suspended', 'আপনার অ্যাকাউন্ট 29 Oct 2026, 01:15 PM পর্যন্ত Suspend করা হয়েছে। কারণ: emni', 'danger', 'fa-user-slash', NULL, 0, '2026-10-28 13:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `Id` int(11) NOT NULL,
  `Audience` varchar(10) NOT NULL,
  `Name` varchar(80) NOT NULL,
  `DurationMonths` int(11) NOT NULL DEFAULT 0,
  `PostLimit` int(11) NOT NULL DEFAULT 0,
  `Price` int(11) NOT NULL DEFAULT 0,
  `RegularPrice` int(11) DEFAULT NULL,
  `Badge` varchar(40) DEFAULT NULL,
  `Description` varchar(300) DEFAULT NULL,
  `Features` text DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `IsFeatured` tinyint(1) NOT NULL DEFAULT 0,
  `SortOrder` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL,
  `IsLive` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`Id`, `Audience`, `Name`, `DurationMonths`, `PostLimit`, `Price`, `RegularPrice`, `Badge`, `Description`, `Features`, `IsActive`, `IsFeatured`, `SortOrder`, `CreatedAt`, `UpdatedAt`, `IsLive`) VALUES
(1, 'Teacher', '১ মাস', 1, 0, 100, 1000, '90% ছাড়', 'শুরু করার জন্য সবচেয়ে সহজ', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 7, '2026-11-08 10:31:37', NULL, 0),
(2, 'Teacher', '৩ মাস', 3, 0, 300, NULL, NULL, 'নিয়মিত টিউশনের জন্য', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 8, '2026-11-08 10:31:37', NULL, 0),
(3, 'Teacher', '৬ মাস', 6, 0, 500, NULL, 'জনপ্রিয়', 'কম খরচে বেশি সময়', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 9, '2026-11-08 10:31:37', NULL, 0),
(4, 'Teacher', '১২ মাস', 12, 0, 1000, NULL, 'সেরা মূল্য', 'পুরো বছর নিশ্চিন্ত', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 11, '2026-11-08 10:31:37', NULL, 0),
(5, 'Student', '১ মাস', 1, 0, 50, NULL, NULL, 'টিচারের সাথে যোগাযোগ আর টিউশন পোস্ট — ১ মাস', 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন', 0, 0, 4, '2026-11-08 10:31:37', NULL, 0),
(6, 'Student', '৩ মাস', 3, 0, 100, 150, 'সেরা মূল্য', 'কম খরচে বেশি সময় — ৩ মাস', 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন', 0, 0, 5, '2026-11-08 10:31:37', NULL, 0),
(7, 'Teacher', 'Premium ১ মাস', 1, 0, 200, NULL, 'লাইভ ক্লাস', 'সব সুবিধা + সাইট থেকে লাইভ ক্লাস — ১ মাস', 'সাইট থেকেই সরাসরি লাইভ ক্লাস — Zoom / Google Meet লাগবে না\nবৃষ্টি, বন্যা বা যেতে না পারলে শিক্ষার্থীকে অনলাইনে পড়ান\nচ্যাট থেকে এক ক্লিকে ক্লাস শুরু — শিক্ষার্থী Join বাটন পাবে\nFind Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 5, '2026-11-12 16:54:37', NULL, 1),
(8, 'Teacher', 'Premium ৩ মাস', 3, 0, 500, 600, 'লাইভ ক্লাস', 'সব সুবিধা + সাইট থেকে লাইভ ক্লাস — ৩ মাস', 'সাইট থেকেই সরাসরি লাইভ ক্লাস — Zoom / Google Meet লাগবে না\nবৃষ্টি, বন্যা বা যেতে না পারলে শিক্ষার্থীকে অনলাইনে পড়ান\nচ্যাট থেকে এক ক্লিকে ক্লাস শুরু — শিক্ষার্থী Join বাটন পাবে\nFind Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 6, '2026-11-12 16:54:37', NULL, 1),
(9, 'Teacher', 'স্পেশাল ৬ মাস', 6, 0, 500, NULL, 'স্পেশাল', 'স্পেশাল অফার — ৬ মাস মাত্র ৳500 (লাইভ ক্লাস ছাড়া)', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 0, 0, 10, '2026-11-12 16:54:37', NULL, 0),
(10, 'Student', 'স্পেশাল ৬ মাস', 6, 0, 200, NULL, 'স্পেশাল', 'স্পেশাল অফার — ৬ মাস মাত্র ৳200', 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন', 0, 0, 6, '2026-11-12 16:54:37', NULL, 0),
(11, 'Teacher', '১ মাস', 1, 0, 300, NULL, NULL, 'টিউশন পেতে ১ মাস', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 1, 0, 1, '2026-11-12 16:54:37', NULL, 0),
(12, 'Teacher', '৩ মাস', 3, 0, 600, NULL, NULL, 'টিউশন পেতে ৩ মাস', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 1, 0, 2, '2026-11-12 16:54:37', NULL, 0),
(13, 'Teacher', '৬ মাস', 6, 0, 1200, NULL, 'জনপ্রিয়', 'টিউশন পেতে ৬ মাস', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 1, 1, 3, '2026-11-12 16:54:37', NULL, 0),
(14, 'Teacher', '১ বছর', 12, 0, 2000, NULL, 'সেরা মূল্য', 'পুরো বছর নিশ্চিন্ত', 'Find Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 1, 0, 4, '2026-11-12 16:54:37', NULL, 0),
(15, 'Student', '৩ মাস', 3, 0, 300, NULL, NULL, 'টিচারের সাথে যোগাযোগ আর টিউশন পোস্ট — ৩ মাস', 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন', 1, 0, 1, '2026-11-12 16:54:37', NULL, 0),
(16, 'Student', '৬ মাস', 6, 0, 500, NULL, 'জনপ্রিয়', 'টিচারের সাথে যোগাযোগ আর টিউশন পোস্ট — ৬ মাস', 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন', 1, 1, 2, '2026-11-12 16:54:37', NULL, 0),
(17, 'Student', '১ বছর', 12, 0, 900, NULL, 'সেরা মূল্য', 'টিচারের সাথে যোগাযোগ আর টিউশন পোস্ট — ১ বছর', 'টিচারদের প্রোফাইল ছবি, মোবাইল নম্বর ও ইমেইল দেখতে পারবেন\nপছন্দের টিচারকে সরাসরি Direct Request পাঠাতে পারবেন\nTuition Circular এ যত খুশি টিউশন পোস্ট করতে পারবেন\nআগ্রহী টিচারদের আবেদন দেখে পছন্দের টিচার বাছাই করতে পারবেন', 1, 0, 3, '2026-11-12 16:54:37', NULL, 0),
(18, 'Teacher', 'Premium ১ মাস', 1, 0, 200, NULL, 'লাইভ ক্লাস', 'সব সুবিধা + লাইভ ক্লাস — ১ মাস', 'সাইট থেকেই সরাসরি লাইভ ক্লাস নিতে পারবেন — Zoom / Google Meet লাগবে না\nচ্যাট থেকে এক ক্লিকে ক্লাস শুরু — শিক্ষার্থী Join বাটন পাবে\nক্লাসের রেকর্ডিং পরে দেখা যায়\nFind Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 1, 0, 12, '2026-11-12 16:54:37', NULL, 1),
(19, 'Teacher', 'Premium ৩ মাস', 3, 0, 500, NULL, 'লাইভ ক্লাস', 'সব সুবিধা + লাইভ ক্লাস — ৩ মাস', 'সাইট থেকেই সরাসরি লাইভ ক্লাস নিতে পারবেন — Zoom / Google Meet লাগবে না\nচ্যাট থেকে এক ক্লিকে ক্লাস শুরু — শিক্ষার্থী Join বাটন পাবে\nক্লাসের রেকর্ডিং পরে দেখা যায়\nFind Tutor তালিকায় উপরের দিকে দেখাবে\nপ্রোফাইল সক্রিয় থাকবে — Find Tutor এ দেখাবে\nশিক্ষার্থীদের Tuition পোস্ট দেখতে ও যোগাযোগ করতে পারবেন\nTeacher Wanted পোস্ট করতে পারবেন\nনিজের স্টুডেন্ট, ক্লাস ও পেমেন্ট ম্যানেজ করতে পারবেন', 1, 1, 13, '2026-11-12 16:54:37', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `passwordresets`
--

CREATE TABLE `passwordresets` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `TokenHash` char(64) NOT NULL,
  `ExpiresAt` datetime NOT NULL,
  `UsedAt` datetime DEFAULT NULL,
  `RequestIp` varchar(64) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `passwordresets`
--

INSERT INTO `passwordresets` (`Id`, `UserId`, `TokenHash`, `ExpiresAt`, `UsedAt`, `RequestIp`, `CreatedAt`) VALUES
(1, 3, '70ec376f8af172f8413e042b6a1f62652e8f6dd6cd31cdb3920ffee2492d364b', '2026-10-29 10:41:03', '2026-10-29 10:17:36', '::1', '2026-10-29 10:11:03'),
(2, 3, 'e6b71d0a309ceed5bc7eae9a26c3af8ad40116ee8132e7c1ee702f5a0e6186ba', '2026-10-29 10:47:36', '2026-10-29 10:20:40', '::1', '2026-10-29 10:17:36'),
(3, 3, '6972be9d227d44dd0df1fbae7702e57610a35b81933a89144c482ce8659980e0', '2026-10-29 10:50:40', NULL, '::1', '2026-10-29 10:20:40'),
(4, 5, 'da4fd5d077a4a90108d3e64c97a4198791d19d3ced906ad237dc7ec8f292d976', '2026-10-29 10:59:41', NULL, '::1', '2026-10-29 10:29:41');

-- --------------------------------------------------------

--
-- Table structure for table `paymentaccounts`
--

CREATE TABLE `paymentaccounts` (
  `Id` int(11) NOT NULL,
  `MethodId` int(11) NOT NULL,
  `Title` varchar(80) NOT NULL,
  `AccountName` varchar(100) DEFAULT NULL,
  `AccountNumber` varchar(40) DEFAULT NULL,
  `BankName` varchar(100) DEFAULT NULL,
  `Branch` varchar(100) DEFAULT NULL,
  `RoutingNumber` varchar(20) DEFAULT NULL,
  `AccountType` varchar(40) DEFAULT NULL,
  `QrImageUrl` varchar(500) DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `SortOrder` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paymentaccounts`
--

INSERT INTO `paymentaccounts` (`Id`, `MethodId`, `Title`, `AccountName`, `AccountNumber`, `BankName`, `Branch`, `RoutingNumber`, `AccountType`, `QrImageUrl`, `IsActive`, `SortOrder`, `CreatedAt`) VALUES
(1, 1, 'Pubali Bank — Khatunganj Branch', 'BENGOL BAY', '0642901094586', 'PUBALI BANK PLC.', 'Khatunganj', '175154278', NULL, 'https://i.ibb.co.com/nsPT0f9y/Pubali-Qr-code.png', 1, 1, '2026-11-08 10:31:37'),
(2, 2, 'Bangla QR — স্ক্যান করে পেমেন্ট', 'BENGOL BAY', NULL, NULL, NULL, NULL, NULL, 'https://i.ibb.co.com/nsPT0f9y/Pubali-Qr-code.png', 0, 1, '2026-11-08 10:31:37'),
(3, 3, 'Bangla QR — স্ক্যান করে পেমেন্ট', 'BENGOL BAY', NULL, NULL, NULL, NULL, NULL, 'https://i.ibb.co.com/nsPT0f9y/Pubali-Qr-code.png', 0, 1, '2026-11-08 10:31:37'),
(4, 4, 'Bangla QR — স্ক্যান করে পেমেন্ট', 'BENGOL BAY', NULL, NULL, NULL, NULL, NULL, 'https://i.ibb.co.com/nsPT0f9y/Pubali-Qr-code.png', 0, 1, '2026-11-08 10:31:37'),
(5, 5, 'Bangla QR — স্ক্যান করে পেমেন্ট', 'BENGOL BAY', NULL, NULL, NULL, NULL, NULL, 'https://i.ibb.co.com/nsPT0f9y/Pubali-Qr-code.png', 1, 1, '2026-11-08 10:31:37'),
(6, 2, 'bKash এজেন্ট নম্বর', NULL, '01791619101', NULL, NULL, NULL, 'Agent', NULL, 1, 0, '2026-11-08 10:31:37'),
(7, 3, 'Nagad এজেন্ট নম্বর', NULL, '01829191916', NULL, NULL, NULL, 'Agent', NULL, 1, 0, '2026-11-08 10:31:37'),
(8, 4, 'Rocket এজেন্ট নম্বর', NULL, '01819027978', NULL, NULL, NULL, 'Agent', NULL, 1, 0, '2026-11-08 10:31:37'),
(9, 6, 'Upay এজেন্ট নম্বর', NULL, '01829191916', NULL, NULL, NULL, 'Agent', NULL, 1, 0, '2026-11-08 10:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `paymentmethods`
--

CREATE TABLE `paymentmethods` (
  `Id` int(11) NOT NULL,
  `Name` varchar(40) NOT NULL,
  `Kind` varchar(10) NOT NULL DEFAULT 'other',
  `LogoUrl` varchar(500) DEFAULT NULL,
  `Color` varchar(9) DEFAULT NULL,
  `TxnRequired` tinyint(1) NOT NULL DEFAULT 1,
  `Instructions` varchar(1000) DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `SortOrder` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paymentmethods`
--

INSERT INTO `paymentmethods` (`Id`, `Name`, `Kind`, `LogoUrl`, `Color`, `TxnRequired`, `Instructions`, `IsActive`, `SortOrder`, `CreatedAt`) VALUES
(1, 'Bank Transfer', 'bank', NULL, '#1D7FE5', 0, 'ব্যাংক অ্যাপ বা যেকোনো Bangla QR সাপোর্টেড অ্যাপ দিয়ে QR স্ক্যান করে পেমেন্ট করুন, অথবা ব্যাংকে গিয়ে জমা দিন। Deposit slip / receipt এর ছবি অবশ্যই আপলোড করুন।', 1, 5, '2026-11-08 10:31:37'),
(2, 'bKash', 'agent', 'https://jobersbd.com/wp-content/themes/jobersbd/assets/img/wallets/bkash.png', '#E2136E', 0, 'bKash অ্যাপ খুলুন (বা ডায়াল করুন *247#), তারপর “Cash Out” চাপুন।\nএজেন্ট নম্বর দিন: {number}\nটাকার পরিমাণ: {amount}। Cash Out চার্জ আপনার ওয়ালেট থেকে আলাদা করে কাটবে।\nপেমেন্ট হলে সফল হওয়ার স্ক্রিনটির স্ক্রিনশট নিন — এটিই আপনার রসিদ।', 1, 1, '2026-11-08 10:31:37'),
(3, 'Nagad', 'agent', 'https://jobersbd.com/wp-content/themes/jobersbd/assets/img/wallets/nagad.png', '#EC1C24', 0, 'Nagad অ্যাপ খুলুন (বা ডায়াল করুন *167#), তারপর “Cash Out” চাপুন।\nএজেন্ট নম্বর দিন: {number}\nটাকার পরিমাণ: {amount}। Cash Out চার্জ আপনার ওয়ালেট থেকে আলাদা করে কাটবে।\nপেমেন্ট হলে সফল হওয়ার স্ক্রিনটির স্ক্রিনশট নিন — এটিই আপনার রসিদ।', 1, 2, '2026-11-08 10:31:37'),
(4, 'Rocket', 'agent', 'https://jobersbd.com/wp-content/themes/jobersbd/assets/img/wallets/rocket.png', '#8C3494', 0, 'Rocket অ্যাপ খুলুন (বা ডায়াল করুন *322#), তারপর “Cash Out” চাপুন।\nএজেন্ট নম্বর দিন: {number}\nটাকার পরিমাণ: {amount}। Cash Out চার্জ আপনার ওয়ালেট থেকে আলাদা করে কাটবে।\nপেমেন্ট হলে সফল হওয়ার স্ক্রিনটির স্ক্রিনশট নিন — এটিই আপনার রসিদ।', 1, 3, '2026-11-08 10:31:37'),
(5, 'Other', 'other', NULL, '#475569', 1, 'যেকোনো Bangla QR সাপোর্টেড অ্যাপ (Upay, Cellfin, ব্যাংক অ্যাপ ইত্যাদি) দিয়ে QR স্ক্যান করে পেমেন্ট করুন। নিচের QR কোডটি অ্যাপের “Scan QR” দিয়ে স্ক্যান করুন, প্যাকেজের ঠিক টাকার পরিমাণ লিখে পেমেন্ট করুন। তারপর Transaction ID কপি করে পেমেন্ট সফল হওয়ার স্ক্রিনশট নিন।', 0, 6, '2026-11-08 10:31:37'),
(6, 'Upay', 'agent', 'https://jobersbd.com/wp-content/themes/jobersbd/assets/img/wallets/upay.png', '#0B4EA2', 0, 'Upay অ্যাপ খুলুন (বা ডায়াল করুন *268#), তারপর “Cash Out” চাপুন।\nএজেন্ট নম্বর দিন: {number}\nটাকার পরিমাণ: {amount}। Cash Out চার্জ আপনার ওয়ালেট থেকে আলাদা করে কাটবে।\nপেমেন্ট হলে সফল হওয়ার স্ক্রিনটির স্ক্রিনশট নিন — এটিই আপনার রসিদ।', 1, 4, '2026-11-08 10:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `paymentrequests`
--

CREATE TABLE `paymentrequests` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `PackageId` int(11) DEFAULT NULL,
  `PackageName` varchar(80) NOT NULL,
  `Audience` varchar(10) NOT NULL,
  `Amount` int(11) NOT NULL,
  `DurationMonths` int(11) NOT NULL DEFAULT 0,
  `PostLimit` int(11) NOT NULL DEFAULT 0,
  `MethodId` int(11) DEFAULT NULL,
  `MethodName` varchar(40) NOT NULL,
  `AccountId` int(11) DEFAULT NULL,
  `AccountInfo` varchar(200) DEFAULT NULL,
  `TxnId` varchar(40) DEFAULT NULL,
  `PayDate` date NOT NULL,
  `ReceiptUrl` varchar(300) NOT NULL,
  `Note` varchar(500) DEFAULT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'Pending',
  `AdminNote` varchar(300) DEFAULT NULL,
  `ReviewedBy` int(11) DEFAULT NULL,
  `ReviewedAt` datetime DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `CouponCode` varchar(30) DEFAULT NULL,
  `Reference` varchar(30) DEFAULT NULL,
  `ReferenceNote` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polloptions`
--

CREATE TABLE `polloptions` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `Text` varchar(100) NOT NULL,
  `SortOrder` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pollvotes`
--

CREATE TABLE `pollvotes` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `OptionId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profilecommentreactions`
--

CREATE TABLE `profilecommentreactions` (
  `Id` int(11) NOT NULL,
  `CommentId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Reaction` varchar(10) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profilepostcomments`
--

CREATE TABLE `profilepostcomments` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `ParentId` int(11) DEFAULT NULL,
  `Content` text NOT NULL,
  `IsEdited` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profilepostreactions`
--

CREATE TABLE `profilepostreactions` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Reaction` varchar(10) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profileposts`
--

CREATE TABLE `profileposts` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text DEFAULT NULL,
  `ImageUrl` varchar(500) DEFAULT NULL,
  `Privacy` varchar(10) NOT NULL DEFAULT 'Public',
  `IsEdited` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL,
  `SharedPostId` int(11) DEFAULT NULL,
  `PostType` varchar(12) NOT NULL DEFAULT 'post',
  `Meta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profileviews`
--

CREATE TABLE `profileviews` (
  `Id` int(11) NOT NULL,
  `ProfileUserId` int(11) NOT NULL,
  `ViewerId` int(11) NOT NULL,
  `ViewDate` date NOT NULL,
  `LastViewedAt` datetime NOT NULL,
  `Hits` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profileviewseen`
--

CREATE TABLE `profileviewseen` (
  `UserId` int(11) NOT NULL,
  `SeenAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referralcodes`
--

CREATE TABLE `referralcodes` (
  `UserId` int(11) NOT NULL,
  `Code` varchar(20) NOT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `Id` int(11) NOT NULL,
  `ReferrerId` int(11) NOT NULL,
  `ReferredUserId` int(11) NOT NULL,
  `Bonus` int(11) NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Credited',
  `Ip` varchar(64) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referralwithdrawals`
--

CREATE TABLE `referralwithdrawals` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Amount` int(11) NOT NULL,
  `Method` varchar(20) NOT NULL,
  `AccountName` varchar(100) DEFAULT NULL,
  `AccountNumber` varchar(40) NOT NULL,
  `BankName` varchar(100) DEFAULT NULL,
  `Branch` varchar(100) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `TxnId` varchar(60) DEFAULT NULL,
  `AdminNote` varchar(300) DEFAULT NULL,
  `ProcessedBy` int(11) DEFAULT NULL,
  `ProcessedAt` datetime DEFAULT NULL,
  `CreatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Rating` tinyint(4) NOT NULL DEFAULT 5,
  `Subtitle` varchar(120) DEFAULT NULL,
  `Content` text NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `AdminNote` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`Id`, `UserId`, `Rating`, `Subtitle`, `Content`, `Status`, `AdminNote`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 3, 5, 'Officiis est odit eos id doloribus', 'Cupidatat odio occaecat maxime fugiat et eum quae excepturi laborum labore omnis tempore officiis elit et sed voluptatum vel omnis', 'Approved', NULL, '2026-10-28 12:51:01', '2026-10-28 12:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `saveditems`
--

CREATE TABLE `saveditems` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `ItemType` varchar(12) NOT NULL,
  `ItemId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sitecontents`
--

CREATE TABLE `sitecontents` (
  `ContentKey` varchar(100) NOT NULL,
  `ContentValue` text DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sitecontents`
--

INSERT INTO `sitecontents` (`ContentKey`, `ContentValue`, `UpdatedAt`) VALUES
('cat_seeded', '1', '2026-11-08 10:31:37'),
('friends_retired', '1', '2026-11-12 17:00:38'),
('hero_v2', '1', '2026-11-08 10:31:37'),
('mail_from_email', '', '2026-10-29 10:25:21'),
('mail_from_name', '', '2026-10-29 10:25:21'),
('mail_host', 'mail.mashtermoshai.com', '2026-10-29 10:25:21'),
('mail_pass', '@mashtermoshai.com', '2026-10-29 10:25:21'),
('mail_port', '465', '2026-10-29 10:25:21'),
('mail_secure', 'ssl', '2026-10-29 10:25:21'),
('mail_site_url', 'https://mashtermoshai.com', '2026-10-29 10:25:21'),
('mail_user', 'noreply@mashtermoshai.com', '2026-10-29 10:25:21'),
('mig_kyc_teacher_v1', '1794480877', '2026-11-12 16:54:37'),
('pay_agent_v1', '1', '2026-11-08 10:31:37'),
('pay_bank_v2', '1', '2026-11-08 10:31:37'),
('pay_enforce_student', '1', '2026-11-08 10:31:37'),
('pay_seeded', '1', '2026-11-08 10:31:37'),
('pk_banner_enabled', '0', '2026-11-08 10:48:37'),
('pk_noimg_v1', '1', '2026-11-08 10:48:37'),
('pkg_feat_v2', '1', '2026-11-08 10:31:37'),
('pkg_live_v1', '1', '2026-11-12 16:54:37'),
('pkg_no_tick_v1', '1', '2026-11-12 16:54:37'),
('pkg_seeded', '1', '2026-11-08 10:31:37'),
('pkg_special_v1', '1', '2026-11-12 16:54:37'),
('pkg_student_free', '', '2026-11-08 10:31:37'),
('pkg_student_v2', '1', '2026-11-08 10:31:37'),
('pkg_t6_v1', '1', '2026-11-12 16:54:37'),
('pkg_v3', '1', '2026-11-12 16:54:37'),
('pkg_v4', '1', '2026-11-12 16:54:37'),
('pkg_v5', '1', '2026-11-12 16:54:37'),
('ref_bonus', '10', '2026-11-12 17:00:52'),
('ref_min_v100', '1', '2026-11-12 17:00:52'),
('ref_min_withdraw', '100', '2026-11-12 17:00:52'),
('ref_model', 'purchase', '2026-11-12 17:00:52'),
('social_links', '[{\"p\":\"facebook\",\"url\":\"https://www.facebook.com/mashtermoshai\"}]', '2026-10-28 14:15:36'),
('sub_sweep_at', '1794482690', '2026-11-12 17:24:50');

-- --------------------------------------------------------

--
-- Table structure for table `sitesettings`
--

CREATE TABLE `sitesettings` (
  `Id` int(11) NOT NULL,
  `HeroImageUrls` text DEFAULT NULL,
  `TutorSectionImageUrl` varchar(500) DEFAULT NULL,
  `SiteName` varchar(200) DEFAULT NULL,
  `NavbarLogoUrl` varchar(500) DEFAULT NULL,
  `FooterLogoUrl` varchar(500) DEFAULT NULL,
  `LogoSizePx` int(11) DEFAULT 56,
  `LogoShape` varchar(20) DEFAULT 'circle',
  `SliderEnabled` tinyint(1) NOT NULL DEFAULT 0,
  `SliderIntervalSeconds` int(11) NOT NULL DEFAULT 5,
  `PrimaryColor` varchar(20) DEFAULT '#1F3B2C',
  `SecondaryColor` varchar(20) DEFAULT '#F3F1E7',
  `AccentColor` varchar(20) DEFAULT '#F4C744',
  `TextColor` varchar(20) DEFAULT '#4B5648',
  `NavbarBgColor` varchar(20) DEFAULT '#F3F1E7',
  `FooterBgColor` varchar(20) DEFAULT '#F3F1E7',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sitesettings`
--

INSERT INTO `sitesettings` (`Id`, `HeroImageUrls`, `TutorSectionImageUrl`, `SiteName`, `NavbarLogoUrl`, `FooterLogoUrl`, `LogoSizePx`, `LogoShape`, `SliderEnabled`, `SliderIntervalSeconds`, `PrimaryColor`, `SecondaryColor`, `AccentColor`, `TextColor`, `NavbarBgColor`, `FooterBgColor`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'https://i.ibb.co.com/tMC8kk2L/Teacher-and-Student-Study-Session.png', '/uploads/tutor-section/tutor_639287184822321960.png', 'Mashter Moshai', '/uploads/logos/navbar_logo_639287184658503100.png', '/uploads/logos/footer_logo_639287184678535869.png', 56, 'circle', 1, 3, '#02b8f5', '#f5fbff', '#ffb300', '#334155', '#134856', '#134856', '2026-10-27 15:52:05', '2026-11-08 10:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `teachercertificates`
--

CREATE TABLE `teachercertificates` (
  `Id` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `Title` varchar(120) NOT NULL,
  `Issuer` varchar(120) DEFAULT NULL,
  `Year` varchar(10) DEFAULT NULL,
  `FileUrl` varchar(500) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacherclasses`
--

CREATE TABLE `teacherclasses` (
  `Id` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Days` varchar(20) DEFAULT NULL,
  `ClassDate` date DEFAULT NULL,
  `StartTime` char(5) NOT NULL,
  `EndTime` char(5) NOT NULL,
  `MeetLink` varchar(300) DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacherpayments`
--

CREATE TABLE `teacherpayments` (
  `Id` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `StudentId` int(11) DEFAULT NULL,
  `Amount` int(11) NOT NULL,
  `Month` char(7) NOT NULL,
  `PaidOn` date DEFAULT NULL,
  `Note` varchar(150) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacherrequests`
--

CREATE TABLE `teacherrequests` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `FullName` varchar(200) NOT NULL DEFAULT '',
  `Email` varchar(200) NOT NULL DEFAULT '',
  `MobileNumber` varchar(30) NOT NULL DEFAULT '',
  `Education` varchar(300) NOT NULL DEFAULT '',
  `Institution` varchar(300) NOT NULL DEFAULT '',
  `SubjectExpertise` varchar(500) NOT NULL DEFAULT '',
  `Experience` varchar(300) NOT NULL DEFAULT '',
  `TeachingStyle` text DEFAULT NULL,
  `AvailableDays` varchar(200) NOT NULL DEFAULT '',
  `PreferredTime` varchar(200) NOT NULL DEFAULT '',
  `HourlyRate` varchar(50) NOT NULL DEFAULT '',
  `CvLink` varchar(500) NOT NULL DEFAULT '',
  `WhyTeach` text DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `RequestDate` datetime NOT NULL DEFAULT current_timestamp(),
  `ResponseDate` datetime DEFAULT NULL,
  `AdminNote` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacherreviews`
--

CREATE TABLE `teacherreviews` (
  `Id` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `StudentUserId` int(11) NOT NULL,
  `Rating` tinyint(4) NOT NULL,
  `Comment` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacherstudents`
--

CREATE TABLE `teacherstudents` (
  `Id` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `StudentUserId` int(11) DEFAULT NULL,
  `Name` varchar(80) NOT NULL,
  `ClassLevel` varchar(60) DEFAULT NULL,
  `Subject` varchar(80) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `MonthlyFee` int(11) DEFAULT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'Active',
  `Note` varchar(200) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachwantedcommentlikes`
--

CREATE TABLE `teachwantedcommentlikes` (
  `Id` int(11) NOT NULL,
  `CommentId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachwantedcomments`
--

CREATE TABLE `teachwantedcomments` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `ParentId` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachwantedlikes`
--

CREATE TABLE `teachwantedlikes` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachwantedposts`
--

CREATE TABLE `teachwantedposts` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `ImageUrl` varchar(500) DEFAULT NULL,
  `LikeCount` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `VideoUrl` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitionapplications`
--

CREATE TABLE `tuitionapplications` (
  `Id` int(11) NOT NULL,
  `TuitionPostId` int(11) NOT NULL,
  `UserId` int(11) DEFAULT NULL,
  `ApplicantName` varchar(200) NOT NULL,
  `PhoneNumber` varchar(30) NOT NULL,
  `WhatsAppNumber` varchar(30) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `CvLink` varchar(500) NOT NULL,
  `CoverNote` text DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `AdminNote` text DEFAULT NULL,
  `AppliedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitioncircularapplications`
--

CREATE TABLE `tuitioncircularapplications` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `TeacherId` int(11) NOT NULL,
  `Message` text DEFAULT NULL,
  `PhoneNumber` varchar(30) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitioncircularcommentlikes`
--

CREATE TABLE `tuitioncircularcommentlikes` (
  `Id` int(11) NOT NULL,
  `CommentId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitioncircularcomments`
--

CREATE TABLE `tuitioncircularcomments` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `ParentId` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitioncircularlikes`
--

CREATE TABLE `tuitioncircularlikes` (
  `Id` int(11) NOT NULL,
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitioncircularposts`
--

CREATE TABLE `tuitioncircularposts` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `ImageUrl` varchar(500) DEFAULT NULL,
  `PreferredArea` varchar(300) DEFAULT NULL,
  `ClassLevel` varchar(200) DEFAULT NULL,
  `Subjects` varchar(300) DEFAULT NULL,
  `Budget` varchar(100) DEFAULT NULL,
  `LikeCount` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `Title` varchar(150) DEFAULT NULL,
  `Medium` varchar(10) DEFAULT NULL,
  `TuitionType` varchar(10) DEFAULT NULL,
  `VideoUrl` varchar(500) DEFAULT NULL,
  `ViewCount` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tuitionposts`
--

CREATE TABLE `tuitionposts` (
  `Id` int(11) NOT NULL,
  `Title` varchar(300) NOT NULL,
  `StudentGender` varchar(100) DEFAULT NULL,
  `StudentClass` varchar(200) DEFAULT NULL,
  `Subjects` varchar(300) DEFAULT NULL,
  `DaysPerWeek` varchar(50) DEFAULT NULL,
  `PreferredTime` varchar(200) DEFAULT NULL,
  `Salary` varchar(100) DEFAULT NULL,
  `Area` varchar(300) DEFAULT NULL,
  `MediaFee` varchar(200) DEFAULT NULL,
  `TuitionCode` varchar(50) DEFAULT NULL,
  `SpecialNote` text DEFAULT NULL,
  `TutorGenderPreference` varchar(50) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Open',
  `ShowOnHome` tinyint(1) NOT NULL DEFAULT 0,
  `PostedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutorrequests`
--

CREATE TABLE `tutorrequests` (
  `Id` int(11) NOT NULL,
  `TutorId` varchar(50) DEFAULT NULL,
  `TutorName` varchar(200) DEFAULT NULL,
  `StudentName` varchar(200) NOT NULL DEFAULT '',
  `PhoneNumber` varchar(30) DEFAULT NULL,
  `WhatsAppNumber` varchar(30) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `StudentClass` varchar(200) DEFAULT NULL,
  `RequiredSubject` varchar(300) DEFAULT NULL,
  `PreferredArea` varchar(300) DEFAULT NULL,
  `TeachingMode` varchar(50) DEFAULT NULL,
  `PreferredDays` varchar(200) DEFAULT NULL,
  `PreferredTime` varchar(200) DEFAULT NULL,
  `MonthlyBudget` varchar(100) DEFAULT NULL,
  `AdditionalNotes` text DEFAULT NULL,
  `RequestType` varchar(50) NOT NULL DEFAULT 'SpecificTutor',
  `RequestId` varchar(50) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending',
  `AdminNote` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL,
  `StudentUserId` int(11) DEFAULT NULL,
  `TeacherUserId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `useractivitydays`
--

CREATE TABLE `useractivitydays` (
  `UserId` int(11) NOT NULL,
  `Day` date NOT NULL,
  `Points` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `useractivitydays`
--

INSERT INTO `useractivitydays` (`UserId`, `Day`, `Points`) VALUES
(5, '2026-11-12', 1);

-- --------------------------------------------------------

--
-- Table structure for table `userdevices`
--

CREATE TABLE `userdevices` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `DeviceHash` char(64) NOT NULL,
  `Label` varchar(120) DEFAULT NULL,
  `Ip` varchar(64) DEFAULT NULL,
  `Status` varchar(10) NOT NULL DEFAULT 'Active',
  `Note` varchar(300) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL,
  `LastSeenAt` datetime DEFAULT NULL,
  `DecidedBy` int(11) DEFAULT NULL,
  `DecidedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `userdevices`
--

INSERT INTO `userdevices` (`Id`, `UserId`, `DeviceHash`, `Label`, `Ip`, `Status`, `Note`, `CreatedAt`, `LastSeenAt`, `DecidedBy`, `DecidedAt`) VALUES
(1, 5, 'd1c6589775829c47066b211ac7e33dd8461588154163813fcedfeafe5dca1d98', 'Edge · Windows', '::1', 'Active', NULL, '2026-11-12 17:00:38', '2026-11-12 17:49:51', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usergoals`
--

CREATE TABLE `usergoals` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Title` varchar(120) NOT NULL,
  `Category` varchar(30) NOT NULL DEFAULT 'Custom',
  `TargetDate` date DEFAULT NULL,
  `ManualProgress` tinyint(4) NOT NULL DEFAULT 0,
  `IsPrimary` tinyint(1) NOT NULL DEFAULT 0,
  `Status` varchar(12) NOT NULL DEFAULT 'Active',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userprofileextras`
--

CREATE TABLE `userprofileextras` (
  `UserId` int(11) NOT NULL,
  `CoverImageUrl` varchar(500) DEFAULT NULL,
  `CoverPosY` tinyint(4) NOT NULL DEFAULT 50,
  `UpdatedAt` datetime DEFAULT NULL,
  `Bio` varchar(160) DEFAULT NULL,
  `City` varchar(80) DEFAULT NULL,
  `Subjects` varchar(300) DEFAULT NULL,
  `Quote` varchar(160) DEFAULT NULL,
  `Headline` varchar(80) DEFAULT NULL,
  `Levels` varchar(200) DEFAULT NULL,
  `LastSeenAt` datetime DEFAULT NULL,
  `TeachMode` varchar(10) DEFAULT NULL,
  `FeeMin` int(11) DEFAULT NULL,
  `FeeMax` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FullName` varchar(200) NOT NULL DEFAULT '',
  `Email` varchar(200) NOT NULL DEFAULT '',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `LastLoginAt` datetime DEFAULT NULL,
  `Role` varchar(20) NOT NULL DEFAULT 'User',
  `IsActive` tinyint(1) NOT NULL DEFAULT 1,
  `MobileNumber` varchar(30) DEFAULT NULL,
  `ProfileImage` varchar(500) DEFAULT NULL,
  `Gender` varchar(30) DEFAULT NULL,
  `AgeRange` varchar(50) DEFAULT NULL,
  `PrimaryDeviceType` varchar(50) DEFAULT NULL,
  `YearsOfExperience` varchar(50) DEFAULT NULL,
  `AreaType` varchar(50) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  `StreetAddress` varchar(500) DEFAULT NULL,
  `PermanentAddress` varchar(500) DEFAULT NULL,
  `EducationLevel` varchar(100) DEFAULT NULL,
  `CurrentStudyStatus` varchar(20) DEFAULT NULL,
  `ExamDegreeTitle` varchar(200) DEFAULT NULL,
  `InstitutionName` varchar(300) DEFAULT NULL,
  `PassingYear` varchar(10) DEFAULT NULL,
  `IsCSEStudent` tinyint(1) DEFAULT NULL,
  `CvLink` varchar(500) DEFAULT NULL,
  `GithubProfile` varchar(500) DEFAULT NULL,
  `PortfolioLink` varchar(500) DEFAULT NULL,
  `LinkedInProfile` varchar(500) DEFAULT NULL,
  `ProfileImageLink` varchar(500) DEFAULT NULL,
  `EmailVerified` tinyint(1) NOT NULL DEFAULT 1,
  `PendingRef` varchar(16) DEFAULT NULL,
  `HowHeard` varchar(30) DEFAULT NULL,
  `HowHeardNote` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `UserName`, `Password`, `FullName`, `Email`, `CreatedAt`, `LastLoginAt`, `Role`, `IsActive`, `MobileNumber`, `ProfileImage`, `Gender`, `AgeRange`, `PrimaryDeviceType`, `YearsOfExperience`, `AreaType`, `Country`, `StreetAddress`, `PermanentAddress`, `EducationLevel`, `CurrentStudyStatus`, `ExamDegreeTitle`, `InstitutionName`, `PassingYear`, `IsCSEStudent`, `CvLink`, `GithubProfile`, `PortfolioLink`, `LinkedInProfile`, `ProfileImageLink`, `EmailVerified`, `PendingRef`, `HowHeard`, `HowHeardNote`) VALUES
(1, 'admin', '$2y$10$ODO3oINl0igaTaUYbePi8OH5AZJiML2bttpk8N56APsnqstPR/qBG', 'Site Admin', 'admin@gmail.com', '2026-10-27 15:52:05', '2026-10-29 10:24:02', 'Admin', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(3, 'Ebtesham', '$2y$10$1luNaX8zdaXYw2UUSfTafuaBSrtf9BN4YWhpg7Ep5HNf6rjR8RgLS', 'Md Ebtesham Azam', 'mdebteshamazam@gmail.com', '2026-10-27 23:27:09', '2026-10-28 13:15:40', 'User', 1, '01521401674', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '/uploads/profiles/3_639287670790210299.jpg', 1, NULL, NULL, NULL),
(4, 'munna', '$2y$10$yQ.X3VhjFp4LWru50DETGOUvBH5RUH7sZ2R1ow18YCT43Ni9SgKsi', 'Md munna', 'cv.jobersbd@gmail.com', '2026-10-28 13:11:06', '2026-10-28 13:11:10', 'User', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL),
(5, 'ifty', '$2y$10$Bbt4zvISN4qst8KbzGUqgem8tE1A4Wo2cFUl/VuE6RbLYkZ3W16T6', 'md ifty', 'mohammadebtesham757@gmail.com', '2026-10-29 10:29:16', '2026-11-12 17:00:38', 'User', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usersubscriptions`
--

CREATE TABLE `usersubscriptions` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `PaymentId` int(11) DEFAULT NULL,
  `PackageName` varchar(80) NOT NULL,
  `Audience` varchar(10) NOT NULL,
  `StartsAt` datetime NOT NULL,
  `EndsAt` datetime DEFAULT NULL,
  `PostsTotal` int(11) NOT NULL DEFAULT 0,
  `PostsUsed` int(11) NOT NULL DEFAULT 0,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `RemindedAt` datetime DEFAULT NULL,
  `Reminded1At` datetime DEFAULT NULL,
  `EndNotifiedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usersuspensions`
--

CREATE TABLE `usersuspensions` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Reason` text DEFAULT NULL,
  `SuspendedUntil` datetime DEFAULT NULL,
  `IsPermanent` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `LiftedAt` datetime DEFAULT NULL,
  `LiftedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usersuspensions`
--

INSERT INTO `usersuspensions` (`Id`, `UserId`, `Reason`, `SuspendedUntil`, `IsPermanent`, `CreatedBy`, `CreatedAt`, `LiftedAt`, `LiftedBy`) VALUES
(1, 4, 'emni', '2026-10-29 13:15:00', 0, 1, '2026-10-28 13:11:43', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Cat_Slug` (`Slug`),
  ADD KEY `IX_Cat_Group` (`GroupId`,`SortOrder`);

--
-- Indexes for table `categorygroups`
--
ALTER TABLE `categorygroups`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `chatclasses`
--
ALTER TABLE `chatclasses`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_ChatClass_Thread` (`Kind`,`AppId`,`StartsAt`);

--
-- Indexes for table `chatdirect`
--
ALTER TABLE `chatdirect`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_ChatDirect` (`StudentId`,`TeacherId`),
  ADD KEY `IX_ChatDirect_T` (`TeacherId`);

--
-- Indexes for table `chatgroupmembers`
--
ALTER TABLE `chatgroupmembers`
  ADD PRIMARY KEY (`GroupId`,`UserId`),
  ADD KEY `IX_ChatGM_U` (`UserId`);

--
-- Indexes for table `chatgroups`
--
ALTER TABLE `chatgroups`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_ChatGroups_T` (`TeacherId`);

--
-- Indexes for table `chatmessages`
--
ALTER TABLE `chatmessages`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Chat_Thread` (`Kind`,`AppId`,`Id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Coupon_Code` (`Code`);

--
-- Indexes for table `edupostcomments`
--
ALTER TABLE `edupostcomments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_EduC_Post` (`PostId`);

--
-- Indexes for table `edupostlikes`
--
ALTER TABLE `edupostlikes`
  ADD PRIMARY KEY (`PostId`,`UserId`);

--
-- Indexes for table `eduposts`
--
ALTER TABLE `eduposts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Edu_Status` (`Status`,`Id`),
  ADD KEY `IX_Edu_User` (`UserId`);

--
-- Indexes for table `emailotps`
--
ALTER TABLE `emailotps`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_EmailOtps_User` (`UserId`,`CreatedAt`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Friendships_Pair` (`UserLow`,`UserHigh`),
  ADD KEY `IX_Friendships_Addressee` (`AddresseeId`,`Status`),
  ADD KEY `IX_Friendships_Requester` (`RequesterId`,`Status`);

--
-- Indexes for table `goaltasks`
--
ALTER TABLE `goaltasks`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_GoalTasks_Goal` (`GoalId`);

--
-- Indexes for table `helpdeskcomments`
--
ALTER TABLE `helpdeskcomments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_HelpdeskComments_Post` (`PostId`);

--
-- Indexes for table `helpdeskposts`
--
ALTER TABLE `helpdeskposts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_HelpdeskPosts_User` (`UserId`);

--
-- Indexes for table `kycrequests`
--
ALTER TABLE `kycrequests`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Kyc_User` (`UserId`,`Kind`),
  ADD KEY `IX_Kyc_Status` (`Status`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Notices_Published` (`IsActive`,`PublishedDate`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Notifications_User` (`UserId`,`IsRead`,`CreatedAt`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_Pkg_Aud` (`Audience`,`SortOrder`);

--
-- Indexes for table `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_PasswordResets_Token` (`TokenHash`),
  ADD KEY `IX_PasswordResets_User` (`UserId`,`CreatedAt`),
  ADD KEY `IX_PasswordResets_Ip` (`RequestIp`,`CreatedAt`);

--
-- Indexes for table `paymentaccounts`
--
ALTER TABLE `paymentaccounts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_PA_Method` (`MethodId`);

--
-- Indexes for table `paymentmethods`
--
ALTER TABLE `paymentmethods`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `paymentrequests`
--
ALTER TABLE `paymentrequests`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_PR_User` (`UserId`),
  ADD KEY `IX_PR_Status` (`Status`),
  ADD KEY `IX_PR_Txn` (`TxnId`);

--
-- Indexes for table `polloptions`
--
ALTER TABLE `polloptions`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_PollOptions_Post` (`PostId`);

--
-- Indexes for table `pollvotes`
--
ALTER TABLE `pollvotes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_PollVotes` (`PostId`,`UserId`),
  ADD KEY `IX_PollVotes_Option` (`OptionId`);

--
-- Indexes for table `profilecommentreactions`
--
ALTER TABLE `profilecommentreactions`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_PCR_CommentUser` (`CommentId`,`UserId`);

--
-- Indexes for table `profilepostcomments`
--
ALTER TABLE `profilepostcomments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_PPC_Post` (`PostId`,`Id`);

--
-- Indexes for table `profilepostreactions`
--
ALTER TABLE `profilepostreactions`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_PPR_PostUser` (`PostId`,`UserId`);

--
-- Indexes for table `profileposts`
--
ALTER TABLE `profileposts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_ProfilePosts_User` (`UserId`,`Id`);

--
-- Indexes for table `profileviews`
--
ALTER TABLE `profileviews`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_PV_Day` (`ProfileUserId`,`ViewerId`,`ViewDate`),
  ADD KEY `IX_PV_Profile` (`ProfileUserId`,`LastViewedAt`);

--
-- Indexes for table `profileviewseen`
--
ALTER TABLE `profileviewseen`
  ADD PRIMARY KEY (`UserId`);

--
-- Indexes for table `referralcodes`
--
ALTER TABLE `referralcodes`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `UX_RefCode` (`Code`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Ref_Referred` (`ReferredUserId`),
  ADD KEY `IX_Ref_Referrer` (`ReferrerId`);

--
-- Indexes for table `referralwithdrawals`
--
ALTER TABLE `referralwithdrawals`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_RefW_User` (`UserId`),
  ADD KEY `IX_RefW_Status` (`Status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Reviews_User` (`UserId`),
  ADD KEY `IX_Reviews_Status` (`Status`);

--
-- Indexes for table `saveditems`
--
ALTER TABLE `saveditems`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Saved` (`UserId`,`ItemType`,`ItemId`);

--
-- Indexes for table `sitecontents`
--
ALTER TABLE `sitecontents`
  ADD PRIMARY KEY (`ContentKey`);

--
-- Indexes for table `sitesettings`
--
ALTER TABLE `sitesettings`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `teachercertificates`
--
ALTER TABLE `teachercertificates`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TCert_Teacher` (`TeacherId`);

--
-- Indexes for table `teacherclasses`
--
ALTER TABLE `teacherclasses`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TC_Teacher` (`TeacherId`);

--
-- Indexes for table `teacherpayments`
--
ALTER TABLE `teacherpayments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TP_TeacherMonth` (`TeacherId`,`Month`);

--
-- Indexes for table `teacherrequests`
--
ALTER TABLE `teacherrequests`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TeacherRequests_UserId` (`UserId`),
  ADD KEY `IX_TeacherRequests_Status` (`Status`);

--
-- Indexes for table `teacherreviews`
--
ALTER TABLE `teacherreviews`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_TR` (`TeacherId`,`StudentUserId`);

--
-- Indexes for table `teacherstudents`
--
ALTER TABLE `teacherstudents`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TS_Teacher` (`TeacherId`),
  ADD KEY `IX_TS_Student` (`StudentUserId`);

--
-- Indexes for table `teachwantedcommentlikes`
--
ALTER TABLE `teachwantedcommentlikes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_TWCL_CommentUser` (`CommentId`,`UserId`);

--
-- Indexes for table `teachwantedcomments`
--
ALTER TABLE `teachwantedcomments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TeachWantedComments_Post` (`PostId`);

--
-- Indexes for table `teachwantedlikes`
--
ALTER TABLE `teachwantedlikes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_TWL_PostUser` (`PostId`,`UserId`);

--
-- Indexes for table `teachwantedposts`
--
ALTER TABLE `teachwantedposts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TeachWantedPosts_User` (`UserId`);

--
-- Indexes for table `tuitionapplications`
--
ALTER TABLE `tuitionapplications`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TuitionApplications_Post` (`TuitionPostId`),
  ADD KEY `IX_TuitionApplications_User` (`UserId`);

--
-- Indexes for table `tuitioncircularapplications`
--
ALTER TABLE `tuitioncircularapplications`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_TCA_PostTeacher` (`PostId`,`TeacherId`),
  ADD KEY `IX_TCA_Teacher` (`TeacherId`);

--
-- Indexes for table `tuitioncircularcommentlikes`
--
ALTER TABLE `tuitioncircularcommentlikes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_TCCL_CommentUser` (`CommentId`,`UserId`);

--
-- Indexes for table `tuitioncircularcomments`
--
ALTER TABLE `tuitioncircularcomments`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TuitionCircularComments_Post` (`PostId`);

--
-- Indexes for table `tuitioncircularlikes`
--
ALTER TABLE `tuitioncircularlikes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_TCL_PostUser` (`PostId`,`UserId`);

--
-- Indexes for table `tuitioncircularposts`
--
ALTER TABLE `tuitioncircularposts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TuitionCircularPosts_User` (`UserId`);

--
-- Indexes for table `tuitionposts`
--
ALTER TABLE `tuitionposts`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TuitionPosts_Status` (`Status`,`PostedDate`);

--
-- Indexes for table `tutorrequests`
--
ALTER TABLE `tutorrequests`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TutorRequests_TutorName` (`TutorName`),
  ADD KEY `IX_TutorRequests_Status` (`Status`);

--
-- Indexes for table `useractivitydays`
--
ALTER TABLE `useractivitydays`
  ADD PRIMARY KEY (`UserId`,`Day`);

--
-- Indexes for table `userdevices`
--
ALTER TABLE `userdevices`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_UD_User` (`UserId`,`Status`),
  ADD KEY `IX_UD_Hash` (`DeviceHash`);

--
-- Indexes for table `usergoals`
--
ALTER TABLE `usergoals`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_UserGoals_User` (`UserId`);

--
-- Indexes for table `userprofileextras`
--
ALTER TABLE `userprofileextras`
  ADD PRIMARY KEY (`UserId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Users_UserName` (`UserName`),
  ADD KEY `IX_Users_Email` (`Email`),
  ADD KEY `IX_Users_Role` (`Role`);

--
-- Indexes for table `usersubscriptions`
--
ALTER TABLE `usersubscriptions`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_US_User` (`UserId`,`Audience`);

--
-- Indexes for table `usersuspensions`
--
ALTER TABLE `usersuspensions`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_UserSuspensions_User` (`UserId`,`LiftedAt`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `categorygroups`
--
ALTER TABLE `categorygroups`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chatclasses`
--
ALTER TABLE `chatclasses`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatdirect`
--
ALTER TABLE `chatdirect`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatgroups`
--
ALTER TABLE `chatgroups`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chatmessages`
--
ALTER TABLE `chatmessages`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `edupostcomments`
--
ALTER TABLE `edupostcomments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eduposts`
--
ALTER TABLE `eduposts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emailotps`
--
ALTER TABLE `emailotps`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goaltasks`
--
ALTER TABLE `goaltasks`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `helpdeskcomments`
--
ALTER TABLE `helpdeskcomments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `helpdeskposts`
--
ALTER TABLE `helpdeskposts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kycrequests`
--
ALTER TABLE `kycrequests`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `passwordresets`
--
ALTER TABLE `passwordresets`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `paymentaccounts`
--
ALTER TABLE `paymentaccounts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `paymentmethods`
--
ALTER TABLE `paymentmethods`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `paymentrequests`
--
ALTER TABLE `paymentrequests`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `polloptions`
--
ALTER TABLE `polloptions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pollvotes`
--
ALTER TABLE `pollvotes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profilecommentreactions`
--
ALTER TABLE `profilecommentreactions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profilepostcomments`
--
ALTER TABLE `profilepostcomments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profilepostreactions`
--
ALTER TABLE `profilepostreactions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profileposts`
--
ALTER TABLE `profileposts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profileviews`
--
ALTER TABLE `profileviews`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referralwithdrawals`
--
ALTER TABLE `referralwithdrawals`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saveditems`
--
ALTER TABLE `saveditems`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sitesettings`
--
ALTER TABLE `sitesettings`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teachercertificates`
--
ALTER TABLE `teachercertificates`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacherclasses`
--
ALTER TABLE `teacherclasses`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacherpayments`
--
ALTER TABLE `teacherpayments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacherrequests`
--
ALTER TABLE `teacherrequests`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacherreviews`
--
ALTER TABLE `teacherreviews`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacherstudents`
--
ALTER TABLE `teacherstudents`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachwantedcommentlikes`
--
ALTER TABLE `teachwantedcommentlikes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachwantedcomments`
--
ALTER TABLE `teachwantedcomments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachwantedlikes`
--
ALTER TABLE `teachwantedlikes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachwantedposts`
--
ALTER TABLE `teachwantedposts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitionapplications`
--
ALTER TABLE `tuitionapplications`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitioncircularapplications`
--
ALTER TABLE `tuitioncircularapplications`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitioncircularcommentlikes`
--
ALTER TABLE `tuitioncircularcommentlikes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitioncircularcomments`
--
ALTER TABLE `tuitioncircularcomments`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitioncircularlikes`
--
ALTER TABLE `tuitioncircularlikes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitioncircularposts`
--
ALTER TABLE `tuitioncircularposts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tuitionposts`
--
ALTER TABLE `tuitionposts`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tutorrequests`
--
ALTER TABLE `tutorrequests`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userdevices`
--
ALTER TABLE `userdevices`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usergoals`
--
ALTER TABLE `usergoals`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usersubscriptions`
--
ALTER TABLE `usersubscriptions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usersuspensions`
--
ALTER TABLE `usersuspensions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `helpdeskcomments`
--
ALTER TABLE `helpdeskcomments`
  ADD CONSTRAINT `FK_HC_Post` FOREIGN KEY (`PostId`) REFERENCES `helpdeskposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `profilecommentreactions`
--
ALTER TABLE `profilecommentreactions`
  ADD CONSTRAINT `FK_PCR_Comment` FOREIGN KEY (`CommentId`) REFERENCES `profilepostcomments` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `profilepostcomments`
--
ALTER TABLE `profilepostcomments`
  ADD CONSTRAINT `FK_PPC_Post` FOREIGN KEY (`PostId`) REFERENCES `profileposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `profilepostreactions`
--
ALTER TABLE `profilepostreactions`
  ADD CONSTRAINT `FK_PPR_Post` FOREIGN KEY (`PostId`) REFERENCES `profileposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `teachwantedcommentlikes`
--
ALTER TABLE `teachwantedcommentlikes`
  ADD CONSTRAINT `FK_TWCL_Comment` FOREIGN KEY (`CommentId`) REFERENCES `teachwantedcomments` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `teachwantedcomments`
--
ALTER TABLE `teachwantedcomments`
  ADD CONSTRAINT `FK_TWC_Post` FOREIGN KEY (`PostId`) REFERENCES `teachwantedposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `teachwantedlikes`
--
ALTER TABLE `teachwantedlikes`
  ADD CONSTRAINT `FK_TWL_Post` FOREIGN KEY (`PostId`) REFERENCES `teachwantedposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `tuitionapplications`
--
ALTER TABLE `tuitionapplications`
  ADD CONSTRAINT `FK_TuitionApplications_TuitionPosts` FOREIGN KEY (`TuitionPostId`) REFERENCES `tuitionposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `tuitioncircularapplications`
--
ALTER TABLE `tuitioncircularapplications`
  ADD CONSTRAINT `FK_TCA_Post` FOREIGN KEY (`PostId`) REFERENCES `tuitioncircularposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `tuitioncircularcommentlikes`
--
ALTER TABLE `tuitioncircularcommentlikes`
  ADD CONSTRAINT `FK_TCCL_Comment` FOREIGN KEY (`CommentId`) REFERENCES `tuitioncircularcomments` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `tuitioncircularcomments`
--
ALTER TABLE `tuitioncircularcomments`
  ADD CONSTRAINT `FK_TCC_Post` FOREIGN KEY (`PostId`) REFERENCES `tuitioncircularposts` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `tuitioncircularlikes`
--
ALTER TABLE `tuitioncircularlikes`
  ADD CONSTRAINT `FK_TCL_Post` FOREIGN KEY (`PostId`) REFERENCES `tuitioncircularposts` (`Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
