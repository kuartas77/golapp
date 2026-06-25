const normalizeKeyPart = (value) => {
    if (value === null || value === undefined || value === '') return null

    return String(value)
}

export function getSkillControlMatchKeys(skillControl) {
    const keys = new Set()
    const addKey = (prefix, value) => {
        const normalizedValue = normalizeKeyPart(value)
        if (normalizedValue) {
            keys.add(`${prefix}:${normalizedValue}`)
        }
    }

    addKey('inscription', skillControl?.inscription_id)
    addKey('inscription', skillControl?.inscription?.id)
    addKey('player', skillControl?.player?.id)
    addKey('player', skillControl?.inscription?.player?.id)
    addKey('code', skillControl?.player?.unique_code)
    addKey('code', skillControl?.inscription?.player?.unique_code)

    return [...keys]
}

export function buildSkillControlLookup(skillControls) {
    const lookup = new Map()

    ;(skillControls ?? []).forEach((skillControl) => {
        getSkillControlMatchKeys(skillControl).forEach((key) => {
            if (!lookup.has(key)) {
                lookup.set(key, skillControl)
            }
        })
    })

    return lookup
}

export function findMatchingSkillControl(lookup, skillControl) {
    for (const key of getSkillControlMatchKeys(skillControl)) {
        if (lookup.has(key)) {
            return lookup.get(key)
        }
    }

    return null
}
