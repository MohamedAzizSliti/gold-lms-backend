<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop withdraw_requests if it exists to re-create it properly
        Schema::dropIfExists('withdraw_requests');

        // Create users table if it doesn't exist yet (to ensure foreign keys work)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Create vendor_wallets if it doesn't exist yet
        if (!Schema::hasTable('vendor_wallets')) {
            Schema::create('vendor_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->decimal('balance', 8, 2)->default(0.0);
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('vendor_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }

        // Create withdraw_requests table with proper foreign key constraints
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 8, 2)->default(0.0)->nullable();
            $table->string('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->nullable()->default('pending');
            $table->unsignedBigInteger('vendor_wallet_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->enum('payment_type', ['paypal', 'bank'])->nullable()->default('bank');
            $table->integer('is_used')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign keys with if exists condition
            if (Schema::hasTable('vendor_wallets')) {
                $table->foreign('vendor_wallet_id')
                    ->references('id')
                    ->on('vendor_wallets')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
            
            if (Schema::hasTable('users')) {
                $table->foreign('vendor_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
