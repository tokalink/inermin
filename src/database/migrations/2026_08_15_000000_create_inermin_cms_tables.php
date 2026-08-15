<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // cms_users
        if (! Schema::hasTable('cms_users')) {
            Schema::create('cms_users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('photo')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->integer('id_cms_privileges')->nullable();
                $table->string('status', 50)->nullable()->default('Active');
                $table->timestamps();
            });
        }

        // cms_privileges
        if (! Schema::hasTable('cms_privileges')) {
            Schema::create('cms_privileges', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->boolean('is_superadmin')->default(false);
                $table->string('theme_color')->nullable()->default('skin-blue');
                $table->timestamps();
            });
        }

        // cms_privileges_roles
        if (! Schema::hasTable('cms_privileges_roles')) {
            Schema::create('cms_privileges_roles', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_create')->default(false);
                $table->boolean('is_read')->default(false);
                $table->boolean('is_edit')->default(false);
                $table->boolean('is_delete')->default(false);
                $table->integer('id_cms_privileges')->nullable();
                $table->integer('id_cms_moduls')->nullable();
                $table->timestamps();
            });
        }

        // cms_moduls
        if (! Schema::hasTable('cms_moduls')) {
            Schema::create('cms_moduls', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('icon')->nullable();
                $table->string('path')->nullable();
                $table->string('table_name')->nullable();
                $table->string('controller')->nullable();
                $table->boolean('is_protected')->default(false);
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // cms_menus
        if (! Schema::hasTable('cms_menus')) {
            Schema::create('cms_menus', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('type')->default('Module');
                $table->string('path')->nullable();
                $table->string('color')->nullable();
                $table->string('icon')->nullable();
                $table->integer('parent_id')->nullable()->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_dashboard')->default(false);
                $table->integer('id_cms_privileges')->nullable();
                $table->integer('sorting')->default(0);
                $table->timestamps();
            });
        }

        // cms_menus_privileges
        if (! Schema::hasTable('cms_menus_privileges')) {
            Schema::create('cms_menus_privileges', function (Blueprint $table) {
                $table->id();
                $table->integer('id_cms_menus')->nullable();
                $table->integer('id_cms_privileges')->nullable();
            });
        }

        // cms_settings
        if (! Schema::hasTable('cms_settings')) {
            Schema::create('cms_settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->text('content')->nullable();
                $table->string('content_input_type')->nullable()->default('text');
                $table->string('dataenum')->nullable();
                $table->string('helper')->nullable();
                $table->string('group_setting')->nullable()->default('General Setting');
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        // cms_logs
        if (! Schema::hasTable('cms_logs')) {
            Schema::create('cms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ipaddress')->nullable();
                $table->string('useragent')->nullable();
                $table->string('url')->nullable();
                $table->string('description')->nullable();
                $table->integer('id_cms_users')->nullable();
                $table->timestamps();
            });
        }

        // cms_notifications
        if (! Schema::hasTable('cms_notifications')) {
            Schema::create('cms_notifications', function (Blueprint $table) {
                $table->id();
                $table->integer('id_cms_users')->nullable();
                $table->string('content')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        // cms_email_templates
        if (! Schema::hasTable('cms_email_templates')) {
            Schema::create('cms_email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->string('subject')->nullable();
                $table->text('content')->nullable();
                $table->string('description')->nullable();
                $table->string('from_name')->nullable();
                $table->string('from_email')->nullable();
                $table->string('cc_email')->nullable();
                $table->timestamps();
            });
        }

        // cms_apicustom
        if (! Schema::hasTable('cms_apicustom')) {
            Schema::create('cms_apicustom', function (Blueprint $table) {
                $table->id();
                $table->string('permalink')->nullable();
                $table->string('tabel')->nullable();
                $table->string('aksi')->nullable();
                $table->string('method_type')->nullable()->default('get');
                $table->text('parameters')->nullable();
                $table->text('responses')->nullable();
                $table->timestamps();
            });
        }

        // cms_statistics
        if (! Schema::hasTable('cms_statistics')) {
            Schema::create('cms_statistics', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }

        // cms_statistic_components
        if (! Schema::hasTable('cms_statistic_components')) {
            Schema::create('cms_statistic_components', function (Blueprint $table) {
                $table->id();
                $table->integer('id_cms_statistics')->nullable();
                $table->string('componentID')->nullable();
                $table->string('component_name')->nullable();
                $table->text('config')->nullable();
                $table->integer('sorting')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_statistic_components');
        Schema::dropIfExists('cms_statistics');
        Schema::dropIfExists('cms_apicustom');
        Schema::dropIfExists('cms_email_templates');
        Schema::dropIfExists('cms_notifications');
        Schema::dropIfExists('cms_logs');
        Schema::dropIfExists('cms_settings');
        Schema::dropIfExists('cms_menus_privileges');
        Schema::dropIfExists('cms_menus');
        Schema::dropIfExists('cms_moduls');
        Schema::dropIfExists('cms_privileges_roles');
        Schema::dropIfExists('cms_privileges');
        Schema::dropIfExists('cms_users');
    }
};
