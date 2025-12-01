-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Dic 01, 2025 alle 16:17
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
(135, 1, '', 11173.55, 100.0000, '2025-12-01 13:56:04');

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
  `isin` varchar(20) DEFAULT NULL,
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
  `sma9` decimal(12,4) DEFAULT NULL,
  `sma21` decimal(12,4) DEFAULT NULL,
  `sma50` decimal(12,4) DEFAULT NULL,
  `sma200` decimal(12,4) DEFAULT NULL,
  `ema9` decimal(12,4) DEFAULT NULL,
  `ema21` decimal(12,4) DEFAULT NULL,
  `ema50` decimal(12,4) DEFAULT NULL,
  `ema200` decimal(12,4) DEFAULT NULL,
  `rsi14` decimal(6,2) DEFAULT NULL,
  `macd_value` decimal(12,4) DEFAULT NULL,
  `macd_signal` decimal(12,4) DEFAULT NULL,
  `macd_hist` decimal(12,4) DEFAULT NULL,
  `atr14` decimal(12,4) DEFAULT NULL,
  `atr14_pct` decimal(8,4) DEFAULT NULL,
  `hist_vol_30d` decimal(8,4) DEFAULT NULL,
  `hist_vol_90d` decimal(8,4) DEFAULT NULL,
  `vol_avg_20d` bigint(20) UNSIGNED DEFAULT NULL,
  `vol_ratio_current_20d` decimal(8,4) DEFAULT NULL,
  `range_1m_min` decimal(12,4) DEFAULT NULL,
  `range_1m_max` decimal(12,4) DEFAULT NULL,
  `range_1m_percentile` decimal(8,4) DEFAULT NULL,
  `range_3m_min` decimal(12,4) DEFAULT NULL,
  `range_3m_max` decimal(12,4) DEFAULT NULL,
  `range_3m_percentile` decimal(8,4) DEFAULT NULL,
  `range_6m_min` decimal(12,4) DEFAULT NULL,
  `range_6m_max` decimal(12,4) DEFAULT NULL,
  `range_6m_percentile` decimal(8,4) DEFAULT NULL,
  `range_1y_min` decimal(12,4) DEFAULT NULL,
  `range_1y_max` decimal(12,4) DEFAULT NULL,
  `range_1y_percentile` decimal(8,4) DEFAULT NULL,
  `fib_low` decimal(12,4) DEFAULT NULL,
  `fib_high` decimal(12,4) DEFAULT NULL,
  `fib_23_6` decimal(12,4) DEFAULT NULL,
  `fib_38_2` decimal(12,4) DEFAULT NULL,
  `fib_50_0` decimal(12,4) DEFAULT NULL,
  `fib_61_8` decimal(12,4) DEFAULT NULL,
  `fib_78_6` decimal(12,4) DEFAULT NULL,
  `fib_23_6_dist_pct` decimal(8,4) DEFAULT NULL,
  `fib_38_2_dist_pct` decimal(8,4) DEFAULT NULL,
  `fib_50_0_dist_pct` decimal(8,4) DEFAULT NULL,
  `fib_61_8_dist_pct` decimal(8,4) DEFAULT NULL,
  `fib_78_6_dist_pct` decimal(8,4) DEFAULT NULL,
  `bb_middle` decimal(12,4) DEFAULT NULL,
  `bb_upper` decimal(12,4) DEFAULT NULL,
  `bb_lower` decimal(12,4) DEFAULT NULL,
  `bb_width_pct` decimal(8,4) DEFAULT NULL,
  `bb_percent_b` decimal(8,4) DEFAULT NULL,
  `technical_as_of` date DEFAULT NULL,
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

