<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CfpProfilController extends Controller
{
    public function addTrait(Request $request)
    {
        $id = DB::table('traits')->insertGetId([
            'idCustomer' => Customer::idCustomer(),
            'title' => $request->title,
        ]);
        return response()->json(['id' => $id, 'title' => $request->title]);
    }

    public function removeTrait($id)
    {
        DB::table('traits')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function addReason(Request $request)
    {
        $id = DB::table('reasons')->insertGetId([
            'idCustomer' => Customer::idCustomer(),
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return response()->json(['id' => $id, 'title' => $request->title, 'description' => $request->description]);
    }

    public function removeReason($id)
    {
        DB::table('reasons')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
