<?php

namespace Tests\Feature\V1;

use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PositionTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_positions_list_fails_with_empty_database()
    {
        $response = $this->getJson(route('api.positions'));

        $response->assertStatus(404);

        $expectedResponse = $this->getExpectedErrorResponse(
            'Positions not found',
        );

        $response->assertJson($expectedResponse);
    }

    public function test_positions_list_successfully()
    {
        Position::factory()->create();

        $response = $this->getJson(route('api.positions'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'positions' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    }
}
