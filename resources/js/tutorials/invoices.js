export const invoicesIndexTutorial = {
    steps: [
        {
            id: 'invoices-index-filters',
            selector: '[data-tour="invoices-index-filters"]',
            title: 'Filtra las facturas',
            text: 'Puedes consultar por estado y rango de fechas para recortar el listado.',
        },
        {
            id: 'invoices-index-table',
            selector: '[data-tour="invoices-index-table"]',
            title: 'Administra el listado',
            text: 'La tabla resume montos, estado, fechas y accesos al detalle de cada factura.',
        },
    ],
}

export const invoiceCreateTutorial = {
    steps: [
        {
            id: 'invoice-create-header',
            selector: '[data-tour="invoice-create-header"]',
            title: 'Valida el contexto de la factura',
            text: 'La cabecera muestra el deportista, grupo y ano con el que se va a generar la factura.',
        },
        {
            id: 'invoice-create-months',
            selector: '[data-tour="invoice-create-months"]',
            title: 'Selecciona mensualidades pendientes',
            text: 'Aqui decides que mensualidades entran en la factura y ajustas sus valores si hace falta.',
        },
        {
            id: 'invoice-create-items',
            selector: '[data-tour="invoice-create-items"]',
            title: 'Agrega cargos adicionales',
            text: 'Este bloque sirve para incluir uniformes, materiales u otros conceptos extra.',
        },
        {
            id: 'invoice-create-billing',
            selector: '[data-tour="invoice-create-billing"]',
            title: 'Completa vencimiento y notas',
            text: 'Antes de guardar defines la fecha de vencimiento y observaciones de la factura.',
        },
        {
            id: 'invoice-create-total',
            selector: '[data-tour="invoice-create-total"]',
            title: 'Revisa los totales',
            text: 'Este resumen te deja validar el subtotal y el total final antes de confirmar.',
        },
        {
            id: 'invoice-create-actions',
            selector: '[data-tour="invoice-create-actions"]',
            title: 'Guarda la factura',
            text: 'Cuando todo este correcto puedes cancelar o guardar la factura generada.',
        },
    ],
}

export const invoiceShowTutorial = {
    steps: [
        {
            id: 'invoice-show-summary',
            selector: '[data-tour="invoice-show-summary"]',
            title: 'Consulta el detalle principal',
            text: 'Este bloque resume estudiante, fechas, items, total pagado y saldo pendiente.',
        },
        {
            id: 'invoice-show-history',
            selector: '[data-tour="invoice-show-history"]',
            title: 'Revisa el historial de pagos',
            text: 'Si la factura ya tiene abonos, aqui se listan con fecha, metodo y referencia.',
        },
        {
            id: 'invoice-show-payment-form',
            selector: '[data-tour="invoice-show-payment-form"]',
            title: 'Registra un nuevo pago',
            text: 'El panel lateral te permite marcar items, elegir metodo de pago y registrar el abono.',
        },
        {
            id: 'invoice-show-actions',
            selector: '[data-tour="invoice-show-actions"]',
            title: 'Imprime o elimina',
            text: 'Al final del panel de pagos puedes imprimir la factura o eliminarla cuando la regla lo permita.',
        },
    ],
}
