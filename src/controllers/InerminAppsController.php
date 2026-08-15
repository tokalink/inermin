<?php

namespace Tokalink\Inermin\controllers;

use Tokalink\Inermin\helpers\Inermin;

class InerminAppsController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_apps';
        $this->primary_key = 'id';
        $this->title_field = 'name';

        // Auto-upgrade cms_apps schema if missing columns
        if (\Illuminate\Support\Facades\Schema::hasTable('cms_apps')) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('cms_apps', 'billing_type')) {
                \Illuminate\Support\Facades\Schema::table('cms_apps', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->enum('billing_type', ['subscription', 'one_time', 'freemium'])->default('subscription')->after('icon');
                    $table->decimal('price_yearly', 12, 2)->default(0)->after('price_monthly');
                    $table->decimal('price_one_time', 12, 2)->default(0)->after('price_yearly');
                });
            }
        }

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'APP NAME', 'name' => 'name'],
            ['label' => 'CODE', 'name' => 'code', 'callback' => function ($row) {
                return '<span class="font-mono text-amber-500 font-bold bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">' . $row->code . '</span>';
            }],
            ['label' => 'BILLING TYPE', 'name' => 'billing_type', 'callback' => function ($row) {
                $type = $row->billing_type ?? 'subscription';
                return match ($type) {
                    'one_time' => '<span class="px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Sekali Beli (Lifetime)</span>',
                    'freemium' => '<span class="px-2 py-0.5 rounded-md text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Freemium (Gratis + Addon)</span>',
                    default => '<span class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Berlangganan (Subscription)</span>',
                };
            }],
            ['label' => 'HARGA BULANAN', 'name' => 'price_monthly', 'callback' => function ($row) {
                return '<span class="font-mono font-bold text-emerald-500">Rp ' . number_format($row->price_monthly ?? 0, 0, ',', '.') . '</span>';
            }],
            ['label' => 'HARGA LIFETIME', 'name' => 'price_one_time', 'callback' => function ($row) {
                return '<span class="font-mono font-bold text-indigo-400">Rp ' . number_format($row->price_one_time ?? 0, 0, ',', '.') . '</span>';
            }],
            ['label' => 'STATUS', 'name' => 'is_active', 'callback' => function ($row) {
                return !empty($row->is_active)
                    ? '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Active</span>'
                    : '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Inactive</span>';
            }],
        ];

        $this->form = [
            ['label' => 'Application Name', 'name' => 'name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Mutasi Suite, CRM Suite'],
            ['label' => 'Application Code', 'name' => 'code', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. mutasi, crm, invoicing (slug identifier)'],
            ['label' => 'Icon Class', 'name' => 'icon', 'type' => 'text', 'placeholder' => 'e.g. bi bi-arrow-repeat, bi bi-people-fill'],
            [
                'label' => 'Model Pembayaran (Billing Model)',
                'name'  => 'billing_type',
                'type'  => 'select',
                'dataenum' => [
                    'subscription' => 'Berlangganan (Bulanan / Tahunan)',
                    'one_time'     => 'Sekali Beli (One-time Purchase / Lifetime)',
                    'freemium'     => 'Freemium (Aplikasi Gratis + Pay Per Addon / Per Unit Bank)'
                ],
                'required' => true
            ],
            ['label' => 'Harga Bulanan / Monthly (Rp)', 'name' => 'price_monthly', 'type' => 'money'],
            ['label' => 'Harga Tahunan / Yearly (Rp)', 'name' => 'price_yearly', 'type' => 'money'],
            ['label' => 'Harga Sekali Beli / One-Time (Rp)', 'name' => 'price_one_time', 'type' => 'money'],
            ['label' => 'Is Active', 'name' => 'is_active', 'type' => 'radio', 'dataenum' => ['1' => 'Active', '0' => 'Inactive'], 'required' => true],
            ['label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'width' => 'col-span-12'],
        ];
    }
}
