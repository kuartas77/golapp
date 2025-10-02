<?php

declare(strict_types=1);

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

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getAll()
    {
        $school = getSchool(auth()->user());
        return User::query()->with(['roles', 'profile'])
        ->select(['users.*','users.name as user_name', 'roles.name as role_name', 'roles.id as role_id'])
            ->join('schools_user', 'schools_user.user_id', 'users.id')
            ->join('model_has_roles', 'model_has_roles.model_id', 'users.id')
            ->join('roles', 'model_has_roles.role_id', 'roles.id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('schools_user.school_id', $school->id);
    }

    public function getAllTrash()
    {
        return getSchool(auth()->user())->users()->with(['roles', 'profile', 'school'])->onlyTrashed();
    }

    public function create(FormRequest $formRequest)
    {
        try {
            DB::beginTransaction();
            $school = getSchool(auth()->user());
            $user = $this->user->query()->create($formRequest->validated() + ['school_id' => $school->id]);
            $user->syncRoles([$formRequest->input('rol_id')]);
            $user->profile()->create();

            $schoolUser = new SchoolUser();
            $schoolUser->user_id = $user->id;
            $schoolUser->school_id = $school->id;
            $schoolUser->save();

            $user->notify(new RegisterNotification($user, ($formRequest->password ?? randomPassword())));
            DB::commit();
            Cache::forget('KEY_USERS_' . $school->id);

            alert()->success(__('messages.user_stored_success'));

            return $user;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("UserRepository create", $exception);
            alert()->error(__('messages.error'));
            return $this->user;
        }
    }

    public function update(User $user, FormRequest $formRequest): void
    {
        try {

            DB::beginTransaction();
            $user->update($formRequest->validated());
            $user->syncRoles([$formRequest->input('rol_id')]);
            Cache::forget('KEY_USERS_' . $user->school_id);
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
