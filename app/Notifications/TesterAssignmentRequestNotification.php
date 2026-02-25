<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TesterAssignmentRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Project $project;
    protected int $requiredTestersCount;

    public function __construct(Project $project, int $requiredTestersCount = 2)
    {
        $this->project = $project;
        $this->requiredTestersCount = $requiredTestersCount;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tester Assignment Request - ' . $this->project->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new project requires testers to be assigned.')
            ->line('Project: ' . $this->project->name)
            ->line('Required testers: ' . $this->requiredTestersCount)
            ->action('Assign Testers', url('/projects/' . $this->project->id . '/assign-testers'))
            ->line('Please assign the required testers to this project.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'tester_assignment_request',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'required_testers_count' => $this->requiredTestersCount,
            'message' => "Project '{$this->project->name}' requires {$this->requiredTestersCount} testers to be assigned.",
            'url' => url("/projects/{$this->project->id}/assign-testers"),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