INSERT INTO `holdings` (`id`, `portfolio_id`, `ticker`, `isin`, `name`, `asset_class`, `sector`, `quantity`, `avg_price`, `current_price`, `dividend_yield`, `annual_dividend`, `dividend_frequency`, `has_dividends`, `total_dividends_5y`, `fifty_two_week_high`, `fifty_two_week_low`, `ytd_change_percent`, `one_month_change_percent`, `three_month_change_percent`, `one_year_change_percent`, `sma9`, `sma21`, `sma50`, `sma200`, `ema9`, `ema21`, `ema50`, `ema200`, `rsi14`, `macd_value`, `macd_signal`, `macd_hist`, `atr14`, `atr14_pct`, `hist_vol_30d`, `hist_vol_90d`, `vol_avg_20d`, `vol_ratio_current_20d`, `range_1m_min`, `range_1m_max`, `range_1m_percentile`, `range_3m_min`, `range_3m_max`, `range_3m_percentile`, `range_6m_min`, `range_6m_max`, `range_6m_percentile`, `range_1y_min`, `range_1y_max`, `range_1y_percentile`, `fib_low`, `fib_high`, `fib_23_6`, `fib_38_2`, `fib_50_0`, `fib_61_8`, `fib_78_6`, `fib_23_6_dist_pct`, `fib_38_2_dist_pct`, `fib_50_0_dist_pct`, `fib_61_8_dist_pct`, `fib_78_6_dist_pct`, `bb_middle`, `bb_upper`, `bb_lower`, `bb_width_pct`, `bb_percent_b`, `technical_as_of`, `previous_close`, `day_high`, `day_low`, `volume`, `price_source`, `exchange`, `first_trade_date`, `is_active`, `created_at`, `updated_at`) VALUES
(10, 1, 'SGLD.MI', 'IE00B579F325', 'Invesco Physical Gold ETC', '', 'Gold', 10.000000, 272.5500, 352.1400, 0.0000, 0.000000, 'None', 0, 0, 359.6700, 239.2600, 41.1000, 5.4700, 19.7900, 45.0800, 344.4289, 340.9462, 327.9298, 290.4607, 345.4428, 339.2448, 327.1959, 293.2637, 56.09, 6.9021, 6.5988, 0.3033, 5.1329, 1.4600, 25.6400, 17.9900, 101261, 0.5100, 331.9500, 352.1400, 100.0000, 293.2000, 359.1800, 89.3300, 268.9700, 359.1800, 92.2000, 240.4200, 359.1800, 94.0700, 267.4500, 359.6700, 337.9061, 324.4420, 313.5600, 302.6781, 287.1851, 4.0400, 7.8700, 10.9600, 14.0500, 18.4500, 341.2995, 352.4625, 330.1365, 6.5400, 0.9856, '2025-12-01', 145.4900, 352.9800, 351.4400, 51151, 'YahooFinance_v8', 'MIL', 1417161600, 1, '2025-11-30 08:10:13', '2025-12-01 11:30:03'),
(11, 1, 'VHYL.MI', 'IE00B8GKDB10', 'Vanguard FTSE All-World High Div. Yield UCITS ETF Dis', '', 'Global', 21.000000, 67.9400, 68.7500, 2.9100, 2.001600, 'Quarterly', 1, 20, 69.5000, 55.7600, 6.0300, 1.3600, 4.5600, 3.7000, 68.2633, 68.2219, 66.3060, 64.8208, 68.4493, 67.7110, 66.7080, 65.1497, 52.91, 0.8202, 0.8199, 0.0004, 0.5443, 0.7900, 8.8200, 8.5500, 19569, 0.7400, 67.3400, 69.3800, 69.1200, 65.7000, 69.3800, 82.8800, 62.6700, 69.3800, 90.6100, 56.4400, 69.3800, 95.1300, 62.6400, 69.5000, 67.8810, 66.8795, 66.0700, 65.2605, 64.1080, 1.2600, 2.7200, 3.9000, 5.0800, 6.7500, 68.2415, 69.3822, 67.1008, 3.3400, 0.7229, '2025-12-01', 45.5650, 68.9300, 68.7500, 14552, 'YahooFinance_v8', 'MIL', 1547798400, 1, '2025-11-30 08:10:13', '2025-12-01 11:30:03'),
(12, 1, 'TDIV.MI', 'NL0011683594', 'VanEck Morn. Dev. Mkts Div Lead. UCITS ETF', '', 'Mixed', 50.000000, 46.0000, 46.6850, 3.8300, 1.790000, 'Quarterly', 1, 20, 47.0000, 36.7950, 12.6600, 3.5900, 6.8800, 13.8200, 46.2822, 46.0431, 44.3369, 43.3557, 46.4088, 45.7140, 44.7749, 43.2474, 54.17, 0.7722, 0.7715, 0.0007, 0.4286, 0.9200, 8.6100, 9.1800, 43064, 0.2200, 45.0650, 46.9000, 88.2800, 43.6800, 46.9000, 93.3200, 41.7650, 46.9000, 95.8100, 37.6800, 46.9000, 97.6700, 41.6900, 47.0000, 45.7468, 44.9716, 44.3450, 43.7184, 42.8263, 2.0100, 3.6700, 5.0100, 6.3500, 8.2700, 46.0920, 47.2445, 44.9395, 5.0000, 0.7573, '2025-12-01', 25.7250, 46.8150, 46.6850, 9425, 'YahooFinance_v8', 'MIL', 1556002800, 1, '2025-11-30 08:10:13', '2025-12-01 11:30:03'),
(15, 1, 'SWDA.MI', 'IE00B4L5Y983', 'iShares Core MSCI World UCITS ETF USD (Acc)', '', 'Global', 35.000000, 111.5400, 110.6900, 0.0000, 0.000000, 'None', 0, 0, 112.5500, 82.7000, 5.1200, -1.1300, 6.0200, 4.7100, 109.9478, 110.3219, 107.4160, 102.0825, 110.3358, 109.3478, 107.7121, 103.5444, 52.21, 1.1169, 1.1213, -0.0043, 1.3957, 1.2600, 13.8600, 11.8700, 198705, 0.2700, 108.1300, 111.9600, 66.8400, 104.4000, 111.9600, 83.2000, 98.5700, 111.9600, 90.5200, 85.2900, 111.9600, 95.2400, 98.4400, 112.5500, 109.2200, 107.1600, 105.4950, 103.8300, 101.4595, 1.3300, 3.1900, 4.6900, 6.2000, 8.3400, 110.2405, 112.5547, 107.9263, 4.2000, 0.5971, '2025-12-01', 59.1400, 110.9900, 110.6500, 53041, 'YahooFinance_v8', 'MIL', 1253862000, 1, '2025-11-30 08:14:43', '2025-12-01 13:56:04');

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
(1, 1, 2025, 11, 'Nov', 46285.60, 45491.24, 794.36, 1.7462),
(114, 1, 2025, 12, 'Dec', 11173.55, 10356.14, 817.41, 7.8930);

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
(1, 'Portafoglio ETF Personale', 'User', 'EUR', '2025-11-27 21:39:10', '2025-12-01 13:56:04');

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
(3, 1, '2025-11-29', 6452.27, 6452.27, 0.00, 0.0000, 0.00, '{\"holdings_count\":3}', '2025-11-29 13:07:51'),
(4, 1, '2025-11-30', 45491.24, 46285.60, 794.36, 1.7462, 0.00, '{\"holdings_count\":4}', '2025-11-30 08:00:40'),
(5, 1, '2025-12-01', 10356.14, 11173.55, 817.41, 7.8930, 0.00, '{\"holdings_count\":4}', '2025-12-01 06:00:02');

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
(290, 3, 'VHYL.MI', 21.000000, 67.9400, 67.9400, 1426.74, 1426.74, 0.00, 0.0000),
(451, 4, 'SWDA.MI', 350.000000, 111.5400, 111.4300, 39000.50, 39039.00, -38.50, -0.0986),
(452, 4, 'SGLD.MI', 10.000000, 272.5500, 349.4800, 3494.80, 2725.50, 769.30, 28.2260),
(453, 4, 'TDIV.MI', 50.000000, 46.0000, 46.8050, 2340.25, 2300.00, 40.25, 1.7500),
(454, 4, 'VHYL.MI', 21.000000, 67.9400, 69.0500, 1450.05, 1426.74, 23.31, 1.6338),
(515, 5, 'SWDA.MI', 35.000000, 111.5400, 110.6900, 3874.15, 3903.90, -29.75, -0.7621),
(516, 5, 'SGLD.MI', 10.000000, 272.5500, 352.1400, 3521.40, 2725.50, 795.90, 29.2020),
(517, 5, 'TDIV.MI', 50.000000, 46.0000, 46.6850, 2334.25, 2300.00, 34.25, 1.4891),
(518, 5, 'VHYL.MI', 21.000000, 67.9400, 68.7500, 1443.75, 1426.74, 17.01, 1.1922);

