<?php

namespace App\Http\Controllers;

use App\Http\Requests\BatchLearnerRequest;
use App\Models\BatchLearner;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class BatchLearnerController extends Controller
{
    protected $batch_learner;

    public function __construct(BatchLearner $_batch_learner)
    {
        $this->batch_learner = $_batch_learner;
    }

    public function index()
    {
        return response()->json(BatchLearner::all());
    }

    public function getNoParticipantLearner($id)
    {
        $idEmployes = BatchLearner::where('batch_id', $id)->pluck('employe_id');

        $employes = DB::table('employes as E')
            ->join('users as U', 'U.id', 'E.idEmploye')
            ->where('E.idCustomer', Customer::idCustomer())
            ->whereNotIn('E.idEmploye', $idEmployes)->get(['E.idEmploye', 'U.name', 'U.firstName', 'U.photo']);

        return response()->json(['employes' => $employes]);
    }

    public function getParticipantLearner($id)
    {
        $idEmployes = BatchLearner::where('batch_id', $id)->pluck('employe_id');

        $employes = DB::table('batch_learners as B')
            ->join('employes as E', 'B.employe_id', 'E.idEmploye')
            ->join('users as U', 'U.id', 'E.idEmploye')
            ->where('E.idCustomer', Customer::idCustomer())
            ->whereIn('E.idEmploye', $idEmployes)->get(['B.id', 'B.batch_id', 'E.idEmploye', 'U.name', 'U.firstName', 'U.photo']);

        return response()->json(['employes' => $employes]);
    }

    public function store(BatchLearnerRequest $request)
    {
        $this->batch_learner->create($request->validated());
        return response()->json('ok');
    }

    public function update(BatchLearnerRequest $request, BatchLearner $batch_learner)
    {
        if ($batch_learner != null) {
            $batch_learner->fill($request->validated());
            $batch_learner->save();
            return response()->json('ok');
        } else {
            return response()->json('erreur');
        }
    }

    public function destroy(BatchLearner $batch_learner)
    {
        if ($batch_learner != null) {
            $batch_learner->delete();
            return response()->json('ok');
        } else {
            return response()->json('erreur');
        }
    }
}
