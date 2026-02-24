<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Evaluation - froids</title>
</head>

<body>
    <div>
        @csrf

        <div>
            <p>Titre:
                <span>"Evaluation à froid de la formation"</span>
            </p>
            <p>Nom de la formation:
                <span>{{ $heading->module_name }}</span>
            </p>
            <p>Date de la formation:
                <span>{{ Carbon\Carbon::parse($heading->projet_date_debut)->format('d-M-Y') }} au
                    {{ Carbon\Carbon::parse($heading->projet_date_fin)->format('d-M-Y') }}</span>
            </p>
            <p>Nom du participant:
                <span>{{ $heading->emp_name }} {{ $heading->emp_firstname }}</span>
            </p>
            <p>Entreprise:
                <span>{{ $heading->etp_name }}</span>
            </p>
        </div>
        <hr>

        <div>
            @foreach ($notes as $note)
                <h2 style="font-weight: 700">{{ $note->quiz_type_name }}</h2>

                <div>
                    <p>{{ $note->quizz_cold_name }}: <strong>{{ $note->note_libelle }}</strong></p>
                </div>
            @endforeach
        </div>

        <div>
            <h2 style="font-weight: 700">Amélioration suggérées</h2>
            <div class="mb-3">
                <label for="quizz-aspect" class="form-label">-Quels aspects de la formation pourraient
                    être améliorés ?</label>
                <p><strong>{{ $heading->general_aspect }}</strong></p>
            </div>
            <div class="mb-3">
                <label for="quizz-suggestion" class="form-label">-Avez-vous des suggestions pour de
                    nouvelles formations ou thématiques ?</label>
                <p><strong>{{ $heading->general_suggestion }}</strong></p>
            </div>
        </div>

        <div>
            <h2 style="font-weight: 700">Satisfaction globale</h2>
            <div style="display: flex; justify-content: space-between">
                <p>Sur une échèle de 1 à 5, comment évaluez-vous l'utilité de la formation ? :
                    <strong>{{ $heading->general_satisfaction }}</strong></p>
            </div>
            <div style="display: flex; justify-content: space-between">
                <p>Recommanderiez-vous cette formation à un collèque ? : <strong>{{ $heading->general_recomand_libelle }}</strong></p>
            </div>
        </div>
    </div>
</body>

</html>