-- --------------------------------------------------------

--
-- Struttura della tabella `technical_insights`
--

CREATE TABLE `technical_insights` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `isin` varchar(32) DEFAULT NULL,
  `scope` enum('portfolio','instrument') NOT NULL DEFAULT 'instrument',
  `model` varchar(100) NOT NULL,
  `generated_at` datetime NOT NULL,
  `raw_input_snapshot` mediumtext DEFAULT NULL,
  `insight_json` mediumtext DEFAULT NULL,
  `insight_text` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI technical insights per portafoglio e strumenti';

--
-- Dump dei dati per la tabella `technical_insights`
--

INSERT INTO `technical_insights` (`id`, `portfolio_id`, `isin`, `scope`, `model`, `generated_at`, `raw_input_snapshot`, `insight_json`, `insight_text`, `created_at`) VALUES
(4, 1, NULL, 'portfolio', 'gpt-5-mini', '2025-12-01 15:16:26', '{\"portfolio_id\":1,\"as_of\":\"2025-12-01T14:18:21.250Z\",\"holdings\":[{\"ticker\":\"SWDA.MI\",\"isin\":null,\"name\":\"iShares Core MSCI World UCITS ETF USD (Acc)\",\"sector\":\"Global\",\"asset_class\":null,\"price\":110.69,\"indicators\":{\"ema9\":110.3358,\"ema21\":109.3478,\"ema50\":107.7121,\"ema200\":103.5444,\"rsi14\":52.21,\"atr14\":null,\"atr14_pct\":1.26,\"hist_vol_30d\":13.86,\"hist_vol_90d\":null,\"range_1m_percentile\":null,\"range_3m_percentile\":null,\"range_1y_percentile\":95.24,\"bb_percent_b\":0.5971,\"macd_value\":null,\"macd_signal\":null,\"macd_hist\":null,\"ytd_change_percent\":5.12,\"one_month_change_percent\":-1.13,\"three_month_change_percent\":6.02,\"one_year_change_percent\":4.71,\"fifty_two_week_high\":112.55,\"fifty_two_week_low\":82.7},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"SGLD.MI\",\"isin\":null,\"name\":\"Invesco Physical Gold ETC\",\"sector\":\"Gold\",\"asset_class\":null,\"price\":352.14,\"indicators\":{\"ema9\":345.4428,\"ema21\":339.2448,\"ema50\":327.1959,\"ema200\":293.2637,\"rsi14\":56.09,\"atr14\":null,\"atr14_pct\":1.46,\"hist_vol_30d\":25.64,\"hist_vol_90d\":null,\"range_1m_percentile\":null,\"range_3m_percentile\":null,\"range_1y_percentile\":94.07,\"bb_percent_b\":0.9856,\"macd_value\":null,\"macd_signal\":null,\"macd_hist\":null,\"ytd_change_percent\":41.1,\"one_month_change_percent\":5.47,\"three_month_change_percent\":19.79,\"one_year_change_percent\":45.08,\"fifty_two_week_high\":359.67,\"fifty_two_week_low\":239.26},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"TDIV.MI\",\"isin\":null,\"name\":\"VanEck Morn. Dev. Mkts Div Lead. UCITS ETF\",\"sector\":\"Mixed\",\"asset_class\":null,\"price\":46.685,\"indicators\":{\"ema9\":46.4088,\"ema21\":45.714,\"ema50\":44.7749,\"ema200\":43.2474,\"rsi14\":54.17,\"atr14\":null,\"atr14_pct\":0.92,\"hist_vol_30d\":8.61,\"hist_vol_90d\":null,\"range_1m_percentile\":null,\"range_3m_percentile\":null,\"range_1y_percentile\":97.67,\"bb_percent_b\":0.7573,\"macd_value\":null,\"macd_signal\":null,\"macd_hist\":null,\"ytd_change_percent\":12.66,\"one_month_change_percent\":3.59,\"three_month_change_percent\":6.88,\"one_year_change_percent\":13.82,\"fifty_two_week_high\":47,\"fifty_two_week_low\":36.795},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"VHYL.MI\",\"isin\":null,\"name\":\"Vanguard FTSE All-World High Div. Yield UCITS ETF Dis\",\"sector\":\"Global\",\"asset_class\":null,\"price\":68.75,\"indicators\":{\"ema9\":68.4493,\"ema21\":67.711,\"ema50\":66.708,\"ema200\":65.1497,\"rsi14\":52.91,\"atr14\":null,\"atr14_pct\":0.79,\"hist_vol_30d\":8.82,\"hist_vol_90d\":null,\"range_1m_percentile\":null,\"range_3m_percentile\":null,\"range_1y_percentile\":95.13,\"bb_percent_b\":0.7229,\"macd_value\":null,\"macd_signal\":null,\"macd_hist\":null,\"ytd_change_percent\":6.03,\"one_month_change_percent\":1.36,\"three_month_change_percent\":4.56,\"one_year_change_percent\":3.7,\"fifty_two_week_high\":69.5,\"fifty_two_week_low\":55.76},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}}]}', '{\"scores\":{\"health_score\":78,\"risk_score\":42,\"diversification_score\":62,\"momentum_score\":70,\"volatility_score\":46,\"extension_score\":66},\"trend\":\"bullish\",\"risk\":\"medium\",\"volatility_comment\":\"Volatilità complessiva moderata; oro (SGLD) mostra volatilità significativamente più alta rispetto agli ETF azionari.\",\"diversification_comment\":\"Diversificazione funzionale tra azioni globali, high-dividend, mercati sviluppati\\/emergenti e oro; leggero sovrappeso su esposizione azionaria globale.\",\"notes\":\"Posizionamento vicino ai massimi a 52 settimane aumenta il rischio di ritracciamenti; supporti su EMA21\\/50 e livelli storici da monitorare.\"}', 'Portafoglio con trend generale positivo e momentum moderato; volatilità complessiva contenuta ad eccezione dell\'oro. Tutti gli strumenti sono vicino ai massimi a 52 settimane: monitorare possibili inversioni di momentum e aumento della volatilità.', '2025-12-01 15:16:28');

