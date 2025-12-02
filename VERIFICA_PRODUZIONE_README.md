# 📋 Guida Verifica Produzione - Strategia Operativa v2

## 🚀 File di Test

Hai a disposizione 2 file per la verifica in produzione:

### 1. `verifica_produzione.php` - Test Completo
Test esaustivo con interfaccia web, include:
- ✅ Verifica ambiente PHP
- ✅ Test database struttura
- ✅ Analisi dati holdings
- ✅ Test SignalGeneratorService
- ✅ Statistiche sistema
- ✅ Log dettagliati

### 2. `test_rapido.php` - Test Veloce
Test leggero per verifiche rapide via terminale

## 🔧 Come Eseguire i Test

### Opzione A: Test Web Completo
```bash
# Carica il file sul server
scp verifica_produzione.php user@tuo-server:/path/to/trading-portfolio/

# Accedi via browser
https://tuo-dominio.com/verifica_produzione.php
```

### Opzione B: Test via Terminale
```bash
# SSH sul server
ssh user@tuo-server

# Vai nella directory
cd /path/to/trading-portfolio

# Test rapido
php test_rapido.php

# Test rapido con segnali
php test_rapido.php?signals=1

# Test completo via CLI
php verifica_produzione.php
```

## 📊 Cosa Verificare

### ✅ Segni di Successo:
1. **Connessione Database**: ✅ Nessun errore di connessione
2. **Strutture Complete**: ✅ Tutte le tabelle e views presenti
3. **Dati Holdings**: ✅ Asset class, role e target allocation corretti
4. **Signal Generator**: ✅ Segnali generati con confidence > 50
5. **Performance**: ✅ Tempo di esecuzione < 1000ms

### ⚠️ Segni di Attenzione:
1. **Confidence bassa**: Segnali con confidence < 50 (normali ma poco utili)
2. **Nessun segnale**: Potrebbe essere normale se non ci sono condizioni favorevoli
3. **Performance lenta**: > 2 secondi (verificare indicizzazione)

### ❌ Errori Critici:
1. **Connessione DB fallita**: Verificare configurazione
2. **Class not found**: Verificare include paths
3. **Foreign key errors**: Verificare integrità database
4. **Memory limit**: Aumentare memory_limit in php.ini

## 🎯 Obiettivi Minimi

Prima di procedere con le fasi successive, assicurati che:

1. ✅ **Database connesso** senza errori
2. ✅ **Strutture presenti** (tabelle + views)
3. ✅ **Dati holdings** popolati correttamente
4. ✅ **SignalGenerator** non genera errori PHP
5. ✅ **Log directory** scrivibile

## 🔍 Debugging

### Verifica Log
```bash
# Guarda i log in tempo reale
tail -f logs/verifica_produzione.log

# Controlla errori PHP
 tail -f /var/log/php_errors.log
```

### Test Database Diretto
```sql
-- Verifica struttura
SHOW TABLES LIKE '%recommendation%';
SHOW TABLES LIKE '%holding%';

-- Verifica views
SELECT * FROM v_active_recommendations LIMIT 5;
SELECT * FROM v_recommendation_performance LIMIT 5;

-- Verifica dati
SELECT ticker, asset_class, role, target_allocation_pct
FROM holdings WHERE portfolio_id = 1;
```

### Test Signal Generator
```php
// Test isolato
require_once 'lib/Services/SignalGeneratorService.php';
$signalGen = new SignalGeneratorService($recRepo, $holdRepo, 1);
$signals = $signalGen->generateSignals();
```

## 📈 Prossimi Passi

Dopo una verifica **✅ SUCCESSO**:

1. **Fase 3**: Test approfonditi SignalGeneratorService
2. **Fase 4**: Implementare API REST endpoints
3. **Fase 5**: Configurare workflow n8n
4. **Monitoraggio**: Setup dashboard e alert

Dopo una verifica **❌ FALLITA**:

1. Correggi gli errori identificati
2. Riesegui i test
3. Condividi i log per debugging

## 🆘 Supporto

Se riscontri errori:

1. **Copia l'output completo** del test
2. **Condividi il log file** `logs/verifica_produzione.log`
3. **Specifica l'ambiente**: PHP version, MySQL version, OS
4. **Descrivi il problema** dettagliatamente

**Buon testing!** 🚀