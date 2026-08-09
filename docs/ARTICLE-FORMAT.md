# صيغة المقال المتوافقة مع AI Article Publisher & SEO Scheduler

هذه الوثيقة هي المرجع الرسمي لصيغة النص الذي تقبله الإضافة.

## البنية الأساسية

يجب أن يحتوي النص على قسمين فقط:

```text
[ARTICLE_DATA]
... بيانات المقال ...
[/ARTICLE_DATA]

[CONTENT]
... محتوى المقال ...
[/CONTENT]
```

الإضافة تبحث عن العلامات الأربع كما هي. يفضّل عدم تغيير الأحرف أو إضافة مسافات داخل أسماء العلامات.

## الحقول المدعومة

```text
TITLE:
SLUG:
CATEGORY:
TAGS:
FOCUS_KEYWORD:
SEO_TITLE:
META_DESCRIPTION:
EXCERPT:
PUBLISH_DATE:
CONTENT_FORMAT:
IMAGE_FILENAME:
IMAGE_TITLE:
IMAGE_ALT:
IMAGE_CAPTION:
IMAGE_DESCRIPTION:
```

### الحقول الأساسية

- `TITLE`: عنوان مقال WordPress.
- `SLUG`: الاسم اللطيف للرابط، ويفضل الإنجليزية lowercase مع `-` بين الكلمات.
- `CONTENT_FORMAT`: استخدم `html` أو `markdown`. للاستخدام مع الذكاء الاصطناعي يوصى بـ `html` لأنه الأكثر ثباتًا.
- `[CONTENT]`: نص المقال نفسه.

### حقول SEO

- `FOCUS_KEYWORD`: العبارة المفتاحية الرئيسية.
- `SEO_TITLE`: عنوان SEO.
- `META_DESCRIPTION`: الوصف التعريفي.
- `EXCERPT`: مقتطف المقال.

### التصنيف والوسوم

- `CATEGORY`: تصنيف واحد.
- `TAGS`: عدة وسوم مفصولة بفاصلة عربية `،` أو إنجليزية `,`.

### الجدولة

`PUBLISH_DATE` يجب أن يكون بالتنسيق:

```text
YYYY-MM-DD HH:MM
```

مثال:

```text
2026-08-15 09:00
```

ويُفسّر التاريخ وفق المنطقة الزمنية المضبوطة في WordPress.

### بيانات الصورة

- `IMAGE_FILENAME`: اسم الملف المقترح قبل الرفع، مثل `ready-mix-concrete-riyadh.jpg`.
- `IMAGE_TITLE`: عنوان الصورة في مكتبة الوسائط.
- `IMAGE_ALT`: النص البديل.
- `IMAGE_CAPTION`: الكلمات التوضيحية.
- `IMAGE_DESCRIPTION`: وصف الصورة.

الصورة نفسها اختيارية ويمكن إضافتها بعد إنشاء المقال.

## الشكل الموصى به للإنتاج

يوصى باستخدام HTML:

```text
CONTENT_FORMAT:
html
```

ومحتوى مثل:

```html
<p>مقدمة المقال.</p>
<h2>عنوان القسم</h2>
<p>محتوى القسم.</p>
<h3>عنوان فرعي</h3>
<ul>
<li>نقطة أولى</li>
<li>نقطة ثانية</li>
</ul>
```

لا تضف `<h1>` داخل `[CONTENT]` لأن `TITLE` هو عنوان الصفحة الرئيسي في WordPress.

## الروابط الداخلية

اكتب الرابط داخل السياق الطبيعي:

```html
<p>يمكنك مراجعة <a href="https://example.com/internal-page/">دليل الخدمة</a> لمعرفة التفاصيل.</p>
```

لا تستخدم رابطًا غير موجود فعليًا في الموقع.

## الروابط الخارجية

يفضّل استخدام مصادر رسمية وموثوقة:

```html
<a href="https://example.gov/official-source" target="_blank" rel="noopener">المصدر الرسمي</a>
```

## ممنوعات داخل الحزمة

لا ترسل داخل `[CONTENT]`:

```text
contentReference[oaicite:2]{index=2}
```

أو أي `oaicite` / `contentReference` / citation داخلي خاص بمنصة الذكاء الاصطناعي.

الإضافة تحتوي على طبقة تنظيف لهذه الآثار، لكن يجب اعتبار التنظيف شبكة أمان وليس جزءًا من صيغة المقال.

إذا كان `CONTENT_FORMAT: html` فلا تستخدم علامات Markdown مثل:

```text
#
##
**النص**
```

ولا تضع Markdown code fences حول الحزمة عند نسخها إلى WordPress.

## مثال كامل

```text
[ARTICLE_DATA]
TITLE:
دليل اختيار الخرسانة الجاهزة بالرياض

SLUG:
ready-mix-concrete-selection-riyadh

CATEGORY:
توريد الخرسانة

TAGS:
خرسانة جاهزة, الرياض, توريد الخرسانة

FOCUS_KEYWORD:
اختيار الخرسانة الجاهزة بالرياض

SEO_TITLE:
اختيار الخرسانة الجاهزة بالرياض | دليل عملي

META_DESCRIPTION:
دليل عملي يساعدك على اختيار الخرسانة الجاهزة المناسبة لمشروعك في الرياض.

EXCERPT:
تعرف على أهم النقاط التي يجب مراجعتها قبل طلب الخرسانة الجاهزة للمشروع.

PUBLISH_DATE:
2026-08-15 09:00

CONTENT_FORMAT:
html

IMAGE_FILENAME:
ready-mix-concrete-selection-riyadh.jpg

IMAGE_TITLE:
اختيار الخرسانة الجاهزة بالرياض

IMAGE_ALT:
شاحنة خرسانة جاهزة في موقع مشروع بمدينة الرياض

IMAGE_CAPTION:
توريد الخرسانة الجاهزة لأحد المشاريع في الرياض

IMAGE_DESCRIPTION:
صورة توضح توريد الخرسانة الجاهزة إلى موقع مشروع في مدينة الرياض.

[/ARTICLE_DATA]

[CONTENT]
<p>مقدمة المقال...</p>
<h2>كيف تختار الخرسانة المناسبة؟</h2>
<p>محتوى المقال...</p>
[/CONTENT]
```

## كيف تتأكد أن الحزمة صحيحة؟

بعد لصقها في الإضافة اضغط **تحليل المحتوى**. يجب أن يظهر في فحص ما قبل الإنشاء أن العنوان وSlug والمحتوى وSEO والتصنيف وموعد الجدولة جاهزة حسب البيانات التي أرسلتها.
