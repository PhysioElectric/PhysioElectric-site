<?php
declare(strict_types=1);

namespace Admin;

/**
 * Received-messages inbox: inquiries submitted through the public
 * contact / project-order wizard.
 */
final class MessageController
{
    public static function index(): void
    {
        admin_view('messages/list', [
            'messages'      => \MessageModel::recent(200),
            'unread'        => \MessageModel::unreadCount(),
            'schemaMissing' => !\Database::tableExists('messages'),
            'adminActive'   => 'messages',
        ]);
    }

    public static function show(int $id): void
    {
        $message = \MessageModel::byId($id);
        if ($message === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/messages');
        }
        // Opening a message marks it read.
        if ((int) $message['is_read'] === 0) {
            \MessageModel::markRead($id, true);
            $message['is_read'] = 1;
        }
        admin_view('messages/show', [
            'message'    => $message,
            'adminActive' => 'messages',
        ]);
    }

    public static function toggleRead(int $id): void
    {
        $message = \MessageModel::byId($id);
        if ($message !== null) {
            \MessageModel::markRead($id, (int) $message['is_read'] === 0);
        }
        redirect('/admin/messages');
    }

    public static function delete(): void
    {
        $id = input_int('id');
        $message = \MessageModel::byId($id);
        if ($message !== null) {
            foreach (\MessageModel::attachments($message) as $att) {
                UploadController::removeUploadFile((string) ($att['path'] ?? ''));
            }
            \MessageModel::delete($id);
            flash('success', t('admin.msg.deleted'));
        } else {
            flash('error', t('admin.noRows'));
        }
        redirect('/admin/messages');
    }

    /**
     * Stream one attachment to the (authenticated) admin. Attachments are
     * never reachable publicly; they are served through this route only,
     * forced as a download with a neutral content type.
     */
    public static function download(int $id, int $idx): void
    {
        $message = \MessageModel::byId($id);
        if ($message === null) {
            not_found();
        }
        $atts = \MessageModel::attachments($message);
        if (!isset($atts[$idx]['path'])) {
            not_found();
        }
        $rel  = (string) $atts[$idx]['path'];
        $base = realpath(BASE_PATH . '/uploads');
        $full = realpath(BASE_PATH . str_replace('/', DIRECTORY_SEPARATOR, $rel));
        if ($base === false || $full === false
            || !str_starts_with($full, $base . DIRECTORY_SEPARATOR)
            || !is_file($full)) {
            not_found();
        }

        $display = (string) ($atts[$idx]['name'] ?? 'attachment');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($display) . '"');
        header('Content-Length: ' . (string) (int) filesize($full));
        header('X-Content-Type-Options: nosniff');
        readfile($full);
        exit;
    }
}
