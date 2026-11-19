<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index()
    {
        $unit = Unit::all();
        return view("admin_panel.unit.index", compact('unit'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:units,name,'.$request->edit_id,
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        if ($request->has('edit_id') && !empty($request->edit_id)) {
            $Company = Unit::find($request->edit_id);
            $msg = [
                'success' => 'Unit Updated Successfully',
                'reload' => true
            ];
        } else {
            $Company = new Unit();
            $msg = [
                'success' => 'Unit Created Successfully',
                'redirect' => route('Unit.home')
            ];
        }

        $Company->name = trim($request->name);

        // Generate slug if empty
        if (empty($Company->slug)) {
            $baseSlug = Str::slug($request->name, '_');
            $slug = 'by_' . $baseSlug;
            // Check uniqueness
            $count = Unit::where('slug', $slug)->where('id', '!=', $request->edit_id ?? 0)->count();
            if ($count > 0) {
                $slug .= '_' . time();
            }
            $Company->slug = $slug;
        }

        // Default to carton for CTN functionality as requested
        $Company->type = $request->type ?? $Company->type ?? 'carton';
        $Company->save();

        return response()->json($msg);
    }

    public function delete($id)
    {

        $company = Unit::find($id);
        if ($company) {
            $company->delete();
            $msg = [
                'success' => 'Unit Deleted Successfully',
                'reload' =>  route('Unit.home'),
            ];
        } else {
            $msg = ['error' => 'Unit Not Found'];
        }
        return response()->json($msg);
    }
}
