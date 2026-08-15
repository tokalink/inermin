<?php

namespace Tokalink\Inermin\controllers;

use Tokalink\Inermin\helpers\Inermin;

class InerminApiController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_apicustom';
        $this->primary_key = 'id';
        $this->title_field = 'nama';

        $tables = Inermin::listTables();
        $tableOptions = array_combine($tables, $tables);

        $this->addaction[] = [
            'label' => 'Test API',
            'icon' => 'bi bi-[code-square]',
            'color' => 'emerald',
            'url' => url('/api/[permalink]'),
            'target' => '_blank',
        ];

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'API NAME', 'name' => 'nama'],
            ['label' => 'PERMALINK', 'name' => 'permalink', 'callback' => function ($row) {
                return '<span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-lg border border-indigo-200/40 dark:border-indigo-800/40">/api/' . $row->permalink . '</span>';
            }],
            ['label' => 'TABLE', 'name' => 'tabel'],
            ['label' => 'METHOD', 'name' => 'method_type', 'callback' => function ($row) {
                $method = strtoupper($row->method_type ?: 'GET');
                $color = match ($method) {
                    'GET' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                    'POST' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                    'PUT' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                    'DELETE' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                    default => 'bg-stone-500/10 text-stone-500 border-stone-500/20',
                };
                return '<span class="px-2.5 py-0.5 rounded-full text-xs font-black font-mono border ' . $color . '">' . $method . '</span>';
            }],
            ['label' => 'ACTION', 'name' => 'aksi', 'callback' => function ($row) {
                return '<span class="uppercase font-bold text-xs text-stone-700 dark:text-stone-300">' . ($row->aksi ?: 'list') . '</span>';
            }],
        ];

        $this->form = [
            ['label' => 'API Name', 'name' => 'nama', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Get Attendance List'],
            ['label' => 'Target Table', 'name' => 'tabel', 'type' => 'select', 'dataenum' => $tableOptions, 'required' => true],
            ['label' => 'Permalink Slug', 'name' => 'permalink', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. get_absen_list (accessed via /api/get_absen_list)'],
            ['label' => 'HTTP Method', 'name' => 'method_type', 'type' => 'select', 'dataenum' => ['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'], 'required' => true],
            ['label' => 'API Action Type', 'name' => 'aksi', 'type' => 'select', 'dataenum' => ['list' => 'List Data (Paginated)', 'detail' => 'Detail Single Record (by ID)', 'add' => 'Add New Record', 'edit' => 'Update Record (by ID)', 'delete' => 'Delete Record (by ID)'], 'required' => true],
            ['label' => 'SQL Where Condition', 'name' => 'sql_where', 'type' => 'text', 'placeholder' => 'e.g. is_active=1 (Optional)'],
            ['label' => 'SQL OrderBy', 'name' => 'sql_orderby', 'type' => 'text', 'placeholder' => 'e.g. id desc (Optional)'],
            ['label' => 'Secret Key (Auth Token)', 'name' => 'secret_key', 'type' => 'text', 'placeholder' => 'e.g. mysecretkey123 (Optional for X-Authorization-Token)'],
        ];
    }
}
