<?php

namespace Tokalink\Inermin\controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Rap2hpoutre\FastExcel\FastExcel;
use Tokalink\Inermin\helpers\Inermin;

class InerminController extends Controller
{
    public $data_inputan;
    public $columns_table;

    // Configuration Properties matching CRUDBooster
    public $table = '';
    public $primary_key = 'id';
    public $title_field = '';
    public $limit = 20;
    public $orderby = 'id,desc';

    public $button_add = true;
    public $button_edit = true;
    public $button_delete = true;
    public $button_detail = true;
    public $button_show = true;
    public $button_filter = true;
    public $button_export = true;
    public $button_import = true;
    public $button_bulk_action = true;
    public $button_action_style = 'button_dropdown'; // button_dropdown, button_icon

    public $col = [];
    public $form = [];
    public $sub_module = [];
    public $addaction = [];
    public $index_button = [];
    public $button_selected = [];
    public $index_statistic = [];
    public $alert = [];

    public function __construct()
    {
        if (method_exists($this, 'cbInit')) {
            $this->cbInit();
        }
    }

    /**
     * Process $this->form schema and automatically fetch datatable relationship options
     */
    protected function processFormSchema()
    {
        $forms = $this->form;
        foreach ($forms as &$f) {
            if (!empty($f['datatable']) && empty($f['dataenum'])) {
                $rawDatatable = explode(',', $f['datatable']);
                $table = trim($rawDatatable[0]);
                $column = trim($rawDatatable[1] ?? 'name');
                $valKey = $f['datatable_value'] ?? 'id';
                $where = $f['datatable_where'] ?? null;
                $format = $f['datatable_format'] ?? null;

                if (Schema::hasTable($table)) {
                    $query = DB::table($table);
                    if ($where) {
                        $query->whereRaw($where);
                    }

                    if ($format) {
                        $query->select($valKey . ' as value', DB::raw("CONCAT($format) as label"));
                    } else {
                        $query->select($valKey . ' as value', $column . ' as label');
                    }

                    $options = $query->get()->map(function ($r) {
                        return ['value' => $r->value, 'label' => $r->label];
                    })->toArray();

                    $f['dataenum'] = $options;
                }
            }
        }
        return $forms;
    }

