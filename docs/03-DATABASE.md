# 03 – Database (MariaDB 10.5)

Questo documento descrive lo schema logico e fisico del database MariaDB utilizzato da **ETF Portfolio Manager – Fineco + n8n**, con focus su:
- separazione tra dati utente e dati di mercato
- tabelle, relazioni, indici e viste principali
- convenzioni per query e aggiornamenti (compresi i workflow n8n).

---

## 1. Panoramica

Il sistema utilizza **un singolo database** (es. `etf_portfolio`) in MariaDB 10.5, organizzato in **due aree logiche**:

- **Dati condivisi (`market_data`)**
  - Quotazioni strumenti (ETF, fondi, eventuali azioni).
  - Anagrafiche ETF.
  - Profili commissionali Fineco.
  - Aggiornati principalmente da workflow n8n.

- **Dati utente (`utente`)**
  - Utenti applicazione e loro portafogli.
  - Posizioni (holdings) e transazioni.
  - Snapshot storici di portafoglio.
  - Eventuali risultati di analisi specifici per portafoglio/strumento.

MariaDB non gestisce nativamente i “schema” come PostgreSQL; in pratica si utilizzano **prefissi di tabella** per separare le aree logiche (es. `market_data_quotes`, `utente_users`, ecc.). In questo documento useremo nomi logici senza prefisso, da adattare nel file `db/init.sql`.

---

## 2. Schema logico

### 2.1 Entità principali

- **User**
  - Identifica un utilizzatore della web‑app.
  - Può avere uno o più portafogli.

- **Portfolio**
  - Collezione logica di posizioni appartenenti a un utente (es. “Personale”, “Trading”, “Pensione”).
  - Ha una valuta base (tipicamente EUR).

- **Holding**
  - Posizione su un singolo strumento (ISIN) all’interno di un portafoglio.
  - Riferimento ai dati di mercato tramite ISIN.

- **Transaction**
  - Operazione storica associata a un portafoglio e a un ISIN:
  - BUY, SELL, DIVIDEND.

- **Portfolio Snapshot**
  - Foto giornaliera dello stato sintetico di un portafoglio (valore totale, investito, P&L, dividendi, numero posizioni).

- **Market Quote**
  - Informazioni di prezzo e volume per singolo ISIN (ultima chiusura).

- **ETF Info**
  - Anagrafica “statico/semi‑statica” per ISIN (nome, ticker, settore, asset class, expense ratio, frequenza dividendo, ecc.).

- **Fineco Commission Profile**
  - Profilo commissionali Fineco associato a ISIN (zero commissioni, standard, USA, ecc.).

- **Analysis Result** (opzionale, consigliato)
  - Risultati aggregati di analisi tecnica/macro/opportunità/ribilanciamento generati da n8n.

---

## 3. Schema fisico – DDL (bozza base)

> Nota: i nomi delle tabelle possono essere prefissati (es. `utente_users`, `market_data_quotes`) nel file `init.sql` per evitare conflitti. Qui sono riportati in forma compatta.

### 3.1 Dati utente

#### 3.1.1 Tabella `users`

