<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ImageService;
use App\Traits\CheckQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class GalleryController extends Controller
{

    use CheckQuery;

    protected $img;

    public function __construct(ImageService $img)
    {
        $this->img = $img;
    }

    public function addImageGallery(Request $request, $idProjet)
    {
        // Configuration pour les fichiers lourds
        ini_set('upload_max_filesize', '50M');
        ini_set('post_max_size', '55M');
        ini_set('memory_limit', '256M');
        set_time_limit(600);

        $request->validate([
            'idProjet' => 'required|integer',
            'photo' => 'required|string', // En réalité, c'est le fichier en base64 ou data URL
            'description' => 'nullable|string',
        ]);

        $idProjet = $request->idProjet;
        $description = $request->description ?? '';
        $dataUrl = $request->photo;

        // Détecter le type de média
        $mediaType = $this->detectMediaType($dataUrl);

        if (!$mediaType) {
            return response()->json([
                'status' => 400,
                'message' => 'Format de média non supporté ou invalide'
            ]);
        }

        try {
            if ($mediaType === 'image') {
                return $this->handleImageUpload($dataUrl, $idProjet, $description);
            } elseif ($mediaType === 'video') {
                return $this->handleVideoUpload($dataUrl, $idProjet, $description);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors du traitement du média: ' . $e->getMessage()
            ]);
        }
    }

    private function detectMediaType($dataUrl)
    {
        // Vérifier si c'est un data URL
        if (strpos($dataUrl, 'data:') === 0) {
            $parts = explode(';', $dataUrl);

            if (count($parts) > 0) {
                $mimePart = $parts[0];

                if (strpos($mimePart, 'image/') !== false) {
                    return 'image';
                } elseif (strpos($mimePart, 'video/') !== false) {
                    return 'video';
                }
            }
        }
        // Sinon, on suppose que c'est du base64 pur, on vérifie par l'extension du nom de fichier
        elseif ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $mimeType = $file->getMimeType();

            if (strpos($mimeType, 'image/') === 0) {
                return 'image';
            } elseif (strpos($mimeType, 'video/') === 0) {
                return 'video';
            }
        }

        return null;
    }

    private function handleImageUpload($dataUrl, $idProjet, $description)
    {
        // Extraire le base64 du data URL
        $base64 = $dataUrl;
        if (strpos($dataUrl, 'base64,') !== false) {
            $base64 = explode('base64,', $dataUrl)[1];
        }

        // Décoder l'image base64
        $imageData = base64_decode($base64);
        if ($imageData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Image base64 invalide'
            ]);
        }

        // Détecter le type MIME original
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        // Créer le manager d'image
        $driver = new Driver();
        $manager = new ImageManager($driver);

        try {
            $image = $manager->read($imageData);

            // Convertir en WebP avec compression
            $convertedImage = $image->toWebp(25);
            $webpData = $convertedImage->__toString();

            // Stocker sur DigitalOcean
            $disk = Storage::disk('do');
            $filename = uniqid() . '.webp';
            $path = 'img/momentum/' . $idProjet . '/' . $filename;

            return DB::transaction(function () use ($disk, $path, $webpData, $idProjet, $filename, $description, $mimeType) {
                $disk->put($path, $webpData, 'public');
                $url = $disk->url($path);

                // Déterminer l'owner
                $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;
                $idOwner = $this->determineOwner($roleId);

                // Sauvegarder en base de données
                $this->img->store($idProjet, $url, $path, $filename, $description, $idOwner, $mimeType, 'image');

                return response()->json([
                    'status' => 200,
                    'message' => 'Image téléchargée avec succès',
                    'imageName' => $filename,
                    'mediaType' => 'image'
                ]);
            });
        } catch (\Exception $e) {
            // En cas d'erreur avec WebP, essayer de sauvegarder l'original
            return $this->saveOriginalImage($imageData, $idProjet, $description, $mimeType);
        }
    }

    private function handleVideoUpload($dataUrl, $idProjet, $description)
    {
        // Extraire le base64 du data URL
        $base64 = $dataUrl;
        if (strpos($dataUrl, 'base64,') !== false) {
            $base64 = explode('base64,', $dataUrl)[1];
        }

        // Décoder la vidéo base64
        $videoData = base64_decode($base64);
        if ($videoData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Vidéo base64 invalide'
            ]);
        }

        // Détecter le type MIME original
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $videoData);
        finfo_close($finfo);

        // Vérifier si c'est déjà un MP4
        $extension = $this->getExtensionFromMime($mimeType);

        // Si ce n'est pas un MP4, on peut choisir de le convertir ou de garder l'original
        // Pour l'exemple, on garde l'original avec sa bonne extension
        if (!$extension) {
            $extension = 'mp4'; // Par défaut
        }

        $disk = Storage::disk('do');
        $filename = uniqid() . '.' . $extension;
        $path = 'img/momentum/' . $idProjet . '/' . $filename;

        return DB::transaction(function () use ($disk, $path, $videoData, $idProjet, $filename, $description, $mimeType) {
            $disk->put($path, $videoData, 'public');
            $url = $disk->url($path);

            // Déterminer l'owner
            $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;
            $idOwner = $this->determineOwner($roleId);

            // Sauvegarder en base de données
            $this->img->store($idProjet, $url, $path, $filename, $description, $idOwner, 'video');

            return response()->json([
                'status' => 200,
                'message' => 'Vidéo téléchargée avec succès',
                'videoName' => $filename,
                'mediaType' => 'video'
            ]);
        });
    }

    private function saveOriginalImage($imageData, $idProjet, $description, $mimeType)
    {
        $extension = $this->getExtensionFromMime($mimeType);
        if (!$extension) {
            $extension = 'jpg'; // Extension par défaut
        }

        $disk = Storage::disk('do');
        $filename = uniqid() . '.' . $extension;
        $path = 'img/momentum/' . $idProjet . '/' . $filename;

        return DB::transaction(function () use ($disk, $path, $imageData, $idProjet, $filename, $description, $mimeType) {
            $disk->put($path, $imageData, 'public');
            $url = $disk->url($path);

            $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;
            $idOwner = $this->determineOwner($roleId);

            $this->img->store($idProjet, $url, $path, $filename, $description, $idOwner, 'image');

            return response()->json([
                'status' => 200,
                'message' => 'Image originale téléchargée avec succès',
                'imageName' => $filename,
                'mediaType' => 'image'
            ]);
        });
    }

    /**
     * Obtient l'extension de fichier à partir du type MIME
     */
    private function getExtensionFromMime($mimeType)
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'video/mp4' => 'mp4',
            'video/avi' => 'avi',
            'video/mov' => 'mov',
            'video/wmv' => 'wmv',
            'video/flv' => 'flv',
            'video/webm' => 'webm',
            'video/ogg' => 'ogv',
        ];

        return $mimeToExt[$mimeType] ?? null;
    }

    /**
     * Détermine l'owner en fonction du rôle
     */
    private function determineOwner($roleId)
    {
        if ($roleId == 3 || $roleId == 6) {
            return Customer::idCustomer();
        } elseif ($roleId == 4 || $roleId == 5) {
            return Auth::user()->id;
        }

        return null;
    }

    public function getAllFolder($year)
    {
        $folders = DB::table('dossiers')
            ->select('idDossier', 'nomDossier')
            ->where('idCfp', Customer::idCustomer())
            ->whereYear('created_at', $year)
            ->orderBy('nomDossier')
            ->get();

        $data = [];

        foreach ($folders as $folder) {
            $idDossier = $folder->idDossier;

            // Récupérer minDate et maxDate pour chaque dossier
            $minAndMaxDate = DB::table('projets')
                ->select(DB::raw('MIN(dateDebut) as minDate'), DB::raw('MAX(dateFin) as maxDate'))
                ->where('idDossier', $idDossier)
                ->first();

            $data[] = [
                'idDossier' => $idDossier,
                'nomDossier' => $folder->nomDossier,
                'image' => $this->getFirstImageFolder($idDossier),
                'countImage' => $this->countImageByFolder($idDossier),
                'countProject' => $this->countProjectByFolder($idDossier),
                'minDate' => $this->formatDate($minAndMaxDate->minDate),
                'maxDate' => $this->formatDate($minAndMaxDate->maxDate),
            ];
        }

        if (count($folders) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $data,
            'year' => $year
        ]);
    }

    public function getProjectFolder($idDossier)
    {
        $projectQuery = DB::table('projets as P')
            ->select('P.dateDebut', 'P.dateFin', 'M.moduleName', 'V.ville_name', 'P.idProjet')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('ville_codeds as V', 'V.id', 'P.idVilleCoded')
            ->where('P.idDossier', $idDossier)
            ->get();

        // Ajouter le nombre d'images à chaque projet et formater les dates
        $projectQuery->transform(function ($projet) {
            $projet->dateDebut = $this->formatDate($projet->dateDebut);
            $projet->dateFin = $this->formatDate($projet->dateFin);
            $projet->image = $this->getFirstImageProject($projet->idProjet);
            $projet->countImage = $this->countImageByProject($projet->idProjet);
            return $projet;
        });

        $dossier = DB::table('dossiers')
            ->select('idDossier', 'nomDossier', DB::raw('YEAR(created_at) as year'))
            ->where('idDossier', $idDossier)
            ->first();

        $minAndMaxDate = DB::table('projets')
            ->select(DB::raw('MIN(dateDebut) as minDate'), DB::raw('MAX(dateFin) as maxDate'))
            ->where('idDossier', $dossier->idDossier)
            ->first();

        return response()->json([
            'projets' => $projectQuery,
            'year' => $dossier->year,
            'nomDossier' => $dossier->nomDossier,
            'minDate' => $this->formatDate($minAndMaxDate->minDate),
            'maxDate' => $this->formatDate($minAndMaxDate->maxDate),
        ]);
    }
    public function getAllFolderOrder($year)
    {
        $folders = DB::table('dossiers')
            ->select('idDossier', 'nomDossier')
            ->where('idCfp', Customer::idCustomer())
            ->whereYear('created_at', $year)
            ->orderByDesc('created_at')
            ->get();

        $data = [];

        foreach ($folders as $folder) {
            $idDossier = $folder->idDossier;
            $data[] = [
                'idDossier' => $idDossier,
                'nomDossier' => $folder->nomDossier,
                'image' => $this->getFirstImageFolder($idDossier),
                'countImage' => $this->countImageByFolder($idDossier)
            ];
        }

        if (count($folders) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'data' => $data,
            'year' => $year
        ]);
    }

    private function getFirstImageFolder($id)
    {
        $projectIds = $this->getProjectByFolder($id);

        $image = DB::table('images')->select('url')->whereIn('idProjet', $projectIds)->first();

        return $image->url ?? null;
    }

    private function getFirstImageProject($id)
    {
        $image = DB::table('images')->select('url')->where('idProjet', $id)->first();

        return $image->url ?? null;
    }

    public function getAllGallery()
    {
        $data = DB::table('dossiers')
            ->select(DB::raw('YEAR(created_at) as year'))
            ->where('idCfp', Customer::idCustomer())
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($data) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    private function countImageByFolder($id)
    {
        $projectIds = $this->getProjectByFolder($id);

        $imageCount = DB::table('images')->select('idImages')->whereIn('idProjet', $projectIds)->get();

        return count($imageCount);
    }

    private function countProjectByFolder($id)
    {
        $projectIds = $this->getProjectByFolder($id);

        return count($projectIds);
    }



    private function countImageByProject($id)
    {
        return DB::table('images')->where('idProjet', $id)->count();
    }

    public function getGalleryByFolder($idDossier)
    {
        $projectIds = $this->getProjectByFolder($idDossier);

        $data = [];

        foreach ($projectIds as $projectId) {
            $data[] = [
                $this->getGaleryByProject($projectId)
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }

    private function getProjectByFolder($id)
    {
        $projects = DB::table('projets')
            ->where('idDossier', $id)
            ->pluck('idProjet');
        return $projects;
    }

    private function getGaleryByProject($id)
    {
        $project = $this->getProject($id);
        $images = $this->getImageByProject($id);
        $result = [
            'moduleName' => $project->moduleName,
            'dateDebut' => $project->dateDebut,
            'dateFin' => $project->dateFin,
            'ville' => $project->ville_name,
            'imageCount' => count($images),
            'images' => $images
        ];
        return $result;
    }

    private function getProject($id)
    {
        $project = DB::table('projets as P')
            ->select('P.dateDebut', 'P.dateFin', 'M.moduleName', 'V.ville_name')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('ville_codeds as V', 'V.id', 'P.idVilleCoded')
            ->where('P.idProjet', $id)
            ->first();
        return $project;
    }

    private function getImageByProject($id)
    {
        $images = DB::table('images')
            ->select('url')
            ->where('idProjet', $id)
            ->get();
        return $images;
    }

    public function allImage($idProjet)
    {
        // Récupérer les images associées au projet
        $images = DB::table('images')
            ->leftJoin('users', 'users.id', '=', 'images.id_added_by')
            ->select(
                'images.url',
                'images.idImages',
                'images.description',
                'images.idProjet',
                'images.id_added_by',
                'images.mediaType',
                'images.created_at',
                DB::raw("COALESCE(users.name, 'Inconnu') as nomAddedBy")
            )
            ->where('idProjet', $idProjet)
            ->get();

        // Récupérer les informations du projet
        $projet = DB::table('projets')
            ->select(
                'dossiers.idDossier',
                'projets.dateDebut as minDate',
                'projets.dateFin as maxDate',
                'mdls.moduleName',
                'projets.idProjet'
            )
            ->where('projets.idProjet', $idProjet)
            ->join('dossiers', 'dossiers.idDossier', '=', 'projets.idDossier')
            ->join('mdls', 'mdls.idModule', '=', 'projets.idModule')
            ->first();

        // Vérifier si le projet existe
        if (!$projet) {
            return response()->json(['error' => 'Projet introuvable'], 404);
        }

        // Formater les dates
        $minDate = $this->formatDate($projet->minDate);
        $maxDate = $this->formatDate($projet->maxDate);

        return response()->json([
            'data' => $images,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            'nomProjet' => $projet->moduleName,
            'idDossier' => $projet->idDossier,
            'idProjet' => $projet->idProjet
        ]);
    }

    private function formatDate($date)
    {
        return $date ? Carbon::parse($date)->translatedFormat('d M Y') : null;
    }
}
