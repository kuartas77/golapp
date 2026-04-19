export function canAccessRoute(route, auth) {
    const matchedRoutes = route?.matched ?? []
    const userRoles = auth.roles ?? []

    for (const routeRecord of matchedRoutes) {
        const requiredRoles = routeRecord.meta?.requiresRole || []
        if (requiredRoles.length > 0) {
            const hasRole = requiredRoles.some((role) => userRoles.includes(role))
            if (!hasRole) {
                return false
            }
        }

        const requiredRolesAll = routeRecord.meta?.requiresRoleAll || []
        if (requiredRolesAll.length > 0) {
            const hasAllRoles = requiredRolesAll.every((role) => userRoles.includes(role))
            if (!hasAllRoles) {
                return false
            }
        }

        const requiredSchoolPermissions = routeRecord.meta?.requiresSchoolPermission || []
        if (requiredSchoolPermissions.length > 0) {
            const hasAnySchoolPermission = requiredSchoolPermissions.some((permission) => auth.hasSchoolPermission(permission))
            if (!hasAnySchoolPermission) {
                return false
            }
        }

        const requiredSchoolPermissionsAll = routeRecord.meta?.requiresSchoolPermissionAll || []
        if (requiredSchoolPermissionsAll.length > 0) {
            const hasAllSchoolPermissions = requiredSchoolPermissionsAll.every((permission) => auth.hasSchoolPermission(permission))
            if (!hasAllSchoolPermissions) {
                return false
            }
        }
    }

    return true
}
