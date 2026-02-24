<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de devis - {{ $data['firstName'] }}</title>
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

        .highlight {
            font-weight: 600;
            color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Demande de devis - {{ $data['firstName'] }} {{ $data['lastName'] }}</h1>
        </div>

        <div class="content">
            <p>Bonjour,</p>

            <p><strong>{{ $data['firstName'] }} {{ $data['lastName'] }}</strong> a soumis une demande de devis pour une
                formation
                de <strong>{{ $data['course'] }}</strong>
                @if ($data['level'])
                    niveau <strong>{{ $data['level'] }}</strong>
                @endif
                avec les informations suivantes :
            </p>

            <p><span class="highlight">Informations de particulier :</span><br>
                Nom : {{ $data['firstName'] }}<br>
                Prénom : {{ $data['lastName'] }}<br>
                Email : {{ $data['email'] }}<br>
                Téléphone : {{ $data['phone'] }}<br>
                Situation professionnelle: {{ $data['professionalStatus'] }}
            </p>

            <p><span class="highlight">Détails de la formation :</span><br>
                Type : particulier<br>
                Modalité : @if ($data['modality'] == 1)
                    Présentielle
                @elseif($data['modality'] == 2)
                    En ligne
                @else
                    Blended
                @endif
                <br>
                Date : {{ $data['startDate'] }} → {{ $data['endDate'] }}<br>
                Lieu : {{ $data['location'] ?? '-' }}
            </p>

            <p><span class="highlight">Notes :</span><br>
                {{ $data['notes'] ?? '-' }}
            </p>

            <p>Merci de contacter cette entreprise pour finaliser le devis.</p>

            <p>Cordialement,<br>
                L’équipe <strong>FormaFusion</strong></p>
        </div>
    </div>
</body>

</html>
