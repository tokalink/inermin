<?php

namespace Tokalink\Inermin\controllers;

class InerminStatisticController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_statistics';
        $this->primary_key = 'id';
        $this->title_field = 'name';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Statistic Name', 'name' => 'name'],
            ['label' => 'Slug', 'name' => 'slug'],
        ];

        $this->form = [
            ['label' => 'Statistic Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Slug', 'name' => 'slug', 'type' => 'text', 'required' => true],
        ];
    }
}
