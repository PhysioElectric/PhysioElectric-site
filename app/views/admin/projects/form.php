<?php
/**
 * Admin: project create/edit form (bilingual + WYSIWYG).
 * Expects: $project, $categories
 */
$adminTitle  = !empty($project['id']) ? t('admin.editProject') : t('admin.newProject');
$adminActive = 'projects';
$action      = !empty($project['id']) ? '/admin/projects/' . (int) $project['id'] : '/admin/projects/create';
$isNew       = empty($project['id']);

/**
 * Reusable editor toolbar markup.
 */
function pe_toolbar(string $langDir): void
{
    ?>
    <div class="pe-editor-toolbar">
        <button type="button" class="pe-editor-btn" data-cmd="bold" title="Bold"><b>B</b></button>
        <button type="button" class="pe-editor-btn" data-cmd="italic" title="Italic"><i>I</i></button>
        <button type="button" class="pe-editor-btn" data-cmd="underline" title="Underline"><u>U</u></button>
        <span class="w-px bg-slate-200 mx-1"></span>
        <button type="button" class="pe-editor-btn" data-block="h2" title="H2">H2</button>
        <button type="button" class="pe-editor-btn" data-block="h3" title="H3">H3</button>
        <button type="button" class="pe-editor-btn" data-block="p" title="P">P</button>
        <span class="w-px bg-slate-200 mx-1"></span>
        <button type="button" class="pe-editor-btn" data-cmd="insertUnorderedList" title="UL"><i data-lucide="list" class="w-4 h-4"></i></button>
        <button type="button" class="pe-editor-btn" data-cmd="insertOrderedList" title="OL"><i data-lucide="list-ordered" class="w-4 h-4"></i></button>
        <button type="button" class="pe-editor-btn" data-block="blockquote" title="Quote"><i data-lucide="quote" class="w-4 h-4"></i></button>
        <button type="button" class="pe-editor-btn" data-block="pre" title="Code"><i data-lucide="code" class="w-4 h-4"></i></button>
        <span class="w-px bg-slate-200 mx-1"></span>
        <button type="button" class="pe-editor-btn" data-action="link" title="Link"><i data-lucide="link" class="w-4 h-4"></i></button>
        <button type="button" class="pe-editor-btn" data-action="image" title="Image"><i data-lucide="image" class="w-4 h-4"></i></button>
        <button type="button" class="pe-editor-btn" data-cmd="removeFormat" title="Clear"><i data-lucide="eraser" class="w-4 h-4"></i></button>
    </div>
    <?php
}
?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500 max-w-xl leading-relaxed">
        همهٔ فیلدها را به دو زبان تکمیل کنید. اسلاگ فقط حروف انگلیسی کوچک، عدد و خط تیره می‌پذیرد.
    </p>
    <a href="/admin/projects" class="admin-btn admin-btn-ghost shrink-0"><i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i><?= e(t('admin.cancel')) ?></a>
</div>

