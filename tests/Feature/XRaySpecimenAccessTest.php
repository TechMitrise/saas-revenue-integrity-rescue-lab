<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XRaySpecimenAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->withoutVite();
        config()->set('rescue_lab.xray.enabled', true);
        config()->set('rescue_lab.xray.access_code', 'specimen-pass');
        config()->set('rescue_lab.xray.cta_url', 'https://example.test/triage');
    }

    public function test_disabled_specimen_returns_not_found(): void
    {
        config()->set('rescue_lab.xray.enabled', false);

        $this->get('/critical-software-xray/sample/revenue-access-failure')->assertNotFound();
        $this->get('/critical-software-xray/sample/revenue-access-failure/access')->assertNotFound();
    }

    public function test_unauthorised_access_is_redirected_to_server_side_gate(): void
    {
        $this->get('/critical-software-xray/sample/revenue-access-failure')
            ->assertRedirect('/critical-software-xray/sample/revenue-access-failure/access');

        $this->get('/critical-software-xray/sample/revenue-access-failure/access')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Protected buyer specimen')
            ->assertDontSee('Executive Risk Brief');
    }

    public function test_valid_access_unlocks_session_and_lock_again_removes_it(): void
    {
        $this->post('/critical-software-xray/sample/revenue-access-failure/access', [
            'access_code' => 'specimen-pass',
        ])->assertRedirect('/critical-software-xray/sample/revenue-access-failure');

        $this->get('/critical-software-xray/sample/revenue-access-failure')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Executive Risk Brief')
            ->assertSee('Request a 20-minute Critical Software Risk Triage')
            ->assertSee('https://example.test/triage', false);

        $this->post('/critical-software-xray/sample/revenue-access-failure/lock')
            ->assertRedirect('/critical-software-xray/sample/revenue-access-failure/access');

        $this->get('/critical-software-xray/sample/revenue-access-failure')
            ->assertRedirect('/critical-software-xray/sample/revenue-access-failure/access');
    }

    public function test_invalid_access_is_rejected_and_rate_limited(): void
    {
        $this->from('/critical-software-xray/sample/revenue-access-failure/access')
            ->post('/critical-software-xray/sample/revenue-access-failure/access', ['access_code' => 'wrong'])
            ->assertRedirect('/critical-software-xray/sample/revenue-access-failure/access')
            ->assertSessionHasErrors('access_code');

        foreach (range(1, 19) as $_) {
            $this->post('/critical-software-xray/sample/revenue-access-failure/access', ['access_code' => 'wrong']);
        }

        $this->post('/critical-software-xray/sample/revenue-access-failure/access', ['access_code' => 'wrong'])
            ->assertTooManyRequests();
    }
}
