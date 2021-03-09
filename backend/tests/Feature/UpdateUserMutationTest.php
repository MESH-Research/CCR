<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UpdateUserMutationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testUserCanUpdateOwnData(): void
    {
        $profile_data_a = json_encode(['a' => 1]);
        $profile_data_b = json_encode(['b' => 2]);

        $user = User::factory()->create([
            'name' => 'test',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => $profile_data_a,
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!, $profile_metadata: JSON){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: $profile_metadata
                    }
                ) {
                    username
                    profile_metadata
                }
            }',
            [
                'id' => $user->id,
                'profile_metadata' => $profile_data_b,
            ]
        );

        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
        $response->assertJsonPath('data.updateUser.profile_metadata', $profile_data_b);
    }

    public function testUserCanUpdateOwnDataToBeTheSame()
    {
        $profile_data_a = json_encode(['a' => 1]);
        $user = User::factory()->create([
            'name' => 'testname',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => $profile_data_a,
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!, $profile_metadata: JSON){
                updateUser(
                    user: {
                        id: $id,
                        name: "testname",
                        email: "brandnew@gmail.com",
                        username: "testusername",
                        profile_metadata: $profile_metadata
                    }
                ) {
                    name
                    email
                    username
                    profile_metadata
                }
            }',
            [
                'id' => $user->id,
                'profile_metadata' => $profile_data_a,
            ]
        );
        $response->assertJsonPath('data.updateUser.name', 'testname');
        $response->assertJsonPath('data.updateUser.email', 'brandnew@gmail.com');
        $response->assertJsonPath('data.updateUser.username', 'testusername');
        $response->assertJsonPath('data.updateUser.profile_metadata', $profile_data_a);
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateOthersData(): void
    {
        $loggedInUser = User::factory()->create([
            'email' => 'loggedin@gmail.com',
            'username' => 'loggedinuser',
        ]);

        $profile_data_a = json_encode(['a' => 1]);
        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
            'profile_metadata' => $profile_data_a,
        ]);

        $this->actingAs($loggedInUser);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: $profile_data_a
                    }
                ) {
                    username
                    profile_metadata
                }
            }',
            [
                'id' => $userToUpdate->id,
                'profile_metadata' => $profile_data_a,
            ]
        );

        $response->assertJsonPath('data', null);
    }

    /**
     * @return void
     */
    public function testApplicationAdministratorCanUpdateOthersData(): void
    {
        $loggedInUser = User::factory()->create();
        $loggedInUser->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $profile_data_a = json_encode(['a' => 1]);
        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
            'profile_metadata' => $profile_data_a,
        ]);
        $this->actingAs($loggedInUser);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!, $profile_metadata: JSON){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: $profile_metadata

                    }
                ) {
                    username
                    profile_metadata
                }
            }',
            [
                'id' => $userToUpdate->id,
                'profile_metadata' => $profile_data_a,
            ]
        );
        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
        $response->assertJsonPath('data.updateUser.profile_metadata', $profile_data_a);
    }

    public function testApplicationAdministratorCanUpdateOthersDataToBeTheSame(): void
    {
        $loggedInUser = User::factory()->create();
        $loggedInUser->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $profile_data_a = json_encode(['a' => 1]);
        $userToUpdate = User::factory()->create([
            'name' => 'testname',
            'email' => 'testemail@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => $profile_data_a,
        ]);
        $this->actingAs($loggedInUser);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!, $profile_metadata: JSON){
                updateUser(
                    user: {
                        id: $id,
                        name: "testname",
                        email: "testemail@gmail.com",
                        username: "testusername"
                        profile_metadata: $profile_metadata
                    }
                ) {
                    name
                    email
                    username
                    profile_metadata
                }
            }',
            [
                'id' => $userToUpdate->id,
                'profile_metadata' => $profile_data_a,
            ]
        );
        $response->assertJsonPath('data.updateUser.name', 'testname');
        $response->assertJsonPath('data.updateUser.email', 'testemail@gmail.com');
        $response->assertJsonPath('data.updateUser.username', 'testusername');
        $response->assertJsonPath('data.updateUser.profile_metadata', $profile_data_a);
    }
}
