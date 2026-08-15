# 🛡️ Privileges, RBAC Matrix & Security Authorization

Security in **Inermin** is built around a granular **Role-Based Access Control (RBAC)** matrix and strict backend route protection.

---

## 🔐 Privileges Permission Matrix (`/administrator/privileges`)

The Privilege Matrix allows administrators to configure fine-grained permissions per module:

- 👁️ **Is Visible**: Controls whether the module menu appears in the sidebar and index view is accessible.
- ➕ **Is Create**: Controls permission to add new records (`/add`).
- 📖 **Is Read**: Controls permission to view record details (`/detail/{id}`).
- ✏️ **Is Edit**: Controls permission to update existing records (`/edit/{id}`).
- ❌ **Is Delete**: Controls permission to delete records (`/delete/{id}`).

### Master Checkboxes:
Each module row features a **Master Checkbox** in the *Row Action* column, allowing administrators to select or deselect all 5 permissions for that module in 1 click!

---

## 🚫 Superadmin Route Protection

All core management controllers (Privileges, Users, Modules, Menus, Settings, API Generator, Statistic Builder, Logs) are protected with strict backend authorization:

```php
if (!Inermin::isSuperadmin()) {
    return redirect(Inermin::adminPath())->with('error', 'Access Denied! Only superadmin accounts can access this area.');
}
```

Even if a non-superadmin user manually types `/administrator/privileges` or `/administrator/modules` directly into the browser URL bar, the backend **strictly blocks access with an Access Denied error**.
