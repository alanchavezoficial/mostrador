# 📦 Sistema E-commerce - Mostrador

Sistema de comercio electrónico completo desarrollado en PHP vanilla con arquitectura MVC personalizada. Incluye gestión de productos, carrito de compras, procesamiento de pedidos, cupones de descuento, wishlist, reviews, y panel de administración completo.

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Arquitectura](#-arquitectura)
- [Base de Datos](#-base-de-datos)
- [Rutas y Endpoints](#-rutas-y-endpoints)
- [Controladores](#-controladores)
- [Seguridad](#-seguridad)
- [Flujos de Trabajo](#-flujos-de-trabajo)
- [Uso del Sistema](#-uso-del-sistema)

---

## ✨ Características

### 🛒 E-commerce Público
- **Catálogo de Productos**: Búsqueda, filtros por categoría, rango de precio, ordenamiento
- **Carrito de Compras**: Agregar, actualizar, eliminar productos con persistencia en BD
- **Checkout**: Proceso de compra con validación de cupones
- **Cupones de Descuento**: Soporte para descuentos porcentuales y fijos
- **Wishlist**: Lista de deseos por usuario
- **Reviews/Calificaciones**: Sistema de valoraciones con estrellas (1-5)
- **Historial de Pedidos**: Visualización de órdenes, detalles y descarga de facturas
- **Seguimiento de Pedidos**: Consulta de estado por número de orden
- **SEO**: Meta tags dinámicos y sitemap.xml automatizado

### 🔐 Autenticación
- **Login/Registro**: Sistema de autenticación con hash de contraseñas (bcrypt)
- **Rate Limiting**: Protección contra fuerza bruta (5 intentos, bloqueo de 5 min)
- **Session Security**: Regeneración de ID de sesión, protección CSRF
- **Redirección Inteligente**: Redirige al checkout después del login si viene del carrito

### 👨‍💼 Panel de Administración
- **Dashboard**: Métricas y estadísticas del negocio
- **CRUD Completo** para:
  - ✅ Productos
  - ✅ Categorías
  - ✅ Artículos/Blog
  - ✅ Cupones de descuento
  - ✅ Pedidos (gestión de estados, tracking, envío)
  - ✅ Usuarios
  - ✅ Testimonios
  - ✅ Información de contacto
  - ✅ Configuraciones del sitio
- **Gestión de Pedidos**: 
  - Estados: pending, processing, shipped, delivered, cancelled, returned
  - Estados de pago: pending, completed, failed, refunded
  - Código de seguimiento y estado de envío
  - Notas internas
- **Operaciones AJAX**: Edición, eliminación y recarga sin page refresh
- **Toast Notifications**: Feedback visual de operaciones

### 📊 Analytics
- **Tracking de Eventos**: Página vista, clic en producto, add to cart, purchase
- **Dashboard con Gráficos**: Visualización con Chart.js

---

## 💻 Requisitos del Sistema

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior / MariaDB 10.2+
- **Servidor Web**: Apache con mod_rewrite / Nginx
- **Extensiones PHP**:
  - mysqli
  - session
  - json
  - mbstring

---

## 🚀 Instalación

### 1. Clonar o descargar el proyecto

```bash
git clone <repository-url> mostrador
cd mostrador
```

### 2. Configurar la base de datos

#### Crear la base de datos:
```sql
CREATE DATABASE ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Importar las tablas base:
```bash
mysql -u root -p ecommerce < scripts/database/ecommerce_clean.sql
mysql -u root -p ecommerce < scripts/database/testimonials_contact.sql
mysql -u root -p ecommerce < scripts/database/add_tracking_columns.sql
```

O desde phpMyAdmin:
1. Selecciona la base de datos `ecommerce`
2. Ve a la pestaña **SQL**
3. Importa cada archivo SQL en orden

#### Tablas creadas:
- `users` - Usuarios del sistema
- `products` - Productos del catálogo
- `categories` - Categorías de productos
- `cart` - Carrito de compras
- `orders` - Pedidos/Órdenes
- `order_items` - Items de cada pedido
- `coupons` - Cupones de descuento
- `wishlist` - Lista de deseos
- `reviews` - Calificaciones y reseñas
- `product_images` - Galería de imágenes de productos
- `returns` - Devoluciones
- `testimonials` - Testimonios de clientes
- `contact_info` - Información de contacto
- `settings` - Configuraciones del sistema

### 3. Configurar la aplicación

Edita `config/config.php`:

```php
<?php
define('BASE_URL', '/mostrador/'); // Ajusta según tu ruta
require_once __DIR__ . '/../src/core/db.php';
```

Edita `src/core/db.php`:

```php
<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Tu contraseña de MySQL
$db   = 'ecommerce';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');
```

### 4. Configurar el servidor web

#### Apache (.htaccess ya incluido):
Asegúrate de tener `mod_rewrite` habilitado:
```apache
a2enmod rewrite
service apache2 restart
```

#### Nginx:
```nginx
location /mostrador/ {
    try_files $uri $uri/ /mostrador/public/index.php?$args;
}
```

### 5. Crear usuario administrador

Ejecuta en MySQL:
```sql
INSERT INTO users (nombre, email, password, role) 
VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password (cámbiala después del login)
```

### 6. Acceder al sistema

- **Frontend**: http://localhost/mostrador/
- **Admin Panel**: http://localhost/mostrador/admin/dashboard
- **Login**: admin@example.com / password

---

## 🏗️ Arquitectura

### Patrón MVC Personalizado

```
mostrador/
├── config/
│   └── config.php              # Configuración global
├── public/                     # Punto de entrada público
│   ├── index.php               # Front controller
│   ├── css/                    # Estilos
│   ├── js/                     # Scripts del cliente
│   └── img/                    # Imágenes
├── src/
│   ├── controllers/            # Lógica de negocio
│   │   ├── AdminController.php
│   │   ├── CartController.php
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   ├── CouponController.php
│   │   └── ...
│   ├── core/                   # Núcleo del framework
│   │   ├── router.php          # Sistema de enrutamiento
│   │   ├── db.php              # Conexión a BD
│   │   ├── auth.php            # Autenticación
│   │   ├── csrf.php            # Protección CSRF
│   │   ├── session.php         # Manejo de sesiones
│   │   ├── security-headers.php # Headers HTTP de seguridad
│   │   └── view.php            # Motor de vistas
│   └── views/                  # Plantillas HTML/PHP
│       ├── admin/              # Vistas del admin
│       ├── layouts/            # Layouts compartidos
│       ├── cart/               # Vistas del carrito
│       ├── orders/             # Vistas de pedidos
│       └── productos/          # Vistas de productos
├── scripts/
│   └── database/               # Migraciones SQL
└── logout.php                  # Cierre de sesión
```

### Flujo de una Request

1. **Entrada**: `public/index.php` recibe todas las peticiones
2. **Seguridad**: Se cargan headers de seguridad y validación CSRF
3. **Enrutamiento**: `router.php` mapea URI → Controller::method
4. **Controller**: Procesa lógica, consulta BD, prepara datos
5. **View**: `View::render()` carga plantilla con datos
6. **Response**: HTML se envía al navegador

---

## 🗄️ Base de Datos

### Esquema Principal

#### Tabla: `orders`
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'),
    total_amount DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    coupon_code VARCHAR(50),
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded'),
    transaction_id VARCHAR(100),
    tracking_code VARCHAR(100),
    shipping_status VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Tabla: `coupons`
```sql
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed'),
    discount_value DECIMAL(10,2) NOT NULL,
    max_uses INT DEFAULT NULL,
    current_uses INT DEFAULT 0,
    expiry_date DATETIME DEFAULT NULL,
    minimum_order DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT 1
);
```

#### Tabla: `cart`
```sql
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id)
);
```

### Relaciones Clave

- `orders.user_id` → `users.id` (ON DELETE CASCADE)
- `order_items.order_id` → `orders.id` (ON DELETE CASCADE)
- `cart.user_id` → `users.id` (ON DELETE CASCADE)
- `wishlist.user_id` → `users.id` (ON DELETE CASCADE)
- `reviews.user_id` → `users.id` (ON DELETE CASCADE)

---

## 🛣️ Rutas y Endpoints

### Rutas Públicas (GET)

| Ruta | Controller::Method | Descripción |
|------|-------------------|-------------|
| `/` | - | Página de inicio |
| `/productos/catalogo` | CatalogController::index | Catálogo con filtros |
| `/productos/{id}` | ProductController::view | Detalle de producto |
| `/cart` | CartController::view | Ver carrito |
| `/checkout` | OrderController::checkout | Formulario de compra |
| `/orders/history` | OrderController::history | Historial de pedidos |
| `/orders/{id}` | OrderController::detail | Detalle de pedido |
| `/orders/invoice/{id}` | OrderController::invoice | Descargar factura |
| `/orders/track/{number}` | OrderController::track | Seguimiento de pedido |
| `/wishlist` | WishlistController::view | Lista de deseos |
| `/login` | - | Formulario de login |

### Rutas Públicas (POST)

| Ruta | Controller::Method | Descripción |
|------|-------------------|-------------|
| `/login` | UserController::login | Procesar login |
| `/cart/add` | CartController::add | Agregar al carrito |
| `/cart/update` | CartController::update | Actualizar cantidad |
| `/cart/remove` | CartController::remove | Quitar del carrito |
| `/checkout/place` | OrderController::place | Crear pedido |
| `/wishlist/toggle` | WishlistController::toggle | Agregar/quitar favorito |
| `/reviews/add` | ReviewController::add | Agregar review |

### Rutas Admin (GET)

| Ruta | Controller::Method | Descripción |
|------|-------------------|-------------|
| `/admin/dashboard` | AdminController::index | Dashboard principal |
| `/admin/productos` | ProductController::adminIndex | Lista de productos |
| `/admin/categorias` | CategoryController::index | Lista de categorías |
| `/admin/cupones` | CouponController::index | Lista de cupones |
| `/admin/pedidos` | OrderController::adminIndex | Lista de pedidos |
| `/admin/pedidos/editar-form` | OrderController::orderEditForm | Form editar pedido |
| `/admin/cupones/editar-form` | CouponController::couponEditForm | Form editar cupón |

### Rutas Admin (POST)

| Ruta | Controller::Method | Descripción |
|------|-------------------|-------------|
| `/admin/productos/crear` | ProductController::productCreate | Crear producto |
| `/admin/productos/editar` | ProductController::productEdit | Editar producto |
| `/admin/productos/delete` | ProductController::productDelete | Eliminar producto |
| `/admin/cupones/crear` | CouponController::couponCreate | Crear cupón |
| `/admin/cupones/editar` | CouponController::couponEdit | Editar cupón |
| `/admin/cupones/delete` | CouponController::couponDelete | Eliminar cupón |
| `/admin/pedidos/editar` | OrderController::orderEdit | Actualizar pedido |

---

## 🎮 Controladores

### CartController
**Funcionalidades**:
- `view()`: Muestra carrito con items y total
- `add()`: Agrega producto al carrito (requiere autenticación)
- `update()`: Actualiza cantidad de un item
- `remove()`: Elimina item del carrito

**Validaciones**:
- Usuario autenticado
- Producto existe
- Cantidad válida (>= 1)

### OrderController
**Funcionalidades Públicas**:
- `checkout()`: Formulario de compra con validación de cupones
- `place()`: Procesa pedido, aplica descuento, crea order + order_items, incrementa uso de cupón
- `history()`: Lista de pedidos del usuario
- `detail()`: Detalle de un pedido específico
- `invoice()`: Genera factura descargable (HTML)
- `track()`: Consulta estado por número de orden (JSON)

**Funcionalidades Admin**:
- `adminIndex()`: Lista todos los pedidos con join a users
- `orderEditForm()`: Carga pedido + items para edición
- `orderEdit()`: Actualiza estado, payment_status, tracking_code, shipping_status, notes

**Validaciones**:
- Cupón válido: activo, no vencido, stock disponible, monto mínimo
- Carrito no vacío
- Dirección de envío obligatoria
- CSRF token en formularios

### CouponController
**Funcionalidades**:
- `index()`: Vista con switch entre 'register' y 'table'
- `couponCreate()`: Crea cupón con validación de código único
- `couponEditForm()`: Carga cupón para modal de edición
- `couponEdit()`: Actualiza cupón existente
- `couponDelete()`: Elimina cupón (soft delete recomendado)

**Validaciones**:
- Código único y alfanumérico
- discount_value > 0
- max_uses >= 0 si se define
- expiry_date en formato válido
- minimum_order >= 0

### WishlistController
**Funcionalidades**:
- `toggle()`: Agrega o quita producto de favoritos
- `view()`: Muestra lista de deseos del usuario

### ReviewController
**Funcionalidades**:
- `add()`: Agrega review con rating, título y contenido
- Validaciones: rating 1-5, usuario autenticado

### SeoController
**Funcionalidades**:
- `sitemap()`: Genera sitemap.xml con productos dinámicamente

---

## 🔒 Seguridad

### Content Security Policy (CSP)
Configurado en `src/core/security-headers.php`:
- Permite scripts de: self, cdn.jsdelivr.net, translate.googleapis.com
- Permite estilos de: self, www.gstatic.com, translate.googleapis.com
- Imágenes: self, data:, www.gstatic.com
- Bloquea inline scripts maliciosos (con excepciones para desarrollo)

### Protección CSRF
- Token generado en `csrf.php` con `csrf_field()`
- Validación con `csrf_require()` en todos los POST
- Token único por sesión

### Autenticación
- **Passwords**: Hash bcrypt con `password_hash()`
- **Session Security**: 
  - `session_regenerate_secure()` después del login
  - HttpOnly y Secure flags en cookies (producción)
- **Rate Limiting**: 
  - Máximo 5 intentos de login
  - Bloqueo de 5 minutos por IP

### Headers HTTP
- `X-Frame-Options: SAMEORIGIN` - Anti clickjacking
- `X-Content-Type-Options: nosniff` - Anti MIME sniffing
- `X-XSS-Protection: 1; mode=block` - Protección XSS
- `Referrer-Policy: strict-origin-when-cross-origin`

### SQL Injection
- Uso de **Prepared Statements** en el 100% de las queries
- `mysqli::prepare()` + `bind_param()`

### XSS (Cross-Site Scripting)
- `htmlspecialchars()` en todas las salidas de usuario
- `ENT_QUOTES` para escapar comillas

---

## 🔄 Flujos de Trabajo

### Flujo de Compra Completo

```
1. Usuario navega catálogo
   └─> CatalogController::index (filtros, búsqueda)

2. Clic en producto
   └─> ProductController::view (galería, reviews, add to cart)

3. Agregar al carrito (requiere login)
   ├─> Si no autenticado → Redirect a /login?redirect=checkout
   └─> Si autenticado → CartController::add

4. Ver carrito
   └─> CartController::view (editar cantidades)

5. Proceder al pago
   ├─> Si no autenticado → Redirect a /login?redirect=checkout
   └─> Si autenticado → OrderController::checkout

6. Ingresar datos de envío + cupón (opcional)
   └─> Validación de cupón en tiempo real

7. Confirmar pedido
   └─> OrderController::place
       ├─> Valida cupón
       ├─> Crea order en BD
       ├─> Crea order_items
       ├─> Incrementa current_uses del cupón
       └─> Limpia carrito

8. Confirmación
   └─> Redirect a /orders/{id} con invoice
```

### Flujo de Gestión de Pedidos (Admin)

```
1. Admin accede a /admin/pedidos
   └─> OrderController::adminIndex
       └─> Query: SELECT orders + user.nombre

2. Clic en "Editar" (data-edit)
   └─> AJAX: orderEditForm (carga modal)
       └─> Muestra: productos, estados, tracking

3. Actualiza estado/tracking/notas
   └─> AJAX POST: orderEdit
       ├─> UPDATE orders SET status=?, payment_status=?, ...
       └─> Recarga tabla con adminIndex

4. Toast: "Pedido actualizado"
```

### Flujo de Cupón

```
1. Admin crea cupón en /admin/cupones?view=register
   └─> CouponController::couponCreate
       └─> INSERT INTO coupons

2. Usuario ingresa código en checkout
   └─> POST /checkout/place con coupon_code

3. Validación (OrderController::validateCoupon)
   ├─> Cupón existe y activo?
   ├─> No vencido?
   ├─> Tiene usos disponibles?
   ├─> Cumple monto mínimo?
   └─> Calcula descuento (percentage o fixed)

4. Si válido:
   ├─> Aplica descuento en total_amount
   ├─> Guarda coupon_code en orders
   └─> Incrementa current_uses

5. Si inválido:
   ├─> Flash error en sesión
   └─> Redirect a checkout con mensaje
```

---

## 📘 Uso del Sistema

### Crear un Producto

1. Ir a `/admin/productos?view=register`
2. Completar formulario:
   - Nombre, descripción, precio
   - Categoría, imagen principal
   - Stock, disponibilidad
3. Submit → `ProductController::productCreate`
4. Producto visible en catálogo público

### Crear un Cupón de Descuento

1. Ir a `/admin/cupones?view=register`
2. Configurar:
   - **Código**: Ej. `VERANO2024`
   - **Tipo**: Percentage (10%) o Fixed ($500)
   - **Valor**: Cantidad de descuento
   - **Máximo de usos**: 100 (o NULL para ilimitado)
   - **Fecha de expiración**: 2024-12-31
   - **Monto mínimo**: $5000 (opcional)
3. Submit → cupón activo

### Gestionar un Pedido

1. Ir a `/admin/pedidos`
2. Ver lista de pedidos con:
   - Número de orden
   - Cliente
   - Estado (badge de color)
   - Estado de pago
   - Total
   - Fecha
3. Clic en ✏️ editar
4. Actualizar:
   - **Estado del pedido**: pending → processing → shipped → delivered
   - **Estado de pago**: pending → completed
   - **Código de seguimiento**: ABC123456
   - **Estado de envío**: "En camino a destino"
   - **Notas internas**: Observaciones para el equipo
5. Guardar → toast de confirmación

### Ver Facturas

**Como cliente**:
1. Ir a `/orders/history`
2. Clic en "Ver" en un pedido
3. Clic en "Descargar Factura"
4. Se descarga invoice.html

**Como admin**:
- Los pedidos muestran toda la info en el modal de edición

---

## 🧪 Testing

### Playwright (Opcional)
Scripts de pruebas automatizadas en `scripts/playwright/`:
```bash
npm install
npx playwright test
```

Ver screenshots en `test-results/`

---

## 🚧 Características Pendientes (Roadmap)

- [ ] **Galería de imágenes**: Admin para subir múltiples imágenes por producto
- [ ] **Email Marketing**: Newsletter, carritos abandonados
- [ ] **RMA System**: Gestión completa de devoluciones
- [ ] **Stock Alerts**: Notificaciones de bajo inventario
- [ ] **Multi-idioma**: Soporte i18n
- [ ] **Payment Gateway**: Integración con Stripe/PayPal
- [ ] **Shipping Calculator**: Cálculo automático de envío
- [ ] **Advanced Analytics**: Reportes de ventas, productos más vendidos

---

## 📄 Licencia

Este proyecto es de uso libre. Modifica y distribuye según tus necesidades.

---

## 👥 Soporte

Para dudas o problemas:
1. Revisa la documentación completa
2. Verifica logs de PHP: `error_log`
3. Revisa consola del navegador para errores JS
4. Valida que las tablas existan: `SHOW TABLES;`

---

## 🎯 Comandos Útiles

### Verificar estructura de tablas:
```sql
DESCRIBE orders;
DESCRIBE coupons;
DESCRIBE cart;
```

### Crear usuario admin manualmente:
```sql
INSERT INTO users (nombre, email, password, role) 
VALUES ('Admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

### Resetear carrito de un usuario:
```sql
DELETE FROM cart WHERE user_id = 1;
```

### Ver pedidos pendientes:
```sql
SELECT order_number, status, total_amount FROM orders WHERE status = 'pending';
```

---

**Desarrollado con ❤️ usando PHP, MySQL y JavaScript vanilla**
