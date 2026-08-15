<?php

namespace Tokalink\Inermin\controllers;

class InerminLogsController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_logs';
        $this->primary_key = 'id';
        $this->title_field = 'description';
        $this->orderby = 'id,desc';

        $this->button_add = false;
        $this->button_edit = false;

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'IP Address', 'name' => 'ipaddress'],
            ['label' => 'User ID', 'name' => 'id_cms_users'],
            ['label' => 'Description', 'name' => 'description'],
            ['label' => 'Timestamp', 'name' => 'created_at'],
        ];
    }
}
