# 🔍 List of Values (LOV) Guide

The **List of Values (LOV)** component in **Inermin** is an enterprise-grade lookup picker component designed for handling large datasets (such as Chart of Accounts / COA, Customers, Products, Employees, Items) without browser performance bottlenecks.

---

## 🌟 Why Use LOV Instead of `<select>`?

- 🚀 **High Performance (AJAX Pagination)**: Loads data in small chunks (8–10 items per page) instead of rendering 50,000 `<option>` tags into the DOM.
- 🔎 **Multi-Column Live Search**: Users can search by Code, Name, Email, Category, or City in a single search box.
- ⚡ **Auto-Fill Mapping**: Selecting a row in LOV can automatically fill adjacent fields (e.g. Phone, Address, Unit Price) in the form!

---

## 💻 Configuration in Controller (`$this->form`)

To use LOV in any CRUD module controller, define `'type' => 'lov'` in `$this->form`:

```php
$this->form[] = [
    'label'        => 'User / Pegawai',
    'name'         => 'user_id',
    'type'         => 'lov',
    'lov_table'    => 'cms_users',              // Target lookup database table
    'lov_value'    => 'id',                     // Primary key column stored to DB
    'lov_label'    => 'name',                   // Display label shown in input box
    'lov_columns'  => 'id,name,email',          // Columns displayed in search modal
    'lov_where'    => 'status="Active"',        // Optional SQL filter condition
    'lov_autofill' => [                         // Optional auto-fill field mapping
        'email'    => 'user_email',
        'phone'    => 'user_phone'
    ],
    'width'        => 'col-span-12 md:col-span-6',
    'required'     => true,
];
```

---

## ⚙️ Supported Parameters Reference

| Parameter | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `lov_table` | `string` | **(Required)** | The target database table name (e.g. `cms_users`, `chart_of_accounts`, `customers`). |
| `lov_value` | `string` | `'id'` | Column value saved into database. |
| `lov_label` | `string` | `'name'` | Column value displayed inside the read-only input box. |
| `lov_columns` | `string` | `lov_label` | Comma-separated column list to render as table headers in the modal. |
| `lov_where` | `string` | `null` | Optional SQL condition clause (e.g. `is_active=1 AND role='Client'`). |
| `lov_autofill` | `array` | `null` | Key-value pair mapping source LOV columns to form field names. |

---

## 🎨 Using LOV Component in Custom Vue Views

You can also call the `<LOVModal />` component inside any custom Vue view (`resources/js/Pages/...`):

```html
<script setup>
import { ref } from 'vue'
import InerminAppLayout from '@inermin/InerminAppLayout.vue'
import LOVModal from '@inermin/LOVModal.vue'

const showLov = ref(false)
const selectedCOA = ref(null)

const onSelectCOA = (row) => {
  selectedCOA.value = row
  console.log('Selected Account:', row)
}
</script>

<template>
  <InerminAppLayout>
    <div class="card p-6 space-y-4">
      <h2 class="font-bold text-base">Journal Entry Form</h2>

      <div class="flex items-center gap-2">
        <input
          type="text"
          readonly
          :value="selectedCOA ? `${selectedCOA.code} - ${selectedCOA.name}` : ''"
          placeholder="Click browse to select COA Account..."
          class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs"
          @click="showLov = true"
        />

        <button @click="showLov = true" class="px-4 py-2.5 rounded-2xl bg-amber-500 text-white font-bold text-xs">
          <i class="bi bi-search"></i> Browse
        </button>
      </div>

      <LOVModal
        :show="showLov"
        title="Lookup Chart of Accounts (COA)"
        table="chart_of_accounts"
        value-column="id"
        label-column="name"
        columns="code,name,type,category"
        where="is_active=1"
        @close="showLov = false"
        @select="onSelectCOA"
      />
    </div>
  </InerminAppLayout>
</template>
```
