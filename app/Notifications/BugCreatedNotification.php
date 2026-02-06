<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $bug;
    protected Task $mainTask;
    protected User $creator;

    public function __construct(Task $bug, Task $mainTask, User $creator)
    {
        $this->bug = $bug;
        $this->mainTask = $mainTask;
        $this->creator = $creator;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Bug Created - ' . $this->bug->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new bug has been created for your task.')
            ->line('Bug: ' . $this->bug->title)
            ->line('Main Task: ' . $this->mainTask->title)
            ->line('Created by: ' . $this->creator->name)
            ->line('Estimated time: ' . $this->bug->estimated_time . ' hours')
            ->action('View Bug', url('/tasks/' . $this->bug->id))
            ->line('Please review and fix this bug.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'bug_created',
            'bug_id' => $this->bug->id,
            'bug_title' => $this->bug->title,
            'main_task_id' => $this->mainTask->id,
            'main_task_title' => $this->mainTask->title,
            'creator_name' => $this->creator->name,
            'creator_id' => $this->creator->id,
            'estimated_time' => $this->bug->estimated_time,
            'message' => "A new bug '{$this->bug->title}' has been created for your task '{$this->mainTask->title}' by {$this->creator->name}.",
        ];
    }
}
