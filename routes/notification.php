<?php

use App\Custom\FakerTester;
use App\Http\Controllers\Notifications\LoginPlayerController;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::post('login', [LoginPlayerController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::prefix('notifications')->group(function() {
        Route::get('', function(){
            return response()->json([]);
        });
        // Route::post('login', [LoginPlayerController::class, 'login']);
        // Route::post('login', [LoginPlayerController::class, 'login']);
        // Route::post('login', [LoginPlayerController::class, 'login']);
    });


    Route::prefix('payments')->group(function() {

        Route::get('statistics', function(){
            return response()->json([]);
        });

        Route::get('', function(){
            $response = [];

            $tester = new FakerTester;

            for ($i=0; $i < 5 ; $i++) {

                $response[] = [
                    'id' => Str::uuid(),
                    'invoice_id' => '1',
                    'invoice_number' => 'FAC-'.$tester->identification(),
                    'amount' => (double) 50000 + $i,
                    'description' => 'aquí irian todos los items y valor',
                    'reference_number' => $tester->identification(),
                    'payment_method' => $tester->payment_method(), // 'CASH','CARD','TRANSFER','CHECK','OTHER'
                    'status' => $tester->status(), //PENDING, PARTIAL, PAID, CANCELLED
                    'image_url' => null,
                    'due_date' => now()->addDays(15)->timestamp,
                    'created_at' => now()->timestamp,
                    'updated_at' => now()->timestamp,
                    'items' => [
                        [
                            'quantity',
                            'unit_price',
                            'total',
                        ]
                    ]
                ];
            }

            return response()->json($response, 200);

        });

        Route::post('', function(Request $request){

            $tester = new FakerTester;

            $school = School::find(1);
            $image = $tester->uploadFile($request->image, $school->slug);
            logger('payment', [$request->all(), $image]);

            $response = [
                'id' => Str::uuid(),
                'invoice_id' => '1',
                'invoice_number' => 'FAC-1010101',
                'amount' => 50000,
                'description' => 'aquí irian todos los items y valor',
                'reference_number' => '40303030303',
                'payment_method' => 'TRANSFER', // 'CASH','CARD','TRANSFER','CHECK','OTHER'
                'status' => 'PENDING', //PENDING, PARTIAL, PAID, CANCELLED
                'image_url' => null,
                'due_date' => now()->addDays(15)->timestamp,
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
                'items' => []
            ];

            return response()->json($response, 200);
        });
        // Route::post('login', [LoginPlayerController::class, 'login']);
        // Route::post('login', [LoginPlayerController::class, 'login']);
        // Route::post('login', [LoginPlayerController::class, 'login']);
    });




    Route::prefix('requests')->group(function() {
        Route::get('', function(){

            $response = [
                'id' => '',
                'user_id' => '',
                'user_name' => null,
                'type' => 'UNIFORM',
                'quantity' => 0,
                'size' => null,
                'brand' => null,
                'model' => null,
                'color' => null,
                'additional_notes' => null,
                'status' => 'PENDING',
                'created_at' => null,
                'updated_at' => null,
                'approved_at' => null,
                'delivered_at' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ];

            return response()->json([$response], 200);

        });

        Route::post('', function(Request $request){
            $response = [
                'id' => Str::uuid(),
                'user_id' => '',
                'user_name' => null,
                'type' => 'UNIFORM',
                'quantity' => 0,
                'size' => null,
                'brand' => null,
                'model' => null,
                'color' => null,
                'additional_notes' => null,
                'status' => 'PENDING',
                'created_at' => null,
                'updated_at' => null,
                'approved_at' => null,
                'delivered_at' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ];
            return response()->json($response, 200);
        });




        Route::get('statistics', function(Request $request){

            $response = [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'delivered' => 0,
                'rejected' => 0,
                'cancelled' => 0,
            ];

            return response()->json($response, 200);
        });
        // Route::post('login', [LoginPlayerController::class, 'login']);
        // Route::post('login', [LoginPlayerController::class, 'login']);
        // Route::post('login', [LoginPlayerController::class, 'login']);
    });
        // TODO:rutas

});
