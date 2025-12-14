<?php
// src/core/router.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

// Ruta limpia sin BASE_URL
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = BASE_URL;
$path = trim(str_replace($base, '', $uri), '/');

// --------------------------
// Rutas administrativas (GET tipo vista)
// --------------------------
$adminRoutes = [
    'admin'                   => ['controller' => 'AdminController',     'method' => 'dashboard'],
    'admin/dashboard'         => ['controller' => 'AdminController',     'method' => 'dashboard'],
    'admin/usuarios'          => ['controller' => 'AdminController',     'method' => 'users'],
    'admin/articulos'         => ['controller' => 'ArticleController',   'method' => 'index'],
    'admin/productos'         => ['controller' => 'ProductController',   'method' => 'index'],
    'admin/categorias'        => ['controller' => 'CategoryController',  'method' => 'index'],
    'admin/configuraciones'   => ['controller' => 'SettingsController',  'method' => 'index'],
    'admin/testimonios'       => ['controller' => 'TestimonialController', 'method' => 'index'],
    'admin/contacto'          => ['controller' => 'ContactController',     'method' => 'index'],
    'admin/cupones'           => ['controller' => 'CouponController',      'method' => 'index'],
    'admin/pedidos'           => ['controller' => 'OrderController',       'method' => 'adminIndex'],
];

if (array_key_exists($path, $adminRoutes)) {
    $route = $adminRoutes[$path];
    require_once __DIR__ . "/../controllers/{$route['controller']}.php";
    
    // Controllers con namespace App\Controllers
    $namespacedControllers = ['TestimonialController', 'ContactController'];
    $controllerClass = in_array($route['controller'], $namespacedControllers) 
        ? "App\\Controllers\\{$route['controller']}" 
        : $route['controller'];
    
    (new $controllerClass)->{$route['method']}();
    return;
}

// --------------------------
// Acciones GET del panel admin (formularios parciales)
// --------------------------
$getActions = [
    'admin/usuarios/editar-form'        => ['controller' => 'AdminController',       'method' => 'userEditForm'],
    'admin/articulos/editar-form'       => ['controller' => 'ArticleController',     'method' => 'articleEditForm'],
    'admin/productos/editar-form'       => ['controller' => 'ProductController',     'method' => 'productEditForm'],
    'admin/categorias/editar-form'      => ['controller' => 'CategoryController',    'method' => 'categoryEditForm'],
    'admin/configuraciones/editar-form' => ['controller' => 'SettingsController',    'method' => 'settingsEditForm'],
    'admin/testimonios/editar-form'     => ['controller' => 'TestimonialController', 'method' => 'testimonialEditForm'],
    'admin/contacto/editar-form'        => ['controller' => 'ContactController',     'method' => 'contactEditForm'],
    'admin/cupones/editar-form'         => ['controller' => 'CouponController',      'method' => 'couponEditForm'],
    'admin/pedidos/editar-form'         => ['controller' => 'OrderController',       'method' => 'orderEditForm'],
    'admin/dashboard/events'            => ['controller' => 'AnalyticsController',   'method' => 'recent'],
    'admin/dashboard/data'              => ['controller' => 'AnalyticsController',   'method' => 'stats']
];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && array_key_exists($path, $getActions)) {
    $action = $getActions[$path];
    require_once __DIR__ . "/../controllers/{$action['controller']}.php";
    
    $namespacedControllers = ['TestimonialController', 'ContactController'];
    $controllerClass = in_array($action['controller'], $namespacedControllers) 
        ? "App\\Controllers\\{$action['controller']}" 
        : $action['controller'];
    
    (new $controllerClass)->{$action['method']}();
    return;
}