CREATE TABLE users (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(255) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL COMMENT 'Argon2id hash + pepper',
full_name VARCHAR(255) DEFAULT NULL,
is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Soft delete',
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
last_login TIMESTAMP NULL DEFAULT NULL,
INDEX idx_email (email),
INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.1.2 Tabella `portfolios`

CREATE TABLE portfolios (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
user_id INT UNSIGNED NOT NULL,
name VARCHAR(100) NOT NULL,
description TEXT DEFAULT NULL,
base_currency CHAR(3) NOT NULL DEFAULT 'EUR',
is_active TINYINT(1) NOT NULL DEFAULT 1,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY fk_portfolio_user (user_id)
REFERENCES users(id)
ON DELETE CASCADE,
INDEX idx_user (user_id),
INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.1.3 Tabella `holdings`

CREATE TABLE holdings (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
portfolio_id INT UNSIGNED NOT NULL,
isin VARCHAR(12) NOT NULL COMMENT 'Riferimento a etf_info.isin',
quantity DECIMAL(18,6) NOT NULL DEFAULT 0,
avg_price DECIMAL(18,6) NOT NULL DEFAULT 0,
target_allocation DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentuale target (0-100)',
notes TEXT DEFAULT NULL,
is_active TINYINT(1) NOT NULL DEFAULT 1,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY fk_holding_portfolio (portfolio_id)
REFERENCES portfolios(id)
ON DELETE CASCADE,
UNIQUE KEY uniq_portfolio_isin (portfolio_id, isin),
INDEX idx_portfolio (portfolio_id),
INDEX idx_isin (isin),
INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.1.4 Tabella `transactions`

CREATE TABLE transactions (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
portfolio_id INT UNSIGNED NOT NULL,
isin VARCHAR(12) NOT NULL,
type ENUM('BUY','SELL','DIVIDEND') NOT NULL,
transaction_date DATE NOT NULL,
quantity DECIMAL(18,6) DEFAULT NULL COMMENT 'Null per DIVIDEND',
price DECIMAL(18,6) DEFAULT NULL COMMENT 'Null per DIVIDEND',
total_amount DECIMAL(18,6) NOT NULL COMMENT 'Importo lordo operazione',
commission DECIMAL(10,2) NOT NULL DEFAULT 0.00,
fx_rate DECIMAL(10,6) NOT NULL DEFAULT 1.000000,
source VARCHAR(50) NOT NULL DEFAULT 'MANUAL',
notes TEXT DEFAULT NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY fk_tx_portfolio (portfolio_id)
REFERENCES portfolios(id)
ON DELETE CASCADE,
INDEX idx_portfolio_date (portfolio_id, transaction_date),
INDEX idx_isin_date (isin, transaction_date),
INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.1.5 Tabella `portfolio_snapshots`

CREATE TABLE portfolio_snapshots (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
portfolio_id INT UNSIGNED NOT NULL,
snapshot_date DATE NOT NULL,
total_value DECIMAL(18,2) NOT NULL COMMENT 'Valore totale portafoglio in valuta base',
total_invested DECIMAL(18,2) NOT NULL COMMENT 'Capitale investito',
unrealized_pnl DECIMAL(18,2) NOT NULL COMMENT 'P&L non realizzato',
realized_pnl DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'P&L realizzato cumulato',
total_dividends DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'Dividendi cumulati',
holdings_count INT UNSIGNED NOT NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY fk_snapshot_portfolio (portfolio_id)
REFERENCES portfolios(id)
ON DELETE CASCADE,
UNIQUE KEY uniq_portfolio_date (portfolio_id, snapshot_date),
INDEX idx_date (snapshot_date DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.1.6 (Opzionale) Tabella `analysis_results`

CREATE TABLE analysis_results (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
portfolio_id INT UNSIGNED DEFAULT NULL COMMENT 'Null se analisi generale',
isin VARCHAR(12) DEFAULT NULL COMMENT 'Null per analisi solo di portafoglio',
analysis_type ENUM('TECHNICAL','MACRO_SENTIMENT','OPPORTUNITY','REBALANCING') NOT NULL,
analyzed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
signal ENUM('BUY','SELL','HOLD','WATCH','NEUTRAL') NOT NULL DEFAULT 'NEUTRAL',
confidence_score DECIMAL(3,2) NOT NULL DEFAULT 0.00 COMMENT '0.00-1.00',
data JSON DEFAULT NULL COMMENT 'Payload JSON specifico del workflow',
FOREIGN KEY fk_ar_portfolio (portfolio_id)
REFERENCES portfolios(id)
ON DELETE CASCADE,
INDEX idx_portfolio_date (portfolio_id, analyzed_at),
INDEX idx_isin_type (isin, analysis_type),
INDEX idx_type_date (analysis_type, analyzed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


---

### 3.2 Dati di mercato (condivisi)

#### 3.2.1 Tabella `etf_info`

CREATE TABLE etf_info (
isin VARCHAR(12) PRIMARY KEY,
ticker VARCHAR(20) NOT NULL,
name VARCHAR(255) NOT NULL,
asset_class VARCHAR(50) NOT NULL,
sector VARCHAR(100) DEFAULT NULL,
currency CHAR(3) NOT NULL DEFAULT 'EUR',
exchange VARCHAR(10) DEFAULT 'MIL' COMMENT 'Borsa principale',
expense_ratio DECIMAL(5,2) DEFAULT NULL COMMENT '% TER',
dividend_frequency VARCHAR(20) DEFAULT NULL COMMENT 'es. MONTHLY, QUARTERLY, ACCUM',
last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
INDEX idx_ticker (ticker),
INDEX idx_asset_class (asset_class),
INDEX idx_sector (sector)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.2.2 Tabella `quotes`

CREATE TABLE quotes (
isin VARCHAR(12) PRIMARY KEY,
price DECIMAL(18,6) NOT NULL COMMENT 'Prezzo di chiusura più recente',
previous_close DECIMAL(18,6) DEFAULT NULL,
volume BIGINT UNSIGNED DEFAULT NULL,
data_source VARCHAR(50) NOT NULL DEFAULT 'alpha_vantage',
last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
INDEX idx_last_update (last_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


#### 3.2.3 Tabella `fineco_commission_cache`

CREATE TABLE fineco_commission_cache (
isin VARCHAR(12) PRIMARY KEY,
commission_profile ENUM('ZERO','STANDARD','USA','ETC_ETN','OTHER') NOT NULL,
commission_rate DECIMAL(5,2) DEFAULT NULL COMMENT 'Percentuale o valore di supporto',
last_checked TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
notes TEXT DEFAULT NULL,
INDEX idx_profile (commission_profile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


---

## 4. Viste per dashboard e API

Le viste servono a semplificare le query del backend e a centralizzare la logica di join tra dati utente e dati di mercato.

### 4.1 Vista `v_holdings_full`

Restituisce tutte le informazioni necessarie per la pagina “Holdings” e per la dashboard.

CREATE VIEW v_holdings_full AS
SELECT
h.id AS holding_id,
h.portfolio_id,
p.user_id,
p.name AS portfolio_name,
h.isin,
ei.ticker,
ei.name AS instrument_name,
ei.asset_class,
ei.sector,
ei.currency,
h.quantity,
h.avg_price,
h.target_allocation,
q.price AS current_price,
(h.quantity * q.price) AS market_value,
(h.quantity * h.avg_price) AS invested_value,
( (q.price - h.avg_price) * h.quantity ) AS unrealized_pnl,
CASE
WHEN h.avg_price > 0 THEN ((q.price - h.avg_price) / h.avg_price) * 100
ELSE NULL
END AS pnl_percentage,
fcc.commission_profile,
fcc.commission_rate,
h.is_active
FROM holdings h
JOIN portfolios p ON h.portfolio_id = p.id
LEFT JOIN etf_info ei ON h.isin = ei.isin
LEFT JOIN quotes q ON h.isin = q.isin
LEFT JOIN fineco_commission_cache fcc ON h.isin = fcc.isin;


### 4.2 Vista `v_portfolio_summary`

Usata per la card principale della dashboard.

CREATE VIEW v_portfolio_summary AS
SELECT
p.id AS portfolio_id,
p.user_id,
p.name AS portfolio_name,
p.base_currency,
COUNT(DISTINCT h.isin) AS holdings_count,
SUM(h.quantity * q.price) AS total_value,
SUM(h.quantity * h.avg_price) AS total_invested,
SUM( (q.price - h.avg_price) * h.quantity ) AS total_unrealized_pnl
FROM portfolios p
LEFT JOIN holdings h
ON p.id = h.portfolio_id
AND h.is_active = 1
LEFT JOIN quotes q
ON h.isin = q.isin
GROUP BY
p.id, p.user_id, p.name, p.base_currency;


---

## 5. Convenzioni di utilizzo

### 5.1 Filtri per `user_id` e `portfolio_id`

Tutte le query lato backend che leggono/modificano dati utente **devono** filtrare sempre per:

- `user_id` (dalla sessione utente)
- `portfolio_id` (validato tramite relazione con `user_id`)

Esempio (pseudo‑PHP):

$sql = "
SELECT * FROM v_holdings_full
WHERE user_id = :user_id
AND portfolio_id = :portfolio_id
AND is_active = 1
";


### 5.2 Aggiornamento holdings da transazioni

Logica standard:

- **BUY**
  - Nuova transazione:
    - aggiorna `quantity` = quantity + bought_quantity
    - aggiorna `avg_price` con media ponderata su quantità.
- **SELL**
  - Nuova transazione:
    - controlla che `quantity` attuale ≥ quantity venduta
    - aggiorna `quantity` = quantity - sold_quantity
    - aggiorna P&L realizzato in logica business (non necessariamente nel DB).
- **DIVIDEND**
  - Non modifica quantità.
  - Aggiorna solo storico transazioni e, se implementato, tabella dividendi dedicata.

Questa logica è implementata in PHP nelle `services/` e non direttamente nel DB (no trigger impliciti per mantenere il controllo applicativo).

### 5.3 Interazione con n8n

- n8n:
  - legge liste di ISIN e holdings tramite API backend o, in scenari controllati, direttamente su viste (con utente DB con permessi limitati in sola lettura).
  - aggiorna `quotes`, `etf_info`, `fineco_commission_cache`.
  - scrive i risultati delle analisi in `analysis_results` e/o tabelle di supporto tramite API autenticata HMAC.

### 5.4 Migrazioni

- Tutte le modifiche allo schema devono essere applicate tramite:
  - file SQL incrementali in `db/migrations/`
  - o tool di migrazione (semplice script PHP).
- `db/init.sql` contiene:
  - creazione database (se necessario)
  - creazione tabelle base, indici, viste.

---

## 6. Best practice e TODO

### 6.1 Best practice

- Sempre **prepared statements** per tutte le query.
- Indici solo dove servono (ISIN, portfolio_id, user_id, date).
- Nessun dato sensibile in chiaro (password solo come hash, API key solo in `.env`).
- Backup regolari:
  - `mysqldump` giornaliero del DB
  - snapshot dei volumi Docker in produzione.

### 6.2 TODO / possibili estensioni schema

- Tabella `dividends` separata con dettaglio per singola distribuzione.
- Tabella `fx_rates` per gestire in modo robusto multi‑valuta.
- Time‑series più dettagliate (prezzi storici giornalieri o intraday).
- Tabelle di log per gli eventi di sicurezza (login falliti, accessi negati).
