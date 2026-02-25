<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $guest;
    protected Project $project;
    protected int $consecutiveAbsentDays;

    public function __construct(User $guest, Project $project, int $consecutiveAbsentDays)
    {
        $this->guest = $guest;
        $this->project = $project;
        $this->consecutiveAbsentDays = $consecutiveAbsentDays;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Attendance Warning - ' . $this->guest->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is an attendance warning for one of your team members.')
            ->line('Student: ' . $this->guest->name)
            ->line('Project: ' . $this->project->name)
            ->line('Consecutive absent days: ' . $this->consecutiveAbsentDays)
            ->action('View Attendance', url('/projects/' . $this->project->id . '/attendance'))
            ->line('Please follow up with this student.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'attendance_warning',
            'guest_id' => $this->guest->id,
            'guest_name' => $this->guest->name,
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'consecutive_absent_days' => $this->consecutiveAbsentDays,
            'message' => "{$this->guest->name} has been absent for {$this->consecutiveAbsentDays} consecutive days in project '{$this->project->name}'.",
        ];
    }
}
