<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DashboardFormat extends Model
{
    use HasFactory;

    public static function formatPrice($price): string|int
    {
        if ($price == 0) {
            $price = 0 . " Ar";
        } else if ($price < 1000000 and $price != 0) {
            $price = number_format($price / 1000, 2) . "K Ar";
        } else if ($price < 1000000000) {
            $price = number_format($price / 1000000, 2) . 'M Ar';
        } else {
            $price = number_format($price / 1000000000, 2) . 'B Ar';
        }

        return $price;
    }


    public static function getProjectStudents(int $idProjet): int
    {
        return DB::table('detail_apprenants')->where('idProjet', $idProjet)->get()->count();
    }
    public static function getProjectCfp(int $idCustomer)
    {
        return DB::table('customers')->where('idCustomer', $idCustomer)->first();
    }
    public static function getProjectFormateurs(int $idProjet)
    {
        return DB::table('v_formateur_cfps')->where('idProjet', $idProjet)->get();
    }

    public static function getProjectEtp()
    {
    }

    public static function getProjectTotalHours(int $idProjet)
    {

        $trainer_hours = DB::table('v_seances')
            ->join('v_union_projets', 'v_seances.idProjet', '=', 'v_union_projets.idProjet')
            ->select(DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession'))
            ->where('v_seances.idProjet', $idProjet)
            ->first()->sumHourSession;

        return $trainer_hours;
    }
}
