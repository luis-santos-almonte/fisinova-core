<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\Patient::class;

    public function definition(): array
    {
        return [
            'firstname'   => $this->faker->firstName,
            'lastname'    => $this->faker->lastName,
            'dni'         => $this->faker->unique()->numerify('###########'), // 8 dígitos por ejemplo
            'passport'    => $this->faker->optional()->bothify('??######'), // ejemplo: AB123456
            'sex'         => $this->faker->randomElement(['male', 'female']),
            'birthdate'   => $this->faker->dateTimeBetween('-90 years', '-18 years'),
            'email'       => $this->faker->unique()->safeEmail,
            'phone'       => $this->faker->phoneNumber,
            'cellphone'   => $this->faker->phoneNumber,
            'address'     => $this->faker->address,
            'city'        => $this->faker->city,
            'active'      => $this->faker->boolean(90), // 90% probabilidad de estar activo
        ];
    }
}
