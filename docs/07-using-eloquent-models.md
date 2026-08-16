# 07. Guide: Eloquent Models & Hybrid Data Architecture in Inermin

## Overview

CRUDBooster secara tradisional menggunakan **Direct Query Builder (`DB::table('nama_tabel')`)** karena alasan efisiensi performa dan kecepatan pembuatan modul (*zero model boilerplate*).

Namun di **Inermin**, Anda diberikan fleksibilitas penuh: Anda bisa memilih untuk **tanpa Model (Query Builder)** atau **menggunakan Eloquent Model** dengan mudah!

---

## 1. Mengapa Inermin Menggunakan Query Builder secara Default?

1. ⚡ **Kecepatan & Performa**: Query Builder `DB::table()` tidak memuat *hydration overhead* dari ratusan instance objek Eloquent Model saat merender tabel ribuan baris data.
2. 🚀 **Zero Boilerplate**: Anda dapat membuat Modul CRUD atau Custom View secara instant dari Module Generator tanpa perlu membuat file `App\Models\NamaModel.php` terlebih dahulu.
3. 🛠️ **Dynamic Schema Friendly**: Memudahkan manipulasi kolom dan skema tabel secara dinamis (*on-the-fly auto-upgrade schema*).

---

## 2. Cara Menggunakan Eloquent Model di Inermin Controller

Mulai versi terbaru **Inermin**, Anda dapat mendeklarasikan properti `$this->model` di dalam `cbInit()` controller Anda.

### Contoh Controller dengan Eloquent Model:

```php
<?php

namespace App\Http\Controllers\Mutasi;

use Tokalink\Inermin\controllers\InerminController;
use App\Models\MutasiBank; // Eloquent Model Anda

class AdminMutasiController extends InerminController
{
    public function cbInit()
    {
        // 1. Cukup definisikan $this->model
        $this->model = MutasiBank::class;
      
        // Inermin akan otomatis mendeteksi $this->table & $this->primary_key dari Model!
        $this->title_field = 'bank_name';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'BANK NAME', 'name' => 'bank_name'],
            ['label' => 'SALDO', 'name' => 'balance', 'callback' => function ($row) {
                // $row masih berupa stdClass/Model instance yang bisa mengakses Accessor Model!
                return 'Rp ' . number_format($row->balance, 0, ',', '.');
            }],
        ];

        $this->form = [
            ['label' => 'Bank Name', 'name' => 'bank_name', 'type' => 'text', 'required' => true],
            ['label' => 'Account Number', 'name' => 'account_number', 'type' => 'text', 'required' => true],
            ['label' => 'Current Balance', 'name' => 'balance', 'type' => 'money', 'required' => true],
        ];
    }

    // Callback Hook sebelum Simpan (Bisa panggil method bisnis Eloquent Model!)
    public function hook_before_add(&$postdata)
    {
        // Panggil static method/business logic dari Eloquent Model
        $postdata['account_number'] = MutasiBank::sanitizeAccountNumber($postdata['account_number']);
    }
}
```

---

## 3. Keuntungan Menggunakan Eloquent Model di Inermin

Jika Anda mendaftarkan `$this->model` di Controller Inermin, Anda mendapatkan keuntungan penuh dari fitur Laravel Eloquent:

### A. Relationships (Relasi Antar Tabel)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsTenant extends Model
{
    protected $table = 'cms_tenants';

    public function branches()
    {
        return $this->hasMany(CmsBranch::class, 'tenant_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(CmsTenantSubscription::class, 'tenant_id');
    }
}
```

### B. Eloquent Accessors & Mutators (Format Data Otomatis)

```php
class CmsApp extends Model
{
    // Accessor: $app->formatted_monthly_price
    public function getFormattedMonthlyPriceAttribute()
    {
        return 'Rp ' . number_format($this->price_monthly, 0, ',', '.');
    }
}
```

### C. Local Query Scopes (Reusability Filter)

```php
class CmsApp extends Model
{
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeSubscription($query)
    {
        return $query->where('billing_type', 'subscription');
    }
}
```

*Penggunaan di Controller Custom View / API:*

```php
$activeApps = CmsApp::active()->subscription()->get();
```

### D. Eloquent Model Observers & Events

Anda dapat membuat Observer (`php artisan make:observer TenantObserver`) untuk menangani event `created`, `updated`, atau `deleted`:

```php
namespace App\Observers;

use App\Models\CmsTenant;

class TenantObserver
{
    public function created(CmsTenant $tenant)
    {
        // Auto-seed default branches & roles saat Tenant baru dibuat
    }
}
```

---

## 4. Kapan Harus Pakai Query Builder vs Eloquent Model?

| Fitur / Skenario                                  | Raw Query Builder (`$this->table`) | Eloquent Model (`$this->model`) |                                     |
| :------------------------------------------------ | :----------------------------------------------------------------------- | :---------------------------------- |
| **Sederhana & Cepat (Standard CRUD)**       | ✅ Sangat Direkomendasikan                                               | ⚡ Opsional                         |
| **Kecepatan Query Ribuan Row Data**         | 🚀 Maksimal (Tanpa Hydration)                                            | 🐢 Sedikit Lebih Lambat             |
| **Business Logic Kompleks (Observed)**      | ⚠️ Manual via Controller Hooks                                         | ✅ Sangat Cocok via Model Observers |
| **Relasi Banyak Tabel (HasMany/BelongsTo)** | ⚠️ Manual via Query Join                                               | ✅ Sangat Mudah via Model Relation  |

---

## Kesimpulan

Inermin mendukung **Arsitektur Hibrida**:

* Untuk modul CRUD standar yang simpel ➔ Gunakan `$this->table = 'nama_tabel'` (Tanpa Model).
* Untuk modul enterprise yang kaya *business logic* ➔ Cukup pasang `$this->model = \App\Models\NamaModel::class`. Kedua cara bekerja 100% harmonis dan didukung penuh di Inermin!
