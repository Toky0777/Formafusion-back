<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - FormaFusion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background-color: #f8fafc;
            line-height: 1.6;
            color: #1e293b;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .email-header {
            background-color: #ffffff;
            padding: 40px 30px 30px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: #4f46e5;
        }

        .email-header h1 {
            font-size: 22px;
            font-weight: 600;
            color: #374151;
            margin-top: 10px;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            color: #1e293b;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .message {
            color: #475569;
            margin-bottom: 24px;
            font-size: 15px;
            line-height: 1.7;
        }

        .button-container {
            text-align: center;
            margin: 35px 0;
        }

        .reset-button {
            display: inline-block;
            background-color: #f8fafc;
            color: #475569;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .reset-button:hover {
            background-color: #eaecee;
        }

        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin: 30px 0;
            border-radius: 6px;
        }

        .info-box p {
            color: #475569;
            font-size: 14px;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .info-box strong {
            color: #1e293b;
        }

        .expiry-notice {
            color: #92400e;
            font-size: 14px;
            text-align: center;
            margin: 25px 0;
            padding: 14px;
            background-color: #fffbeb;
            border-radius: 6px;
            border: 1px solid #fde68a;
        }

        .link-alternative {
            word-break: break-all;
            color: #475569;
            font-size: 14px;
            margin: 25px 0;
            padding: 16px;
            background-color: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-family: monospace;
        }

        .email-footer {
            padding: 30px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .footer-links {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .footer-links a {
            color: #4f46e5;
            text-decoration: none;
            font-size: 13px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .signature {
            margin-top: 25px;
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
        }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-header,
            .email-body {
                padding: 30px 20px;
            }

            .reset-button {
                display: block;
                width: 100%;
                padding: 16px 20px;
            }

            .footer-links {
                flex-direction: column;
                gap: 10px;
            }

            .email-footer {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">Forma<span>Fusion</span></div>
            <h1>Réinitialisation de mot de passe</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p class="greeting">Bonjour,</p>

            <p class="message">
                Vous avez demandé la réinitialisation du mot de passe de votre compte FormaFusion.
                Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe.
            </p>

            <div class="button-container">
                <a href="{{ $resetLink }}" class="reset-button">
                    Réinitialiser mon mot de passe
                </a>
            </div>

            <div class="expiry-notice">
                ⏱️ Ce lien est valable pendant 60 minutes.
            </div>

            <div class="info-box">
                <p><strong>Informations importantes :</strong></p>
                <p>• Si vous n'avez pas initié cette demande, ignorez cet email.</p>
                <p>• Votre mot de passe actuel restera actif tant que vous n'aurez pas confirmé le nouveau.</p>
                <p>• Pour toute assistance, notre équipe support est à votre disposition.</p>
            </div>

            <p class="message">
                Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
            </p>

            <div class="link-alternative">
                {{ $resetLink }}
            </div>
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="email-footer">
            <p>Cet email a été envoyé à : {{ $email }}</p>

            <div class="footer-links">
                <a href="{{ env('FRONTEND_URL', 'https://mg.formafusion.io') }}">Accéder à FormaFusion</a>
                <a href="{{ env('FRONTEND_URL', 'https://mg.formafusion.io') }}/aide">Aide & Support</a>
                <a href="{{ env('FRONTEND_URL', 'https://mg.formafusion.io') }}/contact">Contact</a>
            </div>

            <p class="signature">
                Cordialement,<br>
                <strong>L'équipe FormaFusion</strong><br>
                <small>© {{ date('Y') }} FormaFusion. Tous droits réservés.</small>
            </p>
        </div>
    </div>
</body>

</html>
