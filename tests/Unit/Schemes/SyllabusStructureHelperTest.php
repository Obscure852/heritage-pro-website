<?php

namespace Tests\Unit\Schemes;

use App\Helpers\SyllabusStructureHelper;
use PHPUnit\Framework\TestCase;

class SyllabusStructureHelperTest extends TestCase
{
    public function test_it_parses_and_normalizes_wrapped_legacy_payload_into_objective_groups(): void
    {
        $payload = <<<'JSON'
{
  "syllabus": {
    "title": "Junior Secondary English Syllabus",
    "sections": [
      {
        "form": "Form 1",
        "units": [
          {
            "id": "1.1",
            "title": "Listening",
            "topics": [
              {
                "title": "Pronunciation",
                "general_objectives": [
                  "appreciate different speech sounds"
                ],
                "specific_objectives": [
                  "distinguish speech sounds correctly",
                  ""
                ]
              }
            ]
          }
        ]
      }
    ]
  }
}
JSON;

        $structure = SyllabusStructureHelper::parsePayload($payload);

        $this->assertSame('Junior Secondary English Syllabus', $structure['title']);
        $this->assertCount(1, $structure['sections']);
        $this->assertSame('Form 1', $structure['sections'][0]['form']);
        $this->assertSame('1.1', $structure['sections'][0]['units'][0]['id']);
        $this->assertSame('Pronunciation', $structure['sections'][0]['units'][0]['topics'][0]['title']);
        $this->assertSame(
            'Specific Objectives',
            $structure['sections'][0]['units'][0]['topics'][0]['objective_groups'][1]['label']
        );
        $this->assertSame(
            'distinguish speech sounds correctly',
            $structure['sections'][0]['units'][0]['topics'][0]['objective_groups'][1]['objectives'][0]['text']
        );
    }

    public function test_it_normalizes_nested_subtopics_and_objective_objects_without_losing_text(): void
    {
        $structure = SyllabusStructureHelper::normalize([
            'title' => 'English',
            'sections' => [
                [
                    'form' => 'Form 1',
                    'units' => [
                        [
                            'id' => '1.1',
                            'title' => 'Listening',
                            'topics' => [
                                [
                                    'title' => 'Pronunciation',
                                    'objectives' => [
                                        'general' => [
                                            [
                                                'code' => 'P-1',
                                                'objective_text' => 'identify speech sounds',
                                                'cognitive_level' => 'Knowledge',
                                            ],
                                        ],
                                    ],
                                    'sub_topics' => [
                                        [
                                            'title' => 'Vowels',
                                            'objectives' => [
                                                [
                                                    'text' => 'differentiate short and long vowels',
                                                    'code' => 'V-1',
                                                    'cognitive_level' => 'Application',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $topic = $structure['sections'][0]['units'][0]['topics'][0];
        $subtopic = $topic['subtopics'][0];

        $this->assertSame(['Pronunciation'], $topic['path']);
        $this->assertSame(['Pronunciation', 'Vowels'], $subtopic['path']);
        $this->assertSame('General', $topic['objective_groups'][0]['label']);
        $this->assertSame('identify speech sounds', $topic['objective_groups'][0]['objectives'][0]['text']);
        $this->assertSame('V-1', $subtopic['objective_groups'][0]['objectives'][0]['code']);
        $this->assertSame(
            'differentiate short and long vowels',
            $subtopic['objective_groups'][0]['objectives'][0]['text']
        );
    }

    public function test_it_exports_pretty_json_with_the_expected_wrapper_and_canonical_shape(): void
    {
        $json = SyllabusStructureHelper::toPrettyJson([
            'title' => 'English',
            'sections' => [
                [
                    'form' => 'Form 1',
                    'units' => [
                        [
                            'id' => '1.1',
                            'title' => 'Listening',
                            'topics' => [
                                [
                                    'title' => 'Pronunciation',
                                    'objective_groups' => [
                                        [
                                            'label' => 'Objectives',
                                            'objectives' => [
                                                ['text' => 'identify speech sounds'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertSame('English', $decoded['syllabus']['title']);
        $this->assertSame('Form 1', $decoded['syllabus']['sections'][0]['form']);
        $this->assertSame(
            'identify speech sounds',
            $decoded['syllabus']['sections'][0]['units'][0]['topics'][0]['objective_groups'][0]['objectives'][0]['text']
        );
    }
}
