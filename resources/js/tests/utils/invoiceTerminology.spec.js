import { describe, expect, it } from 'vitest'
import {
    invoiceDocumentPlural,
    invoiceDocumentSingular,
    invoiceDocumentSingularForSchool,
    isElectronicInvoice,
} from '@/utils/invoiceTerminology'

describe('invoice terminology', () => {
    it('uses factura only for electronic documents', () => {
        expect(isElectronicInvoice({ numbering_type: 'electronic' })).toBe(true)
        expect(invoiceDocumentSingular('electronic')).toBe('Factura')
        expect(invoiceDocumentSingular('internal')).toBe('Recibo de caja')
        expect(invoiceDocumentSingular('legacy')).toBe('Recibo de caja')
    })

    it('names the module from the school electronic mode', () => {
        expect(invoiceDocumentPlural(true)).toBe('Facturas')
        expect(invoiceDocumentPlural(false)).toBe('Recibos de caja')
        expect(invoiceDocumentSingularForSchool(true)).toBe('Factura')
        expect(invoiceDocumentSingularForSchool(false)).toBe('Recibo de caja')
    })
})
