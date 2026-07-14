UPDATE payments p
SET
    p.enrollment = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'enrollment'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.enrollment END,
    p.january = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'january'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.january END,
    p.february = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'february'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.february END,
    p.march = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'march'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.march END,
    p.april = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'april'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.april END,
    p.may = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'may'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.may END,
    p.june = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'june'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.june END,
    p.july = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'july'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.july END,
    p.august = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'august'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.august END,
    p.september = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'september'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.september END,
    p.october = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'october'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.october END,
    p.november = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'november'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.november END,
    p.december = CASE WHEN EXISTS (
        SELECT 1 FROM invoice_items ii
        JOIN invoices i ON i.id = ii.invoice_id
        WHERE ii.payment_id = p.id
          AND ii.type = 'monthly'
          AND ii.is_paid = 1
          AND ii.month = 'december'
          AND i.deleted_at IS NULL
    ) THEN 1 ELSE p.december END
WHERE EXISTS (
    SELECT 1
    FROM invoice_items ii
    JOIN invoices i ON i.id = ii.invoice_id
    WHERE ii.payment_id = p.id
      AND ii.type = 'monthly'
      AND ii.is_paid = 1
      AND i.deleted_at IS NULL
);
