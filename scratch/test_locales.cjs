const fs = require('fs');
const { createI18n } = require('vue-i18n');

const locales = ['en', 'ar', 'hi', 'ur', 'bn'];
let hasErrors = false;

locales.forEach(loc => {
    const raw = fs.readFileSync(`resources/js/i18n/locales/${loc}.json`, 'utf8');
    const data = JSON.parse(raw);
    const i18n = createI18n({
        legacy: false,
        locale: loc,
        messages: { [loc]: data },
        missingWarn: false,
        fallbackWarn: false
    });

    function check(obj, path = '') {
        for (const k in obj) {
            const currentPath = path ? `${path}.${k}` : k;
            if (typeof obj[k] === 'object' && obj[k] !== null) {
                check(obj[k], currentPath);
            } else if (typeof obj[k] === 'string') {
                try {
                    i18n.global.t(currentPath);
                } catch (e) {
                    console.error(`ERROR [${loc}] at ${currentPath}:`, e.message);
                    hasErrors = true;
                }
            }
        }
    }
    check(data);
});

if (!hasErrors) {
    console.log('SUCCESS: All 5 locale files have ZERO syntax/compilation errors in vue-i18n!');
} else {
    process.exit(1);
}
