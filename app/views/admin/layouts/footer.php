        </div><!-- /p-6 -->
    </main>
</div><!-- /admin-shell -->

<script src="/assets/js/lucide.min.js"></script>
<script src="/assets/js/admin.js" data-csrf="<?= e(Csrf::token()) ?>"></script>
<script nonce="<?= e(\Security::nonce()) ?>">
    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();
    });
</script>
</body>
</html>
