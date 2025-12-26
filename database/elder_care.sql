-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 03:50 AM
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
-- Database: `elder_care`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(200) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `action_id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` int(10) UNSIGNED NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `target_user_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `baseline_assessments`
--

CREATE TABLE `baseline_assessments` (
  `assessment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `questionnaire_id` int(10) UNSIGNED NOT NULL,
  `score` decimal(7,2) NOT NULL,
  `risk_category` enum('low','moderate','high','critical') NOT NULL,
  `interpretation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interpretation`)),
  `responses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responses`)),
  `completed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diary_entries`
--

CREATE TABLE `diary_entries` (
  `diary_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `entry_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diary_entries`
--

INSERT INTO `diary_entries` (`diary_id`, `user_id`, `title`, `content`, `entry_date`, `created_at`, `updated_at`) VALUES
(1, 1, 's', 'asas', '0000-00-00', '2025-12-25 17:20:42', '2025-12-25 17:20:42'),
(2, 1, 'asdsa', 'asdad', '0000-00-00', '2025-12-25 17:20:46', '2025-12-25 17:20:46'),
(3, 1, 'asdsa', 'asdad', '0000-00-00', '2025-12-25 18:17:04', '2025-12-25 18:17:04'),
(4, 1, 'asdsa', 'asdad', '0000-00-00', '2025-12-25 18:23:22', '2025-12-25 18:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `name`, `code`, `description`, `created_at`) VALUES
(1, 'Reaction Time', 'reaction', 'Test your reflexes and reaction speed', '2025-11-24 07:03:32'),
(3, 'Number Memory', 'number_memory', 'Remember and recall increasingly longer number sequences', '2025-11-24 07:22:40'),
(5, 'Chimp Test', 'chimp_test', 'Working memory test - remember number positions in sequence', '2025-11-24 07:22:40'),
(6, 'Card Flip', 'card_flip', 'Pattern matching memory game - flip cards to find matching pairs', '2025-12-13 03:21:38'),
(7, 'Tetris', 'tetris', 'Cognitive training game', '2025-12-15 06:08:58'),
(8, 'Gem Match', 'gem_match', 'Cognitive training game', '2025-12-15 06:46:03'),
(9, 'Memory Match', 'memory', 'Test your memory by matching patterns and sequences', '2025-12-25 05:45:37'),
(10, 'Attention Focus', 'attention', 'Measure sustained attention and focus ability', '2025-12-25 05:45:37'),
(11, 'Puzzle Solver', 'puzzle', 'Challenge your problem-solving and spatial reasoning', '2025-12-25 05:45:37');

-- --------------------------------------------------------

--
-- Table structure for table `game_scores`
--

CREATE TABLE `game_scores` (
  `score_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_score` int(11) DEFAULT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL,
  `avg_reaction_ms` int(11) DEFAULT NULL,
  `level_reached` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_scores`
--

INSERT INTO `game_scores` (`score_id`, `session_id`, `score`, `max_score`, `accuracy`, `avg_reaction_ms`, `level_reached`, `details`, `created_at`) VALUES
(1, 1, 94, NULL, 0.00, NULL, NULL, NULL, '2025-11-24 07:03:32'),
(3, 3, 300, NULL, 62.50, NULL, NULL, NULL, '2025-11-24 15:41:08'),
(4, 4, 80, NULL, 0.00, NULL, NULL, NULL, '2025-11-27 02:42:10'),
(5, 5, 910, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"100\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 03:50:58'),
(6, 6, 1400, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"100\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 08:34:36'),
(7, 7, 1420, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"100\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 08:36:59'),
(8, 8, 300, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"62.5\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 08:42:31'),
(9, 9, 390, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"66.66666666666666\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 08:43:44'),
(10, 10, 220, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"57.14285714285714\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 08:46:33'),
(11, 11, 1000, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"57.14285714285714\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-13 09:27:16'),
(12, 12, 7886, NULL, NULL, NULL, NULL, '{\"max_score\":\"7886\",\"accuracy\":0,\"level_reached\":\"3\",\"avg_reaction_ms\":null}', '2025-12-15 06:08:58'),
(13, 13, 2670, NULL, NULL, NULL, NULL, '{\"max_score\":\"2670\",\"accuracy\":0,\"level_reached\":\"2\",\"avg_reaction_ms\":null}', '2025-12-15 06:18:58'),
(14, 14, 3800, NULL, NULL, NULL, NULL, '{\"max_score\":\"3800\",\"accuracy\":0,\"level_reached\":\"2\",\"avg_reaction_ms\":null}', '2025-12-15 06:37:31'),
(15, 15, 3800, NULL, NULL, NULL, NULL, '{\"max_score\":\"3800\",\"accuracy\":0,\"level_reached\":\"2\",\"avg_reaction_ms\":null}', '2025-12-15 06:37:46'),
(16, 16, 3800, NULL, NULL, NULL, NULL, '{\"max_score\":\"3800\",\"accuracy\":0,\"level_reached\":\"2\",\"avg_reaction_ms\":null}', '2025-12-15 06:42:15'),
(17, 17, 150, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:46:03'),
(18, 18, 150, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:47:12'),
(19, 19, 300, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:48:21'),
(20, 20, 300, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:49:15'),
(21, 21, 89, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:51:55'),
(22, 22, 271, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:53:40'),
(23, 23, 271, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-15 06:55:10'),
(24, 24, 120, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:14:35'),
(25, 25, 900, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:16:58'),
(26, 26, 900, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:17:42'),
(27, 27, 1800, NULL, NULL, NULL, NULL, '{\"max_score\":\"1800\",\"accuracy\":0,\"level_reached\":\"2\",\"avg_reaction_ms\":null}', '2025-12-18 19:30:10'),
(28, 28, 1820, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:32:36'),
(29, 29, 1820, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:33:44'),
(30, 30, 1820, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:33:53'),
(31, 31, 1820, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":0,\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 19:34:24'),
(32, 32, 600, NULL, NULL, NULL, NULL, '{\"max_score\":null,\"accuracy\":\"50\",\"level_reached\":null,\"avg_reaction_ms\":null}', '2025-12-18 21:36:37'),
(33, 33, 1100, NULL, NULL, NULL, NULL, '{\"max_score\":\"1100\",\"accuracy\":0,\"level_reached\":\"1\",\"avg_reaction_ms\":null}', '2025-12-18 21:40:03'),
(34, 34, 100, NULL, NULL, NULL, NULL, '{\"max_score\":\"100\",\"accuracy\":0,\"level_reached\":\"1\",\"avg_reaction_ms\":null}', '2025-12-25 05:52:46'),
(35, 35, 1000, NULL, NULL, NULL, NULL, '{\"duration\":\"224\",\"max_score\":\"1000\",\"accuracy\":0,\"level_reached\":\"1\",\"avg_reaction_ms\":null}', '2025-12-25 18:27:12'),
(36, 36, 1000, NULL, NULL, NULL, NULL, '{\"duration\":\"224\",\"max_score\":\"1000\",\"accuracy\":0,\"level_reached\":\"1\",\"avg_reaction_ms\":null}', '2025-12-25 19:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `game_sessions`
--

CREATE TABLE `game_sessions` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `game_id` int(10) UNSIGNED NOT NULL,
  `difficulty` enum('easy','medium','hard','custom') DEFAULT 'easy',
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL,
  `session_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_data`)),
  `daily_challenge` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_sessions`
--

INSERT INTO `game_sessions` (`session_id`, `user_id`, `game_id`, `difficulty`, `started_at`, `ended_at`, `session_data`, `daily_challenge`) VALUES
(1, 1, 1, 'hard', '2025-11-24 07:03:32', '2025-11-24 07:03:55', NULL, 0),
(3, 1, 5, 'medium', '2025-11-24 15:41:08', '2025-11-24 15:41:43', NULL, 0),
(4, 1, 1, 'hard', '2025-11-27 02:42:10', '2025-11-27 02:42:30', NULL, 0),
(5, 4, 6, 'easy', '2025-12-13 03:50:58', '2025-12-13 03:50:58', NULL, 0),
(6, 1, 6, 'hard', '2025-12-13 08:34:36', '2025-12-13 08:34:36', NULL, 0),
(7, 1, 6, 'hard', '2025-12-13 08:36:59', '2025-12-13 08:36:59', NULL, 0),
(8, 1, 5, 'medium', '2025-12-13 08:42:31', '2025-12-13 08:42:31', NULL, 0),
(9, 1, 5, 'medium', '2025-12-13 08:43:44', '2025-12-13 08:43:44', NULL, 0),
(10, 1, 5, 'medium', '2025-12-13 08:46:33', '2025-12-13 08:46:33', NULL, 0),
(11, 1, 5, 'medium', '2025-12-13 09:27:16', '2025-12-13 09:27:16', NULL, 0),
(12, 1, 7, 'medium', '2025-12-15 06:08:58', '2025-12-15 06:08:58', NULL, 0),
(13, 1, 7, 'medium', '2025-12-15 06:18:58', '2025-12-15 06:18:58', NULL, 0),
(14, 1, 7, 'medium', '2025-12-15 06:37:31', '2025-12-15 06:37:31', NULL, 0),
(15, 1, 7, 'medium', '2025-12-15 06:37:46', '2025-12-15 06:37:46', NULL, 0),
(16, 1, 7, 'medium', '2025-12-15 06:42:15', '2025-12-15 06:42:15', NULL, 0),
(17, 1, 8, 'medium', '2025-12-15 06:46:03', '2025-12-15 06:46:03', NULL, 0),
(18, 1, 8, 'medium', '2025-12-15 06:47:11', '2025-12-15 06:47:11', NULL, 0),
(19, 1, 8, 'medium', '2025-12-15 06:48:21', '2025-12-15 06:48:21', NULL, 0),
(20, 1, 8, 'medium', '2025-12-15 06:49:15', '2025-12-15 06:49:15', NULL, 0),
(21, 1, 1, 'medium', '2025-12-15 06:51:55', '2025-12-15 06:51:55', NULL, 0),
(22, 1, 1, 'medium', '2025-12-15 06:53:40', '2025-12-15 06:53:40', NULL, 0),
(23, 1, 1, 'medium', '2025-12-15 06:55:10', '2025-12-15 06:55:10', NULL, 0),
(24, 1, 8, 'medium', '2025-12-18 19:14:35', '2025-12-18 19:14:35', NULL, 0),
(25, 1, 8, 'medium', '2025-12-18 19:16:58', '2025-12-18 19:16:58', NULL, 0),
(26, 1, 8, 'medium', '2025-12-18 19:17:42', '2025-12-18 19:17:42', NULL, 0),
(27, 1, 7, 'medium', '2025-12-18 19:30:10', '2025-12-18 19:30:10', NULL, 0),
(28, 1, 8, 'medium', '2025-12-18 19:32:35', '2025-12-18 19:32:35', NULL, 0),
(29, 1, 8, 'medium', '2025-12-18 19:33:44', '2025-12-18 19:33:44', NULL, 0),
(30, 1, 8, 'medium', '2025-12-18 19:33:53', '2025-12-18 19:33:53', NULL, 0),
(31, 1, 8, 'medium', '2025-12-18 19:34:24', '2025-12-18 19:34:24', NULL, 0),
(32, 1, 5, 'medium', '2025-12-18 21:36:37', '2025-12-18 21:36:37', NULL, 0),
(33, 1, 7, 'medium', '2025-12-18 21:40:03', '2025-12-18 21:40:03', NULL, 0),
(34, 1, 7, 'medium', '2025-12-25 05:52:46', '2025-12-25 05:52:46', NULL, 0),
(35, 1, 7, 'medium', '2025-12-25 18:23:28', '2025-12-25 18:27:12', NULL, 0),
(36, 1, 7, 'medium', '2025-12-25 19:01:25', '2025-12-25 19:05:09', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `mood_logs`
--

CREATE TABLE `mood_logs` (
  `mood_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `entry_date` date NOT NULL,
  `mood_value` tinyint(3) UNSIGNED NOT NULL,
  `mood_emoji` varchar(10) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mood_logs`
--

INSERT INTO `mood_logs` (`mood_id`, `user_id`, `entry_date`, `mood_value`, `mood_emoji`, `notes`, `created_at`) VALUES
(1, 1, '2025-11-24', 5, '😄', '', '2025-11-24 07:01:06'),
(2, 1, '2025-11-25', 1, '😢', 'no', '2025-11-25 02:30:13'),
(3, 1, '2025-11-27', 5, '😄', '', '2025-11-27 02:41:35'),
(4, 1, '2025-11-29', 1, '😢', 'asas', '2025-11-29 09:35:06'),
(5, 3, '2025-12-12', 2, '😕', 'oo ggaga', '2025-12-12 23:50:18'),
(6, 1, '0000-00-00', 4, NULL, NULL, '2025-12-15 04:54:48'),
(12, 1, '2025-12-25', 4, NULL, NULL, '2025-12-25 05:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_say') DEFAULT NULL,
  `preferred_language` varchar(50) DEFAULT 'en',
  `phone` varchar(50) DEFAULT NULL,
  `avatar_url` varchar(512) DEFAULT NULL,
  `accessibility_prefs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`accessibility_prefs`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`user_id`, `full_name`, `date_of_birth`, `gender`, `preferred_language`, `phone`, `avatar_url`, `accessibility_prefs`, `created_at`) VALUES
(1, 'Shawn', '2005-06-09', 'male', 'en', NULL, '/assets/images/avatars/user_1_1766653685.png', NULL, '2025-11-24 06:44:34'),
(2, 'Shawn', NULL, NULL, 'en', NULL, NULL, NULL, '2025-12-12 23:49:02'),
(3, '123', NULL, NULL, 'en', NULL, NULL, NULL, '2025-12-12 23:49:38');

-- --------------------------------------------------------

--
-- Table structure for table `questionnaires`
--

CREATE TABLE `questionnaires` (
  `questionnaire_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `short_code` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'wellness',
  `version` varchar(20) DEFAULT '1.0',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questionnaires`
--

INSERT INTO `questionnaires` (`questionnaire_id`, `name`, `short_code`, `type`, `version`, `created_at`) VALUES
(1, 'Wellness Assessment', 'wellness', 'wellness', '1.0', '2025-11-24 07:01:25'),
(2, 'Wellbeing Assessment', 'wellbeing', 'wellbeing', '1.0', '2025-11-27 16:50:46'),
(3, 'WHO-5 Well-Being Index', 'WHO5', 'WHO5', '1.0', '2025-12-13 03:21:38'),
(4, 'Patient Health Questionnaire-9', 'PHQ9', 'PHQ9', '1.0', '2025-12-13 03:21:38'),
(5, 'Generalized Anxiety Disorder-7', 'GAD7', 'GAD7', '1.0', '2025-12-13 03:21:38'),
(6, 'Geriatric Depression Scale-15', 'GDS15', 'GDS15', '1.0', '2025-12-13 03:21:38'),
(7, 'Pittsburgh Sleep Quality Index', 'PSQI', 'PSQI', '1.0', '2025-12-13 03:21:38'),
(8, 'Perceived Stress Scale-4', 'PSS4', 'PSS4', '1.0', '2025-12-13 03:21:38'),
(9, 'PSQI', '', 'sleep', '1.0', '2025-12-13 08:28:40');

-- --------------------------------------------------------

--
-- Table structure for table `questionnaire_responses`
--

CREATE TABLE `questionnaire_responses` (
  `result_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `questionnaire_id` int(10) UNSIGNED NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `score` decimal(7,2) DEFAULT NULL,
  `completed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `interpretation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interpretation`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questionnaire_responses`
--

INSERT INTO `questionnaire_responses` (`result_id`, `user_id`, `questionnaire_id`, `answers`, `score`, `completed_at`, `interpretation`) VALUES
(1, 1, 1, '{\"q0\":5,\"q1\":5,\"q2\":5,\"q3\":5,\"q4\":5,\"q5\":5,\"q6\":5,\"q7\":5,\"q8\":5,\"q9\":5}', 50.00, '2025-11-24 07:01:25', NULL),
(2, 1, 2, '{\"q0\":5,\"q1\":5,\"q2\":5,\"q3\":5,\"q4\":5}', 25.00, '2025-11-27 16:50:46', NULL),
(3, 3, 2, '{\"q0\":3,\"q1\":3,\"q2\":3,\"q3\":3,\"q4\":3}', 60.00, '2025-12-12 23:50:35', NULL),
(4, 1, 9, '{\"q0\":1,\"q1\":1,\"q2\":1,\"q3\":1,\"q4\":1}', 5.00, '2025-12-13 08:28:40', '{\"level\":\"good\",\"color\":\"#28a745\",\"emoji\":\"\\ud83d\\ude34\",\"message\":\"Good sleep quality\",\"recommendation\":\"Your sleep quality is healthy. Maintain good sleep habits: consistent schedule, comfortable environment, avoid screens before bed, and limit caffeine.\",\"severity\":\"none\",\"max_score\":21,\"percentage\":24,\"questionnaire_name\":\"Pittsburgh Sleep Quality Index (PSQI)\",\"reference\":\"Buysse, D.J., Reynolds, C.F., Monk, T.H., Berman, S.R., & Kupfer, D.J. (1989). The Pittsburgh Sleep Quality Index: A new instrument for psychiatric practice and research. Psychiatry Research, 28(2), 193-213.\"}'),
(5, 1, 2, '{\"q0\":1,\"q1\":1,\"q2\":1,\"q3\":1,\"q4\":1}', 20.00, '2025-12-15 04:55:12', '{\"level\":\"poor\",\"color\":\"#dc3545\",\"emoji\":\"\\ud83d\\udc99\",\"message\":\"Poor well-being\",\"recommendation\":\"Your score suggests poor well-being. Please consider consulting a healthcare professional for evaluation and support. This may indicate depression or other health concerns.\",\"severity\":\"severe\",\"max_score\":100,\"percentage\":20,\"questionnaire_name\":\"WHO-5 Well-Being Index\",\"reference\":\"World Health Organization (1998). Wellbeing Measures in Primary Health Care\\/The Depcare Project. WHO Regional Office for Europe: Copenhagen.\"}'),
(6, 1, 2, '{\"q0\":1,\"q1\":1,\"q2\":1,\"q3\":1,\"q4\":1}', 20.00, '2025-12-15 04:55:58', '{\"level\":\"poor\",\"color\":\"#dc3545\",\"emoji\":\"\\ud83d\\udc99\",\"message\":\"Poor well-being\",\"recommendation\":\"Your score suggests poor well-being. Please consider consulting a healthcare professional for evaluation and support. This may indicate depression or other health concerns.\",\"severity\":\"severe\",\"max_score\":100,\"percentage\":20,\"questionnaire_name\":\"WHO-5 Well-Being Index\",\"reference\":\"World Health Organization (1998). Wellbeing Measures in Primary Health Care\\/The Depcare Project. WHO Regional Office for Europe: Copenhagen.\"}');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `role` enum('user','admin','therapist') NOT NULL DEFAULT 'user',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `has_completed_initial_assessment` tinyint(1) NOT NULL DEFAULT 0,
  `baseline_assessment_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `date_of_birth`, `created_at`, `last_login_at`, `is_active`, `role`, `is_admin`, `has_completed_initial_assessment`, `baseline_assessment_id`) VALUES
(1, 'shawn050509', 'shawn050509@gmail.com', '$2y$10$rskqNJYJMtOqlgVrKDZcu.OaXEEbjrEpT617ukYaQ9rEe/r25W1TW', NULL, NULL, '2025-11-24 06:44:34', '2025-12-13 08:21:47', 1, 'user', 1, 0, NULL),
(2, 'shawn0505019', 'shawn0505019@gmail.com', '$2y$10$.yASLzGs5srAx6MbalLJA.Ap9/S4IRPkjU/Xj0bVhV/OXEcUY3hMO', NULL, NULL, '2025-12-12 23:49:02', NULL, 1, 'user', 0, 0, NULL),
(3, 'shawn0505091', 'shawn0505091@gmail.com', '$2y$10$H.wMOPQg4oIBzrBNcDVwiOsFVMngVHxpjDvETw2H6N8QpxtUTJ7rS', NULL, NULL, '2025-12-12 23:49:38', NULL, 1, 'user', 0, 0, NULL),
(4, 'Vobix', 'shawn05050911@gmail.com', '$2y$10$DC48FtZtya/AKG94s2Fr5.RD6wz1/xs644gPoBt5Ta2WWIzHcqkTC', 'Shawn', NULL, '2025-12-13 03:44:52', NULL, 1, 'user', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_game_stats`
--

CREATE TABLE `user_game_stats` (
  `stat_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `game_id` int(10) UNSIGNED NOT NULL,
  `times_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `best_score` int(11) NOT NULL DEFAULT 0,
  `average_score` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_score` bigint(20) NOT NULL DEFAULT 0,
  `rank` int(10) UNSIGNED DEFAULT NULL,
  `percentile` decimal(5,2) DEFAULT NULL,
  `last_played_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `global_rank` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_game_stats`
--

INSERT INTO `user_game_stats` (`stat_id`, `user_id`, `game_id`, `times_played`, `best_score`, `average_score`, `total_score`, `rank`, `percentile`, `last_played_at`, `created_at`, `updated_at`, `global_rank`) VALUES
(1, 4, 6, 1, 910, 910.00, 910, 2, 0.00, '2025-12-13 03:50:58', '2025-12-13 03:50:58', '2025-12-13 08:34:36', NULL),
(2, 1, 6, 2, 1420, 1410.00, 2820, 1, 100.00, '2025-12-13 08:36:59', '2025-12-13 08:34:36', '2025-12-13 08:36:59', NULL),
(3, 1, 5, 5, 1000, 502.00, 2510, 1, 100.00, '2025-12-18 21:36:37', '2025-12-13 08:42:31', '2025-12-18 21:36:37', NULL),
(4, 1, 7, 10, 7886, 2695.60, 26956, 1, NULL, '2025-12-25 19:05:09', '2025-12-15 06:08:58', '2025-12-25 19:05:09', NULL),
(5, 1, 8, 11, 1820, 918.18, 10100, 1, 100.00, '2025-12-18 19:34:24', '2025-12-15 06:46:03', '2025-12-18 19:34:24', NULL),
(6, 1, 1, 3, 271, 210.33, 631, 1, NULL, '2025-12-15 06:55:10', '2025-12-15 06:51:55', '2025-12-15 06:55:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_game_summary`
--

CREATE TABLE `user_game_summary` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `total_sessions` int(10) UNSIGNED DEFAULT 0,
  `best_score` int(11) DEFAULT 0,
  `avg_reaction_ms` decimal(8,2) DEFAULT NULL,
  `last_played_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `high_contrast` tinyint(1) DEFAULT 0,
  `preferred_font_size` enum('small','normal','large','xlarge') DEFAULT 'normal',
  `voice_assistant_enabled` tinyint(1) DEFAULT 0,
  `tap_only_mode` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`user_id`, `high_contrast`, `preferred_font_size`, `voice_assistant_enabled`, `tap_only_mode`, `created_at`) VALUES
(1, 0, 'large', 0, 0, '2025-11-24 06:44:34'),
(2, 0, 'normal', 0, 0, '2025-12-12 23:49:02'),
(3, 0, 'normal', 0, 0, '2025-12-12 23:49:38'),
(4, 0, 'normal', 0, 0, '2025-12-13 03:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_summaries`
--

CREATE TABLE `weekly_summaries` (
  `summary_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `week_start` date NOT NULL,
  `summary_type` varchar(50) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `generated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `admin_user_id` (`admin_user_id`),
  ADD KEY `target_user_id` (`target_user_id`),
  ADD KEY `action_type` (`action_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `baseline_assessments`
--
ALTER TABLE `baseline_assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `questionnaire_id` (`questionnaire_id`),
  ADD KEY `idx_risk_category` (`risk_category`);

--
-- Indexes for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD PRIMARY KEY (`diary_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_entry_date` (`entry_date`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `game_scores`
--
ALTER TABLE `game_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `started_at` (`started_at`);

--
-- Indexes for table `mood_logs`
--
ALTER TABLE `mood_logs`
  ADD PRIMARY KEY (`mood_id`),
  ADD UNIQUE KEY `uniq_user_entry_date` (`user_id`,`entry_date`),
  ADD KEY `entry_date` (`entry_date`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `questionnaires`
--
ALTER TABLE `questionnaires`
  ADD PRIMARY KEY (`questionnaire_id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `questionnaire_id` (`questionnaire_id`),
  ADD KEY `taken_at` (`completed_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uniq_username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_is_admin` (`is_admin`),
  ADD KEY `idx_has_completed_assessment` (`has_completed_initial_assessment`);

--
-- Indexes for table `user_game_stats`
--
ALTER TABLE `user_game_stats`
  ADD PRIMARY KEY (`stat_id`),
  ADD UNIQUE KEY `uniq_user_game` (`user_id`,`game_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `idx_best_score_desc` (`game_id`,`best_score`),
  ADD KEY `idx_avg_score_desc` (`game_id`,`average_score`);

--
-- Indexes for table `user_game_summary`
--
ALTER TABLE `user_game_summary`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `weekly_summaries`
--
ALTER TABLE `weekly_summaries`
  ADD PRIMARY KEY (`summary_id`),
  ADD UNIQUE KEY `uniq_user_week_type` (`user_id`,`week_start`,`summary_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `action_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `baseline_assessments`
--
ALTER TABLE `baseline_assessments`
  MODIFY `assessment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diary_entries`
--
ALTER TABLE `diary_entries`
  MODIFY `diary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `game_scores`
--
ALTER TABLE `game_scores`
  MODIFY `score_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `mood_logs`
--
ALTER TABLE `mood_logs`
  MODIFY `mood_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `questionnaires`
--
ALTER TABLE `questionnaires`
  MODIFY `questionnaire_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  MODIFY `result_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_game_stats`
--
ALTER TABLE `user_game_stats`
  MODIFY `stat_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `weekly_summaries`
--
ALTER TABLE `weekly_summaries`
  MODIFY `summary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD CONSTRAINT `fk_admin_actions_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admin_actions_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `baseline_assessments`
--
ALTER TABLE `baseline_assessments`
  ADD CONSTRAINT `fk_baseline_questionnaire` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`questionnaire_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_baseline_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD CONSTRAINT `fk_diary_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `game_scores`
--
ALTER TABLE `game_scores`
  ADD CONSTRAINT `fk_game_scores_session` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`session_id`) ON DELETE CASCADE;

--
-- Constraints for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `fk_game_sessions_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_game_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `mood_logs`
--
ALTER TABLE `mood_logs`
  ADD CONSTRAINT `fk_mood_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  ADD CONSTRAINT `fk_qresp_questionnaire` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`questionnaire_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qresp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_game_stats`
--
ALTER TABLE `user_game_stats`
  ADD CONSTRAINT `fk_user_game_stats_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_game_stats_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_game_summary`
--
ALTER TABLE `user_game_summary`
  ADD CONSTRAINT `user_game_summary_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `fk_user_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `weekly_summaries`
--
ALTER TABLE `weekly_summaries`
  ADD CONSTRAINT `fk_weekly_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
