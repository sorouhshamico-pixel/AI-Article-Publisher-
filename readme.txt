=== AI Article Publisher & SEO Scheduler ===
Contributors: mohamed-khalifa
Tags: articles, seo, scheduling, featured-image, yoast, duplicate-prevention
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

إضافة مخصصة لنقل المقالات المنظمة إلى WordPress مع SEO والجدولة والصورة البارزة والسجل ومنع التكرار.

المطور: محمد خليفة
مستقل: https://mostaql.com/u/M1_m2/portfolio

== Description ==

تسمح الإضافة بلصق حزمة [ARTICLE_DATA] + [CONTENT]، تحليلها، مراجعتها، ثم إنشاء مقال WordPress كمسودة أو مجدول أو منشور.

تشمل الوظائف:
* عنوان، Slug، تصنيف، وسوم، مقتطف وتاريخ نشر.
* Focus Keyword وSEO Title وMeta Description.
* تكامل اختياري مع Yoast SEO.
* رفع صورة بارزة مع Filename وTitle وAlt وCaption وDescription.
* سجل دائم للمقالات ومحاولات التكرار.
* منع التكرار عبر Slug والعنوان وبصمة المحتوى.
* تنظيف آثار الاستشهادات الداخلية المنسوخة من ChatGPT.
* فحوص صلاحيات وNonce وتنقية HTML والتحقق من الصور.

راجع README.md داخل المشروع للتوثيق الكامل.

== Installation ==

1. ارفع ملف ZIP من إضافات > أضف إضافة جديدة > رفع إضافة.
2. فعّل الإضافة.
3. افتح "AI Article Publisher".
4. الصق الحزمة واضغط "تحليل المحتوى".
5. راجع البيانات والصورة ثم أنشئ المقال.

== Changelog ==

= 0.4.0 =
* تغيير الاسم إلى AI Article Publisher & SEO Scheduler.
* تحديث اسم الحزمة والمجلد والتوثيق والبرومبت بالهوية الجديدة.
* الحفاظ على معرفات البيانات القديمة للتوافق مع سجلات الإصدارات السابقة.

= 0.3.1 =
* إضافة دليل رسمي لصيغة حزمة المقال.
* إضافة برومبت جاهز لإنشاء مقالات متوافقة مع الإضافة.
* عرض البرومبت داخل صفحة الإضافة لسهولة النسخ والاستخدام.

= 0.3.0 =
* مراجعة أمنية موسعة للصلاحيات وطلبات POST ورفع الصور.
* قصر واجهة الإدارة على edit_others_posts.
* تحقق مستقل من publish_posts وupload_files وmanage_categories.
* فحص نوع وحجم الصورة قبل الرفع.
* حدود لحجم إدخال المقال والصورة.
* توثيق README.md وSECURITY.md وCHANGELOG.md وGPL LICENSE.
* إضافة رابط المطور محمد خليفة على مستقل.

= 0.2.1 =
* تنظيف آثار ChatGPT الداخلية قبل الحفظ.

= 0.2.0 =
* سجل دائم ومنع المقالات المكررة.