-- --------------------------------------------------------

--
-- Struttura della tabella `technical_snapshots`
--

CREATE TABLE `technical_snapshots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `isin` varchar(32) NOT NULL,
  `snapshot_date` date NOT NULL,
  `price` decimal(12,4) DEFAULT NULL,
  `rsi14` decimal(6,2) DEFAULT NULL,
  `macd_value` decimal(12,4) DEFAULT NULL,
  `macd_signal` decimal(12,4) DEFAULT NULL,
  `hist_vol_30d` decimal(8,4) DEFAULT NULL,
  `atr14_pct` decimal(8,4) DEFAULT NULL,
  `range_1y_percentile` decimal(8,4) DEFAULT NULL,
  `bb_percent_b` decimal(8,4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `technical_snapshots`
--

INSERT INTO `technical_snapshots` (`id`, `portfolio_id`, `isin`, `snapshot_date`, `price`, `rsi14`, `macd_value`, `macd_signal`, `hist_vol_30d`, `atr14_pct`, `range_1y_percentile`, `bb_percent_b`, `created_at`) VALUES
(1, 1, 'IE00B579F325', '2025-11-30', 349.4800, 55.23, 6.6077, 6.5230, 25.5600, 1.5100, 91.8300, 0.9359, '2025-11-30 20:09:37'),
(2, 1, 'IE00B4L5Y983', '2025-11-30', 111.4300, 53.11, 1.1461, 1.1223, 14.0700, 1.2500, 98.0100, 0.7322, '2025-11-30 20:09:37'),
(3, 1, 'NL0011683594', '2025-11-30', 46.8050, 54.57, 0.7890, 0.7713, 8.7900, 0.9900, 98.9700, 0.8305, '2025-11-30 20:09:37'),
(4, 1, 'IE00B8GKDB10', '2025-11-30', 69.0500, 53.55, 0.8373, 0.8198, 8.9600, 0.8200, 97.4500, 0.8784, '2025-11-30 20:09:37'),
(5, 1, 'IE00B579F325', '2025-12-01', 352.1400, 56.09, 6.9021, 6.5988, 25.6400, 1.4600, 94.0700, 0.9856, '2025-12-01 10:31:48'),
(6, 1, 'IE00B4L5Y983', '2025-12-01', 110.6900, 52.21, 1.1169, 1.1213, 13.8600, 1.2600, 95.2400, 0.5971, '2025-12-01 10:31:48'),
(7, 1, 'NL0011683594', '2025-12-01', 46.6850, 54.17, 0.7722, 0.7715, 8.6100, 0.9200, 97.6700, 0.7573, '2025-12-01 10:31:48'),
(8, 1, 'IE00B8GKDB10', '2025-12-01', 68.7500, 52.91, 0.8202, 0.8199, 8.8200, 0.7900, 95.1300, 0.7229, '2025-12-01 10:31:48');

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

--
-- Dump dei dati per la tabella `transactions`
--

INSERT INTO `transactions` (`id`, `portfolio_id`, `ticker`, `transaction_date`, `type`, `quantity`, `price`, `amount`, `fees`, `notes`, `created_at`) VALUES
(1, 1, 'SGLD.MI', '2025-11-30', 'SELL', 0.000000, 272.5500, 0.00, 0.00, 'Update holding', '2025-11-30 15:18:11'),
(2, 1, 'SGLD.MI', '2025-11-30', 'SELL', 90.000000, 272.5500, 24529.50, 0.00, 'Update holding', '2025-11-30 15:24:59'),
(3, 1, 'SWDA.MI', '2025-11-30', 'BUY', 315.000000, 111.5400, 35135.10, 0.00, 'Update holding', '2025-11-30 15:25:21'),
(4, 1, 'SGLD.MI', '2025-11-30', 'BUY', 90.000000, 272.5500, 24529.50, 0.00, 'Update holding', '2025-11-30 15:27:00'),
(5, 1, 'SGLD.MI', '2025-11-30', 'BUY', 900.000000, 272.5500, 245295.00, 0.00, 'Update holding', '2025-11-30 15:29:48'),
(6, 1, 'SGLD.MI', '2025-11-30', 'SELL', 990.000000, 272.5500, 269824.50, 0.00, 'Update holding', '2025-11-30 15:30:56'),
(7, 1, 'SWDA.MI', '2025-12-01', 'BUY', 3150.000000, 111.5400, 351351.00, 0.00, 'Update holding', '2025-12-01 08:21:02'),
(8, 1, 'SWDA.MI', '2025-12-01', 'SELL', 3465.000000, 111.5400, 386486.10, 0.00, 'Update holding', '2025-12-01 13:56:04');

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
,`sector` varchar(100)
,`quantity` decimal(12,6)
,`avg_price` decimal(12,4)
,`current_price` decimal(12,4)
,`previous_close` decimal(12,4)
,`invested` decimal(24,10)
,`market_value` decimal(24,10)
,`pnl` decimal(25,10)
,`pnl_pct` decimal(24,8)
,`fifty_two_week_high` decimal(12,4)
,`fifty_two_week_low` decimal(12,4)
,`day_high` decimal(12,4)
,`day_low` decimal(12,4)
,`ytd_change_percent` decimal(8,4)
,`one_month_change_percent` decimal(8,4)
,`three_month_change_percent` decimal(8,4)
,`one_year_change_percent` decimal(8,4)
,`dividend_yield` decimal(8,4)
,`annual_dividend` decimal(12,6)
,`dividend_frequency` varchar(20)
,`has_dividends` tinyint(1)
,`total_dividends_5y` int(10) unsigned
,`volume` bigint(20) unsigned
,`exchange` varchar(20)
,`first_trade_date` bigint(20) unsigned
,`price_source` varchar(50)
,`is_active` tinyint(1)
,`created_at` timestamp
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
  ADD UNIQUE KEY `unique_portfolio_isin` (`portfolio_id`,`isin`),
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
-- Indici per le tabelle `technical_insights`
--
ALTER TABLE `technical_insights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_portfolio_scope_date` (`portfolio_id`,`scope`,`generated_at`),
  ADD KEY `idx_isin_date` (`isin`,`generated_at`);

--
-- Indici per le tabelle `technical_snapshots`
--
ALTER TABLE `technical_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_snapshot` (`portfolio_id`,`isin`,`snapshot_date`),
  ADD KEY `idx_isin_date` (`isin`,`snapshot_date`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `metadata_cache`
--
ALTER TABLE `metadata_cache`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `monthly_performance`
--
ALTER TABLE `monthly_performance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT per la tabella `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `snapshots`
--
ALTER TABLE `snapshots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `snapshot_holdings`
--
ALTER TABLE `snapshot_holdings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=519;

--
-- AUTO_INCREMENT per la tabella `technical_insights`
--
ALTER TABLE `technical_insights`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `technical_snapshots`
--
ALTER TABLE `technical_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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

