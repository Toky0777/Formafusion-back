<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Programme de Formation - {{ $module->moduleName ?? 'Module Inconnu' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Important pour les caractères spéciaux dans Dompdf */
            line-height: 1.6;
            margin: 40px;
            color: #333;
        }
        h1 {
            color: #6a0dad;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #8a2be2;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin-top: 30px;
        }
        ul {
            list-style-type: disc;
            margin-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
        .info-box {
            background-color: #f3e8ff;
            border-left: 5px solid #8a2be2;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Programme de Formation : {{ $module->moduleName ?? 'N/A' }}</h1>
        <p>{{ $module->module_subtitle ?? 'Pas de sous-titre disponible.' }}</p>

        <div class="info-box">
            <p><strong>Référence:</strong> {{ $module->reference ?? 'N/A' }}</p>
            <p><strong>Durée:</strong> {{ $module->dureeJ ?? 0 }} jours | {{ $module->dureeH ?? 0 }} heures</p>
            <p><strong>Niveau:</strong> {{ $module->module_level_name ?? 'Non spécifié' }}</p>
            <p><strong>Prix:</strong> {{ number_format($module->prix ?? 0, 0, ',', '.') }} Ar</p>
            <p><strong>Organisme de Formation:</strong> {{ $cfp->customerName ?? 'N/A' }}</p>
            <p><strong>Slogan:</strong> {{ $cfp->customer_slogan ?? 'N/A' }}</p>
        </div>

        @if($objectifs->isNotEmpty())
            <h2>Objectifs de la Formation</h2>
            <ul>
                @foreach($objectifs as $objectif)
                    <li>{{ $objectif->objectif }}</li>
                @endforeach
            </ul>
        @else
            <p>Aucun objectif renseigné.</p>
        @endif

        @if($cibles->isNotEmpty())
            <h2>Public Cible</h2>
            <ul>
                @foreach($cibles as $cible)
                    <li>{{ $cible }}</li>
                @endforeach
            </ul>
        @else
            <p>Aucun public cible renseigné.</p>
        @endif

        @if($prerequis->isNotEmpty())
            <h2>Prérequis</h2>
            <ul>
                @foreach($prerequis as $prerequi)
                    <li>{{ $prerequi }}</li>
                @endforeach
            </ul>
        @else
            <p>Aucun prérequis renseigné.</p>
        @endif

        @if(isset($programmes) && $programmes->isNotEmpty())
            <h2>Programme Détaillé</h2>
            @foreach($programmes as $item)
                <h3>{{ $item->program_title }}</h3>
                @if($item->program_description)
                    <p>{!! $item->program_description !!}</p>
                @endif
            @endforeach
        @else
            <p>Aucun programme détaillé renseigné.</p>
        @endif

        {{-- Vous pouvez ajouter d'autres sections ici en utilisant les variables passées --}}
        {{-- Par exemple, pour les projets avec sessions : --}}
        {{-- @if(isset($projects_with_sessions) && !empty($projects_with_sessions))
            <h2>Sessions de Formation</h2>
            @foreach($projects_with_sessions as $projectData)
                <h3>{{ $projectData['project']->project_title }} ({{ $projectData['ville'] }})</h3>
                <p>Du {{ $projectData['projectStartDate'] }} au {{ $projectData['projectEndDate'] }}</p>
                @if(isset($projectData['sessionsGroupedByDate']) && $projectData['sessionsGroupedByDate']->isNotEmpty())
                    <h4>Détail des sessions:</h4>
                    <ul>
                        @foreach($projectData['sessionsGroupedByDate'] as $date => $sessions)
                            <li><strong>{{ $date }}:</strong>
                                <ul>
                                    @foreach($sessions as $session)
                                        <li>{{ $session->session_name }} ({{ $session->heure_debut }} - {{ $session->heure_fin }})</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        @endif --}}

        <div class="footer">
            <p>&copy; {{ date('Y') }} FormaFusion MG. Tous droits réservés.</p>
            @if($cfp->siteWeb)
                <p>Visitez notre site : <a href="{{ $cfp->siteWeb }}" target="_blank">{{ $cfp->siteWeb }}</a></p>
            @endif
        </div>
    </div>
</body>
</html>