// --------------------------
// Acciones POST del panel admin
// --------------------------
$postActions = [
    // Usuarios
    'admin/usuarios/crear'     => ['controller' => 'AdminController',    'method' => 'userCreate'],
    'admin/usuarios/editar'    => ['controller' => 'AdminController',    'method' => 'userEdit'],
    'admin/usuarios/delete'    => ['controller' => 'AdminController',    'method' => 'userDelete'],

    // Artículos
    'admin/articulos/crear'    => ['controller' => 'ArticleController',  'method' => 'articleCreate'],
    'admin/articulos/editar'   => ['controller' => 'ArticleController',  'method' => 'articleEdit'],
    'admin/articulos/delete'   => ['controller' => 'ArticleController',  'method' => 'articleDelete'],

    // Productos
    'admin/productos/crear'    => ['controller' => 'ProductController',  'method' => 'productCreate'],
    'admin/productos/editar'   => ['controller' => 'ProductController',  'method' => 'productEdit'],
    'admin/productos/delete'   => ['controller' => 'ProductController',  'method' => 'productDelete'],

    // Categorías
    'admin/categorias/crear'   => ['controller' => 'CategoryController', 'method' => 'categoryCreate'],
    'admin/categorias/editar'  => ['controller' => 'CategoryController', 'method' => 'categoryEdit'],
    'admin/categorias/delete'  => ['controller' => 'CategoryController', 'method' => 'categoryDelete'],

    // Configuraciones
    'admin/configuraciones/crear'   => ['controller' => 'SettingsController',    'method' => 'settingsCreate'],
    'admin/configuraciones/editar'  => ['controller' => 'SettingsController',    'method' => 'settingsEdit'],
    'admin/configuraciones/delete'  => ['controller' => 'SettingsController',    'method' => 'settingsDelete'],

    // Testimonios
    'admin/testimonios/crear'   => ['controller' => 'TestimonialController', 'method' => 'testimonialCreate'],
    'admin/testimonios/editar'  => ['controller' => 'TestimonialController', 'method' => 'testimonialEdit'],
    'admin/testimonios/delete'  => ['controller' => 'TestimonialController', 'method' => 'testimonialDelete'],

    // Contacto
    'admin/contacto/crear'   => ['controller' => 'ContactController', 'method' => 'contactCreate'],
    'admin/contacto/editar'  => ['controller' => 'ContactController', 'method' => 'contactEdit'],
    'admin/contacto/delete'  => ['controller' => 'ContactController', 'method' => 'contactDelete'],

    // Cupones
    'admin/cupones/crear'    => ['controller' => 'CouponController', 'method' => 'couponCreate'],
    'admin/cupones/editar'   => ['controller' => 'CouponController', 'method' => 'couponEdit'],
    'admin/cupones/delete'   => ['controller' => 'CouponController', 'method' => 'couponDelete'],

    // Pedidos
    'admin/pedidos/editar'   => ['controller' => 'OrderController', 'method' => 'orderEdit'],

    // Analytics
    'analytics/collect'      => ['controller' => 'AnalyticsController', 'method' => 'collect'],

    // Carrito
    'cart/add'               => ['controller' => 'CartController', 'method' => 'add'],
    'cart/update'            => ['controller' => 'CartController', 'method' => 'update'],
    'cart/remove'            => ['controller' => 'CartController', 'method' => 'remove'],

    // Checkout / pedidos
    'checkout/place'         => ['controller' => 'OrderController', 'method' => 'place'],

    // Wishlist
    'wishlist/toggle'        => ['controller' => 'WishlistController', 'method' => 'toggle'],

    // Reviews
    'reviews/add'            => ['controller' => 'ReviewController', 'method' => 'add'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists($path, $postActions)) {
    // CSRF protection for admin POST endpoints
    if (strpos($path, 'admin/') === 0 || $path === 'login') {
        csrf_require();
    }
    
    $action = $postActions[$path];
    require_once __DIR__ . "/../controllers/{$action['controller']}.php";
    
    $namespacedControllers = ['TestimonialController', 'ContactController'];
    $controllerClass = in_array($action['controller'], $namespacedControllers) 
        ? "App\\Controllers\\{$action['controller']}" 
        : $action['controller'];
    
    (new $controllerClass)->{$action['method']}();
    return;
}

// --------------------------
// Frontend público
// --------------------------
switch ($path) {
    case '':
    case '/':
        // Cargar controllers necesarios para la página principal
        require_once __DIR__ . '/../controllers/TestimonialController.php';
        require_once __DIR__ . '/../controllers/ContactController.php';
        require_once __DIR__ . '/../views/home/index.php';
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_require();
            require_once __DIR__ . '/../controllers/UserController.php';
            (new UserController())->login();
        } else {
            require_once __DIR__ . '/../views/users/login.php';
        }
        break;

    case 'logout':
        require_once __DIR__ . '/../../logout.php';
        break;

    case 'cart':
        require_once __DIR__ . '/../controllers/CartController.php';
        (new CartController())->view();
        break;

    case 'checkout':
        require_once __DIR__ . '/../controllers/OrderController.php';
        (new OrderController())->checkout();
        break;

    case 'orders':
        require_once __DIR__ . '/../controllers/OrderController.php';
        (new OrderController())->history();
        break;

    case 'orders/detail':
        require_once __DIR__ . '/../controllers/OrderController.php';
        (new OrderController())->detail();
        break;

    case 'orders/invoice':
        require_once __DIR__ . '/../controllers/OrderController.php';
        (new OrderController())->invoice();
        break;

    case 'track':
        require_once __DIR__ . '/../controllers/OrderController.php';
        (new OrderController())->track();
        break;

    case 'sitemap.xml':
        require_once __DIR__ . '/../controllers/SeoController.php';
        (new SeoController())->sitemap();
        break;

    case 'productos':
        require_once __DIR__ . '/../controllers/CatalogController.php';
        (new CatalogController())->index();
        break;

    case 'wishlist':
        require_once __DIR__ . '/../controllers/WishlistController.php';
        (new WishlistController())->view();
        break;

    case 'producto':
    case 'product':
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            require_once __DIR__ . '/../controllers/ProductController.php';
            (new ProductController())->view((int) $_GET['id']);
        } else {
            echo "ID de producto no válido.";
        }
        break;

    case 'articulo':
        require_once __DIR__ . '/../controllers/ArticleController.php';
        (new ArticleController())->publicView();
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada";
        break;
}
