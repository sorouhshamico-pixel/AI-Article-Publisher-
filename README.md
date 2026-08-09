<a id="readme-top"></a>

<!-- PROJECT SHIELDS -->
[![License][license-shield]][license-url]
[![PHP Version][php-shield]][php-url]
[![WordPress][wordpress-shield]][wordpress-url]
[![Plugin Version][version-shield]][changelog-url]

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <h3 align="center">AI Article Publisher & SEO Scheduler</h3>

  <p align="center">
    إضافة WordPress مخصصة لتحويل حزمة مقال منظمة إلى مقال جاهز داخل WordPress، مع بيانات SEO، الجدولة، الصورة البارزة، سجل دائم للمقالات، ومنع التكرار قبل الحفظ.
    <br />
    <a href="docs/ARTICLE-FORMAT.md"><strong>استكشف دليل صيغة المقال »</strong></a>
    <br />
    <br />
    <a href="docs/article-package-example.txt">عرض مثال</a>
    &middot;
    <a href="https://github.com/sorouhshamico-pixel/AI-Article-Publisher-/issues/new?labels=bug">الإبلاغ عن خلل</a>
    &middot;
    <a href="https://github.com/sorouhshamico-pixel/AI-Article-Publisher-/issues/new?labels=enhancement">طلب ميزة</a>
  </p>
</div>

<!-- TABLE OF CONTENTS -->
<details>
  <summary>جدول المحتويات</summary>
  <ol>
    <li>
      <a href="#نبذة-عن-المشروع">نبذة عن المشروع</a>
      <ul>
        <li><a href="#الوظائف-الرئيسية">الوظائف الرئيسية</a></li>
        <li><a href="#مبني-باستخدام">مبني باستخدام</a></li>
      </ul>
    </li>
    <li>
      <a href="#البدء">البدء</a>
      <ul>
        <li><a href="#المتطلبات">المتطلبات</a></li>
        <li><a href="#التثبيت">التثبيت</a></li>
      </ul>
    </li>
    <li><a href="#طريقة-الاستخدام">طريقة الاستخدام</a></li>
    <li><a href="#الصلاحيات">الصلاحيات</a></li>
    <li><a href="#خارطة-الطريق">خارطة الطريق</a></li>
    <li><a href="#الأمان-والخصوصية">الأمان والخصوصية</a></li>
    <li><a href="#استكشاف-الأخطاء">استكشاف الأخطاء</a></li>
    <li><a href="#المساهمة">المساهمة</a></li>
    <li><a href="#الترخيص">الترخيص</a></li>
    <li><a href="#تواصل">تواصل</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->
## نبذة عن المشروع

**AI Article Publisher & SEO Scheduler** إضافة WordPress تستقبل حزمة مقال منظمة (نص تلصقه يدويًا من ChatGPT أو أي مصدر آخر) وتحوّلها مباشرة إلى مقال جاهز داخل الموقع: عنوان، Slug، تصنيف، وسوم، بيانات SEO، صورة بارزة، وجدولة نشر، مع فحص خادم يمنع تكرار المقال قبل الحفظ.

| | |
|---|---|
| **الإصدار** | 0.4.0 |
| **المطور** | محمد خليفة |
| **الترخيص** | GPL-2.0-or-later |
| **WordPress** | 6.4 أو أحدث |
| **PHP** | 8.0 أو أحدث |

> الإضافة لا تتصل بـ OpenAI API ولا ترسل محتوى الموقع إلى خدمات خارجية. هي تستقبل النص الذي تلصقه يدويًا داخل لوحة WordPress وتعالجه محليًا داخل الموقع.

### تغيير الاسم في الإصدار 0.4.0

تم تغيير الاسم التجاري للإضافة من **Sorouh Article Publisher** إلى **AI Article Publisher & SEO Scheduler** ليعكس وظيفتها بصورة أوضح وقابلة للاستخدام في أي موقع WordPress.

