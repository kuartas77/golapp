SELECT ii.payment_id, ii.month, COUNT(*) AS items_pagados
FROM invoice_items ii
JOIN invoices i ON i.id = ii.invoice_id
JOIN payments p ON p.id = ii.payment_id
WHERE ii.type = 'monthly'
  AND ii.is_paid = 1
  AND ii.payment_id IS NOT NULL
  AND i.deleted_at IS NULL
  AND CASE ii.month
      WHEN 'enrollment' THEN p.enrollment
      WHEN 'january' THEN p.january
      WHEN 'february' THEN p.february
      WHEN 'march' THEN p.march
      WHEN 'april' THEN p.april
      WHEN 'may' THEN p.may
      WHEN 'june' THEN p.june
      WHEN 'july' THEN p.july
      WHEN 'august' THEN p.august
      WHEN 'september' THEN p.september
      WHEN 'october' THEN p.october
      WHEN 'november' THEN p.november
      WHEN 'december' THEN p.december
  END <> 1
GROUP BY ii.payment_id, ii.month;
