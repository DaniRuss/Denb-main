-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 14, 2026 at 04:51 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `callcenter`
--

-- --------------------------------------------------------

--
-- Table structure for table `action_types`
--

DROP TABLE IF EXISTS `action_types`;
CREATE TABLE IF NOT EXISTS `action_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_types_name_unique` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_am` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_en` text COLLATE utf8mb4_unicode_ci,
  `content_am` text COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_urgent` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `publish_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title_en`, `title_am`, `content_en`, `content_am`, `featured_image`, `is_urgent`, `is_active`, `publish_date`, `created_at`, `updated_at`) VALUES
(1, 'kkkk', 'kkk', '<table><tbody><tr><th rowspan=\"1\" colspan=\"1\"><p>ajjja</p></th><th rowspan=\"1\" colspan=\"1\"><p>kakkak</p></th><th rowspan=\"1\" colspan=\"1\"><p>kakkaka</p></th></tr><tr><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td></tr><tr><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td></tr><tr><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td></tr><tr><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td><td rowspan=\"1\" colspan=\"1\"><p></p></td></tr></tbody></table><p>hello there i am sam</p>', '<p>hello there i am sam</p>', 'announcements/01KK6RKQYWZ6TBG9N669Y671E0.jpg', 1, 1, '2026-03-08 16:01:16', '2026-03-08 10:01:31', '2026-03-08 10:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('hr-call-center-system-cache-livewire-rate-limiter:056fc329aaaa757d31db450f525da23fde4d1b36', 'i:1;', 1773462969),
('hr-call-center-system-cache-livewire-rate-limiter:056fc329aaaa757d31db450f525da23fde4d1b36:timer', 'i:1773462969;', 1773462969),
('hr-call-center-system-cache-site_settings', 'a:39:{s:20:\"organization_name_am\";s:47:\"የደንብ ማስከበር ባለስልጣን\";s:20:\"organization_name_en\";s:58:\"Addis Ababa City Administration Code Enforcement Authority\";s:10:\"tagline_am\";s:0:\"\";s:10:\"tagline_en\";s:0:\"\";s:14:\"footer_text_am\";s:42:\"መብቱ በህግ የተጠበቀ ነው\";s:14:\"footer_text_en\";s:19:\"All rights reserved\";s:14:\"copyright_text\";s:88:\"© 2024 Addis Ababa City Administration Code Enforcement Authority. All rights reserved.\";s:13:\"phone_primary\";s:16:\"+251 11 123 4567\";s:15:\"phone_secondary\";s:16:\"+251 11 765 4321\";s:13:\"email_primary\";s:26:\"info@lawenforcement.gov.et\";s:15:\"email_secondary\";s:29:\"support@lawenforcement.gov.et\";s:10:\"address_am\";s:38:\"አዲስ አበባ፣ ኢትዮጵያ\";s:10:\"address_en\";s:21:\"Addis Ababa, Ethiopia\";s:13:\"primary_color\";s:7:\"#0d6efd\";s:15:\"secondary_color\";s:7:\"#6c757d\";s:12:\"accent_color\";s:7:\"#198754\";s:13:\"hero_title_am\";s:84:\"እንኳን ወደ ደንብ ማስከበር ባለስልጣን በደህና መጡ\";s:13:\"hero_title_en\";s:69:\"Welcome to Addis Ababa City Administration Code Enforcement Authority\";s:16:\"hero_subtitle_am\";s:0:\"\";s:16:\"hero_subtitle_en\";s:0:\"\";s:5:\"stats\";s:439:\"[{\"label_am\":\"\\u1320\\u1245\\u120b\\u120b \\u1230\\u122b\\u1270\\u129e\\u127d\",\"label_en\":\"Total Employees\",\"value\":\"1500\"},{\"label_am\":\"\\u1353\\u122b \\u121a\\u120a\\u1270\\u122a \\u12a6\\u134a\\u1230\\u122e\\u127d\",\"label_en\":\"Para Military Officers\",\"value\":\"850\"},{\"label_am\":\"\\u1232\\u126a\\u120d \\u1230\\u122b\\u1270\\u129e\\u127d\",\"label_en\":\"Civil Employees\",\"value\":\"450\"},{\"label_am\":\"\\u12c8\\u1228\\u12f3\\u12ce\\u127d\",\"label_en\":\"Woredas\",\"value\":\"120\"}]\";s:13:\"working_hours\";s:246:\"[{\"days_am\":\"\\u1230\\u129e - \\u12d3\\u122d\\u1265\",\"days_en\":\"Monday - Friday\",\"hours\":\"8:30 - 17:30\"},{\"days_am\":\"\\u1245\\u12f3\\u121c\",\"days_en\":\"Saturday\",\"hours\":\"8:30 - 12:30\"},{\"days_am\":\"\\u12a5\\u1201\\u12f5\",\"days_en\":\"Sunday\",\"hours\":\"Closed\"}]\";s:10:\"site_title\";s:50:\"የደንብ ማስከበር ባለስልጣን | \";s:16:\"meta_description\";s:138:\"Official portal of the A.A City Administration Code Enforcement Authority - Submit complaints, report tips, and access public information.\";s:13:\"meta_keywords\";s:84:\"law enforcement, complaint, tip, illegal trade, land grabbing, Ethiopia, Addis Ababa\";s:17:\"enable_complaints\";s:1:\"1\";s:11:\"enable_tips\";s:1:\"1\";s:20:\"enable_announcements\";s:1:\"1\";s:10:\"enable_faq\";s:1:\"1\";s:19:\"enable_contact_form\";s:1:\"1\";s:17:\"enable_newsletter\";s:1:\"1\";s:16:\"maintenance_mode\";s:0:\"\";s:10:\"logo_light\";s:40:\"site/logo/01KK4SE9XKJ7JDGHBD8KTGYPN3.jpg\";s:9:\"logo_dark\";s:40:\"site/logo/01KK4SE9YC4T2QDJDJH9HMJ64Y.jpg\";s:7:\"favicon\";s:43:\"site/favicon/01KK4ZVMDHDH10VV94E6GM05JD.png\";s:8:\"og_image\";s:38:\"site/og/01KK4ZVMDZWXEVBMSTGX7N6Y2N.png\";s:19:\"hero_description_en\";s:136:\"A secure, transparent platform to submit complaints, report illegal activities anonymously, and track case statuses. Your voice matters.\";s:15:\"hero_tagline_am\";s:124:\"ቅሬታዎን ያስገቡ ● ህገ-ወጥ ስራዎችን ሪፖርት ያድርጉ ● ጉዳይዎን ይከታተሉ\";s:4:\"faqs\";s:2916:\"[{\"question_am\":\"\\u1245\\u122c\\u1273 \\u12a5\\u1295\\u12f4\\u1275 \\u121b\\u1245\\u1228\\u1265 \\u12a5\\u127d\\u120b\\u1208\\u1201?\",\"question_en\":\"How do I submit a complaint?\",\"answer_am\":\"\\u1260\\u121d\\u1293\\u120c\\u12cd \\u12cd\\u1235\\u1325 \\\"\\u1245\\u122c\\u1273 \\u12a0\\u1245\\u122d\\u1265\\\" \\u12e8\\u121a\\u1208\\u12cd\\u1295 \\u12ed\\u132b\\u1291\\u1362 \\u12e8\\u130d\\u120d \\u12dd\\u122d\\u12dd\\u122e\\u127d\\u12ce\\u1295 \\u12ed\\u1219\\u1209\\u1363 \\u1245\\u122c\\u1273\\u12ce\\u1295 \\u12eb\\u1265\\u122b\\u1229 \\u12a5\\u1293 \\u12a5\\u1295\\u12f0 \\u121d\\u122d\\u132b\\u12ce \\u12f0\\u130b\\u134a \\u134b\\u12ed\\u120e\\u127d\\u1295 \\u12eb\\u12eb\\u12ed\\u12d9\\u1362 \\u1309\\u12f3\\u12ed\\u12ce\\u1295 \\u1208\\u1218\\u12a8\\u1273\\u1270\\u120d \\u12e8\\u121a\\u12eb\\u1235\\u127d\\u120d \\u120d\\u12e9 \\u12e8\\u1272\\u12ac\\u1275 \\u1241\\u1325\\u122d \\u12ed\\u12f0\\u122d\\u1235\\u12ce\\u1273\\u120d\\u1362\",\"answer_en\":\"Click \\\"Submit Complaint\\\" in the menu. Fill in your personal details, describe your complaint, and optionally attach supporting files. You\'ll receive a unique ticket number to track your case.\"},{\"question_am\":\"\\u1325\\u1246\\u121b \\u1235\\u1230\\u1325 \\u121b\\u1295\\u1290\\u1274 \\u12ed\\u1320\\u1260\\u1243\\u120d?\",\"question_en\":\"Is my identity protected when reporting a tip?\",\"answer_am\":\"\\u12a0\\u12ce\\u1362 \\u1235\\u121d-\\u12a0\\u120d\\u1263 \\u12e8\\u1325\\u1246\\u121b \\u121b\\u1245\\u1228\\u1262\\u12eb\\u12ce\\u127d \\u12e8\\u130d\\u120d \\u1218\\u1228\\u1303 \\u12a0\\u12eb\\u1235\\u1348\\u120d\\u130b\\u1278\\u12cd\\u121d\\u1362 \\u1208\\u1218\\u12a8\\u1273\\u1270\\u12eb \\u12a0\\u1308\\u120d\\u130d\\u120e\\u1275 \\u12e8\\u121a\\u12cd\\u120d \\u12e8\\u1218\\u12f3\\u1228\\u123b \\u12ae\\u12f5 \\u12ed\\u1348\\u1320\\u122b\\u120d\\u1362 \\u121b\\u1295\\u1290\\u1275\\u12ce \\u1219\\u1209 \\u1260\\u1219\\u1209 \\u121a\\u1235\\u1325\\u122b\\u12ca \\u1206\\u1296 \\u12ed\\u1246\\u12eb\\u120d\\u1362\",\"answer_en\":\"Yes. Anonymous tip submissions do not require personal information. An access token is generated for tracking purposes. Your identity remains completely confidential.\"},{\"question_am\":\"\\u1245\\u122c\\u1273\\u1295 \\u1208\\u121b\\u1235\\u1270\\u1293\\u1308\\u12f5 \\u121d\\u1295 \\u12eb\\u1205\\u120d \\u130a\\u12dc \\u12ed\\u12c8\\u1235\\u12f3\\u120d?\",\"question_en\":\"How long does it take to process a complaint?\",\"answer_am\":\"\\u12e8\\u1218\\u1300\\u1218\\u122a\\u12eb \\u12f0\\u1228\\u1303 \\u130d\\u121d\\u1308\\u121b \\u12a81-3 \\u12e8\\u1235\\u122b \\u1240\\u1293\\u1275 \\u12ed\\u12c8\\u1235\\u12f3\\u120d\\u1362 \\u12cd\\u1235\\u1265\\u1235\\u1265 \\u1309\\u12f3\\u12ee\\u127d \\u1228\\u12d8\\u121d \\u12eb\\u1208 \\u130a\\u12dc \\u120a\\u12c8\\u1235\\u12f1 \\u12ed\\u127d\\u120b\\u1209\\u1362 \\u12e8\\u1272\\u12ac\\u1275 \\u1241\\u1325\\u122d\\u12ce\\u1295 \\u1260\\u1218\\u1320\\u1240\\u121d \\u1260\\u121b\\u1295\\u129b\\u12cd\\u121d \\u130a\\u12dc \\u12e8\\u1309\\u12f3\\u12ed\\u12ce\\u1295 \\u1201\\u1294\\u1273 \\u1218\\u12a8\\u1273\\u1270\\u120d \\u12ed\\u127d\\u120b\\u1209\\u1362\",\"answer_en\":\"Initial review takes 1\\u20133 business days. Complex cases may take longer. You can track your case status at any time using your ticket number.\"}]\";}', 1773405054),
('hr-call-center-system-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:16:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"manage_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"view_complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:17:\"manage_complaints\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"assign_cases\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"report_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"view_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"manage_inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"create_call_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:18:\"view_own_call_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:27:\"review_supervisor_call_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:25:\"review_director_call_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:25:\"manage_sub_city_call_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:24:\"manage_call_tip_workflow\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:21:\"manage_penalty_action\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:7;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:23:\"manage_woreda_call_tips\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}}s:5:\"roles\";a:8:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"supervisor\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"officer\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:19:\"call_record_officer\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:20:\"call_center_director\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"sub_city_officer\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:22:\"penalty_action_officer\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:14:\"woreda_officer\";s:1:\"c\";s:3:\"web\";}}}', 1773489447);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_assignments`
--

