# Changes pack — Team + Received messages (email validation aligned)

Base: 6d68e52  ->  6461755
27 files changed. Drop over the existing project (project-relative paths).

LATEST FIX: the wizard's email check now matches the server (previously it only
required an '@'), so an invalid email is caught at the contact step with a clear
Persian message instead of a dead-end 422 at the end. A valid email submits fine
even with no cookies/CSRF (verified on Apache + php -S).

VERIFY: cd app && php setup/selftest.php  (exit 0 = good); then Ctrl+F5 and submit.
If anything ever fails, the error box shows status=... code=... for diagnosis.

## Features
1. /admin/team: CRUD + photo upload; About renders from DB (fa/en).
2. /admin/messages: inbox, unread badge, read toggle, attachment download, delete.
3. POST /{lang}/inquiry: stores contact+project requests; attachments not public.

## Robustness
Schema self-heal; no CSRF needed on public form; audited JSON errors; 403 CSRF on
admin; header de-dup (Apache parity).

## Tests
63/63 unit, 44/44 sanitizer, 104/104 e2e (Apache + php -S) -> ALL GREEN
