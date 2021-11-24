<?php

namespace App\Repositories;

use Exception;
use App\Models\User;
use App\Models\SchoolUser;
use App\Traits\ErrorTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Http\FormRequest;

class UserRepository
{
    use ErrorTrait;

    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->query()->with('roles')->where('id', '!=', 1)->get();
    }

    public function getAllTrash()
    {
        return $this->model->query()->with('roles')->onlyTrashed()->get();
    }

    public function create(FormRequest $request)
    {
        try {
            $school = auth()->user()->school;
            $user = $this->model->query()->create($request->validated());
            $user->syncRoles([$request->input('rol_id')]);

            $relationSchool = new SchoolUser();
            $relationSchool->user_id = $user->id;
            $relationSchool->school_id = $school->id;
            $relationSchool->save();

            //$user->notify(new RegisterNotification($user, $request->input('password')));
            alert()->success(__('messages.user_stored_success'));
            Cache::forget('users');
        } catch (Exception $exception) {
            $this->logError("UserRepository create", $exception);
            alert()->error(__('messages.error'));
        }
    }

    public function update(User $user, FormRequest $request)
    {
        try {

            DB::beginTransaction();
            $user->update($request->validated());
            $user->syncRoles([$request->input('rol_id')]);
            DB::commit();

            alert()->success(config('app.name'), __('messages.user_updated', ['user_name' => $user->name]));

        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("UserRepository update", $exception);
            alert()->error(__('messages.error'));
        }
    }

    public function restore(int $id)
    {
        return User::onlyTrashed()->where('id', $id)->restore();
    }
}
