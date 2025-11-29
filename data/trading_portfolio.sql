-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Nov 29, 2025 alle 17:44
-- Versione del server: 10.5.29-MariaDB-0+deb11u1
-- Versione PHP: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trading_portfolio`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `allocation_by_asset_class`
--

CREATE TABLE `allocation_by_asset_class` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `asset_class` varchar(50) NOT NULL,
  `market_value` decimal(12,2) NOT NULL,
  `percentage` decimal(8,4) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asset class allocation percentages';

--
-- Dump dei dati per la tabella `allocation_by_asset_class`
--

INSERT INTO `allocation_by_asset_class` (`id`, `portfolio_id`, `asset_class`, `market_value`, `percentage`, `updated_at`) VALUES
(75, 1, 'ETF', 6452.27, 100.0000, '2025-11-29 16:32:10');

-- --------------------------------------------------------

--
-- Struttura della tabella `cron_logs`
--

CREATE TABLE `cron_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED DEFAULT NULL,
  `job_type` varchar(50) NOT NULL,
  `status` enum('SUCCESS','ERROR','RUNNING') NOT NULL,
  `message` text DEFAULT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cron job execution history';

-- --------------------------------------------------------

--
-- Struttura della tabella `dividend_payments`
--

CREATE TABLE `dividend_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `ticker` varchar(20) NOT NULL,
  `ex_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `amount_per_share` decimal(12,6) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `quantity` decimal(12,6) NOT NULL,
  `status` enum('FORECAST','RECEIVED') DEFAULT 'FORECAST',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dividend payments calendar';

--
-- Dump dei dati per la tabella `dividend_payments`
--