    /**
     * Datagrid index page
     */
    public function getIndex(Request $request = null)
    {
        $request = $request ?: request();

        if (!Inermin::isView()) {
            return redirect('/' . config('inermin.ADMIN_PATH', 'administrator'))
                ->with('error', 'Access Denied!');
        }

        $query = DB::table($this->table);

        // Process automatic Joins for col datatable definitions
        $selects = [$this->table . '.*'];
        $joinedFields = [];
        foreach ($this->col as $col) {
            if (!empty($col['datatable'])) {
                $rawDatatable = explode(',', $col['datatable']);
                $relTable = trim($rawDatatable[0]);
                $relColumn = trim($rawDatatable[1] ?? 'name');
                $relKey = $col['datatable_value'] ?? 'id';
                $fieldName = $col['name'] ?? null;

                if ($fieldName && !isset($joinedFields[$fieldName])) {
                    $joinedFields[$fieldName] = true;
                    $aliasTable = 'rel_' . $fieldName;
                    $aliasName = $fieldName . '_label';

                    if (Schema::hasTable($relTable)) {
                        $query->leftJoin($relTable . ' as ' . $aliasTable, $aliasTable . '.' . $relKey, '=', $this->table . '.' . $fieldName);
                        $selects[] = $aliasTable . '.' . $relColumn . ' as ' . $aliasName;
                    }
                }
            }
        }
        $query->select($selects);

        // Search Filter
        if ($q = $request->input('q')) {
            $query->where(function ($w) use ($q) {
                foreach ($this->col as $idx => $col) {
                    $field = $col['name'] ?? null;
                    if ($field) {
                        if ($idx == 0) {
                            $w->where($this->table . '.' . $field, 'like', "%{$q}%");
                        } else {
                            $w->orWhere($this->table . '.' . $field, 'like', "%{$q}%");
                        }
                    }
                }
            });
        }

        // Sorting
        if ($orderby = $request->input('orderby', $this->orderby)) {
            $orderParts = explode(',', $orderby);
            if (count($orderParts) == 2) {
                $query->orderBy($this->table . '.' . $orderParts[0], $orderParts[1]);
            }
        } else {
            $query->orderBy($this->table . '.' . $this->primary_key, 'desc');
        }

        $limit = $request->input('limit', $this->limit);
        $result = $query->paginate($limit)->withQueryString();

        // Process callbacks & column formatting
        $items = $result->items();
        foreach ($items as &$row) {
            foreach ($this->col as $idx => $col) {
                $fieldName = $col['name'] ?? null;
                $aliasName = $fieldName . '_label';
                if (isset($row->{$aliasName}) && $row->{$aliasName} !== null) {
                    $row->{$fieldName} = $row->{$aliasName};
                }

                // Execute PHP Callback Closure if defined in $col
                if (isset($col['callback']) && is_callable($col['callback'])) {
                    $row->{$fieldName} = call_user_func($col['callback'], $row);
                }

                if ($fieldName && isset($row->{$fieldName})) {
                    $val = $row->{$fieldName};
                    $this->hook_row_index($idx, $val);
                    $row->{$fieldName} = $val;
                }
            }
        }

        // Clean columns array for Inertia JSON serialization (strip Closures)
        $cleanColumns = array_map(function ($col) {
            if (isset($col['callback'])) {
                unset($col['callback']);
            }
            return $col;
        }, $this->col);

        return Inertia::render('Inermin/Datagrid', [
            'page_title' => ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'columns' => $cleanColumns,
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

        $processedForm = $this->processFormSchema();

        return Inertia::render('Inermin/Form', [
            'page_title' => 'Add ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $processedForm,
            'forms' => $processedForm,
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
            if ($name) {
                if ($request->hasFile($name)) {
                    $file = $request->file($name);
                    $path = $file->store('uploads/' . date('Y-m'), 'public');
                    $data[$name] = 'storage/' . $path;
                } elseif ($request->has($name)) {
                    $data[$name] = $request->input($name);
                }
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

        $processedForm = $this->processFormSchema();

        return Inertia::render('Inermin/Form', [
            'page_title' => 'Edit ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $processedForm,
            'forms' => $processedForm,
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

        $request = request();
        $data = [];
        foreach ($this->form as $f) {
            $name = $f['name'] ?? null;
            if ($name) {
                if ($request->hasFile($name)) {
                    $file = $request->file($name);
                    $path = $file->store('uploads/' . date('Y-m'), 'public');
                    $data[$name] = 'storage/' . $path;
                } elseif ($request->has($name)) {
                    $data[$name] = $request->input($name);
                }
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

        $query = DB::table($this->table)->where($this->table . '.' . $this->primary_key, $id);

        // Auto-join relational tables defined in form or col (prioritizing form definition)
        $selects = [$this->table . '.*'];
        $allSchemas = array_merge($this->form, $this->col);
        $joinedFields = [];

        foreach ($allSchemas as $item) {
            if (!empty($item['datatable'])) {
                $rawDatatable = explode(',', $item['datatable']);
                $relTable = trim($rawDatatable[0]);
                $relColumn = trim($rawDatatable[1] ?? 'name');
                $relKey = $item['datatable_value'] ?? 'id';
                $fieldName = $item['name'] ?? null;

                if ($fieldName && !isset($joinedFields[$fieldName])) {
                    $joinedFields[$fieldName] = true;
                    $aliasTable = 'rel_' . $fieldName;
                    $aliasName = $fieldName . '_label';

                    if (Schema::hasTable($relTable)) {
                        $query->leftJoin($relTable . ' as ' . $aliasTable, $aliasTable . '.' . $relKey, '=', $this->table . '.' . $fieldName);
                        $selects[] = $aliasTable . '.' . $relColumn . ' as ' . $aliasName;
                    }
                }
            }
        }

        $row = $query->select(array_unique($selects))->first();
        if (!$row) return redirect(Inermin::mainpath())->with('error', 'Record not found!');

        $processedForm = $this->processFormSchema();

        // Replace foreign key IDs with joined labels or dataenum labels on $row
        foreach ($processedForm as $f) {
            $fieldName = $f['name'] ?? null;
            if (!$fieldName) continue;

            $aliasName = $fieldName . '_label';

            if (isset($row->{$aliasName}) && $row->{$aliasName} !== null && $row->{$aliasName} !== '') {
                $row->{$fieldName} = $row->{$aliasName};
            } elseif (!empty($f['dataenum']) && isset($row->{$fieldName})) {
                $rawVal = $row->{$fieldName};
                foreach ($f['dataenum'] as $opt) {
                    if (is_array($opt) && isset($opt['value'], $opt['label']) && (string)$opt['value'] === (string)$rawVal) {
                        $row->{$fieldName} = $opt['label'];
                        break;
                    } elseif (is_object($opt) && isset($opt->value, $opt->label) && (string)$opt->value === (string)$rawVal) {
                        $row->{$fieldName} = $opt->label;
                        break;
                    }
                }
            }
        }

        return Inertia::render('Inermin/Detail', [
            'page_title' => 'Detail ' . ucwords(str_replace('_', ' ', $this->table)),
            'table_name' => $this->table,
            'primary_key' => $this->primary_key,
            'form_schema' => $processedForm,
            'forms' => $processedForm,
            'row' => $row,
            'is_detail' => true,
            'back_url' => Inermin::mainpath(),
        ]);
    }

    public function getDelete($id = null)
    {
        $id = $id ?: request('id');
        if (!$this->button_delete || !Inermin::isDelete()) {
            return redirect(Inermin::mainpath())->with('error', 'Access Denied!');
        }

        $this->hook_before_delete($id);
        DB::table($this->table)->where($this->primary_key, $id)->delete();
        $this->hook_after_delete($id);

        Inermin::insertLog('Deleted record #' . $id . ' in table ' . $this->table);

        return redirect(Inermin::mainpath())->with('success', 'Data deleted successfully!');
    }

    /**
     * Advanced Data Export (XLSX, PDF via Dompdf, CSV) with Column Selection
     */
    public function getExportData()
    {
        return $this->postExportData();
    }

    public function postExportData()
    {
        if (!$this->button_export) {
            return redirect(Inermin::mainpath())->with('error', 'Export disabled!');
        }

        $request = request();
        $fileformat = strtolower($request->input('fileformat', 'xlsx'));
        $paperSize = strtolower($request->input('paper_size', 'a4'));
        $pageOrientation = strtolower($request->input('page_orientation', 'landscape'));
        $customFilename = trim($request->input('filename', ''));
        $selectedColumns = $request->input('columns', []);

        if (is_string($selectedColumns)) {
            $selectedColumns = array_filter(explode(',', $selectedColumns));
        }

        // Determine columns to export
        $exportCols = [];
        if (!empty($selectedColumns)) {
            foreach ($this->col as $c) {
                if (in_array($c['name'], $selectedColumns)) {
                    $exportCols[] = $c;
                }
            }
        }
        if (empty($exportCols)) {
            $exportCols = $this->col;
        }

        // Build Data Query with Joins
        $query = DB::table($this->table);
        $selects = [$this->table . '.*'];
        foreach ($exportCols as $col) {
            if (!empty($col['datatable'])) {
                $rawDatatable = explode(',', $col['datatable']);
                $relTable = trim($rawDatatable[0]);
                $relColumn = trim($rawDatatable[1] ?? 'name');
                $relKey = $col['datatable_value'] ?? 'id';
                $fieldName = $col['name'];
                $aliasName = $fieldName . '_label';

                if (Schema::hasTable($relTable)) {
                    $query->leftJoin($relTable, $relTable . '.' . $relKey, '=', $this->table . '.' . $fieldName);
                    $selects[] = $relTable . '.' . $relColumn . ' as ' . $aliasName;
                }
            }
        }
        $query->select($selects);

        if ($q = $request->input('q')) {
            $query->where(function ($w) use ($q, $exportCols) {
                foreach ($exportCols as $idx => $col) {
                    $field = $col['name'] ?? null;
                    if ($field) {
                        if ($idx == 0) {
                            $w->where($this->table . '.' . $field, 'like', "%{$q}%");
                        } else {
                            $w->orWhere($this->table . '.' . $field, 'like', "%{$q}%");
                        }
                    }
                }
            });
        }

        $data = $query->orderBy($this->primary_key, 'desc')->get();

        // Process callbacks for raw data
        foreach ($data as &$row) {
            foreach ($exportCols as $col) {
                $fieldName = $col['name'] ?? null;
                $aliasName = $fieldName . '_label';
                if (isset($row->{$aliasName}) && $row->{$aliasName} !== null) {
                    $row->{$fieldName} = $row->{$aliasName};
                }
                if (isset($col['callback']) && is_callable($col['callback'])) {
                    $row->{$fieldName} = call_user_func($col['callback'], $row);
                }
            }
        }

        $filename = $customFilename ?: ($this->table . '_export_' . date('Y-m-d_H-i-s'));
        $filename = str_replace(['/', '\\', ' '], '_', $filename);

        // PDF Export via Dompdf
        if ($fileformat === 'pdf') {
            $title = ucwords(str_replace('_', ' ', $this->table)) . ' Report';
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>' . htmlspecialchars($title) . '</title>
                <style>
                    body { font-family: sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 0; }
                    .header { margin-bottom: 15px; text-align: center; border-b: 2px solid #0284c7; padding-bottom: 8px; }
                    .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #0f172a; }
                    .header p { margin: 4px 0 0 0; color: #64748b; font-size: 9px; font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th { background-color: #0f172a; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 9px; font-weight: bold; text-transform: uppercase; border: 1px solid #334155; }
                    td { padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 8.5px; word-wrap: break-word; vertical-align: top; }
                    tr:nth-child(even) { background-color: #f8fafc; }
                    .footer { position: fixed; bottom: -10px; left: 0; right: 0; height: 20px; font-size: 8px; color: #94a3b8; border-t: 1px solid #e2e8f0; padding-top: 4px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>' . htmlspecialchars($title) . '</h2>
                    <p>Export Date: ' . date('d M Y, H:i') . ' | Total Records: ' . count($data) . '</p>
                </div>
                <table>
                    <thead>
                        <tr>';
            foreach ($exportCols as $c) {
                $html .= '<th>' . htmlspecialchars($c['label'] ?? $c['name']) . '</th>';
            }
            $html .= '</tr>
                    </thead>
                    <tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($exportCols as $c) {
                    $val = $row->{$c['name']} ?? '';
                    $cleanVal = is_string($val) ? trim(strip_tags($val)) : $val;
                    $html .= '<td>' . htmlspecialchars((string)$cleanVal) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>
                </table>
                <div class="footer">
                    <span>Generated by Inermin Admin System &bull; Page 1</span>
                </div>
            </body>
            </html>';

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper($paperSize, $pageOrientation);
            return $pdf->download($filename . '.pdf');
        }

        // Excel / CSV Export via FastExcel
        $ext = $fileformat === 'csv' ? '.csv' : '.xlsx';
        return (new FastExcel($data))->download($filename . $ext, function ($row) use ($exportCols) {
            $formatted = [];
            foreach ($exportCols as $c) {
                $name = $c['name'];
                $label = $c['label'] ?? $name;
                $val = $row->{$name} ?? '';
                $formatted[$label] = is_string($val) ? trim(strip_tags($val)) : $val;
            }
            return $formatted;
        });
    }

    /**
     * FastExcel Data Import
     */
    public function postImportData(Request $request)
    {
        if (!$this->button_import) {
            return redirect(Inermin::mainpath())->with('error', 'Import disabled!');
        }

        $file = $request->file('userfile') ?: $request->file('file');
        if (!$file) {
            return redirect(Inermin::mainpath())->with('error', 'No import file uploaded!');
        }

        try {
            $count = 0;
            (new FastExcel)->import($file->getRealPath(), function ($line) use (&$count) {
                $insert = [];
                foreach ($this->form as $f) {
                    $name = $f['name'] ?? null;
                    $label = $f['label'] ?? $name;
                    if ($name && isset($line[$label])) {
                        $insert[$name] = $line[$label];
                    } elseif ($name && isset($line[$name])) {
                        $insert[$name] = $line[$name];
                    }
                }
                if (!empty($insert)) {
                    if (Schema::hasColumn($this->table, 'created_at')) {
                        $insert['created_at'] = now();
                    }
                    DB::table($this->table)->insert($insert);
                    $count++;
                }
            });

            return redirect(Inermin::mainpath())->with('success', "Imported {$count} records successfully!");
        } catch (\Exception $e) {
            return redirect(Inermin::mainpath())->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Selected Row Actions (Delete, etc)
     */
    public function postActionSelected(Request $request)
    {
        $id = $request->input('id', []);
        $buttonName = $request->input('button_name');

        if (empty($id)) {
            return redirect(Inermin::mainpath())->with('error', 'No records selected!');
        }

        if ($buttonName === 'delete' && $this->button_delete) {
            DB::table($this->table)->whereIn($this->primary_key, $id)->delete();
            Inermin::insertLog('Bulk deleted ' . count($id) . ' records in ' . $this->table);
            return redirect(Inermin::mainpath())->with('success', 'Selected records deleted successfully!');
        }

        return redirect(Inermin::mainpath())->with('success', 'Action completed successfully!');
    }

    // Default empty hooks
    public function hook_before_add(&$arr) {}
    public function hook_after_add($id) {}
    public function hook_before_edit(&$arr, $id) {}
    public function hook_after_edit($id) {}
    public function hook_before_delete($id) {}
    public function hook_after_delete($id) {}
    public function hook_row_index($column_index, &$column_value) {}
}
