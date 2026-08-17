<?php

namespace Tokalink\Inermin\controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InerminStatisticController extends InerminController
{
    public function cbInit()
    {
        $this->table = 'cms_statistics';
        $this->primary_key = 'id';
        $this->title_field = 'name';

        $this->col = [
            ['label' => 'ID', 'name' => 'id'],
            ['label' => 'Statistic Name', 'name' => 'name'],
            ['label' => 'Slug', 'name' => 'slug'],
        ];

        $this->form = [
            ['label' => 'Statistic Name', 'name' => 'name', 'type' => 'text', 'required' => true],
        ];

        $this->addaction = [
            [
                'label' => 'Builder',
                'url' => '/' . config('inermin.ADMIN_PATH', 'administrator') . '/statistic_builder/builder/[id]',
                'icon' => 'bi bi-tools',
                'color' => 'amber'
            ],
            [
                'label' => 'View Dashboard',
                'url' => '/' . config('inermin.ADMIN_PATH', 'administrator') . '/statistic_builder/show/[slug]',
                'icon' => 'bi bi-eye',
                'color' => 'emerald'
            ]
        ];
    }

    public function hook_before_add(&$arr)
    {
        $arr['slug'] = Str::slug($arr['name']);
    }

    public function hook_before_edit(&$arr, $id)
    {
        if (isset($arr['name'])) {
            $arr['slug'] = Str::slug($arr['name']);
        }
    }

    /**
     * Display the visual Statistic Builder interface for an ID.
     */
    public function getBuilder($id)
    {
        $statistic = DB::table('cms_statistics')->where('id', $id)->first();
        if (!$statistic) {
            return redirect('/' . config('inermin.ADMIN_PATH', 'administrator') . '/statistic_builder')
                ->with('error', 'Statistic record not found');
        }

        $components = DB::table('cms_statistic_components')
            ->where('id_cms_statistics', $id)
            ->orderBy('sorting', 'asc')
            ->get()
            ->map(function ($comp) {
                $comp->config = json_decode($comp->config ?? '{}', true) ?: (object)[];
                return $comp;
            });

        return Inertia::render('Inermin/StatisticBuilder/Builder', [
            'page_title' => 'Statistic Builder - ' . $statistic->name,
            'statistic' => $statistic,
            'components' => $components,
        ]);
    }

    /**
     * Render a finished Statistic Dashboard view for a given slug.
     */
    public function getShow($slug)
    {
        $statistic = DB::table('cms_statistics')->where('slug', $slug)->first();
        if (!$statistic) {
            // Fallback by ID if numeric
            if (is_numeric($slug)) {
                $statistic = DB::table('cms_statistics')->where('id', $slug)->first();
            }
        }

        if (!$statistic) {
            return redirect('/' . config('inermin.ADMIN_PATH', 'administrator'))
                ->with('error', 'Statistic dashboard not found');
        }

        $components = DB::table('cms_statistic_components')
            ->where('id_cms_statistics', $statistic->id)
            ->orderBy('sorting', 'asc')
            ->get();

        $processedComponents = [];
        foreach ($components as $comp) {
            $config = json_decode($comp->config ?? '{}', true) ?: [];
            $computedValue = null;

            // Execute SQL query if provided safely
            if (!empty($config['sql'])) {
                try {
                    $sql = trim($config['sql']);

                    // Only SELECT statements are allowed
                    if (!preg_match('/^SELECT\s+/i', $sql)) {
                        throw new \Exception('Only SELECT queries are allowed in statistic builder.');
                    }

                    $forbidden = [';', '--', '/*', '*/', 'UNION', 'INSERT', 'UPDATE', 'DELETE',
                        'DROP', 'TRUNCATE', 'ALTER', 'CREATE', 'EXEC', 'EXECUTE', 'UNHEX',
                        'LOAD_FILE', 'INTO OUTFILE', 'INTO DUMPFILE', 'SLEEP', 'BENCHMARK'];
                    foreach ($forbidden as $keyword) {
                        if (stripos($sql, $keyword) !== false) {
                            throw new \Exception('Forbidden SQL keyword detected: ' . $keyword);
                        }
                    }

                    // Replace session placeholders e.g. [admin_id]
                    foreach (Session::all() as $sKey => $sVal) {
                        if (is_string($sVal) || is_numeric($sVal)) {
                            $replacement = is_numeric($sVal)
                                ? $sVal
                                : "'" . addslashes($sVal) . "'";
                            $sql = str_replace('[' . $sKey . ']', $replacement, $sql);
                        }
                    }
                    $queryResult = DB::select($sql);

                    $compType = strtolower($comp->component_name);
                    if (in_array($compType, ['smallbox', 'statbox', 'small-box', 'stat-card', 'stat_box'])) {
                        if (!empty($queryResult) && count($queryResult) > 0) {
                            $firstRow = (array)$queryResult[0];
                            $val = reset($firstRow);
                            $computedValue = is_numeric($val) ? (float)$val : $val;
                        } else {
                            $computedValue = 0;
                        }
                    } else {
                        // Charts, Tables
                        $computedValue = array_map(function ($r) {
                            return (array)$r;
                        }, $queryResult);
                    }
                } catch (\Exception $e) {
                    $computedValue = 'ERR: ' . $e->getMessage();
                }
            }

            $processedComponents[] = [
                'id' => $comp->id,
                'componentID' => $comp->componentID,
                'component_name' => $comp->component_name,
                'area_name' => $comp->area_name ?: 'area1',
                'sorting' => $comp->sorting,
                'config' => $config,
                'value' => $computedValue,
            ];
        }

        return Inertia::render('Inermin/StatisticBuilder/Show', [
            'page_title' => $statistic->name,
            'statistic' => $statistic,
            'components' => $processedComponents,
        ]);
    }

