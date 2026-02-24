<?php

namespace App\Http\Controllers;

use App\Mail\BadgeAttribueMail;
use App\Models\Customer;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttributionBadgeController extends Controller
{
    /**
     * Afficher la liste des attributions de badges pour le centre de formation connecté
     */
    public function index(Request $request)
    {
        $idCustomer = auth()->user()->id;

        // Récupérer les projets avec des badges attribués
        $projets = DB::table('attributions_badge')
            ->join('projets', 'attributions_badge.idProjet', '=', 'projets.idProjet')
            ->join('badges', 'attributions_badge.idBadge', '=', 'badges.idBadge')
            ->where('badges.idCfp', $idCustomer)
            ->select('projets.idProjet', 'projets.project_title', 'projets.project_reference', 'projets.idTypeProjet')
            ->distinct()
            ->orderBy('projets.project_title')
            ->get();

        // Pour chaque projet, récupérer les attributions de badges
        foreach ($projets as $projet) {
            $query = DB::table('attributions_badge')
                ->join('badges', 'attributions_badge.idBadge', '=', 'badges.idBadge')
                ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
                ->where('attributions_badge.idProjet', $projet->idProjet)
                ->where('badges.idCfp', $idCustomer);

            // Jointure différente selon le type de projet
            if ($projet->idTypeProjet == 1) {
                $query->join('users as u', 'u.id', 'attributions_badge.idEmploye')
                    ->join('employes as e', 'e.idEmploye', '=', 'attributions_badge.idEmploye')
                    ->join('customers as c', 'c.idCustomer', '=', 'e.idCustomer');
            } else if ($projet->idTypeProjet == 2) {
                $query->join('users as u', 'u.id', 'attributions_badge.idEmploye')
                    ->join('employes as e', 'e.id', '=', 'attributions_badge.idEmploye')
                    ->join('customers as c', 'c.idCustomer', '=', 'e.idCustomer');
            }

            $projet->attributions = $query->select(
                'attributions_badge.*',
                'badges.titre as badge_titre',
                'badges.image_path as badge_image',
                'u.name as user_nom',
                'u.firstName as user_prenom',
                'u.email as user_email',
                'u.photo',
                'c.customerName',
                'mdls.moduleName as module_titre'
            )->get();

            // Pour chaque attribution, récupérer le code de vérification
            foreach ($projet->attributions as $attribution) {
                $verification = DB::table('verifications_badge')
                    ->where('idAttribution', $attribution->idAttribution)
                    ->first();

                $attribution->code_verification = $verification ? $verification->code_verification : null;
            }
        }

        return response()->json([
            'projets' => $projets
        ]);
    }


    /**
     * Afficher le formulaire d'attribution de badge
     */
    public function create(Request $request)
    {
        $idCustomer = auth()->user()->id;
        $search = $request->input('search');
        $idProjet = $request->input('projet');
        $idBadge = $request->input('badge');

        $query = DB::table('attributions_badge')
            ->join('badges', 'attributions_badge.idBadge', '=', 'badges.idBadge')
            ->join('users as u', 'u.id', 'attributions_badge.idEmploye')
            ->join('employes as e', 'e.idEmploye', 'attributions_badge.idEmploye')
            ->join('projets', 'attributions_badge.idProjet', '=', 'projets.idProjet')
            ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
            ->where('badges.idCfp', $idCustomer)
            ->select(
                'attributions_badge.*',
                'badges.titre as badge_titre',
                'badges.image_path as badge_image',
                'u.name as user_nom',
                'u.firstName as user_prenom',
                'u.email as user_email',
                'projets.project_title as projet_titre',
                'mdls.moduleName as module_titre'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('apprenants.nom', 'like', "%$search%")
                    ->orWhere('apprenants.prenom', 'like', "%$search%")
                    ->orWhere('apprenants.email', 'like', "%$search%")
                    ->orWhere('badges.titre', 'like', "%$search%")
                    ->orWhere('projets.project_title', 'like', "%$search%");
            });
        }

        if ($idProjet) {
            $query->where('attributions_badge.idProjet', $idProjet);
        }

        if ($idBadge) {
            $query->where('attributions_badge.idBadge', $idBadge);
        }

        $attributions = $query->orderBy('attributions_badge.date_attribution', 'desc')->paginate(15);

        // Récupérer les listes pour les filtres
        $badges = DB::table('badges')
            ->where('idCfp', $idCustomer)
            ->whereNot('is_reset', 1)
            ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
            ->select('idBadge', 'titre', 'mdls.moduleName')
            ->get();

        $projets = DB::table('v_projet_cfps')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->where('project_status', "Terminé")
            ->where('module_name', '!=', 'Default module')
            ->whereIn('idModule', function ($query) {
                $query->select('idModule')->from('badges');
            })
            ->get();

        return response()->json([
            'attributions' => $attributions,
            'badges' => $badges,
            'projets' => $projets,
            'search' => $search,
            'idProjet' => $idProjet,
            'idBadge' => $idBadge
        ]);
    }


    /**
     * Charger les projets d'un module spécifique (pour AJAX)
     */
    public function getProjetsModule($idModule)
    {
        $projets = DB::table('v_projet_cfps')
            ->select('project_name', 'project_reference', 'dateDebut', 'dateFin', 'idProjet')
            ->join('badges', 'badges.idModule', '=', 'v_projet_cfps.idModule')
            ->where(function ($query) {
                $query->where('v_projet_cfps.idCfp', Customer::idCustomer())
                    ->orWhere('v_projet_cfps.idCfp_inter', Customer::idCustomer())
                    ->orWhere('v_projet_cfps.idSubContractor', Customer::idCustomer());
            })
            ->where('project_status', "Terminé")
            ->where('module_name', '!=', 'Default module')
            ->where('idBadge', $idModule)
            ->get();

        return response()->json($projets);
    }


    /**
     * Charger les apprenants d'un projet spécifique (pour AJAX)
     */
    public function getApprenantsProjet($idProjet)
    {
        $typeProjet = DB::table('projets')->select('idTypeProjet')->where('idProjet', $idProjet)->first();

        if (!$typeProjet) {
            return response([
                'status' => 404,
                'message' => "Projet introuvable"
            ], 404);
        }

        switch ($typeProjet->idTypeProjet) {
            case 1:
                $apprenants = DB::table("detail_apprenants as d")
                    ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'e.idCustomer', 'customerName', 'u.photo')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->join('employes as e', 'e.idEmploye', 'd.idEmploye')
                    ->join('customers as c', 'c.idCustomer', 'e.idCustomer')
                    ->where('idProjet', $idProjet)
                    ->get();
                break;
            case 2:
                $apprenants = DB::table("detail_apprenant_inters as d")
                    ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'e.idCustomer', 'customerName', 'u.photo')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->join('employes as e', 'e.idEmploye', 'd.idEmploye')
                    ->join('customers as c', 'c.idCustomer', 'e.idCustomer')
                    ->where('idProjet', $idProjet)
                    ->get();
                break;
        }


        return response()->json($apprenants);
    }

    /**
     * Obtenir les détails d'un badge (pour AJAX)
     */
    public function getBadgeDetails($idBadge)
    {
        $idCustomer = auth()->user()->id;

        $badge = DB::table('badges')
            ->where('badges.idBadge', $idBadge)
            ->where('badges.idCfp', $idCustomer)
            ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
            ->select('badges.*', 'mdls.moduleName as module_titre');

        if ($badge->exists()) {
            $competences = DB::table('competences_badge')
                ->where('idBadge', $idBadge)
                ->get();

            $criteres = DB::table('criteres_badge')
                ->where('idBadge', $idBadge)
                ->orderBy('ordre')
                ->get();

            return response()->json([
                'status' => 200,
                'badge' => $badge->first(),
                'competences' => $competences,
                'criteres' => $criteres
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    /**
     * Enregistrer une nouvelle attribution de badge
     */
    public function store(Request $request)
    {
        $idCfp = auth()->user()->id;

        $validated = $request->validate([
            'idBadge' => 'required|exists:badges,idBadge',
            'idProjet' => 'required|exists:projets,idProjet',
            'apprenants' => 'required|array',
            'apprenants.*' => 'exists:apprenants,idEmploye', // Correction selon votre schéma
            'score' => 'nullable|numeric|min:0|max:100',
            'date_expiration' => 'nullable|date|after:today',
            'est_verifie' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que le badge appartient bien au CFP connecté
            $badgeInfo = DB::table('badges')
                ->where('idBadge', $validated['idBadge'])
                ->first();

            // Vérifier que le projet est accessible pour ce CFP
            $projetInfo = DB::table('v_projet_cfps')
                ->where('idProjet', $validated['idProjet'])
                ->first();

            if (!$badgeInfo || !$projetInfo || $badgeInfo->idCfp != $idCfp) {
                return redirect()->back()->with('error', 'Vous n\'avez pas les droits pour attribuer ce badge ou ce projet.');
            }

            $attributionsReussies = 0;
            $attributionsExistantes = 0;

            foreach ($validated['apprenants'] as $idEmploye) {
                // Vérifier si l'attribution existe déjà
                $existant = DB::table('attributions_badge')
                    ->where('idBadge', $validated['idBadge'])
                    ->where('idEmploye', $idEmploye)
                    ->where('idProjet', $validated['idProjet'])
                    ->first();

                if ($existant) {
                    $attributionsExistantes++;
                    continue;
                }

                // Créer l'attribution
                $idAttribution = DB::table('attributions_badge')->insertGetId([
                    'idBadge' => $validated['idBadge'],
                    'idEmploye' => $idEmploye,
                    'idProjet' => $validated['idProjet'],
                    'date_attribution' => now(),
                    'date_expiration' => $validated['date_expiration'] ?? null,
                    'score' => $validated['score'] ?? null,
                    'est_verifie' => isset($validated['est_verifie']) ? true : false
                ]);

                // Générer un code de vérification
                $codeVerification = Str::uuid()->toString();

                // Enregistrer dans la table de vérification
                DB::table('verifications_badge')->insert([
                    'idAttribution' => $idAttribution,
                    'code_verification' => $codeVerification,
                    'idProjet' => $validated['idProjet']
                ]);

                $typeProjet = DB::table('projets')->select('idTypeProjet')->where('idProjet', $projetInfo->idProjet)->first();

                // Récupérer les informations de l'apprenant
                switch ($typeProjet->idTypeProjet) {
                    case 1:
                        $apprenant = DB::table("detail_apprenants as d")
                            ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'e.idCustomer', 'customerName', 'u.photo')
                            ->join('users as u', 'u.id', 'd.idEmploye')
                            ->join('employes as e', 'e.idEmploye', 'd.idEmploye')
                            ->join('customers as c', 'c.idCustomer', 'e.idCustomer')
                            ->where('d.idEmploye', $idEmploye)
                            ->where('idProjet', $projetInfo->idProjet)
                            ->first();
                        break;
                    case 2:
                        $apprenant = DB::table("detail_apprenant_inters as d")
                            ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'e.idCustomer', 'customerName', 'u.photo')
                            ->join('users as u', 'u.id', 'd.idEmploye')
                            ->join('employes as e', 'e.id', 'd.idEmploye')
                            ->join('customers as c', 'c.idCustomer', 'e.idCustomer')
                            ->where('d.idEmploye', $idEmploye)
                            ->where('idProjet', $projetInfo->idProjet)
                            ->first();
                        break;
                }

                // Envoyer l'email de notification
                $this->envoyerEmailAttribution($apprenant, $badgeInfo, $projetInfo, $codeVerification);

                $attributionsReussies++;
            }

            DB::commit();

            $message = "Attribution réussie pour $attributionsReussies apprenant(s).";
            if ($attributionsExistantes > 0) {
                $message .= " $attributionsExistantes attribution(s) existante(s) ignorée(s).";
            }

            return response([
                'status' => 200,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 500,
                'message' => 'Erreur lors de l\'attribution du badge: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer un email de notification d'attribution de badge
     *
     * @param object $apprenant Les informations de l'apprenant
     * @param object $badge Les informations du badge
     * @param object $projet Les informations du projet
     * @param string $codeVerification Le code de vérification du badge
     * @return void
     */
    protected function envoyerEmailAttribution($apprenant, $badge, $projet, $codeVerification)
    {
        // Récupérer les informations du CFP
        $cfp = DB::table('customers')
            ->where('idCustomer', $badge->idCfp)
            ->first();
        // URL de vérification du badge
        // $urlVerification = route('cfp.badge.verification', ['code' => $codeVerification]);

        $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:8000');
        // $frontendUrl = env('FRONTEND_URL', 'https://badge.mg.formafusion.io');
        $urlVerification = $frontendUrl . '/verification/' . $codeVerification;


        // Construire les données pour l'email
        $emailData = [
            'nom' => $apprenant->emp_name,
            'prenom' => $apprenant->emp_firstname,
            'badge_titre' => $badge->titre,
            'badge_description' => $badge->description,
            'projet_titre' => $projet->project_title,
            'cfp_nom' => $cfp->customerName,
            'url_verification' => $urlVerification,
            'date_attribution' => now()->format('d/m/Y'),
            'score' => $apprenant->score ?? 'Non évalué'
        ];

        // Envoyer l'email
        try {

            $htmlContent = (new BadgeAttribueMail($emailData))->render();

            app(BrevoService::class)->sendEmail(
                $apprenant->emp_email,
                "Attribution de badge",
                $htmlContent
            );

            // Mail::to($apprenant->emp_email)->send(new BadgeAttribueMail($emailData));

            // Optionnel : Logger l'envoi de l'email
            Log::info("Email d'attribution de badge envoyé à {$apprenant->emp_email} pour le badge {$badge->titre}");
        } catch (\Exception $e) {
            // Enregistrer l'erreur mais ne pas bloquer le processus
            Log::error("Erreur lors de l'envoi de l'email d'attribution: " . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une attribution
     */
    // public function show($id)
    // {
    //     $idCustomer = auth()->user()->id;

    //     $attribution = DB::table('attributions_badge')
    //         ->join('badges', 'attributions_badge.idBadge', '=', 'badges.idBadge')
    //         ->join('apprenants', 'attributions_badge.idEmploye', '=', 'apprenants.idEmploye')
    //         ->join('projets', 'attributions_badge.idProjet', '=', 'projets.idProjet')
    //         ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
    //         ->where('attributions_badge.idAttribution', $id)
    //         ->where('badges.idCfp', $idCustomer)
    //         ->select(
    //             'attributions_badge.*',
    //             'badges.*',
    //             'apprenants.nom as user_nom',
    //             'apprenants.prenom as user_prenom',
    //             'apprenants.email as user_email',
    //             'projets.project_title as projet_titre',
    //             'mdls.moduleName as module_titre'
    //         )
    //         ->first();

    //     if (!$attribution) {
    //         return redirect()->route('cfp.attribution.index')
    //             ->with('error', 'Attribution non trouvée');
    //     }

    //     $competences = DB::table('competences_badge')
    //         ->where('idBadge', $attribution->idBadge)
    //         ->get();

    //     $criteres = DB::table('criteres_badge')
    //         ->where('idBadge', $attribution->idBadge)
    //         ->orderBy('ordre')
    //         ->get();

    //     $verification = DB::table('verifications_badge')
    //         ->where('idAttribution', $id)
    //         ->first();

    //     return view('CFP.attributions.show', compact('attribution', 'competences', 'criteres', 'verification'));
    // }

    /**
     * Supprimer une attribution
     */
    public function destroy($id)
    {
        $idCustomer = auth()->user()->id;

        $attribution = DB::table('attributions_badge')
            ->join('badges', 'attributions_badge.idBadge', '=', 'badges.idBadge')
            ->where('attributions_badge.idAttribution', $id)
            ->where('badges.idCfp', $idCustomer)
            ->first();

        if (!$attribution) {
            return response([
                'status' => 404,
                'message' => "Attribution introuvable"
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Supprimer les vérifications
            DB::table('verifications_badge')
                ->where('idAttribution', $id)
                ->delete();

            // Supprimer l'attribution
            DB::table('attributions_badge')
                ->where('idAttribution', $id)
                ->delete();

            DB::commit();

            return response([
                'status' => 200,
                'message' => "Attribution supprimée avec succès!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ]);
        }
    }
}
