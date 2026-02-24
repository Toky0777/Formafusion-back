<?php
namespace App\Services;

use App\Interfaces\LearnerCourseRepository;
use Illuminate\Support\Facades\DB;

class LearnerCourseService implements LearnerCourseRepository{
    public function indexIntra()
    {
        $query = DB::table('detail_apprenants as da')
            ->select('da.idEmploye', 'p.idModule', 'mdls.moduleName as module_name')
            ->join('projets as p', 'da.idProjet', 'p.idProjet')
            ->join('mdls', 'p.idModule', 'mdls.idModule')
            ->join('project_forms as pf', 'da.idProjet', 'pf.idProjet')
            ->where('mdls.moduleName', '!=', "Default module")
            ->groupBy('da.idEmploye', 'p.idModule', 'mdls.moduleName')
            ->orderBy('da.idEmploye', 'desc');

        return $query;
    }

    public function indexInter()
    {
        $query = DB::table('detail_apprenant_inters as dai')
            ->select('dai.idEmploye', 'p.idModule', 'mdls.moduleName as module_name')
            ->join('projets as p', 'dai.idProjet', 'p.idProjet')
            ->join('mdls', 'p.idModule', 'mdls.idModule')
            ->join('project_forms as pf', 'dai.idProjet', 'pf.idProjet')
            ->where('mdls.moduleName', '!=', "Default module")
            ->groupBy('dai.idEmploye', 'p.idModule', 'mdls.moduleName')
            ->orderBy('dai.idEmploye', 'desc');

        return $query;
    }

    public function getLearnerCourse($idEmploye): mixed
    {
        $courseIntras = $this->indexIntra()
            ->where('da.idEmploye', $idEmploye)
            ->get();

        $courseInters = $this->indexInter()
            ->where('dai.idEmploye', $idEmploye)
            ->get();

        return array_merge($courseIntras->toArray(), $courseInters->toArray());
    }

    public function getLearnerCourseForm($idEmploye, $idFormateur): mixed
    {
        $courseIntras = $this->indexIntra()
            ->where('da.idEmploye', $idEmploye)
            ->where('pf.idFormateur', $idFormateur)
            ->get();

        $courseInters = $this->indexInter()
            ->where('dai.idEmploye', $idEmploye)
            ->where('pf.idFormateur', $idFormateur)
            ->get();

        return array_merge($courseIntras->toArray(), $courseInters->toArray());
    }
}