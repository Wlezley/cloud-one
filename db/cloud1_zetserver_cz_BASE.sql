-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Úte 19. říj 2021, 18:31
-- Verze serveru: 10.1.45-MariaDB-0+deb9u1
-- Verze PHP: 7.2.34-18+0~20210223.60+debian9~1.gbpb21322

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `cloud1_zetserver_cz`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `storage_files`
--

CREATE TABLE `storage_files` (
  `fileID` int(11) NOT NULL COMMENT 'ID souboru',
  `ownerID` int(11) NOT NULL COMMENT 'ID majitele souboru',
  `fileName` varchar(512) NOT NULL COMMENT 'Jmeno souboru',
  `fileMime` varchar(64) NOT NULL COMMENT 'Pripona souboru',
  `fileSize` bigint(16) DEFAULT NULL COMMENT 'Velikost souboru',
  `date_upload` timestamp NULL DEFAULT NULL COMMENT 'Datum nahrani',
  `date_download` timestamp NULL DEFAULT NULL COMMENT 'Datum stazeni',
  `date_delete` timestamp NULL DEFAULT NULL COMMENT 'Datum smazani',
  `date_modify` timestamp NULL DEFAULT NULL COMMENT 'Datum zmeny',
  `fileChecksum` char(32) NOT NULL COMMENT 'Kontrolni soucet MD5',
  `storageID` char(16) NOT NULL COMMENT 'ID souboru na disku',
  `downloadID` char(16) NOT NULL COMMENT 'ID pro download'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabulky `user_accounts`
--

CREATE TABLE `user_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(128) NOT NULL,
  `email` varchar(64) NOT NULL,
  `telefon` varchar(16) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Vypisuji data pro tabulku `user_accounts`
--

INSERT INTO `user_accounts` (`id`, `username`, `fullname`, `email`, `telefon`, `password`, `role`) VALUES
(1, 'alexa', 'admin', '', '', '$2y$10$wspXsfg1NCNZaRA6iocwKuS0MrqbM6Nn4S8cDTNIhd0jAW.c0F3um', 'superadmin');

-- --------------------------------------------------------

--
-- Struktura tabulky `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `roles` varchar(255) NOT NULL,
  `allowLogin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Vypisuji data pro tabulku `user_roles`
--

INSERT INTO `user_roles` (`id`, `name`, `roles`, `allowLogin`) VALUES
(1, 'superadmin', '', 1),
(2, 'admin', '', 1),
(3, 'mod1', '', 0),
(4, 'mod2', '', 0),
(5, 'user', '', 0);

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `storage_files`
--
ALTER TABLE `storage_files`
  ADD PRIMARY KEY (`fileID`);

--
-- Klíče pro tabulku `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `storage_files`
--
ALTER TABLE `storage_files`
  MODIFY `fileID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID souboru', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT pro tabulku `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
