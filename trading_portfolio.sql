-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Dic 02, 2025 alle 09:45
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
(141, 1, '', 11176.06, 100.0000, '2025-12-02 06:00:03');

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
(10, 1, 'SGLD.MI', 'IE00B579F325', 'Invesco Physical Gold ETC', '', 'Gold', 10.000000, 272.5500, 350.5000, 0.0000, 0.000000, 'None', 0, 0, 359.6700, 239.2600, 40.4500, 4.9800, 19.2300, 44.4100, 344.2467, 340.8681, 327.8970, 290.4525, 345.1148, 339.0957, 327.1316, 293.2474, 55.57, 6.7713, 6.5727, 0.1986, 5.1593, 1.4700, 25.5600, 17.9800, 109915, 2.0400, 331.9500, 350.5000, 100.0000, 293.2000, 359.1800, 86.8400, 268.9700, 359.1800, 90.3800, 240.4200, 359.1800, 92.6900, 267.4500, 359.6700, 337.9061, 324.4420, 313.5600, 302.6781, 287.1851, 3.5900, 7.4300, 10.5400, 13.6400, 18.0600, 341.2175, 352.0808, 330.3542, 6.3700, 0.9272, '2025-12-02', 145.4900, 352.9800, 349.1100, 224229, 'YahooFinance_v8', 'MIL', 1417161600, 1, '2025-11-30 08:10:13', '2025-12-02 06:00:03'),
(11, 1, 'VHYL.MI', 'IE00B8GKDB10', 'Vanguard FTSE All-World High Div. Yield UCITS ETF Dis', '', 'Global', 21.000000, 67.9400, 68.8600, 2.9100, 2.001600, 'Quarterly', 1, 20, 69.5000, 55.7600, 6.2000, 1.5200, 4.7300, 3.8600, 68.2756, 68.2271, 66.3082, 64.8214, 68.4713, 67.7210, 66.7123, 65.1508, 53.14, 0.8290, 0.8216, 0.0074, 0.5500, 0.8000, 8.7500, 8.5300, 20033, 1.1900, 67.3400, 69.3800, 74.5100, 65.7000, 69.3800, 85.8700, 62.6700, 69.3800, 92.2500, 56.4400, 69.3800, 95.9800, 62.6400, 69.5000, 67.8810, 66.8795, 66.0700, 65.2605, 64.1080, 1.4200, 2.8800, 4.0500, 5.2300, 6.9000, 68.2470, 69.3984, 67.0956, 3.3700, 0.7662, '2025-12-02', 45.5650, 68.9500, 68.6700, 23825, 'YahooFinance_v8', 'MIL', 1547798400, 1, '2025-11-30 08:10:13', '2025-12-02 06:00:03'),
(12, 1, 'TDIV.MI', 'NL0011683594', 'VanEck Morn. Dev. Mkts Div Lead. UCITS ETF', '', 'Mixed', 50.000000, 46.0000, 46.8000, 3.8200, 1.790000, 'Quarterly', 1, 20, 47.0000, 36.7950, 12.9300, 3.8500, 7.1400, 14.1000, 46.2950, 46.0486, 44.3392, 43.3562, 46.4318, 45.7244, 44.7794, 43.2485, 54.55, 0.7814, 0.7733, 0.0081, 0.4350, 0.9300, 8.5300, 9.1600, 44015, 0.6500, 45.0650, 46.9000, 94.5500, 43.6800, 46.9000, 96.8900, 41.7650, 46.9000, 98.0500, 37.6800, 46.9000, 98.9200, 41.6900, 47.0000, 45.7468, 44.9716, 44.3450, 43.7184, 42.8263, 2.2500, 3.9100, 5.2500, 6.5800, 8.4900, 46.0977, 47.2631, 44.9324, 5.0600, 0.8013, '2025-12-02', 25.7250, 46.8900, 46.6700, 28432, 'YahooFinance_v8', 'MIL', 1556002800, 1, '2025-11-30 08:10:13', '2025-12-02 06:00:03'),
(15, 1, 'SWDA.MI', 'IE00B4L5Y983', 'iShares Core MSCI World UCITS ETF USD (Acc)', '', 'Global', 35.000000, 111.5400, 111.0000, 0.0000, 0.000000, 'None', 0, 0, 112.5500, 82.7000, 5.4100, -0.8500, 6.3200, 5.0000, 109.9822, 110.3367, 107.4222, 102.0840, 110.3978, 109.3760, 107.7242, 103.5475, 52.58, 1.1417, 1.1262, 0.0155, 1.4179, 1.2800, 13.7500, 11.8300, 204121, 0.7900, 108.1300, 111.9600, 74.9300, 104.4000, 111.9600, 87.3000, 98.5700, 111.9600, 92.8300, 85.2900, 111.9600, 96.4000, 98.4400, 112.5500, 109.2200, 107.1600, 105.4950, 103.8300, 101.4595, 1.6000, 3.4600, 4.9600, 6.4600, 8.6000, 110.2560, 112.5861, 107.9259, 4.2300, 0.6596, '2025-12-02', 59.1400, 111.0600, 110.3400, 161358, 'YahooFinance_v8', 'MIL', 1253862000, 1, '2025-11-30 08:14:43', '2025-12-02 06:00:03');

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
(114, 1, 2025, 12, 'Dec', 11176.06, 10356.14, 819.92, 7.9172);

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
(5, 1, '2025-12-01', 10356.14, 11176.06, 819.92, 7.9172, 0.00, '{\"holdings_count\":4}', '2025-12-01 06:00:02'),
(6, 1, '2025-12-02', 10356.14, 11176.06, 819.92, 7.9172, 0.00, '{\"holdings_count\":4}', '2025-12-02 06:00:01');

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
(527, 5, 'SWDA.MI', 35.000000, 111.5400, 111.0000, 3885.00, 3903.90, -18.90, -0.4841),
(528, 5, 'SGLD.MI', 10.000000, 272.5500, 350.5000, 3505.00, 2725.50, 779.50, 28.6003),
(529, 5, 'TDIV.MI', 50.000000, 46.0000, 46.8000, 2340.00, 2300.00, 40.00, 1.7391),
(530, 5, 'VHYL.MI', 21.000000, 67.9400, 68.8600, 1446.06, 1426.74, 19.32, 1.3541),
(539, 6, 'SWDA.MI', 35.000000, 111.5400, 111.0000, 3885.00, 3903.90, -18.90, -0.4841),
(540, 6, 'SGLD.MI', 10.000000, 272.5500, 350.5000, 3505.00, 2725.50, 779.50, 28.6003),
(541, 6, 'TDIV.MI', 50.000000, 46.0000, 46.8000, 2340.00, 2300.00, 40.00, 1.7391),
(542, 6, 'VHYL.MI', 21.000000, 67.9400, 68.8600, 1446.06, 1426.74, 19.32, 1.3541);

