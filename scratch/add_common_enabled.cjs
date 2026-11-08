const fs = require('fs');
const path = require('path');

const commonKeys = {
    en: { common: { enabled: "Enabled", disabled: "Disabled" } },
    ar: { common: { enabled: "مفعل", disabled: "معطل" } },
    hi: { common: { enabled: "सक्रिय", disabled: "निष्क्रिय" } },
    ur: { common: { enabled: "فعال", disabled: "غیر فعال" } },
    bn: { common: { enabled: "সক্রিয়", disabled: "নিষ্ক্রিয়" } }
};

function deepMerge(target, source) {
    for (const key of Object.keys(source)) {
        if (source[key] instanceof Object && key in target) {
            Object.assign(source[key], deepMerge(target[key], source[key]));
        }
    }
    Object.assign(target || {}, source);
    return target;
}

const locales = ['en', 'ar', 'hi', 'ur', 'bn'];
locales.forEach(loc => {
    const file = path.join(__dirname, '..', 'resources', 'js', 'i18n', 'locales', `${loc}.json`);
    const data = JSON.parse(fs.readFileSync(file, 'utf8'));
    deepMerge(data, commonKeys[loc]);
    fs.writeFileSync(file, JSON.stringify(data, null, 4), 'utf8');
    console.log(`Updated common in ${loc}.json`);
});
