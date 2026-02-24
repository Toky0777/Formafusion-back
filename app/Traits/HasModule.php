<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasModule
{
    private function listObjectifs($idModule)
    {

        $idObjectifs = DB::table('objectif_modules')
            ->select('idObjectif')
            ->where('idModule', $idModule)
            ->get();
        return $idObjectifs->toArray();
    }

    private function listPrestations($idModule)
    {

        $idPrestations = DB::table('prestation_modules')
            ->select('idPrestation')
            ->where('idModule', $idModule)
            ->get();
        return $idPrestations->toArray();
    }

    private function listProgrammes($idModule)
    {

        $idProgrammes = DB::table('programmes')
            ->select('idProgramme')
            ->where('idModule', $idModule)
            ->get();
        return $idProgrammes->toArray();
    }

    private function listCibles($idModule)
    {

        $idCibles = DB::table('cible_modules')
            ->select('idCible')
            ->where('idModule', $idModule)
            ->get();
        return $idCibles->toArray();
    }

    private function listPrerequis($idModule)
    {

        $idPrerequis = DB::table('prerequis_modules')
            ->select('idPrerequis')
            ->where('idModule', $idModule)
            ->get();
        return $idPrerequis->toArray();
    }

    public function domaines(): array
    {
        $domaines = DB::table('domaine_formations')->select('*')->get();

        return $domaines->toArray();
    }

    public function levels(): array
    {
        $levels = DB::table('module_levels')->select('*')->get();

        return $levels->toArray();
    }

    public function objectifs($id): array
    {
        $objectifs = DB::table('objectif_modules')->select('*')->where('idModule', $id)->get();

        return $objectifs->toArray();
    }

    public function prestations($idModule): array
    {
        $prestations = DB::table('prestation_modules')->select('idPrestation', 'prestation_name')->where('idModule', $idModule)->get();

        return $prestations->toArray();
    }

    public function prerequis($idModule): array
    {
        $prerequis = DB::table('prerequis_modules')->select('idPrerequis', 'prerequis_name')->where('idModule', $idModule)->get();

        return $prerequis->toArray();
    }

    public function cibles($idModule): array
    {
        $cibles = DB::table('cible_modules')->select('idCible', 'cible')->where('idModule', $idModule)->get();

        return $cibles->toArray();
    }

    public function programs($idModule): array
    {
        $programs = DB::table('programmes')->select('idProgramme', 'program_title', 'program_description')->where('idModule', $idModule)->get();

        return $programs->toArray();
    }

    public function program($idModule, $idProgramme): mixed
    {
        $query = DB::table('programmes')
            ->select('idProgramme', 'program_title', 'program_description')
            ->where('idModule', $idModule)
            ->where('idProgramme', $idProgramme);

        return $query;
    }
}
