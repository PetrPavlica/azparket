-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Čtv 07. pro 2023, 08:39
-- Verze serveru: 10.1.38-MariaDB
-- Verze PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `lagus`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `absence`
--

CREATE TABLE `absence` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `user_delegate_id` int(11) DEFAULT NULL,
  `reason_id` int(11) DEFAULT NULL,
  `originator_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `time_range` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_worked` tinyint(1) NOT NULL,
  `whole_day` tinyint(1) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `absence_reason`
--

CREATE TABLE `absence_reason` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_order` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `absence_reason`
--

INSERT INTO `absence_reason` (`id`, `name`, `state_order`, `created_at`, `updated_at`) VALUES
(1, 'Dovolená', 10, '2022-08-29 12:20:04', '2022-08-29 12:20:04'),
(2, 'Doktor', 20, '2022-08-29 12:20:18', '2022-08-29 12:20:18'),
(3, 'Pracovní neschopnost', 30, '2022-08-29 12:20:34', '2022-08-29 12:20:34'),
(4, 'Neomluvená absence', 40, '2022-08-29 12:21:01', '2022-08-29 12:21:01'),
(5, 'Pozdní příchod', 50, '2022-08-29 12:21:19', '2022-08-29 12:21:26'),
(6, 'Náhradní volno', 60, '2022-08-29 12:21:39', '2022-08-29 12:21:39'),
(7, 'Ostatní', 70, '2022-08-29 12:21:48', '2022-08-29 12:21:48');

-- --------------------------------------------------------

--
-- Struktura tabulky `absence_state`
--

CREATE TABLE `absence_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_order` int(11) DEFAULT NULL,
  `for_all` tinyint(1) NOT NULL,
  `allow_edit_tech` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `absence_state`
--

INSERT INTO `absence_state` (`id`, `name`, `state_order`, `for_all`, `allow_edit_tech`, `created_at`, `updated_at`) VALUES
(1, 'Nepodáno', 10, 0, 1, '2022-08-25 14:16:52', '2022-08-25 14:16:52'),
(2, 'Čeká na schválení', 20, 0, 0, '2022-08-25 14:18:01', '2022-08-25 14:18:01'),
(3, 'Neschváleno', 30, 0, 0, '2022-08-25 14:18:16', '2022-08-25 14:18:16'),
(4, 'Schváleno', 40, 0, 0, '2022-08-25 14:18:28', '2022-08-25 14:18:28');

-- --------------------------------------------------------

--
-- Struktura tabulky `api_client`
--

CREATE TABLE `api_client` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve`
--

