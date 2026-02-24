<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formation - Détails du Module</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-size: 16px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            overflow-y: scroll;
        }

        .section {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 40px;
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .big_title {
            font-weight: bold;
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-top: 15px;
            font-style: italic;
        }

        .title {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            text-align: center;
            margin-top: 15px;
        }

        .text-gray {
            color: #666;
            font-size: 16px;
            margin-top: 10px;
        }

        .card {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            justify-content: space-between;
        }

        .card .price {
            margin-top: -10px;
        }

        .card .price label {
            font-style: italic;
        }

        .icon {
            font-size: 30px;
            color: #666;
        }

        .price {
            font-size: 14px;
            padding-top: 10px;
        }

        .availability {
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 15px;
            display: inline-block;
        }

        .available {
            background-color: #d1fae5;
            color: #065f46;
        }

        .unavailable {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .btn {
            background-color: #2563eb;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .sessions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .sessions-table th,
        .sessions-table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
        }

        .rounded-image {
            width: 200px;
            height: 70px;
            margin-bottom: 10px;
        }

        .list {
            list-style-type: disc;
            margin-left: 20px;
        }

        .section ul {
            list-style-type: disc;
            padding-left: 30px;
            margin-top: 10px;
            margin-bottom: 0;
        }

        .section ul li {
            text-align: left;
            font-size: 14px;
            margin-bottom: 8px;
            line-height: 1.5;
            color: #555;
            position: relative;
        }

        .section ul li::marker {
            color: #00b1f4;
        }

        .section ul li:last-child {
            margin-bottom: 0;
        }

        footer {
            display: inline;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
        }

        footer img {
            width: 80px;
            height: 30px;
        }

        footer span {
            margin-right: 5px;
            font-size: 15px;
            font-style: italic;
        }
    </style>
</head>

<body>

    <div class="container">

        <div style="display: inline-flex">
            <div>
                @if (isset($module->etp_logo))
                    <img class="rounded-image"
                        src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/entreprises/{{ $module->etp_logo }}"
                        alt="Logo entreprise">
                @else
                    <span><i class="fa-solid fa-image icon"></i></span>
                @endif
            </div>
            <div>
                <p style="font-weight: bold;">{{ $module->nameCfp }}</p>
            </div>
        </div>


        <div class="section">
            <p class="big_title">{{ $module->moduleName }}</p>
            <p class="text-gray">
                {{ $module->description ? $module->description : 'Aucune description' }}
            </p>
        </div>

        <div class="card">
            <p class="price"><label class="italic">Niveau :</label> {{ $module->module_level_name }}</p>
            <p class="price"><label class="italic">Prix :</label> à partir de
                {{ number_format($module->prix, 2, ',', ' ') }} Ar HT</p>
            <p class="price"><label class="italic">Durée :</label> {{ $module->dureeJ }} jours | {{ $module->dureeH }}
                heures</p>
            <p class="price"><label class="italic">Place :</label>
                {{ $module->minApprenant }}
                @if ($module->maxApprenant > 0)
                    à {{ $module->maxApprenant }}
                @endif
                personnes
            </p>
        </div>

        <div class="section" style="margin-top: 35px;">
            <h1 class="title">Sessions</h1>
            <div class="card">
                @foreach ($projects_with_sessions as $projectId => $projectData)
                    <div style="display: inline-block; vertical-align: top; margin-right: 20px;">
                        <a href="#" class="btn" data-id="{{ $projectId }}">
                            <i class="fa-regular fa-calendar-days"></i>
                            Du {{ $projectData['projectStartDate'] }} - {{ $projectData['projectEndDate'] }}
                        </a>
                        <p class="text-gray" style="display: inline; margin: 0 10px;">
                            <i class="fa-solid fa-location-dot"></i> {{ $projectData['ville'] }}
                        </p>
                        <p class="text-gray" style="display: inline; margin: 0 10px;">
                            <i class="fa-solid fa-money-bill"></i> {{ number_format($module->prix, 2, ',', ' ') }} AR
                        </p>
                        @if ($projectData['availability'] == 1)
                            <span class="availability available" style="display: inline; margin-right: 10px;">Places
                                disponibles</span>
                            <a href="#" class="btn" style="display: inline;">S'inscrire</a>
                        @else
                            <span class="availability unavailable" style="display: inline;">Places indisponibles</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="section">
            <h1 class="title">Objectifs de cette formation</h1>
            <ul>
                @if ($objectifs != null)
                    @foreach ($objectifs as $objectif)
                        <li>{{ $objectif->objectif }}</li>
                    @endforeach
                @else
                    <li>Objectif non renseigné</li>
                @endif
            </ul>
        </div>


        <div class="section">
            <h1 class="title">Programmes de cette formation</h1>
            <ul class="list">
                @foreach ($prog as $pro)
                    <li>
                        <p class="font-semibold"> - {{ $pro->program_title }}</p>
                        <div class="ml-6"> {!! $pro->program_description !!} </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <footer>
        <span>Powered by</span>
        <img src="{{ asset('img/logo/Logo_horizontal.svg') }}" alt="Logo entreprise">
    </footer>

</body>

</html>
