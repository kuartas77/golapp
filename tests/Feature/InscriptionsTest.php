<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Player;
use Tests\Feature\PlayersTest;

class InscriptionsTest extends TestCase
{
    /**
     * @depends Tests\Feature\PlayersTest::testPlayerCreate
     */
    public function testCreateInscription($dataPlayer)
    {
        $this->actingAs($this->user);
        $this->post('/players', $dataPlayer);
        unset($dataPlayer['people']);

        $this->assertDatabaseHas('players', ['unique_code' => $dataPlayer['unique_code']]);

        

    }
}
