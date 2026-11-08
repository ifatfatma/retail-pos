const fs = require('fs');
const path = require('path');

const locales = ['en', 'ar', 'hi', 'ur', 'bn'];
const dicts = {};
for (const l of locales) {
    dicts[l] = JSON.parse(fs.readFileSync(path.join(__dirname, '..', 'resources', 'js', 'i18n', 'locales', `${l}.json`), 'utf8'));
}

function getVal(obj, keyPath) {
    const parts = keyPath.split('.');
    let curr = obj;
    for (const p of parts) {
        if (!curr || typeof curr !== 'object') return undefined;
        curr = curr[p];
    }
    return curr;
}

const vueDir = path.join(__dirname, '..', 'resources', 'js');
const allKeys = new Set();

function scanDir(dir) {
    const files = fs.readdirSync(dir);
    for (const f of files) {
        const full = path.join(dir, f);
        const stat = fs.statSync(full);
        if (stat.isDirectory()) {
            if (f !== 'node_modules' && f !== 'dist' && f !== '.git') {
                scanDir(full);
            }
        } else if (f.endsWith('.vue') || f.endsWith('.js')) {
            const content = fs.readFileSync(full, 'utf8');
            const matches = content.matchAll(/(?:\$t|\bt)\(\s*['"]([a-zA-Z0-9_.]+)['"]/g);
            for (const m of matches) {
                allKeys.add(m[1]);
            }
        }
    }
}

scanDir(vueDir);

console.log(`Total unique translation keys used in source code: ${allKeys.size}`);

const missing = {};
for (const l of locales) {
    missing[l] = [];
    for (const k of allKeys) {
        const val = getVal(dicts[l], k);
        if (val === undefined || val === '') {
            missing[l].push(k);
        }
    }
}

const ignore = new Set(['close', 'iframe', 'meta', 'style']);
for (const l of locales) {
    const realMissing = missing[l].filter(k => !ignore.has(k));
    console.log(`Language ${l}: ${realMissing.length} real missing keys:`);
    console.log(realMissing);
}
