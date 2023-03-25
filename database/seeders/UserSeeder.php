<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users_array = [
            [
                'name' => 'admin',
                'email' => 'admin@aibarber.com',
                'password' => 'asdf1234'
            ],
            [
                'name' => 'admin2',
                'email' => 'admin2@aibarber.com',
                'password' => 'asdf1234'
            ],
            [
                'name' => 'admin3',
                'email' => 'admin3@aibarber.com',
                'password' => 'asdf1234'
            ]
        ];

        foreach ($users_array as $user_array) {
            User::create([
                'name' => $user_array['name'],
                'email' => $user_array['email'],
                'password' => bcrypt($user_array['password'])
            ]);
        }

        // for ($i = 1; $i <= 10; $i++) {
        //     Queue::create([
        //         'tel_no' => strval(rand(000000000000, 999999999999)),
        //         'queue_no' => sprintf("%03d", $i)
        //     ]);
        // }
    }
}
