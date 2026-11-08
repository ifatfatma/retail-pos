const fs = require('fs');
const path = require('path');

const newTranslations = {
    en: {
        brands: {
            brand_name_ar: "Brand Name (Arabic)",
            brand_name_ar_placeholder: "e.g. كلاسميت",
            description_ar: "Description (Arabic)",
            description_ar_placeholder: "Arabic description or overview..."
        },
        categories: {
            category_name_ar: "Category Name (Arabic)",
            category_name_ar_placeholder: "e.g. أدوات مكتبية",
            description_ar: "Description (Arabic)",
            description_ar_placeholder: "Arabic category description..."
        },
        units: {
            unit_name_ar: "Unit Name (Arabic)",
            unit_name_ar_placeholder: "e.g. قطعة",
            short_name_ar: "Short Name (Arabic)",
            short_name_ar_placeholder: "e.g. قطعة"
        },
        coupons: {
            name: "Promotion Name",
            name_ar: "Promotion Name (Arabic)",
            name_ar_placeholder: "e.g. خصم الصيف الكبير",
            description_ar: "Description (Arabic)",
            description_ar_placeholder: "Arabic promotional terms or description...",
            first_order_only: "First Order Only",
            coupon_code: "Coupon Code"
        },
        products: {
            sku: "SKU",
            purchase_price: "Purchase Price",
            product_name_ar: "Product Name (Arabic)",
            product_name_ar_placeholder: "e.g. دفتر مدرسي فاخر",
            description_ar: "Description (Arabic)",
            description_ar_placeholder: "Detailed product description in Arabic..."
        },
        purchases: {
            due_amount: "Due Amount"
        },
        orders: {
            payment_method: "Payment Method"
        },
        common: {
            clear_filters: "Clear Filters",
            save_settings: "Save Settings"
        },
        marketing: {
            customer_delivery: "Customer & Delivery Details",
            campaign_title_ar: "Campaign Title (Arabic)",
            title_ar_placeholder: "e.g. عروض نهاية الأسبوع الكبرى",
            campaign_description_ar: "Campaign Description (Arabic)",
            desc_ar_placeholder: "e.g. احصل على خصم 20% على جميع الأدوات المكتبية"
        }
    },
    ar: {
        brands: {
            brand_name_ar: "اسم العلامة التجارية (بالعربية)",
            brand_name_ar_placeholder: "مثال: كلاسميت",
            description_ar: "الوصف (بالعربية)",
            description_ar_placeholder: "وصف العلامة التجارية بالعربية..."
        },
        categories: {
            category_name_ar: "اسم الفئة (بالعربية)",
            category_name_ar_placeholder: "مثال: أدوات مكتبية",
            description_ar: "الوصف (بالعربية)",
            description_ar_placeholder: "وصف الفئة بالعربية..."
        },
        units: {
            unit_name_ar: "اسم الوحدة (بالعربية)",
            unit_name_ar_placeholder: "مثال: قطعة",
            short_name_ar: "الاسم المختصر (بالعربية)",
            short_name_ar_placeholder: "مثال: قطعة"
        },
        coupons: {
            name: "اسم العرض الترويجي",
            name_ar: "اسم العرض الترويجي (بالعربية)",
            name_ar_placeholder: "مثال: خصم الصيف الكبير",
            description_ar: "الوصف والشروط (بالعربية)",
            description_ar_placeholder: "الوصف والشروط الترويجية بالعربية...",
            first_order_only: "للطلب الأول فقط",
            coupon_code: "رمز القسيمة"
        },
        products: {
            sku: "رمز المنتج (SKU)",
            purchase_price: "سعر الشراء",
            product_name_ar: "اسم المنتج (بالعربية)",
            product_name_ar_placeholder: "مثال: دفتر مدرسي فاخر",
            description_ar: "الوصف (بالعربية)",
            description_ar_placeholder: "وصف المنتج التفصيلي بالعربية..."
        },
        purchases: {
            due_amount: "المبلغ المستحق"
        },
        orders: {
            payment_method: "طريقة الدفع"
        },
        common: {
            clear_filters: "إعادة تعيين الفلاتر",
            save_settings: "حفظ الإعدادات"
        },
        marketing: {
            customer_delivery: "تفاصيل العميل والتوصيل",
            campaign_title_ar: "عنوان الحملة (بالعربية)",
            title_ar_placeholder: "مثال: عروض نهاية الأسبوع الكبرى",
            campaign_description_ar: "وصف الحملة (بالعربية)",
            desc_ar_placeholder: "مثال: احصل على خصم 20% على جميع الأدوات المكتبية"
        }
    },
    hi: {
        brands: {
            brand_name_ar: "ब्रांड नाम (अरबी)",
            brand_name_ar_placeholder: "उदा. كلاسميت",
            description_ar: "विवरण (अरबी)",
            description_ar_placeholder: "अरबी में ब्रांड का विवरण..."
        },
        categories: {
            category_name_ar: "श्रेणी नाम (अरबी)",
            category_name_ar_placeholder: "उदा. أدوات مكتبية",
            description_ar: "विवरण (अरबी)",
            description_ar_placeholder: "अरबी में श्रेणी का विवरण..."
        },
        units: {
            unit_name_ar: "इकाई नाम (अरबी)",
            unit_name_ar_placeholder: "उदा. قطعة",
            short_name_ar: "संक्षिप्त नाम (अरबी)",
            short_name_ar_placeholder: "उदा. قطعة"
        },
        coupons: {
            name: "प्रमोशन नाम",
            name_ar: "प्रमोशन नाम (अरबी)",
            name_ar_placeholder: "उदा. خصم الصيف الكبير",
            description_ar: "विवरण (अरबी)",
            description_ar_placeholder: "अरबी में कूपन की शर्तें या विवरण...",
            first_order_only: "केवल पहले ऑर्डर पर",
            coupon_code: "कूपन कोड"
        },
        products: {
            sku: "एसकेयू (SKU)",
            purchase_price: "खरीद मूल्य",
            product_name_ar: "उत्पाद का नाम (अरबी)",
            product_name_ar_placeholder: "उदा. دفتر مدرسي فاخر",
            description_ar: "विवरण (अरबी)",
            description_ar_placeholder: "अरबी में उत्पाद का विस्तृत विवरण..."
        },
        purchases: {
            due_amount: "बकाया राशि"
        },
        orders: {
            payment_method: "भुगतान का तरीका"
        },
        common: {
            clear_filters: "फ़िल्टर हटाएं",
            save_settings: "सेटिंग्स सहेजें"
        },
        marketing: {
            customer_delivery: "ग्राहक और डिलीवरी विवरण",
            campaign_title_ar: "अभियान शीर्षक (अरबी)",
            title_ar_placeholder: "उदा. عروض نهاية الأسبوع الكبرى",
            campaign_description_ar: "अभियान विवरण (अरबी)",
            desc_ar_placeholder: "उदा. احصل على خصم 20% على جميع الأدوات المكتبية"
        }
    },
    ur: {
        brands: {
            brand_name_ar: "برانڈ نام (عربی)",
            brand_name_ar_placeholder: "مثلاً: كلاسميت",
            description_ar: "تفصیل (عربی)",
            description_ar_placeholder: "عربی میں برانڈ کی تفصیل..."
        },
        categories: {
            category_name_ar: "کیٹیگری نام (عربی)",
            category_name_ar_placeholder: "مثلاً: أدوات مكتبية",
            description_ar: "تفصیل (عربی)",
            description_ar_placeholder: "عربی میں کیٹیگری کی تفصیل..."
        },
        units: {
            unit_name_ar: "یونٹ نام (عربی)",
            unit_name_ar_placeholder: "مثلاً: قطعة",
            short_name_ar: "مختصر نام (عربی)",
            short_name_ar_placeholder: "مثلاً: قطعة"
        },
        coupons: {
            name: "پروموشن نام",
            name_ar: "پروموشن نام (عربی)",
            name_ar_placeholder: "مثلاً: خصم الصيف الكبير",
            description_ar: "تفصیل (عربی)",
            description_ar_placeholder: "عربی میں کوپن کی تفصیل اور شرائط...",
            first_order_only: "صرف پہلے آرڈر پر",
            coupon_code: "کوپن کوڈ"
        },
        products: {
            sku: "ایس کے یو (SKU)",
            purchase_price: "قیمت خرید",
            product_name_ar: "پروڈکٹ کا نام (عربی)",
            product_name_ar_placeholder: "مثلاً: دفتر مدرسي فاخر",
            description_ar: "تفصیل (عربی)",
            description_ar_placeholder: "عربی میں پروڈکٹ کی مکمل تفصیل..."
        },
        purchases: {
            due_amount: "بقایا رقم"
        },
        orders: {
            payment_method: "طریقہ ادائیگی"
        },
        common: {
            clear_filters: "فلٹرز صاف کریں",
            save_settings: "ترتیبات محفوظ کریں"
        },
        marketing: {
            customer_delivery: "کسٹمر اور ڈیلیوری تفصیلات",
            campaign_title_ar: "مہم کا عنوان (عربی)",
            title_ar_placeholder: "مثلاً: عروض نهاية الأسبوع الكبرى",
            campaign_description_ar: "مہم کی تفصیل (عربی)",
            desc_ar_placeholder: "مثلاً: احصل على خصم 20% على جميع الأدوات المكتبية"
        }
    },
    bn: {
        brands: {
            brand_name_ar: "ব্র্যান্ডের নাম (আরবি)",
            brand_name_ar_placeholder: "যেমন: كلاسميت",
            description_ar: "বিবরণ (আরবি)",
            description_ar_placeholder: "আরবিতে ব্র্যান্ডের বিবরণ..."
        },
        categories: {
            category_name_ar: "বিভাগের নাম (আরবি)",
            category_name_ar_placeholder: "যেমন: أدوات مكتبية",
            description_ar: "বিবরণ (আরবি)",
            description_ar_placeholder: "আরবিতে বিভাগের বিবরণ..."
        },
        units: {
            unit_name_ar: "ইউনিটের নাম (আরবি)",
            unit_name_ar_placeholder: "যেমন: قطعة",
            short_name_ar: "সংক্ষিপ্ত নাম (আরবি)",
            short_name_ar_placeholder: "যেমন: قطعة"
        },
        coupons: {
            name: "প্রমোশন নাম",
            name_ar: "প্রমোশন নাম (আরবি)",
            name_ar_placeholder: "যেমন: خصم الصيف الكبير",
            description_ar: "বিবরণ (আরবি)",
            description_ar_placeholder: "আরবিতে কুপনের বিবরণ ও শর্তাবলী...",
            first_order_only: "শুধুমাত্র প্রথম অর্ডারে",
            coupon_code: "কুপন কোড"
        },
        products: {
            sku: "এসকেইউ (SKU)",
            purchase_price: "ক্রয় মূল্য",
            product_name_ar: "পণ্যের নাম (আরবি)",
            product_name_ar_placeholder: "যেমন: دفتر مدرسي فاخر",
            description_ar: "বিবরণ (আরবি)",
            description_ar_placeholder: "আরবিতে পণ্যের বিস্তারিত বিবরণ..."
        },
        purchases: {
            due_amount: "বকেয়া পরিমাণ"
        },
        orders: {
            payment_method: "পরিশোধ পদ্ধতি"
        },
        common: {
            clear_filters: "ফিল্টার মুছুন",
            save_settings: "সেটিংস সংরক্ষণ করুন"
        },
        marketing: {
            customer_delivery: "গ্রাহক ও ডেলিভারি বিবরণ",
            campaign_title_ar: "প্রচারণার শিরোনাম (আরবি)",
            title_ar_placeholder: "যেমন: عروض نهاية الأسبوع الكبرى",
            campaign_description_ar: "প্রচারণার বিবরণ (আরবি)",
            desc_ar_placeholder: "যেমন: احصل على خصم 20% على جميع الأدوات المكتبية"
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
    deepMerge(data, newTranslations[loc]);
    fs.writeFileSync(file, JSON.stringify(data, null, 4), 'utf8');
    console.log(`Updated ${loc}.json`);
});

console.log('All missing translations added successfully!');
