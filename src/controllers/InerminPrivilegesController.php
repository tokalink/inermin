<?php

namespace Tokalink\Inermin\controllers;

class InerminPrivilegesController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_privileges';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->orderby = 'id,asc';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Privilege Name', 'name' => 'name'],
            ['label' => 'Is Superadmin', 'name' => 'is_superadmin'],
        ];

        $this->form = [
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Is Superadmin', 'name' => 'is_superadmin', 'type' => 'select', 'dataenum' => ['1' => 'Yes', '0' => 'No']],
            ['label' => 'Theme Color', 'name' => 'theme_color', 'type' => 'text'],
        ];
    }
}
