<?php

namespace App\Service\API;

use stdClass;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
use App\Notifications\RegisterNotification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterService
{
    use ErrorTrait;
    use UploadFile;

    /**
     * @throws ValidationException
     */
    public function createUserSchoolUsesCase(FormRequest $request): stdClass
    {
        $response = new stdClass();

        try {
            DB::beginTransaction();

            $password = randomPassword();

            $user = User::query()->create([
                'name' => $request->agent,
                'email' => $request->email,
                'password' => $password
            ]);

            $user->syncRoles([User::SCHOOL]);
            $user->profile()->create();

            $validated = $request->validated();
            $validated['logo'] = $this->saveFile($request, 'logo');
            $validated['is_enable'] = ($request->is_enable ?? true);

            $school = School::query()->create($validated);

            $user->school_id = $school->id;
            $user->save();

            $relationSchool = new SchoolUser();
            $relationSchool->user_id = $user->id;
            $relationSchool->school_id = $school->id;
            $relationSchool->save();

            $user->notify(new RegisterNotification($user, $password));

            DB::commit();

            $response->success = true;

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError("RegisterController@register", $th);
            $response->error = $th->getMessage();
        }

        return $response;
    }
}
