<?php

namespace Tokalink\Inermin\controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InerminApiEngineController extends Controller
{
    public function handleApi(Request $request, $permalink, $id = null)
    {
        try {
            $api = DB::table('cms_apicustom')->where('permalink', $permalink)->first();

            if (!$api) {
                return response()->json([
                    'api_status' => 0,
                    'api_message' => "API Endpoint '/api/{$permalink}' not found!",
                ], 404);
            }

            // Method validation
            $expectedMethod = strtoupper($api->method_type ?: 'GET');
            $currentMethod = strtoupper($request->method());

            if ($expectedMethod !== $currentMethod && $expectedMethod !== 'POST') {
                return response()->json([
                    'api_status' => 0,
                    'api_message' => "Method {$currentMethod} not allowed. Expected {$expectedMethod}.",
                ], 405);
            }

            // Secret key authentication check
            if (!empty($api->secret_key)) {
                $providedKey = $request->header('X-Authorization-Token') 
                    ?: $request->header('Authorization') 
                    ?: $request->input('secret_key');

                if ($providedKey !== $api->secret_key) {
                    return response()->json([
                        'api_status' => 0,
                        'api_message' => 'Unauthorized access! Invalid or missing API secret key.',
                    ], 401);
                }
            }

            $table = $api->tabel;
            if (!$table || !Schema::hasTable($table)) {
                return response()->json([
                    'api_status' => 0,
                    'api_message' => "Target database table '{$table}' does not exist!",
                ], 400);
            }

            $action = strtolower($api->aksi ?: 'list');

            switch ($action) {
                case 'detail':
                    $targetId = $id ?: $request->input('id');
                    if (!$targetId) {
                        $sampleRow = DB::table($table)->first();
                        if ($sampleRow) {
                            return response()->json([
                                'api_status' => 1,
                                'api_message' => 'success (showing first record as sample because no id parameter was passed)',
                                'data' => $sampleRow,
                            ]);
                        }
                        return response()->json([
                            'api_status' => 0,
                            'api_message' => 'Parameter id is required! Usage: /api/' . $permalink . '?id=1 or /api/' . $permalink . '/1',
                        ], 400);
                    }

                    $row = DB::table($table)->where('id', $targetId)->first();
                    return response()->json([
                        'api_status' => $row ? 1 : 0,
                        'api_message' => $row ? 'success' : "Record with id '{$targetId}' not found in '{$table}'",
                        'data' => $row,
                    ]);

                case 'add':
                case 'create':
                    $cols = Schema::getColumnListing($table);
                    $data = [];
                    foreach ($cols as $col) {
                        if ($col === 'id') continue;
                        if ($request->has($col)) {
                            $data[$col] = $request->input($col);
                        }
                    }
                    if (Schema::hasColumn($table, 'created_at') && !isset($data['created_at'])) {
                        $data['created_at'] = now();
                    }
                    $id = DB::table($table)->insertGetId($data);
                    return response()->json([
                        'api_status' => 1,
                        'api_message' => 'Record added successfully!',
                        'id' => $id,
                    ]);

                case 'edit':
                case 'update':
                    $id = $request->input('id');
                    if (!$id) {
                        return response()->json([
                            'api_status' => 0,
                            'api_message' => 'Parameter id is required!',
                        ], 400);
                    }
                    $cols = Schema::getColumnListing($table);
                    $data = [];
                    foreach ($cols as $col) {
                        if ($col === 'id') continue;
                        if ($request->has($col)) {
                            $data[$col] = $request->input($col);
                        }
                    }
                    if (Schema::hasColumn($table, 'updated_at') && !isset($data['updated_at'])) {
                        $data['updated_at'] = now();
                    }
                    DB::table($table)->where('id', $id)->update($data);
                    return response()->json([
                        'api_status' => 1,
                        'api_message' => 'Record updated successfully!',
                        'id' => $id,
                    ]);

                case 'delete':
                    $id = $request->input('id');
                    if (!$id) {
                        return response()->json([
                            'api_status' => 0,
                            'api_message' => 'Parameter id is required!',
                        ], 400);
                    }
                    DB::table($table)->where('id', $id)->delete();
                    return response()->json([
                        'api_status' => 1,
                        'api_message' => 'Record deleted successfully!',
                    ]);

                case 'list':
                default:
                    $query = DB::table($table);

                    if (!empty($api->sql_where)) {
                        $query->whereRaw($api->sql_where);
                    }

                    if ($q = $request->input('q')) {
                        $cols = Schema::getColumnListing($table);
                        $query->where(function ($w) use ($q, $cols, $table) {
                            foreach ($cols as $idx => $c) {
                                if ($idx == 0) {
                                    $w->where($table . '.' . $c, 'like', "%{$q}%");
                                } else {
                                    $w->orWhere($table . '.' . $c, 'like', "%{$q}%");
                                }
                            }
                        });
                    }

                    if (!empty($api->sql_orderby)) {
                        $parts = explode(',', $api->sql_orderby);
                        if (count($parts) == 2) {
                            $query->orderBy(trim($parts[0]), trim($parts[1]));
                        } else {
                            $query->orderByRaw($api->sql_orderby);
                        }
                    } else {
                        $query->orderBy('id', 'desc');
                    }

                    $limit = (int) $request->input('limit', 20);
                    $result = $query->paginate($limit);

                    return response()->json([
                        'api_status' => 1,
                        'api_message' => 'success',
                        'total' => $result->total(),
                        'data' => $result->items(),
                    ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'api_status' => 0,
                'api_message' => 'API Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