CREATE TABLE `approve` (
  `id` int(11) NOT NULL,
  `approve_state_id` int(11) DEFAULT NULL,
  `approve_time_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_short` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `send_finish` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve_document`
--

CREATE TABLE `approve_document` (
  `id` int(11) NOT NULL,
  `approve_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve_norm`
--

CREATE TABLE `approve_norm` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `norm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve_part`
--

CREATE TABLE `approve_part` (
  `id` int(11) NOT NULL,
  `approve_state_id` int(11) DEFAULT NULL,
  `approve_id` int(11) DEFAULT NULL,
  `inter_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_mark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_area` double DEFAULT NULL,
  `cus_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cus_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tech_total_from` double DEFAULT NULL,
  `tech_total_to` double DEFAULT NULL,
  `tech_zn_from` double DEFAULT NULL,
  `tech_zn_to` double DEFAULT NULL,
  `tech_ktl_from` double DEFAULT NULL,
  `tech_ktl_to` double DEFAULT NULL,
  `tech_pra_from` double DEFAULT NULL,
  `tech_pra_to` double DEFAULT NULL,
  `tech_demand1` tinyint(1) DEFAULT NULL,
  `tech_demand2` tinyint(1) DEFAULT NULL,
  `tech_demand3` tinyint(1) DEFAULT NULL,
  `tech_demand4` tinyint(1) DEFAULT NULL,
  `inter_demand1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_demand2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_demand3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` double DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_tk` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `approve_user_tk_id` int(11) DEFAULT NULL,
  `approve_user_chpu_id` int(11) DEFAULT NULL,
  `approve_user_vpu_id` int(11) DEFAULT NULL,
  `approve_user_refo_id` int(11) DEFAULT NULL,
  `approve_user_tpv_id` int(11) DEFAULT NULL,
  `blasting` tinyint(1) DEFAULT NULL,
  `time_norm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tech_demand5` tinyint(1) DEFAULT NULL,
  `tech_demand6` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tech_demand7` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_result_tk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_note_tk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_result_chpu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_chpu` date DEFAULT NULL,
  `approve_note_chpu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_result_vpu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_vpu` date DEFAULT NULL,
  `approve_note_vpu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_result_refo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_refo` date DEFAULT NULL,
  `approve_note_refo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_result_tpv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_tpv` date DEFAULT NULL,
  `approve_note_tpv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `norm1_id` int(11) DEFAULT NULL,
  `norm2_id` int(11) DEFAULT NULL,
  `approve_user_koop_id` int(11) DEFAULT NULL,
  `approve_user_pers_id` int(11) DEFAULT NULL,
  `tech_zn_from2` double DEFAULT NULL,
  `tech_zn_to2` double DEFAULT NULL,
  `tech_ktl_from2` double DEFAULT NULL,
  `tech_ktl_to2` double DEFAULT NULL,
  `tech_pra_from2` double DEFAULT NULL,
  `tech_pra_to2` double DEFAULT NULL,
  `tech_zn_from3` double DEFAULT NULL,
  `tech_zn_to3` double DEFAULT NULL,
  `tech_ktl_from3` double DEFAULT NULL,
  `tech_ktl_to3` double DEFAULT NULL,
  `tech_pra_from3` double DEFAULT NULL,
  `tech_pra_to3` double DEFAULT NULL,
  `approve_result_koop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_koop` date DEFAULT NULL,
  `approve_note_koop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_result_pers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date_pers` date DEFAULT NULL,
  `approve_note_pers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `norm_file1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `norm_file2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve_part_document`
--

CREATE TABLE `approve_part_document` (
  `id` int(11) NOT NULL,
  `approve_part_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve_state`
--

CREATE TABLE `approve_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `approve_time`
--

CREATE TABLE `approve_time` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_days` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `order_article` int(11) NOT NULL,
  `publish` datetime DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_default`
--

CREATE TABLE `article_default` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `show_name` tinyint(1) NOT NULL,
  `dropdown` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_event`
--

CREATE TABLE `article_event` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perex` longtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `show_name` tinyint(1) NOT NULL,
  `primary_on_hp` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_file`
--

CREATE TABLE `article_file` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_file` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_file_in_language`
--

CREATE TABLE `article_file_in_language` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_gallery`
--

CREATE TABLE `article_gallery` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `show_name` tinyint(1) NOT NULL,
  `dropdown` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_image`
--

CREATE TABLE `article_image` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_img` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_in_menu`
--

CREATE TABLE `article_in_menu` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_new`
--

CREATE TABLE `article_new` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perex` longtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `show_name` tinyint(1) NOT NULL,
  `date_start` date DEFAULT NULL,
  `date_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_in_calendar` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_template`
--

CREATE TABLE `article_template` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perex` longtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `show_name` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `article_zo`
--

CREATE TABLE `article_zo` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perex` longtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `show_name` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `banner`
--

CREATE TABLE `banner` (
  `id` int(11) NOT NULL,
  `order_banner` int(11) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `banner_language`
--

CREATE TABLE `banner_language` (
  `id` int(11) NOT NULL,
  `banner_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `banner_partner`
--

CREATE TABLE `banner_partner` (
  `id` int(11) NOT NULL,
  `order_banner` int(11) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `banner_partner_language`
--

CREATE TABLE `banner_partner_language` (
  `id` int(11) NOT NULL,
  `banner_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `configurator`
--

CREATE TABLE `configurator` (
  `id` int(11) NOT NULL,
  `order_configurator` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `start_node_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `configurator_input`
--

CREATE TABLE `configurator_input` (
  `id` int(11) NOT NULL,
  `configurator_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `order_input` int(11) NOT NULL,
  `web_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `configurator_node`
--

CREATE TABLE `configurator_node` (
  `id` int(11) NOT NULL,
  `input_id` int(11) DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `node_no` int(11) DEFAULT NULL,
  `configurator_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_salesman` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `configurator_node_product`
--

CREATE TABLE `configurator_node_product` (
  `id` int(11) NOT NULL,
  `node_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `configurator_node_relation`
--

CREATE TABLE `configurator_node_relation` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `child_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `currency`
--

CREATE TABLE `currency` (
  `id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_currency` int(11) DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `count_decimal` double DEFAULT NULL,
  `mark_before` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mark_behind` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `currency`
--

INSERT INTO `currency` (`id`, `code`, `name`, `order_currency`, `exchange_rate`, `count_decimal`, `mark_before`, `mark_behind`, `active`, `created_at`, `updated_at`) VALUES
(1, 'CZK', 'Kč', 1, 1, 0, '', 'Kč', 1, '2021-05-07 09:25:21', '2021-05-07 09:25:21'),
(2, 'EUR', '€', 2, 25.445, 2, '', '€', 1, '2021-05-07 09:27:20', '2021-05-20 10:07:42'),
(3, 'GBP', 'Libra', 3, 29.511, 2, '£', '', 1, '2021-05-07 09:28:11', '2021-05-20 10:07:42'),
(4, 'USD', 'Dolar', 4, 20.835, 2, '$', '', 1, '2021-05-07 09:29:13', '2021-05-20 10:07:42');

-- --------------------------------------------------------

--
-- Struktura tabulky `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `customer_state_id` int(11) DEFAULT NULL,
  `degree` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_change_state` datetime DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `www` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `maturity` int(11) DEFAULT NULL,
  `constant_symbol` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `contract_notify` tinyint(1) DEFAULT NULL,
  `created_by_reservation` tinyint(1) DEFAULT NULL,
  `workshop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recovery_hash` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `customer_in_type`
--

CREATE TABLE `customer_in_type` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `customer_notification`
--

CREATE TABLE `customer_notification` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `process_state_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `customer_ordered`
--

CREATE TABLE `customer_ordered` (
  `id` int(11) NOT NULL,
  `customer_state_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `degree` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_delivery` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_change_state` datetime DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `www` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `maturity` int(11) DEFAULT NULL,
  `constant_symbol` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `customer_state`
--

CREATE TABLE `customer_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_order` int(11) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `customer_state`
--

INSERT INTO `customer_state` (`id`, `name`, `state_order`, `visible`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Suspect', 1, 1, '', '0000-00-00 00:00:00', NULL),
(2, 'Identified', 2, 1, '', '0000-00-00 00:00:00', NULL),
(3, 'Qualified', 3, 1, '', '0000-00-00 00:00:00', NULL),
(4, 'Validated', 4, 1, '', '0000-00-00 00:00:00', NULL),
(5, 'Proposed', 5, 1, '', '0000-00-00 00:00:00', NULL),
(6, 'Won', 6, 0, '', '0000-00-00 00:00:00', NULL),
(7, 'Lost', 7, 0, '', '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `customer_type`
--

CREATE TABLE `customer_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `datagrid_options`
--

CREATE TABLE `datagrid_options` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `value_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `datagrid_options`
--

INSERT INTO `datagrid_options` (`id`, `user_id`, `key_name`, `value`, `value_type`, `customer`) VALUES
(1, 1, 'Intra:Worker:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(2, 1, 'Intra:Worker:table__grid_perPage', '40', 'string', 0),
(3, 1, 'Intra:Worker:table__grid_hidden_columns', '[\"street\",\"zip\",\"country\",\"insurance\",\"male\",\"startDate\",\"endDate\",\"endContractDate\",\"calendarColor\",\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(4, 1, 'Intra:Worker:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(5, 1, 'Intra:Users:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(6, 1, 'Intra:Users:table__grid_perPage', '40', 'string', 0),
(7, 1, 'Intra:Users:table__grid_hidden_columns', '[\"phone\",\"mobile\",\"fax\",\"email\",\"lastLoggedAt\",\"signature\",\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(8, 1, 'Intra:Users:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(9, 2, 'Intra:Users:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(10, 2, 'Intra:Users:table__grid_perPage', '40', 'string', 0),
(11, 2, 'Intra:Users:table__grid_hidden_columns', '[\"phone\",\"mobile\",\"fax\",\"email\",\"lastLoggedAt\",\"signature\",\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(12, 2, 'Intra:Users:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(13, 2, 'Intra:Setting:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(14, 2, 'Intra:Setting:table__grid_perPage', '40', 'string', 0),
(15, 2, 'Intra:Setting:table__grid_hidden_columns', '[\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(16, 2, 'Intra:Setting:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(17, 2, 'Intra:Menu:table__grid_sort', '{\"order_page\":\"ASC\"}', 'array', 0),
(18, 2, 'Intra:Menu:table__grid_perPage', '40', 'string', 0),
(19, 2, 'Intra:Menu:table__grid_hidden_columns', '[\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(20, 2, 'Intra:Menu:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(21, 2, 'Intra:Article:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(22, 2, 'Intra:Article:table__grid_perPage', '40', 'string', 0),
(23, 2, 'Intra:Article:table__grid_hidden_columns', '[\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(24, 2, 'Intra:Article:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(25, 2, 'Intra:Language:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(26, 2, 'Intra:Language:table__grid_perPage', '40', 'string', 0),
(27, 2, 'Intra:Language:table__grid_hidden_columns', '[\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(28, 2, 'Intra:Language:table__grid_hidden_columns_manipulated', '1', 'bool', 0),
(29, 2, 'Intra:Translation:table__grid_sort', '{\"createdAt\":\"DESC\"}', 'array', 0),
(30, 2, 'Intra:Translation:table__grid_perPage', '40', 'string', 0),
(31, 2, 'Intra:Translation:table__grid_hidden_columns', '[\"id\",\"createdAt\",\"updatedAt\"]', 'array', 0),
(32, 2, 'Intra:Translation:table__grid_hidden_columns_manipulated', '1', 'bool', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `delivery_price`
--

CREATE TABLE `delivery_price` (
  `id` int(11) NOT NULL,
  `min_dist` int(11) NOT NULL,
  `max_dist` int(11) NOT NULL,
  `price` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `flat` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `document`
--

CREATE TABLE `document` (
  `id` int(11) NOT NULL,
  `field_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `employment`
--

CREATE TABLE `employment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `czicse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `extern_service_visit`
--

CREATE TABLE `extern_service_visit` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visit_date` date DEFAULT NULL,
  `repeat_period` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `calendar_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `field`
--

CREATE TABLE `field` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `inquiry`
--

CREATE TABLE `inquiry` (
  `id` int(11) NOT NULL,
  `configurator_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_auto` tinyint(1) DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `note` longtext COLLATE utf8mb4_unicode_ci,
  `install_city` longtext COLLATE utf8mb4_unicode_ci,
  `install_zip` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `needs_salesman` tinyint(1) NOT NULL,
  `for_family_house` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `inquiry_product`
--

CREATE TABLE `inquiry_product` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `klic_polozky` int(11) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `inquiry_value`
--

CREATE TABLE `inquiry_value` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `item_in_process`
--

CREATE TABLE `item_in_process` (
  `id` int(11) NOT NULL,
  `process_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `item_type`
--

CREATE TABLE `item_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `item_type_in_item`
--

CREATE TABLE `item_type_in_item` (
  `id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `language`
--

CREATE TABLE `language` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_code` int(11) NOT NULL,
  `default_code` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `language`
--

INSERT INTO `language` (`id`, `name`, `code`, `order_code`, `default_code`, `created_at`, `updated_at`) VALUES
(1, 'Čeština', 'cs', 10, 1, '2023-01-02 10:01:33', '2023-01-02 10:01:33'),
(2, 'Angličtina', 'en', 20, 0, '2023-01-02 10:01:33', '2023-01-02 10:01:33');

-- --------------------------------------------------------

--
-- Struktura tabulky `machine`
--

CREATE TABLE `machine` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reg_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `weight` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `machine_in_extern_service_visit`
--

CREATE TABLE `machine_in_extern_service_visit` (
  `id` int(11) NOT NULL,
  `machine_id` int(11) DEFAULT NULL,
  `extern_service_visit_id` int(11) DEFAULT NULL,
  `result` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_desc` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `managed_change`
--

CREATE TABLE `managed_change` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `originator_id` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `customer_change_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intern_short_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci,
  `doc_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_mark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created_at` date DEFAULT NULL,
  `date_required_end` date DEFAULT NULL,
  `date_real_end` date DEFAULT NULL,
  `reason` longtext COLLATE utf8mb4_unicode_ci,
  `result_of_examination` longtext COLLATE utf8mb4_unicode_ci,
  `actual_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `resulted_by_id` int(11) DEFAULT NULL,
  `result` int(11) DEFAULT NULL,
  `parent_change_id` int(11) DEFAULT NULL,
  `approve_user_id` int(11) DEFAULT NULL,
  `doc_text` longtext COLLATE utf8mb4_unicode_ci,
  `approve_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `managed_change_step`
--

CREATE TABLE `managed_change_step` (
  `id` int(11) NOT NULL,
  `managed_change_id` int(11) DEFAULT NULL,
  `implementation_management` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `responsible` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `managed_risc`
--

CREATE TABLE `managed_risc` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `probability` int(11) NOT NULL,
  `relevance` int(11) NOT NULL,
  `detectability` int(11) NOT NULL,
  `benefit` int(11) NOT NULL,
  `feasibility` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `aspect` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aspect_risc_desc` longtext COLLATE utf8mb4_unicode_ci,
  `aspect_oppor_desc` longtext COLLATE utf8mb4_unicode_ci,
  `measure_risc` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `measure_oppor` longtext COLLATE utf8mb4_unicode_ci,
  `date_risc` date DEFAULT NULL,
  `date_oppor` date DEFAULT NULL,
  `risc_respond` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oppor_respond` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interested_party_expectations` longtext COLLATE utf8mb4_unicode_ci,
  `interested_party_type` int(11) DEFAULT NULL,
  `interested_party_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `managed_risc_revaluation`
--

CREATE TABLE `managed_risc_revaluation` (
  `id` int(11) NOT NULL,
  `managed_risc_id` int(11) DEFAULT NULL,
  `revaluation_date` date DEFAULT NULL,
  `probability` int(11) DEFAULT NULL,
  `relevance` int(11) DEFAULT NULL,
  `detectability` int(11) DEFAULT NULL,
  `benefit` int(11) DEFAULT NULL,
  `feasibility` int(11) DEFAULT NULL,
  `realization_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `reval_respond` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `number` longtext COLLATE utf8mb4_unicode_ci,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` longtext COLLATE utf8mb4_unicode_ci,
  `price_sale` double DEFAULT NULL,
  `orig_name` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `qwp` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `link` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `material_group`
--

CREATE TABLE `material_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group_order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `material_need_buy`
--

CREATE TABLE `material_need_buy` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_buy` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `material_on_visit`
--

CREATE TABLE `material_on_visit` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `number` longtext COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `stock` longtext COLLATE utf8mb4_unicode_ci,
  `unit` longtext COLLATE utf8mb4_unicode_ci,
  `count` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `material_stock`
--

CREATE TABLE `material_stock` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `parent_menu_id` int(11) DEFAULT NULL,
  `order_page` int(11) NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `hide_in_select` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `menu`
--

INSERT INTO `menu` (`id`, `parent_menu_id`, `order_page`, `image`, `created_at`, `updated_at`, `hide_in_select`) VALUES
(1, NULL, 10, NULL, '2023-01-02 10:30:10', '2023-08-11 11:33:17', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `menu_language`
--

CREATE TABLE `menu_language` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_on_front` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_on_sub_front` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `show_up` tinyint(1) NOT NULL,
  `new_window` tinyint(1) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_on_homepage` tinyint(1) DEFAULT NULL,
  `show_signpost` tinyint(1) DEFAULT NULL,
  `menu_description` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `menu_language`
--

INSERT INTO `menu_language` (`id`, `menu_id`, `lang_id`, `name`, `name_on_front`, `name_on_sub_front`, `url`, `link`, `visible`, `show_up`, `new_window`, `title`, `keywords`, `description`, `show_on_homepage`, `show_signpost`, `menu_description`) VALUES
(1, 1, 1, 'Úvod', 'Úvod', 'Úvod', '/', NULL, 1, 0, 0, 'Úvod', NULL, NULL, 1, 0, '<p>Úvodní strana</p>\r\n'),
(2, 1, 2, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `offer`
--

CREATE TABLE `offer` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `salesman_id` int(11) DEFAULT NULL,
  `product_description_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `originator_id` int(11) DEFAULT NULL,
  `inquiry_id` int(11) DEFAULT NULL,
  `offer_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `send_date` datetime DEFAULT NULL,
  `add_toc` tinyint(1) DEFAULT NULL,
  `add_pricing` tinyint(1) DEFAULT NULL,
  `add_footer` tinyint(1) DEFAULT NULL,
  `state` int(11) NOT NULL,
  `planned_send_date` datetime DEFAULT NULL,
  `auto_send` tinyint(1) NOT NULL,
  `accept_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` double DEFAULT NULL,
  `price_delivery` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `price_install` double DEFAULT NULL,
  `price_crane` double DEFAULT NULL,
  `accept_date` datetime DEFAULT NULL,
  `install_city` longtext COLLATE utf8mb4_unicode_ci,
  `install_zip` longtext COLLATE utf8mb4_unicode_ci,
  `new` tinyint(1) NOT NULL,
  `transport_count` int(11) DEFAULT NULL,
  `transport_time` double DEFAULT NULL,
  `install_workers` int(11) DEFAULT NULL,
  `install_distance` double DEFAULT NULL,
  `vat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `offer_part`
--

CREATE TABLE `offer_part` (
  `id` int(11) NOT NULL,
  `offer_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int(11) NOT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `is_chapter` tinyint(1) DEFAULT NULL,
  `page_break` tinyint(1) DEFAULT NULL,
  `is_after_pricing` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `offer_part_template`
--

CREATE TABLE `offer_part_template` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int(11) NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `type` int(11) DEFAULT NULL,
  `is_after_pricing` tinyint(1) NOT NULL,
  `is_default` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `offer_product`
--

CREATE TABLE `offer_product` (
  `id` int(11) NOT NULL,
  `offer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL,
  `klic_polozky` int(11) DEFAULT NULL,
  `price` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `operation_log`
--

CREATE TABLE `operation_log` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_public` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_string` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_plan` date DEFAULT NULL,
  `production_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hinge_technology` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check1n1` tinyint(1) DEFAULT NULL,
  `check1n2` tinyint(1) DEFAULT NULL,
  `check2n1` tinyint(1) DEFAULT NULL,
  `check2n2` tinyint(1) DEFAULT NULL,
  `check3n1` tinyint(1) DEFAULT NULL,
  `check3n2` tinyint(1) DEFAULT NULL,
  `check4n1` tinyint(1) DEFAULT NULL,
  `check4n2` tinyint(1) DEFAULT NULL,
  `check5n1` tinyint(1) DEFAULT NULL,
  `check5n2` tinyint(1) DEFAULT NULL,
  `check6n1` tinyint(1) DEFAULT NULL,
  `check6n2` tinyint(1) DEFAULT NULL,
  `check7n1` tinyint(1) DEFAULT NULL,
  `check7n2` tinyint(1) DEFAULT NULL,
  `check8n1` tinyint(1) DEFAULT NULL,
  `check9n1` tinyint(1) DEFAULT NULL,
  `check10n1` tinyint(1) DEFAULT NULL,
  `check10n2` tinyint(1) DEFAULT NULL,
  `check10n3` tinyint(1) DEFAULT NULL,
  `check10n4` tinyint(1) DEFAULT NULL,
  `give_items_check` tinyint(1) DEFAULT NULL,
  `take_items_check` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `end_run` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_run_changed` datetime DEFAULT NULL,
  `end_run_date` datetime DEFAULT NULL,
  `end_run_date_changed` datetime DEFAULT NULL,
  `release_start_date` datetime DEFAULT NULL,
  `release_start_date_changed` datetime DEFAULT NULL,
  `release_end_date` datetime DEFAULT NULL,
  `release_end_date_changed` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `release_date_changed` datetime DEFAULT NULL,
  `check1n1changed` datetime DEFAULT NULL,
  `check1n2changed` datetime DEFAULT NULL,
  `check2n1changed` datetime DEFAULT NULL,
  `check2n2changed` datetime DEFAULT NULL,
  `check3n1changed` datetime DEFAULT NULL,
  `check3n2changed` datetime DEFAULT NULL,
  `check4n1changed` datetime DEFAULT NULL,
  `check4n2changed` datetime DEFAULT NULL,
  `check5n1changed` datetime DEFAULT NULL,
  `check5n2changed` datetime DEFAULT NULL,
  `check6n1changed` datetime DEFAULT NULL,
  `check6n2changed` datetime DEFAULT NULL,
  `check7n1changed` datetime DEFAULT NULL,
  `check7n2changed` datetime DEFAULT NULL,
  `check8n1changed` datetime DEFAULT NULL,
  `check9n1changed` datetime DEFAULT NULL,
  `check10n1changed` datetime DEFAULT NULL,
  `check10n2changed` datetime DEFAULT NULL,
  `check10n3changed` datetime DEFAULT NULL,
  `check10n4changed` datetime DEFAULT NULL,
  `give_item1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item1changed` datetime DEFAULT NULL,
  `give_item1note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item1note_changed` datetime DEFAULT NULL,
  `give_item2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item2changed` datetime DEFAULT NULL,
  `give_item2note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item2note_changed` datetime DEFAULT NULL,
  `give_item3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item3changed` datetime DEFAULT NULL,
  `give_item3note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item3note_changed` datetime DEFAULT NULL,
  `give_item4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item4changed` datetime DEFAULT NULL,
  `give_item4note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item4note_changed` datetime DEFAULT NULL,
  `give_item5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item5changed` datetime DEFAULT NULL,
  `give_item5note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item5note_changed` datetime DEFAULT NULL,
  `give_item6` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item6changed` datetime DEFAULT NULL,
  `give_item6note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item6note_changed` datetime DEFAULT NULL,
  `give_item7` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item7changed` datetime DEFAULT NULL,
  `give_item7note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item7note_changed` datetime DEFAULT NULL,
  `give_item8` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item8changed` datetime DEFAULT NULL,
  `give_item8note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item8note_changed` datetime DEFAULT NULL,
  `give_item9` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item9changed` datetime DEFAULT NULL,
  `give_item9note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item9note_changed` datetime DEFAULT NULL,
  `give_item10` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item10changed` datetime DEFAULT NULL,
  `give_item10note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item10note_changed` datetime DEFAULT NULL,
  `give_item11` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item11changed` datetime DEFAULT NULL,
  `give_item11note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item11note_changed` datetime DEFAULT NULL,
  `give_item12` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item12changed` datetime DEFAULT NULL,
  `give_item12note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `give_item12note_changed` datetime DEFAULT NULL,
  `take_item1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item1changed` datetime DEFAULT NULL,
  `take_item1note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item1note_changed` datetime DEFAULT NULL,
  `take_item2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item2changed` datetime DEFAULT NULL,
  `take_item2note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item2note_changed` datetime DEFAULT NULL,
  `take_item3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item3changed` datetime DEFAULT NULL,
  `take_item3note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item3note_changed` datetime DEFAULT NULL,
  `take_item4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item4changed` datetime DEFAULT NULL,
  `take_item4note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item4note_changed` datetime DEFAULT NULL,
  `take_item5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item5changed` datetime DEFAULT NULL,
  `take_item5note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item5note_changed` datetime DEFAULT NULL,
  `take_item6` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item6changed` datetime DEFAULT NULL,
  `take_item6note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item6note_changed` datetime DEFAULT NULL,
  `take_item7` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item7changed` datetime DEFAULT NULL,
  `take_item7note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item7note_changed` datetime DEFAULT NULL,
  `take_item8` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item8changed` datetime DEFAULT NULL,
  `take_item8note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item8note_changed` datetime DEFAULT NULL,
  `take_item9` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item9changed` datetime DEFAULT NULL,
  `take_item9note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item9note_changed` datetime DEFAULT NULL,
  `take_item10` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item10changed` datetime DEFAULT NULL,
  `take_item10note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item10note_changed` datetime DEFAULT NULL,
  `take_item11` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item11changed` datetime DEFAULT NULL,
  `take_item11note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item11note_changed` datetime DEFAULT NULL,
  `take_item12` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item12changed` datetime DEFAULT NULL,
  `take_item12note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_item12note_changed` datetime DEFAULT NULL,
  `take_items_check_changed` datetime DEFAULT NULL,
  `give_items_check_changed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `operation_log_item`
--

CREATE TABLE `operation_log_item` (
  `id` int(11) NOT NULL,
  `operation_log_id` int(11) DEFAULT NULL,
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inter_number` int(11) DEFAULT NULL,
  `rod` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `typ` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counts` double DEFAULT NULL,
  `result1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counts_result2` double DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `result1changed` datetime DEFAULT NULL,
  `result2changed` datetime DEFAULT NULL,
  `counts_result2changed` datetime DEFAULT NULL,
  `note_changed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `operation_log_problem`
--

CREATE TABLE `operation_log_problem` (
  `id` int(11) NOT NULL,
  `operation_log_id` int(11) DEFAULT NULL,
  `hour` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stop_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_fix` date DEFAULT NULL,
  `hour_fix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hour_release` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `operation_log_suggestion`
--

CREATE TABLE `operation_log_suggestion` (
  `id` int(11) NOT NULL,
  `operation_log_id` int(11) DEFAULT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `permission_group`
--

CREATE TABLE `permission_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Vypisuji data pro tabulku `permission_group`
--

INSERT INTO `permission_group` (`id`, `name`, `is_hidden`) VALUES
(1, 'Administrátor', 1),
(2, 'Zaměstnanec', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `permission_item`
--

CREATE TABLE `permission_item` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `permission_item`
--

INSERT INTO `permission_item` (`id`, `name`, `caption`, `type`) VALUES
(1, 'UsersPresenter', 'Správa uživatelů', 'presenter'),
(2, 'DailyPlanPresenter__daily_plan_menu', 'Plán zakázek', 'menu'),
(3, 'TaskPresenter__task_menu', 'Úkoly', 'menu'),
(4, 'InquiryPresenter__inquiry_menu', 'Poptávka', 'menu'),
(5, 'OfferPresenter__offer_menu', 'Zákazníci', 'menu'),
(6, 'CustomerPresenter__customer_menu', 'Zákazníci', 'menu'),
(7, 'ProductPresenter__product_menu', 'Produkt', 'menu'),
(8, 'global__menu_customer_section', 'Zákazníci sekce', 'global-element'),
(9, 'MenuPresenter__menu_menu', 'Menu', 'menu'),
(10, 'ArticlePresenter__article_menu', 'Články', 'menu'),
(11, 'BannerPresenter__banner_menu', 'Bannery', 'menu'),
(12, 'LanguagePresenter__language_menu', 'Jazyky', 'menu'),
(13, 'LanguagePresenter__translation_menu', 'Překlady', 'menu'),
(14, 'WebSettingPresenter__web_setting_menu', 'Webová nastavení', 'menu'),
(15, 'SettingPresenter__setting_menu', 'Systémová nastavení', 'menu'),
(16, 'global__menu_menu_section', 'Menu sekce', 'global-element'),
(17, 'MaterialStockPresenter__material_stock_menu', 'Sklady materiálů', 'menu'),
(18, 'MaterialGroupPresenter__material_group_menu', 'Skupiny materiálů', 'menu'),
(19, 'global__menu_dial_section', 'Číselníky sekce', 'global-element'),
(20, 'WorkerPresenter__worker_menu', 'Zaměstnanci', 'menu'),
(21, 'TaskStatePresenter__task_state_menu', 'Stavy úkolů', 'menu'),
(22, 'TaskLogPresenter__task_log_menu', 'Změny v úkolech', 'menu'),
(23, 'VisitLogPresenter__visit_log_menu', 'Změny ve výjezdech', 'menu'),
(24, 'UsersPresenter__users_menu', 'Uživatelé', 'menu'),
(25, 'global__menu_administration', 'Administrace', 'global-element'),
(26, 'UsersPresenter__createComponentTable__group', 'Oprávnění', 'form-element'),
(27, 'CustomerPresenter', 'Správa zákazníků - sekce', 'presenter'),
(28, 'InquiryPresenter', 'Správa poptávek - sekce', 'presenter'),
(29, 'MenuPresenter', 'Správa menu', 'presenter'),
(30, 'OfferPresenter', 'Správa nabídek', 'presenter'),
(31, 'OfferPresenter__createComponentForm', 'Formulář pro přidání/edit nabídky', 'form'),
(32, 'OfferPresenter__createComponentForm__offerNo', 'Číslo nabídky', 'form-element'),
(33, 'OfferPresenter__createComponentForm__customer', 'OfferPresenter__createComponentForm__customer', 'form-element'),
(34, 'OfferPresenter__createComponentForm__customerText', 'Zákazník textem', 'form-element'),
(35, 'OfferPresenter__createComponentForm__salesman', 'OfferPresenter__createComponentForm__salesman', 'form-element'),
(36, 'OfferPresenter__createComponentForm__unit', 'Za cenou', 'form-element'),
(37, 'OfferPresenter__createComponentForm__description', 'Poznámka', 'form-element'),
(38, 'OfferPresenter__createComponentForm__sendDate', 'Datum odeslání', 'form-element'),
(39, 'OfferPresenter__createComponentForm__addTOC', 'Obsah', 'form-element'),
(40, 'OfferPresenter__createComponentForm__addPricing', 'Ceník', 'form-element'),
(41, 'OfferPresenter__createComponentForm__addFooter', 'Kontakt na konec', 'form-element'),
(42, 'OfferPresenter__createComponentForm__productDescription', 'Popis produktu', 'form-element'),
(43, 'OfferPresenter__createComponentForm__reference', 'Reference', 'form-element'),
(44, 'OfferPresenter__createComponentForm__state', 'Stav', 'form-element'),
(45, 'OfferPresenter__createComponentForm__new', 'Je nový', 'form-element'),
(46, 'OfferPresenter__createComponentForm__inquiry', 'OfferPresenter__createComponentForm__inquiry', 'form-element'),
(47, 'OfferPresenter__createComponentForm__plannedSendDate', 'Plánovaný datum odeslání', 'form-element'),
(48, 'OfferPresenter__createComponentForm__autoSend', 'Odeslat automaticky', 'form-element'),
(49, 'OfferPresenter__createComponentForm__acceptDate', 'Datum potvrzení', 'form-element'),
(50, 'OfferPresenter__createComponentForm__price', 'Nabídnutá cena', 'form-element'),
(51, 'OfferPresenter__createComponentForm__priceDelivery', 'Cena dopravy', 'form-element'),
(52, 'OfferPresenter__createComponentForm__priceInstall', 'Cena montáže', 'form-element'),
(53, 'OfferPresenter__createComponentForm__priceCrane', 'Cena jeřábu', 'form-element'),
(54, 'OfferPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(55, 'OfferPresenter__createComponentTable__delete', '', 'form-element'),
(56, 'InquiryPresenter__createComponentForm', 'Formulář pro přidání/edit produktu', 'form'),
(57, 'InquiryPresenter__createComponentForm__products', 'Výsledek konfigurace', 'form-element'),
(58, 'InquiryPresenter__createComponentForm__message', 'Zpráva od zákazníka', 'form-element'),
(59, 'InquiryPresenter__createComponentForm__note', 'Poznámka obchodníka', 'form-element'),
(60, 'InquiryPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(61, 'OfferPresenter__createComponentProductModalForm', 'Formulář pro edit produktů', 'form'),
(62, 'OfferPresenter__createComponentProductModalForm__product', 'OfferPresenter__createComponentProductModalForm__product', 'form-element'),
(63, 'OfferPresenter__createComponentProductModalForm__klic_polozky', 'Klíč položky', 'form-element'),
(64, 'OfferPresenter__createComponentProductModalForm__price', 'Cena/mj [Kč]', 'form-element'),
(65, 'OfferPresenter__createComponentProductModalForm__count', 'Počet', 'form-element'),
(66, 'OfferPresenter__createComponentProductModalForm__send', 'Tlačítko: Uložit', 'form-element'),
(67, 'CustomerPresenter__renderEdit', 'Přidávání/edit zákazníků', 'method'),
(68, 'CustomerPresenter__createComponentForm', 'Formulář pro přidání/edit zákazníků', 'form'),
(69, 'CustomerPresenter__createComponentForm__company', 'Firma', 'form-element'),
(70, 'CustomerPresenter__createComponentForm__name', 'Jméno', 'form-element'),
(71, 'CustomerPresenter__createComponentForm__surname', 'Příjmení', 'form-element'),
(72, 'CustomerPresenter__createComponentForm__email', 'Email', 'form-element'),
(73, 'CustomerPresenter__createComponentForm__idNo', 'IČO', 'form-element'),
(74, 'CustomerPresenter__createComponentForm__vatNo', 'DIČ', 'form-element'),
(75, 'CustomerPresenter__createComponentForm__contactPerson', 'Jméno kontaktní osoby', 'form-element'),
(76, 'CustomerPresenter__createComponentForm__emailDelivery', 'Email kontaktní osoby', 'form-element'),
(77, 'CustomerPresenter__createComponentForm__phoneDelivery', 'Telefon kontatní osoby', 'form-element'),
(78, 'CustomerPresenter__createComponentForm__phone', 'Telefon/mobil', 'form-element'),
(79, 'CustomerPresenter__createComponentForm__streetDelivery', 'Ulice a č. p.', 'form-element'),
(80, 'CustomerPresenter__createComponentForm__cityDelivery', 'Město', 'form-element'),
(81, 'CustomerPresenter__createComponentForm__zipDelivery', 'PSČ', 'form-element'),
(82, 'CustomerPresenter__createComponentForm__street', 'Ulice a č. p.', 'form-element'),
(83, 'CustomerPresenter__createComponentForm__city', 'Město', 'form-element'),
(84, 'CustomerPresenter__createComponentForm__zip', 'PSČ', 'form-element'),
(85, 'CustomerPresenter__createComponentForm__customerState', 'Stav', 'form-element'),
(86, 'CustomerPresenter__createComponentForm__accountNumber', 'Číslo účtu', 'form-element'),
(87, 'CustomerPresenter__createComponentForm__bankCode', 'Kód banky', 'form-element'),
(88, 'CustomerPresenter__createComponentForm__bankName', 'Název banky', 'form-element'),
(89, 'CustomerPresenter__createComponentForm__www', 'Internetové stránky', 'form-element'),
(90, 'CustomerPresenter__createComponentForm__description', 'Poznámka', 'form-element'),
(91, 'CustomerPresenter__createComponentForm__maturity', 'Splatnost (počet dní)', 'form-element'),
(92, 'CustomerPresenter__createComponentForm__constantSymbol', 'Konstantní symbol', 'form-element'),
(93, 'CustomerPresenter__createComponentForm__active', ' Aktivní', 'form-element'),
(94, 'CustomerPresenter__createComponentForm__createdByInquiry', 'Vytvořen automaticky s poptávkou', 'form-element'),
(95, 'CustomerPresenter__createComponentForm__worker', 'Klíčový pracovník', 'form-element'),
(96, 'CustomerPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(97, 'BannerPresenter', 'Správa bannerů', 'presenter'),
(98, 'BannerPresenter__renderEdit', 'Zobrazení stránky s úpravou / přidání banneru', 'method'),
(99, 'BannerPresenter__createComponentBannerForm', 'Formulář pro přidání/edit banneru', 'form'),
(100, 'BannerPresenter__createComponentBannerForm__orderBanner', 'Pořadí', 'form-element'),
(101, 'BannerPresenter__createComponentBannerForm__type', 'Typ', 'form-element'),
(102, 'BannerPresenter__createComponentBannerForm__send', 'Tlačítko: Uložit', 'form-element'),
(103, 'ConfiguratorPresenter', 'Správa konfigurátorů - sekce', 'presenter'),
(104, 'ConfiguratorPresenter__createComponentForm', 'Formulář pro přidání/edit konfigurátoru', 'form'),
(105, 'ConfiguratorPresenter__createComponentForm__name', 'Název', 'form-element'),
(106, 'ConfiguratorPresenter__createComponentForm__orderConfigurator', 'Pořadí', 'form-element'),
(107, 'ConfiguratorPresenter__createComponentForm__active', 'Zobrazit', 'form-element'),
(108, 'ConfiguratorPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(109, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm', 'Formulář pro přidání uzlu', 'form'),
(110, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__input', 'Vstupní pole', 'form-element'),
(111, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__name', 'Označení', 'form-element'),
(112, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__value', 'Hodnota uzlu', 'form-element'),
(113, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__parents', 'Rodičovské uzly', 'form-element'),
(114, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__nodeNo', 'Číslo uzlu', 'form-element'),
(115, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__forSalesman', 'Odkázat na obchodníka', 'form-element'),
(116, 'App_Components_Modals_EditConfNodeModalControl__createComponentConfNodeForm__send', 'Tlačítko: Uložit', 'form-element'),
(117, 'MaterialStockPresenter', 'Správa skladů materiálů', 'presenter'),
(118, 'ProductPresenter', 'Správa produktů - sekce', 'presenter'),
(119, 'DailyPlanPresenter', 'Správa plán zakázek', 'presenter'),
(120, 'MenuPresenter__renderEdit', 'Zobrazení stránky s úpravou / přidáním nového menu', 'method'),
(121, 'MenuPresenter__createComponentMenuForm', 'Formulář pro přidání/edit menu', 'form'),
(122, 'MenuPresenter__createComponentMenuForm__parentMenu', 'Rodičovské menu', 'form-element'),
(123, 'MenuPresenter__createComponentMenuForm__orderPage', 'Pořadí (priorita)', 'form-element'),
(124, 'MenuPresenter__createComponentMenuForm__send', 'Tlačítko: Uložit', 'form-element'),
(125, 'ArticlePresenter', 'Správa článků - sekce', 'presenter'),
(126, 'ArticlePresenter__createComponentSearchForm', 'Formulář pro hledání článku', 'form'),
(127, 'ArticlePresenter__createComponentForm', 'Formulář pro přidání/edit článku', 'form'),
(128, 'ArticlePresenter__createComponentForm__menu', 'Zařazení', 'form-element'),
(129, 'ArticlePresenter__createComponentForm__orderArticle', 'Pořadí', 'form-element'),
(130, 'ArticlePresenter__createComponentForm__publish', 'Datum zveřejnění', 'form-element'),
(131, 'ArticlePresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(132, 'ArticlePresenter__createComponentGalleryForm', 'Formulář pro přidání/edit galerie článku', 'form'),
(133, 'ArticlePresenter__createComponentGalleryForm__send', 'Tlačítko: Uložit změny', 'form-element'),
(134, 'ArticlePresenter__createComponentFilesForm', 'Formulář pro přidání/edit souborů článku', 'form'),
(135, 'ArticlePresenter__createComponentFilesForm__send', 'Tlačítko: Uložit změny', 'form-element'),
(136, 'UsersPresenter__renderEdit', 'Zobrazení stránky s úpravou / přidání nového uživatele', 'method'),
(137, 'UsersPresenter__createComponentTable', 'Tabulka s přehledem uživatelů', 'method'),
(138, 'UsersPresenter__createComponentForm', 'Formulář pro přidání/editaci uživatelů', 'form'),
(139, 'UsersPresenter__createComponentForm__username', 'Přihlašovací jméno (login)', 'form-element'),
(140, 'UsersPresenter__createComponentForm__password', 'Heslo', 'form-element'),
(141, 'UsersPresenter__createComponentForm__name', 'Jméno a příjmení', 'form-element'),
(142, 'UsersPresenter__createComponentForm__phone', 'Telefon', 'form-element'),
(143, 'UsersPresenter__createComponentForm__mobile', 'Mobil', 'form-element'),
(144, 'UsersPresenter__createComponentForm__fax', 'Fax', 'form-element'),
(145, 'UsersPresenter__createComponentForm__email', 'E-mail', 'form-element'),
(146, 'UsersPresenter__createComponentForm__group', 'Oprávnění', 'form-element'),
(147, 'UsersPresenter__createComponentForm__isAdmin', 'Administrátor', 'form-element'),
(148, 'UsersPresenter__createComponentForm__isBlocked', 'Zablokován', 'form-element'),
(149, 'UsersPresenter__createComponentForm__isMaster', 'Vedoucí', 'form-element'),
(150, 'UsersPresenter__createComponentForm__signature', 'Podpis', 'form-element'),
(151, 'UsersPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(152, 'WebSettingPresenter', 'Správa webového nastavení', 'presenter'),
(153, 'WebSettingPresenter__createComponentForm', 'Formulář pro přidání/edit webového nastavení', 'form'),
(154, 'WebSettingPresenter__createComponentForm__code', 'Kód', 'form-element'),
(155, 'WebSettingPresenter__createComponentForm__description', 'Poznámka', 'form-element'),
(156, 'WebSettingPresenter__createComponentForm__type', 'Typ', 'form-element'),
(157, 'WebSettingPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(158, 'ProductPresenter__createComponentForm', 'Formulář pro přidání/edit produktu', 'form'),
(159, 'ProductPresenter__createComponentForm__menu', 'Zařazení', 'form-element'),
(160, 'ProductPresenter__createComponentForm__orderProduct', 'Pořadí', 'form-element'),
(161, 'ProductPresenter__createComponentForm__klic_polozky', 'Klíč položky', 'form-element'),
(162, 'ProductPresenter__createComponentForm__nazev_polozky', 'Název položky', 'form-element'),
(163, 'ProductPresenter__createComponentForm__alter_nazev', 'Alternativní název', 'form-element'),
(164, 'ProductPresenter__createComponentForm__hmotnost_mj', 'Hmotnost na měrnou jednotku [kg]', 'form-element'),
(165, 'ProductPresenter__createComponentForm__evid_cena_pol', 'Cena [Kč]', 'form-element'),
(166, 'ProductPresenter__createComponentForm__atr_rozmer_1', 'Délka [mm]', 'form-element'),
(167, 'ProductPresenter__createComponentForm__atr_rozmer_2', 'Šířka [mm]', 'form-element'),
(168, 'ProductPresenter__createComponentForm__atr_rozmer_3', 'Výška [mm]', 'form-element'),
(169, 'ProductPresenter__createComponentForm__sklad_mnozstvi', 'Skladové množství', 'form-element'),
(170, 'ProductPresenter__createComponentForm__objem', 'Objem [m³]', 'form-element'),
(171, 'ProductPresenter__createComponentForm__zkratka_mj', 'Měrná jednotka', 'form-element'),
(172, 'ProductPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(173, 'ProductPresenter__createComponentGalleryForm', 'Formulář pro přidání/edit galerie produktu', 'form'),
(174, 'ProductPresenter__createComponentGalleryForm__send', 'Tlačítko: Uložit změny', 'form-element'),
(175, 'ProductPresenter__createComponentFilesForm', 'Formulář pro přidání/edit souborů produktu', 'form'),
(176, 'ProductPresenter__createComponentFilesForm__send', 'Tlačítko: Uložit změny', 'form-element'),
(177, 'TaskPresenter', 'Správa úkolů', 'presenter'),
(178, 'TaskPresenter__renderDefault', 'Zobrazení úkolů - nástěnka', 'method'),
(179, 'ProductPresenter__createComponentForm__priceInstall', 'Cena montáže [Kč]', 'form-element'),
(180, 'OfferPresenter__createComponentForm__transportCount', 'Množství dopravy', 'form-element'),
(181, 'OfferPresenter__createComponentForm__transportTime', 'Čas na cestě (jednosměr.) [h]', 'form-element'),
(182, 'OfferPresenter__createComponentForm__installWorkers', 'Počet montážníků', 'form-element'),
(183, 'OfferPresenter__createComponentForm__installDistance', 'Vzdálenost instalace [km]', 'form-element'),
(184, 'InquiryPresenter__createComponentTable__delete', '', 'form-element'),
(185, 'WorkerPresenter', 'Správa zaměstnanců', 'presenter'),
(186, 'WorkerPresenter__createComponentTable__male', 'Pohlaví', 'form-element'),
(187, 'WorkerPresenter__renderEdit', 'Zobrazení stránky s úpravou / přidání nového zaměstnance', 'method'),
(188, 'WorkerPresenter__createComponentForm', 'Formulář pro přidání/edit zaměstnance', 'form'),
(189, 'WorkerPresenter__createComponentForm__name', 'Jméno', 'form-element'),
(190, 'WorkerPresenter__createComponentForm__surname', 'Přijmení', 'form-element'),
(191, 'WorkerPresenter__createComponentForm__personalId', 'Osobní číslo', 'form-element'),
(192, 'WorkerPresenter__createComponentForm__nationality', 'Národnost', 'form-element'),
(193, 'WorkerPresenter__createComponentForm__birthDate', 'Datum narození', 'form-element'),
(194, 'WorkerPresenter__createComponentForm__phone', 'Telefon', 'form-element'),
(195, 'WorkerPresenter__createComponentForm__email', 'E-mail', 'form-element'),
(196, 'WorkerPresenter__createComponentForm__street', 'Ulice', 'form-element'),
(197, 'WorkerPresenter__createComponentForm__city', 'Město', 'form-element'),
(198, 'WorkerPresenter__createComponentForm__zip', 'PSČ', 'form-element'),
(199, 'WorkerPresenter__createComponentForm__country', 'Stát', 'form-element'),
(200, 'WorkerPresenter__createComponentForm__insurance', 'Pojištovna', 'form-element'),
(201, 'WorkerPresenter__createComponentForm__active', 'Aktivní', 'form-element'),
(202, 'WorkerPresenter__createComponentForm__startDate', 'Datum nástupu', 'form-element'),
(203, 'WorkerPresenter__createComponentForm__endDate', 'Pracovní poměr do', 'form-element'),
(204, 'WorkerPresenter__createComponentForm__endContractDate', 'Ukončení pracovního poměru', 'form-element'),
(205, 'WorkerPresenter__createComponentForm__calendarColor', 'Barva kalendáře', 'form-element'),
(206, 'WorkerPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(207, 'SettingPresenter', 'Správa nastavení', 'presenter'),
(208, 'ProductPresenter__createComponentTable__delete', '', 'form-element'),
(209, 'SettingPresenter__createComponentForm', 'Formulář pro přidání/edit nastavení', 'form'),
(210, 'SettingPresenter__createComponentForm__code', 'Kód', 'form-element'),
(211, 'SettingPresenter__createComponentForm__value', 'Hodnota', 'form-element'),
(212, 'SettingPresenter__createComponentForm__description', 'Poznámka', 'form-element'),
(213, 'SettingPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(214, 'ConfiguratorPresenter__createComponentTable__delete', '', 'form-element'),
(215, 'ProductPresenter__createComponentForm__atribut2', 'Atribut 2', 'form-element'),
(216, 'AbsenceReasonPresenter__absence_reason_menu', 'Důvody absencí', 'menu'),
(217, 'OfferPresenter__createComponentForm__vat', 'DPH', 'form-element'),
(218, 'VatPresenter', 'DPH presenter', 'presenter'),
(219, 'VatPresenter__renderEdit', 'Zobrazení stránky s úpravou / přidáním DPH', 'method'),
(220, 'VatPresenter__createComponentForm', 'Formulář pro přidání/edit DPH', 'form'),
(221, 'VatPresenter__createComponentForm__name', 'Označení', 'form-element'),
(222, 'VatPresenter__createComponentForm__value', 'Výše DPH', 'form-element'),
(223, 'VatPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(224, 'TaskStatePresenter', 'Správa stavů úkolů', 'presenter'),
(225, 'MenuPresenter__createComponentMenuForm__hideInSelect', 'Skrýt v číselnících', 'form-element'),
(226, 'WebSettingPresenter__createComponentTable__delete', '', 'form-element'),
(227, 'TranslationPresenter', 'Překlady', 'presenter'),
(228, 'LanguagePresenter', 'Správa jazyků', 'presenter'),
(229, 'SettingPresenter__createComponentTable__delete', '', 'form-element'),
(230, 'ReservationPresenter__reservation_menu', 'Rezervace', 'menu'),
(231, 'ReservationPresenter', 'Správa rezervací', 'presenter'),
(232, 'ReservationPresenter__renderEditItem', 'Zobrazení stránky s úpravou / přidáním rezervovatelné položky', 'method'),
(233, 'ReservationPresenter__createComponentItemForm', 'Formulář pro přidání/edit položky rezervace', 'form'),
(234, 'ReservationPresenter__createComponentItemForm__name', 'Název', 'form-element'),
(235, 'ReservationPresenter__createComponentItemForm__reservablePeriod', 'Doba dílu rezervace [minuty]', 'form-element'),
(236, 'ReservationPresenter__createComponentItemForm__minReservablePeriod', 'Minimální rezervovatelná doba zákazníkem [minuty]', 'form-element'),
(237, 'ReservationPresenter__createComponentItemForm__pricePerHour', 'Cena za hodinu bez DPH', 'form-element'),
(238, 'ReservationPresenter__createComponentItemForm__timeMondayFrom', '', 'form-element'),
(239, 'ReservationPresenter__createComponentItemForm__timeMondayTo', '', 'form-element'),
(240, 'ReservationPresenter__createComponentItemForm__timeTuesdayFrom', '', 'form-element'),
(241, 'ReservationPresenter__createComponentItemForm__timeTuesdayTo', '', 'form-element'),
(242, 'ReservationPresenter__createComponentItemForm__timeWednesdayFrom', '', 'form-element'),
(243, 'ReservationPresenter__createComponentItemForm__timeWednesdayTo', '', 'form-element'),
(244, 'ReservationPresenter__createComponentItemForm__timeThursdayFrom', '', 'form-element'),
(245, 'ReservationPresenter__createComponentItemForm__timeThursdayTo', '', 'form-element'),
(246, 'ReservationPresenter__createComponentItemForm__timeFridayFrom', '', 'form-element'),
(247, 'ReservationPresenter__createComponentItemForm__timeFridayTo', '', 'form-element'),
(248, 'ReservationPresenter__createComponentItemForm__timeSaturdayFrom', '', 'form-element'),
(249, 'ReservationPresenter__createComponentItemForm__timeSaturdayTo', '', 'form-element'),
(250, 'ReservationPresenter__createComponentItemForm__timeSundayFrom', '', 'form-element'),
(251, 'ReservationPresenter__createComponentItemForm__timeSundayTo', '', 'form-element'),
(252, 'ReservationPresenter__createComponentItemForm__active', ' Aktivní', 'form-element'),
(253, 'ReservationPresenter__createComponentItemForm__send', 'Tlačítko: Uložit', 'form-element'),
(254, 'ReservationPresenter__renderEdit', 'Zobrazení stránky s úpravou / přidáním rezervace', 'method'),
(255, 'ReservationPresenter__createComponentForm', 'Formulář pro přidání/edit rezervace', 'form'),
(256, 'ReservationPresenter__createComponentForm__customer', 'ReservationPresenter__createComponentForm__customer', 'form-element'),
(257, 'ReservationPresenter__createComponentForm__reservationItem', 'Rezervovatelná položka', 'form-element'),
(258, 'ReservationPresenter__createComponentForm__dateFrom', 'Datum a čas od', 'form-element'),
(259, 'ReservationPresenter__createComponentForm__dateTo', 'Datum a čas do', 'form-element'),
(260, 'ReservationPresenter__createComponentForm__canceled', 'Zrušeno', 'form-element'),
(261, 'ReservationPresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(262, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm', 'Formulář pro přidání/edit rezervace', 'form'),
(263, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__customer', 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__customer', 'form-element'),
(264, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__reservationItem', 'Rezervovatelná položka', 'form-element'),
(265, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__dateFrom', 'Datum a čas od', 'form-element'),
(266, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__dateTo', 'Datum a čas do', 'form-element'),
(267, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__canceled', 'Zrušeno', 'form-element'),
(268, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__send', 'Tlačítko: Uložit', 'form-element'),
(269, 'App_Components_Reservation_ReservationControl__createComponentReservationAdminModalForm__price', 'Cena za hodinu bez DPH', 'form-element'),
(270, 'CustomerPresenter__createComponentForm__types', 'Typy zákazníka', 'form-element'),
(271, 'CustomerPresenter__createComponentForm__workshop', 'Provozovna', 'form-element'),
(272, 'CustomerPresenter__createComponentForm__password', 'Heslo', 'form-element'),
(273, 'CustomerPresenter__createComponentForm__createdByReservation', 'Vytvořen automaticky s poptávkou', 'form-element'),
(274, 'ReservationPresenter__createComponentForm__price', 'Cena za hodinu bez DPH', 'form-element'),
(0, 'UsersPresenter__createComponentForm__isSalesman', 'Obchodník', 'form-element'),
(0, 'LanguagePresenter__renderEdit', 'Zobrazení stránky s úpravou / přidání jazyka', 'method'),
(0, 'LanguagePresenter__createComponentForm', 'Formulář pro přidání/editaci jazyka', 'form'),
(0, 'LanguagePresenter__createComponentForm__name', 'Název', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__code', 'Kód', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__orderCode', 'Pořadí', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__defaultCode', 'Základní', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__name', 'Název', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__code', 'Kód', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__orderCode', 'Pořadí', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__defaultCode', 'Základní', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__name', 'Název', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__code', 'Kód', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__orderCode', 'Pořadí', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__defaultCode', 'Základní', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__name', 'Název', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__code', 'Kód', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__orderCode', 'Pořadí', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__defaultCode', 'Základní', 'form-element'),
(0, 'LanguagePresenter__createComponentForm__send', 'Tlačítko: Uložit', 'form-element');

-- --------------------------------------------------------

--
-- Struktura tabulky `permission_rule`
--

CREATE TABLE `permission_rule` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `permission_rule`
--

INSERT INTO `permission_rule` (`id`, `group_id`, `item`, `action`) VALUES
(1, 2, 'InquiryPresenter', 'all'),
(2, 2, 'OfferPresenter', 'all'),
(3, 2, 'CustomerPresenter', 'all'),
(4, 2, 'ProductPresenter', 'all'),
(9, 2, 'global__menu_customer_section', 'all');

-- --------------------------------------------------------

--
-- Struktura tabulky `process`
--

CREATE TABLE `process` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `process_state_id` int(11) DEFAULT NULL,
  `originator_id` int(11) DEFAULT NULL,
  `bp_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_order_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `founded_date` datetime DEFAULT NULL,
  `in_state_date` datetime DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `process_state`
--

CREATE TABLE `process_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `archive` tinyint(1) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `process_state`
--

INSERT INTO `process_state` (`id`, `name`, `slug`, `order`, `active`, `archive`, `description`) VALUES
(1, 'Neodeslané', 'neodeslane', 1, 1, 0, NULL),
(2, 'Nové', 'nove', 1, 1, 0, ''),
(3, 'Ve zpracování', 've-zpracovani', 2, 1, 0, ''),
(4, 'Dokončené', 'dokoncene', 3, 1, 0, ''),
(5, 'Archiv', 'archiv', 4, 1, 1, '');

-- --------------------------------------------------------

--
-- Struktura tabulky `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `order_product` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `nazev_polozky` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `klic_polozky` int(11) DEFAULT NULL,
  `alter_nazev` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `klic_postaveni` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hmotnost_mj` double DEFAULT NULL,
  `evid_cena_pol` double DEFAULT NULL,
  `atr_rozmer_1` double DEFAULT NULL,
  `atr_rozmer_2` double DEFAULT NULL,
  `atr_rozmer_3` double DEFAULT NULL,
  `sklad_mnozstvi` double DEFAULT NULL,
  `objem` double DEFAULT NULL,
  `zkratka_mj` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_imported` tinyint(1) DEFAULT '0',
  `price_install` double DEFAULT NULL,
  `atribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `production_line`
--

CREATE TABLE `production_line` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `production_plan`
--

CREATE TABLE `production_plan` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_string` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_plan` date DEFAULT NULL,
  `shift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `production_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `rod_hang` tinyint(1) DEFAULT NULL,
  `rod_send` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `production_progress_report_setting`
--

CREATE TABLE `production_progress_report_setting` (
  `id` int(11) NOT NULL,
  `line` int(11) DEFAULT NULL,
  `number_people_per_shift` double DEFAULT NULL,
  `monthly_labor_costs` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `production_setting`
--

CREATE TABLE `production_setting` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `product_file`
--

CREATE TABLE `product_file` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` int(11) DEFAULT NULL,
  `order_file` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `product_file_in_language`
--

CREATE TABLE `product_file_in_language` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `product_image`
--

CREATE TABLE `product_image` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT NULL,
  `order_img` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `product_in_menu`
--

CREATE TABLE `product_in_menu` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `product_in_plan`
--

CREATE TABLE `product_in_plan` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `product` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `order_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `counts` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `product_language`
--

CREATE TABLE `product_language` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` longtext COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `qualification`
--

CREATE TABLE `qualification` (
  `id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `place` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participation` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `type_of_action` int(11) DEFAULT NULL,
  `evalution_date` date DEFAULT NULL,
  `professional_level` int(11) DEFAULT NULL,
  `organisation_support` int(11) DEFAULT NULL,
  `range` int(11) DEFAULT NULL,
  `new_methods` int(11) DEFAULT NULL,
  `safety` int(11) DEFAULT NULL,
  `time_savings` int(11) DEFAULT NULL,
  `quality_of_work` int(11) DEFAULT NULL,
  `reminders` longtext COLLATE utf8mb4_unicode_ci,
  `efficiency` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reservation`
--

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reservation_item_id` int(11) DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `canceled` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `originator_id` int(11) DEFAULT NULL,
  `price` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reservation_item`
--

CREATE TABLE `reservation_item` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reservable_period` int(11) NOT NULL,
  `min_reservable_period` int(11) NOT NULL,
  `price_per_hour` double DEFAULT NULL,
  `time_monday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_monday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_tuesday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_tuesday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_wednesday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_wednesday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_thursday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_thursday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_friday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_friday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_saturday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_saturday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_sunday_from` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_sunday_to` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reservation_plan`
--

CREATE TABLE `reservation_plan` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reservation_product`
--

CREATE TABLE `reservation_product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `service`
--

CREATE TABLE `service` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `date_service` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `setting`
--

CREATE TABLE `setting` (
  `id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `setting`
--

INSERT INTO `setting` (`id`, `code`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'email_sender_mask', 'Webrex <info@webrex.eu>', '', '2021-05-20 13:35:35', '2023-07-14 09:00:06'),
(2, 'process_number', 'VY{0006}', NULL, '0000-00-00 00:00:00', '2021-09-21 15:42:36'),
(3, 'email_for_absence', 'michal@webrex.eu\n', 'Email pro příjem podaných absencí', '0000-00-00 00:00:00', '2023-07-14 08:59:56'),
(4, 'email_for_success_absence_info', 'michal@webrex.eu', 'Email pro příjem schválených absencí s informací o zástupu', '0000-00-00 00:00:00', '2023-07-14 09:00:14'),
(5, 'email_for_copy_docs', 'michal@webrex.eu', 'Email pro kopii při zasílání dokumentů', '0000-00-00 00:00:00', '2023-07-14 08:59:46'),
(6, 'email_for_invoicing_docs', 'michal@webrex.eu', 'Email pro odesílání dokumentů na fakturaci', '0000-00-00 00:00:00', '2023-07-14 08:59:36'),
(7, 'base_name', 'Asociace českých nábytkářů', 'Název stránky pro odesílání e-mailů atp...', '2022-12-06 13:24:17', '2023-12-06 12:47:11'),
(14, 'default_install_workers', '2', NULL, '2023-07-20 23:05:59', NULL),
(15, 'default_transport_count', '1', NULL, '2023-07-20 23:05:59', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `shift_bonus`
--

CREATE TABLE `shift_bonus` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `day_of_week` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `shift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `production_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `date_start` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `shift_bonus_group`
--

CREATE TABLE `shift_bonus_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `shift_bonus_template`
--

CREATE TABLE `shift_bonus_template` (
  `id` int(11) NOT NULL,
  `shift_bonus_group_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `day_of_week` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `shift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `production_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `shift_plan`
--

CREATE TABLE `shift_plan` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_string` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_plan` date DEFAULT NULL,
  `shift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `production_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skill`
--

CREATE TABLE `skill` (
  `id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_n` int(11) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `tenderable_intern` tinyint(1) NOT NULL,
  `tenderable_extern` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_tenderable` tinyint(1) NOT NULL,
  `tenderable_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skill_in_worker`
--

CREATE TABLE `skill_in_worker` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skill_in_worker_position`
--

CREATE TABLE `skill_in_worker_position` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skill_in_worker_tender`
--

CREATE TABLE `skill_in_worker_tender` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `tender_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `skill_type`
--

CREATE TABLE `skill_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `task`
--

CREATE TABLE `task` (
  `id` int(11) NOT NULL,
  `task_state_id` int(11) DEFAULT NULL,
  `originator_id` int(11) DEFAULT NULL,
  `assigned_id` int(11) DEFAULT NULL,
  `last_edited_id` int(11) DEFAULT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `founded_date` date DEFAULT NULL,
  `close_to_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `in_state_date` date DEFAULT NULL,
  `priority` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `task_comment`
--

CREATE TABLE `task_comment` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `task_document`
--

CREATE TABLE `task_document` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `task_log`
--

CREATE TABLE `task_log` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `founded_date` datetime DEFAULT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci,
  `old_text` longtext COLLATE utf8mb4_unicode_ci,
  `new_text` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `task_state`
--

CREATE TABLE `task_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_type` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `for_dashboard` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Struktura tabulky `traffic`
--

CREATE TABLE `traffic` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_ordered_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `is_cost_program` tinyint(1) NOT NULL,
  `cost_program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_distance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_back_distance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_back_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_back_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_lost_hours_distance` int(11) DEFAULT NULL,
  `cost_lost_minutes_distance` int(11) DEFAULT NULL,
  `cost_lost_hours_back_distance` int(11) DEFAULT NULL,
  `cost_lost_minutes_back_distance` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `translation`
--

CREATE TABLE `translation` (
  `id` int(11) NOT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `key_m` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  `is_blocked` tinyint(1) DEFAULT NULL,
  `qualification_allow` tinyint(1) DEFAULT NULL,
  `qualification_edit` tinyint(1) DEFAULT NULL,
  `qualification_view_effective` tinyint(1) DEFAULT NULL,
  `documents_allow` tinyint(1) DEFAULT NULL,
  `last_logged_at` datetime DEFAULT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_master` tinyint(1) DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT NULL,
  `menu` longtext COLLATE utf8mb4_unicode_ci,
  `is_salesman` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`id`, `group_id`, `department_id`, `username`, `password`, `name`, `phone`, `mobile`, `fax`, `email`, `is_admin`, `is_blocked`, `qualification_allow`, `qualification_edit`, `qualification_view_effective`, `documents_allow`, `last_logged_at`, `signature`, `created_at`, `updated_at`, `is_master`, `is_hidden`, `menu`, `is_salesman`) VALUES
(1, 1, NULL, 'wadmin', '$2y$10$9XHA1reY7WKIU2003BViE.zM09hfDn3ZeQ4PjGi2oIKyEFS2D.Ixy', 'Webrex Administrátor', '', '', '', 'michal@webrex.eu', 1, 0, 1, 1, 1, 1, '2023-12-07 08:32:13', NULL, '2022-08-09 00:00:00', '2023-12-07 08:32:13', 0, 0, '{\"1\":1,\"2\":1,\"3\":1,\"4\":1,\"5\":1,\"6\":1,\"7\":1,\"8\":1,\"9\":1,\"10\":1,\"11\":1,\"12\":1,\"13\":1,\"14\":1,\"15\":1,\"16\":1,\"17\":1,\"18\":1,\"19\":1,\"20\":1}', NULL),
(2, 1, NULL, 'admin', '$2y$10$Z.yIgY2AfQQadjpi8j4AIOiYAoW91YPTd14wqpZWQTFZJXx0Fd9Pa', 'Administrátor', '+420', '+420', '+420', 'web@webrex.eu', 0, 0, 0, 0, 0, 0, '2023-12-07 08:32:42', NULL, '2022-09-21 14:47:58', '2023-12-07 08:32:42', 0, 0, '{\"1\":1,\"2\":1,\"3\":1,\"4\":1,\"5\":1,\"6\":1,\"7\":1,\"8\":1,\"9\":1,\"10\":1,\"11\":1,\"12\":1,\"13\":1,\"14\":1,\"15\":1,\"16\":1,\"17\":1,\"18\":1,\"19\":1,\"20\":1}', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `user_in_workplace`
--

CREATE TABLE `user_in_workplace` (
  `id` int(11) NOT NULL,
  `workplace_id` int(11) DEFAULT NULL,
  `master_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `vacation`
--

CREATE TABLE `vacation` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `vacation_type_id` int(11) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `count_hours` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `vacation_fund`
--

CREATE TABLE `vacation_fund` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hours_base` int(11) DEFAULT NULL,
  `hours_plus` int(11) DEFAULT NULL,
  `hours_minus` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `vacation_type`
--

CREATE TABLE `vacation_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `vacation_type`
--

INSERT INTO `vacation_type` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Dovolená', '2021-12-07 14:43:55', '2021-12-07 14:43:55'),
(2, 'Nemoc', '2021-12-07 14:43:55', '2021-12-07 14:43:55'),
(3, 'Ošetřovné', '2021-12-07 14:43:55', '2021-12-07 14:43:55'),
(4, 'Lékař', '2021-12-07 14:43:55', '2021-12-07 14:43:55'),
(5, 'Propustka', '2021-12-07 14:43:55', '2021-12-07 14:43:55'),
(6, 'Jiné', '2021-12-07 14:43:55', '2021-12-07 14:43:55'),
(7, 'Neplacené volno omluvené', '2021-12-16 12:11:43', '2021-12-16 12:11:43');

-- --------------------------------------------------------

--
-- Struktura tabulky `vat`
--

CREATE TABLE `vat` (
  `id` int(11) NOT NULL,
  `value` double DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `vat`
--

INSERT INTO `vat` (`id`, `value`, `updated_at`, `name`) VALUES
(1, 0, NULL, '0 %'),
(2, 15, NULL, '15 %'),
(3, 21, NULL, '21 %');

-- --------------------------------------------------------

--
-- Struktura tabulky `visit`
--

CREATE TABLE `visit` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_ordered_id` int(11) DEFAULT NULL,
  `traffic_id` int(11) DEFAULT NULL,
  `visit_process_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `refrigerant_id` int(11) DEFAULT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `order_id2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_description` longtext COLLATE utf8mb4_unicode_ci,
  `ships` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_customer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_deadline` date DEFAULT NULL,
  `deadline_times` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `once_times` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minutes_before` double DEFAULT NULL,
  `minutes_after` double DEFAULT NULL,
  `service` tinyint(1) DEFAULT NULL,
  `demadge_other` int(11) DEFAULT NULL,
  `order_finish` int(11) DEFAULT NULL,
  `write_evidence_book` int(11) DEFAULT NULL,
  `evidence_book` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_cooling` double DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `handover` int(11) DEFAULT NULL,
  `free_deli` tinyint(1) DEFAULT NULL,
  `signature` longtext COLLATE utf8mb4_unicode_ci,
  `customer_sign_image` longtext COLLATE utf8mb4_unicode_ci,
  `bozp` int(11) DEFAULT NULL,
  `bozp_el_voltage` int(11) DEFAULT NULL,
  `bozp_height` int(11) DEFAULT NULL,
  `bozp_hazard_voltage` int(11) DEFAULT NULL,
  `bozp_area` int(11) DEFAULT NULL,
  `bozp_under_burden` int(11) DEFAULT NULL,
  `bozp_vzv` int(11) DEFAULT NULL,
  `bozp_hurt` int(11) DEFAULT NULL,
  `bozp_cut` int(11) DEFAULT NULL,
  `bozp_welding` int(11) DEFAULT NULL,
  `bozp_cooling` int(11) DEFAULT NULL,
  `bozp_chemikals` int(11) DEFAULT NULL,
  `bozp_oxygen` int(11) DEFAULT NULL,
  `bozp_burden` int(11) DEFAULT NULL,
  `bozp_fall` int(11) DEFAULT NULL,
  `bozp_fly_subject` int(11) DEFAULT NULL,
  `bozp_rotate_subject` int(11) DEFAULT NULL,
  `bozp_noise` int(11) DEFAULT NULL,
  `bozp_asbest` int(11) DEFAULT NULL,
  `bozp_other` int(11) DEFAULT NULL,
  `bozp_clothes` int(11) DEFAULT NULL,
  `bozp_use_clothes` int(11) DEFAULT NULL,
  `bozp_check` int(11) DEFAULT NULL,
  `bozp_information_other` int(11) DEFAULT NULL,
  `bozp_safety` int(11) DEFAULT NULL,
  `bozp_el_voltage_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_height_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_hazard_voltage_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_area_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_under_burden_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_vzv_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_hurt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_cut_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_welding_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_cooling_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_chemikals_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_oxygen_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_burden_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_fall_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_fly_subject_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_rotate_subject_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_noise_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_asbest_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_other_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_clothes_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_use_clothes_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_check_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_information_other_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bozp_safety_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refrigerant_producer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refrigerant_manufacture_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refrigerant_detector` int(11) DEFAULT NULL,
  `refrigerant_detection_system` int(11) DEFAULT NULL,
  `refrigerant_devices_is_ok` int(11) DEFAULT NULL,
  `refrigerant_demadge` longtext COLLATE utf8mb4_unicode_ci,
  `refrigerant_type_revision` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `visit_document`
--

CREATE TABLE `visit_document` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `visit_log`
--

CREATE TABLE `visit_log` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `founded_date` datetime DEFAULT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci,
  `old_text` longtext COLLATE utf8mb4_unicode_ci,
  `new_text` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `visit_process`
--

CREATE TABLE `visit_process` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_ordered_id` int(11) DEFAULT NULL,
  `traffic_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `date_accept_order` date DEFAULT NULL,
  `date_send_offer` date DEFAULT NULL,
  `date_order_part` date DEFAULT NULL,
  `date_send_part` date DEFAULT NULL,
  `date_finished` date DEFAULT NULL,
  `is_int_order_id` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `visit_process_state`
--

CREATE TABLE `visit_process_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `visit_process_state`
--

INSERT INTO `visit_process_state` (`id`, `name`, `state_order`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Záruční servis', 60, 1, '2022-09-07 10:00:44', '2022-09-09 12:14:57'),
(2, 'Montážní práce', 10, 1, '2022-09-07 10:00:31', '2022-09-09 12:14:00'),
(3, 'Pravidelná roční prohlídka', 50, 1, '2022-09-07 10:00:39', '2022-09-09 12:14:40'),
(4, 'Servisní smlouva', 70, 1, '2022-09-07 10:00:50', '2022-09-09 12:13:41');

-- --------------------------------------------------------

--
-- Struktura tabulky `visit_state`
--

CREATE TABLE `visit_state` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `visit_state`
--

INSERT INTO `visit_state` (`id`, `name`, `state_order`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Založené zakázky', 10, 1, '2022-09-07 09:58:25', '2022-09-07 09:58:25'),
(2, 'Přiřazené zakázky', 20, 1, '2022-09-07 09:58:33', '2022-09-07 09:58:33'),
(3, 'K dokončení', 30, 1, '2022-09-07 09:58:41', '2022-09-07 09:58:41'),
(4, 'Ke kontrole', 40, 1, '2022-09-07 09:58:50', '2022-09-07 09:58:50'),
(5, 'Nabídka', 50, 1, '2022-09-07 09:58:57', '2022-09-07 09:58:57'),
(6, 'Objednání dílů', 60, 1, '2022-09-07 09:59:10', '2022-09-07 09:59:10'),
(7, 'Fakturace', 70, 1, '2022-09-07 09:59:18', '2022-09-07 09:59:18'),
(8, 'Vyfakturováno', 80, 1, '2022-09-07 09:59:25', '2022-09-07 09:59:25'),
(9, 'Nefakturuje se - záruka, storno', 90, 1, '2022-09-07 09:59:39', '2022-09-07 09:59:39'),
(10, 'Čeká na dokončení', 100, 1, '2022-09-07 09:59:53', '2022-09-07 09:59:53'),
(11, 'Čeká na díl nedokončeno', 110, 1, '2022-09-07 10:00:03', '2022-09-07 10:00:03'),
(12, 'Doplnění k fakturaci', 120, 1, '2022-09-07 10:00:15', '2022-09-07 10:00:15');

-- --------------------------------------------------------

--
-- Struktura tabulky `visit_status`
--

CREATE TABLE `visit_status` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_order` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `visit_status`
--

INSERT INTO `visit_status` (`id`, `name`, `state_order`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Hotovo', 10, 1, '2022-09-07 09:56:37', '2022-09-07 09:57:52'),
(2, 'Nabídka', 20, 1, '2022-09-07 09:57:35', '2022-09-07 09:57:35'),
(3, 'Storno', 30, 1, '2022-09-07 09:57:41', '2022-09-07 09:57:41');

-- --------------------------------------------------------

--
-- Struktura tabulky `web_setting`
--

CREATE TABLE `web_setting` (
  `id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `web_setting`
--

INSERT INTO `web_setting` (`id`, `code`, `description`, `type`, `created_at`, `updated_at`) VALUES
(4, 'footer', 'Patička', 'editor', '0000-00-00 00:00:00', NULL),
(14, 'footer_address', 'Patička s dodatečnými odkazy', 'editor', '0000-00-00 00:00:00', '2023-10-25 14:26:18'),
(15, 'veletrh', 'veletrh', 'editor', '2023-08-10 14:55:38', '2023-10-25 14:39:47'),
(16, 'social', 'soical icons', 'editor', '2023-08-11 08:08:13', '2023-10-25 14:15:02'),
(21, 'cojeacn', 'o acn', 'editor', '2023-10-25 15:48:27', '2023-10-26 15:16:44');

-- --------------------------------------------------------

--
-- Struktura tabulky `web_setting_language`
--

CREATE TABLE `web_setting_language` (
  `id` int(11) NOT NULL,
  `setting_id` int(11) DEFAULT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `web_setting_language`
--

INSERT INTO `web_setting_language` (`id`, `setting_id`, `lang_id`, `value`) VALUES
(29, 14, 1, '<div><img alt=\"\" src=\"www/files/logo_black.png\" style=\"width: 300px; height: 280px;\" /></div>\r\n\r\n<div>\r\n<h2><span style=\"font-size: 16px;\"><b>Pro veřejnost</b></span></h2>\r\n\r\n<p>úvod</p>\r\n\r\n<p>jak vybírat nábytek</p>\r\n\r\n<p>certifikáty</p>\r\n\r\n<p>nábytek roku</p>\r\n\r\n<p>tiskové zprávy</p>\r\n\r\n<p>česká výroba nábytku</p>\r\n\r\n<p>kontakty</p>\r\n</div>\r\n\r\n<div>\r\n<h2><span style=\"font-size: 16px;\"><b>Pro výrobce</b></span></h2>\r\n\r\n<p>o ačn</p>\r\n\r\n<p>výhody členství&nbsp;</p>\r\n\r\n<p>seznam členství&nbsp;</p>\r\n\r\n<p>seznam členů ačn</p>\r\n\r\n<p>informace pro nábytkáře</p>\r\n\r\n<p>česká kvalita nábytek - služba</p>\r\n\r\n<p>čská kvalita nábytel - produkt&nbsp;</p>\r\n\r\n<p>mezinárodní organizace&nbsp;</p>\r\n\r\n<p>výstavy a veletrhy</p>\r\n\r\n<p>projekty</p>\r\n</div>\r\n\r\n\r\n<p>&nbsp;</p>\r\n'),
(30, 4, 1, '<div class=\"footer-upper-line\">&nbsp;</div>\r\n\r\n<div id=\"footer_bottom\">\r\n<div class=\"row\">\r\n<p class=\"col\">Copyright © 2023 Asociace českých nábytkářů: edited by n.e.s.p.i.</p>\r\n\r\n<p class=\"col\"><img alt=\"\" src=\"www/files/Social_black/fb.png\" style=\"width: 20px; height: 20px;\" /> <img alt=\"\" src=\"www/files/Social_black/ig.png\" style=\"width: 20px; height: 20px;\" /> <img alt=\"\" src=\"www/files/Social_black/lin.png\" style=\"width: 20px; height: 20px;\" /> <img alt=\"\" src=\"www/files/Social_black/x.png\" style=\"width: 20px; height: 20px;\" /></p>\r\n</div>\r\n</div>\r\n'),
(32, 14, 2, NULL),
(40, 4, 2, NULL),
(48, 15, 1, '<div class=\"row\">\r\n<div class=\"col\">\r\n<p><img alt=\"\" src=\"www\\assets\\images\\hon_logo.png\" /></p>\r\n\r\n<h2>Hon zazářil na veletrhu interzum!</h2>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>V termínu 9. až 12. května 2023 se konal mezinárodní veletrh Interzum v Kolíně nad Rýnem. Zúčastnila se ho i společnost HON a.s., kterí zde pravidelně prezentuje především výškobe stavitelné podnože.&nbsp;</p>\r\n\r\n<p>Letošní stánek představil atypická řešení, která firma díky valstnímu vývoji a vroběumí zákaznímům nabídnout. Kromě novinek nechyběly ani již zavedené produkty doplněné o designové detaily přinášející uživateli benefity ve formě zeleně či osvětlení pracovního místa.&nbsp;</p>\r\n</div>\r\n\r\n<div class=\"col\"><img id=\"main_img\" alt=\"\" src=\"www\\assets\\images\\hon_banner.png\" /></div>\r\n</div>\r\n'),
(49, 15, 2, NULL),
(50, 16, 1, '<div><img alt=\"\" src=\"www/files/Social_blue/fb_blue.png\" style=\"width: 30px; height: 30px;\" /> <img alt=\"\" src=\"www/files/Social_blue/ig_blue.png\" style=\"width: 30px; height: 30px;\" /> <img alt=\"\" src=\"www/files/Social_blue/lin_blue.png\" style=\"width: 30px; height: 30px;\" /> <img alt=\"\" src=\"www/files/Social_blue/x_blue.png\" style=\"width: 30px; height: 30px;\" /></div>\r\n'),
(51, 16, 2, NULL),
(57, 21, 1, '<div class=\"oacn\">\r\n<div class=\"textOacn\">\r\n<h2>Co je AČN</h2>\r\n\r\n<p>zde bude text o ačn</p>\r\n\r\n<p style=\"color:#ff0000;font-size:16px;\">26 LET * 85 ČLENŮ</p>\r\n\r\n<div class=\"image\"><img src=\"www/assets/images/logo.png\" /></div>\r\n</div>\r\n</div>\r\n'),
(58, 21, 2, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `worker`
--

CREATE TABLE `worker` (
  `id` int(11) NOT NULL,
  `worker_position_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `production_line_id` int(11) DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `male` int(11) DEFAULT NULL,
  `agency` tinyint(1) DEFAULT NULL,
  `personal_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_fund` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worker_employment_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_contract_date` date DEFAULT NULL,
  `production_line_change_id` int(11) DEFAULT NULL,
  `not_worker_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `start_date_change` date DEFAULT NULL,
  `shift_change` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yes_worker_id` int(11) DEFAULT NULL,
  `hours_vacation_base` int(11) DEFAULT NULL,
  `hours_vacation` int(11) DEFAULT NULL,
  `calendar_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_in_plan`
--

CREATE TABLE `worker_in_plan` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `worker_position_id` int(11) DEFAULT NULL,
  `manual` tinyint(1) DEFAULT NULL,
  `plus_log` tinyint(1) DEFAULT NULL,
  `minus_log` tinyint(1) DEFAULT NULL,
  `hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_in_user`
--

CREATE TABLE `worker_in_user` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `master_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_in_worker_tender`
--

CREATE TABLE `worker_in_worker_tender` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `tender_id` int(11) DEFAULT NULL,
  `result` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_desc` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_note`
--

CREATE TABLE `worker_note` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_on_traffic`
--

CREATE TABLE `worker_on_traffic` (
  `id` int(11) NOT NULL,
  `traffic_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_on_traffic_substitute`
--

CREATE TABLE `worker_on_traffic_substitute` (
  `id` int(11) NOT NULL,
  `traffic_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_on_visit`
--

CREATE TABLE `worker_on_visit` (
  `id` int(11) NOT NULL,
  `visit_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_on_visit_process`
--

CREATE TABLE `worker_on_visit_process` (
  `id` int(11) NOT NULL,
  `visit_process_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_position`
--

CREATE TABLE `worker_position` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `short` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_position_in_workplace`
--

CREATE TABLE `worker_position_in_workplace` (
  `id` int(11) NOT NULL,
  `workplace_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_position_superiority`
--

CREATE TABLE `worker_position_superiority` (
  `id` int(11) NOT NULL,
  `superior_position_id` int(11) DEFAULT NULL,
  `subordinate_position_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_tender`
--

CREATE TABLE `worker_tender` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tender_date` date DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `time_start` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_end` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tribal` tinyint(1) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `repeat_tender` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `tender_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `worker_tender_type`
--

CREATE TABLE `worker_tender_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worker_column` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_n` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `workplace`
--

CREATE TABLE `workplace` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `workplace_superiority`
--

CREATE TABLE `workplace_superiority` (
  `id` int(11) NOT NULL,
  `superior_workplace_id` int(11) DEFAULT NULL,
  `subordinate_workplace_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `absence`
--
ALTER TABLE `absence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_765AE0C9A76ED395` (`user_id`),
  ADD KEY `IDX_765AE0C95D83CC1` (`state_id`),
  ADD KEY `IDX_765AE0C923107D10` (`user_delegate_id`),
  ADD KEY `IDX_765AE0C959BB1592` (`reason_id`),
  ADD KEY `IDX_765AE0C93DA3F86F` (`originator_id`);

--
-- Klíče pro tabulku `absence_reason`
--
ALTER TABLE `absence_reason`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `absence_state`
--
ALTER TABLE `absence_state`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `api_client`
--
ALTER TABLE `api_client`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_41B343D55F37A13B` (`token`);

--
-- Klíče pro tabulku `approve`
--
ALTER TABLE `approve`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_845DDA8CFB873E97` (`approve_state_id`),
  ADD KEY `IDX_845DDA8CD5F3FDA3` (`approve_time_id`);

--
-- Klíče pro tabulku `approve_document`
--
ALTER TABLE `approve_document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_26368820E063278C` (`approve_id`),
  ADD KEY `IDX_26368820A76ED395` (`user_id`);

--
-- Klíče pro tabulku `approve_norm`
--
ALTER TABLE `approve_norm`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `approve_part`
--
ALTER TABLE `approve_part`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_125EE41FE063278C` (`approve_id`),
  ADD KEY `IDX_125EE41FFB873E97` (`approve_state_id`),
  ADD KEY `IDX_125EE41F5F7A43CB` (`approve_user_tk_id`),
  ADD KEY `IDX_125EE41F2D6FACE8` (`approve_user_chpu_id`),
  ADD KEY `IDX_125EE41F940B841A` (`approve_user_vpu_id`),
  ADD KEY `IDX_125EE41F805CFD95` (`approve_user_refo_id`),
  ADD KEY `IDX_125EE41FCB768AFF` (`approve_user_tpv_id`),
  ADD KEY `IDX_125EE41F2B62CD3C` (`norm1_id`),
  ADD KEY `IDX_125EE41F39D762D2` (`norm2_id`),
  ADD KEY `IDX_125EE41FD6EB2256` (`approve_user_koop_id`),
  ADD KEY `IDX_125EE41F986C35D9` (`approve_user_pers_id`);

--
-- Klíče pro tabulku `approve_part_document`
--
ALTER TABLE `approve_part_document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BFBE5857C7FA6B74` (`approve_part_id`),
  ADD KEY `IDX_BFBE5857A76ED395` (`user_id`);

--
-- Klíče pro tabulku `approve_state`
--
ALTER TABLE `approve_state`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `approve_time`
--
ALTER TABLE `approve_time`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `article_default`
--
ALTER TABLE `article_default`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E8C44687294869C` (`article_id`),
  ADD KEY `IDX_E8C4468B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `article_event`
--
ALTER TABLE `article_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4C1978B67294869C` (`article_id`),
  ADD KEY `IDX_4C1978B6B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `article_file`
--
ALTER TABLE `article_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3CDDB1117294869C` (`article_id`);

--
-- Klíče pro tabulku `article_file_in_language`
--
ALTER TABLE `article_file_in_language`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A6CD88F893CB796C` (`file_id`),
  ADD KEY `IDX_A6CD88F8B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `article_gallery`
--
ALTER TABLE `article_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AAF93C8D7294869C` (`article_id`),
  ADD KEY `IDX_AAF93C8DB213FA4` (`lang_id`);

--
-- Klíče pro tabulku `article_image`
--
ALTER TABLE `article_image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B28A764E7294869C` (`article_id`);

--
-- Klíče pro tabulku `article_in_menu`
--
ALTER TABLE `article_in_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BA6C41407294869C` (`article_id`),
  ADD KEY `IDX_BA6C4140CCD7E912` (`menu_id`);

--
-- Klíče pro tabulku `article_new`
--
ALTER TABLE `article_new`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F9FC673F7294869C` (`article_id`),
  ADD KEY `IDX_F9FC673FB213FA4` (`lang_id`);

--
-- Klíče pro tabulku `article_template`
--
ALTER TABLE `article_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C288EBE87294869C` (`article_id`),
  ADD KEY `IDX_C288EBE8B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `article_zo`
--
ALTER TABLE `article_zo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_84A91E867294869C` (`article_id`),
  ADD KEY `IDX_84A91E86B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `banner_language`
--
ALTER TABLE `banner_language`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D6E2BF96684EC833` (`banner_id`),
  ADD KEY `IDX_D6E2BF96B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `banner_partner`
--
ALTER TABLE `banner_partner`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `banner_partner_language`
--
ALTER TABLE `banner_partner_language`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5747369684EC833` (`banner_id`),
  ADD KEY `IDX_5747369B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `configurator`
--
ALTER TABLE `configurator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_D2260730B6C8C304` (`start_node_id`);

--
-- Klíče pro tabulku `configurator_input`
--
ALTER TABLE `configurator_input`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EF1E144ADF663348` (`configurator_id`);

--
-- Klíče pro tabulku `configurator_node`
--
ALTER TABLE `configurator_node`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6B6285AE36421AD6` (`input_id`),
  ADD KEY `IDX_6B6285AEDF663348` (`configurator_id`);

--
-- Klíče pro tabulku `configurator_node_product`
--
ALTER TABLE `configurator_node_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_69353D36460D9FD7` (`node_id`),
  ADD KEY `IDX_69353D364584665A` (`product_id`);

--
-- Klíče pro tabulku `configurator_node_relation`
--
ALTER TABLE `configurator_node_relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5EE72BC727ACA70` (`parent_id`),
  ADD KEY `IDX_5EE72BCDD62C21B` (`child_id`);

--
-- Klíče pro tabulku `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_81398E09291D7FEE` (`customer_state_id`);

--
-- Klíče pro tabulku `customer_in_type`
--
ALTER TABLE `customer_in_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BAB8CCF39395C3F3` (`customer_id`),
  ADD KEY `IDX_BAB8CCF3C54C8C93` (`type_id`);

--
-- Klíče pro tabulku `customer_notification`
--
ALTER TABLE `customer_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B18CB5D39395C3F3` (`customer_id`),
  ADD KEY `IDX_B18CB5D3F3296240` (`process_state_id`);

--
-- Klíče pro tabulku `customer_ordered`
--
ALTER TABLE `customer_ordered`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DFCFBB27291D7FEE` (`customer_state_id`),
  ADD KEY `IDX_DFCFBB276B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `customer_state`
--
ALTER TABLE `customer_state`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `customer_type`
--
ALTER TABLE `customer_type`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `datagrid_options`
--
ALTER TABLE `datagrid_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CD0E7EEFA76ED395` (`user_id`),
  ADD KEY `key_idx` (`key_name`(191));

--
-- Klíče pro tabulku `delivery_price`
--
ALTER TABLE `delivery_price`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D8698A76443707B0` (`field_id`),
  ADD KEY `IDX_D8698A76A76ED395` (`user_id`);

--
-- Klíče pro tabulku `employment`
--
ALTER TABLE `employment`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `extern_service_visit`
--
ALTER TABLE `extern_service_visit`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `field`
--
ALTER TABLE `field`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `inquiry`
--
ALTER TABLE `inquiry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5A3903F0DF663348` (`configurator_id`),
  ADD KEY `IDX_5A3903F09395C3F3` (`customer_id`);

--
-- Klíče pro tabulku `inquiry_product`
--
ALTER TABLE `inquiry_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_38576A08A7AD6D71` (`inquiry_id`),
  ADD KEY `IDX_38576A084584665A` (`product_id`);

--
-- Klíče pro tabulku `inquiry_value`
--
ALTER TABLE `inquiry_value`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_2975CB2CA7AD6D71` (`inquiry_id`);

--
-- Klíče pro tabulku `item_in_process`
--
ALTER TABLE `item_in_process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_701F13727EC2F574` (`process_id`);

--
-- Klíče pro tabulku `item_type`
--
ALTER TABLE `item_type`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `item_type_in_item`
--
ALTER TABLE `item_type_in_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D2DB884EC54C8C93` (`type_id`),
  ADD KEY `IDX_D2DB884E126F525E` (`item_id`);

--
-- Klíče pro tabulku `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `machine`
--
ALTER TABLE `machine`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `machine_in_extern_service_visit`
--
ALTER TABLE `machine_in_extern_service_visit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7C2E0882F6B75B26` (`machine_id`),
  ADD KEY `IDX_7C2E08829C41544A` (`extern_service_visit_id`);

--
-- Klíče pro tabulku `managed_change`
--
ALTER TABLE `managed_change`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3CA6C0FE9395C3F3` (`customer_id`),
  ADD KEY `IDX_3CA6C0FE3DA3F86F` (`originator_id`),
  ADD KEY `IDX_3CA6C0FE5BC17E2` (`resulted_by_id`),
  ADD KEY `IDX_3CA6C0FED077A41A` (`parent_change_id`),
  ADD KEY `IDX_3CA6C0FE2C77F30D` (`approve_user_id`);

--
-- Klíče pro tabulku `managed_change_step`
--
ALTER TABLE `managed_change_step`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_31D3D6302D544A45` (`managed_change_id`);

--
-- Klíče pro tabulku `managed_risc`
--
ALTER TABLE `managed_risc`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `managed_risc_revaluation`
--
ALTER TABLE `managed_risc_revaluation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_22C46D0A69690D3` (`managed_risc_id`);

--
-- Klíče pro tabulku `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CBE7595DCD6110` (`stock_id`),
  ADD KEY `IDX_7CBE7595FE54D947` (`group_id`);

--
-- Klíče pro tabulku `material_group`
--
ALTER TABLE `material_group`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `material_need_buy`
--
ALTER TABLE `material_need_buy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A83221F575FA0FF2` (`visit_id`),
  ADD KEY `IDX_A83221F5E308AC6F` (`material_id`);

--
-- Klíče pro tabulku `material_on_visit`
--
ALTER TABLE `material_on_visit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_893CDA6275FA0FF2` (`visit_id`),
  ADD KEY `IDX_893CDA62E308AC6F` (`material_id`);

--
-- Klíče pro tabulku `material_stock`
--
ALTER TABLE `material_stock`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7D053A93BE9F9D54` (`parent_menu_id`);

--
-- Klíče pro tabulku `menu_language`
--
ALTER TABLE `menu_language`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F88A3423CCD7E912` (`menu_id`),
  ADD KEY `IDX_F88A3423B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `offer`
--
ALTER TABLE `offer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_29D6873E9395C3F3` (`customer_id`),
  ADD KEY `IDX_29D6873E9F7F22E2` (`salesman_id`),
  ADD KEY `IDX_29D6873E5EF6E847` (`product_description_id`),
  ADD KEY `IDX_29D6873E1645DEA9` (`reference_id`),
  ADD KEY `IDX_29D6873E3DA3F86F` (`originator_id`),
  ADD KEY `IDX_29D6873EA7AD6D71` (`inquiry_id`),
  ADD KEY `IDX_29D6873EB5B63A6B` (`vat_id`);

--
-- Klíče pro tabulku `offer_part`
--
ALTER TABLE `offer_part`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B7F75ED153C674EE` (`offer_id`),
  ADD KEY `IDX_B7F75ED15DA0FB8` (`template_id`);

--
-- Klíče pro tabulku `offer_part_template`
--
ALTER TABLE `offer_part_template`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `offer_product`
--
ALTER TABLE `offer_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7242C2A453C674EE` (`offer_id`),
  ADD KEY `IDX_7242C2A44584665A` (`product_id`);

--
-- Klíče pro tabulku `operation_log`
--
ALTER TABLE `operation_log`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `operation_log_item`
--
ALTER TABLE `operation_log_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EB03B4E697E7E4E4` (`operation_log_id`);

--
-- Klíče pro tabulku `operation_log_problem`
--
ALTER TABLE `operation_log_problem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1706CD5997E7E4E4` (`operation_log_id`);

--
-- Klíče pro tabulku `operation_log_suggestion`
--
ALTER TABLE `operation_log_suggestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EF47C2B397E7E4E4` (`operation_log_id`);

--
-- Klíče pro tabulku `permission_group`
--
ALTER TABLE `permission_group`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `permission_rule`
--
ALTER TABLE `permission_rule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1716376CFE54D947` (`group_id`);

--
-- Klíče pro tabulku `process`
--
ALTER TABLE `process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_861D18969395C3F3` (`customer_id`),
  ADD KEY `IDX_861D1896F3296240` (`process_state_id`),
  ADD KEY `IDX_861D18963DA3F86F` (`originator_id`);

--
-- Klíče pro tabulku `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_klic_polozky` (`klic_polozky`);

--
-- Klíče pro tabulku `production_line`
--
ALTER TABLE `production_line`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `production_plan`
--
ALTER TABLE `production_plan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_64A1069B83297E7` (`reservation_id`);

--
-- Klíče pro tabulku `production_progress_report_setting`
--
ALTER TABLE `production_progress_report_setting`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `production_setting`
--
ALTER TABLE `production_setting`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `product_file`
--
ALTER TABLE `product_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_17714B14584665A` (`product_id`);

--
-- Klíče pro tabulku `product_file_in_language`
--
ALTER TABLE `product_file_in_language`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3F15977793CB796C` (`file_id`),
  ADD KEY `IDX_3F159777B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `product_image`
--
ALTER TABLE `product_image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_64617F034584665A` (`product_id`);

--
-- Klíče pro tabulku `product_in_menu`
--
ALTER TABLE `product_in_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_86671E47CCD7E912` (`menu_id`),
  ADD KEY `IDX_86671E474584665A` (`product_id`);

--
-- Klíče pro tabulku `product_in_plan`
--
ALTER TABLE `product_in_plan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_26387FA9E899029B` (`plan_id`),
  ADD KEY `IDX_26387FA9B83297E7` (`reservation_id`);

--
-- Klíče pro tabulku `product_language`
--
ALTER TABLE `product_language`
  ADD KEY `FK_1F6B1B224584665A` (`product_id`),
  ADD KEY `FK_1F6B1B22B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `qualification`
--
ALTER TABLE `qualification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B712F0CEAE80F5DF` (`department_id`),
  ADD KEY `IDX_B712F0CEA76ED395` (`user_id`);

--
-- Klíče pro tabulku `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_42C849559395C3F3` (`customer_id`),
  ADD KEY `IDX_42C8495575FAE9DB` (`reservation_item_id`),
  ADD KEY `IDX_42C849553DA3F86F` (`originator_id`);

--
-- Klíče pro tabulku `reservation_item`
--
ALTER TABLE `reservation_item`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `reservation_plan`
--
ALTER TABLE `reservation_plan`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `reservation_product`
--
ALTER TABLE `reservation_product`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E19D9AD26B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `shift_bonus`
--
ALTER TABLE `shift_bonus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_947699246B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `shift_bonus_group`
--
ALTER TABLE `shift_bonus_group`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `shift_bonus_template`
--
ALTER TABLE `shift_bonus_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5F3D97D236095116` (`shift_bonus_group_id`);

--
-- Klíče pro tabulku `shift_plan`
--
ALTER TABLE `shift_plan`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5E3DE477C54C8C93` (`type_id`);

--
-- Klíče pro tabulku `skill_in_worker`
--
ALTER TABLE `skill_in_worker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7BB42D1A5585C142` (`skill_id`),
  ADD KEY `IDX_7BB42D1A6B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `skill_in_worker_position`
--
ALTER TABLE `skill_in_worker_position`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_86AE884C5585C142` (`skill_id`),
  ADD KEY `IDX_86AE884CDD842E46` (`position_id`);

--
-- Klíče pro tabulku `skill_in_worker_tender`
--
ALTER TABLE `skill_in_worker_tender`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9AD3C1D55585C142` (`skill_id`),
  ADD KEY `IDX_9AD3C1D59245DE54` (`tender_id`);

--
-- Klíče pro tabulku `skill_type`
--
ALTER TABLE `skill_type`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_527EDB254518D68D` (`task_state_id`),
  ADD KEY `IDX_527EDB253DA3F86F` (`originator_id`),
  ADD KEY `IDX_527EDB25E1501A05` (`assigned_id`),
  ADD KEY `IDX_527EDB25419863A8` (`last_edited_id`);

--
-- Klíče pro tabulku `task_comment`
--
ALTER TABLE `task_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8B9578868DB60186` (`task_id`),
  ADD KEY `IDX_8B957886A76ED395` (`user_id`);

--
-- Klíče pro tabulku `task_document`
--
ALTER TABLE `task_document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_98A9603A8DB60186` (`task_id`),
  ADD KEY `IDX_98A9603AA76ED395` (`user_id`);

--
-- Klíče pro tabulku `task_log`
--
ALTER TABLE `task_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E0BD90428DB60186` (`task_id`),
  ADD KEY `IDX_E0BD9042A76ED395` (`user_id`);

--
-- Klíče pro tabulku `task_state`
--
ALTER TABLE `task_state`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `traffic`
--
ALTER TABLE `traffic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_556026309395C3F3` (`customer_id`),
  ADD KEY `IDX_5560263057A93DD8` (`customer_ordered_id`);

--
-- Klíče pro tabulku `translation`
--
ALTER TABLE `translation`
  ADD KEY `FK_B469456FB213FA4` (`lang_id`);

--
-- Klíče pro tabulku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  ADD KEY `IDX_8D93D649FE54D947` (`group_id`),
  ADD KEY `IDX_8D93D649AE80F5DF` (`department_id`),
  ADD KEY `username` (`username`);

--
-- Klíče pro tabulku `user_in_workplace`
--
ALTER TABLE `user_in_workplace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_32AD0A4FAC25FB46` (`workplace_id`),
  ADD KEY `IDX_32AD0A4F13B3DB11` (`master_id`);

--
-- Klíče pro tabulku `vacation`
--
ALTER TABLE `vacation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E3DADF756B20BA36` (`worker_id`),
  ADD KEY `IDX_E3DADF75D4EE03F0` (`vacation_type_id`);

--
-- Klíče pro tabulku `vacation_fund`
--
ALTER TABLE `vacation_fund`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3BFF98B26B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `vacation_type`
--
ALTER TABLE `vacation_type`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `vat`
--
ALTER TABLE `vat`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `visit`
--
ALTER TABLE `visit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_437EE9399395C3F3` (`customer_id`),
  ADD KEY `IDX_437EE93957A93DD8` (`customer_ordered_id`),
  ADD KEY `IDX_437EE939CC46C289` (`traffic_id`),
  ADD KEY `IDX_437EE9391A2A8B67` (`visit_process_id`),
  ADD KEY `IDX_437EE9395D83CC1` (`state_id`),
  ADD KEY `IDX_437EE9396BF700BD` (`status_id`),
  ADD KEY `IDX_437EE939C6BCD788` (`refrigerant_id`);

--
-- Klíče pro tabulku `visit_document`
--
ALTER TABLE `visit_document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DD3F546175FA0FF2` (`visit_id`),
  ADD KEY `IDX_DD3F5461A76ED395` (`user_id`);

--
-- Klíče pro tabulku `visit_log`
--
ALTER TABLE `visit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B72D696975FA0FF2` (`visit_id`),
  ADD KEY `IDX_B72D6969A76ED395` (`user_id`);

--
-- Klíče pro tabulku `visit_process`
--
ALTER TABLE `visit_process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D0941C519395C3F3` (`customer_id`),
  ADD KEY `IDX_D0941C5157A93DD8` (`customer_ordered_id`),
  ADD KEY `IDX_D0941C51CC46C289` (`traffic_id`),
  ADD KEY `IDX_D0941C515D83CC1` (`state_id`);

--
-- Klíče pro tabulku `visit_process_state`
--
ALTER TABLE `visit_process_state`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `visit_state`
--
ALTER TABLE `visit_state`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `visit_status`
--
ALTER TABLE `visit_status`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `web_setting`
--
ALTER TABLE `web_setting`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `web_setting_language`
--
ALTER TABLE `web_setting_language`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8C361F35EE35BD72` (`setting_id`),
  ADD KEY `IDX_8C361F35B213FA4` (`lang_id`);

--
-- Klíče pro tabulku `worker`
--
ALTER TABLE `worker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9FB2BF62140C3281` (`worker_position_id`),
  ADD KEY `IDX_9FB2BF62586EF89F` (`production_line_id`),
  ADD KEY `IDX_9FB2BF62D6D4B5AD` (`worker_employment_id`),
  ADD KEY `IDX_9FB2BF6236C2AA5A` (`production_line_change_id`),
  ADD KEY `IDX_9FB2BF622A6A3139` (`not_worker_id`),
  ADD KEY `IDX_9FB2BF62A76ED395` (`user_id`),
  ADD KEY `IDX_9FB2BF6220126C59` (`yes_worker_id`);

--
-- Klíče pro tabulku `worker_in_plan`
--
ALTER TABLE `worker_in_plan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C30E1F546B20BA36` (`worker_id`),
  ADD KEY `IDX_C30E1F54E899029B` (`plan_id`),
  ADD KEY `IDX_C30E1F54140C3281` (`worker_position_id`);

--
-- Klíče pro tabulku `worker_in_user`
--
ALTER TABLE `worker_in_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_93C792606B20BA36` (`worker_id`),
  ADD KEY `IDX_93C7926013B3DB11` (`master_id`);

--
-- Klíče pro tabulku `worker_in_worker_tender`
--
ALTER TABLE `worker_in_worker_tender`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AE649DBE6B20BA36` (`worker_id`),
  ADD KEY `IDX_AE649DBE9245DE54` (`tender_id`);

--
-- Klíče pro tabulku `worker_note`
--
ALTER TABLE `worker_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4784C456B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `worker_on_traffic`
--
ALTER TABLE `worker_on_traffic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_97F134EACC46C289` (`traffic_id`),
  ADD KEY `IDX_97F134EA6B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `worker_on_traffic_substitute`
--
ALTER TABLE `worker_on_traffic_substitute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_ACF21091CC46C289` (`traffic_id`),
  ADD KEY `IDX_ACF210916B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `worker_on_visit`
--
ALTER TABLE `worker_on_visit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C7BD2C9675FA0FF2` (`visit_id`),
  ADD KEY `IDX_C7BD2C966B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `worker_on_visit_process`
--
ALTER TABLE `worker_on_visit_process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EEC623151A2A8B67` (`visit_process_id`),
  ADD KEY `IDX_EEC623156B20BA36` (`worker_id`);

--
-- Klíče pro tabulku `worker_position`
--
ALTER TABLE `worker_position`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `worker_position_in_workplace`
--
ALTER TABLE `worker_position_in_workplace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B2F64C8EAC25FB46` (`workplace_id`),
  ADD KEY `IDX_B2F64C8EDD842E46` (`position_id`);

--
-- Klíče pro tabulku `worker_position_superiority`
--
ALTER TABLE `worker_position_superiority`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4861F716F35EE208` (`superior_position_id`),
  ADD KEY `IDX_4861F7167E7EEAD1` (`subordinate_position_id`);

--
-- Klíče pro tabulku `worker_tender`
--
ALTER TABLE `worker_tender`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `worker_tender_type`
--
ALTER TABLE `worker_tender_type`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `workplace`
--
ALTER TABLE `workplace`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `workplace_superiority`
--
ALTER TABLE `workplace_superiority`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5D9228DC3D6F4D1D` (`superior_workplace_id`),
  ADD KEY `IDX_5D9228DCC2ED0765` (`subordinate_workplace_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `absence`
--
ALTER TABLE `absence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `absence_reason`
--
ALTER TABLE `absence_reason`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `absence_state`
--
ALTER TABLE `absence_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pro tabulku `api_client`
--
ALTER TABLE `api_client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve`
--
ALTER TABLE `approve`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve_document`
--
ALTER TABLE `approve_document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve_norm`
--
ALTER TABLE `approve_norm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve_part`
--
ALTER TABLE `approve_part`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve_part_document`
--
ALTER TABLE `approve_part_document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve_state`
--
ALTER TABLE `approve_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `approve_time`
--
ALTER TABLE `approve_time`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_default`
--
ALTER TABLE `article_default`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_event`
--
ALTER TABLE `article_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_file`
--
ALTER TABLE `article_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_file_in_language`
--
ALTER TABLE `article_file_in_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_gallery`
--
ALTER TABLE `article_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_image`
--
ALTER TABLE `article_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_in_menu`
--
ALTER TABLE `article_in_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_new`
--
ALTER TABLE `article_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_template`
--
ALTER TABLE `article_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `article_zo`
--
ALTER TABLE `article_zo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `banner_language`
--
ALTER TABLE `banner_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `banner_partner`
--
ALTER TABLE `banner_partner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `banner_partner_language`
--
ALTER TABLE `banner_partner_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `configurator`
--
ALTER TABLE `configurator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `configurator_input`
--
ALTER TABLE `configurator_input`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `configurator_node`
--
ALTER TABLE `configurator_node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `configurator_node_product`
--
ALTER TABLE `configurator_node_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `configurator_node_relation`
--
ALTER TABLE `configurator_node_relation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `currency`
--
ALTER TABLE `currency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pro tabulku `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `customer_in_type`
--
ALTER TABLE `customer_in_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `customer_notification`
--
ALTER TABLE `customer_notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `customer_ordered`
--
ALTER TABLE `customer_ordered`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `customer_state`
--
ALTER TABLE `customer_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `customer_type`
--
ALTER TABLE `customer_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `datagrid_options`
--
ALTER TABLE `datagrid_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pro tabulku `delivery_price`
--
ALTER TABLE `delivery_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `department`
--
ALTER TABLE `department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `document`
--
ALTER TABLE `document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `employment`
--
ALTER TABLE `employment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `extern_service_visit`
--
ALTER TABLE `extern_service_visit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `field`
--
ALTER TABLE `field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `inquiry`
--
ALTER TABLE `inquiry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `inquiry_product`
--
ALTER TABLE `inquiry_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `inquiry_value`
--
ALTER TABLE `inquiry_value`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `item_in_process`
--
ALTER TABLE `item_in_process`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `item_type`
--
ALTER TABLE `item_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `item_type_in_item`
--
ALTER TABLE `item_type_in_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `language`
--
ALTER TABLE `language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `machine`
--
ALTER TABLE `machine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `machine_in_extern_service_visit`
--
ALTER TABLE `machine_in_extern_service_visit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `managed_change`
--
ALTER TABLE `managed_change`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `managed_change_step`
--
ALTER TABLE `managed_change_step`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `managed_risc`
--
ALTER TABLE `managed_risc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `managed_risc_revaluation`
--
ALTER TABLE `managed_risc_revaluation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `material`
--
ALTER TABLE `material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `material_group`
--
ALTER TABLE `material_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `material_need_buy`
--
ALTER TABLE `material_need_buy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `material_on_visit`
--
ALTER TABLE `material_on_visit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `material_stock`
--
ALTER TABLE `material_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `menu_language`
--
ALTER TABLE `menu_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `offer`
--
ALTER TABLE `offer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `offer_part`
--
ALTER TABLE `offer_part`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `offer_part_template`
--
ALTER TABLE `offer_part_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `offer_product`
--
ALTER TABLE `offer_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `operation_log`
--
ALTER TABLE `operation_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `operation_log_item`
--
ALTER TABLE `operation_log_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `operation_log_problem`
--
ALTER TABLE `operation_log_problem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `operation_log_suggestion`
--
ALTER TABLE `operation_log_suggestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `permission_group`
--
ALTER TABLE `permission_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `permission_rule`
--
ALTER TABLE `permission_rule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pro tabulku `process`
--
ALTER TABLE `process`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `production_line`
--
ALTER TABLE `production_line`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `production_plan`
--
ALTER TABLE `production_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `production_progress_report_setting`
--
ALTER TABLE `production_progress_report_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `production_setting`
--
ALTER TABLE `production_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `product_file`
--
ALTER TABLE `product_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `product_file_in_language`
--
ALTER TABLE `product_file_in_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `product_image`
--
ALTER TABLE `product_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `product_in_menu`
--
ALTER TABLE `product_in_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `product_in_plan`
--
ALTER TABLE `product_in_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `qualification`
--
ALTER TABLE `qualification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `reservation_item`
--
ALTER TABLE `reservation_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `reservation_plan`
--
ALTER TABLE `reservation_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `reservation_product`
--
ALTER TABLE `reservation_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `shift_bonus`
--
ALTER TABLE `shift_bonus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `shift_bonus_group`
--
ALTER TABLE `shift_bonus_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `shift_bonus_template`
--
ALTER TABLE `shift_bonus_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `shift_plan`
--
ALTER TABLE `shift_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skill`
--
ALTER TABLE `skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skill_in_worker`
--
ALTER TABLE `skill_in_worker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skill_in_worker_position`
--
ALTER TABLE `skill_in_worker_position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skill_in_worker_tender`
--
ALTER TABLE `skill_in_worker_tender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `skill_type`
--
ALTER TABLE `skill_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `task`
--
ALTER TABLE `task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `task_comment`
--
ALTER TABLE `task_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `task_document`
--
ALTER TABLE `task_document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `task_log`
--
ALTER TABLE `task_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `task_state`
--
ALTER TABLE `task_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `traffic`
--
ALTER TABLE `traffic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `user_in_workplace`
--
ALTER TABLE `user_in_workplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `vacation`
--
ALTER TABLE `vacation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `vacation_fund`
--
ALTER TABLE `vacation_fund`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `vacation_type`
--
ALTER TABLE `vacation_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `vat`
--
ALTER TABLE `vat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `visit`
--
ALTER TABLE `visit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `visit_document`
--
ALTER TABLE `visit_document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `visit_log`
--
ALTER TABLE `visit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `visit_process`
--
ALTER TABLE `visit_process`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `visit_process_state`
--
ALTER TABLE `visit_process_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pro tabulku `visit_state`
--
ALTER TABLE `visit_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pro tabulku `visit_status`
--
ALTER TABLE `visit_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `web_setting`
--
ALTER TABLE `web_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pro tabulku `web_setting_language`
--
ALTER TABLE `web_setting_language`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT pro tabulku `worker`
--
ALTER TABLE `worker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_in_plan`
--
ALTER TABLE `worker_in_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_in_user`
--
ALTER TABLE `worker_in_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_in_worker_tender`
--
ALTER TABLE `worker_in_worker_tender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_note`
--
ALTER TABLE `worker_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_on_traffic`
--
ALTER TABLE `worker_on_traffic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_on_traffic_substitute`
--
ALTER TABLE `worker_on_traffic_substitute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_on_visit`
--
ALTER TABLE `worker_on_visit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_on_visit_process`
--
ALTER TABLE `worker_on_visit_process`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_position`
--
ALTER TABLE `worker_position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_position_in_workplace`
--
ALTER TABLE `worker_position_in_workplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_position_superiority`
--
ALTER TABLE `worker_position_superiority`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_tender`
--
ALTER TABLE `worker_tender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `worker_tender_type`
--
ALTER TABLE `worker_tender_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `workplace`
--
ALTER TABLE `workplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `workplace_superiority`
--
ALTER TABLE `workplace_superiority`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `absence`
--
ALTER TABLE `absence`
  ADD CONSTRAINT `FK_765AE0C923107D10` FOREIGN KEY (`user_delegate_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_765AE0C93DA3F86F` FOREIGN KEY (`originator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_765AE0C959BB1592` FOREIGN KEY (`reason_id`) REFERENCES `absence_reason` (`id`),
  ADD CONSTRAINT `FK_765AE0C95D83CC1` FOREIGN KEY (`state_id`) REFERENCES `absence_state` (`id`),
  ADD CONSTRAINT `FK_765AE0C9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `approve`
--
ALTER TABLE `approve`
  ADD CONSTRAINT `FK_845DDA8CD5F3FDA3` FOREIGN KEY (`approve_time_id`) REFERENCES `approve_time` (`id`),
  ADD CONSTRAINT `FK_845DDA8CFB873E97` FOREIGN KEY (`approve_state_id`) REFERENCES `approve_state` (`id`);

--
-- Omezení pro tabulku `approve_document`
--
ALTER TABLE `approve_document`
  ADD CONSTRAINT `FK_26368820A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_26368820E063278C` FOREIGN KEY (`approve_id`) REFERENCES `approve` (`id`);

--
-- Omezení pro tabulku `approve_part`
--
ALTER TABLE `approve_part`
  ADD CONSTRAINT `FK_125EE41F2B62CD3C` FOREIGN KEY (`norm1_id`) REFERENCES `approve_norm` (`id`),
  ADD CONSTRAINT `FK_125EE41F2D6FACE8` FOREIGN KEY (`approve_user_chpu_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41F39D762D2` FOREIGN KEY (`norm2_id`) REFERENCES `approve_norm` (`id`),
  ADD CONSTRAINT `FK_125EE41F5F7A43CB` FOREIGN KEY (`approve_user_tk_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41F805CFD95` FOREIGN KEY (`approve_user_refo_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41F940B841A` FOREIGN KEY (`approve_user_vpu_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41F986C35D9` FOREIGN KEY (`approve_user_pers_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41FCB768AFF` FOREIGN KEY (`approve_user_tpv_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41FD6EB2256` FOREIGN KEY (`approve_user_koop_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_125EE41FE063278C` FOREIGN KEY (`approve_id`) REFERENCES `approve` (`id`),
  ADD CONSTRAINT `FK_125EE41FFB873E97` FOREIGN KEY (`approve_state_id`) REFERENCES `approve_state` (`id`);

--
-- Omezení pro tabulku `approve_part_document`
--
ALTER TABLE `approve_part_document`
  ADD CONSTRAINT `FK_BFBE5857A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_BFBE5857C7FA6B74` FOREIGN KEY (`approve_part_id`) REFERENCES `approve_part` (`id`);

--
-- Omezení pro tabulku `article_default`
--
ALTER TABLE `article_default`
  ADD CONSTRAINT `FK_E8C44687294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_E8C4468B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `article_event`
--
ALTER TABLE `article_event`
  ADD CONSTRAINT `FK_4C1978B67294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_4C1978B6B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `article_file`
--
ALTER TABLE `article_file`
  ADD CONSTRAINT `FK_3CDDB1117294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Omezení pro tabulku `article_file_in_language`
--
ALTER TABLE `article_file_in_language`
  ADD CONSTRAINT `FK_A6CD88F893CB796C` FOREIGN KEY (`file_id`) REFERENCES `article_file` (`id`),
  ADD CONSTRAINT `FK_A6CD88F8B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `article_gallery`
--
ALTER TABLE `article_gallery`
  ADD CONSTRAINT `FK_AAF93C8D7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_AAF93C8DB213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `article_image`
--
ALTER TABLE `article_image`
  ADD CONSTRAINT `FK_B28A764E7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Omezení pro tabulku `article_in_menu`
--
ALTER TABLE `article_in_menu`
  ADD CONSTRAINT `FK_BA6C41407294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BA6C4140CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `article_new`
--
ALTER TABLE `article_new`
  ADD CONSTRAINT `FK_F9FC673F7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_F9FC673FB213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `article_template`
--
ALTER TABLE `article_template`
  ADD CONSTRAINT `FK_C288EBE87294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_C288EBE8B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `article_zo`
--
ALTER TABLE `article_zo`
  ADD CONSTRAINT `FK_84A91E867294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_84A91E86B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `banner_language`
--
ALTER TABLE `banner_language`
  ADD CONSTRAINT `FK_D6E2BF96684EC833` FOREIGN KEY (`banner_id`) REFERENCES `banner` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D6E2BF96B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `banner_partner_language`
--
ALTER TABLE `banner_partner_language`
  ADD CONSTRAINT `FK_5747369684EC833` FOREIGN KEY (`banner_id`) REFERENCES `banner_partner` (`id`),
  ADD CONSTRAINT `FK_5747369B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `configurator`
--
ALTER TABLE `configurator`
  ADD CONSTRAINT `FK_D2260730B6C8C304` FOREIGN KEY (`start_node_id`) REFERENCES `configurator_node` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `configurator_input`
--
ALTER TABLE `configurator_input`
  ADD CONSTRAINT `FK_EF1E144ADF663348` FOREIGN KEY (`configurator_id`) REFERENCES `configurator` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `configurator_node`
--
ALTER TABLE `configurator_node`
  ADD CONSTRAINT `FK_6B6285AE36421AD6` FOREIGN KEY (`input_id`) REFERENCES `configurator_input` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_6B6285AEDF663348` FOREIGN KEY (`configurator_id`) REFERENCES `configurator` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `configurator_node_relation`
--
ALTER TABLE `configurator_node_relation`
  ADD CONSTRAINT `FK_5EE72BC727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `configurator_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_5EE72BCDD62C21B` FOREIGN KEY (`child_id`) REFERENCES `configurator_node` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `FK_81398E09291D7FEE` FOREIGN KEY (`customer_state_id`) REFERENCES `customer_state` (`id`);

--
-- Omezení pro tabulku `customer_in_type`
--
ALTER TABLE `customer_in_type`
  ADD CONSTRAINT `FK_BAB8CCF39395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `FK_BAB8CCF3C54C8C93` FOREIGN KEY (`type_id`) REFERENCES `customer_type` (`id`);

--
-- Omezení pro tabulku `customer_ordered`
--
ALTER TABLE `customer_ordered`
  ADD CONSTRAINT `FK_DFCFBB27291D7FEE` FOREIGN KEY (`customer_state_id`) REFERENCES `customer_state` (`id`),
  ADD CONSTRAINT `FK_DFCFBB276B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `datagrid_options`
--
ALTER TABLE `datagrid_options`
  ADD CONSTRAINT `FK_CD0E7EEFA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `FK_D8698A76443707B0` FOREIGN KEY (`field_id`) REFERENCES `field` (`id`),
  ADD CONSTRAINT `FK_D8698A76A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `inquiry`
--
ALTER TABLE `inquiry`
  ADD CONSTRAINT `FK_5A3903F09395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `FK_5A3903F0DF663348` FOREIGN KEY (`configurator_id`) REFERENCES `configurator` (`id`);

--
-- Omezení pro tabulku `inquiry_product`
--
ALTER TABLE `inquiry_product`
  ADD CONSTRAINT `FK_38576A084584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_38576A08A7AD6D71` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiry` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `inquiry_value`
--
ALTER TABLE `inquiry_value`
  ADD CONSTRAINT `FK_2975CB2CA7AD6D71` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiry` (`id`);

--
-- Omezení pro tabulku `item_in_process`
--
ALTER TABLE `item_in_process`
  ADD CONSTRAINT `FK_701F13727EC2F574` FOREIGN KEY (`process_id`) REFERENCES `process` (`id`);

--
-- Omezení pro tabulku `item_type_in_item`
--
ALTER TABLE `item_type_in_item`
  ADD CONSTRAINT `FK_D2DB884E126F525E` FOREIGN KEY (`item_id`) REFERENCES `item_in_process` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D2DB884EC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `item_type` (`id`);

--
-- Omezení pro tabulku `machine_in_extern_service_visit`
--
ALTER TABLE `machine_in_extern_service_visit`
  ADD CONSTRAINT `FK_7C2E08829C41544A` FOREIGN KEY (`extern_service_visit_id`) REFERENCES `extern_service_visit` (`id`),
  ADD CONSTRAINT `FK_7C2E0882F6B75B26` FOREIGN KEY (`machine_id`) REFERENCES `machine` (`id`);

--
-- Omezení pro tabulku `managed_change`
--
ALTER TABLE `managed_change`
  ADD CONSTRAINT `FK_3CA6C0FE2C77F30D` FOREIGN KEY (`approve_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_3CA6C0FE3DA3F86F` FOREIGN KEY (`originator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_3CA6C0FE5BC17E2` FOREIGN KEY (`resulted_by_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_3CA6C0FE9395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `FK_3CA6C0FED077A41A` FOREIGN KEY (`parent_change_id`) REFERENCES `managed_change` (`id`);

--
-- Omezení pro tabulku `managed_change_step`
--
ALTER TABLE `managed_change_step`
  ADD CONSTRAINT `FK_31D3D6302D544A45` FOREIGN KEY (`managed_change_id`) REFERENCES `managed_change` (`id`);

--
-- Omezení pro tabulku `managed_risc_revaluation`
--
ALTER TABLE `managed_risc_revaluation`
  ADD CONSTRAINT `FK_22C46D0A69690D3` FOREIGN KEY (`managed_risc_id`) REFERENCES `managed_risc` (`id`);

--
-- Omezení pro tabulku `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `FK_7CBE7595DCD6110` FOREIGN KEY (`stock_id`) REFERENCES `material_stock` (`id`),
  ADD CONSTRAINT `FK_7CBE7595FE54D947` FOREIGN KEY (`group_id`) REFERENCES `material_group` (`id`);

--
-- Omezení pro tabulku `material_need_buy`
--
ALTER TABLE `material_need_buy`
  ADD CONSTRAINT `FK_A83221F575FA0FF2` FOREIGN KEY (`visit_id`) REFERENCES `visit` (`id`),
  ADD CONSTRAINT `FK_A83221F5E308AC6F` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`);

--
-- Omezení pro tabulku `material_on_visit`
--
ALTER TABLE `material_on_visit`
  ADD CONSTRAINT `FK_893CDA6275FA0FF2` FOREIGN KEY (`visit_id`) REFERENCES `visit` (`id`),
  ADD CONSTRAINT `FK_893CDA62E308AC6F` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`);

--
-- Omezení pro tabulku `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `FK_7D053A93BE9F9D54` FOREIGN KEY (`parent_menu_id`) REFERENCES `menu` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `menu_language`
--
ALTER TABLE `menu_language`
  ADD CONSTRAINT `FK_F88A3423B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`),
  ADD CONSTRAINT `FK_F88A3423CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `offer`
--
ALTER TABLE `offer`
  ADD CONSTRAINT `FK_29D6873E1645DEA9` FOREIGN KEY (`reference_id`) REFERENCES `offer_part_template` (`id`),
  ADD CONSTRAINT `FK_29D6873E3DA3F86F` FOREIGN KEY (`originator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_29D6873E5EF6E847` FOREIGN KEY (`product_description_id`) REFERENCES `offer_part_template` (`id`),
  ADD CONSTRAINT `FK_29D6873E9395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `FK_29D6873E9F7F22E2` FOREIGN KEY (`salesman_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_29D6873EA7AD6D71` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiry` (`id`),
  ADD CONSTRAINT `FK_29D6873EB5B63A6B` FOREIGN KEY (`vat_id`) REFERENCES `vat` (`id`);

--
-- Omezení pro tabulku `offer_part`
--
ALTER TABLE `offer_part`
  ADD CONSTRAINT `FK_B7F75ED153C674EE` FOREIGN KEY (`offer_id`) REFERENCES `offer` (`id`),
  ADD CONSTRAINT `FK_B7F75ED15DA0FB8` FOREIGN KEY (`template_id`) REFERENCES `offer_part_template` (`id`);

--
-- Omezení pro tabulku `offer_product`
--
ALTER TABLE `offer_product`
  ADD CONSTRAINT `FK_7242C2A44584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_7242C2A453C674EE` FOREIGN KEY (`offer_id`) REFERENCES `offer` (`id`);

--
-- Omezení pro tabulku `operation_log_item`
--
ALTER TABLE `operation_log_item`
  ADD CONSTRAINT `FK_EB03B4E697E7E4E4` FOREIGN KEY (`operation_log_id`) REFERENCES `operation_log` (`id`);

--
-- Omezení pro tabulku `operation_log_problem`
--
ALTER TABLE `operation_log_problem`
  ADD CONSTRAINT `FK_1706CD5997E7E4E4` FOREIGN KEY (`operation_log_id`) REFERENCES `operation_log` (`id`);

--
-- Omezení pro tabulku `operation_log_suggestion`
--
ALTER TABLE `operation_log_suggestion`
  ADD CONSTRAINT `FK_EF47C2B397E7E4E4` FOREIGN KEY (`operation_log_id`) REFERENCES `operation_log` (`id`);

--
-- Omezení pro tabulku `permission_rule`
--
ALTER TABLE `permission_rule`
  ADD CONSTRAINT `FK_1716376CFE54D947` FOREIGN KEY (`group_id`) REFERENCES `permission_group` (`id`);

--
-- Omezení pro tabulku `production_plan`
--
ALTER TABLE `production_plan`
  ADD CONSTRAINT `FK_64A1069B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservation_plan` (`id`);

--
-- Omezení pro tabulku `product_file`
--
ALTER TABLE `product_file`
  ADD CONSTRAINT `FK_17714B14584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Omezení pro tabulku `product_file_in_language`
--
ALTER TABLE `product_file_in_language`
  ADD CONSTRAINT `FK_3F15977793CB796C` FOREIGN KEY (`file_id`) REFERENCES `product_file` (`id`),
  ADD CONSTRAINT `FK_3F159777B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `product_image`
--
ALTER TABLE `product_image`
  ADD CONSTRAINT `FK_64617F034584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Omezení pro tabulku `product_in_menu`
--
ALTER TABLE `product_in_menu`
  ADD CONSTRAINT `FK_86671E474584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `FK_86671E47CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);

--
-- Omezení pro tabulku `product_in_plan`
--
ALTER TABLE `product_in_plan`
  ADD CONSTRAINT `FK_26387FA9B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservation_product` (`id`),
  ADD CONSTRAINT `FK_26387FA9E899029B` FOREIGN KEY (`plan_id`) REFERENCES `production_plan` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `product_language`
--
ALTER TABLE `product_language`
  ADD CONSTRAINT `FK_1F6B1B224584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `FK_1F6B1B22B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `qualification`
--
ALTER TABLE `qualification`
  ADD CONSTRAINT `FK_B712F0CEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_B712F0CEAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`);

--
-- Omezení pro tabulku `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `FK_42C849553DA3F86F` FOREIGN KEY (`originator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_42C8495575FAE9DB` FOREIGN KEY (`reservation_item_id`) REFERENCES `reservation_item` (`id`),
  ADD CONSTRAINT `FK_42C849559395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`);

--
-- Omezení pro tabulku `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `FK_E19D9AD26B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `shift_bonus`
--
ALTER TABLE `shift_bonus`
  ADD CONSTRAINT `FK_947699246B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `shift_bonus_template`
--
ALTER TABLE `shift_bonus_template`
  ADD CONSTRAINT `FK_5F3D97D236095116` FOREIGN KEY (`shift_bonus_group_id`) REFERENCES `shift_bonus_group` (`id`);

--
-- Omezení pro tabulku `skill`
--
ALTER TABLE `skill`
  ADD CONSTRAINT `FK_5E3DE477C54C8C93` FOREIGN KEY (`type_id`) REFERENCES `skill_type` (`id`);

--
-- Omezení pro tabulku `skill_in_worker`
--
ALTER TABLE `skill_in_worker`
  ADD CONSTRAINT `FK_7BB42D1A5585C142` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`),
  ADD CONSTRAINT `FK_7BB42D1A6B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `skill_in_worker_position`
--
ALTER TABLE `skill_in_worker_position`
  ADD CONSTRAINT `FK_86AE884C5585C142` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`),
  ADD CONSTRAINT `FK_86AE884CDD842E46` FOREIGN KEY (`position_id`) REFERENCES `worker_position` (`id`);

--
-- Omezení pro tabulku `skill_in_worker_tender`
--
ALTER TABLE `skill_in_worker_tender`
  ADD CONSTRAINT `FK_9AD3C1D55585C142` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`),
  ADD CONSTRAINT `FK_9AD3C1D59245DE54` FOREIGN KEY (`tender_id`) REFERENCES `worker_tender` (`id`);

--
-- Omezení pro tabulku `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `FK_527EDB253DA3F86F` FOREIGN KEY (`originator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_527EDB25419863A8` FOREIGN KEY (`last_edited_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_527EDB254518D68D` FOREIGN KEY (`task_state_id`) REFERENCES `task_state` (`id`),
  ADD CONSTRAINT `FK_527EDB25E1501A05` FOREIGN KEY (`assigned_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `task_comment`
--
ALTER TABLE `task_comment`
  ADD CONSTRAINT `FK_8B9578868DB60186` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`),
  ADD CONSTRAINT `FK_8B957886A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `task_document`
--
ALTER TABLE `task_document`
  ADD CONSTRAINT `FK_98A9603A8DB60186` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`),
  ADD CONSTRAINT `FK_98A9603AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `task_log`
--
ALTER TABLE `task_log`
  ADD CONSTRAINT `FK_E0BD90428DB60186` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`),
  ADD CONSTRAINT `FK_E0BD9042A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `traffic`
--
ALTER TABLE `traffic`
  ADD CONSTRAINT `FK_5560263057A93DD8` FOREIGN KEY (`customer_ordered_id`) REFERENCES `customer_ordered` (`id`),
  ADD CONSTRAINT `FK_556026309395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`);

--
-- Omezení pro tabulku `translation`
--
ALTER TABLE `translation`
  ADD CONSTRAINT `FK_B469456FB213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649AE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`),
  ADD CONSTRAINT `FK_8D93D649FE54D947` FOREIGN KEY (`group_id`) REFERENCES `permission_group` (`id`);

--
-- Omezení pro tabulku `user_in_workplace`
--
ALTER TABLE `user_in_workplace`
  ADD CONSTRAINT `FK_32AD0A4F13B3DB11` FOREIGN KEY (`master_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_32AD0A4FAC25FB46` FOREIGN KEY (`workplace_id`) REFERENCES `workplace` (`id`);

--
-- Omezení pro tabulku `vacation`
--
ALTER TABLE `vacation`
  ADD CONSTRAINT `FK_E3DADF756B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_E3DADF75D4EE03F0` FOREIGN KEY (`vacation_type_id`) REFERENCES `vacation_type` (`id`);

--
-- Omezení pro tabulku `vacation_fund`
--
ALTER TABLE `vacation_fund`
  ADD CONSTRAINT `FK_3BFF98B26B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `visit`
--
ALTER TABLE `visit`
  ADD CONSTRAINT `FK_437EE9391A2A8B67` FOREIGN KEY (`visit_process_id`) REFERENCES `visit_process` (`id`),
  ADD CONSTRAINT `FK_437EE93957A93DD8` FOREIGN KEY (`customer_ordered_id`) REFERENCES `customer_ordered` (`id`),
  ADD CONSTRAINT `FK_437EE9395D83CC1` FOREIGN KEY (`state_id`) REFERENCES `visit_state` (`id`),
  ADD CONSTRAINT `FK_437EE9396BF700BD` FOREIGN KEY (`status_id`) REFERENCES `visit_status` (`id`),
  ADD CONSTRAINT `FK_437EE9399395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `FK_437EE939C6BCD788` FOREIGN KEY (`refrigerant_id`) REFERENCES `material` (`id`),
  ADD CONSTRAINT `FK_437EE939CC46C289` FOREIGN KEY (`traffic_id`) REFERENCES `traffic` (`id`);

--
-- Omezení pro tabulku `visit_document`
--
ALTER TABLE `visit_document`
  ADD CONSTRAINT `FK_DD3F546175FA0FF2` FOREIGN KEY (`visit_id`) REFERENCES `visit` (`id`),
  ADD CONSTRAINT `FK_DD3F5461A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `visit_log`
--
ALTER TABLE `visit_log`
  ADD CONSTRAINT `FK_B72D696975FA0FF2` FOREIGN KEY (`visit_id`) REFERENCES `visit` (`id`),
  ADD CONSTRAINT `FK_B72D6969A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `visit_process`
--
ALTER TABLE `visit_process`
  ADD CONSTRAINT `FK_D0941C5157A93DD8` FOREIGN KEY (`customer_ordered_id`) REFERENCES `customer_ordered` (`id`),
  ADD CONSTRAINT `FK_D0941C515D83CC1` FOREIGN KEY (`state_id`) REFERENCES `visit_process_state` (`id`),
  ADD CONSTRAINT `FK_D0941C519395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `FK_D0941C51CC46C289` FOREIGN KEY (`traffic_id`) REFERENCES `traffic` (`id`);

--
-- Omezení pro tabulku `web_setting_language`
--
ALTER TABLE `web_setting_language`
  ADD CONSTRAINT `FK_8C361F35B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`),
  ADD CONSTRAINT `FK_8C361F35EE35BD72` FOREIGN KEY (`setting_id`) REFERENCES `web_setting` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `worker`
--
ALTER TABLE `worker`
  ADD CONSTRAINT `FK_9FB2BF62140C3281` FOREIGN KEY (`worker_position_id`) REFERENCES `worker_position` (`id`),
  ADD CONSTRAINT `FK_9FB2BF6220126C59` FOREIGN KEY (`yes_worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_9FB2BF622A6A3139` FOREIGN KEY (`not_worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_9FB2BF6236C2AA5A` FOREIGN KEY (`production_line_change_id`) REFERENCES `production_line` (`id`),
  ADD CONSTRAINT `FK_9FB2BF62586EF89F` FOREIGN KEY (`production_line_id`) REFERENCES `production_line` (`id`),
  ADD CONSTRAINT `FK_9FB2BF62A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_9FB2BF62D6D4B5AD` FOREIGN KEY (`worker_employment_id`) REFERENCES `employment` (`id`);

--
-- Omezení pro tabulku `worker_in_plan`
--
ALTER TABLE `worker_in_plan`
  ADD CONSTRAINT `FK_C30E1F54140C3281` FOREIGN KEY (`worker_position_id`) REFERENCES `worker_position` (`id`),
  ADD CONSTRAINT `FK_C30E1F546B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_C30E1F54E899029B` FOREIGN KEY (`plan_id`) REFERENCES `shift_plan` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `worker_in_user`
--
ALTER TABLE `worker_in_user`
  ADD CONSTRAINT `FK_93C7926013B3DB11` FOREIGN KEY (`master_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_93C792606B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `worker_in_worker_tender`
--
ALTER TABLE `worker_in_worker_tender`
  ADD CONSTRAINT `FK_AE649DBE6B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_AE649DBE9245DE54` FOREIGN KEY (`tender_id`) REFERENCES `worker_tender` (`id`);

--
-- Omezení pro tabulku `worker_note`
--
ALTER TABLE `worker_note`
  ADD CONSTRAINT `FK_4784C456B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `worker_on_traffic`
--
ALTER TABLE `worker_on_traffic`
  ADD CONSTRAINT `FK_97F134EA6B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_97F134EACC46C289` FOREIGN KEY (`traffic_id`) REFERENCES `traffic` (`id`);

--
-- Omezení pro tabulku `worker_on_traffic_substitute`
--
ALTER TABLE `worker_on_traffic_substitute`
  ADD CONSTRAINT `FK_ACF210916B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_ACF21091CC46C289` FOREIGN KEY (`traffic_id`) REFERENCES `traffic` (`id`);

--
-- Omezení pro tabulku `worker_on_visit`
--
ALTER TABLE `worker_on_visit`
  ADD CONSTRAINT `FK_C7BD2C966B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`),
  ADD CONSTRAINT `FK_C7BD2C9675FA0FF2` FOREIGN KEY (`visit_id`) REFERENCES `visit` (`id`);

--
-- Omezení pro tabulku `worker_on_visit_process`
--
ALTER TABLE `worker_on_visit_process`
  ADD CONSTRAINT `FK_EEC623151A2A8B67` FOREIGN KEY (`visit_process_id`) REFERENCES `visit_process` (`id`),
  ADD CONSTRAINT `FK_EEC623156B20BA36` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`id`);

--
-- Omezení pro tabulku `worker_position_in_workplace`
--
ALTER TABLE `worker_position_in_workplace`
  ADD CONSTRAINT `FK_B2F64C8EAC25FB46` FOREIGN KEY (`workplace_id`) REFERENCES `workplace` (`id`),
  ADD CONSTRAINT `FK_B2F64C8EDD842E46` FOREIGN KEY (`position_id`) REFERENCES `worker_position` (`id`);

--
-- Omezení pro tabulku `worker_position_superiority`
--
ALTER TABLE `worker_position_superiority`
  ADD CONSTRAINT `FK_4861F7167E7EEAD1` FOREIGN KEY (`subordinate_position_id`) REFERENCES `worker_position` (`id`),
  ADD CONSTRAINT `FK_4861F716F35EE208` FOREIGN KEY (`superior_position_id`) REFERENCES `worker_position` (`id`);

--
-- Omezení pro tabulku `workplace_superiority`
--
ALTER TABLE `workplace_superiority`
  ADD CONSTRAINT `FK_5D9228DC3D6F4D1D` FOREIGN KEY (`superior_workplace_id`) REFERENCES `workplace` (`id`),
  ADD CONSTRAINT `FK_5D9228DCC2ED0765` FOREIGN KEY (`subordinate_workplace_id`) REFERENCES `workplace` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
