# 🎨 Zero-Touch Vendor Overriding & `inermin:make-view` CLI

In traditional admin packages, customizing vendor templates requires editing files inside `vendor/` (which get overwritten during `composer update`) or publishing dozens of core files into your project repository.

**Inermin** solves this with a **Single Source of Truth Architecture** and **Hierarchical Page Resolution**.

---

## 🏛️ How Page Resolution Works

When Inertia renders a page component (e.g. `Dashboard`, `Form`, `Datagrid`, `Chats/Index`), Inermin's page resolver inside `resources/js/app.js` checks paths in this exact hierarchy:

1. 🥇 `resources/js/Pages/${name}.vue` *(Local Application Project)*
2. 🥈 `resources/js/Pages/Inermin/${name}.vue` *(Local Application Overrides)*
3. 🥉 `vendor/tokalink/inermin/resources/js/${name}.vue` *(Core Package Vendor)*
4. 🏅 `packages/inermin/resources/js/${name}.vue` *(Local Development Package)*

### Key Advantage:
- You **NEVER edit files inside `vendor/`**.
- To override any core vendor view (e.g., `Dashboard.vue` or `Form.vue`), simply place a file with the same name inside `resources/js/Pages/Inermin/`.
- Inertia automatically loads your local file instead of the vendor template!

---

## 🛠️ The `inermin:make-view` Artisan Generator Command

To make customizing or scaffolding Vue pages effortless, Inermin provides the `inermin:make-view` artisan command.

### 1. Scaffold a New Custom Vue View Component
```bash
php artisan inermin:make-view Reports/MonthlySales
```
*Generates a scaffolded Vue 3 SFC with layout and Inertia imports inside `resources/js/Pages/Reports/MonthlySales.vue`.*

### 2. Copy & Override Vendor Core Views
If you want to customize a core package view (such as `Dashboard.vue`), run:

```bash
php artisan inermin:make-view Dashboard --force
```

### What happens:
1. `inermin:make-view` checks if a vendor template with that name exists (`Dashboard.vue`).
2. It automatically copies the vendor template directly to `resources/js/Pages/Inermin/Dashboard.vue`.
3. Replaces relative imports (e.g. `./InerminAppLayout.vue`) with clean path aliases (`@inermin/InerminAppLayout.vue`).
4. You can now edit `resources/js/Pages/Inermin/Dashboard.vue` directly in your editor without touching `vendor/`!
