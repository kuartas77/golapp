<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncidentStore;
use App\Models\Incident;
use App\Repositories\IncidentRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class IncidentController extends Controller
{
    private IncidentRepository $repository;

    public function __construct(IncidentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|Factory|JsonResponse|View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
                ->collection($this->repository->all())
                ->toJson();
        }
        return view('incidents.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param IncidentStore $request
     * @return RedirectResponse
     */
    public function store(IncidentStore $request): RedirectResponse
    {
        $incident = $this->repository->createIncident($request);
        if ($incident->wasRecentlyCreated) {
            Alert::success(env('APP_NAME'), __('messages.incident_created'));
        } else {
            Alert::success(env('APP_NAME'), __('messages.incident_fail'));
        }
        return redirect()->to(route('incidents.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param $slug_name
     * @return Application|Factory|View
     */
    public function show($slug_name)
    {
        $incidents = $this->repository->get($slug_name);
        $professor = $incidents->first()->professor;

        view()->share('incidents', $incidents);
        view()->share('professor', $professor);
        return view('incidents.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Incident $incident
     * @return RedirectResponse
     */
    public function edit(Incident $incident): RedirectResponse
    {
        return redirect()->to(route('incidents.index'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Incident $incident
     * @return RedirectResponse
     */
    public function update(Request $request, Incident $incident): RedirectResponse
    {
        return redirect()->to(route('incidents.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Incident $incident
     * @return RedirectResponse
     */
    public function destroy(Incident $incident): RedirectResponse
    {
        return redirect()->to(route('incidents.index'));
    }
}
