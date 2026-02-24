<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à collaborer avec {{ $customer_name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 40px auto;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #2563eb;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .content {
            padding: 30px 25px;
            line-height: 1.6;
        }

        .content p {
            margin-bottom: 16px;
        }

        .link-button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }

        .password-box {
            background-color: #f0f4ff;
            padding: 10px 15px;
            border-radius: 6px;
            display: inline-block;
            font-family: monospace;
            font-size: 15px;
            color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Invitation à collaborer</h1>
        </div>

        <div class="content">
            <p>Bonjour,</p>

            <p><strong>{{ $customer_name }}</strong> vous invite à rejoindre sa plateforme de formation et à collaborer
                au sein de son espace professionnel sur <strong>FormaFusion</strong>.</p>

            <p>Pour accéder à votre compte, cliquez sur le lien ci-dessous :</p>

            <p>
                <a href="https://mg.formafusion.io" class="link-button" target="_blank">Accéder à la plateforme</a>
            </p>

            <p>Vos identifiants de connexion :</p>
            <p>Mot de passe : <span class="password-box">1234@#</span></p>

            <p>Nous vous recommandons de modifier ce mot de passe dès votre première connexion.</p>

            <p>Au plaisir de collaborer avec vous !<br>
                L’équipe <strong>FormaFusion</strong></p>
        </div>
    </div>
</body>

</html>
