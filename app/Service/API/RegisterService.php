<?php

namespace App\Service\API;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\SettingValue;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use stdClass;
use Throwable;

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

            Cache::forget('admin.schools');

            $response->success = true;

        } catch (Throwable $th) {
            DB::rollBack();
            $this->logError("RegisterController@register", $th);
            $response->error = $th->getMessage();
        }

        return $response;
    }

    public function updateSchoolUsesCase(Request $request, School $school)
    {
        try {

            $validated = $request->only(['name', 'email', 'agent', 'address', 'phone']);
            if ($request->hasFile('logo')) {
                $request->merge(['school_id' => $school->id]);
                $validated['logo'] = $this->saveFile($request, 'logo');
                Storage::disk('public')->delete($school->logo);
            }

            DB::beginTransaction();

            $school->fill($validated)->save();

            $settings = SettingValue::query()->where('school_id', $school->id)->get();
            $notify_payment_day = $settings->firstWhere('setting_key', 'NOTIFY_PAYMENT_DAY');
            $inscription_amount = $settings->firstWhere('setting_key', 'INSCRIPTION_AMOUNT');
            $monthly_payment = $settings->firstWhere('setting_key', 'MONTHLY_PAYMENT');
            $annuity = $settings->firstWhere('setting_key', 'ANNUITY');

            $notify_payment_day->update(['value' => $request->NOTIFY_PAYMENT_DAY]);
            $inscription_amount->update(['value' => $request->INSCRIPTION_AMOUNT]);
            $monthly_payment->update(['value' => $request->MONTHLY_PAYMENT]);
            $annuity->update(['value' => $request->ANNUITY]);

            DB::commit();

            $school->refresh()->load(['settingsValues']);

            $key = School::KEY_SCHOOL_CACHE . "_{$school->id}";
            $adminKey = School::KEY_SCHOOL_CACHE . "_admin_{$school->id}";
            Cache::forget($key);
            Cache::forget($adminKey);
            Cache::forget('admin.schools');
            Cache::remember($key, now()->addMinutes(env('SESSION_LIFETIME', 120)), fn() => $school);
            Cache::remember($adminKey, now()->addMinutes(env('SESSION_LIFETIME', 120)), fn() => $school);

        } catch (Throwable $th) {
            DB::rollBack();
            $this->logError('SchoolsController@update', $th);
        }
    }
}
