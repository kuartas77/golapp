<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Requests\User\UserStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\User\UserUpdate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class UserController extends Controller
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $users = $this->repository->getAll();
        $usersTrash = $this->repository->getAllTrash();
        view()->share('users', $users);
        view()->share('usersTrash', $usersTrash);

        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        view()->share('roles', Role::query()->whereNotIn('id', [1,2])->pluck('name', 'id'));

        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserStore $request
     *
     * @return RedirectResponse
     */
    public function store(UserStore $request): RedirectResponse
    {
        $this->repository->create($request);

        return redirect()->to(route('users.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     * @return Factory|Application|RedirectResponse|View
     */
    public function show(User $user)
    {
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     *
     * @return Application|Factory|RedirectResponse|View
     */
    public function edit(User $user)
    {
        if (isAdmin()) {
            view()->share('roles', Role::query()->whereNotIn('id', [1,2])->pluck('name', 'id'));
            view()->share('user', $user->load('roles'));
            return view('admin.user.edit');
        } else {
            alert()->error(config('app.name'), __('messages.denied'));
        }

        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdate $request
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function update(UserUpdate $request, User $user): RedirectResponse
    {
        $this->repository->update($user, $request);

        if (isAdmin() || isSchool()) {
            return redirect()->to(route('users.index'));
        } elseif (isInstructor()) {
            return redirect()->to(route('home'));
        }
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     *
     * @return Application|Redirector|RedirectResponse
     * @throws Exception
     */
    public function destroy(User $user)
    {
        abort_unless(isAdmin() || isSchool(), 401);

        $user->delete();
        alert()->success(config('app.name'), __('messages.user_disabled'));

        return redirect(route('users.index'));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function activate($id): RedirectResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        if($this->repository->restore($id)){
            alert()->success(config('app.name'), __('messages.user_enabled'));
        }   

        return back();
    }
}
