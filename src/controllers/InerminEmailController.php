<?php

namespace Tokalink\Inermin\controllers;

class InerminEmailController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_email_templates';
        $this->primary_key = 'id';
        $this->title_field = 'name';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Template Name', 'name' => 'name'],
            ['label' => 'Slug', 'name' => 'slug'],
            ['label' => 'Subject', 'name' => 'subject'],
        ];

        $this->form = [
            ['label' => 'Template Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Slug', 'name' => 'slug', 'type' => 'text', 'required' => true],
            ['label' => 'Subject', 'name' => 'subject', 'type' => 'text', 'required' => true],
            ['label' => 'Content', 'name' => 'content', 'type' => 'textarea'],
        ];
    }
}
