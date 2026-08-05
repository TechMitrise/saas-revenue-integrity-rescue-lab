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
            ->assertSee('Payment succeeded.')
            ->assertSee('Request a Legacy SaaS Risk Triage')
            ->assertSee('Legacy SaaS Risk &amp;', false)
            ->assertSee('USD 1,500-3,000')
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
            ->assertDontSee('Run the live proof')
            ->assertSee('Inspect the walkthrough');
    }

    public function test_proof_page_uses_the_configured_contact_url(): void
    {
        config()->set('rescue_lab.contact_url', 'https://example.test/risk-triage');

        $this->get('/proof')
            ->assertOk()
            ->assertSee('https://example.test/risk-triage', false)
            ->assertSee('Request a Legacy SaaS Risk Triage');
    }
}
