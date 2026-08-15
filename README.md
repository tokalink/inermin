# Inermin — CRUDBooster Reborn Executive SPA Admin

**Inermin** is a high-performance, modern Single Page Application (SPA) administration framework for Laravel built on **Inertia.js**, **Vue 3 Composition API**, **Vite**, and **TailwindCSS**. It brings the rapid CRUD generation capabilities of CRUDBooster into a state-of-the-art **Aether Console** design system.

---

## 🌟 Core Package Features & Capabilities

- ⚡ **Full Inertia.js Vue 3 SPA**: Seamless page transitions without full browser reloads.
- 🎨 **Aether Console Design System**: Warm Obsidian Dark (`#0c0b09`) background canvas, Obsidian Card (`#15130f`), glassmorphism headers, `Space Grotesk` display typography, and `Plus Jakarta Sans` body typography.
- 🌈 **6 Dynamic Color Accent Swatches**: Switch accent colors (Amber, Emerald, Crimson, Ocean, Violet, Bronze) instantly across all components via topbar swatch picker.
- 🚀 **FastExcel High-Performance Export & Import**: Integrated `rap2hpoutre/fast-excel` for streaming XLSX/CSV data exports and imports without memory bottlenecks.
- 🛠️ **4-Step Module Generator**: Interactive wizard to generate database-backed CRUD modules, auto-create controllers, and auto-assign privilege permissions.
- 🛡️ **Role-Based Access Control (RBAC) Matrix**: Granular per-module privilege checklist (Is Visible, Is Create, Is Read, Is Edit, Is Delete) with quick row/column toggles.
- 📊 **Visual Statistic Builder**: Drag-and-drop dashboard canvas with 4 layout areas, 5 widget types (Stat Cards, Bar Charts, Line Charts, Custom HTML, Data Tables), real-time SQL execution, and live auto-save.
- 📑 **Nested Multi-Level Menu System**: Support for top-level menus, single module links, and parent-child submenu accordions with auto-expansion for active routes.

---

## 📐 Architecture Overview

```
inermin/
├── config/
│   └── inermin.php                     # Package configuration (ADMIN_PATH, APP_NAME, PRIMARY_COLOR)
├── packages/inermin/
│   ├── src/
│   │   ├── commands/
│   │   │   └── InerminInstallCommand.php  # php artisan inermin:install
│   │   ├── controllers/                # Core System Controllers
│   │   │   ├── InerminAuthController.php
│   │   │   ├── InerminDashboardController.php
│   │   │   ├── InerminUsersController.php
│   │   │   ├── InerminPrivilegesController.php
│   │   │   ├── InerminMenusController.php
│   │   │   ├── InerminModulsController.php
│   │   │   ├── InerminStatisticController.php
│   │   │   ├── InerminApiController.php
│   │   │   ├── InerminEmailController.php
│   │   │   └── InerminLogsController.php
│   │   ├── helpers/
│   │   │   └── Inermin.php             # Core CRUDBooster Helper methods
│   │   ├── middleware/
│   │   │   ├── InerminAuthMiddleware.php
│   │   │   └── InerminShareInertiaData.php # Inertia data sharing & menu tree parsing
│   │   └── routes/
│   │       └── web.php                 # Admin routes & dynamic module router
│   └── resources/js/
│       ├── InerminAppLayout.vue        # Aether Console Layout & Navigation Shell
│       ├── Datagrid.vue                # Dynamic Data Table Component
│       ├── Form.vue                    # Dynamic Form Schema Component
│       ├── Dashboard.vue               # Executive Dashboard
│       ├── Auth/Login.vue              # Glassmorphic Login
│       ├── Modules/
│       │   ├── Index.vue               # Module Registry List
│       │   └── Wizard.vue              # 4-Step Module Generator
│       ├── Privileges/
│       │   ├── Index.vue               # Privileges Roles List
│       │   └── Form.vue                # RBAC Permissions Matrix Checklist
│       └── StatisticBuilder/
│           ├── Builder.vue             # Drag & Drop Canvas Builder
│           └── Show.vue                # Live Custom Statistic Dashboard
```

