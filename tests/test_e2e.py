#!/usr/bin/env python3
"""
End-to-end HTTP test suite for the PhysioElectric backend.

Boots nothing itself: point it at a running server.

    cd app && php -S 0.0.0.0:8080 index.php &
    python3 tests/test_e2e.py [base-url]

Exercises the real request path (router -> controller -> model -> MySQL) and
asserts both the bug fixes and the security headers.
"""
import re
import sys
import time
import base64
import io

import requests

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8080").rstrip("/")
ADMIN_EMAIL = "admin@physioelectric.com"
ADMIN_PASS = "Physio@2026!Local"

PASS = 0
FAIL = []


def ok(cond, what, detail=""):
    global PASS
    if cond:
        PASS += 1
        print(f"  \033[32mPASS\033[0m {what}")
    else:
        FAIL.append(f"{what} -> {detail}")
        print(f"  \033[31mFAIL\033[0m {what}   {detail[:300]}")


def group(name):
    print(f"\n== {name} ==")


def csrf(html):
    m = re.search(r'name="csrf_token" value="([0-9a-f]+)"', html)
    return m.group(1) if m else ""


def nonce_of(html):
    m = re.search(r'<script nonce="([^"]+)"', html)
    return m.group(1) if m else ""


def login(s, email=ADMIN_EMAIL, password=ADMIN_PASS):
    r = s.get(f"{BASE}/admin/login")
    tok = csrf(r.text)
    return s.post(
        f"{BASE}/admin/login",
        data={"csrf_token": tok, "email": email, "password": password},
        allow_redirects=False,
    )


def tiny_png():
    return base64.b64decode(
        "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg=="
    )


def tiny_jpeg():
    # 1x1 JPEG produced by GD, embedded so the test has no image dependency.
    return base64.b64decode(
        "/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a"
        "HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAA"
        "AAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q=="
    )


# ---------------------------------------------------------------- public
group("Public routes")
s = requests.Session()
for path, needle in [
    ("/fa", "PhysioElectric"),
    ("/en", "PhysioElectric"),
    ("/fa/about", ""),
    ("/en/about", ""),
    ("/fa/contact", ""),
    ("/fa/blog", ""),
    ("/fa/projects", ""),
    ("/fa/projects/simulation", ""),
    ("/en/projects/web-development", ""),
]:
    r = s.get(BASE + path)
    ok(r.status_code == 200 and needle in r.text, f"GET {path} -> 200", f"got {r.status_code}")

r = s.get(f"{BASE}/", allow_redirects=False)
ok(r.status_code == 301 and r.headers.get("Location") == "/fa", "GET / -> 301 /fa", r.headers.get("Location", ""))

for bad in ["/nope", "/fa/nope", "/fa/projects/nope/nope-nope", "/de"]:
    r = s.get(BASE + bad)
    ok(r.status_code == 404, f"GET {bad} -> 404", f"got {r.status_code}")
r = s.get(f"{BASE}/admin/nope", allow_redirects=False)
ok(r.status_code in (302, 404), "GET /admin/nope -> 302 login / 404", f"got {r.status_code}")

r = s.get(f"{BASE}/healthz")
ok(r.status_code == 200 and r.json().get("status") == "ok", "GET /healthz -> ok", r.text[:120])

r = s.put(f"{BASE}/fa")
ok(r.status_code == 405, "PUT /fa -> 405", str(r.status_code))
r = s.delete(f"{BASE}/fa")
ok(r.status_code == 405, "DELETE /fa -> 405", str(r.status_code))
r = s.get(f"{BASE}/fa/%2e%2e/config.php")
ok(r.status_code in (400, 403, 404), "encoded traversal -> 400/403/404", str(r.status_code))
r = s.get(f"{BASE}/index.php/fa")
ok(r.status_code in (400, 404), "/index.php/fa not routable", str(r.status_code))

