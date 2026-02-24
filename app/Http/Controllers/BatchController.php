<?php

namespace App\Http\Controllers;

use App\Http\Requests\BatchRequest;
use App\Models\Batch;
use App\Models\Customer;

class BatchController extends Controller
{
    protected $batch;

    public function __construct(Batch $_batch)
    {
        $this->batch = $_batch;
    }

    public function index()
    {
        $idCustomer = Customer::idCustomer();

        return response()->json([
            'status' => 200,
            'idCustomer' => $idCustomer
        ]);
    }

    public function getAll()
    {
        $data = Batch::withCount('batchlearners')->where('customer_id', Customer::idCustomer())->get();

        return response()->json([
            'results' => $data
        ]);
    }

    public function store(BatchRequest $request)
    {
        $this->batch->create($request->validated());
        return response()->json(['message' => 'Batch ajouté avec succes'], 200);
    }

    public function edit(Batch $id)
    {
        $batch = Batch::find($id)->first();

        return response(['batch' => $batch]);
    }

    public function update(BatchRequest $request, Batch $batch)
    {
        if ($batch != null) {
            $batch->fill($request->validated());
            $batch->save();
            return response()->json(['message' => 'Batch modifié avec succes'], 200);
        } else {
            return response()->json('erreur');
        }
    }

    public function destroy(Batch $batch)
    {
        try {
            if ($batch != null) {
                $batch->delete();
                return response()->json(['message' => 'Batch supprimé avec succes'], 200);
            } else {
                return response()->json(['error' => 'Batch not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Vous pouvez pas supprimer cette batch'], 500);
        }
    }
}
