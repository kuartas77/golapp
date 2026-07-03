export const monthlyPaymentReceiptsTutorial = {
    steps: [
        { id: 'monthly-receipts-filters', selector: '[data-tour="monthly-receipts-filters"]', title: 'Prepara el envio', text: 'Selecciona periodo, grupo y destinatarios para consultar los recibos disponibles.' },
        { id: 'monthly-receipts-table', selector: '[data-tour="monthly-receipts-table"]', title: 'Revisa los recibos', text: 'La tabla muestra los deportistas y el estado de los recibos del periodo consultado.' },
    ],
}

export const inventoryTutorial = {
    getSteps: ({ activeTab }) => activeTab.value === 'movements' ? [
        { id: 'inventory-tabs', selector: '[data-tour="inventory-tabs"]', title: 'Cambia de inventario a movimientos', text: 'Las pestanas separan el catalogo de productos del historial de entradas y salidas.' },
        { id: 'inventory-movements-table', selector: '[data-tour="inventory-movements-table"]', title: 'Consulta los movimientos', text: 'Revisa entradas, salidas, cantidades, responsables y referencias registradas.' },
    ] : [
        { id: 'inventory-actions', selector: '[data-tour="inventory-actions"]', title: 'Crea un producto', text: 'Registra los elementos que la escuela controla en su inventario.' },
        { id: 'inventory-tabs', selector: '[data-tour="inventory-tabs"]', title: 'Navega por el modulo', text: 'Alterna entre el catalogo de productos y sus movimientos.' },
        { id: 'inventory-products-table', selector: '[data-tour="inventory-products-table"]', title: 'Administra las existencias', text: 'Consulta el stock y accede a las acciones disponibles para cada producto.' },
    ],
}

export const schoolOutingsTutorial = {
    steps: [
        { id: 'school-outings-actions', selector: '[data-tour="school-outings-actions"]', title: 'Programa una salida', text: 'Crea una salida y define su informacion general antes de gestionar participantes y gastos.' },
        { id: 'school-outings-table', selector: '[data-tour="school-outings-table"]', title: 'Administra las salidas', text: 'Consulta el estado de cada salida y accede a sus acciones de gestion.' },
    ],
}

export const attendanceQrTutorial = {
    steps: [
        { id: 'attendance-qr-context', selector: '[data-tour="attendance-qr-context"]', title: 'Inicia una toma rapida', text: 'Esta herramienta abre el registro de asistencia mediante el codigo unico del deportista.' },
        { id: 'attendance-qr-form', selector: '[data-tour="attendance-qr-form"]', title: 'Ingresa el codigo', text: 'Escribe o escanea el codigo y continua para registrar la asistencia.' },
    ],
}
