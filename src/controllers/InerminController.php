<?php

namespace Tokalink\Inermin\controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Tokalink\Inermin\helpers\Inermin;

class InerminController extends Controller
{
    public function cbInit()
    {
    }

    public $data_inputan = [];
    public $columns = [];
    public $form = [];
    public $col = [];
    public $title_field = 'id';
    public $limit = 20;
    public $orderby = 'id,desc';
    public $table;
    public $primary_key = 'id';

    public $button_add = true;
    public $button_edit = true;
    public $button_delete = true;
    public $button_detail = true;
    public $button_show = true;
    public $button_filter = true;
    public $button_export = true;
    public $button_import = true;
    public $button_bulk_action = true;
    public $button_action_style = 'button_icon';

    public $sub_module = [];
    public $addaction = [];
    public $button_selected = [];
    public $alert = [];
    public $index_button = [];
    public $table_row_color = [];
    public $index_statistic = [];
    public $script_js = null;
    public $pre_index_html = null;
    public $post_index_html = null;
    public $style_css = null;
    public $load_js = [];
    public $load_css = [];

    public function __construct()
    {
        $this->cbInit();
    }
    // Hooks
    public function hook_query_index(&$query) {}
    public function hook_row_index($column_index, &$column_value) {}
    public function hook_before_add(&$postdata) {}
    public function hook_after_add($id) {}
    public function hook_before_edit(&$postdata, $id) {}
    public function hook_after_edit($id) {}
    public function hook_before_delete($id) {}
    public function hook_after_delete($id) {}
    public function actionButtonSelected($id_selected, $button_name) {}

