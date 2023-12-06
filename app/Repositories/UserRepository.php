<?php

namespace App\Repositories;

use App\Models\SchoolUser;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Traits\ErrorTrait;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        return getSchool(auth()->user())->users()->with(['roles', 'profile', 'school'])->get();
    }

    public function getAllTrash()
    {
        return getSchool(auth()->user())->users()->with(['roles', 'profile', 'school'])->onlyTrashed()->get();
    }

    public function create(FormRequest $request)
    {
        try {
            DB::beginTransaction();
            $school = getSchool(auth()->user());
            $user = $this->model->query()->create($request->validated() + ['school_id' => $school->id]);
            $user->syncRoles([$request->input('rol_id')]);
            $user->profile()->create();

            $relationSchool = new SchoolUser();
            $relationSchool->user_id = $user->id;
            $relationSchool->school_id = $school->id;
            $relationSchool->save();

            $user->notify(new RegisterNotification($user, ($request->password ?? randomPassword())));
            DB::commit();
            Cache::forget("KEY_USERS_{$school->id}");

            alert()->success(__('messages.user_stored_success'));

            return $user;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("UserRepository create", $exception);
            alert()->error(__('messages.error'));
            return $this->model;
        }
    }

    public function update(User $user, FormRequest $request)
    {
        try {

            DB::beginTransaction();
            $user->update($request->validated());
            $user->syncRoles([$request->input('rol_id')]);
            Cache::forget("KEY_USERS_{$user->school_id}");
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
