<?php

namespace Database\Seeders;

use App\Models\Queue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('queues')->truncate();
        for ($i = 1; $i <= 10; $i++) {
            Queue::create([
                'tel_no' => strval(rand(000000000000, 999999999999)),
                'queue_no' => sprintf("%03d", $i),
                'status' => $i == 1 ? 'current' : ($i == 2 ? 'next' : 'upcoming')
            ]);
        }
    }
}
