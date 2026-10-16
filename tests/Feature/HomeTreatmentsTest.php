<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTreatmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_resin_veneers_treatment_is_shown(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('app.treatment1_title'))
            ->assertDontSee(__('app.treatment2_title'));
    }

    public function test_consult_treatment_opens_a_modal_with_prices_and_a_whatsapp_booking_link(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-modal-open="treatment1-dialog"', escape: false)
            ->assertSee('id="treatment1-dialog"', escape: false)
            ->assertSee(__('app.pricing_tier1_item2_price'))
            ->assertSee('https://wa.me/573115228585?text='.rawurlencode(__('app.treatment1_whatsapp_message')), escape: false);
    }
}
