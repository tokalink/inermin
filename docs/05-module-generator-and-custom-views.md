# 🛠️ Module Generator & Custom View Modules

Inermin features an interactive **4-Step Module Generator Wizard** accessible from `/administrator/modules`.

---

## 📊 Module Types: Standard CRUD vs Custom View Modules

In Step 1 of the Module Generator Wizard, you can choose between two module types:

### 1. 📊 Standard CRUD Module
- **Use Case**: Master data tables (e.g. Products, Customers, Orders, Employees).
- **Generated**: Database table mapping, Datagrid table, Form schema, Detail card.
- **Wizard Flow**: Steps 1 ➔ 2 (Columns) ➔ 3 (Form) ➔ 4 (Finish & Route Generation).

### 2. 💻 Custom View Module
- **Use Case**: Custom dashboards, reports, chat interfaces, tools, or complex pages that do **not** require standard Datagrid or Form tables.
- **Generated**: Controller (`AdminChatController.php`), Vue View Scaffold (`resources/js/Pages/Inermin/Chat/Index.vue`), Sidebar Menu Entry, and Privilege Matrix permissions.
- **Wizard Flow**: Steps 1 ➔ Directly skips to Step 4 (Finish & Route Generation).

---

## 📑 Nested Multi-Level Submenu System

Inermin supports top-level menus, single module links, and parent-child submenu accordions.

### Rules:
- If a child sub-menu is assigned to a role, its parent menu accordion is **automatically included and rendered**.
- Active submenus display a sleek accent pill highlight (`bg-[rgb(var(--accent-rgb))]/10`), proportional padding, and aligned vertical indicator lines.
