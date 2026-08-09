(function(){
  'use strict';
  const $ = (id) => document.getElementById(id);
  const map = {
    TITLE:'title', SLUG:'slug', CATEGORY:'category', TAGS:'tags', FOCUS_KEYWORD:'focus_keyword',
    SEO_TITLE:'seo_title', META_DESCRIPTION:'meta_description', EXCERPT:'excerpt', PUBLISH_DATE:'publish_date',
    CONTENT_FORMAT:'content_format', IMAGE_FILENAME:'image_filename', IMAGE_TITLE:'image_title', IMAGE_ALT:'image_alt',
    IMAGE_CAPTION:'image_caption', IMAGE_DESCRIPTION:'image_description'
  };

  function cleanChatGPTArtifacts(text){
    return (text || '')
      .replace(/contentReference\s*\[\s*oaicite\s*:[^\]]*\]\s*\{[^}]*\}/gi, '')
      .replace(/contentReference\s*\[[^\]]*\]\s*\{[^}]*\}/gi, '')
      .replace(/(?:cite|filecite|memcite|navlist|filenavlist|entity|products|finance|schedule|standing|forecast|i|genui)\b[^]*/gu, '')
      .replace(/\[?\s*oaicite\s*:\s*\d+\s*\]?\s*(?:\{[^}]*\})?/gi, '')
      .replace(/[ \t]{2,}/g, ' ')
      .replace(/\s+([\.,،؛:!؟])/g, '$1')
      .trim();
  }

  function normalizeRaw(raw){
    return cleanChatGPTArtifacts((raw || '')
      .replace(/\r\n?/g, '\n')
      .replace(/[\u200B-\u200F\u202A-\u202E\u2060\uFEFF]/g, '')
      .replace(/^\s*>\s?/gm, ''));
  }

  function cleanMetaLine(line){
    return line
      .trim()
      .replace(/^[-*+]\s+/, '')
      .replace(/^#{1,6}\s*/, '')
      .replace(/^\*\*(.*?)\*\*\s*$/, '$1')
      .replace(/\*\*/g, '')
      .trim();
  }

  function parsePackage(raw){
    raw = normalizeRaw(raw);
    const data = {};
    const dataMatch = raw.match(/\[\s*ARTICLE_DATA\s*\]([\s\S]*?)\[\s*\/\s*ARTICLE_DATA\s*\]/i);
    const contentMatch = raw.match(/\[\s*CONTENT\s*\]([\s\S]*?)\[\s*\/\s*CONTENT\s*\]/i);
    const metadata = dataMatch ? dataMatch[1] : raw.split(/\[\s*CONTENT\s*\]/i)[0];

    const metaLines = metadata.split('\n');
    let pendingKey = null;
    metaLines.forEach(original => {
      const line = cleanMetaLine(original);
      if(!line) return;

      // Accept both:
      // TITLE: value
      // TITLE:\nvalue
      // Plus full-width colon, equals sign, dashes, and harmless Markdown around keys.
      const m = line.match(/^([A-Z][A-Z0-9_]*)\s*(?::|：|=|\s[-–—]\s)\s*(.*?)\s*$/i);
      if(m){
        const key = m[1].toUpperCase();
        const val = (m[2] || '').trim().replace(/^\*\*(.*?)\*\*$/, '$1').trim();
        if(val){
          data[key] = val;
          pendingKey = null;
        } else {
          pendingKey = key;
        }
        return;
      }

      if(pendingKey){
        data[pendingKey] = line.replace(/^\*\*(.*?)\*\*$/, '$1').trim();
        pendingKey = null;
      }
    });

    data.CONTENT = contentMatch ? contentMatch[1].trim() : '';
    return data;
  }

  function inlineMarkdown(text){
    const div = document.createElement('div');
    div.textContent = text;
    let out = div.innerHTML;
    out = out.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    out = out.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
    out = out.replace(/\[([^\]]+)\]\((https?:\/\/[^\s\)]+)\)/g, '<a href="$2">$1</a>');
    return out;
  }

  function markdownToHtml(markdown){
    markdown = normalizeRaw(markdown);
    const lines = markdown.split('\n');
    const html = [];
    let inUl = false, inOl = false;
    let skippedFirstH1 = false;
    const closeLists = () => {
      if(inUl){ html.push('</ul>'); inUl = false; }
      if(inOl){ html.push('</ol>'); inOl = false; }
    };

    lines.forEach(line => {
      const trim = line.trim();
      if(!trim){ closeLists(); return; }
      const h = trim.match(/^(#{1,6})\s+(.+)$/);
      if(h){
        closeLists();
        const sourceLevel = h[1].length;
        // WordPress/theme normally renders the post title as H1, so do not duplicate it inside content.
        if(sourceLevel === 1 && !skippedFirstH1){ skippedFirstH1 = true; return; }
        const level = sourceLevel <= 2 ? 2 : Math.min(sourceLevel, 4);
        html.push('<h' + level + '>' + inlineMarkdown(h[2]) + '</h' + level + '>');
        return;
      }
      const ul = trim.match(/^[-*+]\s+(.+)$/);
      if(ul){
        if(inOl){ html.push('</ol>'); inOl = false; }
        if(!inUl){ html.push('<ul>'); inUl = true; }
        html.push('<li>' + inlineMarkdown(ul[1]) + '</li>');
        return;
      }
      const ol = trim.match(/^\d+[.)]\s+(.+)$/);
      if(ol){
        if(inUl){ html.push('</ul>'); inUl = false; }
        if(!inOl){ html.push('<ol>'); inOl = true; }
        html.push('<li>' + inlineMarkdown(ol[1]) + '</li>');
        return;
      }
      closeLists();
      html.push('<p>' + inlineMarkdown(trim) + '</p>');
    });
    closeLists();
    return html.join('\n');
  }

  function setEditorContent(html){
    const textarea = $('content');
    textarea.value = html || '';
    if(window.tinymce){
      const editor = window.tinymce.get('content');
      if(editor) editor.setContent(html || '');
    }
  }

  function getEditorContent(){
    if(window.tinymce){
      const editor = window.tinymce.get('content');
      if(editor && !editor.isHidden()) return editor.getContent();
    }
    return ($('content').value || '');
  }

  function fill(data){
    Object.keys(map).forEach(key => {
      const el = $(map[key]);
      if(el && data[key] !== undefined) el.value = data[key];
    });
    let content = cleanChatGPTArtifacts(data.CONTENT || '');
    if((data.CONTENT_FORMAT || 'markdown').toLowerCase() !== 'html'){
      content = markdownToHtml(content);
      $('content_format').value = 'html';
    }
    setEditorContent(content);
    $('raw_package').value = $('sap-package').value;
    validate();
  }

  function value(id){
    if(id === 'content') return getEditorContent();
    return ($(id).value || '');
  }

  function validate(){
    const items = [
      ['title','العنوان'], ['slug','Slug'], ['content','المحتوى'], ['focus_keyword','الكلمة المفتاحية'],
      ['seo_title','SEO Title'], ['meta_description','Meta Description'], ['category','التصنيف']
    ];
    const ul = $('sap-check-list');
    ul.innerHTML = '';
    items.forEach(([id,label]) => {
      const li = document.createElement('li');
      const ok = value(id).trim().length > 0;
      li.className = ok ? 'ok' : 'warn';
      li.textContent = (ok ? '✓ ' : '⚠ ') + label + (ok ? ' جاهز' : ' غير موجود');
      ul.appendChild(li);
    });
    if($('post_status').value === 'future'){
      const li = document.createElement('li');
      const ok = ($('publish_date').value || '').trim().length > 0;
      li.className = ok ? 'ok' : 'warn';
      li.textContent = (ok ? '✓ ' : '⚠ ') + (ok ? 'موعد الجدولة موجود' : 'أدخل موعد الجدولة');
      ul.appendChild(li);
    }
    const img = document.createElement('li');
    img.className = $('featured_image').files.length ? 'ok' : 'info';
    img.textContent = $('featured_image').files.length ? '✓ الصورة جاهزة للرفع' : 'ℹ الصورة اختيارية ويمكن إضافتها لاحقًا';
    ul.appendChild(img);
  }

  $('sap-parse').addEventListener('click', () => {
    const raw = $('sap-package').value.trim();
    if(!raw){ alert('ألصق المقال أولًا.'); return; }
    const data = parsePackage(raw);
    if(!data.TITLE && !data.CONTENT){ alert('لم أتعرف على الحزمة. تأكد من وجود [ARTICLE_DATA] و[CONTENT].'); return; }
    fill(data);
    if(!data.TITLE){ alert('تم العثور على المحتوى، لكن بيانات المقال غير مكتملة. استخدم الحزمة الجديدة أو تأكد من نسخ قسم [ARTICLE_DATA] كاملًا.'); }
  });
  $('sap-sample').addEventListener('click', () => { $('sap-package').value = window.SAP_SAMPLE || ''; });
  $('sap-clear').addEventListener('click', () => { if(confirm('مسح كل البيانات؟')) location.reload(); });
  document.querySelectorAll('#sap-form input, #sap-form textarea, #sap-form select').forEach(el => el.addEventListener('input', validate));
  $('featured_image').addEventListener('change', validate);
  setTimeout(() => {
    if(window.tinymce){
      const editor = window.tinymce.get('content');
      if(editor){ editor.on('keyup change SetContent', validate); }
    }
  }, 800);
  $('sap-form').addEventListener('submit', (e) => {
    if(window.tinymce){ window.tinymce.triggerSave(); }
    validate();
    if($('post_status').value === 'future' && !$('publish_date').value.trim()){
      e.preventDefault(); alert('أدخل تاريخ ووقت النشر للمقال المجدول.');
    }
  });
})();
