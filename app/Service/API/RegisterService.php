<?php

namespace App\Service\API;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Service\Auth\AuthUserContext;
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

            if(!$request->is_campus){
                $user = User::query()->create([
                    'name' => $request->agent,
                    'email' => $request->email,
                    'password' => $password
                ]);

                $user->syncRoles([User::SCHOOL]);
                AuthUserContext::forgetUser($user->id);
                $user->profile()->create();
            }else{
                $user = User::query()->firstWhere('email', $request->email);
            }


            $validated = $request->validated();
            $validated['logo'] = $this->saveFile($request, 'logo');
            $validated['is_enable'] = false;

            $school = School::query()->create($validated);

            $user->school_id = $school->id;
            $user->save();

            $relationSchool = new SchoolUser();
            $relationSchool->user_id = $user->id;
            $relationSchool->school_id = $school->id;
            $relationSchool->save();

            if(!$request->is_campus){
                $user->notify(new RegisterNotification($user, $password));
            }

            DB::commit();

            Cache::forget('admin.schools');
            Cache::forget('SCHOOLS_ENABLED');

            $response->success = true;

        } catch (Throwable $th) {
            DB::rollBack();
            report($th);
            $response->error = $th->getMessage();
        }

        return $response;
    }

    public function updateSchoolUsesCase(Request $request, School $school): bool
    {
        $success = true;

        try {

            $validated = $request->only(['name', 'email', 'agent', 'address', 'phone']);
            foreach ([
                'create_contract',
                'send_documents',
                'send_monthly_payment_receipts',
                'tutor_platform',
                'sign_player',
                'inscriptions_enabled',
            ] as $field) {
                if ($request->has($field)) {
                    $validated[$field] = $request->boolean($field);
                }
            }

            if ($request->hasFile('logo')) {
                $request->merge(['school_id' => $school->id]);
                $validated['logo'] = $this->saveFile($request, 'logo');
                if(!is_null($school->logo)){
                    Storage::disk('public')->delete($school->logo);
                }
            }

            DB::beginTransaction();

            $school->fill($validated)->save();

            $settings = $school->loadMissing('settingsValues')->settingsValues;
            $notify_payment_day = $settings->firstWhere('setting_key', 'NOTIFY_PAYMENT_DAY');
            $inscription_amount = $settings->firstWhere('setting_key', 'INSCRIPTION_AMOUNT');
            $annuity = $settings->firstWhere('setting_key', 'ANNUITY');

            if($notify_payment_day && $request->has('NOTIFY_PAYMENT_DAY')) {
                $notify_payment_day->update(['value' => $request->NOTIFY_PAYMENT_DAY]);
            }
            if($inscription_amount && $request->has('INSCRIPTION_AMOUNT')) {
                $inscription_amount->update(['value' => $request->INSCRIPTION_AMOUNT]);
            }
            foreach (Setting::monthlyPaymentTypes() as $monthlyPaymentType) {
                $setting = $settings->firstWhere('setting_key', $monthlyPaymentType);

                if($setting && $request->has($monthlyPaymentType)) {
                    $setting->update(['value' => $request->input($monthlyPaymentType)]);
                }
            }
            if($annuity && $request->has('ANNUITY')) {
                $annuity->update(['value' => $request->ANNUITY]);
            }
            if ($request->has(Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED)) {
                $school->settingsValues()->updateOrCreate(
                    ['setting_key' => Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED],
                    ['value' => $request->boolean(Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED) ? '1' : '0']
                );
            }

            DB::commit();

            School::forgetCachedSchool($school->id);
            Cache::forget('admin.schools');

        } catch (Throwable $th) {
            DB::rollBack();
            report($th);
            $success = false;
        }

        return $success;
    }
}
