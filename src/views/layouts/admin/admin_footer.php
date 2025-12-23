</main>
<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>public/js/rich-editor.js"></script>
<script src="<?= BASE_URL ?>public/js/dark-mode.js"></script>
<script src="<?= BASE_URL ?>public/js/accessibility.js"></script>
<?php if (isset($requiredScripts)): ?>
  <?php foreach ($requiredScripts as $script): ?>
    <script src="<?= BASE_URL ?>public/js/<?= $script ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>
<script src="<?= BASE_URL ?>public/js/menu.js"></script>
<script src="<?= BASE_URL ?>public/js/toast.js"></script>

</body>
</html>
