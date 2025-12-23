<?php
/**
 * Sistema de permisos y roles
 * Define qué puede hacer cada rol en la aplicación
 */

const ROLES_PERMISSIONS = [
    'admin' => [
        // Gestión de usuarios
        'users.view' => true,
        'users.create' => true,
        'users.edit' => true,
        'users.delete' => true,
        
        // Gestión de productos
        'products.view' => true,
        'products.create' => true,
        'products.edit' => true,
        'products.edit_any' => true,
        'products.delete' => true,
        
        // Gestión de órdenes
        'orders.view' => true,
        'orders.view_any' => true,
        'orders.edit' => true,
        'orders.delete' => true,
        
        // Gestión de categorías
        'categories.manage' => true,
        
        // Gestión de cupones
        'coupons.manage' => true,
        
        // Gestión de artículos
        'articles.manage' => true,
        
        // Configuraciones
        'settings.manage' => true,
        
        // Analytics
        'analytics.view' => true,
        
        // Dashboard
        'admin.dashboard' => true,
    ],
    
    'vendedor' => [
        // Gestión de productos (solo suyos)
        'products.view' => true,
        'products.create' => true,
        'products.edit' => true,  // Solo los propios
        'products.delete' => true, // Solo los propios
        
        // Ver órdenes de sus productos
        'orders.view' => true,
        'orders.view_own' => true,
        
        // Dashboard vendedor
        'vendor.dashboard' => true,
        
        // Ver su perfil
        'profile.view' => true,
        'profile.edit' => true,
    ],
    
    'Dueno' => [
        // Gestión de usuarios
        'users.view' => true,
        'users.create' => true,
        'users.edit' => true,
        'users.delete' => true,
        
        // Gestión de productos
        'products.view' => true,
        'products.create' => true,
        'products.edit' => true,
        'products.edit_any' => true,
        'products.delete' => true,
        
        // Gestión de órdenes
        'orders.view' => true,
        'orders.view_any' => true,
        'orders.edit' => true,
        
        // Gestión de categorías
        'categories.manage' => true,
        
        // Gestión de cupones
        'coupons.manage' => true,
        
        // Gestión de artículos
        'articles.manage' => true,
        
        // Configuraciones
        'settings.manage' => true,
        
        // Analytics
        'analytics.view' => true,
        
        // Dashboard
        'admin.dashboard' => true,
    ],
    
    'cliente' => [
        // Ver su perfil
        'profile.view' => true,
        'profile.edit' => true,
        
        // Ver órdenes
        'orders.view_own' => true,
        
        // Ver carrito
        'cart.view' => true,
    ]
];

/**
 * Verificar si un usuario tiene un permiso específico
 * 
 * @param string $permission El permiso a verificar (ej: 'products.edit')
 * @param array $user Datos del usuario (debe incluir 'role' y 'id')
 * @param mixed $resourceOwnerId ID del propietario del recurso (para permisos como 'products.edit')
 * @return bool true si tiene permiso, false si no
 */
function hasPermission(string $permission, array $user, mixed $resourceOwnerId = null): bool {
    $role = $user['role'] ?? null;
    
    if (!$role || !isset(ROLES_PERMISSIONS[$role])) {
        return false;
    }
    
    $permissions = ROLES_PERMISSIONS[$role];
    
    // Si no existe el permiso, retornar false
    if (!isset($permissions[$permission])) {
        return false;
    }
    
    // Si el permiso es para recursos propios (ej: products.edit)
    // y el usuario no es el propietario, denegar
    if (str_contains($permission, '_own') && $resourceOwnerId !== null) {
        return $user['id'] == $resourceOwnerId;
    }
    
    // Para permisos que no son "edit_any", permitir si es propietario
    if (str_contains($permission, 'edit') && !str_contains($permission, 'edit_any') && $resourceOwnerId !== null) {
        return $user['id'] == $resourceOwnerId;
    }
    
    return $permissions[$permission] ?? false;
}

/**
 * Verificar si un usuario tiene un rol específico
 * 
 * @param string $role El rol a verificar
 * @param array $user Datos del usuario
 * @return bool
 */
function hasRole(string $role, array $user): bool {
    return ($user['role'] ?? null) === $role;
}

/**
 * Verificar si un usuario tiene alguno de los roles listados
 * 
 * @param array $roles Array de roles
 * @param array $user Datos del usuario
 * @return bool
 */
function hasAnyRole(array $roles, array $user): bool {
    return in_array($user['role'] ?? null, $roles);
}

/**
 * Require un permiso, redirigir si no lo tiene
 * 
 * @param string $permission
 * @param array $user
 * @param mixed $resourceOwnerId
 * @return void
 */
function requirePermission(string $permission, array $user, mixed $resourceOwnerId = null): void {
    if (!hasPermission($permission, $user, $resourceOwnerId)) {
        error_log('[permissions] Permission denied: ' . $permission . ' for user ' . json_encode($user));
        http_response_code(403);
        echo '403 - Acceso denegado.';
        exit;
    }
}

/**
 * Require un rol específico, redirigir si no lo tiene
 * 
 * @param string $role
 * @param array $user
 * @return void
 */
function requireRole(string $role, array $user): void {
    if (!hasRole($role, $user)) {
        error_log('[permissions] Role required: ' . $role . ' for user ' . json_encode($user));
        http_response_code(403);
        echo '403 - Acceso denegado.';
        exit;
    }
}

/**
 * Require alguno de los roles, redirigir si no lo tiene
 * 
 * @param array $roles
 * @param array $user
 * @return void
 */
function requireAnyRole(array $roles, array $user): void {
    if (!hasAnyRole($roles, $user)) {
        error_log('[permissions] Role(s) required: ' . implode(', ', $roles) . ' for user ' . json_encode($user));
        http_response_code(403);
        echo '403 - Acceso denegado.';
        exit;
    }
}
