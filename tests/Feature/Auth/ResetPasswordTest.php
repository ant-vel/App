<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Antvel\User\Models\Person;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ResetPasswordTest extends TestCase
{
	use DatabaseMigrations;

	public function test_a_reset_password_page_can_be_visited()
	{
		$this->get('/password/reset')
			->assertStatus(200)
			->assertSee('email');
	}

	public function test_a_user_can_request_to_reset_his_password()
	{
		$person = factory(Person::class)->create()->first();

		$response = $this->post('password/email', [
			'email' => $person->user->email
		]);

		$response->assertSessionHas('status');
	}
}