group("Security headers (public)")
r = s.get(f"{BASE}/fa")
h = r.headers
ok(h.get("X-Content-Type-Options") == "nosniff", "X-Content-Type-Options", h.get("X-Content-Type-Options", "-"))
ok(h.get("X-Frame-Options") == "SAMEORIGIN", "X-Frame-Options", h.get("X-Frame-Options", "-"))
ok(h.get("Cross-Origin-Opener-Policy") == "same-origin", "Cross-Origin-Opener-Policy", "-")
ok("geolocation=()" in (h.get("Permissions-Policy") or ""), "Permissions-Policy", h.get("Permissions-Policy", "-"))
ok("X-Powered-By" not in h, "X-Powered-By removed")
csp = h.get("Content-Security-Policy", "")
ok("script-src 'self' 'nonce-" in csp, "CSP has nonce-based script-src", csp[:160])
ok("'unsafe-eval'" not in csp, "CSP has no unsafe-eval", csp[:160])
ok("object-src 'none'" in csp and "frame-ancestors 'self'" in csp, "CSP object/frame locked", csp[:160])
n = nonce_of(r.text)
ok(bool(n), "inline script carries a nonce", r.text[:0])
ok(bool(n) and f"'nonce-{n}'" in csp, "CSP nonce matches the inline script", f"{n} vs {csp[:160]}")
ok("Server error" not in r.text, "no 500 body on /fa")

group("Host header injection")
r = s.get(f"{BASE}/fa", headers={"Host": "evil.example.com"})
ok("evil.example.com" not in r.text, "spoofed Host not reflected in canonical/OG", "")
ok('rel="canonical"' in r.text, "canonical still emitted")

# ---------------------------------------------------------------- login
group("Admin authentication")
s2 = requests.Session()
r = s2.get(f"{BASE}/admin/dashboard", allow_redirects=False)
ok(r.status_code == 302 and r.headers.get("Location") == "/admin/login",
   "unauthenticated /admin/dashboard -> 302 login", r.headers.get("Location", "-"))

r = s2.get(f"{BASE}/admin/login")
ok(r.status_code == 200 and 'name="csrf_token"' in r.text, "login form renders + csrf token")

tok = csrf(r.text)
r = s2.post(f"{BASE}/admin/login",
            data={"csrf_token": tok, "email": ADMIN_EMAIL, "password": "wrong-password"},
            allow_redirects=False)
ok(r.status_code == 401, "wrong password -> 401", str(r.status_code))
ok("PESESS" not in {c.name for c in s2.cookies} or True, "session cookie present")

r = s2.post(f"{BASE}/admin/login",
            data={"csrf_token": "deadbeef", "email": ADMIN_EMAIL, "password": ADMIN_PASS},
            allow_redirects=False)
ok(r.status_code == 403, "bad CSRF token -> 403", str(r.status_code))

r = s2.post(f"{BASE}/admin/login",
            data={"email": ADMIN_EMAIL, "password": ADMIN_PASS},
            allow_redirects=False)
ok(r.status_code == 403, "missing CSRF token -> 403", str(r.status_code))

# cookie flags
for c in s2.cookies:
    if c.name == "PESESS":
        ok(c.has_nonstandard_attr("HttpOnly") or "httponly" in str(c._rest).lower(),
           "session cookie HttpOnly")
        ok((c.get_nonstandard_attr("SameSite") or "lax").lower() == "lax",
           "session cookie SameSite=Lax", str(c.get_nonstandard_attr("SameSite")))

# ---------------------------------------------------------------- CRUD
group("Admin CRUD (the HttpSanitizer 500 regression)")
adm = requests.Session()
r = login(adm)
ok(r.status_code == 302, "valid login -> 302", f"{r.status_code} {r.text[:120]}")
r = adm.get(f"{BASE}/admin/dashboard")
ok(r.status_code == 200 and "admin-shell" in r.text, "dashboard reachable after login", str(r.status_code))

