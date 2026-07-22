<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Notifications\PaymentRequestController;
use App\Http\Controllers\Notifications\UniformRequestsController;
use App\Http\Controllers\Payments\TournamentPayoutsController;
use App\Models\PaymentRequest;
use App\Repositories\PaymentRequestRepository;
use App\Repositories\TournamentPayoutsRepository;
use App\Repositories\UniformRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

final class LegacyControllersCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testPaymentRequestIndexDelegatesForJsonRequestsAndAbortsForBladeAccess(): void
    {
        $repository = Mockery::mock(PaymentRequestRepository::class);
        $repository->shouldReceive('getPaymentRequestsQuery')
            ->once()
            ->andReturn(response()->json(['rows' => []]));

        $controller = new PaymentRequestController($repository);
        $response = $controller->index(Request::create('/api/v2/notifications/payment-requests', 'GET'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['rows' => []], $response->getData(true));

        $this->expectException(NotFoundHttpException::class);
        $controller->index(Request::create('/notifications/payment-requests', 'GET'));
    }

    public function testPaymentRequestProofServesStoredProofAndReturns404WhenMissing(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('proofs/payment.jpg', 'proof-content');

        $paymentRequest = new PaymentRequest(['image' => 'proofs/payment.jpg']);

        $repository = Mockery::mock(PaymentRequestRepository::class);
        $repository->shouldReceive('findForCurrentSchoolOrFail')->once()->with(15)->andReturn($paymentRequest);
        $repository->shouldReceive('findForCurrentSchoolOrFail')->once()->with(16)->andReturn(new PaymentRequest());

        $controller = new PaymentRequestController($repository);

        $response = $controller->proof(15);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('proof-content', $response->getContent());
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=0', $response->headers->get('Cache-Control'));

        $this->assertSame(404, $controller->proof(16)->getStatusCode());
    }

    public function testUniformRequestsIndexDelegatesOnlyForJsonRequests(): void
    {
        $repository = Mockery::mock(UniformRequestRepository::class);
        $repository->shouldReceive('queryTable')
            ->once()
            ->andReturn(response()->json(['rows' => []]));

        $controller = new UniformRequestsController($repository);
        $response = $controller->index(Request::create('/api/v2/notifications/uniform-requests', 'GET'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['rows' => []], $response->getData(true));

        $this->expectException(NotFoundHttpException::class);
        $controller->index(Request::create('/notifications/uniform-requests', 'GET'));
    }

    public function testTournamentPayoutControllerDelegatesRawSearchAndAjaxStore(): void
    {
        $repository = Mockery::mock(TournamentPayoutsRepository::class);
        $repository->shouldReceive('search')
            ->once()
            ->with(['tournament_id' => 1, 'competition_group_id' => 2, 'unique_code' => 'P-1'], true)
            ->andReturn(['rows' => collect()]);
        $repository->shouldReceive('create')
            ->once()
            ->with(['tournament_id' => 1, 'competition_group_id' => 2])
            ->andReturn(['created' => true]);

        $controller = new TournamentPayoutsController($repository);

        $resource = $controller->searchRaw(Request::create('/api/v2/tournament-payouts', 'GET', [
            'dataRaw' => true,
            'tournament_id' => 1,
            'competition_group_id' => 2,
            'unique_code' => 'P-1',
        ]));

        $this->assertSame([], $resource->toArray(Request::create('/')));

        $request = Request::create('/api/v2/tournament-payouts', 'POST', [
            'tournament_id' => 1,
            'competition_group_id' => 2,
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $controller->store($request);
        $this->assertSame(['created' => true], $response->getData(true));
    }
}
