# Manual Técnico: Seguridad y Analytics Admin

## Cambios y Mejoras Realizadas (Diciembre 2025)

### 1. Exportación de Analytics
- Se implementó la exportación de eventos y estadísticas a PDF y CSV desde el dashboard admin.
- Se usan jsPDF, autoTable y Chart.js para generar los reportes.

### 2. Seguridad de Endpoints con API Key
- Todos los endpoints sensibles de analytics requieren una API key única por usuario.
- La API key se genera automáticamente y se almacena en la tabla `users` (columna `api_key`).
- El backend valida la key en cada petición y solo responde si es válida.
- El frontend inyecta la key en cada petición AJAX usando `window.ADMIN_API_KEY`.

### 3. Gestión de API Key
- Solo el usuario con rol "Dueno" (dueño) puede ver la API key de todos los usuarios desde el panel de administración.
- La columna API key solo aparece para el Dueno en la tabla de usuarios.
- Si un usuario no tiene key, se genera automáticamente al cargar el dashboard.

### 4. Corrección de Errores Críticos
- Se eliminaron errores de sintaxis y llaves en `AnalyticsController.php`.
- Se corrigió la validación de API key para evitar errores fatales y mostrar mensajes claros de SQL.
- Se ajustó la consulta SQL para no requerir columnas inexistentes.
- Se mejoró la inyección de la key en el HTML para evitar contaminación por errores previos.

### 5. Debug y Logging
- Se agregaron logs en el backend para depurar validación de API key y errores SQL.
- El frontend muestra en consola la key y la URL de cada petición de stats para facilitar el debug.

### 6. Migraciones y Base de Datos
- Se agregó el script `add_api_key_and_usage.sql` para crear la columna `api_key` en `users` y la tabla de logs de uso.
- Si la columna no existe, debe agregarse manualmente:
  ```sql
  ALTER TABLE users ADD COLUMN api_key VARCHAR(64) UNIQUE AFTER password;
  ```

### 7. Buenas Prácticas
- Los endpoints devuelven siempre JSON limpio (sin HTML ni warnings).
- El dashboard solo muestra información sensible a usuarios autorizados.
- El código está documentado y estructurado para facilitar mantenimiento y auditoría.

---

**Nota:** Si necesitas agregar más roles, cambiar la longitud de la key, o personalizar la exportación, sigue el patrón de seguridad y validación implementado aquí.
