import { createI18n } from 'vue-i18n';
import bootstrapRtlCss from 'bootstrap/dist/css/bootstrap.rtl.min.css?inline';

import en from './locales/en.json';
import ar from './locales/ar.json';
import hi from './locales/hi.json';
import ur from './locales/ur.json';
import bn from './locales/bn.json';

export const STORAGE_KEY = 'app_language';

export const supportedLocales = [
    {
        code: 'en',
        name: 'English',
        nativeName: 'English',
        dir: 'ltr',
        flag: '🇬🇧',
    },
    {
        code: 'ar',
        name: 'Arabic',
        nativeName: 'العربية',
        dir: 'rtl',
        flag: '🇸🇦',
    },
    {
        code: 'hi',
        name: 'Hindi',
        nativeName: 'हिन्दी',
        dir: 'ltr',
        flag: '🇮🇳',
    },
    {
        code: 'ur',
        name: 'Urdu',
        nativeName: 'اردو',
        dir: 'rtl',
        flag: '🇵🇰',
    },
    {
        code: 'bn',
        name: 'Bengali',
        nativeName: 'বাংলা',
        dir: 'ltr',
        flag: '🇧🇩',
    },
];

export function isRtl(localeCode) {
    return localeCode === 'ar' || localeCode === 'ur';
}

export function getSavedLocale() {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved && supportedLocales.some(l => l.code === saved)) {
            return saved;
        }
    } catch (e) {
        console.error('Failed to read language preference from localStorage:', e);
    }
    return 'en';
}

export const i18n = createI18n({
    legacy: false,
    locale: getSavedLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        ar,
        hi,
        ur,
        bn,
    },
});

// Centralized dynamic RTL / LTR stylesheet and DOM manager
function applyDocumentDirection(localeCode) {
    if (typeof document === 'undefined') return;

    const rtl = isRtl(localeCode);
    const html = document.documentElement;

    html.setAttribute('lang', localeCode);
    html.setAttribute('dir', rtl ? 'rtl' : 'ltr');

    if (rtl) {
        document.body.classList.add('rtl-mode');
        document.body.classList.remove('ltr-mode');

        // Inject genuine Bootstrap RTL stylesheet dynamically if not already injected
        let styleTag = document.getElementById('bootstrap-rtl-dynamic');
        if (!styleTag) {
            styleTag = document.createElement('style');
            styleTag.id = 'bootstrap-rtl-dynamic';
            styleTag.textContent = bootstrapRtlCss;
            document.head.appendChild(styleTag);
        }
    } else {
        document.body.classList.remove('rtl-mode');
        document.body.classList.add('ltr-mode');

        // Remove dynamic Bootstrap RTL stylesheet to cleanly restore pure LTR
        const styleTag = document.getElementById('bootstrap-rtl-dynamic');
        if (styleTag) {
            styleTag.remove();
        }
    }
}

export function setLanguage(localeCode) {
    const valid = supportedLocales.find(l => l.code === localeCode);
    const target = valid ? valid.code : 'en';

    if (i18n.global.locale.value !== undefined) {
        i18n.global.locale.value = target;
    } else {
        i18n.global.locale = target;
    }

    try {
        localStorage.setItem(STORAGE_KEY, target);
    } catch (e) {
        console.error('Failed to save language preference:', e);
    }

    applyDocumentDirection(target);

    // Optional event dispatch for any listener
    window.dispatchEvent(new CustomEvent('app:language-changed', { detail: { locale: target, isRtl: isRtl(target) } }));

    return target;
}

export function getCurrentLanguage() {
    return i18n.global.locale.value || i18n.global.locale || 'en';
}

// Locale-aware formatters
export function formatCurrency(amount, currency = 'INR') {
    const num = Number(amount || 0);
    const loc = getCurrentLanguage();
    try {
        return new Intl.NumberFormat(loc === 'en' ? 'en-IN' : loc, {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(num);
    } catch (e) {
        return `₹${num.toFixed(2)}`;
    }
}

export function formatNumber(val, options = {}) {
    const num = Number(val || 0);
    const loc = getCurrentLanguage();
    try {
        return new Intl.NumberFormat(loc === 'en' ? 'en-IN' : loc, options).format(num);
    } catch (e) {
        return String(num);
    }
}

export function formatDate(dateVal, options = { year: 'numeric', month: 'short', day: 'numeric' }) {
    if (!dateVal) return '-';
    const loc = getCurrentLanguage();
    try {
        return new Intl.DateTimeFormat(loc, options).format(new Date(dateVal));
    } catch (e) {
        return String(dateVal);
    }
}

// Initialise document direction immediately upon module load
if (typeof window !== 'undefined') {
    applyDocumentDirection(getSavedLocale());
}

export default i18n;
