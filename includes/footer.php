</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <strong><?= e(APP_NAME) ?></strong>
            <p><?= e(APP_TAGLINE) ?></p>
        </div>
        <div>
            <p>&#128222; (555) 123-4567</p>
            <p>&#128205; 123 Flavour Street, Food City</p>
            <p>&#128338; Mon&ndash;Sun, 10:00&ndash;23:00</p>
        </div>
        <div class="footer-copy">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.
        </div>
    </div>
</footer>
<script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