    /**
     * API: Add a new component to statistic builder.
     */
    public function postAddComponent(Request $request)
    {
        $id_cms_statistics = $request->input('id_cms_statistics');
        $component_name = $request->input('component_name');
        $area_name = $request->input('area_name', 'area1');
        $sorting = $request->input('sorting', 0);

        $componentID = md5(microtime() . rand(1000, 9999));

        $defaultConfig = [
            'name' => 'Untitled Widget',
            'icon' => 'bi bi-graph-up-arrow',
            'color' => 'amber',
            'sql' => 'SELECT COUNT(*) FROM cms_users',
            'link' => '',
            'html' => '<p>Custom Panel Content</p>',
        ];

        $id = DB::table('cms_statistic_components')->insertGetId([
            'id_cms_statistics' => $id_cms_statistics,
            'componentID' => $componentID,
            'component_name' => $component_name,
            'area_name' => $area_name,
            'config' => json_encode($defaultConfig),
            'sorting' => $sorting,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'component' => [
                'id' => $id,
                'componentID' => $componentID,
                'component_name' => $component_name,
                'area_name' => $area_name,
                'sorting' => $sorting,
                'config' => $defaultConfig,
            ]
        ]);
    }

    /**
     * API: Update component placement/area/sorting.
     */
    public function postUpdateAreaComponent(Request $request)
    {
        $componentID = $request->input('componentID');
        $area_name = $request->input('area_name');
        $sorting = $request->input('sorting', 0);

        DB::table('cms_statistic_components')
            ->where('componentID', $componentID)
            ->update([
                'area_name' => $area_name,
                'sorting' => $sorting,
                'updated_at' => now(),
            ]);

        return response()->json(['status' => true]);
    }

    /**
     * API: Save component configuration.
     */
    public function postSaveComponent(Request $request)
    {
        $componentID = $request->input('componentID');
        $config = $request->input('config', []);

        DB::table('cms_statistic_components')
            ->where('componentID', $componentID)
            ->update([
                'config' => json_encode($config),
                'updated_at' => now(),
            ]);

        return response()->json(['status' => true]);
    }

    /**
     * API: Delete component.
     */
    public function getDeleteComponent($id)
    {
        DB::table('cms_statistic_components')
            ->where('componentID', $id)
            ->orWhere('id', $id)
            ->delete();

        return response()->json(['status' => true]);
    }
}