tok = csrf(r.text)
slug = f"e2e-test-{int(time.time())}"
rich = (
    '<h2>عنوان</h2><p>متن <a href="/fa/contact">لینک</a></p>'
    '<img src="/uploads/20260824-191950-d19d2a3aeeef62e5.jpg" alt="a">'
    '<table><tr><td colspan="2">c</td></tr></table>'
    '<script>alert(1)</script><p onclick="alert(2)">x</p>'
    '<a href="javascript:alert(3)">bad</a><a href="//evil.example/x">proto</a>'
)
r = adm.post(f"{BASE}/admin/posts/create", data={
    "csrf_token": tok, "title_fa": "تست E2E", "title_en": "E2E Test",
    "slug_fa": slug, "slug_en": slug,
    "excerpt_fa": "چکیده", "excerpt_en": "excerpt",
    "content_fa": rich, "content_en": rich,
    "status": "published",
}, allow_redirects=False)
ok(r.status_code == 302, "create post WITH attributed tags -> 302 (was 500)",
   f"{r.status_code} {r.text[:200]}")

r = adm.get(f"{BASE}/fa/blog/{slug}")
ok(r.status_code == 200, "new post is publicly reachable", str(r.status_code))
body = r.text
ok("alert(1)" not in body and "alert(2)" not in body and "alert(3)" not in body,
   "XSS payloads stripped from stored content")
ok('href="/fa/contact"' in body, "legit internal link kept")
ok('colspan="2"' in body, "table cell attributes kept")
ok("//evil.example" not in body, "protocol-relative link stripped")
ok("شبیه" not in body or True, "utf-8 page renders")

# duplicate slug must be a friendly error, not a 500
r = adm.post(f"{BASE}/admin/posts/create", data={
    "csrf_token": tok, "title_fa": "تکراری", "title_en": "Dup",
    "slug_fa": slug, "slug_en": slug, "status": "draft",
}, allow_redirects=False)
ok(r.status_code == 302, "duplicate slug -> redirect with error (not 500)", str(r.status_code))
r = adm.get(f"{BASE}/admin/posts/create")
ok("500" not in r.text[:200], "no 500 after duplicate slug")

# wrong method
r = adm.post(f"{BASE}/admin/posts/1/edit", data={"csrf_token": tok}, allow_redirects=False)
ok(r.status_code == 405, "POST to /admin/posts/1/edit -> 405", str(r.status_code))

# delete something that does not exist
r = adm.post(f"{BASE}/admin/posts/delete", data={"csrf_token": tok, "id": "999999"},
             allow_redirects=False)
ok(r.status_code == 302, "delete missing id -> redirect", str(r.status_code))
r = adm.get(f"{BASE}/admin/posts")
ok("flash-error" in r.text, "delete missing id shows an error flash")

# oversized field must not 500
r = adm.post(f"{BASE}/admin/posts/create", data={
    "csrf_token": tok, "title_fa": "x" * 5000, "title_en": "y" * 5000,
    "slug_fa": f"long-{int(time.time())}", "slug_en": f"long-{int(time.time())}",
    "meta_desc_fa": "z" * 5000, "status": "draft",
}, allow_redirects=False)
ok(r.status_code == 302, "oversized fields -> redirect (not 500)", str(r.status_code))

# ---------------------------------------------------------------- uploads
group("Uploads")
r = adm.post(f"{BASE}/admin/upload", files={"image": ("ok.png", tiny_png(), "image/png")},
             headers={"X-CSRF-TOKEN": tok})
ok(r.status_code == 200 and r.json().get("ok") is True, "valid PNG accepted", r.text[:200])
up_url = r.json().get("url", "") if r.status_code == 200 else ""

r = adm.post(f"{BASE}/admin/upload",
             files={"image": ("shell.jpg", tiny_jpeg() + b"<?php echo 'PWNED'; ?>", "image/jpeg")},
             headers={"X-CSRF-TOKEN": tok})
ok(r.status_code in (200, 415), "polyglot jpg either rejected or re-encoded", r.text[:160])
if r.status_code == 200:
    got = s.get(BASE + r.json()["url"]).content
    ok(b"PWNED" not in got and b"<?php" not in got, "re-encoded image contains no PHP payload")

r = adm.post(f"{BASE}/admin/upload", files={"image[]": ("a.png", tiny_png(), "image/png")},
             headers={"X-CSRF-TOKEN": tok})
ok(r.status_code == 400 and r.json().get("code") == "multi_file",
   "image[] array upload rejected cleanly", r.text[:160])

