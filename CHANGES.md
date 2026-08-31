# Changes pack — Team members + Received messages  (incl. graceful-migration fix)

Base: 6d68e52  ->  942bef8
24 files changed. Drop over the existing project (paths are project-relative).

IMPORTANT AFTER UPGRADE (existing database / old Docker volume):
    cd app && php setup/migrate.php
This creates team_members + messages and seeds 4 team members.
If you forget it, the site/admin still works and shows a banner telling you to
run it (no more 500 lock-out).

## What's new
1. Admin > Team Members (/admin/team): CRUD + photo upload; About page renders
   members from the DB (fa/en), markup/CSS unchanged.
2. Admin > Received Messages (/admin/messages): inbox, unread badge, read
   toggle, attachment download, delete.
3. Public POST /{lang}/inquiry: CSRF 419; honeypot silent-drop; 5/hr/IP 429;
   validation; attachments (max 3, pdf/doc/docx/png/jpg/jpeg/zip, <=2MB) stored
   under uploads/attachments/ (not publicly served); contact.js now POSTs.

## Security
CSP img-src allows https://images.unsplash.com (seeded team photos).

## Robustness
Database::tableExists() guards the new read paths so an un-migrated DB degrades
gracefully instead of 500-ing the admin.

## Tests
63/63 unit, 44/44 sanitizer, 104/104 e2e -> ALL GREEN
