const fs = require('fs');
const path = require('path');

const footerKeys = {
    en: {
        storefront: {
            quick_links: "Quick Links",
            customer_service: "Customer Service",
            store_hours_support: "Store Hours & Support",
            powered_by: "Powered by Retail POS Live Inventory System",
            my_cart: "My Cart",
            my_wishlist: "My Wishlist",
            my_account: "My Account"
        }
    },
    ar: {
        storefront: {
            quick_links: "روابط سريعة",
            customer_service: "خدمة العملاء",
            store_hours_support: "ساعات العمل والدعم",
            powered_by: "مدعوم بنظام نقاط البيع وإدارة المخزون الفوري",
            my_cart: "سلتي",
            my_wishlist: "قائمة أمنياتي",
            my_account: "حسابي"
        }
    },
    hi: {
        storefront: {
            quick_links: "त्वरित लिंक",
            customer_service: "ग्राहक सेवा",
            store_hours_support: "दुकान का समय और सहायता",
            powered_by: "रिटेल पीओएस लाइव इन्वेंटरी सिस्टम द्वारा संचालित",
            my_cart: "मेरी कार्ट",
            my_wishlist: "मेरी विशलिस्ट",
            my_account: "मेरा खाता"
        }
    },
    ur: {
        storefront: {
            quick_links: "فوری لنکس",
            customer_service: "کسٹمر سروس",
            store_hours_support: "دکان کے اوقات اور معاونت",
            powered_by: "ریٹیل پی او ایس لائیو انوینٹری سسٹم کے ذریعے تقویت یافتہ",
            my_cart: "میری ٹوکری",
            my_wishlist: "میری پسندیدہ فہرست",
            my_account: "میرا اکاؤنٹ"
        }
    },
    bn: {
        storefront: {
            quick_links: "দ্রুত লিঙ্ক",
            customer_service: "গ্রাহক সেবা",
            store_hours_support: "দোকানের সময় ও সহায়তা",
            powered_by: "রিটেল পিওএস লাইভ ইনভেন্টরি সিস্টেম দ্বারা চালিত",
            my_cart: "আমার কার্ট",
            my_wishlist: "আমার পছন্দের তালিকা",
            my_account: "আমার অ্যাকাউন্ট"
        }
    }
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
    deepMerge(data, footerKeys[loc]);
    fs.writeFileSync(file, JSON.stringify(data, null, 4), 'utf8');
    console.log(`Updated footer keys in ${loc}.json`);
});
