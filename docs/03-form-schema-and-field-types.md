# 📝 Controller Form Schema & Field Types Reference

In **Inermin**, form creation is driven by defining the `$this->form` array inside your module's controller `cbInit()` method (matching the intuitive CRUDBooster developer workflow).

---

## 🛠️ Basic Controller Setup

```php
namespace App\Http\Controllers;

use Tokalink\Inermin\controllers\InerminController;

class AdminAbsenController extends InerminController
{
    public function cbInit()
    {
        $this->table = "absen";
        $this->primary_key = "id";
        $this->title_field = "id";

        // Form Schema Definition
        $this->form = [
            ['label' => 'TANGGAL', 'name' => 'tanggal', 'type' => 'date', 'required' => true, 'width' => 'col-span-12 md:col-span-6'],
            [
                'label'       => 'USER',
                'name'        => 'user_id',
                'type'        => 'lov',
                'lov_table'   => 'cms_users',
                'lov_value'   => 'id',
                'lov_label'   => 'name',
                'lov_columns' => 'id,name,email',
                'width'       => 'col-span-12 md:col-span-6',
                'required'    => true,
            ],
            ['label' => 'MESIN', 'name' => 'mesin', 'type' => 'text', 'width' => 'col-span-12 md:col-span-4'],
            ['label' => 'PIN', 'name' => 'pin', 'type' => 'text', 'width' => 'col-span-12 md:col-span-2'],
            ['label' => 'MASUK', 'name' => 'masuk', 'type' => 'datetime', 'width' => 'col-span-12 md:col-span-3'],
            ['label' => 'KELUAR', 'name' => 'keluar', 'type' => 'datetime', 'width' => 'col-span-12 md:col-span-3'],
            ['label' => 'JAM KERJA', 'name' => 'jam_kerja', 'type' => 'text', 'width' => 'col-span-12'],
        ];
    }
}
```

---

## 🎨 Supported Field Types Reference

| Field Type (`'type'`) | Component Rendered | Extra Parameters |
| :--- | :--- | :--- |
| `text` | Standard Single-line Text Input | `placeholder`, `default` |
| `email` | Email Input with validation | `placeholder` |
| `password` | Password Input | `help` |
| `number` | Numeric Input | `placeholder` |
| `money` / `currency` | Currency Input with `Rp` prefix | `placeholder` |
| `date` | Date Picker | `required` |
| `datetime` / `datetime-local` | Date & Time Picker | `required` |
| `time` | Time Picker | `required` |
| `textarea` | Multi-line Text Area | `rows` |
| `ckeditor` / `wysiwyg` / `html` / `richtext` / `tinymce` | Rich Text Editor with Formatting Toolbar | `height` |
| `select` / `select2` | Dropdown Select | `dataenum`, `datatable` |
| `radio` | Radio Options Group | `dataenum` |
| `checkbox` | Checkbox Options Group | `dataenum` |
| `upload` / `image` | File Upload Zone with Image Preview | `required` |
| `lov` | List of Values Modal Lookup | `lov_table`, `lov_value`, `lov_label`, `lov_columns`, `lov_autofill` |
| `color` / `colorpicker` | Color Picker Swatch | `default` |
| `header` / `heading` | Section Header Divider | `icon`, `help` |
| `hidden` | Hidden Form Input | `default` |

---

## 📐 Grid Layout Width Customization (`'width'`)

Form fields use a responsive **Tailwind 12-Column Grid**. Control field width by setting the `'width'` parameter:

- **100% Full Width (12 Columns)**: `'width' => 'col-span-12'`
- **50% Width (6 Columns)**: `'width' => 'col-span-12 md:col-span-6'`
- **33% Width (4 Columns)**: `'width' => 'col-span-12 md:col-span-4'`
- **25% Width (3 Columns)**: `'width' => 'col-span-12 md:col-span-3'`

---

## 📏 Editor Height Customization (`'height'`)

For rich text editors (`ckeditor`, `wysiwyg`, `html`), customize editor height:

```php
['label' => 'DESKRIPSI', 'name' => 'deskripsi', 'type' => 'html', 'height' => '300px']
```

---

## 🪝 Controller Lifecycle Hooks

You can intercept data before or after saving/updating records:

```php
public function hook_before_add(&$postdata)
{
    // Modify $postdata before database INSERT
    $postdata['created_by'] = Inermin::myId();
}

public function hook_after_add($id)
{
    // Executed after database INSERT
}

public function hook_before_edit(&$postdata, $id)
{
    // Modify $postdata before database UPDATE
}

public function hook_after_edit($id)
{
    // Executed after database UPDATE
}
```
