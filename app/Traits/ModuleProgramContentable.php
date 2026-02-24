<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait ModuleProgramContentable{
    public function getContents($idProgram){
        $contents = DB::table('module_program_contents')->select('*')->where('idProgramme', $idProgram);

        return $contents;
    }

    public function getContent($idProgram, $idContent){
        $content = DB::table('module_program_contents')->select('*')->where('idProgramme', $idProgram)->where('id', $idContent);

        return $content;
    }

    // public function saveContent($idProgram, $title, $description = null){
    //     $lines = preg_split("/\r\n|\n|\r/", $description);
    //      foreach ($lines as $line) {
    //     $trimmed = trim($line);
    //     if (!empty($trimmed)) {
    //          DB::table('module_program_contents')->insertGetId([
    //           'title' => $title,
    //           'description' => '<p>' . e($trimmed) . '</p>',
    //          'idProgramme' => $idProgram
    //      ]);
    //         $inserted = DB::table('module_program_contents')->where('idProgramme', $idProgram)->first();
    //     }
    //    }
    // }

//     public function saveContent($idProgram, $title, $description = null)
// {
   
//     $lines = $description ? preg_split("/\r\n|\n|\r/", $description) : [null];
//     $lastInserted = null;

//     foreach ($lines as $line) {
//         $trimmed = $line ? trim($line) : null;

//         $data = [
//             'title' => $title,
//             'description' => $trimmed ? '<p>' . e($trimmed) . '</p>' : null,
//             'idProgramme' => $idProgram,
//         ];

       
//         $id = DB::table('module_program_contents')->insertGetId($data);

       
//         $lastInserted = DB::table('module_program_contents')->where('id', $id)->first();
//     }

//     return $lastInserted; 
// }

public function saveContent($idProgram, $title = null, $description = null)
{
    $data = [
        'title' => $title, // null
        'description' => $description ?  : null,
        'idProgramme' => $idProgram
    ];

    $id = DB::table('module_program_contents')->insertGetId($data);
    return DB::table('module_program_contents')->where('id', $id)->first();
}

}