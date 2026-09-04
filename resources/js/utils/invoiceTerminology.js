export const isElectronicInvoice = (invoiceOrType) => {
    const numberingType = typeof invoiceOrType === 'object'
        ? invoiceOrType?.numbering_type
        : invoiceOrType

    return numberingType === 'electronic'
}

export const invoiceDocumentSingular = (invoiceOrType) => (
    isElectronicInvoice(invoiceOrType) ? 'Factura' : 'Recibo de caja'
)

export const invoiceDocumentPlural = (electronicInvoicingEnabled) => (
    electronicInvoicingEnabled ? 'Facturas' : 'Recibos de caja'
)

export const invoiceDocumentSingularForSchool = (electronicInvoicingEnabled) => (
    electronicInvoicingEnabled ? 'Factura' : 'Recibo de caja'
)