-- --------------------------------------------------------

--
-- Struttura della tabella `technical_insights`
--

CREATE TABLE `technical_insights` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `portfolio_id` int(10) UNSIGNED NOT NULL,
  `isin` varchar(32) DEFAULT NULL,
  `ticker` varchar(32) DEFAULT NULL,
  `instrument_name` varchar(255) DEFAULT NULL,
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

INSERT INTO `technical_insights` (`id`, `portfolio_id`, `isin`, `ticker`, `instrument_name`, `scope`, `model`, `generated_at`, `raw_input_snapshot`, `insight_json`, `insight_text`, `created_at`) VALUES
(8, 1, NULL, NULL, NULL, 'portfolio', 'gpt-5-mini', '2025-12-01 17:08:42', '{\"portfolio_id\":1,\"as_of\":\"2025-12-01T17:07:18.144Z\",\"holdings\":[{\"ticker\":\"SWDA.MI\",\"isin\":\"IE00B4L5Y983\",\"name\":\"iShares Core MSCI World UCITS ETF USD (Acc)\",\"sector\":\"Global\",\"asset_class\":null,\"price\":110.69,\"indicators\":{\"ema9\":110.3358,\"ema21\":109.3478,\"ema50\":107.7121,\"ema200\":103.5444,\"rsi14\":52.21,\"atr14\":1.3957,\"atr14_pct\":1.26,\"hist_vol_30d\":13.86,\"hist_vol_90d\":11.87,\"range_1m_percentile\":66.84,\"range_3m_percentile\":83.2,\"range_1y_percentile\":95.24,\"bb_percent_b\":0.5971,\"macd_value\":1.1169,\"macd_signal\":1.1213,\"macd_hist\":-0.0043,\"ytd_change_percent\":5.12,\"one_month_change_percent\":-1.13,\"three_month_change_percent\":6.02,\"one_year_change_percent\":4.71,\"fifty_two_week_high\":112.55,\"fifty_two_week_low\":82.7},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"SGLD.MI\",\"isin\":\"IE00B579F325\",\"name\":\"Invesco Physical Gold ETC\",\"sector\":\"Gold\",\"asset_class\":null,\"price\":352.14,\"indicators\":{\"ema9\":345.4428,\"ema21\":339.2448,\"ema50\":327.1959,\"ema200\":293.2637,\"rsi14\":56.09,\"atr14\":5.1329,\"atr14_pct\":1.46,\"hist_vol_30d\":25.64,\"hist_vol_90d\":17.99,\"range_1m_percentile\":100,\"range_3m_percentile\":89.33,\"range_1y_percentile\":94.07,\"bb_percent_b\":0.9856,\"macd_value\":6.9021,\"macd_signal\":6.5988,\"macd_hist\":0.3033,\"ytd_change_percent\":41.1,\"one_month_change_percent\":5.47,\"three_month_change_percent\":19.79,\"one_year_change_percent\":45.08,\"fifty_two_week_high\":359.67,\"fifty_two_week_low\":239.26},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"TDIV.MI\",\"isin\":\"NL0011683594\",\"name\":\"VanEck Morn. Dev. Mkts Div Lead. UCITS ETF\",\"sector\":\"Mixed\",\"asset_class\":null,\"price\":46.685,\"indicators\":{\"ema9\":46.4088,\"ema21\":45.714,\"ema50\":44.7749,\"ema200\":43.2474,\"rsi14\":54.17,\"atr14\":0.4286,\"atr14_pct\":0.92,\"hist_vol_30d\":8.61,\"hist_vol_90d\":9.18,\"range_1m_percentile\":88.28,\"range_3m_percentile\":93.32,\"range_1y_percentile\":97.67,\"bb_percent_b\":0.7573,\"macd_value\":0.7722,\"macd_signal\":0.7715,\"macd_hist\":0.0007,\"ytd_change_percent\":12.66,\"one_month_change_percent\":3.59,\"three_month_change_percent\":6.88,\"one_year_change_percent\":13.82,\"fifty_two_week_high\":47,\"fifty_two_week_low\":36.795},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"VHYL.MI\",\"isin\":\"IE00B8GKDB10\",\"name\":\"Vanguard FTSE All-World High Div. Yield UCITS ETF Dis\",\"sector\":\"Global\",\"asset_class\":null,\"price\":68.75,\"indicators\":{\"ema9\":68.4493,\"ema21\":67.711,\"ema50\":66.708,\"ema200\":65.1497,\"rsi14\":52.91,\"atr14\":0.5443,\"atr14_pct\":0.79,\"hist_vol_30d\":8.82,\"hist_vol_90d\":8.55,\"range_1m_percentile\":69.12,\"range_3m_percentile\":82.88,\"range_1y_percentile\":95.13,\"bb_percent_b\":0.7229,\"macd_value\":0.8202,\"macd_signal\":0.8199,\"macd_hist\":0.0004,\"ytd_change_percent\":6.03,\"one_month_change_percent\":1.36,\"three_month_change_percent\":4.56,\"one_year_change_percent\":3.7,\"fifty_two_week_high\":69.5,\"fifty_two_week_low\":55.76},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}}]}', '{\"scores\":{\"health_score\":82,\"risk_score\":38,\"diversification_score\":65,\"momentum_score\":74,\"volatility_score\":42,\"extension_score\":78},\"trend\":\"bullish\",\"risk\":\"medium\",\"volatility_comment\":\"Volatilità complessivamente moderata, con l’oro più vivace e gli azionari globali su livelli storicamente tranquilli.\",\"diversification_comment\":\"Buona base globale con componente oro e dividendi, ma concentrazione tecnica verso strumenti tutti in area di massimi.\",\"notes\":\"Scenario tecnico costruttivo: tendenza rialzista diffusa ma estensione elevata sui range a 3-12 mesi; opportuno usare ingressi graduali e stop loss sotto i supporti chiave.\"}', 'Portafoglio in solido uptrend, con momentum positivo ma non estremo e volatilità contenuta; diversi strumenti sono però vicini ai massimi annuali, con rischio di fasi correttive nel breve.', '2025-12-01 17:09:07'),
(9, 1, 'IE00B4L5Y983', NULL, NULL, 'instrument', 'gpt-5-mini', '2025-12-01 17:08:42', NULL, '{\"scores\":{\"trend_strength\":78,\"momentum_strength\":65,\"volatility_score\":35,\"overextension_score\":75},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"weakening\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"middle_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[109.5,107.7,105,103.5],\"potential_resistance_levels\":[112.5,115]},\"trend\":\"bullish\",\"momentum\":\"neutral\",\"flags\":[\"near_52w_high\",\"low_volatility\"],\"notes\":\"EMAs allineate al rialzo, MACD in leggero indebolimento e RSI neutro; possibile acquisto su ritracciamento verso 109–108 con stop sotto 107,5 e primo target in area 112,5–115.\"}', 'Trend rialzista ma meno brillante e prezzo vicino ai massimi annuali: operatività long ancora valida ma con ingressi graduali e stop sotto area 107,7.', '2025-12-01 17:09:07'),
(10, 1, 'IE00B579F325', NULL, NULL, 'instrument', 'gpt-5-mini', '2025-12-01 17:08:42', NULL, '{\"scores\":{\"trend_strength\":90,\"momentum_strength\":88,\"volatility_score\":72,\"overextension_score\":85},\"signals\":{\"trend_signal\":\"strong_uptrend\",\"momentum_signal\":\"accelerating\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"high\"},\"levels\":{\"potential_support_levels\":[345,339,327,315],\"potential_resistance_levels\":[360,370,380]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"high_volatility\",\"overextension\"],\"notes\":\"MACD e medie esponenziali molto forti; possibile nuova gamba rialzista ma con rischio di pullback rapido. Strategia: acquisti parziali su ritorni verso 345–339, stop sotto 327 e target in area 360–370, eventuale estensione verso 380.\"}', 'Fortissimo uptrend con prezzo in prossimità dei massimi e vicino alla banda alta: long interessante ma tecnicamente esteso e volatile, meglio entrare su ritracciamenti.', '2025-12-01 17:09:07'),
(11, 1, 'NL0011683594', NULL, NULL, 'instrument', 'gpt-5-mini', '2025-12-01 17:08:42', NULL, '{\"scores\":{\"trend_strength\":82,\"momentum_strength\":72,\"volatility_score\":32,\"overextension_score\":80},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"stable\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[45.7,44.8,43.2],\"potential_resistance_levels\":[47,48.5]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"low_volatility\",\"overextension\"],\"notes\":\"MACD leggermente positivo, RSI equilibrato ma percentile di range a 1 anno molto elevato. Possibile acquisto su pullback verso 45,7–45 con stop sotto 44,8 e target iniziale 47, con eventuale estensione verso 48,5.\"}', 'Trend rialzista ben impostato, prezzo a ridosso dei massimi storici con volatilità contenuta: long ancora favorevole ma tecnicamente tirato sul breve.', '2025-12-01 17:09:07'),
(12, 1, 'IE00B8GKDB10', NULL, NULL, 'instrument', 'gpt-5-mini', '2025-12-01 17:08:42', NULL, '{\"scores\":{\"trend_strength\":76,\"momentum_strength\":64,\"volatility_score\":34,\"overextension_score\":78},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"stable\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[67.7,66.7,65.1],\"potential_resistance_levels\":[69.5,71]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"low_volatility\",\"overextension\"],\"notes\":\"EMA9 sopra EMA21 e 50, MACD lievemente positivo e volatilità bassa; preferibile comprare su ritorni verso 67,7–67 con stop sotto 66,7 e target principale 69,5, eventuale estensione verso 71.\"}', 'Uptrend regolare con prezzo in area massimi e sopra tutte le medie: struttura long solida ma con margini di ingresso migliori in caso di ritracciamento.', '2025-12-01 17:09:07'),
(13, 1, NULL, NULL, NULL, 'portfolio', 'gpt-5.1', '2025-12-01 18:24:16', '{\"portfolio_id\":1,\"as_of\":\"2025-12-01T17:07:18.144Z\",\"holdings\":[{\"ticker\":\"SWDA.MI\",\"isin\":\"IE00B4L5Y983\",\"name\":\"iShares Core MSCI World UCITS ETF USD (Acc)\",\"sector\":\"Global\",\"asset_class\":null,\"price\":110.69,\"indicators\":{\"ema9\":110.3358,\"ema21\":109.3478,\"ema50\":107.7121,\"ema200\":103.5444,\"rsi14\":52.21,\"atr14\":1.3957,\"atr14_pct\":1.26,\"hist_vol_30d\":13.86,\"hist_vol_90d\":11.87,\"range_1m_percentile\":66.84,\"range_3m_percentile\":83.2,\"range_1y_percentile\":95.24,\"bb_percent_b\":0.5971,\"macd_value\":1.1169,\"macd_signal\":1.1213,\"macd_hist\":-0.0043,\"ytd_change_percent\":5.12,\"one_month_change_percent\":-1.13,\"three_month_change_percent\":6.02,\"one_year_change_percent\":4.71,\"fifty_two_week_high\":112.55,\"fifty_two_week_low\":82.7},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"SGLD.MI\",\"isin\":\"IE00B579F325\",\"name\":\"Invesco Physical Gold ETC\",\"sector\":\"Gold\",\"asset_class\":null,\"price\":352.14,\"indicators\":{\"ema9\":345.4428,\"ema21\":339.2448,\"ema50\":327.1959,\"ema200\":293.2637,\"rsi14\":56.09,\"atr14\":5.1329,\"atr14_pct\":1.46,\"hist_vol_30d\":25.64,\"hist_vol_90d\":17.99,\"range_1m_percentile\":100,\"range_3m_percentile\":89.33,\"range_1y_percentile\":94.07,\"bb_percent_b\":0.9856,\"macd_value\":6.9021,\"macd_signal\":6.5988,\"macd_hist\":0.3033,\"ytd_change_percent\":41.1,\"one_month_change_percent\":5.47,\"three_month_change_percent\":19.79,\"one_year_change_percent\":45.08,\"fifty_two_week_high\":359.67,\"fifty_two_week_low\":239.26},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"TDIV.MI\",\"isin\":\"NL0011683594\",\"name\":\"VanEck Morn. Dev. Mkts Div Lead. UCITS ETF\",\"sector\":\"Mixed\",\"asset_class\":null,\"price\":46.685,\"indicators\":{\"ema9\":46.4088,\"ema21\":45.714,\"ema50\":44.7749,\"ema200\":43.2474,\"rsi14\":54.17,\"atr14\":0.4286,\"atr14_pct\":0.92,\"hist_vol_30d\":8.61,\"hist_vol_90d\":9.18,\"range_1m_percentile\":88.28,\"range_3m_percentile\":93.32,\"range_1y_percentile\":97.67,\"bb_percent_b\":0.7573,\"macd_value\":0.7722,\"macd_signal\":0.7715,\"macd_hist\":0.0007,\"ytd_change_percent\":12.66,\"one_month_change_percent\":3.59,\"three_month_change_percent\":6.88,\"one_year_change_percent\":13.82,\"fifty_two_week_high\":47,\"fifty_two_week_low\":36.795},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"VHYL.MI\",\"isin\":\"IE00B8GKDB10\",\"name\":\"Vanguard FTSE All-World High Div. Yield UCITS ETF Dis\",\"sector\":\"Global\",\"asset_class\":null,\"price\":68.75,\"indicators\":{\"ema9\":68.4493,\"ema21\":67.711,\"ema50\":66.708,\"ema200\":65.1497,\"rsi14\":52.91,\"atr14\":0.5443,\"atr14_pct\":0.79,\"hist_vol_30d\":8.82,\"hist_vol_90d\":8.55,\"range_1m_percentile\":69.12,\"range_3m_percentile\":82.88,\"range_1y_percentile\":95.13,\"bb_percent_b\":0.7229,\"macd_value\":0.8202,\"macd_signal\":0.8199,\"macd_hist\":0.0004,\"ytd_change_percent\":6.03,\"one_month_change_percent\":1.36,\"three_month_change_percent\":4.56,\"one_year_change_percent\":3.7,\"fifty_two_week_high\":69.5,\"fifty_two_week_low\":55.76},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}}]}', '{\"scores\":{\"health_score\":82,\"risk_score\":38,\"diversification_score\":65,\"momentum_score\":74,\"volatility_score\":42,\"extension_score\":78},\"trend\":\"bullish\",\"risk\":\"medium\",\"volatility_comment\":\"Volatilità complessivamente moderata, con l’oro più vivace e gli azionari globali su livelli storicamente tranquilli.\",\"diversification_comment\":\"Buona base globale con componente oro e dividendi, ma concentrazione tecnica verso strumenti tutti in area di massimi.\",\"notes\":\"Scenario tecnico costruttivo: tendenza rialzista diffusa ma estensione elevata sui range a 3-12 mesi; opportuno usare ingressi graduali e stop loss sotto i supporti chiave.\"}', 'Portafoglio in solido uptrend, con momentum positivo ma non estremo e volatilità contenuta; diversi strumenti sono però vicini ai massimi annuali, con rischio di fasi correttive nel breve.', '2025-12-01 18:24:18'),
(14, 1, 'IE00B4L5Y983', NULL, NULL, 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":78,\"momentum_strength\":65,\"volatility_score\":35,\"overextension_score\":75},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"weakening\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"middle_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[109.5,107.7,105,103.5],\"potential_resistance_levels\":[112.5,115]},\"trend\":\"bullish\",\"momentum\":\"neutral\",\"flags\":[\"near_52w_high\",\"low_volatility\"],\"notes\":\"EMAs allineate al rialzo, MACD in leggero indebolimento e RSI neutro; possibile acquisto su ritracciamento verso 109–108 con stop sotto 107,5 e primo target in area 112,5–115.\"}', 'Trend rialzista ma meno brillante e prezzo vicino ai massimi annuali: operatività long ancora valida ma con ingressi graduali e stop sotto area 107,7.', '2025-12-01 18:24:18'),
(15, 1, 'IE00B579F325', NULL, NULL, 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":90,\"momentum_strength\":88,\"volatility_score\":72,\"overextension_score\":85},\"signals\":{\"trend_signal\":\"strong_uptrend\",\"momentum_signal\":\"accelerating\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"high\"},\"levels\":{\"potential_support_levels\":[345,339,327,315],\"potential_resistance_levels\":[360,370,380]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"high_volatility\",\"overextension\"],\"notes\":\"MACD e medie esponenziali molto forti; possibile nuova gamba rialzista ma con rischio di pullback rapido. Strategia: acquisti parziali su ritorni verso 345–339, stop sotto 327 e target in area 360–370, eventuale estensione verso 380.\"}', 'Fortissimo uptrend con prezzo in prossimità dei massimi e vicino alla banda alta: long interessante ma tecnicamente esteso e volatile, meglio entrare su ritracciamenti.', '2025-12-01 18:24:18'),
(16, 1, 'NL0011683594', NULL, NULL, 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":82,\"momentum_strength\":72,\"volatility_score\":32,\"overextension_score\":80},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"stable\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[45.7,44.8,43.2],\"potential_resistance_levels\":[47,48.5]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"low_volatility\",\"overextension\"],\"notes\":\"MACD leggermente positivo, RSI equilibrato ma percentile di range a 1 anno molto elevato. Possibile acquisto su pullback verso 45,7–45 con stop sotto 44,8 e target iniziale 47, con eventuale estensione verso 48,5.\"}', 'Trend rialzista ben impostato, prezzo a ridosso dei massimi storici con volatilità contenuta: long ancora favorevole ma tecnicamente tirato sul breve.', '2025-12-01 18:24:18'),
(17, 1, 'IE00B8GKDB10', NULL, NULL, 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":76,\"momentum_strength\":64,\"volatility_score\":34,\"overextension_score\":78},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"stable\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[67.7,66.7,65.1],\"potential_resistance_levels\":[69.5,71]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"low_volatility\",\"overextension\"],\"notes\":\"EMA9 sopra EMA21 e 50, MACD lievemente positivo e volatilità bassa; preferibile comprare su ritorni verso 67,7–67 con stop sotto 66,7 e target principale 69,5, eventuale estensione verso 71.\"}', 'Uptrend regolare con prezzo in area massimi e sopra tutte le medie: struttura long solida ma con margini di ingresso migliori in caso di ritracciamento.', '2025-12-01 18:24:18'),
(18, 1, NULL, NULL, NULL, 'portfolio', 'gpt-5.1', '2025-12-01 18:24:16', '{\"portfolio_id\":1,\"as_of\":\"2025-12-01T17:07:18.144Z\",\"holdings\":[{\"ticker\":\"SWDA.MI\",\"isin\":\"IE00B4L5Y983\",\"name\":\"iShares Core MSCI World UCITS ETF USD (Acc)\",\"sector\":\"Global\",\"asset_class\":null,\"price\":110.69,\"indicators\":{\"ema9\":110.3358,\"ema21\":109.3478,\"ema50\":107.7121,\"ema200\":103.5444,\"rsi14\":52.21,\"atr14\":1.3957,\"atr14_pct\":1.26,\"hist_vol_30d\":13.86,\"hist_vol_90d\":11.87,\"range_1m_percentile\":66.84,\"range_3m_percentile\":83.2,\"range_1y_percentile\":95.24,\"bb_percent_b\":0.5971,\"macd_value\":1.1169,\"macd_signal\":1.1213,\"macd_hist\":-0.0043,\"ytd_change_percent\":5.12,\"one_month_change_percent\":-1.13,\"three_month_change_percent\":6.02,\"one_year_change_percent\":4.71,\"fifty_two_week_high\":112.55,\"fifty_two_week_low\":82.7},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"SGLD.MI\",\"isin\":\"IE00B579F325\",\"name\":\"Invesco Physical Gold ETC\",\"sector\":\"Gold\",\"asset_class\":null,\"price\":352.14,\"indicators\":{\"ema9\":345.4428,\"ema21\":339.2448,\"ema50\":327.1959,\"ema200\":293.2637,\"rsi14\":56.09,\"atr14\":5.1329,\"atr14_pct\":1.46,\"hist_vol_30d\":25.64,\"hist_vol_90d\":17.99,\"range_1m_percentile\":100,\"range_3m_percentile\":89.33,\"range_1y_percentile\":94.07,\"bb_percent_b\":0.9856,\"macd_value\":6.9021,\"macd_signal\":6.5988,\"macd_hist\":0.3033,\"ytd_change_percent\":41.1,\"one_month_change_percent\":5.47,\"three_month_change_percent\":19.79,\"one_year_change_percent\":45.08,\"fifty_two_week_high\":359.67,\"fifty_two_week_low\":239.26},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"TDIV.MI\",\"isin\":\"NL0011683594\",\"name\":\"VanEck Morn. Dev. Mkts Div Lead. UCITS ETF\",\"sector\":\"Mixed\",\"asset_class\":null,\"price\":46.685,\"indicators\":{\"ema9\":46.4088,\"ema21\":45.714,\"ema50\":44.7749,\"ema200\":43.2474,\"rsi14\":54.17,\"atr14\":0.4286,\"atr14_pct\":0.92,\"hist_vol_30d\":8.61,\"hist_vol_90d\":9.18,\"range_1m_percentile\":88.28,\"range_3m_percentile\":93.32,\"range_1y_percentile\":97.67,\"bb_percent_b\":0.7573,\"macd_value\":0.7722,\"macd_signal\":0.7715,\"macd_hist\":0.0007,\"ytd_change_percent\":12.66,\"one_month_change_percent\":3.59,\"three_month_change_percent\":6.88,\"one_year_change_percent\":13.82,\"fifty_two_week_high\":47,\"fifty_two_week_low\":36.795},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"VHYL.MI\",\"isin\":\"IE00B8GKDB10\",\"name\":\"Vanguard FTSE All-World High Div. Yield UCITS ETF Dis\",\"sector\":\"Global\",\"asset_class\":null,\"price\":68.75,\"indicators\":{\"ema9\":68.4493,\"ema21\":67.711,\"ema50\":66.708,\"ema200\":65.1497,\"rsi14\":52.91,\"atr14\":0.5443,\"atr14_pct\":0.79,\"hist_vol_30d\":8.82,\"hist_vol_90d\":8.55,\"range_1m_percentile\":69.12,\"range_3m_percentile\":82.88,\"range_1y_percentile\":95.13,\"bb_percent_b\":0.7229,\"macd_value\":0.8202,\"macd_signal\":0.8199,\"macd_hist\":0.0004,\"ytd_change_percent\":6.03,\"one_month_change_percent\":1.36,\"three_month_change_percent\":4.56,\"one_year_change_percent\":3.7,\"fifty_two_week_high\":69.5,\"fifty_two_week_low\":55.76},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}}]}', '{\"scores\":{\"health_score\":82,\"risk_score\":38,\"diversification_score\":65,\"momentum_score\":74,\"volatility_score\":42,\"extension_score\":78},\"trend\":\"bullish\",\"risk\":\"medium\",\"volatility_comment\":\"Volatilità complessivamente moderata, con l’oro più vivace e gli azionari globali su livelli storicamente tranquilli.\",\"diversification_comment\":\"Buona base globale con componente oro e dividendi, ma concentrazione tecnica verso strumenti tutti in area di massimi.\",\"notes\":\"Scenario tecnico costruttivo: tendenza rialzista diffusa ma estensione elevata sui range a 3-12 mesi; opportuno usare ingressi graduali e stop loss sotto i supporti chiave.\"}', 'Portafoglio in solido uptrend, con momentum positivo ma non estremo e volatilità contenuta; diversi strumenti sono però vicini ai massimi annuali, con rischio di fasi correttive nel breve.', '2025-12-01 18:33:56'),
(19, 1, NULL, NULL, NULL, 'portfolio', 'gpt-5.1', '2025-12-01 18:24:16', '{\"portfolio_id\":1,\"as_of\":\"2025-12-01T17:07:18.144Z\",\"holdings\":[{\"ticker\":\"SWDA.MI\",\"isin\":\"IE00B4L5Y983\",\"name\":\"iShares Core MSCI World UCITS ETF USD (Acc)\",\"sector\":\"Global\",\"asset_class\":null,\"price\":110.69,\"indicators\":{\"ema9\":110.3358,\"ema21\":109.3478,\"ema50\":107.7121,\"ema200\":103.5444,\"rsi14\":52.21,\"atr14\":1.3957,\"atr14_pct\":1.26,\"hist_vol_30d\":13.86,\"hist_vol_90d\":11.87,\"range_1m_percentile\":66.84,\"range_3m_percentile\":83.2,\"range_1y_percentile\":95.24,\"bb_percent_b\":0.5971,\"macd_value\":1.1169,\"macd_signal\":1.1213,\"macd_hist\":-0.0043,\"ytd_change_percent\":5.12,\"one_month_change_percent\":-1.13,\"three_month_change_percent\":6.02,\"one_year_change_percent\":4.71,\"fifty_two_week_high\":112.55,\"fifty_two_week_low\":82.7},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"SGLD.MI\",\"isin\":\"IE00B579F325\",\"name\":\"Invesco Physical Gold ETC\",\"sector\":\"Gold\",\"asset_class\":null,\"price\":352.14,\"indicators\":{\"ema9\":345.4428,\"ema21\":339.2448,\"ema50\":327.1959,\"ema200\":293.2637,\"rsi14\":56.09,\"atr14\":5.1329,\"atr14_pct\":1.46,\"hist_vol_30d\":25.64,\"hist_vol_90d\":17.99,\"range_1m_percentile\":100,\"range_3m_percentile\":89.33,\"range_1y_percentile\":94.07,\"bb_percent_b\":0.9856,\"macd_value\":6.9021,\"macd_signal\":6.5988,\"macd_hist\":0.3033,\"ytd_change_percent\":41.1,\"one_month_change_percent\":5.47,\"three_month_change_percent\":19.79,\"one_year_change_percent\":45.08,\"fifty_two_week_high\":359.67,\"fifty_two_week_low\":239.26},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"TDIV.MI\",\"isin\":\"NL0011683594\",\"name\":\"VanEck Morn. Dev. Mkts Div Lead. UCITS ETF\",\"sector\":\"Mixed\",\"asset_class\":null,\"price\":46.685,\"indicators\":{\"ema9\":46.4088,\"ema21\":45.714,\"ema50\":44.7749,\"ema200\":43.2474,\"rsi14\":54.17,\"atr14\":0.4286,\"atr14_pct\":0.92,\"hist_vol_30d\":8.61,\"hist_vol_90d\":9.18,\"range_1m_percentile\":88.28,\"range_3m_percentile\":93.32,\"range_1y_percentile\":97.67,\"bb_percent_b\":0.7573,\"macd_value\":0.7722,\"macd_signal\":0.7715,\"macd_hist\":0.0007,\"ytd_change_percent\":12.66,\"one_month_change_percent\":3.59,\"three_month_change_percent\":6.88,\"one_year_change_percent\":13.82,\"fifty_two_week_high\":47,\"fifty_two_week_low\":36.795},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}},{\"ticker\":\"VHYL.MI\",\"isin\":\"IE00B8GKDB10\",\"name\":\"Vanguard FTSE All-World High Div. Yield UCITS ETF Dis\",\"sector\":\"Global\",\"asset_class\":null,\"price\":68.75,\"indicators\":{\"ema9\":68.4493,\"ema21\":67.711,\"ema50\":66.708,\"ema200\":65.1497,\"rsi14\":52.91,\"atr14\":0.5443,\"atr14_pct\":0.79,\"hist_vol_30d\":8.82,\"hist_vol_90d\":8.55,\"range_1m_percentile\":69.12,\"range_3m_percentile\":82.88,\"range_1y_percentile\":95.13,\"bb_percent_b\":0.7229,\"macd_value\":0.8202,\"macd_signal\":0.8199,\"macd_hist\":0.0004,\"ytd_change_percent\":6.03,\"one_month_change_percent\":1.36,\"three_month_change_percent\":4.56,\"one_year_change_percent\":3.7,\"fifty_two_week_high\":69.5,\"fifty_two_week_low\":55.76},\"derived\":{\"trend_ema50_ema200\":\"bullish\",\"momentum_ema9_ema21\":\"bullish\",\"near_52w_high\":true,\"near_52w_low\":false}}]}', '{\"scores\":{\"health_score\":82,\"risk_score\":38,\"diversification_score\":65,\"momentum_score\":74,\"volatility_score\":42,\"extension_score\":78},\"trend\":\"bullish\",\"risk\":\"medium\",\"volatility_comment\":\"Volatilità complessivamente moderata, con l’oro più vivace e gli azionari globali su livelli storicamente tranquilli.\",\"diversification_comment\":\"Buona base globale con componente oro e dividendi, ma concentrazione tecnica verso strumenti tutti in area di massimi.\",\"notes\":\"Scenario tecnico costruttivo: tendenza rialzista diffusa ma estensione elevata sui range a 3-12 mesi; opportuno usare ingressi graduali e stop loss sotto i supporti chiave.\"}', 'Portafoglio in solido uptrend, con momentum positivo ma non estremo e volatilità contenuta; diversi strumenti sono però vicini ai massimi annuali, con rischio di fasi correttive nel breve.', '2025-12-01 18:36:51'),
(20, 1, 'IE00B4L5Y983', 'SWDA.MI', 'iShares Core MSCI World UCITS ETF USD (Acc)', 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":78,\"momentum_strength\":65,\"volatility_score\":35,\"overextension_score\":75},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"weakening\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"middle_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[109.5,107.7,105,103.5],\"potential_resistance_levels\":[112.5,115]},\"trend\":\"bullish\",\"momentum\":\"neutral\",\"flags\":[\"near_52w_high\",\"low_volatility\"],\"notes\":\"EMAs allineate al rialzo, MACD in leggero indebolimento e RSI neutro; possibile acquisto su ritracciamento verso 109–108 con stop sotto 107,5 e primo target in area 112,5–115.\"}', 'Trend rialzista ma meno brillante e prezzo vicino ai massimi annuali: operatività long ancora valida ma con ingressi graduali e stop sotto area 107,7.', '2025-12-01 18:36:51'),
(21, 1, 'IE00B579F325', 'SGLD.MI', 'Invesco Physical Gold ETC', 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":90,\"momentum_strength\":88,\"volatility_score\":72,\"overextension_score\":85},\"signals\":{\"trend_signal\":\"strong_uptrend\",\"momentum_signal\":\"accelerating\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"high\"},\"levels\":{\"potential_support_levels\":[345,339,327,315],\"potential_resistance_levels\":[360,370,380]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"high_volatility\",\"overextension\"],\"notes\":\"MACD e medie esponenziali molto forti; possibile nuova gamba rialzista ma con rischio di pullback rapido. Strategia: acquisti parziali su ritorni verso 345–339, stop sotto 327 e target in area 360–370, eventuale estensione verso 380.\"}', 'Fortissimo uptrend con prezzo in prossimità dei massimi e vicino alla banda alta: long interessante ma tecnicamente esteso e volatile, meglio entrare su ritracciamenti.', '2025-12-01 18:36:51'),
(22, 1, 'NL0011683594', 'TDIV.MI', 'VanEck Morn. Dev. Mkts Div Lead. UCITS ETF', 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":82,\"momentum_strength\":72,\"volatility_score\":32,\"overextension_score\":80},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"stable\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[45.7,44.8,43.2],\"potential_resistance_levels\":[47,48.5]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"low_volatility\",\"overextension\"],\"notes\":\"MACD leggermente positivo, RSI equilibrato ma percentile di range a 1 anno molto elevato. Possibile acquisto su pullback verso 45,7–45 con stop sotto 44,8 e target iniziale 47, con eventuale estensione verso 48,5.\"}', 'Trend rialzista ben impostato, prezzo a ridosso dei massimi storici con volatilità contenuta: long ancora favorevole ma tecnicamente tirato sul breve.', '2025-12-01 18:36:51'),
(23, 1, 'IE00B8GKDB10', 'VHYL.MI', 'Vanguard FTSE All-World High Div. Yield UCITS ETF Dis', 'instrument', 'gpt-5.1', '2025-12-01 18:24:16', NULL, '{\"scores\":{\"trend_strength\":76,\"momentum_strength\":64,\"volatility_score\":34,\"overextension_score\":78},\"signals\":{\"trend_signal\":\"uptrend\",\"momentum_signal\":\"stable\",\"rsi_condition\":\"neutral\",\"bollinger_position\":\"upper_band\",\"range_position\":\"near_high\",\"volatility_flag\":\"low\"},\"levels\":{\"potential_support_levels\":[67.7,66.7,65.1],\"potential_resistance_levels\":[69.5,71]},\"trend\":\"bullish\",\"momentum\":\"bullish\",\"flags\":[\"near_52w_high\",\"low_volatility\",\"overextension\"],\"notes\":\"EMA9 sopra EMA21 e 50, MACD lievemente positivo e volatilità bassa; preferibile comprare su ritorni verso 67,7–67 con stop sotto 66,7 e target principale 69,5, eventuale estensione verso 71.\"}', 'Uptrend regolare con prezzo in area massimi e sopra tutte le medie: struttura long solida ma con margini di ingresso migliori in caso di ritracciamento.', '2025-12-01 18:36:51');

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
(5, 1, 'IE00B579F325', '2025-12-01', 350.5000, 55.57, 6.7713, 6.5727, 25.5600, 1.4700, 92.6900, 0.9272, '2025-12-01 10:31:48'),
(6, 1, 'IE00B4L5Y983', '2025-12-01', 111.0000, 52.58, 1.1417, 1.1262, 13.7500, 1.2800, 96.4000, 0.6596, '2025-12-01 10:31:48'),
(7, 1, 'NL0011683594', '2025-12-01', 46.8000, 54.55, 0.7814, 0.7733, 8.5300, 0.9300, 98.9200, 0.8013, '2025-12-01 10:31:48'),
(8, 1, 'IE00B8GKDB10', '2025-12-01', 68.8600, 53.14, 0.8290, 0.8216, 8.7500, 0.8000, 95.9800, 0.7662, '2025-12-01 10:31:48'),
(17, 1, 'IE00B579F325', '2025-12-02', 350.5000, 55.57, 6.7713, 6.5727, 25.5600, 1.4700, 92.6900, 0.9272, '2025-12-02 06:00:03'),
(18, 1, 'IE00B4L5Y983', '2025-12-02', 111.0000, 52.58, 1.1417, 1.1262, 13.7500, 1.2800, 96.4000, 0.6596, '2025-12-02 06:00:03'),
(19, 1, 'NL0011683594', '2025-12-02', 46.8000, 54.55, 0.7814, 0.7733, 8.5300, 0.9300, 98.9200, 0.8013, '2025-12-02 06:00:03'),
(20, 1, 'IE00B8GKDB10', '2025-12-02', 68.8600, 53.14, 0.8290, 0.8216, 8.7500, 0.8000, 95.9800, 0.7662, '2025-12-02 06:00:03');

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
,`isin` varchar(20)
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
,`volume` bigint(20) unsigned
,`exchange` varchar(20)
,`dividend_yield` decimal(8,4)
,`annual_dividend` decimal(12,6)
,`dividend_frequency` varchar(20)
,`has_dividends` tinyint(1)
,`total_dividends_5y` int(10) unsigned
,`first_trade_date` bigint(20) unsigned
,`price_source` varchar(50)
,`is_active` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
,`sma9` decimal(12,4)
,`sma21` decimal(12,4)
,`sma50` decimal(12,4)
,`sma200` decimal(12,4)
,`ema9` decimal(12,4)
,`ema21` decimal(12,4)
,`ema50` decimal(12,4)
,`ema200` decimal(12,4)
,`rsi14` decimal(6,2)
,`macd_value` decimal(12,4)
,`macd_signal` decimal(12,4)
,`macd_hist` decimal(12,4)
,`atr14` decimal(12,4)
,`atr14_pct` decimal(8,4)
,`hist_vol_30d` decimal(8,4)
,`hist_vol_90d` decimal(8,4)
,`range_1m_min` decimal(12,4)
,`range_1m_max` decimal(12,4)
,`range_1m_percentile` decimal(8,4)
,`range_3m_min` decimal(12,4)
,`range_3m_max` decimal(12,4)
,`range_3m_percentile` decimal(8,4)
,`range_6m_min` decimal(12,4)
,`range_6m_max` decimal(12,4)
,`range_6m_percentile` decimal(8,4)
,`range_1y_min` decimal(12,4)
,`range_1y_max` decimal(12,4)
,`range_1y_percentile` decimal(8,4)
,`fib_low` decimal(12,4)
,`fib_high` decimal(12,4)
,`fib_23_6` decimal(12,4)
,`fib_38_2` decimal(12,4)
,`fib_50_0` decimal(12,4)
,`fib_61_8` decimal(12,4)
,`fib_78_6` decimal(12,4)
,`fib_23_6_dist_pct` decimal(8,4)
,`fib_38_2_dist_pct` decimal(8,4)
,`fib_50_0_dist_pct` decimal(8,4)
,`fib_61_8_dist_pct` decimal(8,4)
,`fib_78_6_dist_pct` decimal(8,4)
,`bb_middle` decimal(12,4)
,`bb_upper` decimal(12,4)
,`bb_lower` decimal(12,4)
,`bb_width_pct` decimal(8,4)
,`bb_percent_b` decimal(8,4)
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT per la tabella `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `snapshots`
--
ALTER TABLE `snapshots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `snapshot_holdings`
--
ALTER TABLE `snapshot_holdings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=543;

--
-- AUTO_INCREMENT per la tabella `technical_insights`
--
ALTER TABLE `technical_insights`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT per la tabella `technical_snapshots`
--
ALTER TABLE `technical_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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

CREATE ALGORITHM=UNDEFINED DEFINER=`poRtUsR25`@`%` SQL SECURITY DEFINER VIEW `v_holdings_enriched`  AS SELECT `h`.`id` AS `id`, `h`.`portfolio_id` AS `portfolio_id`, `h`.`isin` AS `isin`, `h`.`ticker` AS `ticker`, `h`.`name` AS `name`, `h`.`asset_class` AS `asset_class`, `h`.`sector` AS `sector`, `h`.`quantity` AS `quantity`, `h`.`avg_price` AS `avg_price`, `h`.`current_price` AS `current_price`, `h`.`previous_close` AS `previous_close`, `h`.`quantity`* `h`.`avg_price` AS `invested`, `h`.`quantity`* coalesce(`h`.`current_price`,`h`.`avg_price`) AS `market_value`, `h`.`quantity`* coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`quantity` * `h`.`avg_price` AS `pnl`, CASE WHEN `h`.`avg_price` > 0 THEN (coalesce(`h`.`current_price`,`h`.`avg_price`) - `h`.`avg_price`) / `h`.`avg_price` * 100 ELSE 0 END AS `pnl_pct`, `h`.`fifty_two_week_high` AS `fifty_two_week_high`, `h`.`fifty_two_week_low` AS `fifty_two_week_low`, `h`.`day_high` AS `day_high`, `h`.`day_low` AS `day_low`, `h`.`ytd_change_percent` AS `ytd_change_percent`, `h`.`one_month_change_percent` AS `one_month_change_percent`, `h`.`three_month_change_percent` AS `three_month_change_percent`, `h`.`one_year_change_percent` AS `one_year_change_percent`, `h`.`volume` AS `volume`, `h`.`exchange` AS `exchange`, `h`.`dividend_yield` AS `dividend_yield`, `h`.`annual_dividend` AS `annual_dividend`, `h`.`dividend_frequency` AS `dividend_frequency`, `h`.`has_dividends` AS `has_dividends`, `h`.`total_dividends_5y` AS `total_dividends_5y`, `h`.`first_trade_date` AS `first_trade_date`, `h`.`price_source` AS `price_source`, `h`.`is_active` AS `is_active`, `h`.`created_at` AS `created_at`, `h`.`updated_at` AS `updated_at`, `h`.`sma9` AS `sma9`, `h`.`sma21` AS `sma21`, `h`.`sma50` AS `sma50`, `h`.`sma200` AS `sma200`, `h`.`ema9` AS `ema9`, `h`.`ema21` AS `ema21`, `h`.`ema50` AS `ema50`, `h`.`ema200` AS `ema200`, `h`.`rsi14` AS `rsi14`, `h`.`macd_value` AS `macd_value`, `h`.`macd_signal` AS `macd_signal`, `h`.`macd_hist` AS `macd_hist`, `h`.`atr14` AS `atr14`, `h`.`atr14_pct` AS `atr14_pct`, `h`.`hist_vol_30d` AS `hist_vol_30d`, `h`.`hist_vol_90d` AS `hist_vol_90d`, `h`.`range_1m_min` AS `range_1m_min`, `h`.`range_1m_max` AS `range_1m_max`, `h`.`range_1m_percentile` AS `range_1m_percentile`, `h`.`range_3m_min` AS `range_3m_min`, `h`.`range_3m_max` AS `range_3m_max`, `h`.`range_3m_percentile` AS `range_3m_percentile`, `h`.`range_6m_min` AS `range_6m_min`, `h`.`range_6m_max` AS `range_6m_max`, `h`.`range_6m_percentile` AS `range_6m_percentile`, `h`.`range_1y_min` AS `range_1y_min`, `h`.`range_1y_max` AS `range_1y_max`, `h`.`range_1y_percentile` AS `range_1y_percentile`, `h`.`fib_low` AS `fib_low`, `h`.`fib_high` AS `fib_high`, `h`.`fib_23_6` AS `fib_23_6`, `h`.`fib_38_2` AS `fib_38_2`, `h`.`fib_50_0` AS `fib_50_0`, `h`.`fib_61_8` AS `fib_61_8`, `h`.`fib_78_6` AS `fib_78_6`, `h`.`fib_23_6_dist_pct` AS `fib_23_6_dist_pct`, `h`.`fib_38_2_dist_pct` AS `fib_38_2_dist_pct`, `h`.`fib_50_0_dist_pct` AS `fib_50_0_dist_pct`, `h`.`fib_61_8_dist_pct` AS `fib_61_8_dist_pct`, `h`.`fib_78_6_dist_pct` AS `fib_78_6_dist_pct`, `h`.`bb_middle` AS `bb_middle`, `h`.`bb_upper` AS `bb_upper`, `h`.`bb_lower` AS `bb_lower`, `h`.`bb_width_pct` AS `bb_width_pct`, `h`.`bb_percent_b` AS `bb_percent_b` FROM `holdings` AS `h` WHERE `h`.`is_active` = 1 ;

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
