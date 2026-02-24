<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attribution de badge</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f7ff;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }

        .header h1 {
            color: #7d44e3;
            margin: 0;
        }

        .content {
            padding: 20px 0;
        }

        .badge-info {
            background-color: #f8f7ff;
            border-left: 4px solid #7d44e3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .button {
            display: inline-block;
            background-color: #7d44e3;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .button:hover {
            background-color: #6433b8;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }

        .page-transition {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container page-transition">
        <div class="header">
            <h1>Félicitations {{ $data['prenom'] }} !</h1>
        </div>

        <div class="content">
            <p>Vous venez de recevoir un nouveau badge <strong>{{ $data['badge_titre'] }}</strong> dans le cadre du
                projet <strong>{{ $data['projet_titre'] }}</strong>.</p>

            <p>Ce badge a été délivré par {{ $data['cfp_nom'] }} le {{ $data['date_attribution'] }}.</p>

            @if (isset($data['score']) && $data['score'] !== 'Non évalué')
                <p><strong>Score obtenu :</strong> {{ $data['score'] }}/100</p>
            @endif

            <div class="badge-info">
                <h3>À propos de ce badge</h3>
                <p>{{ $data['badge_description'] }}</p>
            </div>

            <h3>Vérifier l'authenticité de ce badge</h3>
            <p>Pour vérifier l'authenticité de ce badge, cliquez sur le bouton ci-dessous ou partagez ce lien avec les
                recruteurs.</p>

            <div style="text-align: center;">
                <a href="{{ $data['url_verification'] }}" class="button">Vérifier le badge</a>
            </div>

            <p>Félicitations pour cette nouvelle réussite !</p>

            <p>Cordialement,<br>L'équipe {{ $data['cfp_nom'] }}</p>
        </div>

        <div class="footer">
            <p><small>Si vous n'êtes pas à l'origine de cette demande, merci d'ignorer cet email.</small></p>
        </div>
    </div>
</body>

</html>
