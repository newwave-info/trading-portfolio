# AI Technical Analysis Integration – Architecture & Workflow Guide

## Overview
This document defines how to integrate AI-generated technical insights into the ETF Portfolio Manager.  
The enrichment workflow and AI workflow remain separate to preserve determinism, reliability, and modularity.

---

# 1. High‑Level Architecture

## Workflow A — **Enrichment (Deterministic)**
Purpose: Fetch all market data, compute technical indicators, update DB.

Workflow does:
- Yahoo Finance → OHLCV
- Calculation of technical indicators (EMA, RSI, MACD, ATR, Fibonacci, range, volatility, Bollinger)
- Write snapshot into `holdings`
- Write historical trend into `technical_snapshots`
- Recalculate portfolio metrics / P&L

This workflow **does not call** any AI components.

---

## Workflow B — **AI Technical Insights (Separate, Non‑Deterministic)**
Purpose: Read technical data from DB and generate human‑readable insights using an LLM.

Workflow does:
- Retrieve compact technical context from DB
- Construct LLM prompt
- Query chosen AI model
- Store structured insights in DB (`technical_insights`)
- Expose insights to frontend

This workflow **never computes indicators** (only reads DB).

---

# 2. Required PHP API Endpoints

## 2.1 `/api/n8n/technical-context.php`  
Used **by n8n** before calling OpenAI.

### Responsibilities
- Read `holdings`
- Read last N days from `technical_snapshots` (recommended: 30)
- Produce minimal JSON structure:
  - current technical state
  - recent trend (price, RSI14, bb_percent_b, range percentile, etc.)
  - performance (1M, 1Y, YTD)
  - metadata (asset class, sector…)

---

## 2.2 `/api/n8n/ai_insights.php`  
Used **by n8n after** receiving the LLM output.

### Responsibilities
- Insert insight for portfolio (`scope = "portfolio"`)
- Insert insights per instrument (`scope = "instrument"`)
- Save JSON + human summary
- Optionally invalidate older insights

---

## 2.3 `/api/ai/technical-insights.php`  
Used **by frontend**.

### Responsibilities
- Read latest insights for the portfolio
- Return portfolio + instrument summaries for UI

---

# 3. Database Tables

## 3.1 `holdings`
Stores **current** technical indicators.

## 3.2 `technical_snapshots`
Stores **30–60 days** historical trend for AI.

Fields recommended:
- snapshot_date  
- price  
- rsi14  
- macd_value  
- macd_signal  
- hist_vol_30d  
- atr14_pct  
- range_1y_percentile  
- bb_percent_b  

## 3.3 `technical_insights`
Stores **LLM-generated insights**.

Fields:
- portfolio_id  
- isin (nullable)  
- scope (portfolio/instrument)  
- model  
- generated_at  
- raw_input_snapshot (json)  
- insight_json (json)  
- insight_text  

---

# 4. n8n Workflow “AI Technical Insights v1”

## Steps

1. Trigger (webhook or daily schedule)  
2. HTTP Request → `/api/n8n/technical-context.php`  
3. Code: build prompt  
4. OpenAI node  
5. HTTP Request → `/api/n8n/ai_insights.php`  
6. Frontend reads `/api/ai/technical-insights.php`

---

# 5. Prompt Design Guidelines

- Use system prompt for safety & role setup  
- User prompt = technical JSON  
- Output must be valid JSON  
- Never give trading advice  
- Never mention automation  
- Summaries must be concise  

---

# 6. Frontend Integration Strategy

- Show only 8–10 indicators in UI  
- Show AI-generated insights separately  
- Button “Rigenera analisi AI” → triggers n8n webhook  

---

# 7. Summary

You now have:

- Deterministic enrichment workflow  
- Historical trend snapshots  
- Separate AI workflow  
- Three PHP endpoints  
- Structure for insights storage  
- Clean integration path for frontend & LLM  
