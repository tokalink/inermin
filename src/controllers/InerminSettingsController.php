<?php

namespace Tokalink\Inermin\controllers;

class InerminSettingsController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_settings';
        $this->primary_key = 'id';
        $this->title_field = 'name';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Setting Name', 'name' => 'name'],
            ['label' => 'Group', 'name' => 'group_setting'],
            ['label' => 'Content', 'name' => 'content'],
        ];

        $this->form = [
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Group Setting', 'name' => 'group_setting', 'type' => 'text'],
            ['label' => 'Content', 'name' => 'content', 'type' => 'textarea'],
        ];
    }
}
