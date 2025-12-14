<?php
require_once __DIR__ . '/../../../core/auth.php';
$requiredScripts = [
    'admin/ajax-edit.js',
    'admin/ajax-form.js',
    'admin/ajax-delete.js',
    'admin/ajax-reload.js'
];
?>

<div class="table-block">
    <h2 class="table-title">🛠️ Configuraciones del sistema</h2>

    <?php if (!empty($message)): ?>
        <div class="alert success"><?= $message ?></div>
    <?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Clave</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (is_array($settings) && count($settings)): ?>
                <?php foreach ($settings as $item): ?>
                    <?php if (is_array($item)): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($item['clave'] ?? '—') ?></td>
                            <td>
                                <?php
                                $valor = trim($item['valor'] ?? '');
                                $tipo  = $item['tipo'] ?? 'texto';

                                switch ($tipo) {
                                    case 'color':
                                        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $valor)):
                                            echo '<div style="width:22px; height:22px; border-radius:4px; border:1px solid black; background:' . htmlspecialchars($valor) . ';" title="' . htmlspecialchars($valor) . '"></div>';
                                        else:
                                            echo htmlspecialchars($valor);
                                        endif;
                                        break;

                                    case 'booleano':
                                        echo ($valor === '1') ? '✅' : '❌';
                                        break;

                                    case 'enlace':
                                        echo '<a href="' . htmlspecialchars($valor) . '" target="_blank" title="' . htmlspecialchars($valor) . '">🔗 Enlace</a>';
                                        break;

                                    case 'email':
                                        echo '<a href="mailto:' . htmlspecialchars($valor) . '">' . htmlspecialchars($valor) . '</a>';
                                        break;

                                    default:
                                        echo nl2br(htmlspecialchars($valor));
                                        break;
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($tipo) ?></td>
                            <td class="col-actions">
                                <a href="<?= BASE_URL ?>admin/configuraciones?view=edit&id=<?= $item['id']?>"
                                   class="btn-edit"
                                   title="Editar configuración">✏️</a>
                                <a href="#"
                                   class="btn-delete"
                                   data-ajax-delete
                                   data-url="<?= BASE_URL ?>admin/configuraciones/delete"
                                   data-id="<?= $item['id'] ?? '' ?>"
                                   data-confirm="¿Eliminar esta configuración?"
                                   title="Eliminar configuración">🗑️</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">⚠️ No hay configuraciones registradas</td>
                </tr>
            <?php endif; ?>
        </tbody>

    </table>
</div>
