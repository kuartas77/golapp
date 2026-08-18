<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_invoice_sequences')) {
            Schema::create('school_invoice_sequences', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('next_number')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoice_number_ranges')) {
            Schema::create('invoice_number_ranges', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('resolution_number', 100);
                $table->date('resolution_date');
                $table->string('prefix', 4)->nullable();
                $table->unsignedBigInteger('range_start');
                $table->unsignedBigInteger('range_end');
                $table->unsignedBigInteger('next_number');
                $table->date('valid_from');
                $table->date('valid_until');
                $table->text('technical_key')->nullable();
                $table->boolean('is_active')->default(false);
                $table->unsignedTinyInteger('active_slot')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['school_id', 'active_slot'], 'invoice_ranges_one_active_per_school');
                $table->index(['school_id', 'prefix'], 'invoice_ranges_school_prefix_index');
            });
        }

        if (! Schema::hasColumn('schools', 'electronic_invoicing_enabled')) {
            Schema::table('schools', function (Blueprint $table): void {
                $table->boolean('electronic_invoicing_enabled')->default(false)->after('auto_invoice');
            });
        }

        if (! Schema::hasColumn('invoices', 'numbering_type')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->string('numbering_type', 20)->default('legacy')->after('invoice_number');
            });
        }

        if (! Schema::hasColumn('invoices', 'consecutive_number')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->unsignedBigInteger('consecutive_number')->nullable()->after('numbering_type');
            });
        }

        if (! Schema::hasColumn('invoices', 'invoice_number_range_id')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->foreignId('invoice_number_range_id')->nullable()->after('consecutive_number')
                    ->constrained('invoice_number_ranges')->restrictOnDelete();
            });
        }

        if (Schema::hasIndex('invoices', 'invoices_invoice_number_unique')) {
            Schema::table('invoices', fn (Blueprint $table) => $table->dropUnique('invoices_invoice_number_unique'));
        }

        if (! Schema::hasIndex('invoices', 'invoices_school_invoice_number_unique')) {
            Schema::table('invoices', fn (Blueprint $table) => $table->unique(
                ['school_id', 'invoice_number'],
                'invoices_school_invoice_number_unique'
            ));
        }

        DB::table('invoices')->whereNull('numbering_type')->update(['numbering_type' => 'legacy']);

        DB::table('schools')->select('id')->orderBy('id')->each(function (object $school): void {
            DB::table('school_invoice_sequences')->insertOrIgnore([
                'school_id' => $school->id,
                'next_number' => DB::table('invoices')->where('school_id', $school->id)->count() + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('invoices', 'invoices_school_invoice_number_unique')) {
            Schema::table('invoices', fn (Blueprint $table) => $table->dropUnique('invoices_school_invoice_number_unique'));
        }

        if (Schema::hasColumn('invoices', 'invoice_number_range_id')) {
            Schema::table('invoices', fn (Blueprint $table) => $table->dropConstrainedForeignId('invoice_number_range_id'));
        }
        foreach (['consecutive_number', 'numbering_type'] as $column) {
            if (Schema::hasColumn('invoices', $column)) {
                Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }

        if (! Schema::hasIndex('invoices', 'invoices_invoice_number_unique')) {
            Schema::table('invoices', fn (Blueprint $table) => $table->unique('invoice_number'));
        }
        if (Schema::hasColumn('schools', 'electronic_invoicing_enabled')) {
            Schema::table('schools', fn (Blueprint $table) => $table->dropColumn('electronic_invoicing_enabled'));
        }

        Schema::dropIfExists('invoice_number_ranges');
        Schema::dropIfExists('school_invoice_sequences');
    }
};
