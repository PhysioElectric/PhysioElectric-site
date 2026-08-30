<?php
declare(strict_types=1);

/**
 * Inquiries submitted through the public contact / project-order wizard.
 * Surfaced in the admin panel as a received-messages inbox.
 */
final class MessageModel
{
    /** Newest first. Empty when the table is not migrated yet. */
    public static function recent(?int $limit = null): array
    {
        if (!Database::tableExists('messages')) {
            return [];
        }
        $sql = 'SELECT * FROM messages ORDER BY created_at DESC, id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, (int) $limit);
        }
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function byId(int $id): ?array
    {
        if (!Database::tableExists('messages')) {
            return null;
        }
        $st = Database::pdo()->prepare('SELECT * FROM messages WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function unreadCount(): int
    {
        if (!Database::tableExists('messages')) {
            return 0;
        }
        return (int) Database::pdo()
            ->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')
            ->fetchColumn();
    }

    /**
     * Store a public inquiry. All values must already be validated/capped
     * by the controller; this layer only persists.
     * @param array<string,mixed> $m
     */
    public static function create(array $m): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO messages
                (kind, category, name, company, email, phone, contact_method,
                 contact_id, timeline, body, notes, lang, attachments, ip)
             VALUES
                (:kind, :category, :name, :company, :email, :phone, :contactMethod,
                 :contactId, :timeline, :body, :notes, :lang, :attachments, :ip)'
        );
        $st->execute([
            ':kind'         => $m['kind'],
            ':category'     => $m['category'],
            ':name'         => $m['name'],
            ':company'      => $m['company'],
            ':email'        => $m['email'],
            ':phone'        => $m['phone'],
            ':contactMethod'=> $m['contact_method'],
            ':contactId'    => $m['contact_id'],
            ':timeline'     => $m['timeline'],
            ':body'         => $m['body'],
            ':notes'        => $m['notes'],
            ':lang'         => $m['lang'],
            ':attachments'  => $m['attachments'],
            ':ip'           => $m['ip'],
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function markRead(int $id, bool $read = true): void
    {
        $st = Database::pdo()->prepare('UPDATE messages SET is_read = :r WHERE id = :id');
        $st->execute([':r' => $read ? 1 : 0, ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $st = Database::pdo()->prepare('DELETE FROM messages WHERE id = :id');
        $st->execute([':id' => $id]);
        return $st->rowCount() > 0;
    }

    /**
     * Decode the stored JSON attachment list.
     * @return array<int, array{name:string,path:string}>
     */
    public static function attachments(array $message): array
    {
        $raw = (string) ($message['attachments'] ?? '');
        if ($raw === '') {
            return [];
        }
        $list = json_decode($raw, true);
        return is_array($list) ? $list : [];
    }
}
