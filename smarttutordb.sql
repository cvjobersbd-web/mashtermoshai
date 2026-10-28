-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 05:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smarttutordb`
--

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
(2, 'Deserunt similique q', 'Accusantium praesent', 'Event', '/uploads/notices/notice_639286918573739540.png', '2024-10-02 00:00:00', 0, 1, '2026-10-27 15:57:39', NULL);

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
(1, 4, '🎓 You\'re now a Teacher!', 'Congratulations! Your teacher request has been approved. Access your Teacher Dashboard now.', 'success', 'fa-chalkboard-teacher', '/Teacher/TeacherDashboard', 0, '2026-10-28 09:43:25');

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
(1, '/uploads/hero/hero_639286918009004230.png,/uploads/hero/hero_639286918098723750.png', 'https://i.ibb.co.com/Hfpwnx46/111.png', 'SMART TUTOR HUB', 'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png', 'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png', 56, 'circle', 1, 5, '#1f3b2c', '#f3f1e7', '#f4c744', '#4b5648', '#f3f1e7', '#f3f1e7', '2026-10-27 15:52:05', '2026-10-27 15:56:54');

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

--
-- Dumping data for table `teacherrequests`
--

INSERT INTO `teacherrequests` (`Id`, `UserId`, `FullName`, `Email`, `MobileNumber`, `Education`, `Institution`, `SubjectExpertise`, `Experience`, `TeachingStyle`, `AvailableDays`, `PreferredTime`, `HourlyRate`, `CvLink`, `WhyTeach`, `Status`, `RequestDate`, `ResponseDate`, `AdminNote`) VALUES
(1, 4, 'Md Maruf', 'marufhossenmafuz@gmail.com', '01823018739', 'Quasi odio saepe ali', 'Ut ullam pariatur O', 'Dolor pariatur Dolo', '5-10 years', 'Beatae fugit dolore', '15', 'Voluptatibus quis mi', 'Non qui ut qui sed o', 'Non assumenda laudan', 'Quod eum ab enim con', 'Approved', '2026-10-28 09:43:13', '2026-10-28 09:43:25', '');

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
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
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
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
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
  `UpdatedAt` datetime DEFAULT NULL
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
  `ProfileImageLink` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `UserName`, `Password`, `FullName`, `Email`, `CreatedAt`, `LastLoginAt`, `Role`, `IsActive`, `MobileNumber`, `ProfileImage`, `Gender`, `AgeRange`, `PrimaryDeviceType`, `YearsOfExperience`, `AreaType`, `Country`, `StreetAddress`, `PermanentAddress`, `EducationLevel`, `CurrentStudyStatus`, `ExamDegreeTitle`, `InstitutionName`, `PassingYear`, `IsCSEStudent`, `CvLink`, `GithubProfile`, `PortfolioLink`, `LinkedInProfile`, `ProfileImageLink`) VALUES
(1, 'admin', '$2y$10$ODO3oINl0igaTaUYbePi8OH5AZJiML2bttpk8N56APsnqstPR/qBG', 'Site Admin', 'admin@gmail.com', '2026-10-27 15:52:05', '2026-10-28 09:48:52', 'Admin', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Ebtesham', '$2y$10$qEveLLd7x5Hhwgs070IR3OXZvnWvBopkgRA7bDa3iZa3MAtRYgrTG', 'Md Ebtesham Azam', 'mdebteshamazam@gmail.com', '2026-10-28 09:38:33', '2026-10-28 09:49:07', 'User', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'maruf', '$2y$10$5soPRfo6rp9WvMs8TlAgB.WUoWlKSuo8No6cBHwLcdMt8jpsEad6G', 'Md Maruf', 'marufhossenmafuz@gmail.com', '2026-10-28 09:41:41', '2026-10-28 09:49:36', 'Teacher', 1, '01823018739', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

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
-- Indexes for table `sitesettings`
--
ALTER TABLE `sitesettings`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `teacherrequests`
--
ALTER TABLE `teacherrequests`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IX_TeacherRequests_UserId` (`UserId`),
  ADD KEY `IX_TeacherRequests_Status` (`Status`);

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UX_Users_UserName` (`UserName`),
  ADD KEY `IX_Users_Email` (`Email`),
  ADD KEY `IX_Users_Role` (`Role`);

--
-- AUTO_INCREMENT for dumped tables
--

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
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sitesettings`
--
ALTER TABLE `sitesettings`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teacherrequests`
--
ALTER TABLE `teacherrequests`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `helpdeskcomments`
--
ALTER TABLE `helpdeskcomments`
  ADD CONSTRAINT `FK_HC_Post` FOREIGN KEY (`PostId`) REFERENCES `helpdeskposts` (`Id`) ON DELETE CASCADE;

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
