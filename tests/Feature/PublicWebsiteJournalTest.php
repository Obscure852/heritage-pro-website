<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicWebsiteJournalTest extends TestCase
{
    public function test_journal_index_lists_every_article(): void
    {
        $response = $this->get(route('website.journal'));

        $response->assertOk();

        foreach (config('heritage_journal.articles') as $article) {
            $response
                ->assertSee($article['title'])
                ->assertSee($article['standfirst'])
                ->assertSee(route('website.journal.article', $article['slug']), false);
        }
    }

    public function test_each_article_renders_its_full_body(): void
    {
        foreach (config('heritage_journal.articles') as $article) {
            $response = $this->get(route('website.journal.article', $article['slug']));

            $response
                ->assertOk()
                ->assertSee($article['title'])
                ->assertSee($article['author']);

            foreach ($article['body'] as $block) {
                if ($block['type'] === 'list') {
                    $response->assertSee($block['items'][0]);

                    continue;
                }

                $response->assertSee($block['text']);
            }
        }
    }

    public function test_admissions_playbook_credits_the_institute_of_health_sciences(): void
    {
        $response = $this->get(route('website.journal.article', 'running-admissions-season-without-a-single-lost-application'));

        $response
            ->assertOk()
            ->assertSee('Institute of Health Sciences')
            ->assertSee('they used it to rank them')
            ->assertSee('ran offers this way')
            ->assertSee('enrolled their accepted candidates this way');
    }

    public function test_unknown_article_slug_returns_404(): void
    {
        $this->get(route('website.journal.article', 'no-such-article'))->assertNotFound();
    }

    public function test_homepage_links_the_three_latest_articles(): void
    {
        $response = $this->get(route('website.home'))->assertOk();

        foreach (array_slice(config('heritage_journal.articles'), 0, 3) as $article) {
            $response->assertSee(route('website.journal.article', $article['slug']), false);
        }

        $response->assertSee(route('website.journal'), false);
    }
}
