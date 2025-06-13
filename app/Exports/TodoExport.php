<?php

namespace App\Exports;

use App\Models\Todo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class TodoExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Todo::query();

        if ($this->request->has('title')) {
            $query->where('title', 'like', '%' . $this->request->title . '%');
        }

        if ($this->request->has('assignee')) {
            $assignees = explode(',', $this->request->assignee);
            $query->whereIn('assignee', $assignees);
        }

        if ($this->request->has('start') && $this->request->has('end')) {
            $query->whereBetween('due_date', [$this->request->start, $this->request->end]);
        }

        if ($this->request->has('min') && $this->request->has('max')) {
            $query->whereBetween('time_tracked', [$this->request->min, $this->request->max]);
        }

        if ($this->request->has('status')) {
            $statuses = explode(',', $this->request->status);
            $query->whereIn('status', $statuses);
        }

        if ($this->request->has('priority')) {
            $priorities = explode(',', $this->request->priority);
            $query->whereIn('priority', $priorities);
        }

        $todos = $query->get();

        $todos->push((object)[
            'title' => 'SUMMARY',
            'assignee' => '',
            'due_date' => '',
            'time_tracked' => $todos->sum('time_tracked'),
            'status' => 'Total: ' . $todos->count(),
            'priority' => ''
        ]);

        return $todos;
    }

    public function headings(): array
    {
        return [
            'Title',
            'Assignee',
            'Due Date',
            'Time Tracked',
            'Status',
            'Priority'
        ];
    }

    public function map($todo): array
    {
        return [
            $todo->title,
            $todo->assignee,
            $todo->due_date,
            $todo->time_tracked,
            $todo->status,
            $todo->priority
        ];
    }
}
