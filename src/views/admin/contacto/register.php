<?php 
require_once __DIR__ . '/../../../core/auth.php'; 
?>

<h2 class="form-title">📞 Nuevo Campo de Contacto</h2>

<form method="POST" action="<?= BASE_URL ?>admin/contacto/crear" class="form-block">
  <?= csrf_field(); ?>

  <!-- Clave única -->
  <div class="form-group">
    <label for="field_key">Clave del campo (única):</label>
    <input type="text" id="field_key" name="field_key" required 
           placeholder="ej: email, telefono, direccion">
  </div>

  <!-- Etiqueta -->
  <div class="form-group">
    <label for="label">Etiqueta:</label>
    <input type="text" id="label" name="label" required 
           placeholder="ej: Correo Electrónico">
  </div>

  <!-- Tipo de campo -->
  <div class="form-group">
    <label for="field_type">Tipo de campo:</label>
    <select id="field_type" name="field_type" required>
      <option value="text">Texto</option>
      <option value="email">Email</option>
      <option value="phone">Teléfono</option>
      <option value="url">URL</option>
      <option value="address">Dirección</option>
    </select>
  </div>

  <!-- Valor -->
  <div class="form-group">
    <label for="field_value">Valor:</label>
    <input type="text" id="field_value" name="field_value" required 
           placeholder="ej: contacto@empresa.com">
  </div>

  <!-- Icono -->
  <div class="form-group">
    <label for="icon">Icono (emoji):</label>
    <input type="text" id="icon" name="icon" 
           placeholder="ej: 📧">
  </div>

  <!-- Orden -->
  <div class="form-group">
    <label for="sort_order">Orden de visualización:</label>
    <input type="number" id="sort_order" name="sort_order" value="0">
  </div>

  <!-- Visibilidad -->
  <div class="form-group">
    <label>
      <input type="checkbox" name="is_visible" checked>
      Visible en el sitio
    </label>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">💾 Guardar campo</button>
  </div>
</form>
