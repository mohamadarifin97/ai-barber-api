<?php

namespace Database\Seeders;

use App\Models\Queue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            Queue::create([
                'tel_no' => strval(rand(000000000000, 999999999999)),
                'queue_no' => sprintf("%03d", $i)
            ]);
        }
    }
}
