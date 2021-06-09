<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testSubmissionsHaveAOneToManyRelationshipWithPublications()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #1',
        ]);
        Publication::factory()->count(5)->create();
        $submissions = Submission::factory()->count(10)->for($publication)->create();
        Submission::factory()->count(16)->create();
        $this->assertEquals(1, $submissions->pluck('publication_id')->unique()->count());
        $this->assertGreaterThanOrEqual(10, $publication->submissions->count());
        $this->assertLessThanOrEqual(26, $publication->submissions->count());
    }

    /**
     * @return void
     */
    public function testSubmissionsHaveAManyToManyRelationshipWithUsers()
    {
        $submission_count = 4;
        $user_count = 6;
        $submitter_id = Role::where('name', Role::SUBMITTER)->first()->id;
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);
        $users = User::factory()->count($user_count)->create();

        // Create submissions and attach them to users randomly with random roles
        for ($i = 0; $i < $submission_count; $i++) {
            $random_role_id = Role::whereIn(
                'name',
                [
                    Role::REVIEW_COORDINATOR,
                    Role::REVIEWER,
                    Role::SUBMITTER,
                ]
            )
                ->get()
                ->pluck('id')
                ->random();
            $submission = Submission::factory()->hasAttached(
                $users->random(),
                [
                    'role_id' => $random_role_id,
                ]
            )
                ->for($publication)
                ->create();

            // Ensure at least one Submitter is attached if one was not previously attached
            if ($random_role_id !== $submitter_id) {
                $submission->users()->attach(
                    $users->random(),
                    [
                        'role_id' => $submitter_id,
                    ]
                );
            }
        }
        Submission::all()->map(function ($submission) use ($user_count) {
            $this->assertGreaterThan(0, $submission->users->count());
            $this->assertLessThanOrEqual($user_count, $submission->users->count());
        });
        User::all()->map(function ($user) use ($submission_count) {
            $this->assertLessThanOrEqual($submission_count, $user->submissions->count());
        });
    }

    /**
     * @return void
     */
    public function testIndividualSubmissionsCanBeQueriedById()
    {
        $submission = Submission::factory()->create([
            'title' => 'Test Submission for Querying an Individual Submission',
        ]);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                    title
                }
            }',
            [ 'id' => $submission->id ]
        );
        $expected_data = [
            'submission' => [
                'id' => (string)$submission->id,
                'title' => 'Test Submission for Querying an Individual Submission',
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllSubmissionsCanBeQueried()
    {
        $submission_1 = Submission::factory()->create([
            'title' => 'Test Submission #1 for Querying All Submissions',
        ]);
        $submission_2 = Submission::factory()->create([
            'title' => 'Test Submission #2 for Querying All Submissions',
        ]);
        $response = $this->graphQL(
            'query GetSubmissions {
                submissions {
                    data {
                        id
                        title
                    }
                }
            }'
        );
        $expected_data = [
            'submissions' => [
                'data' => [
                    [
                        'id' => (string)$submission_1->id,
                        'title' => 'Test Submission #1 for Querying All Submissions',
                    ],
                    [
                        'id' => (string)$submission_2->id,
                        'title' => 'Test Submission #2 for Querying All Submissions',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testSubmissionsCanBeQueriedForAPublication()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #3',
        ]);
        $submission = Submission::factory()->hasAttached(
            User::factory()->create(),
            [
                'role_id' => Role::where('name', Role::SUBMITTER)->first()->id,
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Submission for Publication #3',
            ]);
        $response = $this->graphQL(
            'query GetSubmissionsByPublication($id: ID!) {
                publication (id: $id) {
                    id
                    name
                    submissions {
                        id
                        title
                    }
                }
            }',
            [ 'id' => $publication->id ]
        );
        $expected_data = [
            'publication' => [
                'id' => (string)$publication->id,
                'name' => 'Test Publication #3',
                'submissions' => [
                    [
                        'id' => (string)$submission->id,
                        'title' => 'Submission for Publication #3',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllSubmissionsCanBeQueriedForAUser()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #4',
        ]);
        $user = User::factory()->create([
            'name' => 'Test User With Submission #1',
        ]);
        $submission = Submission::factory()->hasAttached(
            $user,
            [
                'role_id' => Role::where('name', Role::SUBMITTER)->first()->id,
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission for Test User With Submission #1',
            ]);

        $response = $this->graphQL(
            'query GetSubmissionsByUser($id: ID!) {
                user (id: $id) {
                    id
                    name
                    submissions {
                        id
                        title
                    }
                }
            }',
            [ 'id' => $user->id ]
        );
        $expected_data = [
            'user' => [
                'id' => (string)$user->id,
                'name' => 'Test User With Submission #1',
                'submissions' => [
                    [
                        'id' => (string)$submission->id,
                        'title' => 'Test Submission for Test User With Submission #1',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }
}