<?php

namespace App\Interfaces;

interface LearnerCourseRepository
{
    public function indexIntra();
    public function indexInter();
    public function getLearnerCourse($idEmploye): mixed;
    public function getLearnerCourseForm($idEmploye, $idFormateur): mixed;
}