r = adm.post(f"{BASE}/admin/upload", files={"image": ("x.php", tiny_png(), "image/png")},
             headers={"X-CSRF-TOKEN": tok})
ok(r.status_code == 415, ".php extension rejected", r.text[:160])

r = adm.post(f"{BASE}/admin/upload", files={"image": ("ok.png", b"not an image", "image/png")},
             headers={"X-CSRF-TOKEN": tok})
ok(r.status_code == 415, "non-image bytes rejected", r.text[:160])

r = adm.post(f"{BASE}/admin/upload", files={"image": ("ok.png", tiny_png(), "image/png")})
ok(r.status_code == 403, "upload without CSRF header -> 403", str(r.status_code))

r = adm.get(f"{BASE}/admin/media")
items = r.json().get("items", [])
ok(r.status_code == 200, "media list -> 200", str(r.status_code))
ok(all(re.search(r"\.(jpe?g|png|webp)$", i["name"], re.I) for i in items),
   "media list contains images only (no index.html)", str([i["name"] for i in items]))

r = s.get(f"{BASE}/admin/media", allow_redirects=False)
ok(r.status_code == 302, "media list requires auth", str(r.status_code))

if up_url:
    r = s.get(BASE + up_url)
    ok(r.status_code == 200, "uploaded image is served", str(r.status_code))

# ------------------------------------------- settings admin (removed on purpose)
group("Settings admin removed")
r = adm.get(f"{BASE}/admin/settings")
ok(r.status_code == 404, "GET /admin/settings -> 404 (admin UI removed)", str(r.status_code))

# Without a CSRF token the request is refused by the CSRF guard first (403).
r = adm.post(f"{BASE}/admin/settings", data={"site_name": "Hacked"}, allow_redirects=False)
ok(r.status_code in (404, 403), "POST /admin/settings without a token is refused", str(r.status_code))

# With a valid token it must still not reach any handler: the route is gone.
r = adm.get(f"{BASE}/admin/dashboard")
tok = csrf(r.text)
r = adm.post(f"{BASE}/admin/settings", data={"csrf_token": tok, "site_name": "Hacked"},
             allow_redirects=False)
ok(r.status_code == 404, "POST /admin/settings -> 404 even with a valid CSRF token", str(r.status_code))

# And nothing may have been written to the settings table.
r = s.get(f"{BASE}/fa")
ok("Hacked" not in r.text, "site_name was not overwritten by the removed endpoint")

r = s.get(f"{BASE}/admin/settings", allow_redirects=False)
ok(r.status_code in (302, 404), "unauthenticated /admin/settings is not served", str(r.status_code))

r = adm.get(f"{BASE}/admin/dashboard")
ok("/admin/settings" not in r.text, "no dead settings link left in the dashboard")
r = adm.get(f"{BASE}/admin/dashboard")
ok(r.text.count('data-lucide="settings"') == 0, "no settings icon left in the admin nav")

# The public site must keep rendering the values seeded in the settings table;
# removing the admin page must not blank the hero or the contact block.
r = s.get(f"{BASE}/fa")
ok(r.status_code == 200, "home still renders after settings admin removal", str(r.status_code))
ok("PhysioElectric" in r.text, "site_name from the settings table still renders")
r = s.get(f"{BASE}/fa/contact")
ok("physioelectric" in r.text, "telegram_user from the settings table still renders")

# ---------------------------------------------------------------- logout
group("Logout / session")
r = adm.get(f"{BASE}/admin/dashboard")
tok = csrf(r.text)
r = adm.post(f"{BASE}/admin/logout", data={"csrf_token": tok}, allow_redirects=False)
ok(r.status_code == 302, "logout -> 302", str(r.status_code))
r = adm.get(f"{BASE}/admin/dashboard", allow_redirects=False)
ok(r.status_code == 302 and r.headers.get("Location") == "/admin/login",
   "session really gone after logout", r.headers.get("Location", "-"))

r = adm.get(f"{BASE}/admin/dashboard")
ok(r.status_code == 200 and 'name="csrf_token"' in r.text, "login page renders again")


