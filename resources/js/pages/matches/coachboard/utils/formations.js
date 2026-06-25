// formations.js
export const footballModality = {
    11: 'Fútbol 11',
    10: 'Fútbol 10',
    9: 'Fútbol 9',
    7: 'Fútbol 7',
    8: 'Fútbol 8',
    6: 'Fútbol 6',
    5: 'Fútbol 5',
}

export const baseFormationsMap = {
    11: {
        '4-4-2': [4, 4, 2],
        '4-3-3': [4, 3, 3],
        '4-2-3-1': [4, 2, 3, 1],
        '3-5-2': [3, 5, 2],
        '3-4-3': [3, 4, 3],
        '4-1-4-1': [4, 1, 4, 1],
        '5-3-2': [5, 3, 2],
        '4-5-1': [4, 5, 1],
        '5-4-1': [5, 4, 1],
    },
    10: {
        '4-4-1': [4, 4, 1],
        '4-3-2': [4, 3, 2],
        '3-4-2': [3, 4, 2],
        '3-3-3': [3, 3, 3],
        '4-2-3': [4, 2, 3],
        '3-2-3-1': [3, 2, 3, 1],
    },
    9: {
        '3-3-2': [3, 3, 2],
        '3-2-3': [3, 2, 3],
        '4-3-1': [4, 3, 1],
        '2-4-2': [2, 4, 2],
        '3-1-3-1': [3, 1, 3, 1],
        '4-2-2': [4, 2, 2],
    },
    8: {
        '3-3-1': [3, 3, 1],
        '3-2-2': [3, 2, 2],
        '2-3-2': [2, 3, 2],
        '4-2-1': [4, 2, 1],
        '2-2-1-2': [2, 2, 1, 2],
        '3-1-3': [3, 1, 3],
        '4-3': [4, 3],
    },
    7: {
        '3-2-1': [3, 2, 1],
        '2-3-1': [2, 3, 1],
        '3-1-2': [3, 1, 2],
        '2-2-2': [2, 2, 2],
        '2-1-2-1': [2, 1, 2, 1],
        '1-3-2': [1, 3, 2],
        '3-3': [3, 3],
    },
    6: {
        '2-2-1': [2, 2, 1],
        '2-1-2': [2, 1, 2],
        '1-3-1': [1, 3, 1],
        '3-1-1': [3, 1, 1],
        '1-2-2': [1, 2, 2],
    },
    5: {
        '2-2': [2, 2],
        '3-1': [3, 1],
        '1-3': [1, 3],
        '2-1-1': [2, 1, 1],
        '1-2-1': [1, 2, 1],
        '1-1-2': [1, 1, 2],
    }
}

// Función helper para validar formaciones
export function validateFormation(formationStr, modality) {
    if (!formationStr || !modality) return { valid: false, error: 'Datos incompletos' }

    // Verificar formato
    if (!/^(\d+-)+\d+$/.test(formationStr)) {
        return { valid: false, error: 'Formato inválido. Usa números separados por guiones (ej: 4-3-3)' }
    }

    const parts = formationStr.split('-').map(s => Number(s))

    // Verificar que todos sean números positivos
    if (parts.some(n => Number.isNaN(n) || n < 0)) {
        return { valid: false, error: 'Formato inválido. Solo se permiten números positivos' }
    }

    // Calcular jugadores de campo (excluyendo portero)
    const fieldPlayers = parts.reduce((a, b) => a + b, 0)
    const expectedFieldPlayers = modality - 1 // Restar portero

    if (fieldPlayers !== expectedFieldPlayers) {
        return {
            valid: false,
            error: `La suma debe ser ${expectedFieldPlayers} (${modality} jugadores total - 1 portero)`
        }
    }

    return { valid: true, parts }
}

// Función para obtener formaciones sugeridas automáticamente
export function getSuggestedFormations(playerCount) {
    const suggestions = {
        11: ['4-4-2', '4-3-3', '3-5-2', '4-2-3-1'],
        10: ['4-4-1', '4-3-2', '3-4-2', '3-3-3'],
        9: ['3-3-2', '3-2-3', '4-3-1', '2-4-2'],
        8: ['3-3-1', '3-2-2', '2-3-2', '4-2-1'],
        7: ['3-2-1', '2-3-1', '3-1-2', '2-2-2'],
        6: ['2-2-1', '2-1-2', '1-3-1'],
        5: ['2-2', '3-1', '2-1-1'],
    }

    return suggestions[playerCount] || []
}
