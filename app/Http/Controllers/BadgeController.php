<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BadgeController extends Controller
{
    /**
     * AttributionBadgeController : attribution des badges aux apprenants
     * VerficationBadgeController : verification des badges des apprenants
     */
    /**
     * Afficher la liste des badges pour le centre de formation connecté
     */
    public function index(Request $request)
    {
        $idCustomer = auth()->user()->id;

        $badges = DB::table('badges')
            ->where('idCfp', $idCustomer)
            ->whereNot('is_reset', 1)
            ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
            ->select('badges.*', 'mdls.moduleName as module_titre')
            ->orderBy('date_creation', 'desc')
            ->get();

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName')
            ->where('idCustomer', $idCustomer)
            ->whereNot('moduleName', 'Default module')
            ->whereIn('idModule', function ($query) {
                $query->select('idModule')->from('badges');
            })
            ->get();

        if (count($badges) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'badges' => $badges,
            'modules' => $modules
        ]);
    }


    public function getModules()
    {
        $idCustomer = auth()->user()->id;

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName')
            ->where('idCustomer', $idCustomer)
            ->whereNot('moduleName', 'Default module')
            ->whereNotIn('idModule', function ($query) {
                $query->select('idModule')->from('badges');
            })
            ->get();

        if (count($modules) <= 0)
            return response()->json([
                'status' => 404,
                'message' => "Aucun résultat !"
            ], 404);

        return response()->json([
            'status' => 200,
            'modules' => $modules
        ]);
    }

    /**
     * Enregistrer un nouveau badge
     */
    public function store(Request $request)
    {
        $idCustomer = auth()->user()->id;

        $validated = $request->validate([
            'idModule' => 'required|exists:mdls,idModule',
            'titre' => 'required|string|max:200',
            'sous_titre' => 'required|string|max:200',
            'description' => 'required|string',
            'a_propos' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'competences' => 'nullable|array',
            'competences.*' => 'string|max:100',
            'criteres' => 'required|array',
            'criteres.*' => 'string'
        ]);

        $identifiant = Str::slug($validated['titre']) . '-' . Str::random(6);

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $driver = new Driver();
                $manager = new ImageManager($driver);
                $image = $manager->read($file)->toWebp();
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';

                $path = 'badge/idModule_' . $validated['idModule'] . '/' . $filename;

                // Upload vers DigitalOcean Spaces
                $disk = Storage::disk('do');
                $disk->put($path, $image->__toString());

                // Assurez-vous de stocker le chemin de l'image
                $imagePath = $path;
            }

            $idBadge = DB::table('badges')->insertGetId([
                'idModule' => $validated['idModule'],
                'idCfp' => $idCustomer,
                'titre' => $validated['titre'],
                'sous_titre' => $validated['sous_titre'],
                'description' => $validated['description'],
                'a_propos' => $validated['a_propos'],
                'image_path' => $imagePath,
                'identifiant_unique' => $identifiant,
                'date_creation' => now()
            ]);

            if (isset($validated['competences']) && !empty($validated['competences'])) {
                foreach ($validated['competences'] as $competence) {
                    if (!empty($competence)) {
                        DB::table('competences_badge')->insert([
                            'idBadge' => $idBadge,
                            'nom_competence' => $competence
                        ]);
                    }
                }
            }

            if (!empty($validated['criteres'])) {
                $ordre = 1;
                foreach ($validated['criteres'] as $critere) {
                    if (!empty($critere)) {
                        DB::table('criteres_badge')->insert([
                            'idBadge' => $idBadge,
                            'texte_critere' => $critere,
                            'ordre' => $ordre++
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Badge créé avec succès.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la création du badge : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Afficher les détails d'un badge
     */
    public function show($id)
    {
        $idCustomer = auth()->user()->id;

        $badge = DB::table('badges')
            ->where('badges.idBadge', $id)
            ->where('badges.idCfp', $idCustomer)
            ->join('mdls', 'badges.idModule', '=', 'mdls.idModule')
            ->select('badges.*', 'mdls.titre as module_titre')
            ->first();

        if (!$badge) {
            return redirect()->route('badges.index')
                ->with('error', 'Badge non trouvé');
        }

        $competences = DB::table('competences_badge')
            ->where('idBadge', $id)
            ->get();

        $criteres = DB::table('criteres_badge')
            ->where('idBadge', $id)
            ->orderBy('ordre')
            ->get();

        $attributions = DB::table('attributions_badge')
            ->where('idBadge', $id)
            ->join('utilisateurs', 'attributions_badge.idUser', '=', 'utilisateurs.idUser')
            ->select('attributions_badge.*', 'utilisateurs.nom', 'utilisateurs.prenom', 'utilisateurs.email')
            ->orderBy('date_attribution', 'desc')
            ->get();

        return view('badges.show', compact('badge', 'competences', 'criteres', 'attributions'));
    }

    /**
     * Afficher le formulaire d'édition d'un badge
     */
    public function edit($id)
    {
        $idCustomer = auth()->user()->id;

        $badge = DB::table('badges')
            ->where('idBadge', $id)
            ->where('idCfp', $idCustomer)
            ->first();

        if (!$badge) {
            return response()->json(['error' => 'Badge non trouvé'], 404);
        }

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName as titre')
            ->where('idCustomer', $idCustomer)
            ->whereNot('moduleName', 'Default module')
            ->get();

        $competences = DB::table('competences_badge')
            ->where('idBadge', $id)
            ->get();

        $criteres = DB::table('criteres_badge')
            ->where('idBadge', $id)
            ->orderBy('ordre')
            ->get();

        // Retourner les données au format JSON pour AJAX
        return response()->json([
            'status' => 200,
            'badge' => $badge,
            'modules' => $modules,
            'competences' => $competences,
            'criteres' => $criteres
        ]);
    }

    /**
     * Mettre à jour un badge
     */
    public function update(Request $request, $id)
    {
        $idCustomer = auth()->user()->id;

        $badge = DB::table('badges')
            ->where('idBadge', $id)
            ->where('idCfp', $idCustomer)
            ->first();

        if (!$badge) {
            return response()->json(['error' => 'Badge non trouvé'], 404);
        }

        $validated = $request->validate([
            'idModule' => 'required|exists:mdls,idModule',
            'titre' => 'required|string|max:200',
            'sous_titre' => 'nullable|string|max:200',
            'description' => 'required|string',
            'a_propos' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'competences' => 'nullable|array',
            'competences.*' => 'string|max:100',
            'competences_id' => 'nullable|array',
            'competences_id.*' => 'integer',
            'competences_to_delete' => 'nullable|array',
            'competences_to_delete.*' => 'integer',
            'criteres' => 'required|array',
            'criteres.*' => 'string',
            'criteres_id' => 'nullable|array',
            'criteres_id.*' => 'integer',
            'criteres_to_delete' => 'nullable|array',
            'criteres_to_delete.*' => 'integer'
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'idModule' => $validated['idModule'],
                'titre' => $validated['titre'],
                'sous_titre' => $validated['sous_titre'] ?? null,
                'description' => $validated['description'],
                'a_propos' => $validated['a_propos'] ?? null,
            ];

            if ($request->hasFile('image')) {
                if ($badge->image_path) {
                    Storage::disk('do')->delete($badge->image_path);
                }

                $updateData['image_path'] = $request->file('image')->store('badges', 'do');
            }

            DB::table('badges')
                ->where('idBadge', $id)
                ->update($updateData);

            // Gestion des compétences à supprimer spécifiquement
            if ($request->has('competences_to_delete') && is_array($request->competences_to_delete)) {
                DB::table('competences_badge')
                    ->where('idBadge', $id)
                    ->whereIn('idCompetence', $request->competences_to_delete)
                    ->delete();
            }

            // Mise à jour des compétences existantes et ajout des nouvelles
            if (!empty($validated['competences'])) {
                foreach ($validated['competences'] as $index => $competence) {
                    if (!empty($competence)) {
                        // Si un ID est fourni, c'est une compétence existante
                        if (isset($validated['competences_id'][$index])) {
                            DB::table('competences_badge')
                                ->where('idCompetence', $validated['competences_id'][$index])
                                ->where('idBadge', $id)
                                ->update(['nom_competence' => $competence]);
                        } else {
                            // Sinon, c'est une nouvelle compétence
                            DB::table('competences_badge')->insert([
                                'idBadge' => $id,
                                'nom_competence' => $competence
                            ]);
                        }
                    }
                }
            }

            // Gestion des critères à supprimer spécifiquement
            if ($request->has('criteres_to_delete') && is_array($request->criteres_to_delete)) {
                DB::table('criteres_badge')
                    ->where('idBadge', $id)
                    ->whereIn('idCritere', $request->criteres_to_delete)
                    ->delete();
            }

            // Mise à jour des critères existants et ajout des nouveaux
            if (!empty($validated['criteres'])) {
                foreach ($validated['criteres'] as $index => $critere) {
                    if (!empty($critere)) {
                        // Si un ID est fourni, c'est un critère existant
                        if (isset($validated['criteres_id'][$index])) {
                            DB::table('criteres_badge')
                                ->where('idCritere', $validated['criteres_id'][$index])
                                ->where('idBadge', $id)
                                ->update(['texte_critere' => $critere]);
                        } else {
                            // Sinon, c'est un nouveau critère
                            $ordre = DB::table('criteres_badge')
                                ->where('idBadge', $id)
                                ->max('ordre') + 1;

                            DB::table('criteres_badge')->insert([
                                'idBadge' => $id,
                                'texte_critere' => $critere,
                                'ordre' => $ordre ?? 1
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Badge mis à jour avec succès!',
                'badge_id' => $id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Supprimer un badge
     */
    public function destroy($id)
    {
        $idCustomer = auth()->user()->id;

        $badge = DB::table('badges')
            ->where('idBadge', $id)
            ->where('idCfp', $idCustomer)
            ->first();

        if (!$badge) {
            return response()->json([
                'status' => 404,
                'message' => 'Badge introuvable !'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Supprimer les vérifications
            DB::table('verifications_badge')
                ->join('attributions_badge', 'verifications_badge.idAttribution', '=', 'attributions_badge.idAttribution')
                ->where('attributions_badge.idBadge', $id)
                ->delete();

            // Supprimer les attributions
            DB::table('attributions_badge')
                ->where('idBadge', $id)
                ->delete();

            // Supprimer le badge
            DB::table('badges')
                ->where('idBadge', $id)
                ->delete();

            // DB::table('badges')
            //     ->where('idBadge', $id)
            //     ->update(['is_reset' => 1]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Badge supprimé avec succès!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ]);
        }
    }
}
