<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProofPageTest extends TestCase
{
    public function test_root_opens_the_public_proof_page(): void
    {
        $this->get('/')->assertRedirect('/proof');

        $this->get('/proof')
            ->assertOk()
            ->assertSee('Revenue leaks rarely start at checkout.')
            ->assertSee('Run the 8-action rescue')
            ->assertSee('10')
            ->assertSee('Effectively-once business effect');
    }

    public function test_proof_page_links_the_repository_only_when_configured(): void
    {
        $this->get('/proof')->assertDontSee('>GitHub</a>', false);

        config()->set('rescue_lab.repository_url', 'https://github.com/example/rescue-lab');

        $this->get('/proof')
            ->assertOk()
            ->assertSee('GitHub')
            ->assertSee('https://github.com/example/rescue-lab', false);
    }

    public function test_production_proof_can_remain_read_only(): void
    {
        config()->set('rescue_lab.demo_mutations_enabled', false);

        $this->get('/proof')
            ->assertOk()
            ->assertDontSee('Run the 8-action rescue')
            ->assertSee('Review executable evidence');
    }
}
