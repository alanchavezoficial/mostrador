<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>
<form method="POST"
      action="<?= BASE_URL ?>admin/contacto/editar"
      data-ajax-form
      data-ajax
      class="form-block">
  <?= csrf_field(); ?>

  <h2 class="form-title">Editar Campo de Contacto</h2>

  <input type="hidden" name="id" value="<?= $contact['id'] ?>">

  <div class="form-group">
    <label for="edit-label">Etiqueta</label>
    <input type="text" name="label"
           id="edit-label"
           value="<?= htmlspecialchars($contact['label']) ?>"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="edit-field_type">Tipo de campo</label>
    <select name="field_type" id="edit-field_type" class="form-input" required>
      <option value="text" <?= $contact['field_type'] === 'text' ? 'selected' : '' ?>>Texto</option>
      <option value="email" <?= $contact['field_type'] === 'email' ? 'selected' : '' ?>>Email</option>
      <option value="phone" <?= $contact['field_type'] === 'phone' ? 'selected' : '' ?>>Teléfono</option>
      <option value="url" <?= $contact['field_type'] === 'url' ? 'selected' : '' ?>>URL</option>
      <option value="address" <?= $contact['field_type'] === 'address' ? 'selected' : '' ?>>Dirección</option>
    </select>
  </div>

  <div class="form-group">
    <label for="edit-field_value">Valor</label>
    <input type="text" name="field_value"
           id="edit-field_value"
           value="<?= htmlspecialchars($contact['field_value']) ?>"
           class="form-input" required>
  </div>

  <div class="form-group">
    <label for="edit-icon">Icono (emoji)</label>
    <input type="text" name="icon"
           id="edit-icon"
           value="<?= htmlspecialchars($contact['icon']) ?>"
           class="form-input">
  </div>

  <div class="form-group">
    <label for="edit-sort_order">Orden</label>
    <input type="number" name="sort_order"
           id="edit-sort_order"
           value="<?= $contact['sort_order'] ?>"
           class="form-input">
  </div>

  <div class="form-group-inline">
    <label>
      <input type="checkbox" name="is_visible" <?= $contact['is_visible'] ? 'checked' : '' ?>>
      Visible públicamente
    </label>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn">💾 Actualizar</button>
  </div>
</form>