    public function getIndex()
    {
        $request = request();
        $query = DB::table($this->table);

        // Apply Query Index Hook
        $this->hook_query_index($query);

        // Search
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                foreach ($this->col as $col) {
                    if (isset($col['name'])) {
                        $q->orWhere($this->table . '.' . $col['name'], 'like', "%{$search}%");
                    }
                }
            });
        }

        // Filter Column (CRUDBooster style advanced filter)
        if ($filter_column = $request->input('filter_column')) {
            $query->where(function ($w) use ($filter_column) {
                foreach ($filter_column as $colName => $fc) {
                    $value = $fc['value'] ?? null;
                    $type = $fc['type'] ?? 'like';

                    if ($type === 'empty') {
                        $w->whereNull($this->table . '.' . $colName)->orWhere($this->table . '.' . $colName, '');
                        continue;
                    }

                    if ($value === null || $value === '' || $type === '') {
                        continue;
                    }

                    switch ($type) {
                        case 'like':
                        case 'not like':
                            $w->where($this->table . '.' . $colName, $type, '%' . $value . '%');
                            break;
                        case 'in':
                        case 'not in':
                            $vals = is_array($value) ? $value : array_map('trim', explode(',', $value));
                            if ($type === 'in') {
                                $w->whereIn($this->table . '.' . $colName, $vals);
                            } else {
                                $w->whereNotIn($this->table . '.' . $colName, $vals);
                            }
                            break;
                        default:
                            $w->where($this->table . '.' . $colName, $type, $value);
                            break;
                    }
                }
            });
        }

        // Sorting
        if ($orderby = $request->input('orderby', $this->orderby)) {
            $orderParts = explode(',', $orderby);
            if (count($orderParts) == 2) {
                $query->orderBy($orderParts[0], $orderParts[1]);
            }
        } else {
            $query->orderBy($this->primary_key, 'desc');
        }

        $limit = $request->input('limit', $this->limit);
        $result = $query->paginate($limit)->withQueryString();

        // Process row index hooks & column formatting
        $items = $result->items();
        foreach ($items as &$row) {
            foreach ($this->col as $idx => $col) {
                $fieldName = $col['name'] ?? null;
                if ($fieldName && isset($row->{$fieldName})) {
                    $val = $row->{$fieldName};
                    $this->hook_row_index($idx, $val);
                    $row->{$fieldName} = $val;
                }
            }
        }

        return Inertia::render('Inermin/Datagrid', [
            'page_title' => ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'columns' => $this->col,
            'data' => $result,
            'filters' => $request->only(['q', 'orderby', 'limit', 'filter_column']),
            'permissions' => [
                'can_add' => $this->button_add && Inermin::isCreate(),
                'can_edit' => $this->button_edit && Inermin::isUpdate(),
                'can_delete' => $this->button_delete && Inermin::isDelete(),
                'can_detail' => $this->button_detail && Inermin::isRead(),
                'can_export' => $this->button_export,
                'can_import' => $this->button_import,
                'can_bulk_action' => $this->button_bulk_action,
                'can_filter' => $this->button_filter,
                'can_show' => $this->button_show,
                'button_action_style' => $this->button_action_style,
            ],
            'sub_module' => $this->sub_module,
            'addaction' => $this->addaction,
            'index_button' => $this->index_button,
            'button_selected' => $this->button_selected,
            'index_statistic' => $this->index_statistic,
            'alerts' => $this->alert,
        ]);
    }

    public function getAdd()
    {
        if (!$this->button_add || !Inermin::isCreate()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }

        return Inertia::render('Inermin/Form', [
            'page_title' => 'Add ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $this->form,
            'forms' => $this->form,
            'row' => null,
            'action_url' => Inermin::mainpath('add'),
        ]);
    }

    public function postAddSave()
    {
        if (!$this->button_add || !Inermin::isCreate()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }

        $request = request();
        $data = [];
        foreach ($this->form as $f) {
            $name = $f['name'] ?? null;
            if ($name && $request->has($name)) {
                $data[$name] = $request->input($name);
            }
        }

        $this->hook_before_add($data);

        if (Schema::hasColumn($this->table, 'created_at')) {
            $data['created_at'] = now();
        }

        $id = DB::table($this->table)->insertGetId($data);
        $this->hook_after_add($id);

        Inermin::insertLog('Added new record #' . $id . ' in table ' . $this->table);

        return redirect(Inermin::mainpath())->with('success', 'Data saved successfully!');
    }

    public function getEdit($id = null)
    {
        $id = $id ?: request('id');
        if (!$this->button_edit || !Inermin::isUpdate()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }
        if (!$id) {
            return redirect(Inermin::mainpath());
        }

        $row = DB::table($this->table)->where($this->primary_key, $id)->first();
        if (!$row) return redirect(Inermin::mainpath())->with('error', 'Record not found!');

        return Inertia::render('Inermin/Form', [
            'page_title' => 'Edit ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $this->form,
            'forms' => $this->form,
            'row' => $row,
            'action_url' => Inermin::mainpath('edit/' . $id),
        ]);
    }

    public function postEditSave($id = null)
    {
        $id = $id ?: request('id');
        if (!$this->button_edit || !Inermin::isUpdate()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }
        if (!$id) {
            return redirect(Inermin::mainpath());
        }

        $request = request();
        $data = [];
        foreach ($this->form as $f) {
            $name = $f['name'] ?? null;
            if ($name && $request->has($name)) {
                $data[$name] = $request->input($name);
            }
        }

        $this->hook_before_edit($data, $id);

        if (Schema::hasColumn($this->table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table($this->table)->where($this->primary_key, $id)->update($data);
        $this->hook_after_edit($id);

        Inermin::insertLog('Updated record #' . $id . ' in table ' . $this->table);

        return redirect(Inermin::mainpath())->with('success', 'Data updated successfully!');
    }

    public function getDetail($id = null)
    {
        $id = $id ?: request('id');
        if (!$this->button_detail || !Inermin::isRead()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }
        if (!$id) {
            return redirect(Inermin::mainpath());
        }

        $row = DB::table($this->table)->where($this->primary_key, $id)->first();
        if (!$row) return redirect(Inermin::mainpath())->with('error', 'Record not found!');

        return Inertia::render('Inermin/Detail', [
            'page_title' => 'Detail ' . ucwords(str_replace('_', ' ', $this->table)),
            'form_schema' => $this->form,
            'row' => $row,
            'back_url' => Inermin::mainpath(),
        ]);
    }

    public function getDelete($id = null)
    {
        $id = $id ?: request('id');
        if (!$this->button_delete || !Inermin::isDelete()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }
        if (!$id) {
            return redirect(Inermin::mainpath());
        }

        $this->hook_before_delete($id);

        DB::table($this->table)->where($this->primary_key, $id)->delete();

        $this->hook_after_delete($id);
        Inermin::insertLog("Deleted data at " . $this->table . " ID " . $id);

        return redirect()->back()->with('success', 'Data deleted successfully');
    }

    public function postActionSelected()
    {
        $request = request();
        $id_selected = $request->input('id_selected', []);
        $button_name = $request->input('button_name');

        if ($button_name === 'delete') {
            foreach ($id_selected as $id) {
                $this->hook_before_delete($id);
                DB::table($this->table)->where($this->primary_key, $id)->delete();
                $this->hook_after_delete($id);
            }
            Inermin::insertLog("Bulk deleted data at " . $this->table);
            return redirect()->back()->with('success', 'Selected data deleted successfully');
        } else {
            $this->actionButtonSelected($id_selected, $button_name);
            return redirect()->back()->with('success', 'Action executed successfully');
        }
    }

    public function getExportData()
    {
        if (!$this->button_export) {
            return redirect(Inermin::mainpath())->with('error', 'Export access denied!');
        }

        $filename = $this->table . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        $query = DB::table($this->table);

        $request = request();
        if ($request->has('filter_column')) {
            $filters = $request->input('filter_column');
            if (is_array($filters)) {
                foreach ($filters as $key => $filter) {
                    $type = $filter['type'] ?? 'like';
                    $val = $filter['value'] ?? null;
                    if ($val !== null && $val !== '') {
                        if ($type === 'like') {
                            $query->where($key, 'like', '%' . $val . '%');
                        } elseif ($type === '=') {
                            $query->where($key, '=', $val);
                        }
                    }
                }
            }
        }

        $data = $query->get();

        if (class_exists(\Rap2hpoutre\FastExcel\FastExcel::class)) {
            return (new \Rap2hpoutre\FastExcel\FastExcel($data))->download($filename);
        }

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . str_replace('.xlsx', '.csv', $filename),
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if (count($data) > 0) {
                fputcsv($file, array_keys((array) $data[0]));
                foreach ($data as $row) {
                    fputcsv($file, (array) $row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function postImportData()
    {
        if (!$this->button_import) {
            return redirect(Inermin::mainpath())->with('error', 'Import access denied!');
        }

        $request = request();
        if (!$request->hasFile('userfile')) {
            return redirect()->back()->with('error', 'Please choose a file to import.');
        }

        $file = $request->file('userfile');
        $path = $file->getRealPath();

        if (class_exists(\Rap2hpoutre\FastExcel\FastExcel::class)) {
            $collections = (new \Rap2hpoutre\FastExcel\FastExcel())->import($path);
            $inserted = 0;
            foreach ($collections as $row) {
                $insertData = [];
                foreach ($row as $k => $v) {
                    if ($k && Schema::hasColumn($this->table, $k) && $k !== $this->primary_key) {
                        $insertData[$k] = $v;
                    }
                }
                if (!empty($insertData)) {
                    if (Schema::hasColumn($this->table, 'created_at')) {
                        $insertData['created_at'] = now();
                    }
                    DB::table($this->table)->insert($insertData);
                    $inserted++;
                }
            }
            Inermin::insertLog("Imported $inserted rows into " . $this->table);
            return redirect()->back()->with('success', "Imported $inserted rows successfully!");
        }

        return redirect()->back()->with('error', 'FastExcel package not installed.');
    }
}

