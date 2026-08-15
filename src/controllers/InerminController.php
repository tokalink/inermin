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
            'filters' => $request->only(['q', 'orderby', 'limit']),
            'permissions' => [
                'can_add' => $this->button_add && Inermin::isCreate(),
                'can_edit' => $this->button_edit && Inermin::isUpdate(),
                'can_delete' => $this->button_delete && Inermin::isDelete(),
                'can_detail' => $this->button_detail && Inermin::isRead(),
                'can_export' => $this->button_export,
                'can_import' => $this->button_import,
                'can_bulk_action' => $this->button_bulk_action,
            ],
            'sub_module' => $this->sub_module,
            'addaction' => $this->addaction,
            'button_selected' => $this->button_selected,
            'index_statistic' => $this->index_statistic,
            'alerts' => $this->alert,
        ]);
    }

    public function getAdd()
    {
        return Inertia::render('Inermin/Form', [
            'page_title' => 'Add ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $this->form,
            'forms' => $this->form,
            'row' => null,
            'action_url' => '/' . Inermin::adminPath() . '/' . $this->table . '/add',
        ]);
    }

    public function postAddSave()
    {
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

        return redirect('/' . Inermin::adminPath() . '/' . $this->table)->with('success', 'Data saved successfully!');
    }

    public function getEdit($id)
    {
        $row = DB::table($this->table)->where($this->primary_key, $id)->first();
        if (!$row) return redirect('/' . Inermin::adminPath() . '/' . $this->table);

        return Inertia::render('Inermin/Form', [
            'page_title' => 'Edit ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $this->form,
            'forms' => $this->form,
            'row' => $row,
            'action_url' => '/' . Inermin::adminPath() . '/' . $this->table . '/edit/' . $id,
        ]);
    }

    public function postEditSave($id)
    {
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

        return redirect('/' . Inermin::adminPath() . '/' . $this->table)->with('success', 'Data updated successfully!');
    }

    public function getDetail($id)
    {
        $row = DB::table($this->table)->where($this->primary_key, $id)->first();

        return Inertia::render('Inermin/Detail', [
            'page_title' => 'Detail ' . ucwords(str_replace('_', ' ', $this->table)),
            'form_schema' => $this->form,
            'row' => $row,
            'back_url' => Inermin::mainpath(),
        ]);
    }

    public function getDelete($id)
    {
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
        $data = DB::table($this->table)->get();
        return response()->json($data);
    }
}

