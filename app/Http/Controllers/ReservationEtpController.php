<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReservationEtpController extends Controller
{

    private function getApprenant($id)
    {
        $apprs = DB::table('reservation_participant')
            ->select('id', 'nom', 'prenom', 'email', 'fonction', 'idReservation')
            ->where('idReservation', $id)
            ->orderBy('nom', 'asc')
            ->get()
            ->toArray();
        return $apprs;
    }

    private function getResponsable($id)
    {
        $resp = DB::table('reservation_responsable')
            ->select('id', 'nom', 'prenom', 'email', 'telephone', 'fonction', 'idReservation')
            ->where('idReservation', $id)
            ->orderBy('nom', 'asc')
            ->get()
            ->toArray();
        return $resp;
    }

    public function allRsvEtp()
    {
        $reservations = DB::table('v_reservations_etp')
            ->select('id', 'idEtp', 'project_start_date', 'project_end_date', 'project_ville', 'project_module_name', 'project_module_logo', 'project_module_domaine_name', 'nbPlaceReserved', 'cfp_name')
            ->where('idEtp', Customer::idCustomer())
            ->get();

        return response()->json([
            'status' => 200,
            'reservations' => [
                'reservation_count' => count($reservations),
                'reservation_item' => $reservations
            ]
        ]);
    }

    public function showReservationById($idReservation)
    {
        $reservation = DB::table('v_reservations_etp')
            ->select('id', 'cfp_name', 'cfp_email', 'cfp_phone', 'project_start_date', 'project_end_date', 'project_ville', 'project_module_name', 'project_module_logo', 'project_description', 'project_reference', 'project_type')
            ->where('idEtp', Customer::idCustomer())
            ->where('id', $idReservation)
            ->first();


        $result = [
            'idReservation' => $reservation->id,
            'cfp_name' => $reservation->cfp_name,
            'cfp_email' => $reservation->cfp_email,
            'cfp_phone' => $reservation->cfp_phone,
            'project_ville' => $reservation->project_ville,
            'project_type' => $reservation->project_type,
            'project_reference' => $reservation->project_reference,
            'project_description' => $reservation->project_description,
            'project_start_date' => $reservation->project_start_date,
            'project_end_date' => $reservation->project_end_date,
            'project_module_name' => $reservation->project_module_name,
            'project_module_logo' => $reservation->project_module_logo,
            'apprenants' => $this->getApprenant($reservation->id),
            'referent' => $this->getResponsable($reservation->id)
        ];

        return response()->json([
            'status' => 200,
            'reservation' => $result
        ]);
    }
}