<form method="post" action="<?= e($action) ?>" class="space-y-6" data-slug-form>
    <?= Csrf::field() ?>

    <!-- Category + publish settings -->
    <div class="admin-card p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
            <i data-lucide="settings-2" class="w-4 h-4 text-physio-500"></i>
            دسته‌بندی و انتشار
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="admin-label" for="category_id"><?= e(t('admin.category')) ?> *</label>
                <select id="category_id" name="category_id" class="admin-select" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($project['category_id'] ?? 1) === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= e(L($c, 'name')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="admin-label" for="status"><?= e(t('admin.statusField')) ?></label>
                <select id="status" name="status" class="admin-select">
                    <option value="draft" <?= ($project['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= e(t('admin.draft')) ?></option>
                    <option value="published" <?= ($project['status'] ?? '') === 'published' ? 'selected' : '' ?>><?= e(t('admin.published')) ?></option>
                </select>
            </div>
            <div>
                <label class="admin-label" for="sort_order">ترتیب نمایش</label>
                <input type="number" id="sort_order" name="sort_order" class="admin-input" dir="ltr"
                       value="<?= e((string) ($project['sort_order'] ?? 0)) ?>">
            </div>
        </div>
        <div class="mt-5">
            <label class="admin-label"><?= e(t('admin.image')) ?> (JPG/PNG/Webp — حداکثر ۲MB)</label>
            <div class="pe-image-field" data-field="image">
                <input type="hidden" name="image" value="<?= e((string) ($project['image'] ?? '')) ?>">
                <div class="flex items-center gap-3">
                    <div class="w-20 h-14 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0" data-preview>
                        <?php if (!empty($project['image'])): ?>
                            <img src="<?= e((string) $project['image']) ?>" alt="" class="w-full h-full object-cover" data-preview-img>
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
        <div class="mt-5">
            <label class="admin-label" for="tech_tags"><?= e(t('admin.techTags')) ?></label>
            <input type="text" id="tech_tags" name="tech_tags" class="admin-input" dir="ltr"
                   value="<?= e((string) ($project['tech_tags'] ?? '')) ?>" placeholder="Python, OpenCV, Docker">
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
                           value="<?= e((string) ($project['title_fa'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="slug_fa"><?= e(t('admin.slug')) ?> (اگر خالی باشد از اسلاگ انگلیسی استفاده می‌شود)</label>
                    <input type="text" id="slug_fa" name="slug_fa" class="admin-input font-mono" dir="ltr"
                           value="<?= e((string) ($project['slug_fa'] ?? '')) ?>" placeholder="my-project-slug">
                </div>
            </div>
            <div>
                <label class="admin-label" for="short_desc_fa"><?= e(t('admin.shortDesc')) ?></label>
                <textarea id="short_desc_fa" name="short_desc_fa" rows="2" class="admin-textarea"
                          placeholder="خلاصهٔ کوتاه پروژه برای کارت‌ها..."><?= e((string) ($project['short_desc_fa'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.content')) ?></label>
                <div class="pe-wysiwyg" data-target="content_fa">
                    <?php pe_toolbar('rtl'); ?>
                    <div class="pe-editor-area" contenteditable="true" data-placeholder="محتوای فارسی پروژه را اینجا بنویسید..."><?= (string) ($project['content_fa'] ?? '') /* sanitized on save */ ?></div>
                    <input type="hidden" name="content_fa" data-wysiwyg-input>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="meta_title_fa"><?= e(t('admin.metaTitle')) ?></label>
                    <input type="text" id="meta_title_fa" name="meta_title_fa" class="admin-input" maxlength="255"
                           value="<?= e((string) ($project['meta_title_fa'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="meta_desc_fa"><?= e(t('admin.metaDesc')) ?></label>
                    <input type="text" id="meta_desc_fa" name="meta_desc_fa" class="admin-input" maxlength="500"
                           value="<?= e((string) ($project['meta_desc_fa'] ?? '')) ?>">
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
                    <input type="text" id="title_en" name="title_en" class="admin-input" data-slug-source
                           value="<?= e((string) ($project['title_en'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="slug_en">Slug (URL) *</label>
                    <input type="text" id="slug_en" name="slug_en" class="admin-input font-mono" data-slug-target
                           value="<?= e((string) ($project['slug_en'] ?? '')) ?>" placeholder="my-project-slug" required>
                </div>
            </div>
            <div>
                <label class="admin-label" for="short_desc_en">Short description</label>
                <textarea id="short_desc_en" name="short_desc_en" rows="2" class="admin-textarea"
                          placeholder="Short project summary for cards..."><?= e((string) ($project['short_desc_en'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="admin-label">Content</label>
                <div class="pe-wysiwyg" data-target="content_en">
                    <?php pe_toolbar('ltr'); ?>
                    <div class="pe-editor-area" contenteditable="true" data-placeholder="Write the English project content here..."><?= (string) ($project['content_en'] ?? '') /* sanitized on save */ ?></div>
                    <input type="hidden" name="content_en" data-wysiwyg-input>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="meta_title_en">SEO Title</label>
                    <input type="text" id="meta_title_en" name="meta_title_en" class="admin-input" maxlength="255"
                           value="<?= e((string) ($project['meta_title_en'] ?? '')) ?>">
                </div>
                <div>
                    <label class="admin-label" for="meta_desc_en">SEO Description</label>
                    <input type="text" id="meta_desc_en" name="meta_desc_en" class="admin-input" maxlength="500"
                           value="<?= e((string) ($project['meta_desc_en'] ?? '')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="admin-btn admin-btn-primary !px-8 !py-3">
            <i data-lucide="save" class="w-4 h-4"></i>
            <?= e($isNew ? t('admin.save') : t('admin.saveChanges')) ?>
        </button>
        <a href="/admin/projects" class="admin-btn admin-btn-ghost"><?= e(t('admin.cancel')) ?></a>
    </div>
</form>