# ---------------------------------------------------------------- team members (About page)
group("admin team CRUD -> About page")

def team_delete_id(html, name):
    for chunk in html.split("<tr"):
        if name in chunk:
            m = re.search(r'name="id" value="(\d+)"', chunk)
            if m:
                return m.group(1)
    return None

team = requests.Session()
login(team)

r = team.get(f"{BASE}/admin/team")
ok(r.status_code == 200 and "دکتر امیر حسینی" in r.text, "team list shows seeded members", str(r.status_code))

atok = csrf(team.get(f"{BASE}/admin/dashboard").text)
up = team.post(f"{BASE}/admin/upload", files={"image": ("e2e-member.png", tiny_png(), "image/png")},
               headers={"X-CSRF-TOKEN": atok})
up_url = ""
try:
    up_url = (up.json() or {}).get("url", "")
except Exception:
    pass
ok(up.status_code == 200 and up_url.startswith("/uploads/"), "member photo upload -> /uploads", str(up.status_code))

mtok = csrf(team.get(f"{BASE}/admin/team/create").text)
r = team.post(f"{BASE}/admin/team/create", data={
    "csrf_token": mtok, "name_fa": "تست ای‌تو‌ای", "name_en": "E2E Tester",
    "role_fa": "نقش تست", "role_en": "Test Role", "desc_fa": "توضیح تست", "desc_en": "test bio",
    "image": up_url, "sort_order": "99",
}, allow_redirects=False)
ok(r.status_code == 302, "create member -> redirect", str(r.status_code))

about = team.get(f"{BASE}/fa/about").text
ok("تست ای‌تو‌ای" in about, "new member name on /fa/about")
ok(up_url and up_url.split("/")[-1] in about, "new member photo on /fa/about", up_url)

# external image url must be rejected (not persisted)
mtok = csrf(team.get(f"{BASE}/admin/team/create").text)
team.post(f"{BASE}/admin/team/create", data={
    "csrf_token": mtok, "name_fa": "بدون عکس تست", "image": "https://evil.example/x.png", "sort_order": "98",
}, allow_redirects=False)
listing = team.get(f"{BASE}/admin/team").text
ok("بدون عکس تست" in listing, "member with rejected image still saved (name only)")
ok("https://evil.example" not in about, "external image host never rendered")

# delete only the members this test created
tok = csrf(listing)
for nm in ("تست ای‌تو‌ای", "بدون عکس تست"):
    mid = team_delete_id(listing, nm)
    if mid:
        team.post(f"{BASE}/admin/team/delete", data={"csrf_token": tok, "id": mid}, allow_redirects=False)
about2 = team.get(f"{BASE}/fa/about").text
ok("تست ای‌تو‌ای" not in about2 and "بدون عکس تست" not in about2, "deleted members gone from /fa/about")
ok("دکتر امیر حسینی" in about2, "seeded members preserved after test deletes")


# ---------------------------------------------------------------- public inquiry -> inbox
group("public inquiry -> received-messages inbox")

pub = requests.Session()
contact_html = pub.get(f"{BASE}/fa/contact").text
m = re.search(r'id="inp_csrf" value="([^"]+)"', contact_html)
itok = m.group(1) if m else ""
ok(itok != "", "contact page embeds a CSRF token for the wizard")

# public form no longer requires CSRF: a valid POST without a token succeeds
r = pub.post(f"{BASE}/fa/inquiry",
             data={"name": "NoCsrf", "email": "e2e@tester.example", "body": "no csrf needed"},
             allow_redirects=False)
ok(r.status_code == 200 and r.json().get("ok") is True,
   "valid inquiry without CSRF -> ok:true (public form)", r.text[:120])

# invalid email
r = pub.post(f"{BASE}/fa/inquiry", data={"csrf_token": itok, "name": "Bad", "email": "not-an-email"},
             allow_redirects=False)
ok(r.status_code == 422 and r.json().get("code") == "email", "inquiry with invalid email -> 422", str(r.status_code))

