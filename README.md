# Inermin (Inertia.js + Vue 3 Admin Panel & CRUD Generator for Laravel)

[![Latest Stable Version](https://img.shields.io/badge/release-v1.0.0-indigo.svg)](https://github.com/tokalink/inermin)
[![Laravel Version](https://img.shields.io/badge/laravel-^10.0|^11.0|^12.0-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/php-^8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Inermin** is a modern, high-performance **Inertia.js + Vue 3 SPA** Admin Panel and dynamic **Module/CRUD Generator** for Laravel. Designed as a state-of-the-art replacement for CRUDBooster, Inermin offers zero full-page reloads, rich aesthetic design systems (light & dark modes), and an intuitive 4-step Module Generator.

---

## ✨ Features

- **⚡ Inertia.js + Vue 3 SPA Architecture**: Single Page Application experience with fast transitions and zero page reloads.
- **🧙 4-Step Module Generator Wizard**:
  1. **Module Info**: Select table, icon, path slug, and controller name.
  2. **Table Columns**: Configure datagrid display columns, labels, image flags.
  3. **Form Fields**: Build form inputs (`text`, `email`, `password`, `select`, `upload`, `date`, etc.).
  4. **Privileges & Finish**: Assign role permissions (Visible, Create, Read, Edit, Delete) and generate controller files.
- **🌳 Interactive Menu Management Builder**: Drag & drop tree structure builder with parent/child submenus, icon selector, and privilege role mappings.
- **👥 Role-Based Access Control (RBAC)**: Manage privileges, roles, and granular module permissions.
- **🎨 Premium UI Aesthetics**: Modern typography, vibrant accents, dark mode support, full-width responsive layout.
- **🛠️ Automated Controller Code Generator**: Automatically generates PHP controllers extending `InerminController` directly in `app/Http/Controllers/`.

---

## 📋 Requirements

- **PHP**: `^8.2`
- **Laravel Framework**: `^10.0`, `^11.0`, or `^12.0`
- **Node.js**: `^18.0` or higher
- **Inertia.js**: `^1.0` or `^2.0`

---

## 🚀 Installation Guide

### Step 1: Install Package via Composer

Run the following command in your Laravel project root:

```bash
composer require tokalink/inermin
```

*Or, if installing from a local path or repository:*

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/inermin"
    }
],
"require": {
    "tokalink/inermin": "*"
}
```

### Step 2: Run the Inermin Installer Command

Execute the automated installer to publish configs, migrate `cms_*` database tables, seed default superadmin accounts, and copy Vue 3 SPA pages:

```bash
php artisan inermin:install
```

### Step 3: Install Frontend Dependencies & Build Assets

Ensure Vue 3 and Inertia.js dependencies are installed in your Laravel project:

```bash
npm install
npm run build
```

*For local development with hot reloading:*

```bash
npm run dev
```

---

## 🔐 Default Access & Credentials

After running `php artisan inermin:install`, log into your admin panel at:

- **URL Path**: `http://your-domain.com/administrator`
- **Email**: `admin@crudbooster.com`
- **Password**: `123456`

*(You can customize the admin path prefix in `config/inermin.php`)*.

---

## 💻 Creating Custom Modules

### Option A: Using the Module Generator UI (Recommended)

1. Open your admin dashboard and navigate to **Module Generator** (`/administrator/modules`).
2. Click **"Generate New Module"**.
3. Complete the 4-Step Wizard.
4. Inermin will automatically create the controller file (e.g. `app/Http/Controllers/AdminProductsController.php`) and register the route `/administrator/products`!

### Option B: Creating Controllers Manually

Create a controller extending `InerminController`:

```php
<?php

namespace App\Http\Controllers;

use Tokalink\Inermin\controllers\InerminController;

class AdminProductsController extends InerminController
{
    public function cbInit()
    {
        $this->table = "products";
        $this->primary_key = "id";
        $this->title_field = "name";

        $this->col = [
            ['label' => 'Product Name', 'name' => 'name'],
            ['label' => 'Image', 'name' => 'photo', 'image' => true],
            ['label' => 'Price', 'name' => 'price'],
        ];

        $this->form = [
            ['label' => 'Product Name', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Product Photo', 'name' => 'photo', 'type' => 'upload', 'required' => false],
            ['label' => 'Price', 'name' => 'price', 'type' => 'number', 'required' => true],
        ];
    }
}
```

---

## ⚙️ Configuration (`config/inermin.php`)

Publish config if needed:

```bash
php artisan vendor:publish --tag=inermin-config
```

Config parameters:

```php
return [
    'ADMIN_PATH' => 'administrator',
    'APP_NAME' => 'Inermin Admin',
    'THEME_COLOR' => 'indigo',
];
```

---

## 📄 License

The Inermin package is open-source software licensed under the [MIT License](LICENSE).
