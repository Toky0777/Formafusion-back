<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ContentRefreshController extends Controller
{
    // Liste des contenus d’un module (avec fichiers inclus)
    public function index($idModuleContent)
    {
        $contents = DB::table('content_refresh')
            ->where('idModuleContent', $idModuleContent)
            ->get();

        // Récupérer fichiers si type=file
        foreach ($contents as $content) {
            if ($content->contentType === 'file') {
                $content->files = DB::table('content_files')
                    ->where('idContent', $content->idContent)
                    ->get();
            }
        }

        return response()->json($contents);
    }

    // Créer un contenu


    public function store(Request $request)
    {
        // Validation
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'videoLink' => 'nullable|string',
            'videoDescription' => 'nullable|string',
            'texte' => 'nullable|string',
            'idModuleContent' => 'required|integer|exists:module_program_contents,id',
            'files' => 'array|nullable',
            'files.*.fileName' => 'nullable|string|max:200',
            'files.*.filePath' => 'nullable|string',
            'files.*.fileDescription' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Insérer le contenu principal
            $idContent = DB::table('content_refresh')->insertGetId([
                'title' => $data['title'] ?? null,
                'videoLink' => $data['videoLink'] ?? null,
                'videoDescription' => $data['videoDescription'] ?? null,
                'texte' => $data['texte'] ?? null,
                'idModuleContent' => $data['idModuleContent'],
            ]);

            // Gestion des fichiers si présents (URLs)
            if (!empty($data['files'])) {
                foreach ($data['files'] as $file) {
                    DB::table('content_files')->insert([
                        'idContent' => $idContent,
                        'fileName' => $file['fileName'] ?? null,
                        'filePath' => $file['filePath'] ?? null,
                        'fileDescription' => $file['fileDescription'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Contenu enregistré avec succès',
                'idContent' => $idContent
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // Récupérer un contenu
    public function show($idModuleContent)
    {
        $content = DB::table('content_refresh')
            ->where('idModuleContent', $idModuleContent)
            ->where('isDelete', 0)
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Contenu introuvable' . $idModuleContent], 404);
        }

        $files = DB::table('content_files')
            ->select('idFile', 'fileName', 'filePath', 'fileDescription')
            ->join('content_refresh', 'content_files.idContent', '=', 'content_refresh.idContent')
            ->where('content_refresh.idContent', $content->idContent)
            ->where('isDelete', 0)
            ->get();

        return response()->json([
            'content' => $content,
            'files' => $files
        ]);
    }

    public function edit($idModuleContent)
    {
        try {
            // Récupérer le contenu principal (même logique que show)
            $content = DB::table('content_refresh')
                ->where('idModuleContent', $idModuleContent)
                ->where('isDelete', 0)
                ->first();

            if (!$content) {
                return response()->json(['message' => 'Contenu introuvable'], 404);
            }

            // Récupérer les fichiers associés (même logique que show avec JOIN)
            $files = DB::table('content_files')
                ->select('idFile', 'fileName', 'filePath', 'fileDescription')
                ->join('content_refresh', 'content_files.idContent', '=', 'content_refresh.idContent')
                ->where('content_refresh.idContent', $content->idContent)
                ->where('isDelete', 0) // Préciser la table pour éviter l'ambiguïté
                ->get();

            return response()->json([
                'content' => $content,
                'files' => $files
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }
    // Mettre à jour un contenu


    public function update(Request $request, $idContent)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'videoLink' => 'nullable|string',
            'videoDescription' => 'nullable|string',
            'texte' => 'nullable|string',
            'idModuleContent' => 'required|integer|exists:module_program_contents,id',
            'files' => 'array|nullable',
            'files.*.fileName' => 'nullable|string|max:200',
            'files.*.filePath' => 'nullable|string',
            'files.*.fileDescription' => 'nullable|string',
        ]);



        DB::beginTransaction();
        try {
            $updated = DB::table('content_refresh')
                ->where('idContent', $idContent)
                ->update([
                    'title' => $data['title'] ?? null,
                    'videoLink' => $data['videoLink'] ?? null,
                    'videoDescription' => $data['videoDescription'] ?? null,
                    'texte' => $data['texte'] ?? null,
                    'idModuleContent' => $data['idModuleContent'],
                ]);

            // if (!$updated) {
            //     return response()->json(['message' => 'Contenu introuvable'], 404);
            // }

            // Supprimer les anciens fichiers et réinsérer les nouveaux
            DB::table('content_files')->where('idContent', $idContent)->delete();

            if (!empty($data['files'])) {
                foreach ($data['files'] as $file) {
                    DB::table('content_files')->insert([
                        'idContent' => $idContent,
                        'fileName' => $file['fileName'] ?? null,
                        'filePath' => $file['filePath'] ?? null,
                        'fileDescription' => $file['fileDescription'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Contenu mis à jour avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // Ajouter un fichier à un contenu existant
    public function addFile(Request $request, $idContent)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10 Mo
            'fileDescription' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $path = Storage::disk('do')->put('content-files', $request->file('file'));

            $idFile = DB::table('content_files')->insertGetId([
                'idContent' => $idContent,
                'fileName' => $request->file('file')->getClientOriginalName(),
                'filePath' => $path,
                'fileDescription' => $request->fileDescription,
            ]);

            return response()->json([
                'message' => 'Fichier ajouté',
                'idFile' => $idFile,
                'url' => Storage::disk('do')->url($path)
            ], 201);
        }

        return response()->json(['message' => 'Aucun fichier envoyé'], 400);
    }

    // Supprimer un fichier
    public function deleteFile($idFile)
    {
        // On récupère le fichier
        $file = DB::table('content_files')->where('idFile', $idFile)->first();

        if (!$file) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        try {
            // Suppression du fichier dans Spaces
            if ($file->filePath) {
                Storage::disk('do')->delete($file->filePath);
            }

            // Suppression dans la base
            DB::table('content_files')->where('idFile', $idFile)->delete();

            return response()->json(['message' => 'Fichier supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // Supprimer un contenu


    public function destroy($id)
    {
        $deleted = DB::table('content_refresh')
            ->where('idContent', $id)
            ->update([
                'isDelete' => 1
            ]);

        if (!$deleted) {
            return response()->json(['message' => 'Contenu introuvable'], 404);
        }

        return response()->json(['message' => 'Contenu supprimé avec succès']);
    }
    
}
