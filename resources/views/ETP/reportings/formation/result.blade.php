@extends('layouts.masterEtp')

@section('content')
    <div class="w-full flex flex-col align-center">
        <div class="flex flex-col items-center">
            <div class="w-3/5 my-3">
                <x-menu-reporting-etp click='formation' />
            </div>
            <div class="w-3/5 p-2 bg-slate-100 min-h-52 rounded-md shadow-md">
                @include('ETP.reportings.formation.formulaireForm')
            </div>
        </div>
        <div class="w-full flex flex-column items-center ">
            <div class="flex justify-center w-fit gap-2 my-5">
                <div class="">
                    <a href="{{ Route('exportXlEtp') }}"
                        class="py-2 px-4 font-semibold text-slate-50 rounded-3 bg-green-500 hover:bg-green-400 hover:text-white">Telecharger
                        en
                        Excel</a>
                </div>
                <div class="">
                    <a href="{{ Route('exportPdfEtp') }}"
                        class="py-2 px-4 font-semibold text-slate-50 rounded-3 bg-red-500 hover:bg-red-400 hover:text-white">Telecharger
                        en
                        Pdf</a>
                </div>
            </div>
            @include('ETP.reportings.formation.dataForm')
        </div>

    </div>
@endsection


@section('script')
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/jquery.date-dropdowns.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script src={{ asset('js/filter/newFilter.js') }}></script>
    <script src={{ asset('js/reporting.js') }}></script>
    <script type="text/javascript">
        $('#daterange').daterangepicker({
            ranges: {
                'Tous': ["{{ $formatedEarliestDate }}", "{{ $formatedLatestDate }}"],
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')]
            },
            "endDate": "{{ $formatedLatestDate }}",
            "startDate": "{{ $formatedEarliestDate }}"
        }, function(start, end, label) {});
    </script>
@endsection
