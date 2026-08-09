# Security Policy

## Supported version

النسخة المدعومة حاليًا أمنيًا هي آخر إصدار موجود في المستودع. الإصدار الأول الذي يتضمن مراجعة أمان موسعة هو `0.3.0`.

## Security controls

- Authorization عبر WordPress capabilities.
- CSRF protection عبر nonce والتحقق في الخادم.
- Server-side validation, sanitization, and output escaping.
- HTML filtering عبر `wp_kses_post()`.
- Explicit allowlists لحالة المقال وتنسيق المحتوى وأنواع الصور.
- Image MIME/extension validation وحدود للحجم.
- SQL variable values تمر عبر `$wpdb->prepare()` حيث توجد مدخلات متغيرة.
- لا توجد اتصالات خارجية أو مفاتيح API في الإضافة.

## Reporting a vulnerability

يرجى عدم نشر ثغرة أمنية بشكل علني قبل إتاحة فرصة لمعالجتها.

المطور: **محمد خليفة**  
مستقل: https://mostaql.com/u/M1_m2/portfolio

عند الإبلاغ، يفضل إرسال:

- إصدار WordPress وPHP.
- إصدار الإضافة.
- خطوات إعادة المشكلة.
- نوع حساب المستخدم وصلاحياته.
- Screenshots أو Request/Response منقحة من أي أسرار.

## Secrets

لا تضف إلى المستودع أيًا من التالي:

- كلمات مرور WordPress أو الاستضافة.
- مفاتيح OpenAI/API.
- Application Passwords.
- ملفات `.env`.
- نسخ قواعد بيانات أو ملفات `wp-config.php`.

إذا تم نشر سر بالخطأ، يجب إلغاؤه وتدويره فورًا؛ حذف commit وحده لا يعتبر كافيًا.
