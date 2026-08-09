<?php
/**
 * Plugin Name: AI Article Publisher & SEO Scheduler
 * Description: Import structured AI-written articles into WordPress with SEO metadata, scheduling, featured images, duplicate prevention, and article logs.
 * Version: 0.4.0
 * Author: محمد خليفة
 * Author URI: https://mostaql.com/u/M1_m2/portfolio
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Text Domain: ai-article-publisher-seo-scheduler
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Sorouh_Article_Publisher {
    const VERSION = '0.4.0';
    const CAPABILITY = 'edit_others_posts';
    const MAX_ARTICLE_BYTES = 2097152; // 2 MB input safety limit.
    const MAX_IMAGE_BYTES   = 12582912; // 12 MB, also bounded by WordPress upload limit.
    const MENU_SLUG = 'sorouh-article-publisher';
    const LOG_SLUG  = 'sorouh-article-publisher-log';
    const DB_VERSION = '1.0';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_sap_create_article', [ $this, 'handle_create_article' ] );
        add_action( 'admin_init', [ $this, 'maybe_upgrade_database' ] );
        add_filter( 'wpseo_title', [ $this, 'filter_yoast_title' ], 20, 1 );
        add_filter( 'wpseo_metadesc', [ $this, 'filter_yoast_metadesc' ], 20, 1 );
    }

    public function admin_menu() {
        add_menu_page(
            'AI Article Publisher & SEO Scheduler',
            'AI Article Publisher',
            self::CAPABILITY,
            self::MENU_SLUG,
            [ $this, 'render_page' ],
            'dashicons-edit-page',
            25
        );

        add_submenu_page(
            self::MENU_SLUG,
            'سجل المقالات',
            'سجل المقالات',
            self::CAPABILITY,
            self::LOG_SLUG,
            [ $this, 'render_logs_page' ]
        );
    }

    public function enqueue_assets( $hook ) {
        $allowed = [ 'toplevel_page_' . self::MENU_SLUG, self::MENU_SLUG . '_page_' . self::LOG_SLUG ];
        if ( ! in_array( $hook, $allowed, true ) ) { return; }
        wp_enqueue_style( 'sap-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.css', [], self::VERSION );
        wp_enqueue_script( 'sap-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.js', [], self::VERSION, true );
    }

    private function sample_package() {
        return <<<TXT
[ARTICLE_DATA]
TITLE: مثال عنوان المقال
SLUG: example-article-slug
CATEGORY: توريد الخرسانة
TAGS: خرسانة جاهزة, خرسانة الرياض
FOCUS_KEYWORD: الخرسانة الجاهزة بالرياض
SEO_TITLE: مثال عنوان SEO | صروح الشامي
META_DESCRIPTION: وصف ميتا مختصر ومحسن لمحركات البحث.
EXCERPT: ملخص قصير للمقال.
PUBLISH_DATE: 2026-08-15 09:00
CONTENT_FORMAT: html
IMAGE_FILENAME: ready-mix-concrete-riyadh.jpg
IMAGE_TITLE: توريد خرسانة جاهزة بالرياض
IMAGE_ALT: توريد خرسانة جاهزة إلى مشروع في الرياض
IMAGE_CAPTION: توريد الخرسانة الجاهزة لأحد المشاريع في الرياض
IMAGE_DESCRIPTION: صورة توضح أعمال توريد الخرسانة الجاهزة في الرياض.
[/ARTICLE_DATA]

[CONTENT]
<h2>مقدمة</h2>
<p>اكتب محتوى المقال هنا.</p>

<h2>عنوان فرعي</h2>
<p>يمكن استخدام النص المنسق والقوائم والروابط.</p>
[/CONTENT]
TXT;
    }

    private function ai_prompt_template() {
        return <<<'PROMPT'
اكتب مقالًا عربيًا احترافيًا ومحسنًا لمحركات البحث ومتوافقًا بالكامل مع إضافة WordPress "AI Article Publisher & SEO Scheduler".

قواعد الإخراج الإلزامية:
1. أخرج الحزمة فقط من [ARTICLE_DATA] حتى [/CONTENT]، ولا تكتب أي مقدمة أو ملاحظات خارجها.
2. استخدم جميع أسماء الحقول كما هي بالإنجليزية وبدون تغيير.
3. اجعل CONTENT_FORMAT: html.
4. لا تستخدم Markdown داخل [CONTENT]؛ ممنوع # و## و** و```.
5. لا تضع H1 داخل المحتوى؛ عنوان WordPress في TITLE هو H1. ابدأ العناوين الداخلية بـ <h2> ثم <h3> عند الحاجة.
6. استخدم HTML نظيفًا فقط داخل [CONTENT]: <p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <a>.
7. أدرج الروابط الداخلية المطلوبة كرابط HTML كامل داخل السياق الطبيعي للمقال.
8. أدرج الروابط الخارجية الموثوقة كرابط HTML كامل، واستخدم target="_blank" rel="noopener" للروابط الخارجية فقط.
9. لا تضع أي استشهادات أو رموز داخلية مثل contentReference أو oaicite أو citation أو مراجع خاصة بمنصة الذكاء الاصطناعي.
10. لا تكرر TITLE داخل [CONTENT].
11. SLUG باللغة الإنجليزية، lowercase، والكلمات مفصولة بشرطة - فقط.
12. SEO_TITLE واضح ومناسب لمحركات البحث.
13. META_DESCRIPTION وصف جذاب ومختصر للمقال.
14. EXCERPT ملخص مستقل وقصير.
15. TAGS مفصولة بفاصلة إنجليزية أو عربية.
16. PUBLISH_DATE بالتنسيق YYYY-MM-DD HH:MM.
17. IMAGE_FILENAME اسم إنجليزي SEO مع الامتداد .jpg.
18. IMAGE_ALT يصف الصورة طبيعيًا ولا يحشو الكلمات المفتاحية.
19. IMAGE_TITLE وIMAGE_CAPTION وIMAGE_DESCRIPTION يجب أن تكون مرتبطة مباشرة بموضوع الصورة والمقال.
20. لا تخترع روابط داخلية. استخدم فقط الروابط التي أزودك بها. إذا لم أزودك برابط مناسب، لا تضف رابطًا وهميًا.
21. لا تخترع مصدرًا خارجيًا أو معلومة رقمية غير موثقة.

استخدم القالب التالي حرفيًا:

[ARTICLE_DATA]
TITLE:
{عنوان المقال}

SLUG:
{english-seo-slug}

CATEGORY:
{التصنيف}

TAGS:
{وسم 1, وسم 2, وسم 3}

FOCUS_KEYWORD:
{الكلمة المفتاحية الرئيسية}

SEO_TITLE:
{عنوان SEO}

META_DESCRIPTION:
{وصف Meta}

EXCERPT:
{مقتطف المقال}

PUBLISH_DATE:
{YYYY-MM-DD HH:MM}

CONTENT_FORMAT:
html

IMAGE_FILENAME:
{seo-image-filename.jpg}

IMAGE_TITLE:
{عنوان الصورة}

IMAGE_ALT:
{النص البديل}

IMAGE_CAPTION:
{الكلمات التوضيحية}

IMAGE_DESCRIPTION:
{وصف الصورة}

[/ARTICLE_DATA]

[CONTENT]
<p>مقدمة المقال...</p>
<h2>عنوان فرعي...</h2>
<p>...</p>
[/CONTENT]

بيانات المقال المطلوب:
العنوان/الفكرة: {اكتب الفكرة هنا}
الكلمة المفتاحية الرئيسية: {اكتبها هنا}
التصنيف: {اكتب التصنيف هنا}
تاريخ النشر: {YYYY-MM-DD HH:MM}
الروابط الداخلية المسموح استخدامها: {ضع الروابط هنا}
المصادر الخارجية المسموح استخدامها: {ضع الروابط هنا}
متطلبات إضافية: {عدد الكلمات، الجمهور، المدينة، نبرة الكتابة، FAQ، إلخ}
PROMPT;
    }

    public function render_page() {
        if ( ! current_user_can( self::CAPABILITY ) ) { wp_die( 'غير مصرح.' ); }
        $notice = isset( $_GET['sap_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['sap_notice'] ) ) : '';
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
        $error = isset( $_GET['sap_error'] ) ? sanitize_text_field( wp_unslash( $_GET['sap_error'] ) ) : '';
        ?>
        <div class="wrap sap-wrap" dir="rtl">
            <h1>AI Article Publisher & SEO Scheduler</h1>
            <p class="description">ألصق الحزمة التي أجهزها لك، راجع البيانات المستخرجة، أضف الصورة إن أردت، ثم أنشئ المقال كمسودة أو مجدول أو منشور.</p>
            <p class="description"><strong>مطور الإضافة:</strong> محمد خليفة</p>

            <?php if ( $notice ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?><?php if ( $post_id ) : ?> — <a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">فتح المقال</a><?php endif; ?></p></div>
            <?php endif; ?>
            <?php if ( $error ) : ?>
                <div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error ); ?><?php if ( $post_id && get_post( $post_id ) ) : ?> — <a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">فتح المقال الموجود</a><?php endif; ?></p></div>
            <?php endif; ?>

            <div class="sap-grid">
                <section class="sap-card sap-card-main">
                    <h2>1. لصق المقال</h2>
                    <textarea id="sap-package" rows="24" placeholder="ألصق المقال الكامل هنا..."></textarea>
                    <div class="sap-actions">
                        <button type="button" class="button button-primary" id="sap-parse">تحليل المحتوى</button>
                        <button type="button" class="button" id="sap-sample">إدراج مثال</button>
                        <button type="button" class="button-link-delete" id="sap-clear">مسح</button>
                    </div>
                    <details class="sap-help"><summary>تنسيق الحزمة المدعوم</summary><pre id="sap-sample-text"><?php echo esc_html( $this->sample_package() ); ?></pre></details>
                    <details class="sap-help"><summary>برومبت جاهز لإنشاء مقال متوافق مع الإضافة</summary><p>انسخ البرومبت التالي إلى ChatGPT أو أي نموذج ذكاء اصطناعي، ثم عدّل بيانات المقال والروابط والتاريخ حسب الحاجة.</p><textarea rows="22" readonly dir="rtl" style="width:100%;font-family:monospace;white-space:pre-wrap;"><?php echo esc_textarea( $this->ai_prompt_template() ); ?></textarea></details>
                </section>

                <form class="sap-card" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="sap-form">
                    <input type="hidden" name="action" value="sap_create_article">
                    <?php wp_nonce_field( 'sap_create_article', 'sap_nonce' ); ?>
                    <input type="hidden" name="raw_package" id="raw_package">

                    <h2>2. بيانات المقال</h2>
                    <div class="sap-fields">
                        <?php $this->text_field( 'title', 'عنوان المقال', true ); ?>
                        <?php $this->text_field( 'slug', 'Slug', true, 'ltr' ); ?>
                        <?php $this->text_field( 'category', 'التصنيف' ); ?>
                        <?php $this->text_field( 'tags', 'الوسوم (مفصولة بفاصلة)' ); ?>
                        <?php $this->text_field( 'focus_keyword', 'الكلمة المفتاحية الرئيسية' ); ?>
                        <?php $this->text_field( 'seo_title', 'SEO Title' ); ?>
                        <?php $this->textarea_field( 'meta_description', 'Meta Description', 3 ); ?>
                        <?php $this->textarea_field( 'excerpt', 'المقتطف', 3 ); ?>
                        <?php $this->text_field( 'publish_date', 'تاريخ ووقت النشر', false, 'ltr', 'مثال: 2026-08-15 09:00' ); ?>
                        <label>حالة المقال
                            <select name="post_status" id="post_status">
                                <option value="draft">مسودة</option>
                                <option value="future" selected>مجدول</option>
                                <option value="publish">نشر الآن</option>
                            </select>
                        </label>
                        <label>تنسيق المحتوى
                            <select name="content_format" id="content_format">
                                <option value="markdown">Markdown / نص من ChatGPT</option>
                                <option value="html">HTML</option>
                            </select>
                        </label>
                    </div>

                    <h2>3. محتوى المقال</h2>
                    <p class="description">يظهر المحتوى في المحرر المرئي بدون علامات Markdown مثل # أو **.</p>
                    <?php
                    wp_editor( '', 'content', [
                        'textarea_name' => 'content',
                        'textarea_rows' => 18,
                        'media_buttons' => false,
                        'teeny'         => false,
                        'quicktags'     => true,
                        'tinymce'       => true,
                    ] );
                    ?>

                    <div class="sap-image-box">
                        <h2>4. الصورة البارزة</h2>
                        <p class="description">اختياري الآن. يمكنك إنشاء المقال بدون صورة ثم إضافتها لاحقًا.</p>
                        <label class="sap-upload">اختر صورة جديدة
                            <input type="file" name="featured_image" id="featured_image" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <div class="sap-fields sap-image-fields">
                            <?php $this->text_field( 'image_filename', 'اسم الملف المقترح', false, 'ltr' ); ?>
                            <?php $this->text_field( 'image_title', 'عنوان الصورة' ); ?>
                            <?php $this->textarea_field( 'image_alt', 'النص البديل Alt Text', 2 ); ?>
                            <?php $this->textarea_field( 'image_caption', 'Caption / الكلمات التوضيحية', 2 ); ?>
                            <?php $this->textarea_field( 'image_description', 'وصف الصورة', 3 ); ?>
                        </div>
                    </div>

                    <div class="sap-check" id="sap-check">
                        <h2>فحص قبل الإنشاء</h2>
                        <ul id="sap-check-list"><li>ألصق المقال واضغط «تحليل المحتوى».</li></ul>
                    </div>

                    <button type="submit" class="button button-primary button-hero" id="sap-submit">إنشاء المقال</button>
                </form>
            </div>
        </div>
        <script>window.SAP_SAMPLE = <?php echo wp_json_encode( $this->sample_package() ); ?>;</script>
        <?php
    }

    private function text_field( $name, $label, $required = false, $dir = 'rtl', $placeholder = '' ) {
        printf(
            '<label>%s<input type="text" name="%s" id="%s" dir="%s" %s placeholder="%s"></label>',
            esc_html( $label ), esc_attr( $name ), esc_attr( $name ), esc_attr( $dir ), $required ? 'required' : '', esc_attr( $placeholder )
        );
    }

    private function textarea_field( $name, $label, $rows = 3 ) {
        printf( '<label>%s<textarea name="%s" id="%s" rows="%d"></textarea></label>', esc_html( $label ), esc_attr( $name ), esc_attr( $name ), absint( $rows ) );
    }

    private function post_value( $key, $default = '' ) {
        if ( ! isset( $_POST[ $key ] ) || is_array( $_POST[ $key ] ) ) {
            return $default;
        }
        return wp_unslash( $_POST[ $key ] );
    }

    private function enforce_input_limits( $title, $content_raw ) {
        if ( strlen( (string) $content_raw ) > self::MAX_ARTICLE_BYTES ) {
            wp_die( esc_html__( 'حجم محتوى المقال أكبر من الحد المسموح به داخل الإضافة.', 'sorouh-article-publisher' ) );
        }
        if ( function_exists( 'mb_strlen' ) ? mb_strlen( $title, 'UTF-8' ) > 300 : strlen( $title ) > 900 ) {
            wp_die( esc_html__( 'عنوان المقال أطول من الحد المسموح به.', 'sorouh-article-publisher' ) );
        }
    }

    private function validate_image_upload_request() {
        if ( empty( $_FILES['featured_image']['name'] ) ) {
            return true;
        }
        if ( ! current_user_can( 'upload_files' ) ) {
            return new WP_Error( 'sap_upload_capability', 'ليس لديك صلاحية رفع الملفات.' );
        }
        if ( ! isset( $_FILES['featured_image']['error'], $_FILES['featured_image']['tmp_name'], $_FILES['featured_image']['size'] ) ) {
            return new WP_Error( 'sap_invalid_upload', 'بيانات رفع الصورة غير مكتملة.' );
        }
        if ( UPLOAD_ERR_OK !== (int) $_FILES['featured_image']['error'] ) {
            return new WP_Error( 'sap_upload_error', 'فشل رفع الصورة. رمز الخطأ: ' . absint( $_FILES['featured_image']['error'] ) );
        }
        $max_size = min( (int) wp_max_upload_size(), self::MAX_IMAGE_BYTES );
        if ( (int) $_FILES['featured_image']['size'] <= 0 || (int) $_FILES['featured_image']['size'] > $max_size ) {
            return new WP_Error( 'sap_upload_size', 'حجم الصورة غير مسموح. الحد الأقصى: ' . size_format( $max_size ) . '.' );
        }
        $allowed = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'webp'         => 'image/webp',
        ];
        $name = sanitize_file_name( wp_unslash( $_FILES['featured_image']['name'] ) );
        $check = wp_check_filetype_and_ext( $_FILES['featured_image']['tmp_name'], $name, $allowed );
        if ( empty( $check['type'] ) || ! in_array( $check['type'], array_values( $allowed ), true ) ) {
            return new WP_Error( 'sap_upload_type', 'نوع الصورة غير مسموح. الأنواع المدعومة: JPG وPNG وWebP.' );
        }
        return true;
    }

    public function handle_create_article() {
        if ( ! current_user_can( self::CAPABILITY ) ) { wp_die( 'غير مصرح.' ); }
        check_admin_referer( 'sap_create_article', 'sap_nonce' );

        $title = sanitize_text_field( $this->post_value( 'title' ) );
        $slug  = sanitize_title( $this->post_value( 'slug' ) );
        $content_raw = $this->post_value( 'content' );
        // Remove ChatGPT/internal citation artifacts before any validation, duplicate check, or save.
        $content_raw = $this->clean_chatgpt_artifacts( $content_raw );
        $format = sanitize_key( $this->post_value( 'content_format', 'markdown' ) );
        $status = sanitize_key( $this->post_value( 'post_status', 'draft' ) );
        $publish_date = sanitize_text_field( $this->post_value( 'publish_date' ) );

        $this->enforce_input_limits( $title, $content_raw );
        if ( '' === $title || '' === trim( $content_raw ) ) { wp_die( esc_html__( 'العنوان والمحتوى مطلوبان.', 'sorouh-article-publisher' ) ); }
        if ( ! in_array( $status, [ 'draft', 'future', 'publish' ], true ) ) { $status = 'draft'; }
        if ( in_array( $status, [ 'future', 'publish' ], true ) && ! current_user_can( 'publish_posts' ) ) {
            $status = 'draft';
        }
        if ( ! in_array( $format, [ 'markdown', 'html' ], true ) ) { $format = 'markdown'; }

        $image_validation = $this->validate_image_upload_request();
        if ( is_wp_error( $image_validation ) ) {
            wp_die( esc_html( $image_validation->get_error_message() ) );
        }

        $content = ( 'html' === $format ) ? wp_kses_post( $content_raw ) : $this->markdown_to_html( $content_raw );
        $content = $this->clean_chatgpt_artifacts( $content );

        // Authoritative server-side duplicate protection. WordPress would otherwise silently create slugs such as "-2".
        $duplicate = $this->find_duplicate_article( $title, $slug, $content );
        if ( $duplicate ) {
            $this->log_event( [
                'event_type'           => 'duplicate_blocked',
                'post_id'              => 0,
                'title'                => $title,
                'slug'                 => $slug,
                'focus_keyword'        => sanitize_text_field( $this->post_value( 'focus_keyword' ) ),
                'post_status'          => $status,
                'content_hash'         => $this->content_hash( $content ),
                'duplicate_reason'     => implode( ' + ', $duplicate['reasons'] ),
                'duplicate_of_post_id' => (int) $duplicate['post_id'],
            ] );

            $message = 'تم منع إنشاء المقال لأنه مكرر. سبب المطابقة: ' . implode( '، ', $duplicate['reasons'] ) . '.';
            wp_safe_redirect( add_query_arg( [
                'page'      => self::MENU_SLUG,
                'sap_error' => $message,
                'post_id'   => (int) $duplicate['post_id'],
            ], admin_url( 'admin.php' ) ) );
            exit;
        }

        $postarr = [
            'post_type'    => 'post',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
            'post_excerpt' => sanitize_textarea_field( $this->post_value( 'excerpt' ) ),
            'post_status'  => $status,
            'post_author'  => get_current_user_id(),
        ];

        if ( 'future' === $status ) {
            $dt = $this->normalize_local_datetime( $publish_date );
            if ( ! $dt ) { wp_die( 'حالة «مجدول» تتطلب تاريخ ووقت صحيحين.' ); }
            $postarr['post_date'] = $dt;
            $postarr['post_date_gmt'] = get_gmt_from_date( $dt );
        } elseif ( $publish_date ) {
            $dt = $this->normalize_local_datetime( $publish_date );
            if ( $dt ) {
                $postarr['post_date'] = $dt;
                $postarr['post_date_gmt'] = get_gmt_from_date( $dt );
            }
        }

        $post_id = wp_insert_post( $postarr, true );
        if ( is_wp_error( $post_id ) ) { wp_die( esc_html( $post_id->get_error_message() ) ); }

        $this->assign_category( $post_id, sanitize_text_field( $this->post_value( 'category' ) ) );
        $this->assign_tags( $post_id, sanitize_text_field( $this->post_value( 'tags' ) ) );
        $this->save_seo_meta( $post_id );

        if ( ! empty( $_FILES['featured_image']['name'] ) ) {
            $attachment_id = $this->handle_featured_image_upload( $post_id );
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            } else {
                update_post_meta( $post_id, '_sap_image_upload_error', $attachment_id->get_error_message() );
            }
        }

        $this->log_event( [
            'event_type'    => 'created',
            'post_id'       => $post_id,
            'title'         => $title,
            'slug'          => $slug,
            'focus_keyword' => sanitize_text_field( $this->post_value( 'focus_keyword' ) ),
            'post_status'   => get_post_status( $post_id ) ?: $status,
            'content_hash'  => $this->content_hash( $content ),
        ] );

        clean_post_cache( $post_id );
        $notice = 'تم إنشاء المقال بنجاح';
        if ( 'future' === get_post_status( $post_id ) ) { $notice = 'تم إنشاء المقال وجدولته بنجاح'; }
        if ( 'publish' === get_post_status( $post_id ) ) { $notice = 'تم إنشاء المقال ونشره بنجاح'; }

        wp_safe_redirect( add_query_arg( [ 'page' => self::MENU_SLUG, 'sap_notice' => $notice, 'post_id' => $post_id ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public static function activate() {
        self::install_log_table();
        update_option( 'sap_db_version', self::DB_VERSION );
    }

    public function maybe_upgrade_database() {
        if ( get_option( 'sap_db_version' ) !== self::DB_VERSION ) {
            self::install_log_table();
            update_option( 'sap_db_version', self::DB_VERSION );
        }
    }

    private static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'sap_article_log';
    }

    private static function install_log_table() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_type varchar(30) NOT NULL DEFAULT 'created',
            post_id bigint(20) unsigned NOT NULL DEFAULT 0,
            title text NOT NULL,
            normalized_title varchar(191) NOT NULL DEFAULT '',
            slug varchar(200) NOT NULL DEFAULT '',
            focus_keyword varchar(255) NOT NULL DEFAULT '',
            post_status varchar(20) NOT NULL DEFAULT '',
            content_hash char(64) NOT NULL DEFAULT '',
            duplicate_reason varchar(255) NOT NULL DEFAULT '',
            duplicate_of_post_id bigint(20) unsigned NOT NULL DEFAULT 0,
            created_by bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY normalized_title (normalized_title),
            KEY slug (slug(191)),
            KEY content_hash (content_hash),
            KEY post_id (post_id)
        ) {$charset_collate};";
        dbDelta( $sql );
    }

    private function normalize_title_for_match( $title ) {
        $value = html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $value = preg_replace( '/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}\x{FEFF}]/u', '', $value );
        $value = preg_replace( '/\s+/u', ' ', trim( $value ) );
        if ( function_exists( 'mb_strtolower' ) ) { $value = mb_strtolower( $value, 'UTF-8' ); }
        return $value;
    }

    private function content_hash( $content ) {
        $value = html_entity_decode( wp_strip_all_tags( (string) $content, true ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $value = preg_replace( '/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}\x{FEFF}]/u', '', $value );
        $value = preg_replace( '/\s+/u', ' ', trim( $value ) );
        if ( function_exists( 'mb_strtolower' ) ) { $value = mb_strtolower( $value, 'UTF-8' ); }
        return hash( 'sha256', $value );
    }

    private function find_duplicate_article( $title, $slug, $content ) {
        global $wpdb;
        $reasons = [];
        $post_id = 0;
        $normalized_title = $this->normalize_title_for_match( $title );
        $hash = $this->content_hash( $content );
        $slug = sanitize_title( $slug );

        // 1) Check the live WordPress posts table, including articles created before this plugin had logging.
        if ( $slug ) {
            $id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_name = %s AND post_status NOT IN ('trash','auto-draft') ORDER BY ID ASC LIMIT 1",
                $slug
            ) );
            if ( $id ) { $reasons[] = 'Slug مطابق'; $post_id = $id; }
        }

        $title_id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_title = %s AND post_status NOT IN ('trash','auto-draft') ORDER BY ID ASC LIMIT 1",
            $title
        ) );
        if ( $title_id ) {
            $reasons[] = 'العنوان مطابق';
            if ( ! $post_id ) { $post_id = $title_id; }
        }

        // 2) Check the persistent plugin log. This still detects a previously-added article even if it was later deleted.
        $table = self::table_name();
        $log_match = $wpdb->get_row( $wpdb->prepare(
            "SELECT post_id, normalized_title, slug, content_hash FROM {$table}
             WHERE event_type = 'created' AND (normalized_title = %s OR slug = %s OR content_hash = %s)
             ORDER BY id ASC LIMIT 1",
            $normalized_title, $slug, $hash
        ) );
        if ( $log_match ) {
            if ( $normalized_title && hash_equals( (string) $log_match->normalized_title, $normalized_title ) && ! in_array( 'العنوان مطابق', $reasons, true ) ) {
                $reasons[] = 'العنوان موجود في سجل الإضافة';
            }
            if ( $slug && hash_equals( (string) $log_match->slug, $slug ) && ! in_array( 'Slug مطابق', $reasons, true ) ) {
                $reasons[] = 'Slug موجود في سجل الإضافة';
            }
            if ( $hash && hash_equals( (string) $log_match->content_hash, $hash ) ) {
                $reasons[] = 'المحتوى مطابق';
            }
            if ( ! $post_id && ! empty( $log_match->post_id ) ) { $post_id = (int) $log_match->post_id; }
        }

        $reasons = array_values( array_unique( $reasons ) );
        if ( ! $reasons ) { return false; }
        return [ 'reasons' => $reasons, 'post_id' => $post_id ];
    }

    private function log_event( array $data ) {
        global $wpdb;
        $defaults = [
            'event_type'           => 'created',
            'post_id'              => 0,
            'title'                => '',
            'slug'                 => '',
            'focus_keyword'        => '',
            'post_status'          => '',
            'content_hash'         => '',
            'duplicate_reason'     => '',
            'duplicate_of_post_id' => 0,
        ];
        $data = wp_parse_args( $data, $defaults );
        $wpdb->insert(
            self::table_name(),
            [
                'event_type'           => sanitize_key( $data['event_type'] ),
                'post_id'              => absint( $data['post_id'] ),
                'title'                => sanitize_text_field( $data['title'] ),
                'normalized_title'     => $this->normalize_title_for_match( $data['title'] ),
                'slug'                 => sanitize_title( $data['slug'] ),
                'focus_keyword'        => sanitize_text_field( $data['focus_keyword'] ),
                'post_status'          => sanitize_key( $data['post_status'] ),
                'content_hash'         => preg_replace( '/[^a-f0-9]/', '', strtolower( (string) $data['content_hash'] ) ),
                'duplicate_reason'     => sanitize_text_field( $data['duplicate_reason'] ),
                'duplicate_of_post_id' => absint( $data['duplicate_of_post_id'] ),
                'created_by'           => get_current_user_id(),
                'created_at'           => current_time( 'mysql' ),
            ],
            [ '%s','%d','%s','%s','%s','%s','%s','%s','%s','%d','%d','%s' ]
        );
    }

    public function render_logs_page() {
        if ( ! current_user_can( self::CAPABILITY ) ) { wp_die( 'غير مصرح.' ); }
        global $wpdb;
        $table = self::table_name();
        $rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 300" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $created_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE event_type = 'created'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $duplicate_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE event_type = 'duplicate_blocked'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        ?>
        <div class="wrap sap-wrap sap-log-wrap" dir="rtl">
            <h1>سجل مقالات صروح</h1>
            <p class="description">سجل دائم لما تم إنشاؤه من خلال الإضافة ومحاولات التكرار التي تم منعها. يتم الاحتفاظ بالسجل حتى لو حُذف المقال من ووردبريس.</p>
            <div class="sap-log-stats">
                <div class="sap-stat"><strong><?php echo esc_html( number_format_i18n( $created_count ) ); ?></strong><span>مقال تم إنشاؤه</span></div>
                <div class="sap-stat"><strong><?php echo esc_html( number_format_i18n( $duplicate_count ) ); ?></strong><span>محاولة تكرار مُنعت</span></div>
            </div>
            <div class="sap-card sap-log-card">
                <table class="widefat striped sap-log-table">
                    <thead><tr>
                        <th>النوع</th><th>العنوان</th><th>Slug</th><th>الحالة</th><th>سبب المنع</th><th>المقال</th><th>المستخدم</th><th>التاريخ</th>
                    </tr></thead>
                    <tbody>
                    <?php if ( ! $rows ) : ?>
                        <tr><td colspan="8">لا توجد سجلات بعد. أول مقال جديد يتم إنشاؤه سيظهر هنا.</td></tr>
                    <?php else : foreach ( $rows as $row ) :
                        $is_duplicate = 'duplicate_blocked' === $row->event_type;
                        $linked_post_id = $is_duplicate ? (int) $row->duplicate_of_post_id : (int) $row->post_id;
                        $post = $linked_post_id ? get_post( $linked_post_id ) : null;
                        $live_status = $post ? get_post_status( $linked_post_id ) : '';
                        $user = get_userdata( (int) $row->created_by );
                    ?>
                        <tr>
                            <td><span class="sap-badge <?php echo $is_duplicate ? 'sap-badge-danger' : 'sap-badge-success'; ?>"><?php echo $is_duplicate ? 'مكرر مرفوض' : 'تم الإنشاء'; ?></span></td>
                            <td><strong><?php echo esc_html( $row->title ); ?></strong></td>
                            <td dir="ltr"><code><?php echo esc_html( $row->slug ); ?></code></td>
                            <td><?php echo esc_html( $live_status ?: $row->post_status ?: '—' ); ?></td>
                            <td><?php echo esc_html( $row->duplicate_reason ?: '—' ); ?></td>
                            <td>
                                <?php if ( $post && get_edit_post_link( $linked_post_id ) ) : ?>
                                    <a href="<?php echo esc_url( get_edit_post_link( $linked_post_id ) ); ?>">فتح #<?php echo esc_html( $linked_post_id ); ?></a>
                                <?php elseif ( $linked_post_id ) : ?>
                                    <span class="description">غير متاح #<?php echo esc_html( $linked_post_id ); ?></span>
                                <?php else : ?>—<?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $user ? $user->display_name : '—' ); ?></td>
                            <td dir="ltr"><?php echo esc_html( $row->created_at ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function normalize_local_datetime( $value ) {
        if ( ! $value ) { return false; }
        $tz = wp_timezone();
        $formats = [ 'Y-m-d H:i', 'Y-m-d H:i:s', 'Y-m-d\\TH:i' ];
        foreach ( $formats as $format ) {
            $dt = DateTimeImmutable::createFromFormat( $format, $value, $tz );
            if ( $dt && $dt->format( $format ) === $value ) { return $dt->format( 'Y-m-d H:i:s' ); }
        }
        return false;
    }

    private function assign_category( $post_id, $category_name ) {
        if ( ! $category_name ) { return; }
        $term = term_exists( $category_name, 'category' );
        if ( ! $term ) {
            if ( ! current_user_can( 'manage_categories' ) ) { return; }
            $term = wp_insert_term( $category_name, 'category' );
        }
        if ( is_wp_error( $term ) ) { return; }
        $term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
        wp_set_post_terms( $post_id, [ $term_id ], 'category', false );
    }

    private function assign_tags( $post_id, $tags_line ) {
        if ( ! $tags_line ) { return; }
        $tags = preg_split( '/[,،]+/u', $tags_line );
        $tags = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', $tags ) ) ) );
        if ( $tags ) { wp_set_post_terms( $post_id, $tags, 'post_tag', false ); }
    }

    private function save_seo_meta( $post_id ) {
        $focus = sanitize_text_field( $this->post_value( 'focus_keyword' ) );
        $seo_title = sanitize_text_field( $this->post_value( 'seo_title' ) );
        $meta_desc = sanitize_textarea_field( $this->post_value( 'meta_description' ) );

        update_post_meta( $post_id, '_sap_focus_keyword', $focus );
        update_post_meta( $post_id, '_sap_seo_title', $seo_title );
        update_post_meta( $post_id, '_sap_meta_description', $meta_desc );

        // Yoast compatibility: these private keys are widely used by Yoast, but remain implementation details.
        if ( defined( 'WPSEO_VERSION' ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $focus );
            update_post_meta( $post_id, '_yoast_wpseo_title', $seo_title );
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );
            // Trigger a normal update so Yoast can refresh its indexable state through its own hooks.
            wp_update_post( [ 'ID' => $post_id ] );
        }
    }

    public function filter_yoast_title( $title ) {
        if ( ! is_singular( 'post' ) ) { return $title; }
        $custom = get_post_meta( get_queried_object_id(), '_sap_seo_title', true );
        return $custom ?: $title;
    }

    public function filter_yoast_metadesc( $description ) {
        if ( ! is_singular( 'post' ) ) { return $description; }
        $custom = get_post_meta( get_queried_object_id(), '_sap_meta_description', true );
        return $custom ?: $description;
    }

    private function handle_featured_image_upload( $post_id ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $desired = sanitize_file_name( $this->post_value( 'image_filename' ) );
        if ( $desired ) {
            $current_ext = strtolower( pathinfo( $_FILES['featured_image']['name'], PATHINFO_EXTENSION ) );
            $desired_ext = strtolower( pathinfo( $desired, PATHINFO_EXTENSION ) );
            if ( ! $desired_ext && $current_ext ) { $desired .= '.' . $current_ext; }
            if ( $desired_ext && $current_ext && $desired_ext !== $current_ext ) {
                $desired = pathinfo( $desired, PATHINFO_FILENAME ) . '.' . $current_ext;
            }
            $_FILES['featured_image']['name'] = $desired;
        }

        $attachment_data = [
            'post_title'   => sanitize_text_field( $this->post_value( 'image_title' ) ),
            'post_excerpt' => sanitize_textarea_field( $this->post_value( 'image_caption' ) ),
            'post_content' => sanitize_textarea_field( $this->post_value( 'image_description' ) ),
        ];
        $attachment_data = array_filter( $attachment_data, static fn( $v ) => '' !== $v );

        $attachment_id = media_handle_upload( 'featured_image', $post_id, $attachment_data, [ 'test_form' => false ] );
        if ( is_wp_error( $attachment_id ) ) { return $attachment_id; }

        $alt = sanitize_textarea_field( $this->post_value( 'image_alt' ) );
        if ( $alt ) { update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt ); }
        return $attachment_id;
    }

    /**
     * Remove internal ChatGPT citation/reference tokens that can be copied with article text.
     * These strings are UI/transport metadata and must never be rendered on the public site.
     */
    private function clean_chatgpt_artifacts( $content ) {
        $content = (string) $content;

        // Examples: contentReference[oaicite:2]{index=2}
        $content = preg_replace( '/contentReference\s*\[\s*oaicite\s*:[^\]]*\]\s*\{[^}]*\}/iu', '', $content );
        // Defensive variant for other contentReference payloads with the same copied structure.
        $content = preg_replace( '/contentReference\s*\[[^\]]*\]\s*\{[^}]*\}/iu', '', $content );

        // Rendered/internal OpenAI citation markers that may be copied as plain text.
        $content = preg_replace( '/(?:cite|filecite|memcite|navlist|filenavlist|entity|products|finance|schedule|standing|forecast|i|genui)\b[^]*/u', '', $content );

        // Rare malformed leftovers such as standalone oaicite markers.
        $content = preg_replace( '/\[?\s*oaicite\s*:\s*\d+\s*\]?\s*(?:\{[^}]*\})?/iu', '', $content );

        // Remove whitespace created around deleted artifacts without altering HTML structure.
        $content = preg_replace( '/[ \t]{2,}/u', ' ', $content );
        $content = preg_replace( '/\s+([\.,،؛:!؟])/u', '$1', $content );
        return trim( $content );
    }

    private function markdown_to_html( $markdown ) {
        $markdown = str_replace( [ "\r\n", "\r" ], "\n", trim( $markdown ) );
        $lines = explode( "\n", $markdown );
        $html = [];
        $in_ul = false;
        $in_ol = false;
        foreach ( $lines as $line ) {
            $trim = trim( $line );
            if ( '' === $trim ) {
                if ( $in_ul ) { $html[] = '</ul>'; $in_ul = false; }
                if ( $in_ol ) { $html[] = '</ol>'; $in_ol = false; }
                continue;
            }
            if ( preg_match( '/^(#{1,4})\s+(.+)$/u', $trim, $m ) ) {
                if ( $in_ul ) { $html[] = '</ul>'; $in_ul = false; }
                if ( $in_ol ) { $html[] = '</ol>'; $in_ol = false; }
                $source_level = strlen( $m[1] );
                // The WordPress theme normally outputs the post title as H1. Keep article body headings at H2-H4.
                $level = ( $source_level <= 2 ) ? 2 : min( $source_level, 4 );
                $html[] = '<h' . $level . '>' . $this->inline_markdown( $m[2] ) . '</h' . $level . '>';
                continue;
            }
            if ( preg_match( '/^[-*]\s+(.+)$/u', $trim, $m ) ) {
                if ( $in_ol ) { $html[] = '</ol>'; $in_ol = false; }
                if ( ! $in_ul ) { $html[] = '<ul>'; $in_ul = true; }
                $html[] = '<li>' . $this->inline_markdown( $m[1] ) . '</li>';
                continue;
            }
            if ( preg_match( '/^\d+[\.\)]\s+(.+)$/u', $trim, $m ) ) {
                if ( $in_ul ) { $html[] = '</ul>'; $in_ul = false; }
                if ( ! $in_ol ) { $html[] = '<ol>'; $in_ol = true; }
                $html[] = '<li>' . $this->inline_markdown( $m[1] ) . '</li>';
                continue;
            }
            if ( $in_ul ) { $html[] = '</ul>'; $in_ul = false; }
            if ( $in_ol ) { $html[] = '</ol>'; $in_ol = false; }
            $html[] = '<p>' . $this->inline_markdown( $trim ) . '</p>';
        }
        if ( $in_ul ) { $html[] = '</ul>'; }
        if ( $in_ol ) { $html[] = '</ol>'; }
        return wp_kses_post( implode( "\n", $html ) );
    }

    private function inline_markdown( $text ) {
        $text = esc_html( $text );
        $text = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text );
        $text = preg_replace( '/(?<!\*)\*([^*]+)\*(?!\*)/u', '<em>$1</em>', $text );
        $text = preg_replace_callback( '/\[([^\]]+)\]\((https?:\/\/[^\s\)]+)\)/u', static function( $m ) {
            return '<a href="' . esc_url( html_entity_decode( $m[2] ) ) . '">' . $m[1] . '</a>';
        }, $text );
        return $text;
    }
}

register_activation_hook( __FILE__, [ 'Sorouh_Article_Publisher', 'activate' ] );
new Sorouh_Article_Publisher();
