@extends('layouts.masterEtp')

@section('content')
    <div class="flex flex-col w-full h-full mb-12">
        <div class="flex flex-col w-full p-4 mx-auto mt-4 xl:p-0 gap-y-4 xl:container align-center">
            <div class="flex flex-row gap-3">
                <x-menu-reporting-etp click='formation' />
                <x-apprenant-reporting-etp click='apprenant' />
                <x-clients-reporting-etp click='client' />
                <x-cours-reporting-etp click='cours' />
                <x-chiffre-reporting-etp click='chiffres' />
            </div>

            <div class="w-full h-screen bg-gray-100 font-sans flex items-center justify-center">
                <div x-data="{ openTab: 1 }" class="w-full h-full">
                    <div class="w-full h-full p-4">
                        <div class="mb-4 flex space-x-4 p-2 bg-white rounded-lg shadow-md border-b-2 border-gray-200 mb-4">
                            <p class="p-2 text-lg font-medium text-slate-800">
                                Chiffre d'affaire par:
                            </p>
                            <button x-on:click="openTab = 1" :class="{ 'bg-[#A462A4] text-white': openTab === 1 }"
                                class="flex-1 py-2 px-4 rounded-md focus:outline-none focus:shadow-outline-blue transition-all duration-300">
                                Projets
                            </button>
                            <button x-on:click="openTab = 2" :class="{ 'bg-[#A462A4] text-white': openTab === 2 }"
                                class="flex-1 py-2 px-4 rounded-md focus:outline-none focus:shadow-outline-blue transition-all duration-300">
                                Cours
                            </button>
                            <button x-on:click="openTab = 3" :class="{ 'bg-[#A462A4] text-white': openTab === 3 }"
                                class="flex-1 py-2 px-4 rounded-md focus:outline-none focus:shadow-outline-blue transition-all duration-300">
                                Clients
                            </button>
                            <button x-on:click="openTab = 4" :class="{ 'bg-[#A462A4] text-white': openTab === 4 }"
                                class="flex-1 py-2 px-4 rounded-md focus:outline-none focus:shadow-outline-blue transition-all duration-300">
                                Mois
                            </button>
                        </div>

                        <div class="h-[calc(100%-100px)] overflow-auto">
                            <!-- ajustement pour afficher du contenu en full height -->
                            <div x-show="openTab === 1"
                                class="transition-all duration-300 bg-white p-4 rounded-lg shadow-md border-l-4 border-[#A462A4]">
                                <h2 class="text-2xl font-semibold mb-2 text-[#A462A4]">Chiffre d'affaire par projet</h2>
                                <table class="table caption-top">
                                    <thead class="table-light">
                                        <tr class="px-4 text-xl font-medium text-slate-600">
                                            <th scope="col" class="">
                                                <select name="mois"
                                                    class="w-full h-full capitalize bg-transparent cursor-pointer focus:outline-none"
                                                    id="select_month">
                                                    <option class="text-slate-700 hover:bg-slate-400" value="12">Tous
                                                        les projets</option>
                                                    @foreach ($project_by_month as $key => $projects)
                                                        <option class="text-slate-700 hover:bg-slate-400"
                                                            value="{{ $key }}">
                                                            {{ $months[$key] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </th>
                                            <th scope="col">Formation</th>
                                            <th scope="col">ETP</th>
                                            <th scope="col" class="text-right">Coût</th>
                                            <th scope="col" class="text-right">Cost/Emp</th>
                                            <th scope="col" class="text-right">Invités</th>
                                            <th scope="col" class="text-right">Absents</th>
                                            <th scope="col" class="text-right">Formés</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project_by_month as $key => $projects)
                                            @php
                                                $i = 0;
                                            @endphp
                                            @foreach ($projects as $project)
                                                <tr class="border-2 month_{{ $key }}">
                                                    <td>{{ $months[$key] }}</td>
                                                    <td class="text-left">{{ $project->module_name }}</td>
                                                    <td>{{ $project->etp_name }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($project->total_ttc, 2, '.', ' ') }} Ar</td>
                                                    @if (DashboardFormat::getProjectStudents($project->idProjet) != 0)
                                                        <td>{{ number_format($project->total_ttc / DashboardFormat::getProjectStudents($project->idProjet), 2, '.', ' ') }}
                                                            Ar</td>
                                                    @else
                                                        <td>0 Ar</td>
                                                    @endif
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                    <td>0</td>
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                </tr>
                                            @endforeach
                                            @php
                                                $i = $i + 1;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="openTab === 2"
                                class="transition-all duration-300 bg-white p-4 rounded-lg shadow-md border-l-4 border-[#A462A4]">
                                <h2 class="text-2xl font-semibold mb-2 text-[#A462A4]">Chiffre d'affaire par Cours/Modules
                                </h2>
                                <table class="table caption-top">
                                    <thead class="table-light">
                                        <tr class="px-4 text-xl font-medium text-slate-600">
                                            <th scope="col" class="">
                                                <select name="mois"
                                                    class="w-full h-full capitalize bg-transparent cursor-pointer focus:outline-none"
                                                    id="select_month">
                                                    <option class="text-slate-700 hover:bg-slate-400" value="12">Tous
                                                        les modules</option>
                                                    @foreach ($project_by_month as $key => $projects)
                                                        <option class="text-slate-700 hover:bg-slate-400"
                                                            value="{{ $key }}">
                                                            {{ $months[$key] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </th>
                                            <th scope="col">Formation</th>
                                            <th scope="col">ETP</th>
                                            <th scope="col" class="text-right">Coût</th>
                                            <th scope="col" class="text-right">Cost/Emp</th>
                                            <th scope="col" class="text-right">Invités</th>
                                            <th scope="col" class="text-right">Absents</th>
                                            <th scope="col" class="text-right">Formés</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project_by_month as $key => $projects)
                                            @php
                                                $i = 0;
                                            @endphp
                                            @foreach ($projects as $project)
                                                <tr class="border-2 month_{{ $key }}">
                                                    <td>{{ $months[$key] }}</td>
                                                    <td class="text-left">{{ $project->module_name }}</td>
                                                    <td>{{ $project->etp_name }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($project->total_ttc, 2, '.', ' ') }} Ar</td>
                                                    @if (DashboardFormat::getProjectStudents($project->idProjet) != 0)
                                                        <td>{{ number_format($project->total_ttc / DashboardFormat::getProjectStudents($project->idProjet), 2, '.', ' ') }}
                                                            Ar</td>
                                                    @else
                                                        <td>0 Ar</td>
                                                    @endif
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                    <td>0</td>
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                </tr>
                                            @endforeach
                                            @php
                                                $i = $i + 1;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="openTab === 3"
                                class="transition-all duration-300 bg-white p-4 rounded-lg shadow-md border-l-4 border-[#A462A4]">
                                <h2 class="text-2xl font-semibold mb-2 text-[#A462A4]">Chiffre d'affaire par centre de
                                    formation</h2>
                                <table class="table caption-top">
                                    <thead class="table-light">
                                        <tr class="px-4 text-xl font-medium text-slate-600">
                                            <th scope="col" class="">
                                                <select name="mois"
                                                    class="w-full h-full capitalize bg-transparent cursor-pointer focus:outline-none"
                                                    id="select_month">
                                                    <option class="text-slate-700 hover:bg-slate-400" value="12">Tous
                                                        les centre de formations</option>
                                                    @foreach ($project_by_month as $key => $projects)
                                                        <option class="text-slate-700 hover:bg-slate-400"
                                                            value="{{ $key }}">
                                                            {{ $months[$key] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </th>
                                            <th scope="col">Formation</th>
                                            <th scope="col">ETP</th>
                                            <th scope="col" class="text-right">Coût</th>
                                            <th scope="col" class="text-right">Cost/Emp</th>
                                            <th scope="col" class="text-right">Invités</th>
                                            <th scope="col" class="text-right">Absents</th>
                                            <th scope="col" class="text-right">Formés</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project_by_month as $key => $projects)
                                            @php
                                                $i = 0;
                                            @endphp
                                            @foreach ($projects as $project)
                                                <tr class="border-2 month_{{ $key }}">
                                                    <td>{{ $months[$key] }}</td>
                                                    <td class="text-left">{{ $project->module_name }}</td>
                                                    <td>{{ $project->etp_name }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($project->total_ttc, 2, '.', ' ') }} Ar</td>
                                                    @if (DashboardFormat::getProjectStudents($project->idProjet) != 0)
                                                        <td>{{ number_format($project->total_ttc / DashboardFormat::getProjectStudents($project->idProjet), 2, '.', ' ') }}
                                                            Ar</td>
                                                    @else
                                                        <td>0 Ar</td>
                                                    @endif
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                    <td>0</td>
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                </tr>
                                            @endforeach
                                            @php
                                                $i = $i + 1;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="openTab === 4"
                                class="transition-all duration-300 bg-white p-4 rounded-lg shadow-md border-l-4 border-[#A462A4]">
                                <h2 class="text-2xl font-semibold mb-2 text-[#A462A4]">Chiffre d'affaire par Mois</h2>
                                <table class="table caption-top">
                                    <thead class="table-light">
                                        <tr class="px-4 text-xl font-medium text-slate-600">
                                            <th scope="col" class="">
                                                <select name="mois"
                                                    class="w-full h-full capitalize bg-transparent cursor-pointer focus:outline-none"
                                                    id="select_month">
                                                    <option class="text-slate-700 hover:bg-slate-400" value="12">Tous
                                                        les mois</option>
                                                    @foreach ($project_by_month as $key => $projects)
                                                        <option class="text-slate-700 hover:bg-slate-400"
                                                            value="{{ $key }}">
                                                            {{ $months[$key] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </th>
                                            <th scope="col">Formation</th>
                                            <th scope="col">ETP</th>
                                            <th scope="col" class="text-right">Coût</th>
                                            <th scope="col" class="text-right">Cost/Emp</th>
                                            <th scope="col" class="text-right">Invités</th>
                                            <th scope="col" class="text-right">Absents</th>
                                            <th scope="col" class="text-right">Formés</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project_by_month as $key => $projects)
                                            @php
                                                $i = 0;
                                            @endphp
                                            @foreach ($projects as $project)
                                                <tr class="border-2 month_{{ $key }}">
                                                    <td>{{ $months[$key] }}</td>
                                                    <td class="text-left">{{ $project->module_name }}</td>
                                                    <td>{{ $project->etp_name }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($project->total_ttc, 2, '.', ' ') }} Ar</td>
                                                    @if (DashboardFormat::getProjectStudents($project->idProjet) != 0)
                                                        <td>{{ number_format($project->total_ttc / DashboardFormat::getProjectStudents($project->idProjet), 2, '.', ' ') }}
                                                            Ar</td>
                                                    @else
                                                        <td>0 Ar</td>
                                                    @endif
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                    <td>0</td>
                                                    <td>{{ DashboardFormat::getProjectStudents($project->idProjet) }}</td>
                                                </tr>
                                            @endforeach
                                            @php
                                                $i = $i + 1;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('script')
    {{-- <script src={{ asset('js/dashboard/dashboardEtp.js') }}></script> --}}
@endsection
