<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

class PublicWebsiteProspectusTest extends TestCase
{
    public function test_prospectus_renders_every_block_of_the_document(): void
    {
        $prospectus = config('heritage_prospectus');

        $response = $this->get(route('website.prospectus'))
            ->assertOk()
            ->assertSee($prospectus['meta']['heading'])
            ->assertSee($prospectus['meta']['standfirst']);

        foreach ($prospectus['body'] as $block) {
            if ($block['type'] === 'list') {
                $response->assertSee($block['items'][0]);

                continue;
            }

            $response->assertSee($block['text']);
        }
    }

    public function test_contents_index_links_to_every_section_anchor(): void
    {
        $response = $this->get(route('website.prospectus'))->assertOk();

        $headings = collect(config('heritage_prospectus.body'))
            ->where('type', 'heading')
            ->pluck('text');

        $this->assertGreaterThan(0, $headings->count());

        foreach ($headings as $heading) {
            $slug = Str::slug($heading);

            $response
                ->assertSee('href="#' . $slug . '"', false)
                ->assertSee('id="' . $slug . '"', false);
        }
    }

    public function test_homepage_hero_links_to_the_prospectus(): void
    {
        $this->get(route('website.home'))
            ->assertOk()
            ->assertSee('Read the prospectus')
            ->assertSee(route('website.prospectus'), false);
    }
}
