# Inermin — CRUDBooster Reborn Executive SPA Admin

**Inermin** is a high-performance, modern Single Page Application (SPA) administration framework for Laravel built on **Inertia.js**, **Vue 3 Composition API**, **Vite**, and **TailwindCSS**. It brings the rapid CRUD generation capabilities of CRUDBooster into a state-of-the-art **Aether Console** design system.

---

## 📚 Complete Package Documentation Sitemap

Comprehensive documentation guides are available in the [`docs/`](./docs) directory:

1. 🚀 [**Installation Guide via GitHub VCS Repository**](./docs/01-installation-github.md) — Step-by-step setup using GitHub composer repo & automated installer.
2. 🔍 [**List of Values (LOV) Guide**](./docs/02-lov-list-of-values.md) — Reusable lookup picker, multi-column search modal, and auto-fill field mapping.
3. 📝 [**Controller Form Schema & Field Types Reference**](./docs/03-form-schema-and-field-types.md) — `$this->form` reference, field types, width grid, height, and hooks.
4. 🎨 [**Zero-Touch Vendor Overriding & `inermin:make-view` CLI**](./docs/04-vendor-overriding-and-make-view.md) — Customizing views without editing `vendor/`.
5. 🛠️ [**Module Generator & Custom View Modules**](./docs/05-module-generator-and-custom-views.md) — Standard CRUD modules vs Custom View Modules.
6. 🛡️ [**Privileges, RBAC Matrix & Security Authorization**](./docs/06-privileges-rbac-and-security.md) — Permission checklist, master checkboxes, and superadmin route protection.
7. 💎 [**Guide: Eloquent Models & Hybrid Data Architecture**](./docs/07-using-eloquent-models.md) — Using Eloquent Models vs Query Builder in Inermin Controllers.

---

## 🌟 Core Package Features & Capabilities

- ⚡ **Full Inertia.js Vue 3 SPA**: Seamless page transitions without full browser reloads.
- 🎨 **Aether Console Design System**: Warm Obsidian Dark (`#0c0b09`) background canvas, Obsidian Card (`#15130f`), glassmorphism headers, `Space Grotesk` display typography, and `Plus Jakarta Sans` body typography.
- 🌈 **6 Dynamic Color Accent Swatches**: Switch accent colors (Amber, Emerald, Crimson, Ocean, Violet, Bronze) instantly across all components via topbar swatch picker.
- 🔍 **List of Values (LOV) Picker**: Reusable modal lookup picker with multi-column live search, fast AJAX pagination, and automatic field auto-fill mapping.
- 🎨 **WYSIWYG / CKEditor Rich Text Component**: Native formatting toolbar supporting `ckeditor`, `wysiwyg`, `html`, `tinymce`, and `richtext` field types with live `</> HTML` mode.
- 📐 **Dynamic Width & Height Customization**: Configure field grid width (`'width' => 'col-span-12 md:col-span-6'`) and editor height (`'height' => '200px'`).
- 👤 **Account & Password Security Management**: Built-in Profile Avatar upload, details edit, and Password change settings page (`/administrator/profile`).
- 🚀 **FastExcel High-Performance Export & Import**: Integrated `rap2hpoutre/fast-excel` for streaming XLSX/CSV data exports and imports without memory bottlenecks.
- 🛠️ **4-Step Module Generator**: Interactive wizard supporting both **Standard CRUD Modules** and **Custom View Modules** (Controller + Vue Scaffold).
- 🛡️ **Role-Based Access Control (RBAC) Matrix**: Granular per-module privilege checklist (Is Visible, Is Create, Is Read, Is Edit, Is Delete) with row-level **Master Checkboxes**.
- 📊 **Visual Statistic Builder**: Drag-and-drop dashboard canvas with 4 layout areas, 5 widget types (Stat Cards, Bar Charts, Line Charts, Custom HTML, Data Tables), real-time SQL execution, and live auto-save.
- 📑 **Nested Multi-Level Menu System**: Support for top-level menus, single module links, and parent-child submenu accordions with automatic parent resolution.

---

## 🛠️ Quick Installation via GitHub Repository

Because **Inermin** is in active development before Packagist registration, install it directly from GitHub:

### 1. Add GitHub Repository to your project `composer.json`:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/tokalink/inermin.git"
        }
    ]
}
```

### 2. Require Package via Composer:
```bash
composer require tokalink/inermin:dev-main
```

### 3. Run Automated Installer:
```bash
php artisan inermin:install
```

### 4. Compile Frontend Assets:
```bash
npm run build
```

### 5. Access Admin Portal:
Navigate to `http://localhost:8000/administrator` (Default credentials: `admin@inermin.com` / `123456`).

---

## 🛠️ Artisan CLI Helper Commands

```bash
# Generate a new custom Vue view page component
php artisan inermin:make-view Reports/MonthlySales

# Copy and override vendor core view (e.g., Dashboard) to local resources/js/Pages/
php artisan inermin:make-view Dashboard --force
```

---

## 📄 License & Credits
Developed by the **Inermin Team** & **Antigravity AI**. Powered by Laravel, Inertia.js, Vue 3, and CRUDBooster Reborn.
