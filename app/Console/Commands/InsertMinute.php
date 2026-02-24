<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InsertMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insertion dans la base de donnée';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $abns = DB::table('detail_abns')
            ->select('dateFin')
            ->get();

        $dateLimit = Carbon::now();

        foreach ($abns as $abn) {
            if($abn->dateFin == $dateLimit){
                DB::delete('delete detail_abns where dateFin = ?', [$dateLimit]);
            }
        }
    }
}
