<?php require_once __DIR__ . '/../../../core/auth.php'; ?>

<h2 class="form-title">🎟️ Crear cupón</h2>

<?php if (!empty($message)): ?>
  <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>admin/cupones/crear" data-ajax enctype="application/x-www-form-urlencoded" class="form-block">
  <?= csrf_field(); ?>

  <div class="form-group">
    <label for="code">Código*</label>
    <input type="text" id="code" name="code" required placeholder="EJEMPLO10" style="text-transform:uppercase;">
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="discount_type">Tipo de descuento</label>
      <select id="discount_type" name="discount_type">
        <option value="percentage">Porcentaje (%)</option>
        <option value="fixed">Monto fijo ($)</option>
      </select>
    </div>

    <div class="form-group">
      <label for="discount_value">Valor del descuento*</label>
      <input type="number" id="discount_value" name="discount_value" step="0.01" min="0" required>
    </div>
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="max_uses">Usos máximos</label>
      <input type="number" id="max_uses" name="max_uses" min="0" placeholder="Dejar vacío = ilimitado">
    </div>

    <div class="form-group">
      <label for="minimum_order">Compra mínima ($)</label>
      <input type="number" id="minimum_order" name="minimum_order" step="0.01" min="0" value="0">
    </div>
  </div>

  <div class="form-group">
    <label for="expiry_date">Fecha de expiración</label>
    <input type="datetime-local" id="expiry_date" name="expiry_date">
  </div>

  <div class="form-group">
    <label>
      <input type="checkbox" name="is_active" checked>
      Cupón activo
    </label>
  </div>

  <button type="submit" class="btn-primary">💾 Guardar cupón</button>
</form>
