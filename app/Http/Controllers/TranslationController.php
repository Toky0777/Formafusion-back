<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class TranslationController extends Controller
{
    public function translate(Request $request)
    {
        $request->validate([
            'text' => 'required', // peut être string ou array
            'target_lang' => 'required|string|size:2',
            'source_lang' => 'nullable|string|size:2'
        ]);

        $targetLang = strtoupper($request->target_lang);
        $sourceLang = $request->filled('source_lang') ? strtoupper($request->source_lang) : null;

        // ⚡ Supporter plusieurs textes à la fois
        $texts = is_array($request->text) ? $request->text : [$request->text];

        $results = [];

        foreach ($texts as $text) {
            $cacheKey = 'translation_' . md5($text . $targetLang . ($sourceLang ?? ''));

            if (Cache::has($cacheKey)) {
                $results[] = ['text' => Cache::get($cacheKey)];
                continue;
            }

            // 🧱 Vérifier si on respecte le rate limit interne
            $rateKey = 'deepl_limit:' . now()->format('Y-m-d-H-i'); // 1 clé par minute
            if (RateLimiter::tooManyAttempts($rateKey, 25)) { // max 25 appels par minute
                sleep(3); // attendre un peu avant de réessayer
            }
            RateLimiter::hit($rateKey, 60); // compteur valable 60 secondes

            $params = [
                'auth_key' => env('DEEPL_API_KEY'),
                'text' => $text,
                'target_lang' => $targetLang,
            ];

            if ($sourceLang) {
                $params['source_lang'] = $sourceLang;
            }

            // 🔁 Retry automatique (max 3 essais, pause 2s)
            $response = retry(3, function () use ($params) {
                $res = Http::asForm()->post('https://api-free.deepl.com/v2/translate', $params);

                // si DeepL renvoie 429, attendre avant de réessayer
                if ($res->status() === 429) {
                    sleep(2);
                }

                return $res;
            }, 2000);

            if ($response->failed()) {
                $results[] = ['text' => '[Erreur de traduction]'];
                continue;
            }

            $translated = $response->json('translations.0.text');
            $results[] = ['text' => $translated];

            // 🗃️ Mise en cache (30 jours)
            Cache::put($cacheKey, $translated, now()->addDays(30));
        }

        return response()->json(['translations' => $results]);
    }

    // Méthode pour les traductions en masse (optionnelle)
    public function translateBulk(Request $request)
    {
        $request->validate([
            'texts' => 'required|array',
            'target_lang' => 'required|string|size:2',
            'source_lang' => 'nullable|string|size:2'
        ]);

        $params = [
            'auth_key'    => env('DEEPL_API_KEY'),
            'target_lang' => strtoupper($request->target_lang),
        ];

        // Ajouter les textes à traduire
        foreach ($request->texts as $text) {
            $params['text'] = $text;
        }

        // Ajouter la langue source seulement si spécifiée
        if ($request->has('source_lang') && !empty($request->source_lang)) {
            $params['source_lang'] = strtoupper($request->source_lang);
        }

        $response = Http::asForm()->post('https://api-free.deepl.com/v2/translate', $params);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erreur lors de la traduction',
                'details' => $response->body()
            ], 500);
        }

        return response()->json($response->json());
    }
}
