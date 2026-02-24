<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SectionController extends Controller
{
    public function show()
    {
        $idCustomer = Customer::idCustomer();
        $sections = [
            'reglement' => DB::table('reglement')->where('idCustomer', $idCustomer)->first(),
            'accueil' => DB::table('accueil')->where('idCustomer', $idCustomer)->first(),
            'conditions' => DB::table('conditions')->where('idCustomer', $idCustomer)->first(),
            'acces' => DB::table('acces')->where('idCustomer', $idCustomer)->first(),
            'accompagnement' => DB::table('accompagnement')->where('idCustomer', $idCustomer)->first(),
            'organigramme' => DB::table('marketplace_images')->where('idCustomer', $idCustomer)->where('path', 'like', "img/marketorg%")->first(),
        ];

        return response()->json([
            'status' => 200,
            'sections' => $sections
        ]);
    }

    private function updateCustomerContent($table, $content, $message)
    {
        $idCustomer = Customer::idCustomer();
        try {
            DB::table($table)->updateOrInsert(
                ['idCustomer' => $idCustomer],
                ['contenu' => $content]
            );

            return response()->json([
                'status' => 201,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeBySection(SectionRequest $request)
    {
        $table = $request->section;
        $content = $request->content;

        $allowedTables = [
            'reglement',
            'accueil',
            'conditions',
            'acces',
            'accompagnement',
        ];

        if (! in_array($table, $allowedTables, true)) {
            return response()
                ->json([
                    'status'  => 'error',
                    'message' => 'Section invalide.',
                ], 400);
        }

        $message = $this->getMessage($table);

        return $this->updateCustomerContent($table, $content, $message);
    }


    private function getMessage($table)
    {
        $messages = [
            'reglement'       => 'Règlement mis à jour avec succès.',
            'accueil'         => 'Livret d’accueil mis à jour avec succès.',
            'conditions'      => 'Conditions générales de vente mises à jour avec succès.',
            'acces'           => 'Modalités d’accès mises à jour avec succès.',
            'accompagnement'  => 'Modalités de suivi et d’accompagnement mises à jour avec succès.',
        ];

        return $messages[$table] ?? 'Section mise à jour avec succès.';
    }

    // Ajouter ou mettre à jour le règlement
    public function storeReglement($idCustomer, $content)
    {
        return $this->updateCustomerContent('reglement', $idCustomer, $content, 'Règlement mis à jour avec succès.');
    }

    // Ajouter ou mettre à jour le livret d'accueil
    public function storeAccueil($idCustomer, $content)
    {
        return $this->updateCustomerContent('accueil', $idCustomer, $content, 'Livret d’accueil mis à jour avec succès.');
    }

    // Ajouter ou mettre à jour les conditions générales de vente
    public function storeConditions($idCustomer, $content)
    {
        return $this->updateCustomerContent('conditions', $idCustomer, $content, 'Conditions générales de vente mises à jour avec succès.');
    }

    // Ajouter ou mettre à jour les modalités d'accès
    public function storeAcces($idCustomer, $content)
    {
        return $this->updateCustomerContent('acces', $idCustomer, $content, 'Modalités d’accès mises à jour avec succès.');
    }

    // Ajouter ou mettre à jour les modalités de suivi et d'accompagnement
    public function storeAccompagnement($idCustomer, $content)
    {
        return $this->updateCustomerContent('accompagnement', $idCustomer, $content, 'Modalités de suivi et d’accompagnement mises à jour avec succès.');
    }


    public function storeOrganigramme(Request $request)
    {
        ini_set('upload_max_filesize', '5M');
        ini_set('post_max_size', '50M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');

        $driver = new Driver();
        $manager = new ImageManager($driver);

        $validate = Validator::make($request->all(), [
            'photo' => 'required|image|max:5120',
            'customer_id' => 'exists:customers,idCustomer'
        ]);

        if ($validate->fails()) {
            return back()->with(['error' => $validate->messages()]);
        }

        $file = $request->photo;
        $idCustomer = Customer::idCustomer();
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if ($file) {
            if ($file->getSize() > $maxFileSize) {
                return back()->with(['error' => 'L\'un des fichiers est trop grand. La taille maximale autorisée est de 5 MB par fichier.']);
            }

            try {
                $existingImage = DB::table('marketplace_images')
                    ->where('idCustomer', $idCustomer)
                    ->where('path', 'like', 'img/marketorg%')
                    ->first();

                if ($existingImage) {
                    // Supprimez l'ancien fichier du stockage
                    Storage::disk('do')->delete($existingImage->path);
                }

                // Traitez et enregistrez le nouveau fichier
                $image = $manager->read($file)->toWebp(25);

                $disk = Storage::disk('do');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
                $path = 'img/marketorg/' . $idCustomer . '/' . $filename;

                $disk->put($path, $image->__toString());

                $url = $disk->url($path);

                // Mettez à jour ou insérez le fichier dans la base de données
                DB::table('marketplace_images')->updateOrInsert(
                    ['idCustomer' => $idCustomer],
                    ['url' => $url, 'path' => $path]
                );

                return response()->json([
                    'status' => 201,
                    'message' => 'Image mise à jour avec succès.',
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur lors du traitement de l\'image : ' . $e->getMessage(), [
                    'file' => $file->getClientOriginalName(),
                ]);

                return response()->json([
                    'status' => 500,
                    'message' => 'Erreur lors du traitement de l\'image',
                ]);
            }
        }
    }



    public function storePictures(Request $request)
    {
        ini_set('upload_max_filesize', '5M');
        ini_set('post_max_size', '50M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');

        $validator = Validator::make($request->all(), [
            'photos.*' => 'required|image|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $files = $request->file('photos');
        $idCustomer = Customer::idCustomer();
        $storedImages = [];

        if (!$files || !is_array($files)) {
            return response()->json([
                'status' => 400,
                'message' => 'Aucun fichier n’a été téléchargé.',
            ], 400);
        }

        try {
            $manager = new ImageManager(new Driver());
            $disk = Storage::disk('do');

            foreach ($files as $file) {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
                $path = 'img/marketpicture/' . $idCustomer . '/' . $filename;

                // $webpImage = $manager->read($file)->toWebp(25);
                $webpImage = $manager->read($file->getRealPath())->toWebp(25);
                // $disk->put($path, $webpImage->__toString());
                $disk->put($path, $webpImage->toString());

                $url = $disk->url($path);

                $id = DB::table('marketplace_images')->insertGetId([
                    'idCustomer' => $idCustomer,
                    'url' => $url,
                    'path' => $path,
                ]);

                $storedImages[] = [
                    'id' => $id,
                    'url' => $url
                ];
            }

            return response()->json([
                'status' => 201,
                'message' => 'Images ajoutées avec succès.',
                'images' => $storedImages,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement des images : ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue lors de l’envoi des images.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $image = DB::table('marketplace_images')
            ->where('id', $id)
            ->first();

        if ($image) {
            $filePath = $image->path;

            Storage::disk('do')->delete($filePath);

            DB::table('marketplace_images')
                ->where('id', $id)
                ->delete();

            return response()->json('success', 201);
        } else {
            return response()->json('error', 400);
        }
    }

    public function getInfoProfil()
    {
        $idCustomer = Customer::idCustomer();
        return response()->json(
            [
                'reglement' => DB::table('reglement')->where('idCustomer', $idCustomer)->exists(),
                'accueil' => DB::table('accueil')->where('idCustomer', $idCustomer)->exists(),
                'conditions' => DB::table('conditions')->where('idCustomer', $idCustomer)->exists(),
                'reasons' => DB::table('reasons')->where('idCustomer', $idCustomer)->exists(),
                'traits' => DB::table('traits')->where('idCustomer', $idCustomer)->exists(),
                'photo' => DB::table('marketplace_images')->where('idCustomer', $idCustomer)->where('path', 'like', "img/marketpicture%")->exists(),
                'acces' => DB::table('acces')->where('idCustomer', $idCustomer)->exists(),
                'accompagnement' => DB::table('accompagnement')->where('idCustomer', $idCustomer)->exists(),
                'organigramme' => DB::table('marketplace_images')->where('idCustomer', $idCustomer)->where('path', 'like', "img/marketorg%")->exists(),
            ]
        );
    }
}
