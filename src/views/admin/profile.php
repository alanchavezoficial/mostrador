<?php
if (!isset($user) || !in_array($user['role'], ['admin', 'Dueno'])) {
    die('Acceso denegado');
}
?>

<style>
    .admin-profile-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        max-width: 800px;
        margin: 0 auto;
    }

    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #dc3545;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .profile-info h2 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .profile-info p {
        margin: 3px 0;
        color: #6c757d;
        font-size: 14px;
    }

    .profile-info .role-badge {
        display: inline-block;
        background: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin-top: 8px;
        font-weight: bold;
    }

    .profile-section {
        background: white;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-section h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    .form-group .help-text {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .btn-save, .btn-cancel, .btn-change-password {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
    }

    .btn-save {
        background: #28a745;
        color: white;
    }

    .btn-save:hover {
        background: #218838;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .btn-change-password {
        background: #dc3545;
        color: white;
    }

    .btn-change-password:hover {
        background: #c82333;
    }

    .alert {
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 25px;
        border: 1px solid #888;
        width: 90%;
        max-width: 400px;
        border-radius: 8px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }
</style>

<div class="admin-profile-container">
    <!-- Encabezado del Perfil -->
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user['nombre'] ?? $user['email'], 0, 1)); ?>
        </div>
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['nombre']); ?></h2>
            <p>📧 <?php echo htmlspecialchars($user['email']); ?></p>
            <p>🆔 Usuario ID: #<?php echo $user['id']; ?></p>
            <span class="role-badge">⚡ <?php echo strtoupper($user['role']); ?></span>
        </div>
    </div>

    <!-- Sección de Información Personal -->
    <div class="profile-section">
        <h3>👤 Información Personal</h3>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="alert alert-success">
                ✓ Perfil actualizado correctamente
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>admin/perfil-actualizar">
            <?= csrf_field(); ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                    <div class="help-text">Tu nombre completo</div>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <div class="help-text">Tu correo para notificaciones</div>
                </div>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección (Opcional)</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($user['direccion'] ?? ''); ?>" placeholder="Calle, número, apartamento">
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono (Opcional)</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>" placeholder="+54 9 1234-5678">
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-save">💾 Guardar Cambios</button>
                <a href="<?= BASE_URL ?>admin/dashboard" class="btn-cancel">← Volver al Dashboard</a>
            </div>
        </form>
    </div>

    <!-- Sección de Seguridad -->
    <div class="profile-section">
        <h3>🔐 Seguridad</h3>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
            <p style="margin: 0 0 10px 0; font-weight: 500;">Última sesión iniciada:</p>
            <p style="margin: 0; color: #6c757d;">
                <?php 
                if (isset($user['last_login'])) {
                    echo date('d/m/Y H:i', strtotime($user['last_login']));
                } else {
                    echo 'Primera sesión';
                }
                ?>
            </p>
        </div>

        <p style="color: #6c757d; font-size: 14px; margin-bottom: 15px;">
            ✓ Tu contraseña se almacena de forma segura con encriptación bcrypt<br>
            ✓ Acceso al panel administrativo restringido por roles<br>
            ✓ Se registran todas las acciones administrativas
        </p>

        <button type="button" class="btn-change-password" onclick="openPasswordModal()">🔐 Cambiar Contraseña</button>
    </div>
</div>

<!-- Modal para cambiar contraseña -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePasswordModal()">&times;</span>
        <h3>🔐 Cambiar Contraseña</h3>
        
        <form onsubmit="submitPasswordChange(event)">
            <div class="form-group">
                <label for="currentPassword">Contraseña Actual:</label>
                <input type="password" id="currentPassword" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="newPassword">Nueva Contraseña:</label>
                <input type="password" id="newPassword" name="new_password" required minlength="8">
                <div class="help-text">Mínimo 8 caracteres</div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Confirmar Contraseña:</label>
                <input type="password" id="confirmPassword" name="confirm_password" required minlength="8">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-save" style="flex: 1;">Cambiar Contraseña</button>
                <button type="button" class="btn-cancel" onclick="closePasswordModal()" style="flex: 1;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.querySelector('#passwordModal form').reset();
}

function submitPasswordChange(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        alert('Las contraseñas no coinciden');
        return;
    }
    
    if (newPassword.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres');
        return;
    }
    
    fetch('<?= BASE_URL ?>admin/cambiar-contrasena', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contraseña actualizada correctamente');
            closePasswordModal();
        } else {
            alert('Error: ' + (data.message || 'No se pudo cambiar la contraseña'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar la contraseña');
    });
}

window.onclick = function(event) {
    const modal = document.getElementById('passwordModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
