<form action="{{ route('reporting.filter.formationEtp') }}" method="post" class="w-full">
    @csrf
    <div class="flex justify-evenly flex-wrap">
        <div class="text-center flex flex-col mx-1.5">
            <label for="date_begin" class="font-semibold">Plage de date</label>
            <input type="text" id="daterange" name="daterange" class="rounded border-2 p-2 my-3 bg-slate-50" />
        </div>
        <div class="text-center flex flex-col  mx-1.5">
            <label for="formation" class="font-semibold">Formation</label>
            <x-drop-form id="formation" titre="Nom de la Formation" :drop=$all_etp_formation />
        </div>
    </div>
    <div class="flex justify-end m-3">
        <button id="filtrer" type="submit"
            class="py-2 px-4 font-semibold text-slate-50 rounded-3 bg-[#A462A4] hover:bg-[#A462A4b9] cursor-pointer">Filtrer</button>
    </div>
</form>
