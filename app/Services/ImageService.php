<?php

namespace App\Services;

use App\Interfaces\ImageInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImageService implements ImageInterface
{
    public function index($idProjet): mixed
    {
        $images = DB::table('v_images')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        $data = [];

        foreach ($images as $image) {
            $data[] = [
                'idImages' => $image->idImages,
                'idProjet' => $image->idProjet,
                'img_url' => $image->img_url,
                'img_path' => $image->img_path,
                'img_description' => $image->img_description,
                'role_name_adder' => $image->role_name_adder,
                'is_added' => ($image->id_added_by === Auth::user()->id) ? true : false,
            ];
        }


        return $data;
    }

    public function store($idProjet, $url, $path, $fileName, $description, $idAddedBy, $mediaType): void
    {
        DB::table('images')->insert([
            'idTypeImage' => 1,
            'idProjet' => $idProjet,
            'url' => $url,
            'path' => $path,
            'nomImage' => $fileName,
            'description' => $description,
            'id_added_by' => $idAddedBy,
            'mediaType' => $mediaType
        ]);
    }

    public function show($id): mixed
    {
        $image = DB::table('images')
            ->select('url', 'idImages', 'path', 'description', 'idProjet', 'id_added_by')
            ->where('idImages', $id);

        return $image;
    }

    public function delete($idImage, $idAddedBy): void
    {
        DB::table('images')
            ->where('idImages', $idImage)
            ->where('id_added_by', $idAddedBy)
            ->delete();
    }

    public function update($idImage, $idAddedBy, $name = null, $description = null): void
    {
        DB::table('images')
            ->where('idImages', $idImage)
            ->where('id_added_by', $idAddedBy)
            ->update([
                'nomImage' => $name,
                'description' => $description
            ]);
    }
}