DROP TABLE IF EXISTS `case_assignments`;
CREATE TABLE IF NOT EXISTS `case_assignments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `caseable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caseable_id` bigint UNSIGNED NOT NULL,
  `assigned_by` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `assignment_type` enum('primary','supporting','reviewer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `assignment_notes` text COLLATE utf8mb4_unicode_ci,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deadline` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','completed','reassigned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `complaint_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_assignments_caseable_type_caseable_id_index` (`caseable_type`,`caseable_id`),
  KEY `case_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `case_assignments_assigned_to_foreign` (`assigned_to`),
  KEY `case_assignments_department_id_foreign` (`department_id`),
  KEY `case_assignments_complaint_id_foreign` (`complaint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_communications`
--

DROP TABLE IF EXISTS `case_communications`;
CREATE TABLE IF NOT EXISTS `case_communications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `caseable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caseable_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `direction` enum('incoming','outgoing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` enum('email','phone','portal','in_person') COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_communications_caseable_type_caseable_id_index` (`caseable_type`,`caseable_id`),
  KEY `case_communications_user_id_foreign` (`user_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `case_updates`
--

DROP TABLE IF EXISTS `case_updates`;
CREATE TABLE IF NOT EXISTS `case_updates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `caseable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caseable_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `update_type` enum('status_change','assignment','investigation_note','resolution','public_update','internal_note') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible to complainant',
  `notify_complainant` tinyint(1) NOT NULL DEFAULT '0',
  `notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_updates_caseable_type_caseable_id_index` (`caseable_type`,`caseable_id`),
  KEY `case_updates_user_id_foreign` (`user_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique tracking ID',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'National ID if available',
  `address` text COLLATE utf8mb4_unicode_ci,
  `complaint_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complaint_type_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'If other is selected',
  `incident_date` date DEFAULT NULL,
  `incident_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `officer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Name of officer involved',
  `officer_badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Badge number if known',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `evidence_description` text COLLATE utf8mb4_unicode_ci,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Multiple file paths',
  `confiscated_items` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Items confiscated',
  `confiscated_value` decimal(10,2) DEFAULT NULL,
  `confiscation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confiscation_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `assigned_department` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `investigation_notes` text COLLATE utf8mb4_unicode_ci,
  `resolution` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint UNSIGNED DEFAULT NULL,
  `last_viewed_by_complainant` timestamp NULL DEFAULT NULL,
  `view_count` int NOT NULL DEFAULT '0',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `complaints_ticket_number_unique` (`ticket_number`),
  KEY `complaints_assigned_to_foreign` (`assigned_to`),
  KEY `complaints_assigned_department_foreign` (`assigned_department`),
  KEY `complaints_resolved_by_foreign` (`resolved_by`),
  KEY `complaints_ticket_number_index` (`ticket_number`),
  KEY `complaints_email_index` (`email`),
  KEY `complaints_status_index` (`status`),
  KEY `complaints_priority_index` (`priority`)
) ;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `ticket_number`, `full_name`, `email`, `phone`, `id_number`, `address`, `complaint_type`, `complaint_type_other`, `incident_date`, `incident_location`, `officer_name`, `officer_badge`, `description`, `evidence_description`, `attachments`, `confiscated_items`, `confiscated_value`, `confiscation_reason`, `confiscation_location`, `priority`, `status`, `assigned_to`, `assigned_department`, `assigned_at`, `investigation_notes`, `resolution`, `resolved_at`, `resolved_by`, `last_viewed_by_complainant`, `view_count`, `is_anonymous`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CMP-20260308-113377', 'John Doe', 'john.doe@example.com', '+251 91 234 5678', NULL, 'Bole, Woreda 03, House 123', 'fraud', NULL, '2024-05-20', 'Bole Market', NULL, NULL, 'This is a test complaint regarding a potential fraud case in the market area.', NULL, NULL, NULL, NULL, NULL, NULL, 'critical', 'in_progress', 2, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-08 19:06:27', 4, 0, '2026-03-08 18:53:54', '2026-03-08 19:06:27', NULL),
(2, 'CMP-20260308-684199', 'Abdulkerim', 'admin@example.com', '0922878608', NULL, NULL, 'fraud', NULL, '2026-03-08', 'bole', NULL, NULL, 'jkhjhjjhjh', NULL, '\"[\\\"complaints\\\\\\/2026\\\\\\/03\\\\\\/08\\\\\\/1773006984_69adf0882d072.docx\\\"]\"', NULL, NULL, NULL, NULL, 'medium', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-08 18:56:31', 1, 0, '2026-03-08 18:56:24', '2026-03-08 18:56:31', NULL),
(3, 'CMP-20260309-106086', 'አብዱልከሪም random', 'ak47seid@gmail.com', '+251922878608', NULL, 'Addis Ababa', 'fraud', NULL, '2026-03-09', 'bole', NULL, NULL, 'gg', NULL, NULL, NULL, NULL, NULL, NULL, 'high', 'in_progress', 3, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-09 09:30:38', 3, 0, '2026-03-09 09:19:54', '2026-03-09 09:30:38', NULL),
(4, 'CMP-20260310-280486', 'eeee', 'Man@gmail.com', '0933221122', NULL, NULL, 'misconduct', NULL, '2026-03-10', 'AA', NULL, NULL, 'wgwfvf', NULL, NULL, NULL, NULL, NULL, NULL, 'medium', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-10 10:40:15', 2, 0, '2026-03-10 10:39:08', '2026-03-10 10:40:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_am` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `head_of_department_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`),
  KEY `departments_head_of_department_id_foreign` (`head_of_department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name_am` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name_am` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_city_id` bigint UNSIGNED NOT NULL,
  `woreda_id` bigint UNSIGNED NOT NULL,
  `kebele` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `house_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'የስራ መደብ',
  `rank` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_type` enum('para_military_officer','civil_employee','district_para_military') COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `hire_date` date NOT NULL,
  `birth_date` date NOT NULL,
  `birthplace` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'የትውልድ ቦታ',
  `education_level` enum('below_12','complete_12','certificate','diploma','degree','masters','phd') COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_of_study` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ethio_coder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shirt_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pant_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shoe_size_casual` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shoe_size_leather` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hat_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cloth_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rain_cloth_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jacket_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `t_shirt_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `training_round` int DEFAULT NULL COMMENT '1ኛ, 2ኛ, 3ኛ, 4ኛ, 5ኛ, 6ኛ ዙር',
  `last_training_date` date DEFAULT NULL,
  `training_notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','suspended','on_leave','retired','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_suspended_payment` tinyint(1) NOT NULL DEFAULT '0',
  `suspension_reason` text COLLATE utf8mb4_unicode_ci,
  `suspension_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_id_unique` (`employee_id`),
  UNIQUE KEY `employees_email_unique` (`email`),
  UNIQUE KEY `employees_national_id_unique` (`national_id`),
  KEY `employees_sub_city_id_foreign` (`sub_city_id`),
  KEY `employees_woreda_id_foreign` (`woreda_id`),
  KEY `employees_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `first_name_am`, `last_name_am`, `first_name_en`, `last_name_en`, `gender`, `age`, `email`, `phone`, `emergency_contact`, `sub_city_id`, `woreda_id`, `kebele`, `house_number`, `position`, `rank`, `employee_type`, `salary`, `hire_date`, `birth_date`, `birthplace`, `education_level`, `field_of_study`, `institution`, `national_id`, `ethio_coder`, `shirt_size`, `pant_size`, `shoe_size_casual`, `shoe_size_leather`, `hat_size`, `cloth_size`, `rain_cloth_size`, `jacket_size`, `t_shirt_size`, `training_round`, `last_training_date`, `training_notes`, `status`, `is_suspended_payment`, `suspension_reason`, `suspension_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'CO2211', 'Daniel', 'Russom', NULL, NULL, 'male', 55, 'dani@gmail.com', '0933229931', '0933229930', 12, 125, '19', NULL, 'code', 'officer', 'civil_employee', 55000.00, '2026-03-09', '2019-01-09', 'A.A', 'certificate', 'noting ', 'africa', '77888322010', NULL, 'M', '30', '42', '44', '10', 'M', 'M', 'M', 'M', 5, '2026-03-09', 'good', 'active', 0, NULL, NULL, '2026-03-09 17:08:46', '2026-03-09 17:08:46', NULL),
(2, 8, 'CO2212', 'አብዱከሪም ', 'ሰይድ', 'Abdulekerim', 'Seid', 'male', 29, 'abdul@gmail.com', '0933229938', '0933229', 12, 125, '11', NULL, 'Officer', 'officer', 'para_military_officer', 50000.00, '2017-11-11', '2019-10-22', 'A.A', 'certificate', NULL, NULL, '33222', NULL, 'M', 'M', '40', '40', '10', 'M', 'M', 'M', 'M', 3, '2017-10-11', NULL, 'active', 0, NULL, NULL, '2026-03-10 02:19:07', '2026-03-10 02:36:04', NULL),
(3, 12, 'CO2213', 'ከድር', 'ከተማ', 'kedir', 'ketema', 'male', 40, 'Kedir1@gmail.com', '0933229938', '0933229939', 11, 112, '19', '821', 'code', 'officer', 'para_military_officer', 8000.00, '2025-08-01', '2016-01-03', 'AA', 'diploma', 'CS', 'AA', '33228', NULL, 'M', 'M', '40', '44', '10', 'M', 'M', 'M', 'M', 4, '2025-11-01', NULL, 'active', 0, NULL, NULL, '2026-03-13 05:04:45', '2026-03-13 05:04:45', NULL),
(4, 13, 'CO2214', 'Officer one ', 'two ', NULL, NULL, 'female', 65, 'Office@gmail.com', '0977559933', '99448877', 12, 130, '1', NULL, 'code', 'officer', 'para_military_officer', 8999.00, '2026-03-01', '2021-01-14', 'A.A', 'below_12', NULL, NULL, '110099', NULL, 'M', 'm', 'm', '40', '10', 'm', 'm', 'm', 'm', 2, '2026-03-01', NULL, 'active', 0, NULL, NULL, '2026-03-14 01:34:31', '2026-03-14 01:34:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_discipline_histories`
--

DROP TABLE IF EXISTS `employee_discipline_histories`;
CREATE TABLE IF NOT EXISTS `employee_discipline_histories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint UNSIGNED NOT NULL,
  `discipline_date` date NOT NULL,
  `discipline_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_taken` text COLLATE utf8mb4_unicode_ci,
  `duration_days` int UNSIGNED DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_discipline_histories_recorded_by_foreign` (`recorded_by`),
  KEY `employee_discipline_histories_employee_id_discipline_date_index` (`employee_id`,`discipline_date`),
  KEY `employee_discipline_histories_discipline_type_status_index` (`discipline_type`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_discipline_histories`
--

INSERT INTO `employee_discipline_histories` (`id`, `employee_id`, `discipline_date`, `discipline_type`, `description`, `action_taken`, `duration_days`, `status`, `recorded_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2026-03-10', 'verbal_warning', 'test Discipline', NULL, NULL, 'active', 2, '2026-03-10 01:15:57', '2026-03-10 01:15:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `escalations`
--

DROP TABLE IF EXISTS `escalations`;
CREATE TABLE IF NOT EXISTS `escalations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `caseable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caseable_id` bigint UNSIGNED NOT NULL,
  `escalated_by` bigint UNSIGNED NOT NULL,
  `escalated_to` bigint UNSIGNED NOT NULL,
  `from_level` int NOT NULL,
  `to_level` int NOT NULL,
  `reason` enum('timeout','complexity','sensitivity','conflict_of_interest','requires_approval','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason_details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `escalated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `responded_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `complaint_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `escalations_caseable_type_caseable_id_index` (`caseable_type`,`caseable_id`),
  KEY `escalations_escalated_by_foreign` (`escalated_by`),
  KEY `escalations_escalated_to_foreign` (`escalated_to`),
  KEY `escalations_complaint_id_foreign` (`complaint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `escalation_levels`
--

DROP TABLE IF EXISTS `escalation_levels`;
CREATE TABLE IF NOT EXISTS `escalation_levels` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int NOT NULL,
  `response_time_hours` int NOT NULL COMMENT 'Max hours for response',
  `resolution_time_hours` int NOT NULL COMMENT 'Max hours for resolution',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `escalation_levels_level_unique` (`level`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follow_up_actions`
--

DROP TABLE IF EXISTS `follow_up_actions`;
CREATE TABLE IF NOT EXISTS `follow_up_actions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `incident_report_id` bigint UNSIGNED NOT NULL,
  `action_type_id` bigint UNSIGNED NOT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `follow_up_actions_assigned_by_foreign` (`assigned_by`),
  KEY `follow_up_actions_assigned_to_foreign` (`assigned_to`),
  KEY `follow_up_actions_incident_report_id_status_index` (`incident_report_id`,`status`),
  KEY `follow_up_actions_action_type_id_status_index` (`action_type_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

DROP TABLE IF EXISTS `incident_reports`;
CREATE TABLE IF NOT EXISTS `incident_reports` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint UNSIGNED NOT NULL,
  `incident_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incident_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reported',
  `reported_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incident_reports_reported_by_foreign` (`reported_by`),
  KEY `incident_reports_employee_id_incident_date_index` (`employee_id`,`incident_date`),
  KEY `incident_reports_incident_type_status_index` (`incident_type`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000001_create_sub_cities_table', 1),
(5, '2024_01_01_000002_create_employees_table', 1),
(6, '2024_01_01_000003_create_departments_table', 1),
(7, '2024_01_01_000004_create_complaints_table', 1),
(8, '2024_01_01_000005_create_tips_table', 1),
(9, '2024_01_01_000006_create_case_updates_table', 1),
(10, '2024_01_01_000007_create_case_assignments_table', 1),
(11, '2024_01_01_000008_create_escalations_table', 1),
(12, '2024_01_01_000009_create_uniform_inventories_table', 1),
(13, '2024_01_01_000010_create_quarterly_reports_table', 1),
(14, '2026_03_06_204708_create_permission_tables', 1),
(15, '2026_03_07_000006_sync_existing_tables_with_models', 2),
(16, '2026_03_07_000007_create_site_settings_table', 3),
(17, '2026_03_08_122436_create_announcements_table', 4),
(18, '2026_03_08_214456_update_complaints_and_tips_enums', 5),
(19, '2026_03_08_224030_add_evidence_description_to_complaints_table', 6),
(20, '2026_03_09_090000_add_call_tip_workflow_fields_to_tips_table', 7),
(21, '2026_03_09_090100_add_sub_city_to_users_table', 7),
(22, '2026_03_10_000001_create_employee_discipline_histories_table', 8),
(23, '2026_03_10_000002_add_user_id_to_employees_table', 9),
(24, '2026_03_10_000003_create_penalty_action_management_tables', 10),
(25, '2026_03_10_120000_add_username_to_users_table', 11),
(26, '2026_03_13_061859_change_uniform_size_columns_to_string', 12),
(27, '2026_03_13_114146_add_woreda_to_users_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 6),
(6, 'App\\Models\\User', 7),
(3, 'App\\Models\\User', 9),
(7, 'App\\Models\\User', 9),
(7, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(7, 'App\\Models\\User', 13);

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

DROP TABLE IF EXISTS `officers`;
CREATE TABLE IF NOT EXISTS `officers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `badge_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rank` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rank_am` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialization` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','on_leave','suspended','retired','transferred') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `date_joined` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `case_load_limit` int NOT NULL DEFAULT '10',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `officers_badge_number_unique` (`badge_number`),
  KEY `officers_user_id_foreign` (`user_id`),
  KEY `officers_department_id_foreign` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalty_assignments`
--

DROP TABLE IF EXISTS `penalty_assignments`;
CREATE TABLE IF NOT EXISTS `penalty_assignments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `incident_report_id` bigint UNSIGNED NOT NULL,
  `penalty_type_id` bigint UNSIGNED NOT NULL,
  `assigned_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `duration_days` int UNSIGNED DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penalty_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `penalty_assignments_assigned_to_foreign` (`assigned_to`),
  KEY `penalty_assignments_incident_report_id_assigned_date_index` (`incident_report_id`,`assigned_date`),
  KEY `penalty_assignments_penalty_type_id_status_index` (`penalty_type_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalty_types`
--

DROP TABLE IF EXISTS `penalty_types`;
CREATE TABLE IF NOT EXISTS `penalty_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `default_duration_days` int UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penalty_types_name_unique` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage_users', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(2, 'manage_roles', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(3, 'view_complaints', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(4, 'manage_complaints', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(5, 'assign_cases', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(6, 'report_tips', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(7, 'view_reports', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(8, 'manage_inventory', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(9, 'create_call_tips', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(10, 'view_own_call_tips', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(11, 'review_supervisor_call_tips', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(12, 'review_director_call_tips', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(13, 'manage_sub_city_call_tips', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(14, 'manage_call_tip_workflow', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(15, 'manage_penalty_action', 'web', '2026-03-10 01:29:26', '2026-03-10 01:29:26'),
(16, 'manage_woreda_call_tips', 'web', '2026-03-13 08:43:20', '2026-03-13 08:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `quarterly_reports`
--

DROP TABLE IF EXISTS `quarterly_reports`;
CREATE TABLE IF NOT EXISTS `quarterly_reports` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int NOT NULL,
  `quarter` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `total_complaints` int UNSIGNED NOT NULL DEFAULT '0',
  `resolved_complaints` int UNSIGNED NOT NULL DEFAULT '0',
  `pending_complaints` int UNSIGNED NOT NULL DEFAULT '0',
  `total_tips` int UNSIGNED NOT NULL DEFAULT '0',
  `verified_tips` int UNSIGNED NOT NULL DEFAULT '0',
  `total_escalations` int UNSIGNED NOT NULL DEFAULT '0',
  `sub_city_id` bigint UNSIGNED NOT NULL,
  `report_type` enum('para_military','civil_employees','uniform_summary','training_summary','complaint_summary','tip_summary') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `prepared_by` bigint UNSIGNED NOT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','under_review','approved','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `report_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quarterly_reports_sub_city_id_foreign` (`sub_city_id`),
  KEY `quarterly_reports_prepared_by_foreign` (`prepared_by`),
  KEY `quarterly_reports_approved_by_foreign` (`approved_by`),
  KEY `quarterly_reports_department_id_foreign` (`department_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(2, 'supervisor', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(3, 'officer', 'web', '2026-03-06 19:01:10', '2026-03-06 19:01:10'),
(4, 'call_record_officer', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(5, 'call_center_director', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(6, 'sub_city_officer', 'web', '2026-03-09 17:18:52', '2026-03-09 17:18:52'),
(7, 'penalty_action_officer', 'web', '2026-03-10 01:29:27', '2026-03-10 01:29:27'),
(8, 'woreda_officer', 'web', '2026-03-13 08:43:20', '2026-03-13 08:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(3, 2),
(4, 2),
(5, 2),
(7, 2),
(11, 2),
(3, 3),
(4, 3),
(9, 4),
(10, 4),
(12, 5),
(13, 6),
(15, 7),
(16, 8);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('BOeVElgEtlWxI7ID1aJLodhhJHz7NOrPmHh50ebr', 13, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRkZmd04yQVJURmZuTUhJYkNucFNma2NOVEwyMTl4b2JLdm84RVViQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9lbXBsb3llZXMiO3M6NToicm91dGUiO3M6NDA6ImZpbGFtZW50LmFkbWluLnJlc291cmNlcy5lbXBsb3llZXMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNGY2MjRhNDM4YTZhZTc2Yjc3YzI2OTU1ZTRhMGNlYTljM2ZlYmQ1Y2E2YzI4NTU2YWJmMjIzYTk3ZTlhYzAyMiI7czo2OiJ0YWJsZXMiO2E6NDp7czo0MDoiOTE4MDg5NjdiYmNlMTY4Y2E0ZGQ5MGVmMjExZjUwN2ZfY29sdW1ucyI7YTo2OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MjM6ImNvbXBsYWludC50aWNrZXRfbnVtYmVyIjtzOjU6ImxhYmVsIjtzOjg6IlRpY2tldCAjIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNjoiZXNjYWxhdGVkQnkubmFtZSI7czo1OiJsYWJlbCI7czoxMjoiRXNjYWxhdGVkIEJ5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNjoiZXNjYWxhdGVkVG8ubmFtZSI7czo1OiJsYWJlbCI7czoxMjoiRXNjYWxhdGVkIFRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJsZXZlbCI7czo1OiJsYWJlbCI7czo1OiJMZXZlbCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Njoic3RhdHVzIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTI6IkVzY2FsYXRlZCBPbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiNWRhOTZlMGM4YjY4ZmQ0MzIyZGZlOWU4OWFiNjlmOTZfY29sdW1ucyI7YTo3OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6ImJhZGdlX251bWJlciI7czo1OiJsYWJlbCI7czo3OiJCYWRnZSAjIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJ1c2VyLm5hbWUiO3M6NToibGFiZWwiO3M6MTI6Ik9mZmljZXIgTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoicmFuayI7czo1OiJsYWJlbCI7czo0OiJSYW5rIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxODoiZGVwYXJ0bWVudC5uYW1lX2VuIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoic3BlY2lhbGl6YXRpb24iO3M6NToibGFiZWwiO3M6MTQ6IlNwZWNpYWxpemF0aW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiZGF0ZV9qb2luZWQiO3M6NToibGFiZWwiO3M6NjoiSm9pbmVkIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiIwZjBhNzA0MjgwNzNmZjI0OTllYWIzNWFlYTg0OWQ4OF9jb2x1bW5zIjthOjY6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo0OiJuYW1lIjtzOjU6ImxhYmVsIjtzOjQ6Ik5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6ImVtYWlsIjtzOjU6ImxhYmVsIjtzOjU6IkVtYWlsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoicm9sZXMubmFtZSI7czo1OiJsYWJlbCI7czo1OiJSb2xlcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6ODoic3ViX2NpdHkiO3M6NToibGFiZWwiO3M6ODoiU3ViIENpdHkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Njoid29yZWRhIjtzOjU6ImxhYmVsIjtzOjY6IldvcmVkYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMDoiQ3JlYXRlZCBhdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fX1zOjQwOiIxMDhjY2I3MmRkYzVlNzA5M2QwZDFiODY1MDM2MTM2M19jb2x1bW5zIjthOjc6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiZW1wbG95ZWVfaWQiO3M6NToibGFiZWwiO3M6MjoiSUQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJmdWxsX25hbWVfYW0iO3M6NToibGFiZWwiO3M6MTk6Ik5hbWUgKOGKoOGIm+GIreGKmykiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJmdWxsX25hbWVfZW4iO3M6NToibGFiZWwiO3M6MTQ6Ik5hbWUgKEVuZ2xpc2gpIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjg6InBvc2l0aW9uIjtzOjU6ImxhYmVsIjtzOjg6IlBvc2l0aW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJwaG9uZSI7czo1OiJsYWJlbCI7czo1OiJQaG9uZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Njoic3RhdHVzIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToiaGlyZV9kYXRlIjtzOjU6ImxhYmVsIjtzOjk6IkhpcmUgZGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fX0=', 1773462991),
('kSDgT7ySga7tZcnMhIZOKuvWx1v3hQ4ULTDBBNmv', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSm4yOW1MWXdzQnBVZmNSMTd3cTNlRHk2c2c1bVJhcGxxYkV3Q1ppbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9lbXBsb3llZXMiO3M6NToicm91dGUiO3M6NDA6ImZpbGFtZW50LmFkbWluLnJlc291cmNlcy5lbXBsb3llZXMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMjM4ZDdlNDNmZGMxYWIxYzZiMjgzODViMjdiZTNkNjlmZWNhOTE1NDM4ZGMwNDE0ZWRkMzE1MTYzMWY5ODkwYSI7czo2OiJ0YWJsZXMiO2E6NTp7czo0MDoiZjA5ZjEyMTJmNDc4YzZlZTJhM2FjMzJhZTU2OTgxZGFfY29sdW1ucyI7YTo3OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6InRpY2tldF9udW1iZXIiO3M6NToibGFiZWwiO3M6ODoiVGlja2V0ICMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6ImZ1bGxfbmFtZSI7czo1OiJsYWJlbCI7czoxMjoiQ2l0aXplbiBOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoiY29tcGxhaW50X3R5cGUiO3M6NToibGFiZWwiO3M6NDoiVHlwZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Njoic3RhdHVzIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6ODoicHJpb3JpdHkiO3M6NToibGFiZWwiO3M6ODoiUHJpb3JpdHkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJhc3NpZ25lZFRvLm5hbWUiO3M6NToibGFiZWwiO3M6MTE6IkFzc2lnbmVkIFRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czo5OiJTdWJtaXR0ZWQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjowO319czo0MDoiNWRhOTZlMGM4YjY4ZmQ0MzIyZGZlOWU4OWFiNjlmOTZfY29sdW1ucyI7YTo3OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6ImJhZGdlX251bWJlciI7czo1OiJsYWJlbCI7czo3OiJCYWRnZSAjIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJ1c2VyLm5hbWUiO3M6NToibGFiZWwiO3M6MTI6Ik9mZmljZXIgTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoicmFuayI7czo1OiJsYWJlbCI7czo0OiJSYW5rIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxODoiZGVwYXJ0bWVudC5uYW1lX2VuIjtzOjU6ImxhYmVsIjtzOjEwOiJEZXBhcnRtZW50IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoic3BlY2lhbGl6YXRpb24iO3M6NToibGFiZWwiO3M6MTQ6IlNwZWNpYWxpemF0aW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiZGF0ZV9qb2luZWQiO3M6NToibGFiZWwiO3M6NjoiSm9pbmVkIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiIxMDhjY2I3MmRkYzVlNzA5M2QwZDFiODY1MDM2MTM2M19jb2x1bW5zIjthOjc6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiZW1wbG95ZWVfaWQiO3M6NToibGFiZWwiO3M6MjoiSUQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJmdWxsX25hbWVfYW0iO3M6NToibGFiZWwiO3M6MTk6Ik5hbWUgKOGKoOGIm+GIreGKmykiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJmdWxsX25hbWVfZW4iO3M6NToibGFiZWwiO3M6MTQ6Ik5hbWUgKEVuZ2xpc2gpIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjg6InBvc2l0aW9uIjtzOjU6ImxhYmVsIjtzOjg6IlBvc2l0aW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJwaG9uZSI7czo1OiJsYWJlbCI7czo1OiJQaG9uZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjU7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Njoic3RhdHVzIjtzOjU6ImxhYmVsIjtzOjY6IlN0YXR1cyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjY7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6OToiaGlyZV9kYXRlIjtzOjU6ImxhYmVsIjtzOjk6IkhpcmUgZGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiY2ZkYTRiMzQzMGI0NjcyMGNmZmI4Y2ExZGU3ZjQyNGVfY29sdW1ucyI7YToyOntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoibmFtZSI7czo1OiJsYWJlbCI7czo0OiJOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNjoicGVybWlzc2lvbnMubmFtZSI7czo1OiJsYWJlbCI7czoxMToiUGVybWlzc2lvbnMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjBmMGE3MDQyODA3M2ZmMjQ5OWVhYjM1YWVhODQ5ZDg4X2NvbHVtbnMiO2E6NTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToiZW1haWwiO3M6NToibGFiZWwiO3M6NToiRW1haWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJyb2xlcy5uYW1lIjtzOjU6ImxhYmVsIjtzOjU6IlJvbGVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJzdWJfY2l0eSI7czo1OiJsYWJlbCI7czo4OiJTdWIgQ2l0eSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoiY3JlYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMDoiQ3JlYXRlZCBhdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjA7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjE7fX19fQ==', 1773390488),
('qviIjifyNbhC4kae1lf9NN6yOHWwTNFSvtwkQVp1', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo3OntzOjM6InVybCI7YTowOnt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9yb2xlcyI7czo1OiJyb3V0ZSI7czozNjoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLnJvbGVzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo2OiJfdG9rZW4iO3M6NDA6IjdJUFZuVXh5MDZIRmdETTc5dXY3TXY5Q1NrTWFIREhvZXZyZW16MlkiO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImQ2MjM5NTk0OGUwYWMzYjRlYmI4NjFkODliZDQwZTMxYTVjZjkyZDdkOTZhZWFjMzMwNmZmN2VkMmM0NTE5ZjYiO3M6NjoidGFibGVzIjthOjI6e3M6NDA6IjBmMGE3MDQyODA3M2ZmMjQ5OWVhYjM1YWVhODQ5ZDg4X2NvbHVtbnMiO2E6Njp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToiZW1haWwiO3M6NToibGFiZWwiO3M6NToiRW1haWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJyb2xlcy5uYW1lIjtzOjU6ImxhYmVsIjtzOjU6IlJvbGVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJzdWJfY2l0eSI7czo1OiJsYWJlbCI7czo4OiJTdWIgQ2l0eSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjE7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtiOjA7fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJ3b3JlZGEiO3M6NToibGFiZWwiO3M6NjoiV29yZWRhIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MDt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEwOiJDcmVhdGVkIGF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fXM6NDA6ImNmZGE0YjM0MzBiNDY3MjBjZmZiOGNhMWRlN2Y0MjRlX2NvbHVtbnMiO2E6Mjp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6InBlcm1pc3Npb25zLm5hbWUiO3M6NToibGFiZWwiO3M6MTE6IlBlcm1pc3Npb25zIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX19fQ==', 1773403590);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_translatable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `sort_order`, `is_translatable`, `created_at`, `updated_at`) VALUES
(1, 'organization_name_am', 'የደንብ ማስከበር ባለስልጣን', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(2, 'organization_name_en', 'Addis Ababa City Administration Code Enforcement Authority', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 18:12:24'),
(3, 'tagline_am', '', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 18:07:45'),
(4, 'tagline_en', '', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 18:07:46'),
(5, 'footer_text_am', 'መብቱ በህግ የተጠበቀ ነው', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(6, 'footer_text_en', 'All rights reserved', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(7, 'copyright_text', '© 2024 Addis Ababa City Administration Code Enforcement Authority. All rights reserved.', 'text', 'general', 0, 0, '2026-03-07 15:12:42', '2026-03-07 18:12:25'),
(8, 'phone_primary', '+251 11 123 4567', 'text', 'contact', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(9, 'phone_secondary', '+251 11 765 4321', 'text', 'contact', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(10, 'email_primary', 'info@lawenforcement.gov.et', 'text', 'contact', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(11, 'email_secondary', 'support@lawenforcement.gov.et', 'text', 'contact', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(12, 'address_am', 'አዲስ አበባ፣ ኢትዮጵያ', 'text', 'contact', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(13, 'address_en', 'Addis Ababa, Ethiopia', 'text', 'contact', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(14, 'primary_color', '#0d6efd', 'color', 'appearance', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(15, 'secondary_color', '#6c757d', 'color', 'appearance', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(16, 'accent_color', '#198754', 'color', 'appearance', 0, 0, '2026-03-07 15:12:42', '2026-03-07 15:12:42'),
(17, 'hero_title_am', 'እንኳን ወደ ደንብ ማስከበር ባለስልጣን በደህና መጡ', 'text', 'hero', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(18, 'hero_title_en', 'Welcome to Addis Ababa City Administration Code Enforcement Authority', 'text', 'hero', 0, 0, '2026-03-07 15:12:43', '2026-03-07 18:09:24'),
(19, 'hero_subtitle_am', '', 'text', 'hero', 0, 0, '2026-03-07 15:12:43', '2026-03-07 18:09:24'),
(20, 'hero_subtitle_en', '', 'text', 'hero', 0, 0, '2026-03-07 15:12:43', '2026-03-07 18:09:24'),
(21, 'stats', '[{\"label_am\":\"\\u1320\\u1245\\u120b\\u120b \\u1230\\u122b\\u1270\\u129e\\u127d\",\"label_en\":\"Total Employees\",\"value\":\"1500\"},{\"label_am\":\"\\u1353\\u122b \\u121a\\u120a\\u1270\\u122a \\u12a6\\u134a\\u1230\\u122e\\u127d\",\"label_en\":\"Para Military Officers\",\"value\":\"850\"},{\"label_am\":\"\\u1232\\u126a\\u120d \\u1230\\u122b\\u1270\\u129e\\u127d\",\"label_en\":\"Civil Employees\",\"value\":\"450\"},{\"label_am\":\"\\u12c8\\u1228\\u12f3\\u12ce\\u127d\",\"label_en\":\"Woredas\",\"value\":\"120\"}]', 'json', 'hero', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(22, 'working_hours', '[{\"days_am\":\"\\u1230\\u129e - \\u12d3\\u122d\\u1265\",\"days_en\":\"Monday - Friday\",\"hours\":\"8:30 - 17:30\"},{\"days_am\":\"\\u1245\\u12f3\\u121c\",\"days_en\":\"Saturday\",\"hours\":\"8:30 - 12:30\"},{\"days_am\":\"\\u12a5\\u1201\\u12f5\",\"days_en\":\"Sunday\",\"hours\":\"Closed\"}]', 'json', 'contact', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(23, 'site_title', 'የደንብ ማስከበር ባለስልጣን | ', 'text', 'seo', 0, 0, '2026-03-07 15:12:43', '2026-03-07 18:07:55'),
(24, 'meta_description', 'Official portal of the A.A City Administration Code Enforcement Authority - Submit complaints, report tips, and access public information.', 'text', 'seo', 0, 0, '2026-03-07 15:12:43', '2026-03-07 18:07:55'),
(25, 'meta_keywords', 'law enforcement, complaint, tip, illegal trade, land grabbing, Ethiopia, Addis Ababa', 'text', 'seo', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(26, 'enable_complaints', '1', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(27, 'enable_tips', '1', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(28, 'enable_announcements', '1', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(29, 'enable_faq', '1', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(30, 'enable_contact_form', '1', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(31, 'enable_newsletter', '1', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 15:12:43'),
(32, 'maintenance_mode', '', 'boolean', 'features', 0, 0, '2026-03-07 15:12:43', '2026-03-07 18:07:56'),
(33, 'logo_light', 'site/logo/01KK4SE9XKJ7JDGHBD8KTGYPN3.jpg', 'text', 'general', 0, 0, '2026-03-07 15:22:22', '2026-03-07 15:37:32'),
(34, 'logo_dark', 'site/logo/01KK4SE9YC4T2QDJDJH9HMJ64Y.jpg', 'text', 'general', 0, 0, '2026-03-07 15:22:30', '2026-03-07 15:37:32'),
(35, 'favicon', 'site/favicon/01KK4ZVMDHDH10VV94E6GM05JD.png', 'text', 'general', 0, 0, '2026-03-07 15:24:34', '2026-03-07 17:29:40'),
(36, 'og_image', 'site/og/01KK4ZVMDZWXEVBMSTGX7N6Y2N.png', 'text', 'general', 0, 0, '2026-03-07 15:32:07', '2026-03-07 17:29:40'),
(37, 'hero_description_en', 'A secure, transparent platform to submit complaints, report illegal activities anonymously, and track case statuses. Your voice matters.', 'text', 'hero', 0, 0, '2026-03-07 15:55:28', '2026-03-07 15:55:28'),
(38, 'hero_tagline_am', 'ቅሬታዎን ያስገቡ ● ህገ-ወጥ ስራዎችን ሪፖርት ያድርጉ ● ጉዳይዎን ይከታተሉ', 'text', 'hero', 0, 0, '2026-03-07 15:55:28', '2026-03-07 15:55:28'),
(39, 'faqs', '[{\"question_am\":\"\\u1245\\u122c\\u1273 \\u12a5\\u1295\\u12f4\\u1275 \\u121b\\u1245\\u1228\\u1265 \\u12a5\\u127d\\u120b\\u1208\\u1201?\",\"question_en\":\"How do I submit a complaint?\",\"answer_am\":\"\\u1260\\u121d\\u1293\\u120c\\u12cd \\u12cd\\u1235\\u1325 \\\"\\u1245\\u122c\\u1273 \\u12a0\\u1245\\u122d\\u1265\\\" \\u12e8\\u121a\\u1208\\u12cd\\u1295 \\u12ed\\u132b\\u1291\\u1362 \\u12e8\\u130d\\u120d \\u12dd\\u122d\\u12dd\\u122e\\u127d\\u12ce\\u1295 \\u12ed\\u1219\\u1209\\u1363 \\u1245\\u122c\\u1273\\u12ce\\u1295 \\u12eb\\u1265\\u122b\\u1229 \\u12a5\\u1293 \\u12a5\\u1295\\u12f0 \\u121d\\u122d\\u132b\\u12ce \\u12f0\\u130b\\u134a \\u134b\\u12ed\\u120e\\u127d\\u1295 \\u12eb\\u12eb\\u12ed\\u12d9\\u1362 \\u1309\\u12f3\\u12ed\\u12ce\\u1295 \\u1208\\u1218\\u12a8\\u1273\\u1270\\u120d \\u12e8\\u121a\\u12eb\\u1235\\u127d\\u120d \\u120d\\u12e9 \\u12e8\\u1272\\u12ac\\u1275 \\u1241\\u1325\\u122d \\u12ed\\u12f0\\u122d\\u1235\\u12ce\\u1273\\u120d\\u1362\",\"answer_en\":\"Click \\\"Submit Complaint\\\" in the menu. Fill in your personal details, describe your complaint, and optionally attach supporting files. You\'ll receive a unique ticket number to track your case.\"},{\"question_am\":\"\\u1325\\u1246\\u121b \\u1235\\u1230\\u1325 \\u121b\\u1295\\u1290\\u1274 \\u12ed\\u1320\\u1260\\u1243\\u120d?\",\"question_en\":\"Is my identity protected when reporting a tip?\",\"answer_am\":\"\\u12a0\\u12ce\\u1362 \\u1235\\u121d-\\u12a0\\u120d\\u1263 \\u12e8\\u1325\\u1246\\u121b \\u121b\\u1245\\u1228\\u1262\\u12eb\\u12ce\\u127d \\u12e8\\u130d\\u120d \\u1218\\u1228\\u1303 \\u12a0\\u12eb\\u1235\\u1348\\u120d\\u130b\\u1278\\u12cd\\u121d\\u1362 \\u1208\\u1218\\u12a8\\u1273\\u1270\\u12eb \\u12a0\\u1308\\u120d\\u130d\\u120e\\u1275 \\u12e8\\u121a\\u12cd\\u120d \\u12e8\\u1218\\u12f3\\u1228\\u123b \\u12ae\\u12f5 \\u12ed\\u1348\\u1320\\u122b\\u120d\\u1362 \\u121b\\u1295\\u1290\\u1275\\u12ce \\u1219\\u1209 \\u1260\\u1219\\u1209 \\u121a\\u1235\\u1325\\u122b\\u12ca \\u1206\\u1296 \\u12ed\\u1246\\u12eb\\u120d\\u1362\",\"answer_en\":\"Yes. Anonymous tip submissions do not require personal information. An access token is generated for tracking purposes. Your identity remains completely confidential.\"},{\"question_am\":\"\\u1245\\u122c\\u1273\\u1295 \\u1208\\u121b\\u1235\\u1270\\u1293\\u1308\\u12f5 \\u121d\\u1295 \\u12eb\\u1205\\u120d \\u130a\\u12dc \\u12ed\\u12c8\\u1235\\u12f3\\u120d?\",\"question_en\":\"How long does it take to process a complaint?\",\"answer_am\":\"\\u12e8\\u1218\\u1300\\u1218\\u122a\\u12eb \\u12f0\\u1228\\u1303 \\u130d\\u121d\\u1308\\u121b \\u12a81-3 \\u12e8\\u1235\\u122b \\u1240\\u1293\\u1275 \\u12ed\\u12c8\\u1235\\u12f3\\u120d\\u1362 \\u12cd\\u1235\\u1265\\u1235\\u1265 \\u1309\\u12f3\\u12ee\\u127d \\u1228\\u12d8\\u121d \\u12eb\\u1208 \\u130a\\u12dc \\u120a\\u12c8\\u1235\\u12f1 \\u12ed\\u127d\\u120b\\u1209\\u1362 \\u12e8\\u1272\\u12ac\\u1275 \\u1241\\u1325\\u122d\\u12ce\\u1295 \\u1260\\u1218\\u1320\\u1240\\u121d \\u1260\\u121b\\u1295\\u129b\\u12cd\\u121d \\u130a\\u12dc \\u12e8\\u1309\\u12f3\\u12ed\\u12ce\\u1295 \\u1201\\u1294\\u1273 \\u1218\\u12a8\\u1273\\u1270\\u120d \\u12ed\\u127d\\u120b\\u1209\\u1362\",\"answer_en\":\"Initial review takes 1\\u20133 business days. Complex cases may take longer. You can track your case status at any time using your ticket number.\"}]', 'json', 'general', 0, 0, '2026-03-07 17:35:31', '2026-03-07 17:35:31');

-- --------------------------------------------------------

--
-- Table structure for table `sub_cities`
--

DROP TABLE IF EXISTS `sub_cities`;
CREATE TABLE IF NOT EXISTS `sub_cities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_am` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'አማርኛ ስም',
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_cities_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_cities`
--

INSERT INTO `sub_cities` (`id`, `name_am`, `name_en`, `code`, `created_at`, `updated_at`) VALUES
(11, 'አዲስ ከተማ', 'Addis Ketema', 1, '2026-03-08 17:30:28', '2026-03-08 17:30:28'),
(12, 'አቃቂ ቃሊቲ', 'Akaki Kaliti', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(13, 'አራዳ', 'Arada', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(14, 'ቦሌ', 'Bole', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(15, 'ጉለሌ', 'Gullele', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(16, 'ቂርቆስ', 'Kirkos', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(17, 'ኮልፈ ቀራኒዮ', 'Kolfe Keranio', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(18, 'ለሚ ኩራ', 'Lemi Kura', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(19, 'ልደታ', 'Lideta', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(20, 'ንፋስ ስልክ ላፍቶ', 'Nifas Silk-Lafto', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(21, 'የካ', 'Yeka', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `tips`
--

DROP TABLE IF EXISTS `tips`;
CREATE TABLE IF NOT EXISTS `tips` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tip_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'T-20240306-XXXX',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tip_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `reporter_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional for anonymous',
  `reporter_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reporter_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caller_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caller_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '1',
  `tip_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tip_type_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `woreda` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specific_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `suspect_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suspect_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suspect_vehicle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suspect_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Photos/Videos',
  `has_evidence` tinyint(1) NOT NULL DEFAULT '0',
  `evidence_description` text COLLATE utf8mb4_unicode_ci,
  `urgency_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_ongoing` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Activity still happening',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `supervisor_comment` text COLLATE utf8mb4_unicode_ci,
  `director_comment` text COLLATE utf8mb4_unicode_ci,
  `investigation_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_city_notes` text COLLATE utf8mb4_unicode_ci,
  `supervisor_reviewed_at` timestamp NULL DEFAULT NULL,
  `director_reviewed_at` timestamp NULL DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `assigned_department` bigint UNSIGNED DEFAULT NULL,
  `eligible_for_reward` tinyint(1) NOT NULL DEFAULT '0',
  `reward_amount` decimal(10,2) DEFAULT NULL,
  `reward_claimed` tinyint(1) NOT NULL DEFAULT '0',
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For anonymous tracking',
  `last_accessed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tips_tip_number_unique` (`tip_number`),
  KEY `tips_assigned_to_foreign` (`assigned_to`),
  KEY `tips_assigned_department_foreign` (`assigned_department`),
  KEY `tips_tip_number_index` (`tip_number`),
  KEY `tips_status_index` (`status`),
  KEY `tips_location_index` (`location`),
  KEY `tips_tip_source_status_index` (`tip_source`,`status`),
  KEY `tips_tip_source_sub_city_index` (`tip_source`,`sub_city`),
  KEY `tips_created_by_index` (`created_by`)
) ;

--
-- Dumping data for table `tips`
--

INSERT INTO `tips` (`id`, `tip_number`, `title`, `tip_source`, `reporter_name`, `reporter_email`, `reporter_phone`, `caller_name`, `caller_phone`, `is_anonymous`, `tip_type`, `tip_type_other`, `location`, `sub_city`, `woreda`, `specific_address`, `description`, `suspect_name`, `suspect_description`, `suspect_vehicle`, `suspect_company`, `evidence_files`, `has_evidence`, `evidence_description`, `urgency_level`, `is_ongoing`, `status`, `created_by`, `supervisor_comment`, `director_comment`, `investigation_status`, `sub_city_notes`, `supervisor_reviewed_at`, `director_reviewed_at`, `dispatched_at`, `closed_at`, `assigned_to`, `assigned_department`, `eligible_for_reward`, `reward_amount`, `reward_claimed`, `access_token`, `last_accessed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'TIP-20260308-033405', NULL, 'public', NULL, NULL, NULL, NULL, NULL, 1, 'illegal_trade', NULL, 'Test location', NULL, NULL, NULL, 'Test description', NULL, NULL, NULL, NULL, NULL, 1, 'Evidence description test', 'medium', 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'xplyA1q4gqfiLn1dKzaj7h9nSEo0HLcW', NULL, '2026-03-08 19:08:55', '2026-03-08 19:08:55', NULL),
(2, 'TIP-20260308-592949', NULL, 'public', NULL, NULL, NULL, NULL, NULL, 1, 'illegal_trade', NULL, 'Test location', NULL, NULL, NULL, 'Test description', NULL, NULL, NULL, NULL, NULL, 1, 'Evidence description test', 'medium', 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'aSwrkzXKjzzv2sGwmbkvCOWJAns4Uxet', NULL, '2026-03-08 19:16:40', '2026-03-08 19:16:40', NULL),
(3, 'TIP-20260308-367265', NULL, 'public', NULL, NULL, NULL, NULL, NULL, 1, 'illegal_trade', NULL, 'Test location', NULL, NULL, NULL, 'Test description', NULL, NULL, NULL, NULL, NULL, 1, 'Evidence description test', 'medium', 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'IvhWlUPQKtsl6NPmt37r7grFh3iPzfrO', NULL, '2026-03-08 19:26:56', '2026-03-08 19:26:56', NULL),
(4, 'TIP-20260309-907867', NULL, 'public', NULL, NULL, NULL, NULL, NULL, 1, 'land_grabbing', NULL, 'Lebu', 'አራዳ', '09', NULL, 'testt', NULL, NULL, NULL, NULL, '[\"tips\\/2026\\/03\\/09\\/1773084344_69af1eb8f2786.jpg\"]', 1, NULL, 'high', 0, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'wcS6BJSLVWc5HYlcMHHkX2UGzkNn9n2W', '2026-03-09 16:26:28', '2026-03-09 16:25:47', '2026-03-09 16:26:28', NULL),
(5, 'TIP-20260310-451218', 'land issue', 'call_center', NULL, NULL, NULL, NULL, NULL, 0, 'other', 'Call Center Tip', 'Bole, Woreda 13', 'Bole', '13', NULL, 'this is ....', NULL, NULL, NULL, NULL, NULL, 0, NULL, 'medium', 0, 'under_investigation', 4, 'review this case', 'Test', 'under_investigation', 'this case is under investigation so give me time', '2026-03-09 23:12:25', '2026-03-09 23:19:03', '2026-03-09 23:19:03', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-03-09 22:58:42', '2026-03-09 23:25:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uniform_distributions`
--

DROP TABLE IF EXISTS `uniform_distributions`;
CREATE TABLE IF NOT EXISTS `uniform_distributions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint UNSIGNED NOT NULL,
  `item_type` enum('shirt','pant','jacket','rain_coat','t_shirt','hat','shoe_casual','shoe_leather') COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `distribution_date` date NOT NULL,
  `distribution_type` enum('new','replacement','additional') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_by` bigint UNSIGNED NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniform_distributions_employee_id_foreign` (`employee_id`),
  KEY `uniform_distributions_issued_by_foreign` (`issued_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uniform_inventories`
--

DROP TABLE IF EXISTS `uniform_inventories`;
CREATE TABLE IF NOT EXISTS `uniform_inventories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name_am` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `item_type` enum('shirt','pant','jacket','rain_coat','t_shirt','hat','shoe_casual','shoe_leather') COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `quantity_in_stock` int UNSIGNED NOT NULL DEFAULT '0',
  `minimum_stock` int NOT NULL DEFAULT '10',
  `min_stock_level` int UNSIGNED NOT NULL DEFAULT '10',
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pieces',
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maximum_stock` int NOT NULL DEFAULT '100',
  `sub_city_id` bigint UNSIGNED DEFAULT NULL,
  `received_date` date NOT NULL,
  `supplier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uniform_inventories_sub_city_id_foreign` (`sub_city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `woreda` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `sub_city`, `woreda`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@lawenforcement.gov.et', 'admin@lawenforcement.gov.et', NULL, NULL, NULL, '$2y$12$biar0Xc2SQN8UVc7AXD9Te2WLB47JSJSN0LYsQIcwGIdn44eVEvOK', NULL, '2026-03-06 17:58:21', '2026-03-06 17:58:21'),
(2, 'Super Admin', 'admin@aalea.gov.et', 'admin@aalea.gov.et', NULL, NULL, NULL, '$2y$12$aM0OFu89U1aNdEJ8kvOqruMFjJKfGs.MSOOU57zHW7Ohwg/mxuNV.', 'ZZRRzNrgCUsLAlID1udJoioxcgCoCcB3B7BcKwDSU74qMAGKJ5nx6VCT9zPh', '2026-03-06 19:01:10', '2026-03-13 08:43:21'),
(3, 'Admin', 'admin@admin.com', 'admin@admin.com', NULL, NULL, NULL, '$2y$12$FNp5WV0UcnUW9s4Y.bYuzee1GeepgLte6nqOy3ZeXpbFQDaBmoOWu', NULL, '2026-03-06 20:54:47', '2026-03-08 19:24:52'),
(4, 'Call C', 'Call@gmail.com', 'Call@gmail.com', NULL, NULL, NULL, '$2y$12$FUCaUMs2GhLhvkr4YRFegudWg6htTDPiRiYkQSX65nGcWlByhijB6', NULL, '2026-03-09 17:21:09', '2026-03-09 17:21:09'),
(5, 'SuperViser', 'superv@gmail.com', 'superv@gmail.com', NULL, NULL, NULL, '$2y$12$6o11Ac3E16vTbDduHPKkbObDTPlOf.hDKBa6bhjynT..Kv.t9nhJ2', NULL, '2026-03-09 23:08:19', '2026-03-09 23:08:19'),
(6, 'Call Director', 'calld@gmail.com', 'calld@gmail.com', NULL, NULL, NULL, '$2y$12$3kSnVn3j7A1yfSirx53DH.GrP4waUwYecCEkh3ouuSlGQ8E/EaE1e', NULL, '2026-03-09 23:14:06', '2026-03-09 23:14:06'),
(7, 'Bole Tip', 'Boles@gmail.com', 'Boles@gmail.com', 'Bole', NULL, NULL, '$2y$12$HDOTYF2j3XloDRiLyuFMS.BwDUnjFE3Mo4QEkcIBKEeCEYmXbAkr.', NULL, '2026-03-09 23:20:58', '2026-03-09 23:20:58'),
(8, 'Abdulekerim Seid', 'abdul@gmail.com', 'abdul@gmail.com', NULL, NULL, NULL, '$2y$12$1gYzggWg82b7zpFVJIVdZ.bSuvNKzXyjvFtE.BmHYRNgy304dN40W', NULL, '2026-03-10 02:19:07', '2026-03-10 02:19:07'),
(9, 'kedir ketema', 'Kedir@gmail.com', 'Kedir@gmail.com', NULL, NULL, NULL, '$2y$12$6isyu6glj4WveCusziwghO40zEyI2VXNYIHG07NagFlPu6AEdyAHi', NULL, '2026-03-13 03:15:51', '2026-03-13 03:15:51'),
(12, 'kedir ketema', 'Kedir1@gmail.com', 'Kedir', NULL, NULL, NULL, '$2y$12$fWOGLeT5lXSUqcZMXy/PiekfaTMpt7A6K8HP7uWNLTJozAv05.R..', NULL, '2026-03-13 05:04:45', '2026-03-13 05:04:45'),
(13, 'Officer one  two', 'Office@gmail.com', 'officer', NULL, NULL, NULL, '$2y$12$UWGktlxHMku2E6XrazDLS.Bsc0CUWP3vYp49AUq4jDPpjWMrSkL/6', NULL, '2026-03-14 01:34:31', '2026-03-14 01:34:31');

-- --------------------------------------------------------

--
-- Table structure for table `woredas`
--

DROP TABLE IF EXISTS `woredas`;
CREATE TABLE IF NOT EXISTS `woredas` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `sub_city_id` bigint UNSIGNED NOT NULL,
  `name_am` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `woredas_sub_city_id_foreign` (`sub_city_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `woredas`
--

INSERT INTO `woredas` (`id`, `sub_city_id`, `name_am`, `name_en`, `code`, `created_at`, `updated_at`) VALUES
(111, 11, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(112, 11, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(113, 11, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(114, 11, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(115, 11, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(116, 11, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(117, 11, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(118, 11, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(119, 11, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(120, 11, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(121, 11, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(122, 11, 'ወረዳ 12', 'Woreda 12', 12, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(123, 11, 'ወረዳ 13', 'Woreda 13', 13, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(124, 11, 'ወረዳ 14', 'Woreda 14', 14, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(125, 12, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(126, 12, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(127, 12, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(128, 12, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(129, 12, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(130, 12, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(131, 12, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(132, 12, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(133, 12, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(134, 12, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(135, 12, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(136, 13, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(137, 13, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(138, 13, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(139, 13, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(140, 13, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(141, 13, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(142, 13, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(143, 13, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(144, 13, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(145, 13, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(146, 14, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(147, 14, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(148, 14, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(149, 14, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(150, 14, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(151, 14, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(152, 14, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(153, 14, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(154, 14, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(155, 14, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(156, 14, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(157, 14, 'ወረዳ 12', 'Woreda 12', 12, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(158, 14, 'ወረዳ 13', 'Woreda 13', 13, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(159, 14, 'ወረዳ 14', 'Woreda 14', 14, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(160, 14, 'ወረዳ 15', 'Woreda 15', 15, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(161, 15, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(162, 15, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(163, 15, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(164, 15, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(165, 15, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(166, 15, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(167, 15, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(168, 15, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(169, 15, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(170, 15, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(171, 16, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(172, 16, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(173, 16, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(174, 16, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(175, 16, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(176, 16, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(177, 16, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(178, 16, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(179, 16, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(180, 16, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(181, 16, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(182, 17, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(183, 17, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(184, 17, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(185, 17, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(186, 17, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(187, 17, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(188, 17, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(189, 17, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(190, 17, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(191, 17, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(192, 17, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(193, 17, 'ወረዳ 12', 'Woreda 12', 12, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(194, 17, 'ወረዳ 13', 'Woreda 13', 13, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(195, 17, 'ወረዳ 14', 'Woreda 14', 14, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(196, 17, 'ወረዳ 15', 'Woreda 15', 15, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(197, 18, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(198, 18, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(199, 18, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(200, 18, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(201, 18, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(202, 18, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(203, 18, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(204, 18, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(205, 18, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(206, 18, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(207, 18, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(208, 18, 'ወረዳ 12', 'Woreda 12', 12, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(209, 18, 'ወረዳ 13', 'Woreda 13', 13, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(210, 18, 'ወረዳ 14', 'Woreda 14', 14, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(211, 19, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(212, 19, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(213, 19, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(214, 19, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(215, 19, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(216, 19, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(217, 19, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(218, 19, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(219, 19, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(220, 19, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(221, 20, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(222, 20, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(223, 20, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(224, 20, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(225, 20, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(226, 20, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(227, 20, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(228, 20, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(229, 20, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(230, 20, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(231, 20, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(232, 20, 'ወረዳ 12', 'Woreda 12', 12, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(233, 20, 'ወረዳ 13', 'Woreda 13', 13, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(234, 20, 'ወረዳ 14', 'Woreda 14', 14, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(235, 20, 'ወረዳ 15', 'Woreda 15', 15, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(236, 21, 'ወረዳ 01', 'Woreda 01', 1, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(237, 21, 'ወረዳ 02', 'Woreda 02', 2, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(238, 21, 'ወረዳ 03', 'Woreda 03', 3, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(239, 21, 'ወረዳ 04', 'Woreda 04', 4, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(240, 21, 'ወረዳ 05', 'Woreda 05', 5, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(241, 21, 'ወረዳ 06', 'Woreda 06', 6, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(242, 21, 'ወረዳ 07', 'Woreda 07', 7, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(243, 21, 'ወረዳ 08', 'Woreda 08', 8, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(244, 21, 'ወረዳ 09', 'Woreda 09', 9, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(245, 21, 'ወረዳ 10', 'Woreda 10', 10, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(246, 21, 'ወረዳ 11', 'Woreda 11', 11, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(247, 21, 'ወረዳ 12', 'Woreda 12', 12, '2026-03-08 17:30:29', '2026-03-08 17:30:29'),
(248, 21, 'ወረዳ 13', 'Woreda 13', 13, '2026-03-08 17:30:29', '2026-03-08 17:30:29');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `case_assignments`
--
ALTER TABLE `case_assignments`
  ADD CONSTRAINT `case_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `case_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `case_assignments_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `case_assignments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `case_communications`
--
ALTER TABLE `case_communications`
  ADD CONSTRAINT `case_communications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_updates`
--
ALTER TABLE `case_updates`
  ADD CONSTRAINT `case_updates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_assigned_department_foreign` FOREIGN KEY (`assigned_department`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `complaints_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaints_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_head_of_department_id_foreign` FOREIGN KEY (`head_of_department_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_sub_city_id_foreign` FOREIGN KEY (`sub_city_id`) REFERENCES `sub_cities` (`id`),
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_woreda_id_foreign` FOREIGN KEY (`woreda_id`) REFERENCES `woredas` (`id`);

--
-- Constraints for table `escalations`
--
ALTER TABLE `escalations`
  ADD CONSTRAINT `escalations_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `escalations_escalated_by_foreign` FOREIGN KEY (`escalated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `escalations_escalated_to_foreign` FOREIGN KEY (`escalated_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `officers`
--
ALTER TABLE `officers`
  ADD CONSTRAINT `officers_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `officers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quarterly_reports`
--
ALTER TABLE `quarterly_reports`
  ADD CONSTRAINT `quarterly_reports_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quarterly_reports_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quarterly_reports_prepared_by_foreign` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quarterly_reports_sub_city_id_foreign` FOREIGN KEY (`sub_city_id`) REFERENCES `sub_cities` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tips`
--
ALTER TABLE `tips`
  ADD CONSTRAINT `tips_assigned_department_foreign` FOREIGN KEY (`assigned_department`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `tips_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tips_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `uniform_distributions`
--
ALTER TABLE `uniform_distributions`
  ADD CONSTRAINT `uniform_distributions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `uniform_distributions_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `uniform_inventories`
--
ALTER TABLE `uniform_inventories`
  ADD CONSTRAINT `uniform_inventories_sub_city_id_foreign` FOREIGN KEY (`sub_city_id`) REFERENCES `sub_cities` (`id`);

--
-- Constraints for table `woredas`
--
ALTER TABLE `woredas`
  ADD CONSTRAINT `woredas_sub_city_id_foreign` FOREIGN KEY (`sub_city_id`) REFERENCES `sub_cities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
