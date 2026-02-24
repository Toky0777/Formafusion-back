<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation à froid</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        p {
            line-height: 1.6;
            font-size: 15px;
        }

        .btn {
            display: inline-block;
            margin: 20px 0;
            padding: 12px 24px;
            background-color: #0056b3;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .btn:hover {
            background-color: #003d80;
        }

        .steps {
            background-color: #f8fafc;
            border-left: 4px solid #0056b3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Évaluation à froid</h2>

        <p>Bonjour,</p>
        <p>Vous avez été invité à effectuer une évaluation à froid de la part de <strong>{{ $customer_name }}</strong>.
        </p>

        <p>Pour réaliser votre évaluation, veuillez cliquer sur le bouton ci-dessous :</p>

        <p style="text-align: center;">
            <a href="https://mg.formafusion.io" class="btn">Accéder à l’évaluation</a>
        </p>

        <div class="steps">
            <p><strong>Étapes à suivre :</strong></p>
            <ol>
                <li>Rendez-vous sur le lien indiqué ci-dessus.</li>
                <li>Connectez-vous avec vos identifiants.</li>
                <li>Accédez à la section <em>« Évaluations à froid »</em>.</li>
                <li>Complétez l’évaluation du projet.</li>
            </ol>
        </div>

        <p><strong>Important :</strong> Tous les résultats seront disponibles dans l’espace de travail de votre
            entreprise.</p>

        <p>Nous vous remercions pour votre collaboration.</p>

        <div class="footer">
            <p>&copy; 2025 Formafusion - Tous droits réservés</p>
        </div>
    </div>
</body>

</html>
