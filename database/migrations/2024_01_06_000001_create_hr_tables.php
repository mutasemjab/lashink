<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('default_days_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('allocated_days', 6, 1)->default(0);
            $table->decimal('used_days', 6, 1)->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 6, 1);
            $table->string('reason', 500)->nullable();
            $table->string('status', 15)->default('pending'); // pending|approved|rejected|cancelled
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('admins')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('request_date');
            $table->string('reason', 500)->nullable();
            $table->string('status', 15)->default('pending'); // pending|approved|rejected
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->string('repayment_type', 15)->default('single'); // single|installments
            $table->unsignedTinyInteger('installments_count')->default(1);
            $table->decimal('repaid_amount', 10, 2)->default(0);
            $table->boolean('is_settled')->default(false);
            $table->timestamps();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('status', 15)->default('draft'); // draft|finalized
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('finalized_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('total_net', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['period_month', 'period_year']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('admins')->cascadeOnDelete();
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('unpaid_leave_deduction', 10, 2)->default(0);
            $table->decimal('advance_deduction', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->string('payment_status', 15)->default('unpaid'); // unpaid|paid
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 15)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('salary_advances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
