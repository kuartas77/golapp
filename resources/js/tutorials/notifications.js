export const paymentRequestsTutorial = {
    steps: [
        {
            id: 'payment-requests-intro',
            selector: '[data-tour="payment-requests-intro"]',
            title: 'Revisa los comprobantes recibidos',
            text: 'Esta vista concentra los comprobantes enviados desde la app por los acudientes.',
        },
        {
            id: 'payment-requests-table',
            selector: '[data-tour="payment-requests-table"]',
            title: 'Valida y procesa pagos',
            text: 'Desde la tabla puedes abrir el comprobante o marcar la factura correspondiente como pagada.',
        },
    ],
}

export const uniformRequestsTutorial = {
    steps: [
        {
            id: 'uniform-requests-filter',
            selector: '[data-tour="uniform-requests-filter"]',
            title: 'Filtra por tipo de solicitud',
            text: 'Usa este selector para quedarte solo con uniformes, balones, guayos u otros pedidos.',
        },
        {
            id: 'uniform-requests-table',
            selector: '[data-tour="uniform-requests-table"]',
            title: 'Convierte solicitudes en factura',
            text: 'La tabla muestra las solicitudes pendientes y te deja abrir la creacion de factura asociada.',
        },
    ],
}

export const topicNotificationsTutorial = {
    steps: [
        {
            id: 'topic-notifications-actions',
            selector: '[data-tour="topic-notifications-actions"]',
            title: 'Crea una notificacion',
            text: 'Desde este boton abres el modal para enviar un mensaje a la app movil.',
        },
        {
            id: 'topic-notifications-table',
            selector: '[data-tour="topic-notifications-table"]',
            title: 'Consulta el historial enviado',
            text: 'La tabla lista topic, titulo, mensaje y fecha de las notificaciones creadas.',
        },
    ],
}
