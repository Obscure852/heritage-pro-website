<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicWebsiteHomepageTest extends TestCase
{
    public function test_homepage_presents_the_editorial_platform_narrative(): void
    {
        $response = $this->get(route('website.home'));

        $response
            ->assertOk()
            ->assertSee('The intelligent operating system for every school, college, and institution.')
            ->assertSee('Purpose-built for junior schools, senior schools, and tertiary institutions.')
            ->assertSee('Authorised Heritage Pro resellers.')
            ->assertSee('The people who build Heritage Pro.')
            ->assertSee('images/website/students-classroom.webp', false)
            ->assertSee('images/website/students-laptop.webp', false)
            ->assertSee('Book a demo');
    }

    public function test_homepage_lists_live_client_institutions(): void
    {
        $response = $this->get(route('website.home'));

        $response->assertOk();

        foreach (config('heritage_website.clients') as $client) {
            $response->assertSee($client['label']);
        }
    }

    public function test_homepage_demo_form_posts_to_the_book_demo_route(): void
    {
        $this->get(route('website.home'))
            ->assertOk()
            ->assertSee(route('website.book-demo'), false)
            ->assertSee('name="learner_band"', false)
            ->assertSee('name="edition"', false)
            ->assertSee('name="work_email"', false);
    }
}
