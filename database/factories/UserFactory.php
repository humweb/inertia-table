<?php

namespace Humweb\Table\Database\Factories;

use Humweb\Table\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password',

        ];
    }
}
