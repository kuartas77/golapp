<?php


namespace App\Repositories\Contracts;


interface AssistRepositoryContract
{
    /**
     * @param $request
     * @param false $deleted
     * @return array
     */
    public function search($request, $deleted = false): array;

    /**
     * @param $request
     * @return array
     */
    public function create($request): array;

    /**
     * @param $assist
     * @param $request
     * @return bool
     */
    public function update($assist, $request): bool;

}
