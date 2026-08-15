# 🚀 Installation Guide via GitHub Repository (Pre-Publishing)

Because **Inermin** is currently in active development before public registration on Packagist, you can install it directly into any fresh or existing Laravel 11 / 12 application via **GitHub VCS Repository**.

---

## 📋 Requirements
- **PHP**: ^8.2
- **Laravel**: 11.x or 12.x
- **Node.js**: 18+ & NPM
- **Git**: Installed on your system

---

## 🛠️ Step 1: Configure `composer.json` in Your Laravel Project

Open your Laravel application's `composer.json` file and add the GitHub repository definition under `"repositories"`:

```json
{
    "name": "laravel/laravel",
    "type": "project",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/tokalink/inermin.git"
        }
    ],
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0|^12.0",
        "rap2hpoutre/fast-excel": "^5.0",
        "tokalink/inermin": "dev-main"
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

---

## 📦 Step 2: Install Package via Composer

Run the composer update / require command in your terminal:

```bash
composer require tokalink/inermin:dev-main
```

*Composer will download the latest package source directly from `https://github.com/tokalink/inermin.git` into `vendor/tokalink/inermin`.*

---

## ⚙️ Step 3: Run the 100% Automated Installer

Execute the single automated Artisan installer command:

```bash
php artisan inermin:install
```

### What `inermin:install` automatically performs:
1. **Configures `package.json`**: Adds `@inertiajs/vue3`, `@vitejs/plugin-vue`, `vue`, and required dependencies.
2. **Configures `vite.config.js`**: Injects `@vitejs/plugin-vue` plugin and `@inermin` alias pointing to package resources.
3. **Configures `resources/js/app.js`**: Sets up multi-tier Inertia page resolver (prioritizing local `resources/js/Pages` before `vendor/`).
4. **Publishes Public Assets**: Copies default avatars and icons to `public/vendor/inermin`.
5. **Publishes Customizable `Dashboard.vue`**: Automatically places a customizable `Dashboard.vue` inside `resources/js/Pages/Inermin/Dashboard.vue` so you can customize your admin homepage right away!
6. **Runs Database Migrations & Seeders**: Creates core tables (`cms_users`, `cms_privileges`, `cms_moduls`, `cms_menus`, etc.) and seeds default administrator credentials.
7. **Runs NPM Install & Build**: Automatically installs Node dependencies and compiles Vite production assets.

---

## ⚡ Step 4: Build Frontend Assets & Serve

Compile Vite assets:

```bash
# For Development (Hot Reloading)
npm run dev

# For Production Bundle
npm run build
```

Start your Laravel development server:

```bash
php artisan serve
```

---

## 🔑 Default Credentials & Access

Navigate to `http://localhost:8000/administrator` in your browser.

- **Admin URL**: `/administrator`
- **Default Email**: `admin@inermin.com`
- **Default Password**: `123456`
