<?php require_once __DIR__ . '/../../../core/auth.php'; ?>

<form method="POST"
      action="<?= BASE_URL ?>admin/cupones/editar?ajax=1"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar cupón</h2>
  <input type="hidden" name="id" value="<?= $coupon['id'] ?>">

  <div class="form-group">
    <label for="edit-code">Código*</label>
    <input type="text" id="edit-code" name="code"
           value="<?= htmlspecialchars($coupon['code']) ?>"
           class="form-input" required style="text-transform:uppercase;">
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="edit-discount-type">Tipo</label>
      <select id="edit-discount-type" name="discount_type" class="form-input">
        <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Porcentaje (%)</option>
        <option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>Monto fijo ($)</option>
      </select>
    </div>

    <div class="form-group">
      <label for="edit-discount-value">Valor*</label>
      <input type="number" id="edit-discount-value" name="discount_value"
             value="<?= htmlspecialchars($coupon['discount_value']) ?>"
             step="0.01" min="0" class="form-input" required>
    </div>
  </div>

  <div class="form-group-inline">
    <div class="form-group">
      <label for="edit-max-uses">Usos máximos</label>
      <input type="number" id="edit-max-uses" name="max_uses"
             value="<?= htmlspecialchars($coupon['max_uses'] ?? '') ?>"
             min="0" class="form-input" placeholder="Ilimitado">
    </div>

    <div class="form-group">
      <label for="edit-minimum-order">Compra mínima ($)</label>
      <input type="number" id="edit-minimum-order" name="minimum_order"
             value="<?= htmlspecialchars($coupon['minimum_order']) ?>"
             step="0.01" min="0" class="form-input">
    </div>
  </div>

  <div class="form-group">
    <label for="edit-expiry-date">Fecha de expiración</label>
    <input type="datetime-local" id="edit-expiry-date" name="expiry_date"
           value="<?= !empty($coupon['expiry_date']) ? date('Y-m-d\TH:i', strtotime($coupon['expiry_date'])) : '' ?>"
           class="form-input">
  </div>

  <div class="form-group">
    <label for="edit-is-active">¿Activo?</label>
    <select name="is_active" id="edit-is-active" class="form-input">
      <option value="1" <?= $coupon['is_active'] ? 'selected' : '' ?>>Sí</option>
      <option value="0" <?= !$coupon['is_active'] ? 'selected' : '' ?>>No</option>
    </select>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-primary">💾 Guardar cambios</button>
  </div>
</form>
