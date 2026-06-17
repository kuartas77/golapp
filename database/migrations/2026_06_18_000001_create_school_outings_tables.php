<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_outings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('departure_date');
            $table->decimal('amount_per_player', 12, 2)->default(0);
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'departure_date']);
        });

        Schema::create('school_outing_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_outing_id')->constrained('school_outings')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_outing_id', 'inscription_id'], 'outing_participant_inscription_unique');
            $table->index(['school_id', 'player_id']);
        });

        Schema::create('school_outing_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_outing_id')->constrained('school_outings')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_outing_id', 'name'], 'outing_activity_name_unique');
        });

        Schema::create('school_outing_contributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_outing_id')->constrained('school_outings')->cascadeOnDelete();
            $table->foreignId('school_outing_participant_id')->constrained('school_outing_participants')->cascadeOnDelete();
            $table->foreignId('school_outing_activity_id')->constrained('school_outing_activities')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('contribution_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_outing_id', 'contribution_date'], 'outing_contributions_outing_date_idx');
            $table->index(['school_outing_participant_id'], 'outing_contributions_participant_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_outing_contributions');
        Schema::dropIfExists('school_outing_activities');
        Schema::dropIfExists('school_outing_participants');
        Schema::dropIfExists('school_outings');
    }
};