INSERT INTO `dividend_payments` (`id`, `portfolio_id`, `ticker`, `ex_date`, `payment_date`, `amount_per_share`, `total_amount`, `quantity`, `status`, `created_at`, `updated_at`) VALUES
(143, 1, 'TDIV.MI', '2025-12-03', '2026-01-02', 0.447500, 22.38, 50.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:15:20'),
(144, 1, 'TDIV.MI', '2026-03-04', '2026-04-03', 0.447500, 22.38, 50.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:15:20'),
(145, 1, 'TDIV.MI', '2026-06-03', '2026-07-03', 0.447500, 22.38, 50.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:15:20'),
(146, 1, 'TDIV.MI', '2026-09-02', '2026-10-02', 0.447500, 22.38, 50.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:15:20'),
(147, 1, 'VHYL.MI', '2025-12-20', '2026-01-19', 0.500400, 10.51, 21.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:32:10'),
(148, 1, 'VHYL.MI', '2026-03-23', '2026-04-22', 0.500400, 10.51, 21.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:32:10'),
(149, 1, 'VHYL.MI', '2026-06-24', '2026-07-24', 0.500400, 10.51, 21.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:32:10'),
(150, 1, 'VHYL.MI', '2026-09-25', '2026-10-25', 0.500400, 10.51, 21.000000, 'FORECAST', '2025-11-29 16:15:20', '2025-11-29 16:32:10');

-- --------------------------------------------------------

--
-- Struttura della tabella `holdings`
--

CREATE TABLE `holdings` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `ticker` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `asset_class` enum('ETF','Stock','Bond','Cash','Other') NOT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `quantity` decimal(12,6) NOT NULL,
  `avg_price` decimal(12,4) NOT NULL,
  `current_price` decimal(12,4) DEFAULT NULL,
  `dividend_yield` decimal(8,4) DEFAULT NULL,
  `annual_dividend` decimal(12,6) DEFAULT NULL,
  `dividend_frequency` varchar(20) DEFAULT NULL,
  `has_dividends` tinyint(1) NOT NULL DEFAULT 0,
  `total_dividends_5y` int(10) UNSIGNED DEFAULT NULL,
  `fifty_two_week_high` decimal(12,4) DEFAULT NULL,
  `fifty_two_week_low` decimal(12,4) DEFAULT NULL,
  `ytd_change_percent` decimal(8,4) DEFAULT NULL,
  `one_month_change_percent` decimal(8,4) DEFAULT NULL,
  `three_month_change_percent` decimal(8,4) DEFAULT NULL,
  `one_year_change_percent` decimal(8,4) DEFAULT NULL,
  `previous_close` decimal(12,4) DEFAULT NULL,
  `day_high` decimal(12,4) DEFAULT NULL,
  `day_low` decimal(12,4) DEFAULT NULL,
  `volume` bigint(20) UNSIGNED DEFAULT NULL,
  `price_source` varchar(50) DEFAULT NULL,
  `exchange` varchar(20) DEFAULT NULL,
  `first_trade_date` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Holdings with current and historical prices';

--
-- Dump dei dati per la tabella `holdings`
--

INSERT INTO `holdings` (`id`, `portfolio_id`, `ticker`, `name`, `asset_class`, `sector`, `quantity`, `avg_price`, `current_price`, `dividend_yield`, `annual_dividend`, `dividend_frequency`, `has_dividends`, `total_dividends_5y`, `fifty_two_week_high`, `fifty_two_week_low`, `ytd_change_percent`, `one_month_change_percent`, `three_month_change_percent`, `one_year_change_percent`, `previous_close`, `day_high`, `day_low`, `volume`, `price_source`, `exchange`, `first_trade_date`, `is_active`, `created_at`, `updated_at`) VALUES
(5, 1, 'SGLD.MI', 'Invesco Physical Gold ETC', 'ETF', NULL, 10.000000, 272.5530, 272.5530, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CSV Import', NULL, NULL, 1, '2025-11-29 16:22:16', '2025-11-29 16:22:16'),
(6, 1, 'VHYL.MI', 'Vanguard FTSE All-World High Div. Yield UCITS ETF Dis', 'ETF', NULL, 21.000000, 67.9400, 67.9400, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CSV Import', NULL, NULL, 1, '2025-11-29 16:22:16', '2025-11-29 16:22:16'),
(7, 1, 'TDIV.MI', 'VanEck Morn. Dev. Mkts Div Lead. UCITS ETF', 'ETF', NULL, 50.000000, 46.0000, 46.0000, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CSV Import', NULL, NULL, 1, '2025-11-29 16:22:16', '2025-11-29 16:22:16');

-- --------------------------------------------------------

--
-- Struttura della tabella `metadata_cache`
--

CREATE TABLE `metadata_cache` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `cache_key` varchar(100) NOT NULL,
  `cache_value` text NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache for computed metadata';

-- --------------------------------------------------------

--
-- Struttura della tabella `monthly_performance`
--

CREATE TABLE `monthly_performance` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `month_label` varchar(10) NOT NULL,
  `total_value` decimal(12,2) NOT NULL,
  `total_invested` decimal(12,2) NOT NULL,
  `total_gain` decimal(12,2) NOT NULL,
  `gain_pct` decimal(8,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Monthly performance aggregates';

--
-- Dump dei dati per la tabella `monthly_performance`
--

INSERT INTO `monthly_performance` (`id`, `portfolio_id`, `year`, `month`, `month_label`, `total_value`, `total_invested`, `total_gain`, `gain_pct`) VALUES
(1, 1, 2025, 11, 'Nov', 6452.27, 6452.27, 0.00, 0.0000);

-- --------------------------------------------------------

--
-- Struttura della tabella `portfolios`
--

CREATE TABLE `portfolios` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `base_currency` char(3) NOT NULL DEFAULT 'EUR',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Portfolio metadata';

--
-- Dump dei dati per la tabella `portfolios`
--

INSERT INTO `portfolios` (`id`, `name`, `owner`, `base_currency`, `created_at`, `updated_at`) VALUES
(1, 'Portafoglio ETF Personale', 'User', 'EUR', '2025-11-27 21:39:10', '2025-11-29 16:30:32');

-- --------------------------------------------------------

--
-- Struttura della tabella `snapshots`
--

CREATE TABLE `snapshots` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `snapshot_date` date NOT NULL,
  `total_invested` decimal(12,2) NOT NULL,
  `total_market_value` decimal(12,2) NOT NULL,
  `total_pnl` decimal(12,2) NOT NULL,
  `total_pnl_pct` decimal(8,4) NOT NULL,
  `total_dividends_received` decimal(12,2) DEFAULT 0.00,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Daily portfolio value snapshots';

--
-- Dump dei dati per la tabella `snapshots`
--

INSERT INTO `snapshots` (`id`, `portfolio_id`, `snapshot_date`, `total_invested`, `total_market_value`, `total_pnl`, `total_pnl_pct`, `total_dividends_received`, `metadata`, `created_at`) VALUES
(1, 1, '2025-11-27', 0.00, 0.00, 0.00, 0.0000, 0.00, NULL, '2025-11-27 21:39:10'),
(2, 1, '2025-11-28', 43273.90, 44587.20, 1313.30, 3.0349, 0.00, '{\"holdings_count\":4}', '2025-11-28 07:26:52'),
(3, 1, '2025-11-29', 6452.27, 6452.27, 0.00, 0.0000, 0.00, '{\"holdings_count\":3}', '2025-11-29 13:07:51');

-- --------------------------------------------------------

--
-- Struttura della tabella `snapshot_holdings`
--

CREATE TABLE `snapshot_holdings` (
  `id` int(10) UNSIGNED NOT NULL,
  `snapshot_id` int(10) UNSIGNED NOT NULL,
  `ticker` varchar(20) NOT NULL,
  `quantity` decimal(12,6) NOT NULL,
  `avg_price` decimal(12,4) NOT NULL,
  `current_price` decimal(12,4) NOT NULL,
  `market_value` decimal(12,2) NOT NULL,
  `invested` decimal(12,2) NOT NULL,
  `pnl` decimal(12,2) NOT NULL,
  `pnl_pct` decimal(8,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Holdings details within each snapshot';

--
-- Dump dei dati per la tabella `snapshot_holdings`
--

INSERT INTO `snapshot_holdings` (`id`, `snapshot_id`, `ticker`, `quantity`, `avg_price`, `current_price`, `market_value`, `invested`, `pnl`, `pnl_pct`) VALUES
(205, 2, 'TDIV.MI', 500.000000, 46.0000, 46.7150, 23357.50, 23000.00, 357.50, 1.5543),
(206, 2, 'VHYL.MI', 210.000000, 67.9400, 68.7600, 14439.60, 14267.40, 172.20, 1.2069),
(207, 2, 'SGLD.MI', 10.000000, 272.5500, 344.9600, 3449.60, 2725.50, 724.10, 26.5676),
(208, 2, 'SPYD.FRA', 50.000000, 65.6200, 66.8100, 3340.50, 3281.00, 59.50, 1.8135),
(288, 3, 'SGLD.MI', 10.000000, 272.5530, 272.5530, 2725.53, 2725.53, 0.00, 0.0000),
(289, 3, 'TDIV.MI', 50.000000, 46.0000, 46.0000, 2300.00, 2300.00, 0.00, 0.0000),
(290, 3, 'VHYL.MI', 21.000000, 67.9400, 67.9400, 1426.74, 1426.74, 0.00, 0.0000);

-- --------------------------------------------------------

--
-- Struttura della tabella `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `ticker` varchar(20) NOT NULL,
  `transaction_date` date NOT NULL,
  `type` enum('BUY','SELL','DIVIDEND','FEE','DEPOSIT','WITHDRAWAL') NOT NULL,
  `quantity` decimal(12,6) DEFAULT NULL,
  `price` decimal(12,4) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `fees` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Complete transaction history';

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `v_dividends_enriched`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `v_dividends_enriched` (
`id` int(10) unsigned
,`portfolio_id` int(10) unsigned
,`ticker` varchar(20)
,`ex_date` date
,`payment_date` date
,`amount_per_share` decimal(12,6)
,`total_amount` decimal(12,2)
,`quantity` decimal(12,6)
,`status` enum('FORECAST','RECEIVED')
,`created_at` timestamp
,`updated_at` timestamp
,`snapshot_date_used` date
,`snapshot_quantity` decimal(12,6)
,`quantity_at_ex_date` decimal(12,6)
,`paid_amount` decimal(24,12)
,`owned_on_snapshot` int(1)
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `v_holdings_enriched`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `v_holdings_enriched` (
`id` int(10) unsigned
,`portfolio_id` int(10) unsigned
,`ticker` varchar(20)
,`name` varchar(255)
,`asset_class` enum('ETF','Stock','Bond','Cash','Other')
,`quantity` decimal(12,6)
,`avg_price` decimal(12,4)
,`current_price` decimal(12,4)
,`price_source` varchar(50)
,`invested` decimal(24,10)
,`market_value` decimal(24,10)
,`pnl` decimal(25,10)
,`pnl_pct` decimal(24,8)
,`is_active` tinyint(1)
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `v_portfolio_metadata`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `v_portfolio_metadata` (
`portfolio_id` int(10) unsigned
,`name` varchar(255)
,`owner` varchar(255)
,`base_currency` char(3)
,`total_holdings` bigint(21)
,`total_invested` decimal(46,10)
,`total_market_value` decimal(46,10)
,`total_pnl` decimal(47,10)
,`total_pnl_pct` decimal(64,14)
,`total_dividends_received` decimal(34,2)
,`last_update` timestamp
);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `allocation_by_asset_class`
--
ALTER TABLE `allocation_by_asset_class`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_portfolio_class` (`portfolio_id`,`asset_class`);

--
-- Indici per le tabelle `cron_logs`
--
ALTER TABLE `cron_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portfolio_id` (`portfolio_id`),
  ADD KEY `idx_job_type` (`job_type`),
  ADD KEY `idx_executed` (`executed_at`);

--
-- Indici per le tabelle `dividend_payments`
--
ALTER TABLE `dividend_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_portfolio_date` (`portfolio_id`,`ex_date`),
  ADD KEY `idx_ticker` (`ticker`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indici per le tabelle `holdings`
--
ALTER TABLE `holdings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_portfolio_ticker` (`portfolio_id`,`ticker`),
  ADD KEY `idx_ticker` (`ticker`),
  ADD KEY `idx_asset_class` (`asset_class`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indici per le tabelle `metadata_cache`
--
ALTER TABLE `metadata_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_portfolio_key` (`portfolio_id`,`cache_key`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indici per le tabelle `monthly_performance`
--
ALTER TABLE `monthly_performance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_portfolio_month` (`portfolio_id`,`year`,`month`),
  ADD KEY `idx_portfolio_year` (`portfolio_id`,`year`,`month`);

--
-- Indici per le tabelle `portfolios`
--
ALTER TABLE `portfolios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- Indici per le tabelle `snapshots`
--
ALTER TABLE `snapshots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_portfolio_snapshot` (`portfolio_id`,`snapshot_date`),
  ADD KEY `idx_date` (`snapshot_date`),
  ADD KEY `idx_portfolio_date` (`portfolio_id`,`snapshot_date`);

--
-- Indici per le tabelle `snapshot_holdings`
--
ALTER TABLE `snapshot_holdings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_snapshot` (`snapshot_id`),
  ADD KEY `idx_ticker` (`ticker`);

--
-- Indici per le tabelle `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_portfolio_date` (`portfolio_id`,`transaction_date`),
  ADD KEY `idx_ticker` (`ticker`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_date` (`transaction_date`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `allocation_by_asset_class`
--
ALTER TABLE `allocation_by_asset_class`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT per la tabella `cron_logs`
--
ALTER TABLE `cron_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `dividend_payments`
--
ALTER TABLE `dividend_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT per la tabella `holdings`
--
ALTER TABLE `holdings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `metadata_cache`
--
ALTER TABLE `metadata_cache`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `monthly_performance`
--
ALTER TABLE `monthly_performance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT per la tabella `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `snapshots`
--
ALTER TABLE `snapshots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `snapshot_holdings`
--
ALTER TABLE `snapshot_holdings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;

--
-- AUTO_INCREMENT per la tabella `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Struttura per vista `v_dividends_enriched`
--
DROP TABLE IF EXISTS `v_dividends_enriched`;

CREATE ALGORITHM=UNDEFINED DEFINER=`poRtUsR25`@`%` SQL SECURITY DEFINER VIEW `v_dividends_enriched`  AS WITH ranked AS (SELECT `dp`.`id` AS `dp_id`, `dp`.`portfolio_id` AS `portfolio_id`, `dp`.`ticker` AS `ticker`, `dp`.`ex_date` AS `ex_date`, `sh`.`quantity` AS `quantity`, `s`.`snapshot_date` AS `snapshot_date`, row_number() over ( partition by `dp`.`id` order by `s`.`snapshot_date` desc) AS `rn` FROM ((`dividend_payments` `dp` join `snapshots` `s` on(`s`.`portfolio_id` = `dp`.`portfolio_id` and `s`.`snapshot_date` <= `dp`.`ex_date`)) join `snapshot_holdings` `sh` on(`sh`.`snapshot_id` = `s`.`id` and `sh`.`ticker` = `dp`.`ticker`)))  SELECT `dp`.`id` AS `id`, `dp`.`portfolio_id` AS `portfolio_id`, `dp`.`ticker` AS `ticker`, `dp`.`ex_date` AS `ex_date`, `dp`.`payment_date` AS `payment_date`, `dp`.`amount_per_share` AS `amount_per_share`, `dp`.`total_amount` AS `total_amount`, `dp`.`quantity` AS `quantity`, `dp`.`status` AS `status`, `dp`.`created_at` AS `created_at`, `dp`.`updated_at` AS `updated_at`, `r`.`snapshot_date` AS `snapshot_date_used`, CASE WHEN `r`.`rn` = 1 THEN `r`.`quantity` ELSE NULL END AS `snapshot_quantity`, coalesce(case when `r`.`rn` = 1 then `r`.`quantity` end,`dp`.`quantity`) AS `quantity_at_ex_date`, `dp`.`amount_per_share`* coalesce(case when `r`.`rn` = 1 then `r`.`quantity` end,`dp`.`quantity`) AS `paid_amount`, CASE WHEN `r`.`rn` = 1 THEN 1 ELSE 0 END AS `owned_on_snapshot` FROM (`dividend_payments` `dp` left join `ranked` `r` on(`r`.`dp_id` = `dp`.`id` and `r`.`rn` = 1)))  ;

-- --------------------------------------------------------

--
-- Struttura per vista `v_holdings_enriched`
--
DROP TABLE IF EXISTS `v_holdings_enriched`;

CREATE ALGORITHM=UNDEFINED DEFINER=`poRtUsR25`@`%` SQL SECURITY DEFINER VIEW `v_holdings_enriched`  AS SELECT `h`.`id` AS `id`, `h`.`portfolio_id` AS `portfolio_id`, `h`.`ticker` AS `ticker`, `h`.`name` AS `name`, `h`.`asset_class` AS `asset_class`, `h`.`quantity` AS `quantity`, `h`.`avg_price` AS `avg_price`, `h`.`current_price` AS `current_price`, `h`.`price_source` AS `price_source`, `h`.`quantity`* `h`.`avg_price` AS `invested`, `h`.`quantity`* coalesce(`h`.`current_price`,`h`.`avg_price`) AS `market_value`, `h`.`quantity`* coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`quantity` * `h`.`avg_price` AS `pnl`, CASE WHEN `h`.`avg_price` > 0 THEN (coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`avg_price`) / `h`.`avg_price` * 100 ELSE 0 END AS `pnl_pct`, `h`.`is_active` AS `is_active`, `h`.`updated_at` AS `updated_at` FROM `holdings` AS `h` WHERE `h`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Struttura per vista `v_portfolio_metadata`
--
DROP TABLE IF EXISTS `v_portfolio_metadata`;

CREATE ALGORITHM=UNDEFINED DEFINER=`poRtUsR25`@`%` SQL SECURITY DEFINER VIEW `v_portfolio_metadata`  AS SELECT `p`.`id` AS `portfolio_id`, `p`.`name` AS `name`, `p`.`owner` AS `owner`, `p`.`base_currency` AS `base_currency`, count(distinct `h`.`ticker`) AS `total_holdings`, coalesce(sum(`h`.`quantity` * `h`.`avg_price`),0) AS `total_invested`, coalesce(sum(`h`.`quantity` * coalesce(`h`.`current_price`,`h`.`avg_price`)),0) AS `total_market_value`, coalesce(sum(`h`.`quantity` * coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`quantity` * `h`.`avg_price`),0) AS `total_pnl`, CASE WHEN coalesce(sum(`h`.`quantity` * `h`.`avg_price`),0) > 0 THEN (coalesce(sum(`h`.`quantity` * coalesce(`h`.`current_price`,`h`.`avg_price`)),0) - coalesce(sum(`h`.`quantity` * `h`.`avg_price`),0)) / sum(`h`.`quantity` * `h`.`avg_price`) * 100 ELSE 0 END AS `total_pnl_pct`, coalesce((select sum(`dp`.`total_amount`) from `dividend_payments` `dp` where `dp`.`portfolio_id` = `p`.`id` and `dp`.`status` = 'RECEIVED'),0) AS `total_dividends_received`, `p`.`updated_at` AS `last_update` FROM (`portfolios` `p` left join `holdings` `h` on(`h`.`portfolio_id` = `p`.`id` and `h`.`is_active` = 1)) GROUP BY `p`.`id`, `p`.`name`, `p`.`owner`, `p`.`base_currency`, `p`.`updated_at` ;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `allocation_by_asset_class`
--
ALTER TABLE `allocation_by_asset_class`
  ADD CONSTRAINT `allocation_by_asset_class_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `cron_logs`
--
ALTER TABLE `cron_logs`
  ADD CONSTRAINT `cron_logs_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `dividend_payments`
--
ALTER TABLE `dividend_payments`
  ADD CONSTRAINT `dividend_payments_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `holdings`
--
ALTER TABLE `holdings`
  ADD CONSTRAINT `holdings_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `metadata_cache`
--
ALTER TABLE `metadata_cache`
  ADD CONSTRAINT `metadata_cache_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `monthly_performance`
--
ALTER TABLE `monthly_performance`
  ADD CONSTRAINT `monthly_performance_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `snapshots`
--
ALTER TABLE `snapshots`
  ADD CONSTRAINT `snapshots_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `snapshot_holdings`
--
ALTER TABLE `snapshot_holdings`
  ADD CONSTRAINT `snapshot_holdings_ibfk_1` FOREIGN KEY (`snapshot_id`) REFERENCES `snapshots` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