للحفاظ على التوافق مع الإصدارات السابقة، تم الإبقاء داخليًا على بعض معرفات قاعدة البيانات وأسماء الإجراءات القديمة. هذا مقصود حتى لا تضيع سجلات المقالات أو بيانات الإضافة عند التحديث. الاسم الظاهر للمستخدم واسم حزمة التثبيت والمجلد أصبح بالاسم الجديد.

### الوظائف الرئيسية

- لصق المقال كاملًا في حزمة واحدة وتحليل الحقول تلقائيًا.
- دعم HTML وMarkdown البسيط.
- إنشاء المقال كمسودة أو نشره أو جدولته.
- حفظ العنوان، Slug، التصنيف، الوسوم، المقتطف وتاريخ النشر.
- حفظ Focus Keyword وSEO Title وMeta Description.
- تكامل اختياري مع Yoast SEO عند اكتشافه.
- تنظيف آثار استشهادات ChatGPT الداخلية مثل `contentReference[oaicite:...]` قبل الحفظ.
- رفع صورة بارزة مع اسم ملف SEO مخصص.
- تعبئة Title وAlt Text وCaption وDescription للصورة.
- سجل دائم للمقالات التي أُنشئت بواسطة الإضافة.
- تسجيل محاولات المقالات المكررة التي تم منعها.
- منع التكرار اعتمادًا على Slug والعنوان وبصمة المحتوى.
- فحص مقالات WordPress الموجودة أيضًا، وليس سجل الإضافة فقط.

### مبني باستخدام

