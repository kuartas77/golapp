<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Repositories\UniformRequestRepository;
use Illuminate\Http\Request;

class UniformRequestsController extends Controller
{
    public function __construct(private UniformRequestRepository $repository)
    {

    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->repository->queryTable();
        }
        return view('uniform-request.index');
    }
}
