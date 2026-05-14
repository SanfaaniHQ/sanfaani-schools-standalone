<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolPublicPage;
use App\Models\SchoolWebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSchoolPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_school_page_uses_canonical_school_slug_route(): void
    {
        $school = School::create([
            'name' => 'Greenfield Academy',
            'slug' => 'greenfield-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        SchoolPublicPage::create([
            'school_id' => $school->id,
            'slug' => 'greenfield-academy',
            'is_active' => true,
            'title' => 'Greenfield Academy',
            'headline' => 'Greenfield Academy Admissions and Results',
            'description' => 'A trusted school page for admissions, result checking, and parent contact.',
            'contact_email' => 'info@greenfield.test',
        ]);

        SchoolWebsiteSetting::create([
            'school_id' => $school->id,
            'website_mode' => 'inbuilt_website',
            'website_enabled' => true,
            'result_checker_enabled' => true,
            'admissions_enabled' => true,
            'contact_page_enabled' => true,
        ]);

        $this->get('/schools/greenfield-academy')
            ->assertOk()
            ->assertSee('Greenfield Academy Admissions and Results')
            ->assertSee('rel="canonical"', false)
            ->assertSee(route('public.schools.show', 'greenfield-academy'), false);

        $this->get('/s/greenfield-academy')
            ->assertOk()
            ->assertSee(route('public.schools.show', 'greenfield-academy'), false);
    }
}
