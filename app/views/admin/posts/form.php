<?php
/**
 * Admin: post create/edit form (bilingual + WYSIWYG).
 * Expects: $post (row or blank)
 */
$adminTitle  = !empty($post['id']) ? t('admin.editPost') : t('admin.newPost');
$adminActive = 'posts';
$action      = !empty($post['id']) ? '/admin/posts/' . (int) $post['id'] : '/admin/posts/create';
$isNew       = empty($post['id']);
?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500 max-w-xl leading-relaxed">
        همهٔ فیلدها را هم به فارسی و هم به انگلیسی تکمیل کنید. اسلاگ فقط حروف انگلیسی کوچک، عدد و خط تیره می‌پذیرد.
    </p>
    <a href="/admin/posts" class="admin-btn admin-btn-ghost shrink-0"><i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i><?= e(t('admin.cancel')) ?></a>
</div>

<form method="post" action="<?= e($action) ?>" class="space-y-6" data-slug-form>
    <?= Csrf::field() ?>

    <!-- Publish settings -->
    <div class="admin-card p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
            <i data-lucide="settings-2" class="w-4 h-4 text-physio-500"></i>
            تنظیمات انتشار
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="admin-label" for="status"><?= e(t('admin.statusField')) ?></label>
                <select id="status" name="status" class="admin-select">
                    <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= e(t('admin.draft')) ?></option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>><?= e(t('admin.published')) ?></option>
                </select>
            </div>
            <div>
                <label class="admin-label" for="published_at"><?= e(t('admin.date')) ?> (<?= e(t('admin.published')) ?>)</label>
                <input type="datetime-local" id="published_at" name="published_at"
                       class="admin-input" dir="ltr"
                       value="<?= e(date('Y-m-d\TH:i', strtotime((string) ($post['published_at'] ?? date('Y-m-d H:i'))))) ?>">
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.image')) ?> (JPG/PNG/Webp — حداکثر ۲MB)</label>
                <div class="pe-image-field" data-field="image">
                    <input type="hidden" name="image" value="<?= e((string) ($post['image'] ?? '')) ?>">
                    <div class="flex items-center gap-3">
                        <div class="w-20 h-14 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0" data-preview>
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= e((string) $post['image']) ?>" alt="" class="w-full h-full object-cover" data-preview-img>
                            <?php else: ?>
                                <i data-lucide="image" class="w-6 h-6 text-slate-300" data-preview-icon></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="admin-btn admin-btn-ghost !py-1.5 !px-3 !text-xs cursor-pointer">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i><?= e(t('admin.uploadImage')) ?>
                                <input type="file" accept="image/jpeg,image/png,image/webp" data-file-input class="hidden">
                            </label>
                            <div class="flex gap-2">
                                <button type="button" class="text-[11px] font-semibold text-physio-600 hover:text-physio-500" data-media-toggle>کتابخانهٔ رسانه</button>
                                <button type="button" class="text-[11px] font-semibold text-rose-500 hover:text-rose-400 hidden" data-remove-image><?= e(t('admin.removeImage')) ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="media-picker mt-3 hidden">
                        <div class="border border-slate-200 rounded-xl p-2 bg-slate-50">
                            <div class="media-grid" data-media-grid></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= FA block ================= -->
    <div class="admin-card p-6" dir="rtl">
        <div class="flex items-center gap-2 mb-5 pb-4 border-b border-slate-100">
            <span class="w-2.5 h-2.5 rounded-full bg-physio-500"></span>
            <h2 class="text-sm font-bold text-slate-700">فارسی (FA)</h2>
        </div>
        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="title_fa"><?= e(t('admin.title')) ?> *</label>
                    <input type="text" id="title_fa" name="title_fa" class="admin-input" required
                           value="<?= e((string) ($post['title_fa'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="slug_fa"><?= e(t('admin.slug')) ?> (اگر خالی باشد از اسلاگ انگلیسی استفاده می‌شود)</label>
                    <input type="text" id="slug_fa" name="slug_fa" class="admin-input font-mono" dir="ltr"
                           value="<?= e((string) ($post['slug_fa'] ?? '')) ?>" placeholder="my-post-slug">
                </div>
            </div>
            <div>
                <label class="admin-label" for="excerpt_fa"><?= e(t('admin.shortDesc')) ?></label>
                <textarea id="excerpt_fa" name="excerpt_fa" rows="2" class="admin-textarea"
                          placeholder="یک خلاصهٔ کوتاه برای کارت مطلب..."><?= e((string) ($post['excerpt_fa'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.content')) ?></label>
                <div class="pe-wysiwyg" data-target="content_fa">
                    <div class="pe-editor-toolbar">
                        <button type="button" class="pe-editor-btn" data-cmd="bold" title="<?= e(t('editor.bold')) ?>"><b>B</b></button>
                        <button type="button" class="pe-editor-btn" data-cmd="italic" title="<?= e(t('editor.italic')) ?>"><i>I</i></button>
                        <button type="button" class="pe-editor-btn" data-cmd="underline" title="<?= e(t('editor.underline')) ?>"><u>U</u></button>
                        <span class="w-px bg-slate-200 mx-1"></span>
                        <button type="button" class="pe-editor-btn" data-block="h2" title="<?= e(t('editor.h2')) ?>">H2</button>
                        <button type="button" class="pe-editor-btn" data-block="h3" title="<?= e(t('editor.h3')) ?>">H3</button>
                        <button type="button" class="pe-editor-btn" data-block="p" title="<?= e(t('editor.p')) ?>">P</button>
                        <span class="w-px bg-slate-200 mx-1"></span>
                        <button type="button" class="pe-editor-btn" data-cmd="insertUnorderedList" title="<?= e(t('editor.ul')) ?>"><i data-lucide="list" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-cmd="insertOrderedList" title="<?= e(t('editor.ol')) ?>"><i data-lucide="list-ordered" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-block="blockquote" title="<?= e(t('editor.quote')) ?>"><i data-lucide="quote" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-block="pre" title="<?= e(t('editor.code')) ?>"><i data-lucide="code" class="w-4 h-4"></i></button>
                        <span class="w-px bg-slate-200 mx-1"></span>
                        <button type="button" class="pe-editor-btn" data-action="link" title="<?= e(t('editor.link')) ?>"><i data-lucide="link" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-action="image" title="<?= e(t('editor.image')) ?>"><i data-lucide="image" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-cmd="removeFormat" title="<?= e(t('editor.clear')) ?>"><i data-lucide="eraser" class="w-4 h-4"></i></button>
                    </div>
                    <div class="pe-editor-area" contenteditable="true" data-placeholder="محتوای فارسی را اینجا بنویسید..."><?= (string) ($post['content_fa'] ?? '') /* sanitized on save */ ?></div>
                    <input type="hidden" name="content_fa" data-wysiwyg-input>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="meta_title_fa"><?= e(t('admin.metaTitle')) ?></label>
                    <input type="text" id="meta_title_fa" name="meta_title_fa" class="admin-input" maxlength="255"
                           value="<?= e((string) ($post['meta_title_fa'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="meta_desc_fa"><?= e(t('admin.metaDesc')) ?></label>
                    <input type="text" id="meta_desc_fa" name="meta_desc_fa" class="admin-input" maxlength="500"
                           value="<?= e((string) ($post['meta_desc_fa'] ?? '')) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ================= EN block ================= -->
    <div class="admin-card p-6" dir="ltr">
        <div class="flex items-center gap-2 mb-5 pb-4 border-b border-slate-100">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
            <h2 class="text-sm font-bold text-slate-700">English (EN)</h2>
        </div>
        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="title_en">Title * <span class="text-slate-400 font-normal">(slug source)</span></label>
                    <input type="text" id="title_en" name="title_en" class="admin-input"
                           data-slug-source
                           value="<?= e((string) ($post['title_en'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="slug_en">Slug (URL) *</label>
                    <input type="text" id="slug_en" name="slug_en" class="admin-input font-mono"
                           data-slug-target
                           value="<?= e((string) ($post['slug_en'] ?? '')) ?>" placeholder="my-post-slug" required>
                </div>
            </div>
            <div>
                <label class="admin-label" for="excerpt_en">Short description</label>
                <textarea id="excerpt_en" name="excerpt_en" rows="2" class="admin-textarea"
                          placeholder="A short summary for the post card..."><?= e((string) ($post['excerpt_en'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="admin-label">Content</label>
                <div class="pe-wysiwyg" data-target="content_en">
                    <div class="pe-editor-toolbar">
                        <button type="button" class="pe-editor-btn" data-cmd="bold" title="Bold"><b>B</b></button>
                        <button type="button" class="pe-editor-btn" data-cmd="italic" title="Italic"><i>I</i></button>
                        <button type="button" class="pe-editor-btn" data-cmd="underline" title="Underline"><u>U</u></button>
                        <span class="w-px bg-slate-200 mx-1"></span>
                        <button type="button" class="pe-editor-btn" data-block="h2" title="Heading 2">H2</button>
                        <button type="button" class="pe-editor-btn" data-block="h3" title="Heading 3">H3</button>
                        <button type="button" class="pe-editor-btn" data-block="p" title="Paragraph">P</button>
                        <span class="w-px bg-slate-200 mx-1"></span>
                        <button type="button" class="pe-editor-btn" data-cmd="insertUnorderedList" title="Bullet list"><i data-lucide="list" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-cmd="insertOrderedList" title="Numbered list"><i data-lucide="list-ordered" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-block="blockquote" title="Quote"><i data-lucide="quote" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-block="pre" title="Code"><i data-lucide="code" class="w-4 h-4"></i></button>
                        <span class="w-px bg-slate-200 mx-1"></span>
                        <button type="button" class="pe-editor-btn" data-action="link" title="Link"><i data-lucide="link" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-action="image" title="Image"><i data-lucide="image" class="w-4 h-4"></i></button>
                        <button type="button" class="pe-editor-btn" data-cmd="removeFormat" title="Clear formatting"><i data-lucide="eraser" class="w-4 h-4"></i></button>
                    </div>
                    <div class="pe-editor-area" contenteditable="true" data-placeholder="Write the English content here..."><?= (string) ($post['content_en'] ?? '') /* sanitized on save */ ?></div>
                    <input type="hidden" name="content_en" data-wysiwyg-input>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="meta_title_en">SEO Title</label>
                    <input type="text" id="meta_title_en" name="meta_title_en" class="admin-input" maxlength="255"
                           value="<?= e((string) ($post['meta_title_en'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="meta_desc_en">SEO Description</label>
                    <input type="text" id="meta_desc_en" name="meta_desc_en" class="admin-input" maxlength="500"
                           value="<?= e((string) ($post['meta_desc_en'] ?? '')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="admin-btn admin-btn-primary !px-8 !py-3">
            <i data-lucide="save" class="w-4 h-4"></i>
            <?= e($isNew ? t('admin.save') : t('admin.saveChanges')) ?>
        </button>
        <a href="/admin/posts" class="admin-btn admin-btn-ghost"><?= e(t('admin.cancel')) ?></a>
    </div>
</form>
