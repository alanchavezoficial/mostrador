</main>

<footer>
    <p>&copy; 2025 Props Fotográficos</p>
</footer>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>public/js/menu.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/analytics.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/wishlist.js"></script>
    <?php if (isset($page_js)): ?>
        <script src="<?php echo BASE_URL; ?>public/js/<?= htmlspecialchars($page_js) ?>"></script>
    <?php endif; ?>
</body>
</html>