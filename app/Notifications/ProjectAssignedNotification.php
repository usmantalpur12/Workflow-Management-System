<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class ProjectAssignedNotification extends Notification
{
    use Queueable;

    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('New Project Assignment - ' . $this->project->name)
            ->view('emails.project-assigned', [
                'project' => $this->project,
                'user' => $notifiable
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'project_assigned',
            'message' => "New project '{$this->project->name}' has been assigned to your department",
            'data' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'department_id' => $notifiable->department_id,
            ],
        ];
    }
}
