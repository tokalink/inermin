<?php

namespace Tokalink\Inermin\controllers;

class InerminApiController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_apicustom';
        $this->primary_key = 'id';
        $this->title_field = 'nama';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'API Name', 'name' => 'nama'],
            ['label' => 'Table', 'name' => 'tabel'],
            ['label' => 'Permalink', 'name' => 'permalink'],
            ['label' => 'Method', 'name' => 'method_type'],
        ];

        $this->form = [
            ['label' => 'API Name', 'name' => 'nama', 'type' => 'text', 'required' => true],
            ['label' => 'Table', 'name' => 'tabel', 'type' => 'text', 'required' => true],
            ['label' => 'Permalink', 'name' => 'permalink', 'type' => 'text', 'required' => true],
            ['label' => 'Method Type', 'name' => 'method_type', 'type' => 'select', 'dataenum' => ['GET', 'POST', 'PUT', 'DELETE']],
        ];
    }
}
