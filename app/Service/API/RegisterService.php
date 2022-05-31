<?php

namespace App\Service\API;

use stdClass;
use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Traits\ErrorTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Notifications\RegisterNotification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterService
{
    use ErrorTrait;
    
    /**
     * @throws ValidationException
     */
    public function createUserSchoolUsesCase(FormRequest $request): stdClass
    {
        $response = new stdClass();
        
        try {
            DB::beginTransaction();
            
            $user = User::query()->create([
                'name' => $request->agent,
                'email' => $request->email,
                'password' => $request->password
            ]);

            $user->syncRoles([User::SCHOOL]);
            $user->profile()->create();

            $validated = $request->validated();
            $validated['is_enable'] = true;

            $school = School::query()->create($validated);

            $user->school_id = $school->id;
            $user->save();

            $relationSchool = new SchoolUser();
            $relationSchool->user_id = $user->id;
            $relationSchool->school_id = $school->id;
            $relationSchool->save();

            $school->trainingGroups()->update(['user_id' => $user->id]);

            $user->notify(new RegisterNotification($user, Str::of($request->password)->mask("*", 4)));

            DB::commit();

            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $response->user = $user;
            $response->token = $user->createToken($request->input('email'))->plainTextToken;

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError("RegisterController@register", $th);
            $response->error = $th->getMessage();
        }

        return $response;
    }
}
