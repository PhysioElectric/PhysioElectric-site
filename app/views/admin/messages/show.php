<?php
/**
 * Admin: single received message.
 * Expects: $message
 */
$adminTitle  = t('admin.msg.title');
$adminActive = $adminActive ?? 'messages';
$atts        = MessageModel::attachments($message);
?>
<div class="flex items-center justify-between mb-5">
    <a href="/admin/messages" class="admin-btn admin-btn-ghost"><i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i><?= e(t('admin.msg.title')) ?></a>
    <?php if (admin_can_edit()): ?>
    <div class="flex items-center gap-2">
        <form method="post" action="/admin/messages/<?= (int) $message['id'] ?>/read">
            <?= Csrf::field() ?>
            <button type="submit" class="admin-btn admin-btn-ghost"><i data-lucide="check-check" class="w-4 h-4"></i><?= e(t('admin.msg.toggleRead')) ?></button>
        </form>
        <form method="post" action="/admin/messages/delete" onsubmit="return confirm('<?= e(t('admin.msg.confirmDelete')) ?>');">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
            <button type="submit" class="admin-btn text-rose-500 hover:!bg-rose-500/10"><i data-lucide="trash-2" class="w-4 h-4"></i><?= e(t('admin.delete')) ?></button>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="admin-card p-6 space-y-5">
    <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-full bg-physio-100 text-physio-600 flex items-center justify-center font-bold text-lg"><?= e(mb_substr((string) $message['name'], 0, 1)) ?></span>
        <div>
            <p class="font-bold text-slate-800"><?= e((string) $message['name']) ?>
                <?php if ((string) $message['company'] !== ''): ?>
                    <span class="text-sm font-normal text-slate-400">(<?= e((string) $message['company']) ?>)</span>
                <?php endif; ?>
            </p>
            <p class="text-xs text-slate-400"><?= e(format_date((string) $message['created_at'])) ?> · <?= e(strtoupper((string) $message['lang'])) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs text-slate-400 mb-1"><?= e(t('admin.msg.email')) ?></p>
            <a class="ltr font-medium text-physio-600" dir="ltr" href="mailto:<?= e((string) $message['email']) ?>"><?= e((string) $message['email']) ?></a>
        </div>
        <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs text-slate-400 mb-1"><?= e(t('admin.msg.phone')) ?></p>
            <span class="ltr font-medium text-slate-700" dir="ltr"><?= e((string) $message['phone'] ?: '—') ?></span>
        </div>
        <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs text-slate-400 mb-1"><?= e(t('admin.msg.method')) ?></p>
            <span class="font-medium text-slate-700"><?= e((string) $message['contact_method'] ?: '—') ?>
                <?php if ((string) $message['contact_id'] !== ''): ?>
                    <span class="ltr text-physio-600" dir="ltr"><?= e((string) $message['contact_id']) ?></span>
                <?php endif; ?>
            </span>
        </div>
        <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs text-slate-400 mb-1"><?= e(t('admin.msg.timeline')) ?></p>
            <span class="font-medium text-slate-700"><?= e((string) $message['timeline'] ?: '—') ?></span>
        </div>
        <div class="bg-slate-50 rounded-lg p-3 md:col-span-2">
            <p class="text-xs text-slate-400 mb-1"><?= e(t('admin.msg.category')) ?></p>
            <span class="font-medium text-slate-700"><?= e((string) $message['category'] ?: '—') ?></span>
        </div>
    </div>

    <div>
        <p class="text-xs text-slate-400 mb-2"><?= e(t('admin.msg.body')) ?></p>
        <div class="bg-slate-50 rounded-lg p-4 text-sm leading-relaxed text-slate-700 whitespace-pre-line"><?= e((string) $message['body']) ?></div>
    </div>

    <?php if ((string) $message['notes'] !== ''): ?>
        <div>
            <p class="text-xs text-slate-400 mb-2"><?= e(t('admin.msg.notes')) ?></p>
            <div class="bg-slate-50 rounded-lg p-4 text-sm leading-relaxed text-slate-700"><?= e((string) $message['notes']) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($atts !== []): ?>
        <div>
            <p class="text-xs text-slate-400 mb-2"><?= e(t('admin.msg.attach')) ?></p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($atts as $i => $att): ?>
                    <a href="/admin/messages/<?= (int) $message['id'] ?>/file/<?= $i ?>" class="admin-btn admin-btn-ghost">
                        <i data-lucide="paperclip" class="w-4 h-4"></i><?= e((string) $att['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
