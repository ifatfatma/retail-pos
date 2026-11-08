const fs = require('fs');
const path = require('path');

const keysToAdd = {
    en: {
        settings: {
            languages: "Languages & Localization",
            languages_desc: "Choose which languages are active on the website and select the default language.",
            enabled_languages: "Enabled Languages",
            default_language: "Default Website Language",
            default_language_desc: "The primary language used when a visitor visits the store.",
            language_switcher: "Show Language Switcher in Header",
            language_switcher_desc: "Allow visitors to switch languages from the website navigation bar.",
            at_least_one_language: "At least one language must remain enabled.",
            active_on_site: "Enabled on Website",
            disabled_on_site: "Disabled",
            language_preview: "Language Selector Preview"
        }
    },
    ar: {
        settings: {
            languages: "اللغات والتعريب",
            languages_desc: "اختر اللغات المفعلة على الموقع وحدد اللغة الافتراضية للمتجر.",
            enabled_languages: "اللغات المفعلة",
            default_language: "اللغة الافتراضية للموقع",
            default_language_desc: "اللغة الأساسية التي تظهر للزوار عند فتح المتجر لأول مرة.",
            language_switcher: "إظهار محدد اللغة في شريط الترويسة",
            language_switcher_desc: "السماح للزوار بتغيير لغة الموقع من شريط التنقل العلوي.",
            at_least_one_language: "يجب أن تظل لغة واحدة على الأقل مفعلة.",
            active_on_site: "مفعلة على الموقع",
            disabled_on_site: "معطلة",
            language_preview: "معاينة محدد اللغة"
        }
    },
    hi: {
        settings: {
            languages: "भाषाएं और स्थानीयकरण",
            languages_desc: "चुनें कि वेबसाइट पर कौन सी भाषाएं सक्रिय हैं और डिफ़ॉल्ट भाषा चुनें।",
            enabled_languages: "सक्रिय भाषाएं",
            default_language: "डिफ़ॉल्ट वेबसाइट भाषा",
            default_language_desc: "दुकान पर आने वाले आगंतुकों के लिए प्राथमिक भाषा।",
            language_switcher: "हेडर में भाषा स्विचर दिखाएं",
            language_switcher_desc: "आगंतुकों को वेबसाइट नेविगेशन बार से भाषा बदलने की अनुमति दें।",
            at_least_one_language: "कम से कम एक भाषा सक्रिय रहनी चाहिए।",
            active_on_site: "वेबसाइट पर सक्रिय",
            disabled_on_site: "निष्क्रिय",
            language_preview: "भाषा चयनकर्ता पूर्वावलोकन"
        }
    },
    ur: {
        settings: {
            languages: "زبانیں اور لوکلائزیشن",
            languages_desc: "منتخب کریں کہ ویب سائٹ پر کون سی زبانیں فعال ہیں اور ڈیفالٹ زبان کا انتخاب کریں۔",
            enabled_languages: "فعال زبانیں",
            default_language: "ویب سائٹ کی بنیادی زبان",
            default_language_desc: "اسٹور پر آنے والے صارفین کے لیے بنیادی زبان۔",
            language_switcher: "ہیڈر میں زبان سوئچر دکھائیں",
            language_switcher_desc: "صارفین کو ویب سائٹ نیویگیشن بار سے زبان تبدیل کرنے کی اجازت دیں۔",
            at_least_one_language: "کم از کم ایک زبان فعال رہنی چاہیے۔",
            active_on_site: "ویب سائٹ پر فعال",
            disabled_on_site: "غیر فعال",
            language_preview: "زبان سوئچر کا پیش نظارہ"
        }
    },
    bn: {
        settings: {
            languages: "ভাষা ও স্থানীয়করণ",
            languages_desc: "ওয়েবসাইটে কোন ভাষাগুলো সক্রিয় থাকবে তা নির্বাচন করুন এবং ডিফল্ট ভাষা নির্ধারণ করুন।",
            enabled_languages: "সক্রিয় ভাষাসমূহ",
            default_language: "ওয়েবসাইটের ডিফল্ট ভাষা",
            default_language_desc: "প্রথমবার স্টোরে আসা গ্রাহকদের জন্য প্রাথমিক ভাষা।",
            language_switcher: "হেডারে ভাষা পরিবর্তনকারী দেখান",
            language_switcher_desc: "গ্রাহকদের ওয়েবসাইটের নেভিগেশন বার থেকে ভাষা পরিবর্তনের সুযোগ দিন।",
            at_least_one_language: "কমপক্ষে একটি ভাষা অবশ্যই সক্রিয় থাকতে হবে।",
            active_on_site: "ওয়েবসাইটে সক্রিয়",
            disabled_on_site: "নিষ্ক্রিয়",
            language_preview: "ভাষা নির্বাচকের পূর্বরূপ"
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
    deepMerge(data, keysToAdd[loc]);
    fs.writeFileSync(file, JSON.stringify(data, null, 4), 'utf8');
    console.log(`Updated ${loc}.json with language settings keys`);
});
