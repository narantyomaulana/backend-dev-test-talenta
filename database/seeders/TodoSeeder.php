<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Todo;

class TodoSeeder extends Seeder
{
    public function run()
    {
        $todos = [
            [
                'title' => 'Setup Laravel Project',
                'assignee' => 'John',
                'due_date' => '2025-07-01',
                'time_tracked' => 3.5,
                'status' => 'completed',
                'priority' => 'high'
            ],
            [
                'title' => 'Create API Documentation',
                'assignee' => 'Doe',
                'due_date' => '2025-07-15',
                'time_tracked' => 2.0,
                'status' => 'in_progress',
                'priority' => 'medium'
            ],
            [
                'title' => 'Test API Endpoints',
                'assignee' => 'John',
                'due_date' => '2025-08-01',
                'time_tracked' => 0,
                'status' => 'pending',
                'priority' => 'low'
            ]
        ];

        foreach ($todos as $todo) {
            Todo::create($todo);
        }
    }
}