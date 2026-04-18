<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Notifications\RegisterNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ManualEmailController extends Controller
{
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

        } catch (Throwable $th) {
            DB::rollBack();
            report($th);
        }
    }
}
