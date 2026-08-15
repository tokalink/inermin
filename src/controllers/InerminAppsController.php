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

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'APP NAME', 'name' => 'name'],
            ['label' => 'CODE', 'name' => 'code', 'callback' => function ($row) {
                return '<span class="font-mono text-amber-500 font-bold bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">' . $row->code . '</span>';
            }],
            ['label' => 'PRICE / MO', 'name' => 'price_monthly', 'callback' => function ($row) {
                return '<span class="font-mono font-bold text-emerald-500">Rp ' . number_format($row->price_monthly ?: 0, 0, ',', '.') . '</span>';
            }],
            ['label' => 'STATUS', 'name' => 'is_active', 'callback' => function ($row) {
                return $row->is_active 
                    ? '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Active</span>'
                    : '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Inactive</span>';
            }],
        ];

        $this->form = [
            ['label' => 'Application Name', 'name' => 'name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Mutasi Suite, CRM Suite'],
            ['label' => 'Application Code', 'name' => 'code', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. mutasi, crm, invoicing (slug identifier)'],
            ['label' => 'Icon Class', 'name' => 'icon', 'type' => 'text', 'placeholder' => 'e.g. bi bi-arrow-repeat, bi bi-people-fill'],
            ['label' => 'Monthly Price (Rp)', 'name' => 'price_monthly', 'type' => 'money', 'required' => true],
            ['label' => 'Is Active', 'name' => 'is_active', 'type' => 'radio', 'dataenum' => ['1' => 'Active', '0' => 'Inactive'], 'required' => true],
            ['label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'width' => 'col-span-12'],
        ];
    }
}
