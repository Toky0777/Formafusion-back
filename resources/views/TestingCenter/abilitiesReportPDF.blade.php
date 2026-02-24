<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Compétences - {{ $qcm->intituleQCM }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            color: #444;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .info-section,
        .summary {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .info-section p,
        .summary p {
            font-size: 16px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .summary p {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Rapport de Compétences - {{ $qcm->intituleQCM }}</h1>

        <!-- Informations Générales -->
        <div class="info-section">
            <h2>Informations Générales</h2>
            <p><strong>Nom :</strong> {{ $apprenant->name }}</p>
            <p><strong>Prénom :</strong> {{ $apprenant->firstName }}</p>
            <p><strong>Date du test :</strong> {{ \Carbon\Carbon::parse($session->dateDebut)->format('d/m/Y H:i') }}</p>
            <p><strong>Points Maximum :</strong> {{ $results['max_points'] }}</p>
            <p><strong>Durée :</strong> {{ gmdate('H:i:s', $results['session_info']['duree']) }}</p>
        </div>

        <!-- Résultats par Catégorie -->
        <h2>Résultats par Catégorie</h2>
        <table>
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Points Maximum</th>
                    <th>Points Obtenus</th>
                    <th>Pourcentages</th>
                    <th>Critique</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($results['categories'] as $categorie)
                    <tr>
                        <td>{{ $categorie['nom'] }}</td>
                        <td>{{ $categorie['points_max'] }}</td>
                        <td>{{ $categorie['points_obtenus'] }}</td>
                        <td>{{ $categorie['pourcentage'] }}%</td>
                        <td>
                            @if (isset($categorie['analyse']))
                                {{ $categorie['analyse']['description'] }}
                            @else
                                <span class="text-gray-400">Non disponible</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Résumé Global -->
        <div class="summary">
            <h2>Résumé Global</h2>
            <p><strong>Total de Points Obtenus :</strong> {{ $results['total_points'] }} /
                {{ $results['max_points'] }}</p>
            <p><strong>Pourcentage :</strong> {{ $results['pourcentage_global'] }}%</p>
            <p><strong>Moyenne des pourcentages :</strong> {{ $averagePercentage }}%</p>
            @if (isset($results['analyse_globale']))
                <p><strong>Analyse Globale :</strong><br>{{ $results['analyse_globale']['description'] }}</p>
                <p><strong>Recommandations :</strong><br>{{ $results['analyse_globale']['recommendations'] }}</p>
            @else
                <p class="text-gray-700 text-lg">
                    <p><strong>Analyse Globale :</p>
                    <p><strong>Recommandations :</strong><br></p>
                </p>
            @endif
        </div>
    </div>
</body>

</html>
