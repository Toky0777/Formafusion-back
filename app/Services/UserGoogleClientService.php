<?php

namespace App\Services;

use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;
use Exception;
use Illuminate\Support\Carbon;
//use Google_Service;
use Illuminate\Support\Facades\Log;

class UserGoogleClientService
{
  public $client;
  private $oauth2Service;

  public function __construct()
  {
    $this->client = new Google_Client();
    $this->configureClient();
  }

  private function configureClient()
  {
    $this->client->setClientId(config('services.google.client_id'));
    $this->client->setClientSecret(config('services.google.client_secret'));
    $this->client->setRedirectUri(config('services.google.redirect_uri'));

    //$this->oauth2Service = new Google_Service_Oauth2($this->client);
    $this->client->addScope('https://www.googleapis.com/auth/userinfo.email');
    $this->client->addScope('https://www.googleapis.com/auth/userinfo.profile');
    $this->client->addScope('https://www.googleapis.com/auth/calendar');
    $this->client->addScope('https://www.googleapis.com/auth/calendar.events');
    $this->client->setAccessType('offline');  // Pour obtenir un refresh token
    $this->client->setPrompt('consent');      // Force l'affichage du consentement
  }

  public function getAuthUrl()
  {
    return $this->client->createAuthUrl();
  }

  public function handleCallback(string $authCode)
  {
    try {
      $token = $this->client->fetchAccessTokenWithAuthCode($authCode);

      if (isset($token['error'])) {
        throw new Exception('Error fetching access token: ' . $token['error']);
      }

      return [
        'access_token' => $token['access_token'],
        'refresh_token' => $token['refresh_token'] ?? null,
        'expires_in' => $token['expires_in'],
        'email' => $this->getUserEmail($token['access_token'])
      ];
    } catch (Exception $e) {
      Log::error('Google Auth Error: ' . $e->getMessage());
      throw $e;
    }
  }

  private function getUserEmail($accessToken)
  {
    $this->client->setAccessToken($accessToken);
    //$oauth2 = new Google_Service_Oauth2($this->client);

    // $userInfo = $oauth2->userinfo->get();
    // return $userInfo->getEmail();
  }

  public function refreshToken($refreshToken)
  {
    try {
      $this->client->setAccessToken([
        'refresh_token' => $refreshToken
      ]);

      if ($this->client->isAccessTokenExpired()) {
        $newToken = $this->client->fetchAccessTokenWithRefreshToken();
        return [
          'access_token' => $newToken['access_token'],
          'expires_in' => $newToken['expires_in']
        ];
      }

      return null;
    } catch (Exception $e) {
      Log::error('Token refresh error: ' . $e->getMessage());
      throw $e;
    }
  }

  public function refreshTokenIfNeeded(User $user)
  {
    if ($user->google_token_expires_at && $user->google_token_expires_at->isPast()) {
      $this->client->setAccessToken([
        'access_token' => $user->google_token,
        'refresh_token' => $user->google_refresh_token
      ]);

      if ($this->client->isAccessTokenExpired()) {
        $newToken = $this->client->fetchAccessTokenWithRefreshToken();

        $user->update([
          'google_token' => $newToken['access_token'],
          'google_token_expires_at' => Carbon::now()->addSeconds($newToken['expires_in'])
        ]);
      }
    }
  }
}
