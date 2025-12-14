<?php require_once __DIR__ . '/../../../core/auth.php'; ?>
<h2 class="table-title">🎟️ Cupones</h2>

<?php if (!empty($_GET['msg'])): ?>
  <div class="alert success">Acción completada: <?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="table-block" style="margin-bottom:16px;">
  <form method="POST" action="<?= BASE_URL ?>admin/cupones/crear" class="form-block">
    <?= csrf_field(); ?>
    <div class="form-group-inline">
      <div class="form-group">
        <label>Código*</label>
        <input type="text" name="code" required placeholder="EJEMPLO10">
      </div>
      <div class="form-group">
        <label>Tipo</label>
        <select name="discount_type">
          <option value="percentage">%</option>
          <option value="fixed">Monto fijo</option>
        </select>
      </div>
      <div class="form-group">
        <label>Valor*</label>
        <input type="number" name="discount_value" step="0.01" min="0" required>
      </div>
      <div class="form-group">
        <label>Uso máx</label>
        <input type="number" name="max_uses" min="0" placeholder="ilimitado">
      </div>
    </div>
    <div class="form-group-inline">
      <div class="form-group">
        <label>Expira</label>
        <input type="datetime-local" name="expiry_date">
      </div>
      <div class="form-group">
        <label>Mín. pedido</label>
        <input type="number" name="minimum_order" step="0.01" min="0">
      </div>
      <div class="form-group" style="align-self:flex-end;">
        <label>
          <input type="checkbox" name="is_active" checked> Activo
        </label>
      </div>
    </div>
    <button type="submit" class="btn-primary">Crear cupón</button>
  </form>
</div>

<div class="table-block">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Código</th>
        <th>Tipo</th>
        <th>Valor</th>
        <th>Usos</th>
        <th>Expira</th>
        <th>Mínimo</th>
        <th>Activo</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($coupons)): ?>
        <tr><td colspan="8" class="text-center">Sin cupones</td></tr>
      <?php else: ?>
        <?php foreach ($coupons as $c): ?>
          <tr data-coupon='<?= json_encode($c) ?>'>
            <td><?= htmlspecialchars($c['code']) ?></td>
            <td><?= $c['discount_type'] === 'fixed' ? 'Fijo' : '%' ?></td>
            <td><?= $c['discount_type'] === 'fixed' ? '$'.number_format($c['discount_value'],2,',','.') : number_format($c['discount_value'],2).'%'; ?></td>
            <td><?= (int)$c['current_uses'] ?>/<?= $c['max_uses'] === null ? '∞' : (int)$c['max_uses'] ?></td>
            <td><?= htmlspecialchars($c['expiry_date'] ?? '-') ?></td>
            <td>$<?= number_format($c['minimum_order'],2,',','.') ?></td>
            <td><?= $c['is_active'] ? '✅' : '—' ?></td>
            <td class="col-actions">
              <button class="btn-edit" data-edit-coupon>✏️</button>
              <form method="POST" action="<?= BASE_URL ?>admin/cupones/delete" style="display:inline;">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button class="btn-delete" onclick="return confirm('Eliminar cupón?')">🗑️</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="table-block" style="margin-top:16px;">
  <h3>Editar cupón</h3>
  <form id="edit-form" method="POST" action="<?= BASE_URL ?>admin/cupones/editar" class="form-block">
    <?= csrf_field(); ?>
    <input type="hidden" name="id" id="edit-id">
    <div class="form-group-inline">
      <div class="form-group">
        <label>Código*</label>
        <input type="text" name="code" id="edit-code" required>
      </div>
      <div class="form-group">
        <label>Tipo</label>
        <select name="discount_type" id="edit-type">
          <option value="percentage">%</option>
          <option value="fixed">Monto fijo</option>
        </select>
      </div>
      <div class="form-group">
        <label>Valor*</label>
        <input type="number" step="0.01" min="0" name="discount_value" id="edit-value" required>
      </div>
      <div class="form-group">
        <label>Uso máx</label>
        <input type="number" min="0" name="max_uses" id="edit-max">
      </div>
    </div>
    <div class="form-group-inline">
      <div class="form-group">
        <label>Expira</label>
        <input type="datetime-local" name="expiry_date" id="edit-expiry">
      </div>
      <div class="form-group">
        <label>Mín. pedido</label>
        <input type="number" step="0.01" min="0" name="minimum_order" id="edit-min">
      </div>
      <div class="form-group" style="align-self:flex-end;">
        <label><input type="checkbox" name="is_active" id="edit-active"> Activo</label>
      </div>
    </div>
    <button type="submit" class="btn-primary">Guardar cambios</button>
  </form>
</div>

<script>
  document.querySelectorAll('[data-edit-coupon]').forEach(btn => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      const data = JSON.parse(tr.dataset.coupon);
      document.getElementById('edit-id').value = data.id;
      document.getElementById('edit-code').value = data.code;
      document.getElementById('edit-type').value = data.discount_type;
      document.getElementById('edit-value').value = data.discount_value;
      document.getElementById('edit-max').value = data.max_uses ?? '';
      document.getElementById('edit-expiry').value = data.expiry_date ? data.expiry_date.replace(' ', 'T') : '';
      document.getElementById('edit-min').value = data.minimum_order;
      document.getElementById('edit-active').checked = data.is_active == 1;
      window.scrollTo({ top: document.getElementById('edit-form').offsetTop - 20, behavior: 'smooth' });
    });
  });
</script>
