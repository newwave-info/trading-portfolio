#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Read v2.2 workflow
const workflowPath = path.join(__dirname, 'n8n-portfolio-enrichment-v2.2.json');
const workflow = JSON.parse(fs.readFileSync(workflowPath, 'utf8'));

// FIXED classification logic (to replace in all 5 nodes)
const fixedETFLogic = `if (name.includes('dividend') || name.includes('aristocrat') || name.includes('div')) {
      sector = 'Dividend';
    } else if (name.includes('world') || name.includes('global')) {
      sector = 'Global';
    } else if (name.includes('u.s.') || name.includes('s&p')) {
      sector = 'USA';
    } else {
      sector = 'Mixed';
    }`;

// OLD logic to find and replace
const oldETFLogic = `if (name.includes('dividend') || name.includes('aristocrat')) {
      sector = 'Dividend';
    } else if (name.includes('world') || name.includes('global')) {
      sector = 'Global';
    } else if (name.includes('u.s.') || name.includes('s&p')) {
      sector = 'USA';
    } else {
      sector = 'Mixed';
    }`;

// Nodes to fix
const nodesToFix = [
  'TWD Success?',
  'FMP Success?',
  'Yahoo Success?',
  'JustETF Success?',
  'Fallback - Keep Existing'
];

let fixedCount = 0;

// Fix each node
workflow.nodes.forEach(node => {
  if (nodesToFix.includes(node.name)) {
    if (node.parameters && node.parameters.jsCode) {
      const oldCode = node.parameters.jsCode;

      // Replace old logic with fixed logic
      const newCode = oldCode.replace(oldETFLogic, fixedETFLogic);

      if (newCode !== oldCode) {
        node.parameters.jsCode = newCode;
        fixedCount++;
        console.log(`✅ Fixed classification in: ${node.name}`);
      } else {
        console.log(`⚠️  No changes in: ${node.name}`);
      }
    }
  }
});

// Update workflow metadata
workflow.name = 'Portfolio Enrichment v2.2 – Classification Fixed';
if (workflow.connections && workflow.connections['Aggregate & Sign']) {
  // Update workflow_id in Aggregate & Sign node
  const aggNode = workflow.nodes.find(n => n.name === 'Aggregate & Sign');
  if (aggNode && aggNode.parameters && aggNode.parameters.jsCode) {
    aggNode.parameters.jsCode = aggNode.parameters.jsCode.replace(
      'portfolio_enrichment_v2.1',
      'portfolio_enrichment_v2.2'
    );
  }
}

// Write fixed workflow
fs.writeFileSync(workflowPath, JSON.stringify(workflow, null, 2));

console.log(`\n🎉 Fixed ${fixedCount} nodes in workflow v2.2`);
console.log(`📁 Output: ${workflowPath}`);