- [WordPress Plugin API](https://developer.wordpress.org/plugins/)
- PHP
- JavaScript (Vanilla)

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- GETTING STARTED -->
## البدء

### المتطلبات

- WordPress 6.4 أو أحدث.
- PHP 8.0 أو أحدث.
- صلاحيات مناسبة على الموقع لتفعيل الإضافات (راجع قسم [الصلاحيات](#الصلاحيات)).

### التثبيت

**من ملف ZIP:**

1. افتح لوحة تحكم WordPress.
2. انتقل إلى **إضافات > أضف إضافة جديدة > رفع إضافة**.
3. اختر ملف `ai-article-publisher-seo-scheduler-v0.4.0.zip`.
4. اضغط **التثبيت الآن**.
5. فعّل الإضافة.
6. ستظهر قائمة **AI Article Publisher** في لوحة التحكم للمستخدمين المخولين.

**تثبيت يدوي:**

انسخ مجلد:

```text
ai-article-publisher-seo-scheduler
```

إلى:

```text
/wp-content/plugins/
```

ثم فعّل الإضافة من لوحة WordPress.

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- USAGE EXAMPLES -->
## طريقة الاستخدام

### المرجع الرسمي لصيغة المقال وبرومبت الذكاء الاصطناعي

هذا الجزء أساسي للاستخدام الصحيح للإضافة. لا تعتمد فقط على المثال المختصر أدناه.

- [دليل صيغة المقال الكامل](docs/ARTICLE-FORMAT.md): يشرح بنية `[ARTICLE_DATA]` و`[CONTENT]`، كل حقل، HTML، الروابط، الصورة، الجدولة، والأخطاء التي يجب تجنبها.
- [برومبت AI جاهز](docs/AI-PROMPT-TEMPLATE.md): برومبت كامل يمكن نسخه إلى ChatGPT أو أي نموذج ذكاء اصطناعي لإنتاج مقال متوافق مباشرة مع الإضافة.
- [مثال حزمة](docs/article-package-example.txt): مثال بسيط جاهز للتجربة.

يوصى دائمًا بـ:

```text
CONTENT_FORMAT:
html
```

ويجب أن تكون نتيجة النموذج **الحزمة فقط** من `[ARTICLE_DATA]` حتى `[/CONTENT]`، بدون مقدمة خارجية، وبدون Markdown داخل المحتوى، وبدون أي `contentReference` أو `oaicite` أو رموز استشهاد داخلية.

الإضافة تعرض أيضًا **برومبتًا جاهزًا داخل صفحة ناشر المقالات** حتى يمكن نسخه مباشرة من لوحة WordPress.

### صيغة حزمة المقال

```text
[ARTICLE_DATA]
TITLE: صب الخرسانة في الأجواء الحارة بالرياض
SLUG: concrete-pouring-hot-weather-riyadh
CATEGORY: الجودة والمعايير
TAGS: خرسانة جاهزة, صب الخرسانة, الرياض
FOCUS_KEYWORD: صب الخرسانة في الأجواء الحارة
SEO_TITLE: صب الخرسانة في الأجواء الحارة بالرياض | أهم الاحتياطات
META_DESCRIPTION: دليل عملي لصب الخرسانة في الأجواء الحارة بالرياض.
EXCERPT: ملخص قصير للمقال.
PUBLISH_DATE: 2026-08-15 09:00
CONTENT_FORMAT: html
IMAGE_FILENAME: concrete-pouring-hot-weather-riyadh.jpg
IMAGE_TITLE: صب الخرسانة في الأجواء الحارة بالرياض
IMAGE_ALT: صب الخرسانة الجاهزة في مشروع بمدينة الرياض
IMAGE_CAPTION: أعمال صب الخرسانة الجاهزة بالرياض
IMAGE_DESCRIPTION: صورة توضح أعمال صب الخرسانة الجاهزة في الرياض.
[/ARTICLE_DATA]

[CONTENT]
<p>مقدمة المقال هنا.</p>
<h2>عنوان فرعي</h2>
<p>محتوى المقال...</p>
[/CONTENT]
```

يمكن كتابة الحقل والقيمة في السطر نفسه (`TITLE: عنوان المقال`) أو في سطرين منفصلين.

<details>
<summary>شرح الحقول الكامل</summary>

| الحقل | الوظيفة |
|---|---|
| `TITLE` | عنوان مقال WordPress |
| `SLUG` | الاسم اللطيف للرابط |
| `CATEGORY` | التصنيف |
| `TAGS` | الوسوم مفصولة بفاصلة عربية أو إنجليزية |
| `FOCUS_KEYWORD` | العبارة المفتاحية الرئيسية |
| `SEO_TITLE` | عنوان SEO |
| `META_DESCRIPTION` | الوصف التعريفي |
| `EXCERPT` | مقتطف المقال |
| `PUBLISH_DATE` | تاريخ ووقت النشر حسب المنطقة الزمنية للموقع |
| `CONTENT_FORMAT` | `html` أو `markdown` |
| `IMAGE_FILENAME` | اسم الملف المقترح للصورة |
| `IMAGE_TITLE` | عنوان الصورة في مكتبة الوسائط |
| `IMAGE_ALT` | النص البديل |
| `IMAGE_CAPTION` | الكلمات التوضيحية |
| `IMAGE_DESCRIPTION` | وصف الصورة |

</details>

### طريقة الاستخدام اليومية

1. افتح **ناشر مقالات صروح**.
2. الصق الحزمة كاملة في مربع **لصق المقال**.
3. اضغط **تحليل المحتوى**.
4. راجع الحقول التي تم استخراجها.
5. راجع محتوى المقال داخل المحرر.
6. اختر الحالة: مسودة، مجدول، أو نشر الآن.
7. إذا كان المقال مجدولًا، تأكد من تاريخ ووقت النشر.
8. أضف الصورة البارزة إذا كانت جاهزة، أو اتركها فارغة وأضفها لاحقًا.
9. راجع قسم **فحص قبل الإنشاء**.
10. اضغط **إنشاء المقال**.

### الصورة البارزة

الصورة اختيارية عند إنشاء المقال. الأنواع المدعومة: JPG/JPEG، PNG، WebP. يتم التحقق من نوع الملف وحجمه قبل محاولة حفظه — الحد الفعلي هو الأقل بين حد WordPress للرفع و12 ميجابايت. يمكن تحديد اسم ملف SEO في `IMAGE_FILENAME`؛ إذا كان الامتداد المقترح لا يطابق امتداد الملف الحقيقي، تحافظ الإضافة على امتداد الملف المرفوع.

### منع التكرار

قبل إنشاء المقال يتم الفحص على الخادم، وليس في JavaScript فقط. يتم البحث عن:

1. Slug مطابق في مقالات WordPress الحالية.
2. عنوان مطابق في مقالات WordPress الحالية.
3. عنوان أو Slug سبق تسجيله في سجل الإضافة.
4. بصمة SHA-256 للمحتوى بعد التطبيع والتنظيف.

إذا وجدت مطابقة، تمنع الإضافة إنشاء المقال وتسجل محاولة الرفض، لتفادي إنشاء WordPress لـ Slug بديل مثل `article-slug-2` عندما يكون المقال في الأصل مكررًا.

### سجل المقالات

من **ناشر مقالات صروح > سجل المقالات** يمكن مشاهدة آخر السجلات: المقالات التي تم إنشاؤها، المقالات المكررة التي تم منعها، العنوان وSlug، حالة المقال، سبب المنع عند وجود تكرار، رقم المقال المرتبط، المستخدم الذي نفذ العملية، ووقت إنشاء السجل.

السجل محفوظ في جدول WordPress مخصص (`{prefix}_sap_article_log`) ولا يُحذف تلقائيًا عند حذف المقال من WordPress، لأن الهدف منه الاحتفاظ بتاريخ المقالات ومنع إعادة إدخال مقال سبق استخدامه.

### Yoast SEO

تحتفظ الإضافة بنسخة مستقلة من بيانات SEO في Post Meta الخاصة بها (`_sap_focus_keyword`, `_sap_seo_title`, `_sap_meta_description`)، وعند اكتشاف Yoast SEO يتم أيضًا مزامنة القيم المتوافقة مع حقوله، مع استخدام فلاتر Yoast لعرض SEO Title وMeta Description المخصصين.

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

## الصلاحيات

صُممت الإضافة وفق مبدأ أقل صلاحية مناسب لسير العمل:

- صفحة الإضافة وسجل المقالات تتطلب صلاحية `edit_others_posts`، أي أنها موجهة عادةً للمحررين والمديرين.
- النشر المباشر أو الجدولة يتطلبان `publish_posts`. إذا لم تتوفر الصلاحية يتم خفض الحالة إلى مسودة.
- رفع الصور يتطلب `upload_files`.
- إنشاء تصنيف جديد يتطلب `manage_categories`. إذا لم تتوفر الصلاحية فلن تنشئ الإضافة تصنيفًا جديدًا تلقائيًا.

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- ROADMAP -->
## خارطة الطريق

- [x] لصق حزمة مقال وتحليل تلقائي للحقول
- [x] منع التكرار على مستوى الخادم (Slug / عنوان / بصمة محتوى)
- [x] تكامل اختياري مع Yoast SEO
- [x] سجل دائم للمقالات والمحاولات المرفوضة
- [ ] دعم إضافات SEO إضافية (Rank Math)
- [ ] واجهة مراجعة جماعية لعدة حزم مقالات

راجع [Issues المفتوحة](https://github.com/sorouhshamico-pixel/AI-Article-Publisher-/issues) لقائمة الميزات المقترحة والمشكلات المعروفة، و[CHANGELOG.md](CHANGELOG.md) للتاريخ الكامل للإصدارات.

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

## الأمان والخصوصية

- فحص صلاحيات المستخدم قبل عرض الصفحات وتنفيذ الإنشاء، وNonce على طلب إنشاء المقال للحماية من CSRF.
- Sanitization للحقول النصية وSlugs والوسوم وبيانات الصور، و`wp_kses_post()` لتنقية HTML المقال قبل الحفظ.
- Escape للمخرجات المعروضة في لوحة الإدارة، وWhitelist صريح لحالات المقال وتنسيق المحتوى.
- فحص صلاحية رفع الملفات، وفحص خطأ الرفع والحجم وMIME للصورة قبل المعالجة (JPG/PNG/WebP فقط).
- استخدام WordPress APIs لإنشاء المقالات والوسائط والمصطلحات، واستعلامات مجهزة `prepare()` عند إدخال قيم متغيرة في SQL.
- منع النشر أو الجدولة لمن لا يملك `publish_posts`، ومنع إنشاء تصنيفات جديدة لمن لا يملك `manage_categories`.
- لا توجد مفاتيح API أو كلمات مرور أو أسرار مخزنة داخل كود الإضافة، ولا توجد اتصالات HTTP خارجية تنفذها الإضافة.
- السجل محفوظ في قاعدة بيانات WordPress نفسها فقط؛ لا تُرسل المقالات أو السجلات إلى أي خدمة خارجية.

راجع أيضًا [SECURITY.md](SECURITY.md).

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

## استكشاف الأخطاء

<details>
<summary>ظهر Slug ينتهي بـ <code>-2</code></summary>
<br>
يفترض أن تمنع الإضافة إنشاء المقال المكرر قبل هذه المرحلة. تأكد من أن الإضافة محدثة وأن إنشاء المقال يتم من خلال واجهتها.
</details>

<details>
<summary>لم يتم إنشاء التصنيف</summary>
<br>
تأكد أن المستخدم يملك صلاحية <code>manage_categories</code>، أو أن التصنيف موجود مسبقًا.
</details>

<details>
<summary>تم حفظ المقال كمسودة رغم اختيار النشر</summary>
<br>
المستخدم لا يملك <code>publish_posts</code>، لذلك تم خفض الحالة إلى مسودة كإجراء أمان.
</details>

<details>
<summary>الصورة لم تُرفع</summary>
<br>
تحقق من نوع الصورة (JPG/PNG/WebP)، حجم الملف، حد الرفع المسموح في WordPress/PHP، وصلاحية <code>upload_files</code> للمستخدم.
</details>

<details>
<summary>بيانات Yoast لا تظهر كما هو متوقع</summary>
<br>
تأكد من تفعيل Yoast ثم افتح المقال واحفظه مرة واحدة عند الحاجة لتحديث فهرسة Yoast، خصوصًا بعد تحديثات Yoast الرئيسية.
</details>

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- CONTRIBUTING -->
## المساهمة

المساهمات هي ما يجعل مجتمع المصادر المفتوحة مكانًا رائعًا للتعلم والإلهام والإبداع. أي مساهمة تُقدَّر **بشدة**.

إذا كان لديك اقتراح لتحسين المشروع، الرجاء عمل fork للمستودع وإنشاء pull request. يمكنك أيضًا فتح issue بالوسم "enhancement". لا تنسَ إعطاء المشروع نجمة! شكرًا مجددًا!

1. اعمل Fork للمشروع
2. أنشئ فرع الميزة الخاص بك (`git checkout -b feature/AmazingFeature`)
3. فحوص أساسية قبل الالتزام:
   ```bash
   php -l ai-article-publisher-seo-scheduler.php
   node --check assets/admin.js
   ```
4. اعمل Commit للتغييرات (`git commit -m 'Add some AmazingFeature'`)
5. ارفع الفرع (`git push origin feature/AmazingFeature`)
6. افتح Pull Request

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- LICENSE -->
## الترخيص

هذا المشروع مرخص وفق GPL-2.0-or-later. راجع ملف [LICENSE](LICENSE) لمزيد من التفاصيل.

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- CONTACT -->
## تواصل

**محمد خليفة** — مستقل: https://mostaql.com/u/M1_m2/portfolio

رابط المشروع: [https://github.com/sorouhshamico-pixel/AI-Article-Publisher-](https://github.com/sorouhshamico-pixel/AI-Article-Publisher-)

<p align="right">(<a href="#readme-top">للأعلى ↑</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
[license-shield]: https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg
[license-url]: LICENSE
[php-shield]: https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg
[php-url]: https://www.php.net/
[wordpress-shield]: https://img.shields.io/badge/WordPress-6.4%2B-21759b.svg
[wordpress-url]: https://wordpress.org/
[version-shield]: https://img.shields.io/badge/version-0.4.0-success.svg
[changelog-url]: CHANGELOG.md