CREATE ALGORITHM=UNDEFINED DEFINER=`poRtUsR25`@`%` SQL SECURITY DEFINER VIEW `v_holdings_enriched`  AS SELECT `h`.`id` AS `id`, `h`.`portfolio_id` AS `portfolio_id`, `h`.`ticker` AS `ticker`, `h`.`name` AS `name`, `h`.`asset_class` AS `asset_class`, `h`.`sector` AS `sector`, `h`.`quantity` AS `quantity`, `h`.`avg_price` AS `avg_price`, `h`.`current_price` AS `current_price`, `h`.`previous_close` AS `previous_close`, `h`.`quantity`* `h`.`avg_price` AS `invested`, `h`.`quantity`* coalesce(`h`.`current_price`,`h`.`avg_price`) AS `market_value`, `h`.`quantity`* coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`quantity` * `h`.`avg_price` AS `pnl`, CASE WHEN `h`.`avg_price` > 0 THEN (coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`avg_price`) / `h`.`avg_price` * 100 ELSE 0 END AS `pnl_pct`, `h`.`fifty_two_week_high` AS `fifty_two_week_high`, `h`.`fifty_two_week_low` AS `fifty_two_week_low`, `h`.`day_high` AS `day_high`, `h`.`day_low` AS `day_low`, `h`.`ytd_change_percent` AS `ytd_change_percent`, `h`.`one_month_change_percent` AS `one_month_change_percent`, `h`.`three_month_change_percent` AS `three_month_change_percent`, `h`.`one_year_change_percent` AS `one_year_change_percent`, `h`.`dividend_yield` AS `dividend_yield`, `h`.`annual_dividend` AS `annual_dividend`, `h`.`dividend_frequency` AS `dividend_frequency`, `h`.`has_dividends` AS `has_dividends`, `h`.`total_dividends_5y` AS `total_dividends_5y`, `h`.`volume` AS `volume`, `h`.`exchange` AS `exchange`, `h`.`first_trade_date` AS `first_trade_date`, `h`.`price_source` AS `price_source`, `h`.`is_active` AS `is_active`, `h`.`created_at` AS `created_at`, `h`.`updated_at` AS `updated_at` FROM `holdings` AS `h` WHERE `h`.`is_active` = 1 ;

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
