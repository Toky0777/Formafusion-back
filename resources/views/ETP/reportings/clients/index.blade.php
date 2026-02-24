@extends('layouts.masterEtp')
@section('content')
    <div class="flex flex-col w-full p-4 mx-auto xl:p-0 gap-y-4 xl:container align-center">
        <form action="{{ route('reporting.filter.formationEtp') }}" method="post">
            <div class="grid grid-cols-4 gap-4 p-4 bg-white rounded-lg shadow-sm">
                @csrf
                <label class="input input-bordered w-[24rem] flex items-center gap-2 mt-2">
                    <input class="grow" type="text" id="name_appr" onkeyup="searchByName()"
                        placeholder="Qui cherchez-vous ?" />
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                        class="w-4 h-4 opacity-70">
                        <path fill-rule="evenodd"
                            d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                            clip-rule="evenodd" />
                    </svg>
                </label>
                <span class="inline-flex grid items-center grid-cols-1 col-span-2 gap-2 grid-cols-subgrid">
                    <select id="formation" name="formation" class="select select-bordered text-slate-600">
                        <option disabled selected>CFP</option>
                        <option value="all" selected>
                            Tous les centre de formation
                        </option>
                        @foreach ($all_cfp as $item)
                            <option class="p-2 cursor-pointer input_formation option pointer hover:bg-slate-100 text-start"
                                value="{{ $item->idModule }}">
                                {{ $item->cfp_name }}
                            </option>
                        @endforeach
                    </select>
                </span>
                <button id="filtrer" type="submit"
                    class="py-2 px-4 font-semibold text-white bg-[#A462A4] hover:bg-[#A462A4b9] cursor-pointer rounded-md">Filtrer</button>
            </div>
        </form>


        <div class="flex items-center w-full flex-column ">
            <div id="tableReporting" class="w-full p-4 bg-white rounded-md shadow-sm xl:container table-responsive-xxl">
                <table class="table align-middle table-striped caption-top table-hover">
                    <caption>
                        <div class="inline-flex items-start justify-between w-full">
                            <div>
                                <div class="flex justify-center gap-2 my-5 w-fit">
                                    <div class="">
                                        <a href="{{ route('exportXlCl') }}"
                                            class="px-4 py-3 font-normal text-green-600 duration-200 bg-green-100 rounded-lg hover:text-green-700 hover:bg-green-200">
                                            <span class="inline-flex items-center gap-2">
                                                <i class="text-xl fa-solid fa-file-excel"></i>
                                                <p>Telecharger en Excel</p>
                                            </span>
                                        </a>
                                    </div>
                                    <div class="">
                                        <a href="{{ route('exportPdfCl') }}"
                                            class="px-4 py-3 font-normal text-red-500 duration-200 bg-red-100 rounded-lg hover:text-red-700 hover:bg-red-200">
                                            <span class="inline-flex items-center gap-2">
                                                <i class="text-xl fa-solid fa-file-pdf"></i>
                                                <p>Telecharger en PDF</p>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </caption>
                    <thead class="table-light">
                        <tr class="!text-2xl font-medium text-gray-800">
                            <th scope="col">Centre de formation</th>
                            <th scope="col">Fonction</th>
                            <th scope="col">Matricule</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Formation</th>
                            <th scope="col">Type de formation</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Lieu de formation</th>
                            <th scope="col">Date de début</th>
                            <th scope="col">Date Fin</th>
                            <th scope="col">Durée de la formation</th>
                        </tr>
                    </thead>
                    <tbody id="showResult">
                        @foreach ($all_learner as $item)
                            <tr class="text-lg text-gray-600">
                                <td>{{ $item->cfp_name }}</td>
                                <td>{{ $item->emp_fonction }}</td>
                                <td>{{ $item->emp_matricule }}</td>
                                <td>{{ $item->emp_name }} {{ $item->emp_firstname }}</td>
                                <td>{{ $item->module_name }}</td>
                                <td>{{ $item->project_type }}</td>
                                <td>{{ $item->project_status }}</td>
                                <td>{{ $item->salle_name }} <br> {{ $item->salle_quartier }}</td>
                                <td>{{ $item->dateDebut }}</td>
                                <td>{{ $item->dateFin }}</td>
                                <td class="text-center">{{ $item->dureeH }} H</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Section pour afficher les résultats de la recherche -->
            <div id="searchResults" class="w-full p-4 bg-white rounded-md shadow-sm xl:container table-responsive-xxl">
                <!-- Les résultats de recherche seront insérés ici via AJAX -->
            </div>
        </div>
    </div>
@endsection

@push('custom_style')
    <link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
@endpush
<script>
    function searchByName() {
        var name_appr = $('#name_appr').val();
        var showResult = $('#showResult'); // Corrigé ici

        if (name_appr.length > 0) {
            $.ajax({
                type: "GET",
                url: `/reporting/historique/search/${name_appr}/name`,
                dataType: "json",
                success: function(res) {
                    showResult.html(''); // Réinitialise le conteneur de résultats

                    if (res.apprenants.length > 0) {
                        var resultsHtml = `
                            <table class="table align-middle table-striped caption-top table-hover">
                                <thead class="table-light">
                                </thead>
                                <tbody>`;

                        $.each(res.apprenants, function(i, val) {
                            resultsHtml += `
                                <tr class="text-lg text-gray-600">
                                    <td>${val.emp_matricule}</td>
                                    <td>${val.emp_name} ${val.emp_firstname}</td>
                                    <td>${val.emp_fonction}</td>
                                    <td>${val.module_name}</td>
                                    <td>${val.project_type}</td>
                                    <td>${val.project_status}</td>
                                    <td>${val.salle_name} <br> ${val.salle_quartier}</td>
                                    <td>${val.etp_name}</td>
                                    <td>${val.dateDebut}</td>
                                    <td>${val.dateFin}</td>
                                    <td class="text-center">${val.dureeH}H</td>
                                    <td class="text-right">${val.taux_de_presence}%</td>
                                </tr>
                            `;
                        });

                        resultsHtml += `
                                </tbody>
                            </table>`;
                        showResult.html(resultsHtml);
                    } else {
                        showResult.append(`
                            <table class="table align-middle table-striped caption-top table-hover">
                                <tbody class="flex items-center justify-center w-full">
                                        <td class="text-center">Aucun résultat trouvé</td>
                                </tbody>
                            </table>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    showResult.html(
                        '<p class="flex text-center">Une erreur est survenue lors de la recherche.</p>');
                }
            });
        } else {
            showResult.html(''); // Vider le conteneur de résultats si le champ est vide
        }
    }
</script>


@section('script')
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/filter/newFilter.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script src={{ asset('js/imask.js') }}></script>
@endsection
