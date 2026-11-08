const fs = require('fs');
const locales = ['en', 'ar', 'hi', 'ur', 'bn'];
const data = {};
const allKeys = new Set();

function getKeys(obj, prefix = '') {
    const keys = [];
    for (const k of Object.keys(obj)) {
        const full = prefix ? `${prefix}.${k}` : k;
        if (typeof obj[k] === 'object' && obj[k] !== null) {
            keys.push(...getKeys(obj[k], full));
        } else {
            keys.push(full);
        }
    }
    return keys;
}

locales.forEach(loc => {
    data[loc] = new Set(getKeys(JSON.parse(fs.readFileSync(`resources/js/i18n/locales/${loc}.json`))));
    data[loc].forEach(k => allKeys.add(k));
});

console.log(`Total unique keys in all locale files combined: ${allKeys.size}`);

let perfect = true;
locales.forEach(loc => {
    const missing = [];
    allKeys.forEach(k => {
        if (!data[loc].has(k)) missing.push(k);
    });
    if (missing.length > 0) {
        console.log(`Locale ${loc} is missing ${missing.length} keys:`, missing.slice(0, 10));
        perfect = false;
    } else {
        console.log(`Locale ${loc} has 100% key parity (${data[loc].size} keys)`);
    }
});

if (perfect) {
    console.log('PERFECT PARITY: All 5 language files have 100% identical keys!');
}
