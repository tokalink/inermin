<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tokalink\Inermin\helpers\Inermin;

class InerminTenantsController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_tenants';
        $this->primary_key = 'id';
        $this->title_field = 'name';

        // Auto-upgrade cms_tenants schema if missing columns
        if (Schema::hasTable('cms_tenants')) {
            if (!Schema::hasColumn('cms_tenants', 'db_mode')) {
                Schema::table('cms_tenants', function ($table) {
                    $table->enum('db_mode', ['shared', 'separate', 'dedicated'])->default('shared')->after('domain');
                    $table->string('db_host')->nullable()->after('db_mode');
                    $table->string('db_port')->nullable()->after('db_host');
                    $table->string('db_name')->nullable()->after('db_port');
                    $table->string('db_username')->nullable()->after('db_name');
                    $table->text('db_password')->nullable()->after('db_username');
                });
            }
        }

        // Custom Action Button: Impersonate Tenant
        $this->addaction[] = [
            'label' => 'Impersonate',
            'icon' => 'bi bi-incognito',
            'color' => 'amber',
            'url' => url(Inermin::adminPath() . '/tenants/impersonate/[id]'),
            'showIf' => 'row.status === "active"',
        ];

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'TENANT NAME', 'name' => 'name'],
            ['label' => 'CODE', 'name' => 'code', 'callback' => function ($row) {
                return '<span class="font-mono font-bold text-amber-500 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">' . $row->code . '</span>';
            }],
            ['label' => 'CUSTOM DOMAIN', 'name' => 'domain', 'callback' => function ($row) {
                return $row->domain 
                    ? '<a href="http://' . $row->domain . '" target="_blank" class="font-mono text-xs text-indigo-400 hover:underline flex items-center gap-1"><i class="bi bi-globe"></i> ' . $row->domain . '</a>'
                    : '<span class="text-stone-500 italic">Subdomain Default</span>';
            }],
            ['label' => 'DATABASE MODE', 'name' => 'db_mode', 'callback' => function ($row) {
                $mode = $row->db_mode ?? 'shared';
                return match ($mode) {
                    'separate' => '<span class="px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Separate DB</span>',
                    'dedicated' => '<span class="px-2 py-0.5 rounded-md text-xs font-bold bg-violet-500/10 text-violet-400 border border-violet-500/20">Dedicated Server</span>',
                    default => '<span class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Shared DB</span>',
                };
            }],
            ['label' => 'STATUS', 'name' => 'status', 'callback' => function ($row) {
                return match ($row->status) {
                    'active' => '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Active</span>',
                    'suspended' => '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Suspended</span>',
                    default => '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">Expired</span>',
                };
            }],
        ];

        $this->form = [
            ['label' => 'Tenant Company Name', 'name' => 'name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Yayasan ABC, PT Sejahtera'],
            ['label' => 'Tenant Slug Code', 'name' => 'code', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. yayasan-abc (Unique slug identifier)'],
            ['label' => 'Custom Domain', 'name' => 'domain', 'type' => 'text', 'placeholder' => 'e.g. portal.yayasanabc.com (Optional)'],
            [
                'label' => 'Database Isolation Mode',
                'name'  => 'db_mode',
                'type'  => 'select',
                'dataenum' => [
                    'shared'    => 'Shared Database (Default - Shared Server DB)',
                    'separate'  => 'Separate Database (Addon - Isolated DB on Same Server)',
                    'dedicated' => 'Dedicated Server (Enterprise Addon - On-Premise DB Server)'
                ],
                'required' => true
            ],
            ['label' => 'Dedicated DB Host IP/URL', 'name' => 'db_host', 'type' => 'text', 'placeholder' => 'e.g. 10.0.1.50 or db.client.com'],
            ['label' => 'Dedicated DB Port', 'name' => 'db_port', 'type' => 'text', 'placeholder' => 'e.g. 3306'],
            ['label' => 'Dedicated DB Name', 'name' => 'db_name', 'type' => 'text', 'placeholder' => 'e.g. ptsejahtera_db'],
            ['label' => 'Dedicated DB Username', 'name' => 'db_username', 'type' => 'text', 'placeholder' => 'e.g. db_user'],
            ['label' => 'Dedicated DB Password', 'name' => 'db_password', 'type' => 'password', 'placeholder' => '••••••••'],
            [
                'label' => 'Account Status',
                'name'  => 'status',
                'type'  => 'select',
                'dataenum' => [
                    'active'    => 'Active',
                    'suspended' => 'Suspended',
                    'expired'   => 'Expired'
                ],
                'required' => true
            ],
        ];
    }

    public function getImpersonate($id)
    {
        if (!Inermin::isSuperadmin()) {
            return redirect(Inermin::adminPath())->with('error', 'Only Superadmins can impersonate tenants!');
        }

        $tenant = DB::table('cms_tenants')->where('id', $id)->first();
        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant record not found!');
        }

        // Find tenant admin user
        $tenantUser = DB::table('cms_users')->where('tenant_id', $tenant->id)->first();
        if (!$tenantUser) {
            // Create a temporary tenant admin user if none exists
            $userId = DB::table('cms_users')->insertGetId([
                'name' => 'Admin ' . $tenant->name,
                'email' => 'admin@' . $tenant->code . '.com',
                'password' => bcrypt('123456'),
                'id_cms_privileges' => 1,
                'tenant_id' => $tenant->id,
                'status' => 'Active',
                'created_at' => now(),
            ]);
            $tenantUser = DB::table('cms_users')->where('id', $userId)->first();
        }

        // Save original superadmin session & switch to tenant user
        session()->put('impersonate_original_user_id', session()->get('admin_id'));
        session()->put('impersonate_tenant_name', $tenant->name);
        session()->put('admin_id', $tenantUser->id);

        Inermin::insertLog("Started impersonating Tenant #{$tenant->id} ({$tenant->name})");

        return redirect(Inermin::adminPath())->with('success', "Now impersonating Tenant '{$tenant->name}' (IT Support Mode)");
    }

    public function getStopImpersonate()
    {
        $originalUserId = session()->get('impersonate_original_user_id');
        if ($originalUserId) {
            session()->put('admin_id', $originalUserId);
            session()->forget(['impersonate_original_user_id', 'impersonate_tenant_name']);

            Inermin::insertLog("Stopped impersonating Tenant");
            return redirect(Inermin::adminPath())->with('success', 'Exited IT Support impersonation mode');
        }

        return redirect(Inermin::adminPath());
    }
}
