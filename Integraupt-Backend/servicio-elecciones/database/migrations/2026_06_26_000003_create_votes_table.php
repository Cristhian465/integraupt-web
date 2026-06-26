<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->string('student_id');
            $table->string('faculty')->nullable();
            
            // The three vote types - can be null if they voted blank
            $table->foreignId('asamblea_party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('consejo_uni_party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('consejo_fac_party_id')->nullable()->constrained('parties')->nullOnDelete();
            
            $table->string('verification_code')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // A student can only vote once per election
            $table->unique(['election_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
