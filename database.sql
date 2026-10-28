-- =============================================================
--  Smart Tutor Hub — MySQL Database Schema
--  cPanel → phpMyAdmin → আপনার ডাটাবেস সিলেক্ট করুন → Import → এই ফাইল
--  (টেবিল/কলামের নাম পুরোনো PostgreSQL ডাটাবেসের মতোই রাখা হয়েছে)
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- Users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Users` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserName` VARCHAR(100) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `FullName` VARCHAR(200) NOT NULL DEFAULT '',
  `Email` VARCHAR(200) NOT NULL DEFAULT '',
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastLoginAt` DATETIME NULL,
  `Role` VARCHAR(20) NOT NULL DEFAULT 'User',
  `IsActive` TINYINT(1) NOT NULL DEFAULT 1,
  `MobileNumber` VARCHAR(30) NULL,
  `ProfileImage` VARCHAR(500) NULL,
  `Gender` VARCHAR(30) NULL,
  `AgeRange` VARCHAR(50) NULL,
  `PrimaryDeviceType` VARCHAR(50) NULL,
  `YearsOfExperience` VARCHAR(50) NULL,
  `AreaType` VARCHAR(50) NULL,
  `Country` VARCHAR(100) NULL,
  `StreetAddress` VARCHAR(500) NULL,
  `PermanentAddress` VARCHAR(500) NULL,
  `EducationLevel` VARCHAR(100) NULL,
  `CurrentStudyStatus` VARCHAR(20) NULL,
  `ExamDegreeTitle` VARCHAR(200) NULL,
  `InstitutionName` VARCHAR(300) NULL,
  `PassingYear` VARCHAR(10) NULL,
  `IsCSEStudent` TINYINT(1) NULL,
  `CvLink` VARCHAR(500) NULL,
  `GithubProfile` VARCHAR(500) NULL,
  `PortfolioLink` VARCHAR(500) NULL,
  `LinkedInProfile` VARCHAR(500) NULL,
  `ProfileImageLink` VARCHAR(500) NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_Users_UserName` (`UserName`),
  KEY `IX_Users_Email` (`Email`),
  KEY `IX_Users_Role` (`Role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TeacherRequests (Become a Tutor)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TeacherRequests` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `FullName` VARCHAR(200) NOT NULL DEFAULT '',
  `Email` VARCHAR(200) NOT NULL DEFAULT '',
  `MobileNumber` VARCHAR(30) NOT NULL DEFAULT '',
  `Education` VARCHAR(300) NOT NULL DEFAULT '',
  `Institution` VARCHAR(300) NOT NULL DEFAULT '',
  `SubjectExpertise` VARCHAR(500) NOT NULL DEFAULT '',
  `Experience` VARCHAR(300) NOT NULL DEFAULT '',
  `TeachingStyle` TEXT NULL,
  `AvailableDays` VARCHAR(200) NOT NULL DEFAULT '',
  `PreferredTime` VARCHAR(200) NOT NULL DEFAULT '',
  `HourlyRate` VARCHAR(50) NOT NULL DEFAULT '',
  `CvLink` VARCHAR(500) NOT NULL DEFAULT '',
  `WhyTeach` TEXT NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `RequestDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ResponseDate` DATETIME NULL,
  `AdminNote` TEXT NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_TeacherRequests_UserId` (`UserId`),
  KEY `IX_TeacherRequests_Status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TutorRequests (Student → Admin/Teacher)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TutorRequests` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `TutorId` VARCHAR(50) NULL,
  `TutorName` VARCHAR(200) NULL,
  `StudentName` VARCHAR(200) NOT NULL DEFAULT '',
  `PhoneNumber` VARCHAR(30) NULL,
  `WhatsAppNumber` VARCHAR(30) NULL,
  `Email` VARCHAR(200) NULL,
  `StudentClass` VARCHAR(200) NULL,
  `RequiredSubject` VARCHAR(300) NULL,
  `PreferredArea` VARCHAR(300) NULL,
  `TeachingMode` VARCHAR(50) NULL,
  `PreferredDays` VARCHAR(200) NULL,
  `PreferredTime` VARCHAR(200) NULL,
  `MonthlyBudget` VARCHAR(100) NULL,
  `AdditionalNotes` TEXT NULL,
  `RequestType` VARCHAR(50) NOT NULL DEFAULT 'SpecificTutor',
  `RequestId` VARCHAR(50) NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `AdminNote` TEXT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_TutorRequests_TutorName` (`TutorName`),
  KEY `IX_TutorRequests_Status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Notices (News & Announcements)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Notices` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Title` VARCHAR(500) NOT NULL DEFAULT '',
  `Content` MEDIUMTEXT NOT NULL,
  `Category` VARCHAR(50) NOT NULL DEFAULT 'News',
  `ImageUrl` VARCHAR(500) NOT NULL DEFAULT '',
  `PublishedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IsAnnouncement` TINYINT(1) NOT NULL DEFAULT 0,
  `IsActive` TINYINT(1) NOT NULL DEFAULT 1,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_Notices_Published` (`IsActive`, `PublishedDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Notifications
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Notifications` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `Title` VARCHAR(300) NULL,
  `Message` TEXT NULL,
  `Type` VARCHAR(20) NOT NULL DEFAULT 'info',
  `Icon` VARCHAR(50) NOT NULL DEFAULT 'fa-bell',
  `Link` VARCHAR(500) NULL,
  `IsRead` TINYINT(1) NOT NULL DEFAULT 0,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_Notifications_User` (`UserId`, `IsRead`, `CreatedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- SiteSettings (Admin → Settings)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `SiteSettings` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `HeroImageUrls` TEXT NULL,
  `TutorSectionImageUrl` VARCHAR(500) NULL,
  `SiteName` VARCHAR(200) NULL,
  `NavbarLogoUrl` VARCHAR(500) NULL,
  `FooterLogoUrl` VARCHAR(500) NULL,
  `LogoSizePx` INT NULL DEFAULT 56,
  `LogoShape` VARCHAR(20) NULL DEFAULT 'circle',
  `SliderEnabled` TINYINT(1) NOT NULL DEFAULT 0,
  `SliderIntervalSeconds` INT NOT NULL DEFAULT 5,
  `PrimaryColor` VARCHAR(20) NULL DEFAULT '#02B8F5',
  `SecondaryColor` VARCHAR(20) NULL DEFAULT '#F5FBFF',
  `AccentColor` VARCHAR(20) NULL DEFAULT '#FFB300',
  `TextColor` VARCHAR(20) NULL DEFAULT '#334155',
  `NavbarBgColor` VARCHAR(20) NULL DEFAULT '#134856',
  `FooterBgColor` VARCHAR(20) NULL DEFAULT '#08224A',
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- UserSuspensions (অ্যাডমিন ইউজারকে নির্দিষ্ট সময় বা স্থায়ীভাবে Suspend করে)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `UserSuspensions` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `Reason` TEXT NULL,
  `SuspendedUntil` DATETIME NULL,
  `IsPermanent` TINYINT(1) NOT NULL DEFAULT 0,
  `CreatedBy` INT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LiftedAt` DATETIME NULL,
  `LiftedBy` INT NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_UserSuspensions_User` (`UserId`, `LiftedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Reviews (স্টুডেন্ট/টিচারের রিভিউ — অ্যাডমিন Approve করলে হোম পেজে)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Reviews` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `Rating` TINYINT NOT NULL DEFAULT 5,
  `Subtitle` VARCHAR(120) NULL,
  `Content` TEXT NOT NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `AdminNote` TEXT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_Reviews_User` (`UserId`),
  KEY `IX_Reviews_Status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- SiteContents (অ্যাডমিন থেকে এডিট করা যায় এমন লেখা, যেমন Become a Tutor সেকশন)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `SiteContents` (
  `ContentKey` VARCHAR(100) NOT NULL,
  `ContentValue` TEXT NULL,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`ContentKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TuitionPosts (Admin → Tuition Circular)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TuitionPosts` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Title` VARCHAR(300) NOT NULL,
  `StudentGender` VARCHAR(100) NULL,
  `StudentClass` VARCHAR(200) NULL,
  `Subjects` VARCHAR(300) NULL,
  `DaysPerWeek` VARCHAR(50) NULL,
  `PreferredTime` VARCHAR(200) NULL,
  `Salary` VARCHAR(100) NULL,
  `Area` VARCHAR(300) NULL,
  `MediaFee` VARCHAR(200) NULL,
  `TuitionCode` VARCHAR(50) NULL,
  `SpecialNote` TEXT NULL,
  `TutorGenderPreference` VARCHAR(50) NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Open',
  `ShowOnHome` TINYINT(1) NOT NULL DEFAULT 0,
  `PostedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_TuitionPosts_Status` (`Status`, `PostedDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TuitionApplications
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TuitionApplications` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `TuitionPostId` INT NOT NULL,
  `UserId` INT NULL,
  `ApplicantName` VARCHAR(200) NOT NULL,
  `PhoneNumber` VARCHAR(30) NOT NULL,
  `WhatsAppNumber` VARCHAR(30) NULL,
  `Email` VARCHAR(200) NULL,
  `CvLink` VARCHAR(500) NOT NULL,
  `CoverNote` TEXT NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `AdminNote` TEXT NULL,
  `AppliedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_TuitionApplications_Post` (`TuitionPostId`),
  KEY `IX_TuitionApplications_User` (`UserId`),
  CONSTRAINT `FK_TuitionApplications_TuitionPosts` FOREIGN KEY (`TuitionPostId`) REFERENCES `TuitionPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TuitionCircularPosts (Student feed) + comments + likes
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TuitionCircularPosts` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `Content` TEXT NOT NULL,
  `ImageUrl` VARCHAR(500) NULL,
  `PreferredArea` VARCHAR(300) NULL,
  `ClassLevel` VARCHAR(200) NULL,
  `Subjects` VARCHAR(300) NULL,
  `Budget` VARCHAR(100) NULL,
  `LikeCount` INT NOT NULL DEFAULT 0,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_TuitionCircularPosts_User` (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `TuitionCircularComments` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `PostId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `Content` TEXT NOT NULL,
  `ParentId` INT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_TuitionCircularComments_Post` (`PostId`),
  CONSTRAINT `FK_TCC_Post` FOREIGN KEY (`PostId`) REFERENCES `TuitionCircularPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `TuitionCircularLikes` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `PostId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_TCL_PostUser` (`PostId`, `UserId`),
  CONSTRAINT `FK_TCL_Post` FOREIGN KEY (`PostId`) REFERENCES `TuitionCircularPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `TuitionCircularCommentLikes` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `CommentId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_TCCL_CommentUser` (`CommentId`, `UserId`),
  CONSTRAINT `FK_TCCL_Comment` FOREIGN KEY (`CommentId`) REFERENCES `TuitionCircularComments` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TuitionCircularApplications (টিচার → স্টুডেন্টের পোস্টে Apply)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TuitionCircularApplications` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `PostId` INT NOT NULL,
  `TeacherId` INT NOT NULL,
  `Message` TEXT NULL,
  `PhoneNumber` VARCHAR(30) NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_TCA_PostTeacher` (`PostId`, `TeacherId`),
  KEY `IX_TCA_Teacher` (`TeacherId`),
  CONSTRAINT `FK_TCA_Post` FOREIGN KEY (`PostId`) REFERENCES `TuitionCircularPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TeachWantedPosts (Teacher "পড়াতে চাই" feed) + comments + likes
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `TeachWantedPosts` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `Content` TEXT NOT NULL,
  `ImageUrl` VARCHAR(500) NULL,
  `LikeCount` INT NOT NULL DEFAULT 0,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_TeachWantedPosts_User` (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `TeachWantedComments` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `PostId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `Content` TEXT NOT NULL,
  `ParentId` INT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_TeachWantedComments_Post` (`PostId`),
  CONSTRAINT `FK_TWC_Post` FOREIGN KEY (`PostId`) REFERENCES `TeachWantedPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `TeachWantedLikes` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `PostId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_TWL_PostUser` (`PostId`, `UserId`),
  CONSTRAINT `FK_TWL_Post` FOREIGN KEY (`PostId`) REFERENCES `TeachWantedPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `TeachWantedCommentLikes` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `CommentId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UX_TWCL_CommentUser` (`CommentId`, `UserId`),
  CONSTRAINT `FK_TWCL_Comment` FOREIGN KEY (`CommentId`) REFERENCES `TeachWantedComments` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Helpdesk
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `HelpdeskPosts` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `UserId` INT NOT NULL,
  `Title` VARCHAR(300) NOT NULL,
  `Content` TEXT NOT NULL,
  `MediaUrl` VARCHAR(500) NULL,
  `MediaType` VARCHAR(20) NULL,
  `IsAdminPost` TINYINT(1) NOT NULL DEFAULT 0,
  `IsAcknowledged` TINYINT(1) NOT NULL DEFAULT 0,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Open',
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` DATETIME NULL,
  PRIMARY KEY (`Id`),
  KEY `IX_HelpdeskPosts_User` (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `HelpdeskComments` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `PostId` INT NOT NULL,
  `UserId` INT NOT NULL,
  `Content` TEXT NOT NULL,
  `MediaUrl` VARCHAR(500) NULL,
  `MediaType` VARCHAR(20) NULL,
  `IsAdminReply` TINYINT(1) NOT NULL DEFAULT 0,
  `ParentCommentId` INT NULL,
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IX_HelpdeskComments_Post` (`PostId`),
  CONSTRAINT `FK_HC_Post` FOREIGN KEY (`PostId`) REFERENCES `HelpdeskPosts` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
--  ডিফল্ট ডাটা
-- =============================================================

-- ডিফল্ট সাইট সেটিংস
INSERT INTO `SiteSettings` (`HeroImageUrls`, `TutorSectionImageUrl`, `SiteName`, `NavbarLogoUrl`, `FooterLogoUrl`,
  `LogoSizePx`, `LogoShape`, `SliderEnabled`, `SliderIntervalSeconds`, `PrimaryColor`, `SecondaryColor`, `AccentColor`,
  `TextColor`, `NavbarBgColor`, `FooterBgColor`)
SELECT '', 'https://i.ibb.co.com/Hfpwnx46/111.png', 'SMART TUTOR HUB', 'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png',
  'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png', 56, 'circle', 0, 5, '#02B8F5', '#F5FBFF', '#FFB300', '#334155', '#134856', '#08224A'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `SiteSettings`);

-- ডিফল্ট অ্যাডমিন অ্যাকাউন্ট
--   Username: admin
--   Password: Admin@123   ← প্রথমবার লগইন করেই অবশ্যই পরিবর্তন করবেন!
--   (প্রথম লগইনে পাসওয়ার্ডটি স্বয়ংক্রিয়ভাবে নিরাপদ hash এ রূপান্তরিত হবে)
INSERT INTO `Users` (`UserName`, `Password`, `FullName`, `Email`, `Role`, `IsActive`)
SELECT 'admin', 'Admin@123', 'Site Admin', 'admin@gmail.com', 'Admin', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `Users` WHERE `UserName` = 'admin');
