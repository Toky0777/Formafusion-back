<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class QcmImages extends Model
{
    use HasFactory;

    protected $table = "images_qcm";
    protected $primaryKey = "idImageQ";

    protected $fillable = [
        'idTypeImage',
        'url',
        'nomImage',
        'path',
        'created_at',
        'updated_at',
    ];

    // Relation avec le modèle QcmQuestions
    public function question()
    {
        return $this->belongsTo(QcmQuestions::class, 'idImageQ', 'idImageQ');
    }

    /**
     * Enregistrer une image sur le serveur (do) et dans la base de données (v2)
     * 
     * @param Request $request
     * @param int $idQcm
     * @param int $idQuestion
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadQuestionPhoto(Request $request, $idQuestion)
    {
        // Ajuster les paramètres PHP
        ini_set('upload_max_filesize', '5M');
        ini_set('post_max_size', '50M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');

        $driver = new Driver();
        $manager = new ImageManager($driver);

        $validate = Validator::make($request->all(), [
            'myFile.*' => 'required|image|max:5120', // Validation Laravel (taille en KB)
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()], 401);
        }

        $files = $request->file('myFile');
        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        $urls = [];

        if ($files) {
            foreach ($files as $file) {
                if ($file->getSize() > $maxFileSize) {
                    return response()->json(['error' => 'La taille de l\'image ne doit pas dépasser 5 Mo.'], 401);
                }

                try {
                    $image = $manager->read($file)->toWebp(25); // Convertir l'image en WebP

                    $disk = Storage::disk('do'); // Utiliser le disque DigitalOcean
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp'; // Nom du fichier
                    $path = 'questionImg/' . $idQuestion . '/' . $filename; // Chemin du fichier

                    $disk->put($path, $image->__toString()); // Enregistrer l'image sur le disque

                    $url = $disk->url($path); // URL de l'image
                    $urls[] = $url; // Ajouter l'URL dans le tableau

                    // v2
                    // Enregistrer l'image dans la base de données
                    $imageModel = QcmImages::create([
                        'idTypeImage' => 2, // ID du type d'image : questionImg
                        'url' => $url,
                        'nomImage' => $filename,
                        'path' => $path,
                    ]);

                    $imageId = $imageModel->idImageQ; // Récupérer l'ID de l'image

                    // Mettre à jour la question avec l'ID de l'image
                    QcmQuestions::where('idQuestion', $idQuestion)->update(['idImageQ' => $imageId]);
                    // v2
                } catch (Exception $e) {
                    Log::error('Erreur lors du traitement de l\'image : ' . $e->getMessage(), [
                        'file' => $file->getClientOriginalName(),
                        'idQuestion' => $idQuestion
                    ]);

                    return response()->json(['error' => 'Une erreur est survenue lors du traitement de l\'image.'], 500);
                }
            }

            return response()->json(['success' => 'Les images ont été enregistrées avec succès.', 'urls' => $urls], 200);
        }
    }

    /**
     * Mettre à jour une image de question
     * 
     * @param Request $request
     * @param int $idImageQ
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateQuestionPhoto(Request $request, $idImageQ)
    {
        $image = QcmImages::find($idImageQ);
        if (!$image) {
            return response()->json(['error' => 'Image introuvable'], 404);
        }

        $validate = Validator::make($request->all(), [
            'myFile' => 'required|image|max:5120', // Validation Laravel (taille en KB)
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()], 401);
        }

        $file = $request->file('myFile');
        $driver = new Driver();
        $manager = new ImageManager($driver);

        try {
            $imageWebp = $manager->read($file)->toWebp(25); // Convertir l'image en WebP

            $disk = Storage::disk('do'); // Utiliser le disque DigitalOcean
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp'; // Nom du fichier
            $path = 'questionImg/' . $filename; // Chemin du fichier

            // Supprimer l'ancienne image
            $disk->delete($image->path);

            $disk->put($path, $imageWebp->__toString()); // Enregistrer la nouvelle image sur le disque

            $url = $disk->url($path); // URL de la nouvelle image

            // Mettre à jour l'image dans la base de données
            $image->update([
                'url' => $url,
                'nomImage' => $filename,
                'path' => $path,
            ]);

            return response()->json(['success' => 'L\'image a été mise à jour avec succès.'], 200);
        } catch (Exception $e) {
            Log::error('Erreur lors du traitement de l\'image : ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'idImageQ' => $idImageQ
            ]);

            return response()->json(['error' => 'Une erreur est survenue lors du traitement de l\'image.'], 500);
        }
    }

    /**
     * Supprimer une image de question
     * 
     * @param int $idImageQ
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteQuestionPhoto($idImageQ)
    {
        $image = QcmImages::find($idImageQ);
        if (!$image) {
            return response()->json(['error' => 'Image introuvable'], 404);
        }

        try {
            $disk = Storage::disk('do'); // Utiliser le disque DigitalOcean
            $disk->delete($image->path); // Supprimer l'image du disque

            // Supprimer l'image de la base de données
            $image->delete();

            // Mettre à jour la question pour supprimer l'ID de l'image
            QcmQuestions::where('idImageQ', $idImageQ)->update(['idImageQ' => null]);

            return response()->json(['success' => 'L\'image a été supprimée avec succès.'], 200);
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression de l\'image : ' . $e->getMessage(), [
                'idImageQ' => $idImageQ
            ]);

            return response()->json(['error' => 'Une erreur est survenue lors de la suppression de l\'image.'], 500);
        }
    }

    /**
     * Récupérer une image de question
     * 
     * @param int $idQuestion
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionImage($idQuestion)
    {
        $question = QcmQuestions::find($idQuestion);
        if ($question && $question->image) {
            return response()->json(['image' => $question->image], 200);
        } else {
            return response()->json(['error' => 'Image introuvable'], 404);
        }
    }

    /**
     * Supprimer toutes les images d'un QCM
     * 
     * @param int $idQCM
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAllQcmPhotos($idQCM)
    {
        // Vérifier si le QCM existe
        $qcm = Qcm::find($idQCM);
        if (!$qcm) {
            return response()->json(['error' => 'QCM introuvable'], 404);
        }

        // Récupérer toutes les questions du QCM
        $questions = QcmQuestions::where('idQCM', $idQCM)->get();

        // Récupérer tous les IDs d'images associées à ces questions
        $imageIds = $questions->pluck('idImageQ')->filter()->unique()->toArray();

        if (empty($imageIds)) {
            return response()->json(['message' => 'Aucune image trouvée pour ce QCM'], 200);
        }

        $disk = Storage::disk('do'); // Utiliser le disque DigitalOcean
        $deletedCount = 0;
        $errors = [];

        try {
            // Traiter chaque image
            foreach ($imageIds as $imageId) {
                $image = QcmImages::find($imageId);

                if ($image) {
                    try {
                        // Supprimer l'image du stockage
                        if ($disk->exists($image->path)) {
                            $disk->delete($image->path);
                        }

                        // Supprimer l'image de la base de données
                        $image->delete();

                        $deletedCount++;
                    } catch (Exception $e) {
                        $errors[] = "Erreur lors de la suppression de l'image ID {$imageId}: {$e->getMessage()}";
                        Log::error("Erreur lors de la suppression de l'image", [
                            'idImageQ' => $imageId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Mettre à jour toutes les questions pour supprimer les références aux images
            QcmQuestions::where('idQCM', $idQCM)
                ->whereIn('idImageQ', $imageIds)
                ->update(['idImageQ' => null]);

            if (!empty($errors)) {
                return response()->json([
                    'warning' => 'Certaines images n\'ont pas pu être supprimées',
                    'deleted' => $deletedCount,
                    'errors' => $errors
                ], 207); // 207 Multi-Status
            }

            return response()->json([
                'success' => 'Toutes les images du QCM ont été supprimées avec succès',
                'deleted' => $deletedCount
            ], 200);
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression des images du QCM', [
                'idQCM' => $idQCM,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue lors de la suppression des images',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
