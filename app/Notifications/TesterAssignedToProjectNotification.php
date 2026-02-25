<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TesterAssignedToProjectNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Project $project;
    protected User $assignedBy;

    public function __construct(Project $project, User $assignedBy)
    {
        $this->project = $project;
        $this->assignedBy = $assignedBy;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have been assigned as Tester - ' . $this->project->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned as a tester to a project.')
            ->line('Project: ' . $this->project->name)
            ->line('Assigned by: ' . $this->assignedBy->name)
            ->action('View Project', url('/projects/' . $this->project->id))
            ->line('You can now create bugs and test the project tasks.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'tester_assigned',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'assigned_by' => $this->assignedBy->name,
            'assigned_by_id' => $this->assignedBy->id,
            'message' => "You have been assigned as a tester to project '{$this->project->name}' by {$this->assignedBy->name}.",
        ];
    }
}