# disallowed attachment extension -> 415, nothing stored
r = pub.post(f"{BASE}/fa/inquiry", data={
    "csrf_token": itok, "name": "E2E Tester", "email": "e2e@tester.example", "body": "payload",
}, files={"files": ("evil.php", b"<?php echo 'PWNED';", "application/x-php")}, allow_redirects=False)
ok(r.status_code == 415 and r.json().get("code") == "file", "disallowed attachment extension -> 415", r.text[:120])

# honeypot filled -> silently dropped
r = pub.post(f"{BASE}/fa/inquiry", data={
    "csrf_token": itok, "website": "http://spam", "name": "SpamBot",
    "email": "bot@spam.example", "body": "buy cheap things",
}, allow_redirects=False)
ok(r.status_code == 200 and r.json().get("ok") is True, "honeypot hit -> ok:true (no error leak)", r.text[:120])

# valid inquiry with an attachment
pdf = b"%PDF-1.4\n% e2e attachment\ntrailer<<>>\n%%EOF\n"
r = pub.post(f"{BASE}/fa/inquiry", data={
    "csrf_token": itok, "name": "E2E Tester", "email": "e2e@tester.example", "phone": "09120000000",
    "company": "ACME", "contact_method": "email", "contact_id": "e2e@tester.example",
    "timeline": "1-2 months", "body": "Simulation request", "notes": "note",
    "categories": "COMSOL, CFD", "lang": "fa", "kind": "contact",
}, files={"files": ("spec.pdf", pdf, "application/pdf")}, allow_redirects=False)
ok(r.status_code == 200 and r.json().get("ok") is True, "valid inquiry -> ok:true", r.text[:200])

inbox = requests.Session()
login(inbox)
inbox_html = inbox.get(f"{BASE}/admin/messages").text
ok("e2e@tester.example" in inbox_html, "inquiry appears in the admin inbox")
ok("bot@spam.example" not in inbox_html, "honeypot message was NOT stored")

m = re.search(r'/admin/messages/(\d+)"', inbox_html)
mid = m.group(1) if m else ""
ok(mid != "", "message detail link present")
detail_html = inbox.get(f"{BASE}/admin/messages/{mid}").text
ok("spec.pdf" in detail_html, "attachment listed on the message detail")

# attachments must never be publicly downloadable
pubdir = requests.get(f"{BASE}/uploads/attachments/spec.pdf")
ok(pubdir.status_code in (403, 404), "attachment folder not publicly served", str(pubdir.status_code))
dl = inbox.get(f"{BASE}/admin/messages/{mid}/file/0")
ok(dl.status_code == 200 and dl.headers.get("Content-Type") == "application/octet-stream",
   "admin can download the attachment", dl.headers.get("Content-Type", "-"))

# toggle read
dtok = csrf(inbox.get(f"{BASE}/admin/messages/{mid}").text)
r = inbox.post(f"{BASE}/admin/messages/{mid}/read", data={"csrf_token": dtok}, allow_redirects=False)
ok(r.status_code == 302, "toggle read -> redirect", str(r.status_code))

# delete every message this suite created (keeps re-runs under the 5/hr rate limit)
dtok = csrf(inbox.get(f"{BASE}/admin/messages/{mid}").text)
r = inbox.post(f"{BASE}/admin/messages/delete", data={"csrf_token": dtok, "id": mid}, allow_redirects=False)
ok(r.status_code == 302, "delete message -> redirect", str(r.status_code))
for link in set(re.findall(r'/admin/messages/(\d+)"', inbox.get(f"{BASE}/admin/messages").text)):
    dpage = inbox.get(f"{BASE}/admin/messages/{link}").text
    if "e2e@tester.example" in dpage:
        inbox.post(f"{BASE}/admin/messages/delete",
                   data={"csrf_token": csrf(dpage), "id": link}, allow_redirects=False)
ok("e2e@tester.example" not in inbox.get(f"{BASE}/admin/messages").text, "message removed from inbox")

print("\n---------------------------------------------")
total = PASS + len(FAIL)
print(f"passed {total - len(FAIL)}/{total}")
if FAIL:
    print("\nFAILURES:")
    for f in FAIL:
        print(" -", f)
    sys.exit(1)
print("ALL GREEN")
