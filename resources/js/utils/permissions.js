/**
 * Check if the current user has a specific permission
 * @param {string} permission - The permission to check
 * @returns {boolean}
 */
export function hasPermission(permission) {
    if (!window.appData || !window.appData.permissions) {
        return false;
    }

    const permissions = window.appData.permissions;

    // If permissions is an array of objects (from Eloquent), check the 'name' field
    if (Array.isArray(permissions)) {
        return permissions.some(p =>
            (typeof p === 'string' ? p : p?.name) === permission
        );
    }

    // If permissions is an object/JSON
    if (typeof permissions === 'object') {
        return Object.values(permissions).some(p =>
            (typeof p === 'string' ? p : p?.name) === permission
        );
    }

    return false;
}

/**
 * Check if the current user has a specific role
 * @param {string} role - The role to check
 * @returns {boolean}
 */
export function hasRole(role) {
    if (!window.appData || !window.appData.user || !window.appData.user.role) {
        return false;
    }

    return window.appData.user.role === role;
}

/**
 * Check if the current user has any of the specified permissions
 * @param {Array<string>} permissions - Array of permissions to check
 * @returns {boolean}
 */
export function hasAnyPermission(permissions) {
    return permissions.some(permission => hasPermission(permission));
}

/**
 * Check if the current user has all of the specified permissions
 * @param {Array<string>} permissions - Array of permissions to check
 * @returns {boolean}
 */
export function hasAllPermissions(permissions) {
    return permissions.every(permission => hasPermission(permission));
}

/**
 * Get the current user's role
 * @returns {string|null}
 */
export function getUserRole() {
    if (!window.appData || !window.appData.user) {
        return null;
    }
    return window.appData.user.role;
}

/**
 * Check if user is authenticated
 * @returns {boolean}
 */
export function isAuthenticated() {
    return window.appData && window.appData.user !== null;
}
