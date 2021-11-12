<?php


namespace App\Repositories\Contracts;


interface InscriptionRepositoryContract
{
    /**
     * @param $request
     * @return mixed
     */
    public function checkDocumentExists($request);

    public function checkUniqueCode($request);

    public function searchUniqueCode($request);

    public function findById($id, $trashed = false);
}
