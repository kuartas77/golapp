import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'
import { SCHOOL_PERMISSION_KEYS } from '@/config/school-permissions'

export const backofficeAccessRequirements = {
    players: {
        roles: ['super-admin', 'school', 'assistant'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.players],
    },
    playerStats: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.matches],
    },
    competitionStats: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.matches],
    },
    inscriptions: {
        roles: ['super-admin', 'school', 'assistant'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.inscriptions],
    },
    evaluations: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.evaluations],
    },
    attendances: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.attendances],
    },
    trainingSessions: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.trainingSessions],
    },
    sessionPlanning: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.sessionPlanning],
    },
    methodology: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.methodology],
    },
    matches: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.matches],
    },
    payments: {
        roles: ['super-admin', 'school', 'assistant'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.payments],
    },
    reports: {
        roles: ['super-admin', 'school', 'assistant'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.reports],
    },
    billing: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.billing],
    },
    invoiceNumbering: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.billing],
        requiresElectronicInvoicing: true,
    },
    inventory: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.inventory],
    },
    schoolOutings: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.schoolOutings],
    },
    playerCredits: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.playerCredits],
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
    clubDocuments: {
        roles: ['super-admin', 'school'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.clubDocuments],
    },
    documentPlanning: {
        roles: ['super-admin', 'school', 'instructor'],
        anyRole: true,
        schoolPermissions: [SCHOOL_PERMISSION_KEYS.documentPlanning],
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

    const hasElectronicInvoicing = !requirements.requiresElectronicInvoicing
        || auth.hasRole('super-admin')
        || Boolean(auth.user?.electronic_invoicing_enabled)

    return hasRoles && hasSchoolPermissions && hasElectronicInvoicing
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
