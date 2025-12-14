</main>

<footer>
    <p>&copy; 2025 Props Fotográficos</p>
</footer>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>public/js/toast.js"></script>
    <script src="<?= BASE_URL ?>public/js/analytics.js"></script>
    <?php if (isset($page_js)): ?>
        <script src="<?= BASE_URL ?>public/js/<?= htmlspecialchars($page_js) ?>"></script>
    <?php endif; ?>
</body>
</html>