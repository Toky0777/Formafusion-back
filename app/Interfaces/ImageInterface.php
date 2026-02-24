<?php

namespace App\Interfaces;

interface ImageInterface
{
    public function index($idProjet): mixed;
    public function store($idProjet, $url, $path, $fileName, $description, $idAddedBy, $mediaType): void;
    public function show($id): mixed;
    public function delete($idImage, $idAddedBy): void;
    public function update($idImage, $idAddedBy, $name = null, $description = null): void;
}
