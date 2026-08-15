<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Applications Suite Registry Table
        if (!Schema::hasTable('cms_apps')) {
            Schema::create('cms_apps', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique(); // e.g. 'crm', 'mutasi', 'invoicing', 'hr'
                $table->string('icon')->nullable()->default('bi bi-box-seam');
                $table->enum('billing_type', ['subscription', 'one_time', 'freemium'])->default('subscription');
                $table->decimal('price_monthly', 12, 2)->default(0);
                $table->decimal('price_yearly', 12, 2)->default(0);
                $table->decimal('price_one_time', 12, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default application suites
            DB::table('cms_apps')->insert([
                ['name' => 'Mutasi Suite', 'code' => 'mutasi', 'icon' => 'bi bi-arrow-repeat', 'billing_type' => 'freemium', 'description' => 'Aplikasi Mutasi Finansial Gratis + Addon Metered Akun Bank (100k/bank)', 'price_monthly' => 0, 'price_one_time' => 0, 'created_at' => now()],
                ['name' => 'CRM Suite', 'code' => 'crm', 'icon' => 'bi bi-people-fill', 'billing_type' => 'subscription', 'description' => 'Leads Management, Sales Pipeline Deals & Follow-up (Subscription)', 'price_monthly' => 500000, 'price_yearly' => 5000000, 'created_at' => now()],
                ['name' => 'Invoicing & Billing', 'code' => 'invoicing', 'icon' => 'bi bi-receipt-cutoff', 'billing_type' => 'one_time', 'description' => 'Tagihan Invoice, Payment Gateway & Tax Billing (Sekali Beli / Lifetime)', 'price_one_time' => 2500000, 'price_monthly' => 0, 'created_at' => now()],
                ['name' => 'HR & Absensi', 'code' => 'hr', 'icon' => 'bi bi-clock-history', 'billing_type' => 'subscription', 'description' => 'Manajemen SDM, Kehadiran, Cuti & Penggajian (Subscription)', 'price_monthly' => 350000, 'price_yearly' => 3500000, 'created_at' => now()],
            ]);
        }

        // 2. Tenants Registry Table
        if (!Schema::hasTable('cms_tenants')) {
            Schema::create('cms_tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Yayasan ABC"
                $table->string('code')->unique(); // e.g. "yayasan-abc"
                $table->string('domain')->nullable(); // Custom domain e.g. "yayasanabc.com"
                $table->enum('db_mode', ['shared', 'separate', 'dedicated'])->default('shared');
                $table->string('db_host')->nullable();
                $table->string('db_port')->nullable();
                $table->string('db_name')->nullable();
                $table->string('db_username')->nullable();
                $table->text('db_password')->nullable();
                $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
                $table->timestamps();
            });
        }

        // 3. Branches / Entities / Sub-Usaha Table
        if (!Schema::hasTable('cms_branches')) {
            Schema::create('cms_branches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name'); // e.g. "PT A", "PT B", "Cabang Jakarta"
                $table->string('code')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('cms_tenants')->onDelete('cascade');
            });
        }

        // 4. Tenant App Subscriptions Table
        if (!Schema::hasTable('cms_tenant_subscriptions')) {
            Schema::create('cms_tenant_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('app_code'); // e.g. 'mutasi', 'crm'
                $table->string('plan')->default('starter'); // 'starter', 'pro', 'enterprise'
                $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
                $table->dateTime('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('cms_tenants')->onDelete('cascade');
            });
        }

        // 5. Tenant Addon Subscriptions Table
        if (!Schema::hasTable('cms_tenant_addons')) {
            Schema::create('cms_tenant_addons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('addon_code'); // e.g. 'extra_users', 'extra_storage', 'whatsapp_gateway'
                $table->integer('quantity')->default(1);
                $table->enum('status', ['active', 'expired'])->default('active');
                $table->dateTime('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('cms_tenants')->onDelete('cascade');
            });
        }

        // Add app_code to cms_moduls
        if (Schema::hasTable('cms_moduls') && !Schema::hasColumn('cms_moduls', 'app_code')) {
            Schema::table('cms_moduls', function (Blueprint $table) {
                $table->string('app_code')->nullable()->after('icon');
            });
        }

        // Add app_code to cms_menus
        if (Schema::hasTable('cms_menus') && !Schema::hasColumn('cms_menus', 'app_code')) {
            Schema::table('cms_menus', function (Blueprint $table) {
                $table->string('app_code')->nullable()->after('parent_id');
            });
        }

        // Add tenant_id & branch_id to cms_users
        if (Schema::hasTable('cms_users')) {
            Schema::table('cms_users', function (Blueprint $table) {
                if (!Schema::hasColumn('cms_users', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->after('id_cms_privileges');
                }
                if (!Schema::hasColumn('cms_users', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
                }
            });
        }

        // Add tenant_id to cms_privileges
        if (Schema::hasTable('cms_privileges') && !Schema::hasColumn('cms_privileges', 'tenant_id')) {
            Schema::table('cms_privileges', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_tenant_addons');
        Schema::dropIfExists('cms_tenant_subscriptions');
        Schema::dropIfExists('cms_branches');
        Schema::dropIfExists('cms_tenants');
        Schema::dropIfExists('cms_apps');
    }
};
