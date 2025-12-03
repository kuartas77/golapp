<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'inscription_id',
        'training_group_id',
        'year',
        'student_name',
        'total_amount',
        'paid_amount',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'school_id',
        'created_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    protected $appends = ['url_print', 'url_destroy'];

    public function getUrlDestroyAttribute(): string
    {
        return route('invoices.destroy', [$this->attributes['id']]);
    }

    public function getUrlprintAttribute(): string
    {
        return route('invoices.print', [$this->attributes['invoice_number']]);
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function trainingGroup()
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentReceived::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Obtener los meses pendientes de la tabla payments
    public function getPendingMonths()
    {
        $payment = Payment::where('inscription_id', $this->inscription_id)
            ->where('year', $this->year)
            ->first();

        if (!$payment) {
            return collect();
        }

        $months = [
            'enrollment' => 'Matrícula',
            'january' => 'Enero',
            'february' => 'Febrero',
            'march' => 'Marzo',
            'april' => 'Abril',
            'may' => 'Mayo',
            'june' => 'Junio',
            'july' => 'Julio',
            'august' => 'Agosto',
            'september' => 'Septiembre',
            'october' => 'Octubre',
            'november' => 'Noviembre',
            'december' => 'Diciembre'
        ];

        $pending = [];
        foreach ($months as $key => $name) {
            if ($payment->{$key} == 2) { // Asumiendo que 2 = debe
                $pending[] = [
                    'month' => $key,
                    'name' => $name,
                    'amount' => $payment->{$key . '_amount'},
                    'payment_id' => $payment->id
                ];
            }
        }

        return collect($pending);
    }

    public function updateTotals()
    {
        $total = $this->items()->sum('total');
        $paid = $this->payments()->sum('amount');

        $this->total_amount = $total;
        $this->paid_amount = $paid;
        $this->status = $this->calculateStatus($total, $paid);
        $this->save();
    }

    private function calculateStatus($total, $paid)
    {
        if ($paid >= $total) return 'paid';
        if ($paid > 0) return 'partial';
        return 'pending';
    }

    // Marcar meses como pagados en la tabla payments original
    public function markMonthsAsPaid()
    {
        $payment = Payment::where('inscription_id', $this->inscription_id)
            ->where('year', $this->year)
            ->first();

        if (!$payment) {
            return;
        }

        // Obtener los ítems de meses que están marcados como pagados
        $monthItems = $this->items()
            ->where('type', 'monthly')
            ->where('is_paid', 1)
            ->whereNotNull('month')
            ->get();

        foreach ($monthItems as $item) {
            if (in_array($item->month, [
                'enrollment', 'january', 'february', 'march', 'april', 'may',
                'june', 'july', 'august', 'september', 'october', 'november', 'december'
            ])) {
                $payment->{$item->month} = 1; // Marcar como pagado
            }
        }

        $payment->save();
    }
}