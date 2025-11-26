// FIXED CLASSIFICATION LOGIC
// Priority: dividend BEFORE global/world
// Add 'div' check to capture "Div Lead." patterns

function classifyHolding(name, instrumentType) {
  const nameLower = name.toLowerCase();
  let asset_class = 'Unknown';
  let sector = 'Unknown';

  // Commodity classification
  if (nameLower.includes('gold') || nameLower.includes('silver') || instrumentType === 'ETC') {
    asset_class = 'Commodity';
    sector = nameLower.includes('gold') ? 'Gold' : 'Precious Metals';
  }
  // ETF classification - FIXED ORDER
  else if (instrumentType === 'ETF') {
    asset_class = 'Equity';

    // CHECK DIVIDEND FIRST (before global/world)
    if (nameLower.includes('dividend') || nameLower.includes('aristocrat') || nameLower.includes('div')) {
      sector = 'Dividend';
    }
    // Then check world/global
    else if (nameLower.includes('world') || nameLower.includes('global')) {
      sector = 'Global';
    }
    else if (nameLower.includes('u.s.') || nameLower.includes('s&p')) {
      sector = 'USA';
    }
    else {
      sector = 'Mixed';
    }
  }
  // Active funds classification
  else if (instrumentType === 'Fondo') {
    asset_class = 'Equity';

    if (nameLower.includes('biotech')) {
      sector = 'Healthcare';
    }
    else if (nameLower.includes('robot') || nameLower.includes('tech')) {
      sector = 'Technology';
    }
    else if (nameLower.includes('dividend')) {
      sector = 'Dividend';
    }
    else {
      sector = 'Active Fund';
    }
  }

  return { asset_class, sector };
}

// Usage in n8n Code nodes:
// const { asset_class, sector } = classifyHolding(holding.name, holding.instrument_type);
