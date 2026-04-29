import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'
import { SCHOOL_PERMISSION_KEYS } from '@/config/school-permissions'

export const backofficeAccessRequirements = {
    players: {
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.players],
    },
    inscriptions: {
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.inscriptions],
    },
    evaluations: {
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.evaluations],
    },
    attendances: {
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.attendances],
    },
    trainingSessions: {
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.trainingSessions],
    },
    matches: {
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.matches],
    },
    payments: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.payments],
    },
    reports: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.reports],
    },
    billing: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.billing],
    },
    schoolProfile: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.schoolProfile],
    },
    contracts: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.contracts],
    },
    userManagement: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.userManagement],
    },
    trainingGroups: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.trainingGroups],
    },
    competitionGroups: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.competitionGroups],
    },
    topicNotifications: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.systemNotify],
    },
    paymentRequests: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.billing, SCHOOL_PERMISSION_KEYS.systemNotify],
    },
    uniformRequests: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.billing, SCHOOL_PERMISSION_KEYS.systemNotify],
    },
    evaluationTemplates: {
        roles: ['super-admin'],
    },
}

export function hasBackofficeAccess(auth, requirements = {}) {
    const roles = requirements.roles ?? []
    const schoolPermissions = requirements.schoolPermissions ?? []

    const hasRoles = roles.length === 0
        ? true
        : requirements.anyRole
            ? roles.some((role) => auth.hasRole(role))
            : roles.every((role) => auth.hasRole(role))

    const hasSchoolPermissions = schoolPermissions.length === 0
        ? true
        : requirements.anySchoolPermission
            ? schoolPermissions.some((permission) => auth.hasSchoolPermission(permission))
            : schoolPermissions.every((permission) => auth.hasSchoolPermission(permission))

    return hasRoles && hasSchoolPermissions
}

export function useBackofficeAccess() {
    const auth = useAuthUser()

    const access = Object.fromEntries(
        Object.entries(backofficeAccessRequirements).map(([key, requirements]) => [
            key,
            computed(() => hasBackofficeAccess(auth, requirements)),
        ])
    )

    const canAccess = (key) => hasBackofficeAccess(auth, backofficeAccessRequirements[key] ?? {})

    return {
        access,
        canAccess,
    }
}
