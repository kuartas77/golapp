<?php

namespace App\Http\Controllers\BackOffice;

use App\Models\School;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\RegisterNotification;

class ManualEmailController extends Controller
{
    use ErrorTrait;

    public function __invoke(Request $request)
    {
        try {
            $school = School::query()->with(['users'])->findOrFail((int)$request->school_id);

            foreach ($school->users as $user) {
                DB::beginTransaction();
                
                $user->password = $password = randomPassword();
                $user->save();
                $user->notify(new RegisterNotification($user, $password));

                DB::commit();
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError(__CLASS__, $th);
        }   
    }
}
