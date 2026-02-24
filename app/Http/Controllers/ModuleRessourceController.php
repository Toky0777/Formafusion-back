<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModuleRessource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ModuleRessourceController extends Controller
{
    public function index($id)
    {
        $ressoures = DB::table('module_ressources')->where('idModule', $id)->get();
        return response()->json($ressoures);
    }

    public function store(Request $req, int $idModule)
    {
        $validate = Validator::make($req->all(), [
            'module_ressource_file' => 'required|max:50000|not_in:exe,bat,sh,msi,cmd',
        ]);

        if ($validate->fails()) {
            return back()->with('error', $validate->messages());
        } else {
            if ($req->hasFile('module_ressource_file')) {
                try {
                    $module = DB::table('mdls')->select('idModule')->where('idModule', $idModule)->first();

                    $files = $req->module_ressource_file;
                    $all_files = [];

                    foreach ($files as $key => $file) {
                        $fileSize[$key] = $file->getSize();
                        $fileSizeInMb[$key] = round($fileSize[$key] / (1024 * 1024), 2);
                        $fileName[$key] = $file->getClientOriginalName();
                        $fileExtension[$key] = $file->getClientOriginalExtension();

                        $disk = Storage::disk('public');
                        $path = $disk->putFile('ressource/projet/' . $idModule, $file);

                        $all_files[] = [
                            'module_ressource_name' => $fileName[$key],
                            'module_ressource_extension' => $fileExtension[$key],
                            'taille' => $fileSizeInMb[$key],
                            'idModule' => $module->idModule,
                            'file_path' => $path
                        ];
                    }

                    DB::table('module_ressources')->insert($all_files);

                    return response()->json([
                        'status' => 200,
                        'message' => 'Fichier Importé(s) avec succès',
                        'files' => $all_files
                    ]);
                } catch (Exception $e) {
                    return response()->json([
                        'status' => 400,
                        'message' => "Ajout impossible !"
                    ]);
                }
            }
        }
    }

    public function destroy(int $idModuleRessource)
    {
        try {
            $file = DB::table('module_ressources')
                ->select('file_path', 'module_ressource_name')
                ->where('idModuleRessource', $idModuleRessource)
                ->first();

            if ($file) {
                $disk = Storage::disk('public');
                DB::transaction(function () use ($disk, $file, $idModuleRessource) {
                    if ($disk->exists($file->file_path)) {
                        $disk->delete($file->file_path);
                    }

                    DB::table('module_ressources')->where('idModuleRessource', $idModuleRessource)->delete();
                });

                return response()->json([
                    'status' => 200,
                    'message' => "Fichier '" . $file->module_ressource_name . "' supprimé avec succès"
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Fichier introuvable dans la base de données" 
                ]);
            }
        } catch (Exception $e) {
            Log::error("Erreur lors de la suppression : " . $e->getMessage());
            return response()->json([
                'status' => 400,
                'message' => "Erreur inconnue lors de la suppression",
                'error' => $e->getMessage()
            ]);
        }
    }

    public function download(int $idModuleRessource)
    {
        $file = DB::table('module_ressources')
            ->select('file_path', 'module_ressource_name')
            ->where('idModuleRessource', $idModuleRessource)
            ->first();

        if (!$file) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($file->file_path)) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        $absolutePath = $disk->path($file->file_path);
        $mime = $disk->mimeType($file->file_path);

        // ✅ Supprimer le 4ᵉ paramètre Response::HTTP_OK
        return response()->download(
            $absolutePath,
            $file->module_ressource_name,
            ['Content-Type' => $mime]
        );
    }


    public function countByModule($idModule)
    {
        $count = ModuleRessource::countByModuleId($idModule);
        return response()->json([
            'status' => 200,
            'idModule' => $idModule,
            'count' => $count
        ]);
    }
}
