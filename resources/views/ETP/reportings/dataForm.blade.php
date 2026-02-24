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

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom Complet</th>
                <th>Fonction</th>
                <th>Module</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Lieu</th>
                <th>Entreprise</th>
                <th>Dates</th>
                <th>Durée</th>
                <th>Présence</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($all_learner) && is_iterable($all_learner))
                @foreach($all_learner as $learner)
                    <tr>
                        <td>{{ $learner['emp_matricule'] ?? '' }}</td>
                        <td>{{ ($learner['emp_name'] ?? '') . ' ' . ($learner['emp_firstname'] ?? '') }}</td>
                        <td>{{ $learner['emp_fonction'] ?? '' }}</td>
                        <td>{{ $learner['module_name'] ?? '' }}</td>
                        <td>{{ $learner['project_type'] ?? '' }}</td>
                        <td>
                            @php
                                $status = $learner['project_status'] ?? '';
                                $class = match($status) {
                                    'Terminé' => 'termine',
                                    'Supprimé' => 'supprime',
                                    default => 'autre'
                                };
                            @endphp
                            <span class="status {{ $class }}">{{ $status }}</span>
                        </td>
                        <td>{{ ($learner['salle_name'] ?? '') }} {{ ($learner['salle_quartier'] ?? '') ? '(' . $learner['salle_quartier'] . ')' : '' }}</td>
                        <td>{{ $learner['etp_name'] ?? '' }}</td>
                        <td>{{ $learner['dateDebut'] ?? '' }} au {{ $learner['dateFin'] ?? '' }}</td>
                        <td>{{ $learner['dureeH'] ?? '' }}h</td>
                        <td>{{ isset($learner['taux_de_presence']) ? $learner['taux_de_presence'] . '%' : 'N/A' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="11">Aucune donnée disponible.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
