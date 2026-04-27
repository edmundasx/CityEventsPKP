-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 05:18 AM
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
-- Database: `cityevents`
--

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE `blocked_users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `blocked_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organizer_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(60) NOT NULL,
  `location` varchar(255) NOT NULL,
  `lat` decimal(10,6) DEFAULT NULL,
  `lng` decimal(10,6) DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected','update_pending') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `cover_image` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `organizer_id`, `title`, `description`, `category`, `location`, `lat`, `lng`, `event_date`, `price`, `status`, `rejection_reason`, `cover_image`, `created_at`, `updated_at`) VALUES
(1, 3, 'Senamiesčio vakarinis turas', 'Ekskursija su gidu po senamiesčio kiemus ir paslėptas vietas.', 'culture', 'Vilnius, Pilies g. 1', 54.686157, 25.285774, '2026-03-21 18:31:03', 0.00, 'approved', NULL, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(2, 3, 'Neries vakaro bėgimas', '10 km trasa palei upę su muzika finišo zonoje.', 'sports', 'Vilnius, Neries krantinė', 54.699921, 25.268493, '2026-03-28 18:31:03', 5.00, 'approved', NULL, 'https://images.unsplash.com/photo-1508609349937-5ec4ae374ebf?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(3, 3, 'Klaipėdos vasaros koncertas', 'Atviras koncertas su vietiniais atlikėjais ir maisto zonomis.', 'music', 'Klaipėda, Kruizinių laivų terminalas', 55.710803, 21.127880, '2026-04-05 18:31:03', 0.00, 'approved', NULL, 'https://images.unsplash.com/photo-1506156886591-1f54be9ea5f1?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(4, 3, 'Kauno kūrybinių dirbtuvių diena', 'Šeštadienio dirbtuvės šeimoms ir jauniems kūrėjams.', 'arts', 'Kaunas, Laisvės al. 68', 54.898521, 23.912289, '2026-03-14 18:31:03', 7.50, 'approved', NULL, 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(5, 3, 'Trakų pilies regata', 'Valčių lenktynės ir maisto vagonėliai prie Galvės ežero.', 'sports', 'Trakai, Karaimų g. 53', 54.646227, 24.933012, '2026-04-15 18:31:03', 9.00, 'pending', NULL, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(6, 5, 'Panevėžio amatų mugė', 'Rankdarbių ir vietinių gaminių mugė miesto centre.', 'market', 'Panevėžys, Laisvės a.', 55.733333, 24.350000, '2026-04-06 18:31:03', 2.00, 'approved', NULL, 'https://images.unsplash.com/photo-1492724441997-5dc865305da7?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-04-07 03:45:06'),
(7, 5, 'Šiaulių džiazo vakaras', 'Atviras džiazo koncertas su vietiniais atlikėjais.', 'music', 'Šiauliai, Prisikėlimo a. 3', 55.934909, 23.313682, '2026-03-31 18:31:03', 4.00, 'approved', NULL, 'https://images.unsplash.com/photo-1506156886591-1f54be9ea5f1?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(8, 7, 'Joninių laužai', 'Tradicinis Joninių minėjimas su muzika ir maistu.', 'festival', 'Vilnius, Verkių parkas', 54.750000, 25.300000, '2026-04-10 18:31:03', 0.00, 'approved', NULL, 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(9, 7, 'Kaimo sūrio degustacija', 'Mažų ūkių sūrių degustacija ir edukacijos.', 'food', 'Molėtai, Turgaus g. 2', 55.230000, 25.420000, '2026-04-03 18:31:03', 6.50, 'approved', NULL, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(10, 9, 'Alytaus kino vakarai', 'Lauko kino seansai su diskusijomis.', 'film', 'Alytus, S. Dariaus ir S. Girėno g. 13', 54.401447, 24.049978, '2026-03-19 18:31:03', 3.00, 'approved', NULL, 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(11, 9, 'Birštono dviračių žygis', 'Šeštadienio dviračių maršrutas Nemuno kilpomis.', 'sports', 'Birštonas, B. Sruogos g. 4', 54.608400, 24.028400, '2026-03-30 18:31:03', 5.00, 'approved', NULL, 'https://images.unsplash.com/photo-1508609349937-5ec4ae374ebf?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(12, 11, 'Marijampolės teatro vakaras', 'Vietinio teatro trupės premjera.', 'theatre', 'Marijampolė, J. Basanavičiaus a. 3', 54.559915, 23.354119, '2026-03-22 18:31:03', 7.00, 'approved', NULL, 'https://images.unsplash.com/photo-1515169067865-5387a876a836?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(13, 11, 'Kėdainių skonių turas', 'Ekskursija po senamiestį ir degustacija.', 'food', 'Kėdainiai, Didžioji g. 10', 55.289383, 23.974691, '2026-03-27 18:31:03', 8.00, 'approved', NULL, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(14, 3, 'Švenčionių žygis', 'Miško takais vedantis 12 km maršrutas.', 'sports', 'Švenčionys, Žeimenos g. 5', 55.166667, 26.000000, '2026-03-12 18:31:03', 4.50, 'rejected', NULL, NULL, '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(15, 5, 'Ukmergės kūrybos mugė', 'Vietos kūrėjų darbų mugė ir koncertai.', 'market', 'Ukmergė, Vytauto g. 10', 55.246944, 24.752778, '2026-03-18 18:31:03', 1.50, 'approved', NULL, 'https://images.unsplash.com/photo-1492724441997-5dc865305da7?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(16, 7, 'Zarasų ežerų šventė', 'Dienos programa su sporto rungtimis ir muzika.', 'festival', 'Zarasai, Sėlių a. 22', 55.732500, 26.255833, '2026-04-25 18:31:03', 0.00, 'approved', NULL, NULL, '2026-03-16 18:31:03', '2026-04-06 20:50:40'),
(17, 9, 'Druskininkų meno savaitgalis', 'Parodos, performansai ir kūrybinės dirbtuvės.', 'arts', 'Druskininkai, Vilniaus al. 13', 54.016667, 23.966667, '2026-03-25 18:31:03', 6.00, 'approved', NULL, 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(18, 11, 'Telšių istorijos maršrutas', 'Gidas pasakoja legendas apie miesto kalvas.', 'culture', 'Telšiai, Turgaus a. 1', 55.984444, 22.247778, '2026-04-13 18:31:03', 3.50, 'approved', NULL, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(19, 5, 'Vilniaus technologijų forumas', 'Pranešimai apie miestų inovacijas ir tvarius projektus.', 'conference', 'Vilnius, Konstitucijos pr. 26', 54.700000, 25.270000, '2026-04-07 18:31:03', 12.00, 'approved', NULL, 'https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(20, 7, 'Elektrėnų šeimų piknikas', 'Bendruomenės renginys su žaidimais ir muzika.', 'community', 'Elektrėnai, Draugystės g. 17', 54.773700, 24.660400, '2026-04-01 18:31:03', 0.00, 'approved', NULL, 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=900&q=80', '2026-03-16 18:31:03', '2026-03-16 18:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `event_drafts`
--

CREATE TABLE `event_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organizer_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(60) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','pending') NOT NULL DEFAULT 'draft',
  `cover_image` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_drafts`
--

INSERT INTO `event_drafts` (`id`, `organizer_id`, `title`, `description`, `category`, `location`, `event_date`, `event_time`, `price`, `status`, `cover_image`, `created_at`, `updated_at`) VALUES
(1, 3, 'Žiemos festivalio planas', 'Pirminė programa su scena ir mugėmis.', 'festival', 'Vilnius, Kalvarijų g. 10', '2026-04-30 18:31:03', '16:00:00', 0.00, 'draft', NULL, '2026-03-15 18:31:03', '2026-03-16 18:31:03'),
(2, 3, 'Tradicinės muzikos vakaras', 'Programos aprašas bus papildytas.', 'music', 'Vilnius, Rotušės a. 5', '2026-05-05 18:31:03', '18:30:00', 5.00, 'draft', NULL, '2026-03-14 18:31:03', '2026-03-16 18:31:03'),
(3, 5, 'Kultūros forumo eskizas', 'Preliminari pranešimų tema.', 'conference', 'Kaunas, K. Donelaičio g. 8', '2026-04-20 18:31:03', '10:00:00', 10.00, 'draft', NULL, '2026-03-13 18:31:03', '2026-03-16 18:31:03'),
(4, 5, 'Amatų edukacijos diena', 'Planuojamos keturios dirbtuvės.', 'arts', 'Panevėžys, Vasario 16-osios g. 20', '2026-05-15 18:31:03', '11:00:00', 3.00, 'draft', NULL, '2026-03-12 18:31:03', '2026-03-16 18:31:03'),
(5, 7, 'Joninių pasiruošimas', 'Erdvių ir leidimų planas.', 'festival', 'Vilnius, Verkių parkas', '2026-05-25 18:31:03', '15:00:00', 0.00, 'draft', NULL, '2026-03-11 18:31:03', '2026-03-16 18:31:03'),
(6, 7, 'Kaimo sūrių edukacijos', 'Temos ir degustacijų grafikas.', 'food', 'Molėtai, Turgaus g. 2', '2026-04-27 18:31:03', '14:00:00', 6.50, 'pending', NULL, '2026-03-10 18:31:03', '2026-03-16 18:31:03'),
(7, 9, 'Lauko kino naktis', 'Filmų sąrašo juodraštis.', 'film', 'Alytus, S. Dariaus ir S. Girėno g. 13', '2026-04-18 18:31:03', '21:30:00', 2.50, 'draft', NULL, '2026-03-09 18:31:03', '2026-03-16 18:31:03'),
(8, 9, 'Dviračių maršruto plėtra', 'Papildomi sustojimai ir savanoriai.', 'sports', 'Birštonas, B. Sruogos g. 4', '2026-05-03 18:31:03', '09:00:00', 4.00, 'draft', NULL, '2026-03-08 18:31:03', '2026-03-16 18:31:03'),
(9, 11, 'Teatro vakaro repeticijos', 'Repeticijų tvarkaraštis.', 'theatre', 'Marijampolė, J. Basanavičiaus a. 3', '2026-04-23 18:31:03', '19:00:00', 7.00, 'draft', NULL, '2026-03-07 18:31:03', '2026-03-16 18:31:03'),
(10, 11, 'Skonių turo degustacijos', 'Planuojamos stotelės ir partneriai.', 'food', 'Kėdainiai, Didžioji g. 10', '2026-04-29 18:31:03', '12:00:00', 8.00, 'pending', NULL, '2026-03-06 18:31:03', '2026-03-16 18:31:03'),
(11, 3, 'Žygio Švenčionių variantas', 'Atsarginė maršruto schema.', 'sports', 'Švenčionys, Žeimenos g. 5', '2026-05-10 18:31:03', '08:00:00', 4.50, 'draft', NULL, '2026-03-05 18:31:03', '2026-03-16 18:31:03'),
(12, 5, 'Ukmergės mugės planavimas', 'Kūrėjų atrankos kriterijai.', 'market', 'Ukmergė, Vytauto g. 10', '2026-05-07 18:31:03', '10:00:00', 1.50, 'draft', NULL, '2026-03-04 18:31:03', '2026-03-16 18:31:03'),
(13, 7, 'Zarasų šventės scena', 'Apšvietimo ir garso poreikiai.', 'festival', 'Zarasai, Sėlių a. 22', '2026-05-17 18:31:03', '17:00:00', 0.00, 'draft', NULL, '2026-03-03 18:31:03', '2026-03-16 18:31:03'),
(14, 9, 'Meno savaitgalio galerijos', 'Galerijų sąrašas ir laikai.', 'arts', 'Druskininkai, Vilniaus al. 13', '2026-05-13 18:31:03', '13:00:00', 6.00, 'draft', NULL, '2026-03-02 18:31:03', '2026-03-16 18:31:03'),
(15, 11, 'Telšių istorijos maršrutas', 'Papildomos stotelės ir gidai.', 'culture', 'Telšiai, Turgaus a. 1', '2026-05-04 18:31:03', '15:30:00', 3.50, 'pending', NULL, '2026-03-01 18:31:03', '2026-03-16 18:31:03'),
(16, 5, 'Technologijų forumo darbotvarkė', 'Pagrindinių pranešėjų laikas.', 'conference', 'Vilnius, Konstitucijos pr. 26', '2026-05-20 18:31:03', '09:30:00', 12.00, 'draft', NULL, '2026-02-28 18:31:03', '2026-03-16 18:31:03'),
(17, 7, 'Šeimų pikniko scenarijus', 'Žaidimų ir koncertų seka.', 'community', 'Elektrėnai, Draugystės g. 17', '2026-05-01 18:31:03', '12:30:00', 0.00, 'draft', NULL, '2026-02-27 18:31:03', '2026-03-16 18:31:03'),
(18, 3, 'Naktinio bėgimo eskizas', 'Trasos apšvietimo planas.', 'sports', 'Vilnius, Neries krantinė', '2026-05-08 18:31:03', '22:00:00', 5.00, 'draft', NULL, '2026-02-26 18:31:03', '2026-03-16 18:31:03'),
(19, 9, 'Kino diskusijų tema', 'Temos pasiūlymai po seansų.', 'film', 'Alytus, S. Dariaus ir S. Girėno g. 13', '2026-04-21 18:31:03', '20:00:00', 0.00, 'draft', NULL, '2026-02-25 18:31:03', '2026-03-16 18:31:03'),
(20, 11, 'Gastro turas Žemaitijoje', 'Partnerių sąrašas ir maršrutas.', 'food', 'Telšiai, Turgaus a. 1', '2026-05-14 18:31:03', '14:00:00', 9.00, 'draft', NULL, '2026-02-24 18:31:03', '2026-03-16 18:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `event_reminders`
--

CREATE TABLE `event_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `minutes_before` int(11) NOT NULL,
  `remind_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tag` varchar(50) NOT NULL DEFAULT 'favorite',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `event_id`, `user_id`, `tag`, `created_at`) VALUES
(1, 1, 2, 'laukiu', '2026-03-15 18:31:03'),
(2, 2, 4, 'begu', '2026-03-14 18:31:03'),
(3, 3, 6, 'muzika', '2026-03-13 18:31:03'),
(4, 4, 8, 'dirbtuvės', '2026-03-12 18:31:03'),
(5, 5, 10, 'regata', '2026-03-11 18:31:03'),
(6, 6, 12, 'amatų mugė', '2026-03-10 18:31:03'),
(7, 7, 13, 'džiazas', '2026-03-09 18:31:03'),
(8, 8, 14, 'joninės', '2026-03-08 18:31:03'),
(9, 9, 15, 'sūris', '2026-03-07 18:31:03'),
(10, 10, 16, 'kino vakaras', '2026-03-06 18:31:03'),
(11, 11, 17, 'dviračiai', '2026-03-05 18:31:03'),
(12, 12, 18, 'teatras', '2026-03-04 18:31:03'),
(13, 13, 19, 'skonio turas', '2026-03-03 18:31:03'),
(14, 14, 20, 'miško žygis', '2026-03-02 18:31:03'),
(15, 15, 6, 'ukmergė', '2026-03-01 18:31:03'),
(16, 16, 8, 'zarasai', '2026-02-28 18:31:03'),
(17, 17, 10, 'druskininkai', '2026-02-27 18:31:03'),
(18, 18, 12, 'telsiai', '2026-02-26 18:31:03'),
(19, 19, 14, 'technologijos', '2026-02-25 18:31:03'),
(20, 20, 16, 'šeimos', '2026-02-24 18:31:03'),
(22, 7, 2, 'favorite', '2026-03-16 21:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `type` enum('user','organizer','admin') NOT NULL DEFAULT 'user',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 'Primename apie artėjantį senamiesčio turą.', 'user', 0, '2026-03-15 18:31:03'),
(2, 4, 'Renginys \"Neries vakaro bėgimas\" patvirtintas.', 'user', 1, '2026-03-14 18:31:03'),
(3, 6, 'Koncerto pradžia perkelta į 20:00.', 'user', 0, '2026-03-13 18:31:03'),
(4, 8, 'Dirbtuvės Kaune pasibaigė. Pasidalykite atsiliepimu!', 'user', 0, '2026-03-12 18:31:03'),
(5, 10, 'Regata Trakuose laukia jūsų šį savaitgalį.', 'user', 1, '2026-03-11 18:31:03'),
(6, 12, 'Amatų mugė Panevėžyje priminimas.', 'user', 0, '2026-03-10 18:31:03'),
(7, 13, 'Naujas džiazų vakaro komentaras.', 'user', 1, '2026-03-09 18:31:03'),
(8, 14, 'Joninių laužai prasidės 21:00.', 'user', 0, '2026-03-08 18:31:03'),
(9, 15, 'Kaimo sūrio degustacija: priminimas apie vietų ribotumą.', 'user', 0, '2026-03-07 18:31:03'),
(10, 16, 'Kino vakaras Alytuje perkeltas po stogu.', 'user', 1, '2026-03-06 18:31:03'),
(11, 17, 'Dviračių žygis Birštone: atvykite 30 min. anksčiau.', 'user', 0, '2026-03-05 18:31:03'),
(12, 18, 'Teatro vakaras Marijampolėje prasidės laiku.', 'user', 0, '2026-03-04 18:31:03'),
(13, 19, 'Skonių turas Kėdainiuose – liko 5 vietos.', 'user', 1, '2026-03-03 18:31:03'),
(14, 20, 'Švenčionių žygis atšauktas dėl oro sąlygų.', 'user', 0, '2026-03-02 18:31:03'),
(15, 6, 'Ukmergės kūrybos mugė sulaukė naujų dalyvių.', 'user', 0, '2026-03-01 18:31:03'),
(16, 8, 'Zarasų ežerų šventės programa atnaujinta.', 'user', 1, '2026-02-28 18:31:03'),
(17, 10, 'Meno savaitgalio registracija patvirtinta.', 'user', 0, '2026-02-27 18:31:03'),
(18, 12, 'Telšių istorijos maršruto bilietai atnaujinti.', 'user', 0, '2026-02-26 18:31:03'),
(19, 14, 'Technologijų forumo pranešėjų sąrašas paskelbtas.', 'user', 1, '2026-02-25 18:31:03'),
(20, 16, 'Elektrėnų šeimų piknikas ieško savanorių.', 'user', 0, '2026-02-24 18:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `time_offset` varchar(10) NOT NULL,
  `channels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`channels`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `user_id`, `event_id`, `time_offset`, `channels`, `created_at`) VALUES
(1, 2, 1, '30m', '[\"email\"]', '2026-03-15 18:31:03'),
(2, 4, 2, '1h', '[\"sms\",\"email\"]', '2026-03-14 18:31:03'),
(3, 6, 3, '1d', '[\"email\"]', '2026-03-13 18:31:03'),
(4, 8, 4, '2h', '[\"push\"]', '2026-03-12 18:31:03'),
(5, 10, 5, '1h', '[\"sms\"]', '2026-03-11 18:31:03'),
(6, 12, 6, '45m', '[\"email\"]', '2026-03-10 18:31:03'),
(7, 13, 7, '30m', '[\"email\",\"push\"]', '2026-03-09 18:31:03'),
(8, 14, 8, '1d', '[\"email\"]', '2026-03-08 18:31:03'),
(9, 15, 9, '2h', '[\"push\"]', '2026-03-07 18:31:03'),
(10, 16, 10, '1h', '[\"email\",\"sms\"]', '2026-03-06 18:31:03'),
(11, 17, 11, '30m', '[\"email\"]', '2026-03-05 18:31:03'),
(12, 18, 12, '2h', '[\"sms\"]', '2026-03-04 18:31:03'),
(13, 19, 13, '1d', '[\"push\",\"email\"]', '2026-03-03 18:31:03'),
(14, 20, 14, '1h', '[\"email\"]', '2026-03-02 18:31:03'),
(15, 6, 15, '30m', '[\"sms\"]', '2026-03-01 18:31:03'),
(16, 8, 16, '1h', '[\"push\"]', '2026-02-28 18:31:03'),
(17, 10, 17, '2h', '[\"email\"]', '2026-02-27 18:31:03'),
(18, 12, 18, '1d', '[\"email\",\"push\"]', '2026-02-26 18:31:03'),
(19, 14, 19, '45m', '[\"sms\"]', '2026-02-25 18:31:03'),
(20, 16, 20, '30m', '[\"email\"]', '2026-02-24 18:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `organizer_activity`
--

CREATE TABLE `organizer_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organizer_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_title` varchar(200) DEFAULT NULL,
  `type` enum('like','approved','declined','submitted') NOT NULL,
  `actor_name` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizer_activity`
--

INSERT INTO `organizer_activity` (`id`, `organizer_id`, `event_id`, `event_title`, `type`, `actor_name`, `created_at`) VALUES
(1, 3, 1, 'Senamiesčio vakarinis turas', 'approved', 'Sistemos administratorius', '2026-03-15 18:31:03'),
(2, 3, 2, 'Neries vakaro bėgimas', 'submitted', 'Renginių organizatorius A', '2026-03-13 18:31:03'),
(3, 3, 3, 'Klaipėdos vasaros koncertas', 'approved', 'Sistemos administratorius', '2026-03-12 18:31:03'),
(4, 3, 4, 'Kauno kūrybinių dirbtuvių diena', 'approved', 'Sistemos administratorius', '2026-03-14 18:31:03'),
(5, 3, 5, 'Trakų pilies regata', 'submitted', 'Renginių organizatorius A', '2026-03-11 18:31:03'),
(6, 5, 6, 'Panevėžio amatų mugė', 'approved', 'Sistemos administratorius', '2026-03-10 18:31:03'),
(7, 5, 7, 'Šiaulių džiazo vakaras', 'approved', 'Sistemos administratorius', '2026-03-09 18:31:03'),
(8, 7, 8, 'Joninių laužai', 'submitted', 'Renginių organizatorius C', '2026-03-08 18:31:03'),
(9, 7, 9, 'Kaimo sūrio degustacija', 'approved', 'Sistemos administratorius', '2026-03-07 18:31:03'),
(10, 9, 10, 'Alytaus kino vakarai', 'approved', 'Sistemos administratorius', '2026-03-06 18:31:03'),
(11, 9, 11, 'Birštono dviračių žygis', 'submitted', 'Renginių organizatorius D', '2026-03-05 18:31:03'),
(12, 11, 12, 'Marijampolės teatro vakaras', 'approved', 'Sistemos administratorius', '2026-03-04 18:31:03'),
(13, 11, 13, 'Kėdainių skonių turas', 'approved', 'Sistemos administratorius', '2026-03-03 18:31:03'),
(14, 3, 14, 'Švenčionių žygis', 'declined', 'Sistemos administratorius', '2026-03-02 18:31:03'),
(15, 5, 15, 'Ukmergės kūrybos mugė', 'approved', 'Sistemos administratorius', '2026-03-01 18:31:03'),
(16, 7, 16, 'Zarasų ežerų šventė', 'submitted', 'Renginių organizatorius C', '2026-02-28 18:31:03'),
(17, 9, 17, 'Druskininkų meno savaitgalis', 'approved', 'Sistemos administratorius', '2026-02-27 18:31:03'),
(18, 11, 18, 'Telšių istorijos maršrutas', 'approved', 'Sistemos administratorius', '2026-02-26 18:31:03'),
(19, 5, 19, 'Vilniaus technologijų forumas', 'approved', 'Sistemos administratorius', '2026-02-25 18:31:03'),
(20, 7, 20, 'Elektrėnų šeimų piknikas', 'approved', 'Sistemos administratorius', '2026-02-24 18:31:03'),
(21, 5, 7, 'Šiaulių džiazo vakaras', 'like', NULL, '2026-03-16 21:55:04'),
(22, 5, 7, 'Šiaulių džiazo vakaras', 'like', NULL, '2026-03-16 21:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','organizer','admin') NOT NULL DEFAULT 'user',
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Sistemos administratorius', 'admin@cityevents.lt', '$2y$12$QHKFqU5bRyFDTMQrvB0IeOYYPu3JshS.5jKELv5g1h1kKYDrnTWB.', 'admin', '+37060000001', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(2, 'Miesto lankytojas A', 'user@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000002', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(3, 'Renginių organizatorius A', 'organizer@cityevents.lt', '$2y$12$NMogWo4mF985XFLYnF/Ng.eKsvO4XkxbUfetyS2vFRugyGzr082eu', 'organizer', '+37060000003', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(4, 'Miesto lankytojas B', 'user2@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000004', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(5, 'Renginių organizatorius B', 'organizer2@cityevents.lt', '$2y$12$NMogWo4mF985XFLYnF/Ng.eKsvO4XkxbUfetyS2vFRugyGzr082eu', 'organizer', '+37060000005', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(6, 'Miesto lankytojas C', 'user3@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000006', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(7, 'Renginių organizatorius C', 'organizer3@cityevents.lt', '$2y$12$NMogWo4mF985XFLYnF/Ng.eKsvO4XkxbUfetyS2vFRugyGzr082eu', 'organizer', '+37060000007', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(8, 'Miesto lankytojas D', 'user4@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000008', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(9, 'Renginių organizatorius D', 'organizer4@cityevents.lt', '$2y$12$NMogWo4mF985XFLYnF/Ng.eKsvO4XkxbUfetyS2vFRugyGzr082eu', 'organizer', '+37060000009', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(10, 'Miesto lankytojas E', 'user5@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000010', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(11, 'Renginių organizatorius E', 'organizer5@cityevents.lt', '$2y$12$NMogWo4mF985XFLYnF/Ng.eKsvO4XkxbUfetyS2vFRugyGzr082eu', 'organizer', '+37060000011', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(12, 'Miesto lankytojas F', 'user6@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000012', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(13, 'Miesto lankytojas G', 'user7@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000013', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(14, 'Miesto lankytojas H', 'user8@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000014', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(15, 'Miesto lankytojas I', 'user9@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000015', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(16, 'Miesto lankytojas J', 'user10@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000016', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(17, 'Miesto lankytojas K', 'user11@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000017', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(18, 'Miesto lankytojas L', 'user12@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000018', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(19, 'Miesto lankytojas M', 'user13@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000019', '2026-03-16 18:31:03', '2026-03-16 18:31:03'),
(20, 'Miesto lankytojas N', 'user14@cityevents.lt', '$2y$12$Dm.FpYmla9G5UflFKt6dX.hpJ3QJ3jWAjn.EFz1BNT7NmXy97KXt2', 'user', '+37060000020', '2026-03-16 18:31:03', '2026-03-16 18:31:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_organizer` (`organizer_id`),
  ADD KEY `idx_events_status` (`status`),
  ADD KEY `idx_events_event_date` (`event_date`);

--
-- Indexes for table `event_drafts`
--
ALTER TABLE `event_drafts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_drafts_organizer` (`organizer_id`);

--
-- Indexes for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_reminders_user_event` (`user_id`,`event_id`),
  ADD KEY `idx_event_reminders_remind_at` (`remind_at`),
  ADD KEY `idx_event_reminders_event_id` (`event_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_favorites_user` (`user_id`),
  ADD KEY `idx_favorites_event` (`event_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_event` (`user_id`,`event_id`),
  ADD KEY `fk_notification_settings_event` (`event_id`);

--
-- Indexes for table `organizer_activity`
--
ALTER TABLE `organizer_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_organizer_activity_organizer` (`organizer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `event_drafts`
--
ALTER TABLE `event_drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `event_reminders`
--
ALTER TABLE `event_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `organizer_activity`
--
ALTER TABLE `organizer_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD CONSTRAINT `fk_blocked_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_drafts`
--
ALTER TABLE `event_drafts`
  ADD CONSTRAINT `fk_event_drafts_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD CONSTRAINT `fk_event_reminders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_reminders_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `fk_notification_settings_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organizer_activity`
--
ALTER TABLE `organizer_activity`
  ADD CONSTRAINT `fk_organizer_activity_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
