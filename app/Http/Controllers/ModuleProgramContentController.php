<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ModuleProgramContentable;
use Illuminate\Http\Request;

class ModuleProgramContentController extends Controller
{
    use ModuleProgramContentable;
    public function index($idProgram)
    {
        $contents = $this->getContents($idProgram)->orderBy('description', 'asc');

        if (count($contents->get()) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'program_contents_count' => count($contents->get()),
                'program_contents' => $contents->get()
            ]);
        }
    }

    public function store(Request $req, $idProgram)
    {
        $req->validate([
            'content_description' => 'required|min:2|max:255'
        ]);

        $newContent = $this->saveContent($idProgram, $req->content_title, $req->content_description);

        return response()->json([
            'status' => 200,
            'message' => 'Succès',
            'newContent' => $newContent
        ]);
    }

    public function show($idProgram, $idContent)
    {
        $content = $this->getContent($idProgram, $idContent);

        if (!$content->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'program_content' => $content->first()
            ]);
        }
    }

    public function update(Request $req, $idProgram, $idContent)
    {
        $req->validate([
            'content_description' => 'required|min:2|max:255'
        ]);

        $content = $this->getContent($idProgram, $idContent);

        if (!$content->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        } else {
            $content->update([
                'title' => $req->content_title,
                'description' => $req->content_description
            ]);

            return response()->json([
                'status' => 200,
                'message' => "Succès"
            ]);
        }
    }

    public function destroy($idProgram, $idContent)
    {
        $content = $this->getContent($idProgram, $idContent);

        if (!$content->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        } else {
            $content->delete();

            return response()->json([
                'status' => 200,
                'message' => "Suppression avec Succès"
            ]);
        }
    }
}
  