<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageRequest;
use App\Http\Requests\ImageUpdateRequest;
use App\Models\Customer;
use App\Services\ImageService;
use App\Traits\CheckQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    use CheckQuery;

    protected $img;

    public function __construct(ImageService $img)
    {
        $this->img = $img;
    }

    public function index($idProjet)
    {
        $images = $this->img->index($idProjet);
        return response()->json([
            'status' => 200,
            'count_images' => count($images),
            'images' => $images
        ]);
    }

    public function store(Request $request)
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

    /**
     * Détecte le type de média à partir du data URL
     */
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

    /**
     * Gère l'upload d'image avec conversion en WebP
     */
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

    /**
     * Gère l'upload de vidéo avec conversion en MP4 si nécessaire
     */
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

    /**
     * Sauvegarde l'image originale sans conversion
     */
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

    public function update(ImageUpdateRequest $req, $id)
    {
        $image = $this->img->show($id);

        if ($image->exists()) {
            $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;

            if ($roleId == 3 || $roleId == 6) {
                // CFP ou ETP
                $idOwner = Customer::idCustomer();
            } elseif ($roleId == 4 || $roleId == 5) {
                // Apprenants ou Formateurs
                $idOwner = Auth::user()->id;
            }

            $this->img->update($id, $idOwner, null, $req->validated()['description']);

            return response()->json([
                'status' => 200,
                'message' => 'Image modifiée avec succès.'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ]);
        }
    }

    public function destroy($id)
    {
        $image = $this->img->show($id);

        if ($image->exists()) {
            $filePath = $image->first()->path;

            DB::transaction(function () use ($filePath, $id) {
                $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;

                if ($roleId == 3 || $roleId == 6) {
                    // CFP ou ETP
                    $idOwner = Customer::idCustomer();
                } elseif ($roleId == 4) {
                    // Apprenants
                    $idOwner = Auth::user()->id;
                }

                Storage::disk('do')->delete($filePath);
                $this->img->delete($id, $idOwner);
            });

            return response()->json([
                'status' => 200,
                'message' => 'Image supprimée avec succès.'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }
}
