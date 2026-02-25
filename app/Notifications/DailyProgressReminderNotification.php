<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyProgressReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Project $project;
    protected float $completedHours;
    protected float $targetHours;

    public function __construct(Project $project, float $completedHours, float $targetHours = 6)
    {
        $this->project = $project;
        $this->completedHours = $completedHours;
        $this->targetHours = $targetHours;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $remainingHours = max(0, $this->targetHours - $this->completedHours);

        return (new MailMessage)
            ->subject('Daily Progress Reminder - ' . $this->project->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder about your daily progress.')
            ->line('Project: ' . $this->project->name)
            ->line('Completed today: ' . $this->completedHours . ' hours')
            ->line('Target: ' . $this->targetHours . ' hours')
            ->line('Remaining: ' . $remainingHours . ' hours')
            ->action('View Tasks', url('/projects/' . $this->project->id . '/tasks'))
            ->line($remainingHours > 0 ? 'Please complete your remaining tasks for today.' : 'Great job! You have met your daily target.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'daily_progress_reminder',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'completed_hours' => $this->completedHours,
            'target_hours' => $this->targetHours,
            'remaining_hours' => max(0, $this->targetHours - $this->completedHours),
            'message' => "You have completed {$this->completedHours} out of {$this->targetHours} hours today in project '{$this->project->name}'.",
        ];
    }
}
