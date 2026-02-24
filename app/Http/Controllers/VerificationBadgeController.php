<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VerificationBadgeController extends Controller
{
    /**
     * Afficher la page de vérification publique d'un badge
     */
    public function verify($code)
    {
        // Rechercher le code de vérification dans la base de données
        $verification = DB::table('verifications_badge')
            ->where('code_verification', $code)
            ->first();

        if (!$verification) {
            return response()->json([
                'status' => 404,
                'message' => 'Code de vérification invalide. Ce badge ne peut pas être authentifié.'
            ], 404);
        }

        // Récupérer les détails de l'attribution
        $attribution = DB::table('attributions_badge')
            ->where('idAttribution', $verification->idAttribution)
            ->first();

        if (!$attribution) {
            return response()->json([
                'status' => 404,
                'message' => 'Erreur: Ce badge existe mais les détails ne sont pas disponibles.'
            ], 404);
        }

        // Vérifier si le badge n'a pas expiré
        $isExpired = false;
        if ($attribution->date_expiration && Carbon::parse($attribution->date_expiration)->isPast()) {
            $isExpired = true;
        }

        // Récupérer les détails du badge
        $badge = DB::table('badges')
            ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
            ->where('badges.idBadge', $attribution->idBadge)
            ->select('badges.*', 'mdls.moduleName as module_nom')
            ->first();

        // Récupérer les détails du projet
        $projet = DB::table('v_projet_cfps')
            ->where('idProjet', $attribution->idProjet)
            ->first();

        $typeProjet = DB::table('projets')->select('idTypeProjet')->where('idProjet', $projet->idProjet)->first();

        // Récupérer les informations de l'apprenant
        switch ($typeProjet->idTypeProjet) {
            case 1:
                $apprenant = DB::table("detail_apprenants as d")
                    ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'e.idCustomer', 'customerName', 'u.photo')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->join('employes as e', 'e.idEmploye', 'd.idEmploye')
                    ->join('customers as c', 'c.idCustomer', 'e.idCustomer')
                    ->where('d.idEmploye', $attribution->idEmploye)
                    ->where('idProjet', $projet->idProjet)
                    ->first();
                break;
            case 2:
                $apprenant = DB::table("detail_apprenant_inters as d")
                    ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'e.idCustomer', 'customerName', 'u.photo')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->join('employes as e', 'e.id', 'd.idEmploye')
                    ->join('customers as c', 'c.idCustomer', 'e.idCustomer')
                    ->where('d.idEmploye', $attribution->idEmploye)
                    ->where('idProjet', $projet->idProjet)
                    ->first();
                break;
        }
        // dd($apprenant);

        // Récupérer les détails du CFP
        $cfp = DB::table('customers')
            ->where('idCustomer', $badge->idCfp)
            ->first();

        // Récupérer les compétences du badge
        $competences = DB::table('competences_badge')
            ->where('idBadge', $badge->idBadge)
            ->get();

        // Récupérer les critères du badge
        $criteres = DB::table('criteres_badge')
            ->where('idBadge', $badge->idBadge)
            ->orderBy('ordre')
            ->get();

        return response()->json([
            'status' => 200,
            'verified' => true,
            'isExpired' => $isExpired,
            'attribution' => $attribution,
            'badge' => $badge,
            'apprenant' => $apprenant,
            'projet' => $projet,
            'cfp' => $cfp,
            'competences' => $competences,
            'criteres' => $criteres,
            'verification' => $verification
        ]);
    }

    /**
     * Régénérer un code de vérification
     */
    public function regenererCode($idAttribution)
    {
        $idCustomer = auth()->user()->id;

        $attribution = DB::table('attributions_badge')
            ->join('badges', 'attributions_badge.idBadge', '=', 'badges.idBadge')
            ->where('attributions_badge.idAttribution', $idAttribution)
            ->where('badges.idCfp', $idCustomer)
            ->first();

        if (!$attribution) {
            return redirect()->route('cfp.attribution.index')
                ->with('error', 'Attribution non trouvée');
        }

        try {
            $nouveauCode = Str::uuid();

            DB::table('verifications_badge')
                ->where('idAttribution', $idAttribution)
                ->update([
                    'code_verification' => $nouveauCode,
                    'date_verification' => null,
                    'ip_verification' => null
                ]);

            return redirect()->back()
                ->with('success', 'Code de vérification régénéré avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }
}
