@extends('layouts.masterEtp')

@section('content')
    <div class="flex flex-col w-full p-4 mx-auto xl:p-0 gap-y-4 xl:container align-center">
        <form action="{{ route('reporting.filter.formationEtp') }}" method="post" class="">
            <div class="grid grid-cols-4 gap-4 p-4 bg-white rounded-lg shadow-sm">
                @csrf
                <span class="inline-flex grid items-center grid-cols-2 col-span-3 gap-4 grid-cols-subgrid">
                    <label class="flex items-center gap-2 input input-bordered text-slate-400">
                        Plage de date
                        <input type="text" id="daterange" name="daterange" class="grow text-slate-600" placeholder="" />
                    </label>

                    <select id="formation" name="formation" class="w-full select select-bordered text-slate-600">
                        <option disabled selected>Formation</option>
                        <option value="all" selected>
                            Tous les modules
                        </option>
                        @foreach ($all_etp_formation as $item)
                            <option class="p-2 cursor-pointer input_formation option pointer hover:bg-slate-100 text-start"
                                value="{{ $item->idModule }}">
                                {{ $item->module_name }}
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
                                <p class="py-2 text-2xl font-medium text-gray-800">Filtre de la formation</p>
                                <div class="pb-2 pl-3 text-xl">
                                    <p><span class="text-xl font-medium text-gray-800">Dates :</span> {{ $data_filter[0] }}
                                    </p>
                                    <p><span class="text-xl font-medium text-gray-800">Formation :</span>
                                        {{ $data_filter[1] }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-center gap-2 my-5 w-fit">
                                    <div class="">
                                        <a href="{{ Route('exportXlEtp') }}"
                                            class="px-4 py-3 font-normal text-green-600 duration-200 bg-green-100 rounded-lg hover:text-green-700 hover:bg-green-200">
                                            <span class="inline-flex items-center gap-2">
                                                <i class="text-xl fa-solid fa-file-excel"></i>
                                                <p>Telecharger en Excel</p>
                                            </span>
                                        </a>
                                    </div>
                                    <div class="">
                                        <a href="{{ Route('exportPdfEtp') }}"
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
                            <th scope="col">Matricule</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Fonction</th>
                            <th scope="col">Formation</th>
                            <th scope="col">Type de formation</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Lieu de formation</th>
                            <th scope="col">Entreprise</th>
                            <th scope="col">Date de début</th>
                            <th scope="col" class="text-right">Durée de la formation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($all_learner as $item)
                            <tr class="text-lg text-gray-600">
                                <td>{{ $item->emp_matricule }}</td>
                                <td>{{ $item->emp_name }} {{ $item->emp_firstname }}</td>
                                <td>{{ $item->emp_fonction }}</td>
                                <td>{{ $item->module_name }}</td>
                                <td>{{ $item->project_type }}</td>
                                <td>{{ $item->project_status }}</td>
                                <td>{{ $item->salle_name }} <br> {{ $item->salle_quartier }}</td>
                                <td>{{ $item->etp_name }}</td>
                                <td>{{ $item->dateDebut }}</td>
                                <td class="text-right">{{ $item->dureeH }} H</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endsection

    @push('custom_style')
        <link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
    @endpush

    @section('script')
        <script src="{{ asset('js/moment.min.js') }}"></script>
        <script src="{{ asset('js/filter/newFilter.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
        <script src={{ asset('js/reporting.js') }}></script>
        <script type="text/javascript">
            $('#daterange').daterangepicker({
                ranges: {
                    'Tous': ["{{ $formatedEarliestDate }}", "{{ $formatedLatestDate }}"],
                    'Aujourd\'hui': [moment(), moment()],
                    'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 derniers jours': [moment().subtract(6, 'days'), moment()],
                    '30 dernier jours': [moment().subtract(29, 'days'), moment()],
                    'Mois actuel': [moment().startOf('month'), moment().endOf('month')],
                    'Le mois dernier': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                        .endOf(
                            'month')
                    ]
                },
                "endDate": "{{ $formatedLatestDate }}",
                "startDate": "{{ $formatedEarliestDate }}"
            }, function(start, end, label) {});
        </script>
    @endsection
