<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $task;
    protected User $assignedBy;

    public function __construct(Task $task, User $assignedBy)
    {
        $this->task = $task;
        $this->assignedBy = $assignedBy;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned - ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new task has been assigned to you.')
            ->line('Task: ' . $this->task->title)
            ->line('Project: ' . $this->task->project->name)
            ->line('Assigned by: ' . $this->assignedBy->name)
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->when($this->task->due_date, fn($mail) => $mail->line('Due date: ' . $this->task->due_date->format('M d, Y')))
            ->when($this->task->estimated_time, fn($mail) => $mail->line('Estimated time: ' . $this->task->estimated_time . ' hours'))
            ->action('View Task', url('/tasks/' . $this->task->id))
            ->line('Please review and start working on this task.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'task_type' => $this->task->type,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'assigned_by' => $this->assignedBy->name,
            'assigned_by_id' => $this->assignedBy->id,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date?->format('Y-m-d'),
            'message' => "You have been assigned a new task '{$this->task->title}' by {$this->assignedBy->name}.",
            'url' => url("/projects/{$this->task->project_id}/tasks/{$this->task->id}"),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