---

## 🛠️ Installation & Setup Guide

### 1. Requirements
- PHP 8.2+
- Laravel 11.x or 12.x
- Node.js 18+ & NPM

### 2. Composer Dependencies
Add FastExcel and Inermin package dependencies:
```bash
composer require rap2hpoutre/fast-excel
```

### 3. Run Inermin Installer
Execute the installation artisan command to publish views, migrations, assets, and seed default admin accounts:
```bash
php artisan inermin:install
```

### 4. Build Frontend Assets
Compile Inertia Vue 3 assets via Vite:
```bash
npm run build
```

### 5. Access Admin Portal
Start your Laravel development server:
```bash
php artisan serve
```
Navigate to `http://localhost:8000/administrator` (Default credentials: `superadmin@inermin.com` / `123456`).

---

## 🎨 Theme & Accent Customization System

The **Aether Console** design system uses CSS custom properties defined in `InerminAppLayout.vue`:

| Accent Name | `--accent-rgb` | `--accent-soft` | `--accent-deep` | Description |
| :--- | :--- | :--- | :--- | :--- |
| **Amber** | `217, 119, 6` | `#f59e0b` | `#b45309` | Warm executive amber (default) |
| **Emerald** | `5, 150, 105` | `#10b981` | `#047857` | Vibrant mint emerald |
| **Crimson** | `220, 38, 38` | `#ef4444` | `#b91c1c` | Deep crimson ruby |
| **Ocean** | `14, 116, 144` | `#06b6d4` | `#0e7490` | Cyber cyan ocean |
| **Violet** | `124, 58, 237` | `#8b5cf6` | `#6d28d9` | Royal amethyst violet |
| **Bronze** | `180, 83, 9` | `#d97706` | `#92400e` | Classic metallic bronze |

---

## 📑 Core Package Module Documentation

### 1. Dynamic Datagrid (`Datagrid.vue`)
- **FastExcel Stream Export**: Click `Export Data` to stream XLSX spreadsheet downloads directly without memory buffer limits.
- **FastExcel Stream Import**: Click `Import Data` to upload XLSX files and batch insert rows.
- **Live Search & Filter**: Real-time multi-column search with active pagination state retention.

### 2. Module Generator (`Modules/Wizard.vue`)
- **Step 1**: Basic info (Module Name, Table, Icon, Slug).
- **Step 2**: Datagrid Column display settings.
- **Step 3**: Form field schema definition (Text, Select, Upload, Date, etc.).
- **Step 4**: Finalization & automatic route generation.
- *Note*: Creating a module automatically registers RBAC privilege matrix entries (`cms_privileges_roles`) for all roles.

### 3. Privileges Role Permission Matrix (`Privileges/Form.vue`)
- Responsive grid checklist matrix allowing administrators to set **Is Visible**, **Is Create**, **Is Read**, **Is Edit**, and **Is Delete** permissions per module.
- Includes quick-toggle column buttons (*Toggle All View*, *Toggle All Create*, etc.).

### 4. Statistic Builder (`StatisticBuilder/Builder.vue` & `Show.vue`)
- Visual drag-and-drop canvas with 4 layout areas.
- Configurable widget types:
  1. `smallbox`: Stat Card with real-time SQL count execution.
  2. `chartbar`: Bar Chart visualization.
  3. `chartline`: Line Chart visualization.
  4. `panelcustom`: Custom HTML/Markdown text box.
  5. `table`: Database query table widget.
- Real-time SQL variable substitution (`[admin_id]`, `[admin_name]`).

---

## 💡 Custom Application Modules
Custom modules built by the user in their application project (e.g. `AdminChatsController`, `AdminAbsenController`) inherit all Aether Console theme styles, layout wrappers, and FastExcel import/export capabilities automatically!

---

## 📄 License & Credits
Developed by the **Inermin Team** & **Antigravity AI**. Powered by Laravel, Inertia.js, Vue 3, and CRUDBooster Reborn.
