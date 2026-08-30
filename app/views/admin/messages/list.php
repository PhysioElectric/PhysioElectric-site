<?php
/**
 * Admin: received messages inbox.
 * Expects: $messages, $unread
 */
$adminTitle  = t('admin.msg.title');
$adminActive = $adminActive ?? 'messages';
?>
<?php if (!empty($schemaMissing)): ?>
    <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-sm p-3 leading-relaxed">
        <?= e(t('admin.schemaMissing')) ?>
    </div>
<?php endif; ?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500">
        <b><?= count($messages) ?></b> <?= e(t('admin.msg.total')) ?>
        <?php if ((int) $unread > 0): ?>
            <span class="badge badge-amber"><?= (int) $unread ?> <?= e(t('admin.msg.unread')) ?></span>
        <?php endif; ?>
    </p>
</div>

<div class="admin-card overflow-x-auto">
    <?php if (empty($messages)): ?>
        <p class="p-10 text-sm text-slate-400 text-center"><?= e(t('admin.msg.empty')) ?></p>
    <?php else: ?>
        <table class="admin-table min-w-[760px]">
            <thead>
                <tr>
                    <th></th>
                    <th><?= e(t('admin.msg.from')) ?></th>
                    <th><?= e(t('admin.msg.contact')) ?></th>
                    <th><?= e(t('admin.msg.category')) ?></th>
                    <th><?= e(t('admin.msg.attach')) ?></th>
                    <th><?= e(t('admin.date')) ?></th>
                    <th class="text-end"><?= e(t('admin.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                    <?php $atts = MessageModel::attachments($m); ?>
                    <tr class="<?= (int) $m['is_read'] === 0 ? 'bg-physio-50/50' : '' ?>">
                        <td>
                            <?php if ((int) $m['is_read'] === 0): ?>
                                <span class="w-2 h-2 rounded-full bg-physio-500 inline-block"></span>
                            <?php endif; ?>
                        </td>
                        <td class="font-medium text-slate-700">
                            <span class="block"><?= e((string) $m['name']) ?></span>
                            <?php if ((string) $m['company'] !== ''): ?>
                                <span class="block text-xs text-slate-400"><?= e((string) $m['company']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-slate-500 text-sm">
                            <span class="block ltr" dir="ltr"><?= e((string) $m['email']) ?></span>
                            <?php if ((string) $m['phone'] !== ''): ?>
                                <span class="block text-xs ltr" dir="ltr"><?= e((string) $m['phone']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-slate-500 text-sm max-w-[180px] truncate"><?= e((string) $m['category']) ?></td>
                        <td class="text-slate-500 text-sm"><?= count($atts) > 0 ? count($atts) . ' 📎' : '—' ?></td>
                        <td class="text-slate-500 text-sm whitespace-nowrap"><?= e(format_date((string) $m['created_at'])) ?></td>
                        <td class="text-end">
                            <div class="flex items-center justify-end gap-2">
                                <a href="/admin/messages/<?= (int) $m['id'] ?>" class="admin-btn admin-btn-ghost"><i data-lucide="eye" class="w-4 h-4"></i><?= e(t('admin.msg.view')) ?></a>
                                <form method="post" action="/admin/messages/delete" onsubmit="return confirm('<?= e(t('admin.msg.confirmDelete')) ?>');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <button type="submit" class="admin-btn text-rose-500 hover:!bg-rose-500/10"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
