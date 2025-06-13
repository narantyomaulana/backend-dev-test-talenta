<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'assignee',
        'due_date',
        'time_tracked',
        'status',
        'priority'
    ];

    protected $casts = [
        'due_date' => 'date',
        'time_tracked' => 'decimal:2'
    ];

    public static function validationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'assignee' => 'nullable|string|max:255',
            'due_date' => 'required|date|after:today',
            'time_tracked' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,open,in_progress,completed',
            'priority' => 'nullable|in:low,medium,high'
        ];
    }
}
