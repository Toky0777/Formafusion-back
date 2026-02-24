<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporting Formation ETP</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        h1 {
            font-size: 14px;
        }
        .status {
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }
        .termine {
            background-color: #dcfce7;
            color: #166534;
        }
        .supprime {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .autre {
            background-color: #fef9c3;
            color: #854d0e;
        }
    </style>
</head>
<body>
    <h1>Reporting Formation ETP</h1>

    @if(!empty($data_filter))
        <p><strong>Filtres :</strong> {{ implode(', ', $data_filter) }}</p>
    @endif

    @if(!empty($all_learner) && is_iterable($all_learner))
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Fonction</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Entreprise</th>
                    <th>Lieu</th>
                    <th>Debut</th>
                    <th>Fin</th>
                    <th>Dure</th>
                </tr>
            </thead>
            <tbody>
                @foreach($all_learner as $item)
                    <tr>
                        <td>{{ $item['module_name'] ?? '' }}</td>
                        <td>{{ $item['emp_matricule'] ?? '' }}</td>
                        <td>{{ ($item['emp_name'] ?? '') . ' ' . ($item['emp_firstname'] ?? '') }}</td>
                        <td>{{ $item['emp_fonction'] ?? '' }}</td>
                        <td>{{ $item['project_type'] ?? '' }}</td>
                        <td>
                            @php
                                $status = $item['project_status'] ?? '';
                                $class = match($status) {
                                    'Terminé' => 'termine',
                                    'Supprimé' => 'supprime',
                                    default => 'autre'
                                };
                            @endphp
                            <span class="status {{ $class }}">{{ $status }}</span>
                        </td>
                        <td>{{ $item['etp_name'] ?? '' }}</td>
                        <td>{{ ($item['salle_name'] ?? '') }} {{ ($item['salle_quartier'] ?? '') ? '(' . $item['salle_quartier'] . ')' : '' }}</td>
                        <td>{{ $item['dateDebut'] ?? '' }}</td>
                        <td>{{ $item['dateFin'] ?? '' }}</td>
                        <td>{{ $item['dureeH'] ?? '' }}h</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Aucune donnée à afficher.</p>
    @endif
</body>
</html>