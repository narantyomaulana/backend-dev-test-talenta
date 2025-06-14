<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TodoExport;


class TodoController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            Todo::validationRules()
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $todo = Todo::create([
            'title' => $request->title,
            'assignee' => $request->assignee,
            'due_date' => $request->due_date,
            'time_tracked' => $request->time_tracked ?? 0,
            'status' => $request->status ?? 'pending',
            'priority' => $request->priority ?? 'medium'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Todo created successfully',
            'data' => $todo
        ], 201);
    }


    public function index(Request $request)
    {
        $query = Todo::query();

        // Filter by title (partial match)
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by assignee (multiple values)
        if ($request->has('assignee')) {
            $assignees = explode(',', $request->assignee);
            $query->whereIn('assignee', $assignees);
        }

        // Filter by due_date (date range)
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('due_date', [$request->start, $request->end]);
        }

        // Filter by time_tracked (numeric range)
        if ($request->has('min') && $request->has('max')) {
            $query->whereBetween('time_tracked', [$request->min, $request->max]);
        }

        // Filter by status (multiple values)
        if ($request->has('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // Filter by priority (multiple values)
        if ($request->has('priority')) {
            $priorities = explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        $todos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $todos,
            'summary' => [
                'total_todos' => $todos->count(),
                'total_time_tracked' => $todos->sum('time_tracked')
            ]
        ]);
    }


    public function exportExcel(Request $request)
    {
        return Excel::download(new TodoExport($request), 'todos.xlsx');
    }


    public function chartStatus()
    {
        $statusSummary = Todo::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'status_summary' => [
                'pending' => $statusSummary['pending'] ?? 0,
                'open' => $statusSummary['open'] ?? 0,
                'in_progress' => $statusSummary['in_progress'] ?? 0,
                'completed' => $statusSummary['completed'] ?? 0
            ]
        ]);
    }

    /**
     * 3. Chart Data - Priority Summary
     */
    public function chartPriority()
    {
        $prioritySummary = Todo::selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        return response()->json([
            'priority_summary' => [
                'low' => $prioritySummary['low'] ?? 0,
                'medium' => $prioritySummary['medium'] ?? 0,
                'high' => $prioritySummary['high'] ?? 0
            ]
        ]);
    }

    /**
     * 3. Chart Data - Assignee Summary
     */

    public function chartAssignee()
    {
        $assigneeSummary = Todo::whereNotNull('assignee')
            ->selectRaw('assignee,
                count(*) as total_todos,
                sum(case when status = "pending" then 1 else 0 end) as total_pending_todos,
                sum(case when status = "completed" then time_tracked else 0 end) as total_timetracked_completed_todos')
            ->groupBy('assignee')
            ->get()
            ->mapWithKeys(function ($assignee) {
                return [$assignee->assignee => [
                    'total_todos' => $assignee->total_todos,
                    'total_pending_todos' => $assignee->total_pending_todos,
                    'total_timetracked_completed_todos' => (float) $assignee->total_timetracked_completed_todos
                ]];
            });

        return response()->json(['assignee_summary' => $assigneeSummary]);
    }
}
