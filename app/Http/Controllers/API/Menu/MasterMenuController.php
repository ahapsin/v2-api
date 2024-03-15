<?php

namespace App\Http\Controllers\API\Menu;

use App\Http\Controllers\API\ActivityLogger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\M_MasterMenu;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MasterMenuController extends Controller
{
    public function index()
    {
        $data = M_MasterMenu::orderBy('order', 'asc')->get();
        
        return response()->json(['message' => 'OK', "status" => 200, 'response' => $this->buildMenuArray($data)], 200);
    }

    function buildMenuArray($menuItems)
    {
        $menuArray = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem['parent'] === '0' && $menuItem['status'] === 'active') {
                $menuArray[$menuItem['id']] = [
                    'menuid' => $menuItem['id'],
                    'menuitem' => [
                        'labelmenu' => $menuItem['menu_name'],
                        'routename' => $menuItem['route'],
                        'leading' => explode( ',',$menuItem['leading']),
                        'action' => $menuItem['action'],
                        'ability' => $menuItem['ability'],
                        'submenu' => []
                    ]
                ];
            }
        }

        foreach ($menuItems as $menuItem) {
            if (!isset($menuArray[$menuItem['parent']])) {
                $menuArray[$menuItem['id']] = [
                    'menuid' => $menuItem['id'],
                    'menuitem' => [
                        'labelmenu' => $menuItem['menu_name'],
                        'routename' => $menuItem['route'],
                        'leading' => explode(',', $menuItem['leading']),
                        'action' => $menuItem['action'],
                        'ability' => $menuItem['ability'],
                        'submenu' => []
                    ]
                ];
            }
        }

        foreach ($menuItems as $menuItem) {
            if ($menuItem['parent'] !== '0' && isset($menuArray[$menuItem['parent']])) {
                $menuArray[$menuItem['parent']]['menuitem']['submenu'][] = [
                    'subid' => $menuItem['id'],
                    'sublabel' => $menuItem['menu_name'],
                    'subroute' => $menuItem['route'],
                    'leading' => explode( ',',$menuItem['leading']),
                    'action' => $menuItem['action'],
                    'ability' => $menuItem['ability']
                ];
            }
        }   

        return array_values($menuArray);
    }

    public function _validate($request)
    {
        $validator = $request->validate([
            'menu_name' => 'required|string',
            'route' => 'required|string',
            'parent' => 'required',
            'order' => 'numeric',
            'leading' => 'string',
            // 'action' => 'string'
        ]);

        return $validator;
    }


    public function store(Request $req)
    { 
        try {
            DB::beginTransaction();
            $validator = $this->_validate($req);

            $validator['status'] = 'active';
            $validator['created_by'] = $req->user()->id;

            ActivityLogger::logActivity($req,"Success",200);
            $create =  M_MasterMenu::create($validator);

            DB::commit();
            return response()->json(['message' => 'Master Menu created successfully', "status" => 200, 'response' => $create], 200);
        } catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(), "status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }
}
