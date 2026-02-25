<?php

namespace App\Services;

use App\Models\GuestFeedbackSubmission;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Mentor KPI calculation based on:
 * - Guest feedback (at least 4 submissions per guest per 4 weeks; weekly submissions; quality)
 * - Guest attendance (mentor's guests)
 * - Project delivery (at least 2 projects per group, group size 2-3+)
 */
class MentorKpiService
{
    protected AbsenceTrackingService $absenceService;

    public function __construct(AbsenceTrackingService $absenceService)
    {
        $this->absenceService = $absenceService;
    }

    /** Minimum group size for project delivery to count */
    public const MIN_GROUP_SIZE = 2;

    /** Minimum projects per group for delivery target */
    public const MIN_PROJECTS_PER_GROUP = 2;

    /** Target: 4 feedback submissions per guest within 4 weeks */
    public const FEEDBACK_SUBMISSIONS_PER_GUEST_4_WEEKS = 4;

    /** KPI weights (0-100 total) */
    public const WEIGHT_FEEDBACK = 40;
    public const WEIGHT_ATTENDANCE = 30;
    public const WEIGHT_PROJECT_DELIVERY = 30;

    /**
     * Get all mentors in workspace (members who created at least one guest).
     */
    public function getMentorsInWorkspace(int $workspaceId): Collection
    {
        $mentorIds = \DB::table('workspace_user')
            ->where('workspace_id', $workspaceId)
            ->where('role', 'guest')
            ->whereNotNull('created_by_user_id')
            ->pluck('created_by_user_id')
            ->unique()
            ->filter();

        return User::whereIn('id', $mentorIds)->orderBy('name')->get();
    }

    /**
     * Get full KPI breakdown for a mentor (or all mentors).
     *
     * @param int $workspaceId
     * @param Carbon|null $periodEnd End of period (default today)
     * @param int|null $weeks Period length in weeks (default 4)
     * @param int|null $mentorId If set, only this mentor; otherwise all
     * @return Collection|array
     */
    public function getKpiForMentors(int $workspaceId, ?Carbon $periodEnd = null, int $weeks = 4, ?int $mentorId = null)
    {
        $periodEnd = $periodEnd ?? today();
        $periodEnd = $periodEnd->copy()->endOfDay();
        $periodStart = $periodEnd->copy()->subWeeks($weeks)->startOfDay();

        $mentors = $mentorId
            ? User::where('id', $mentorId)->get()
            : $this->getMentorsInWorkspace($workspaceId);

        $result = collect();
        foreach ($mentors as $mentor) {
            $guests = $mentor->getCreatedGuestsInWorkspace($workspaceId);
            $feedback = $this->getFeedbackMetrics($workspaceId, $mentor->id, $guests, $periodStart, $periodEnd);
            $attendance = $this->getAttendanceMetrics($workspaceId, $guests, $periodEnd);
            $projects = $this->getProjectDeliveryMetrics($workspaceId, $mentor->id, $periodStart, $periodEnd);

            $scores = [
                'feedback_score' => $this->scoreFeedback($feedback, $guests->count()),
                'attendance_score' => $this->scoreAttendance($attendance, $guests->count()),
                'project_delivery_score' => $this->scoreProjectDelivery($projects),
            ];
            $overall = (int) round(
                ($scores['feedback_score'] * self::WEIGHT_FEEDBACK / 100) +
                ($scores['attendance_score'] * self::WEIGHT_ATTENDANCE / 100) +
                ($scores['project_delivery_score'] * self::WEIGHT_PROJECT_DELIVERY / 100)
            );
            $overall = min(100, max(0, $overall));

            $result->push([
                'mentor' => $mentor,
                'guests_count' => $guests->count(),
                'feedback' => $feedback,
                'attendance' => $attendance,
                'projects' => $projects,
                'scores' => $scores,
                'kpi_total' => $overall,
                'meets_target' => $this->meetsTargets($feedback, $attendance, $projects, $guests->count()),
            ]);
        }

        return $mentorId ? $result->first() : $result->sortByDesc('kpi_total')->values();
    }

    protected function getFeedbackMetrics(int $workspaceId, int $mentorId, Collection $guests, Carbon $periodStart, Carbon $periodEnd): array
    {
        $submissions = GuestFeedbackSubmission::where('workspace_id', $workspaceId)
            ->where('mentor_id', $mentorId)
            ->whereBetween('submitted_at', [$periodStart, $periodEnd])
            ->with('answers.question', 'answers.option')
            ->get();

        $perGuest = [];
        $weeklyCounts = [];
        $totalScore = 0;
        $scoreCount = 0;

        foreach ($guests as $guest) {
            $guestSubs = $submissions->where('guest_id', $guest->id);
            $perGuest[$guest->id] = $guestSubs->count();
            foreach ($guestSubs as $sub) {
                foreach ($sub->answers as $ans) {
                    $v = $ans->getScoreValue();
                    if ($v !== null) {
                        $totalScore += $v;
                        $scoreCount++;
                    }
                }
            }
        }

        foreach ($submissions->groupBy(fn ($s) => $s->submitted_at->format('Y-W')) as $week => $items) {
            $weeklyCounts[$week] = $items->count();
        }

        $avgScore = $scoreCount > 0 ? $totalScore / $scoreCount : null;
        $guestsWithEnough = $guests->filter(fn ($g) => ($perGuest[$g->id] ?? 0) >= self::FEEDBACK_SUBMISSIONS_PER_GUEST_4_WEEKS)->count();

        return [
            'total_submissions' => $submissions->count(),
            'submissions_per_guest' => $perGuest,
            'guests_with_at_least_4' => $guestsWithEnough,
            'weekly_submissions' => $weeklyCounts,
            'average_score' => $avgScore,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ];
    }

    protected function getAttendanceMetrics(int $workspaceId, Collection $guests, Carbon $asOf): array
    {
        $guestAbsence = [];
        $totalAbsence = 0;
        foreach ($guests as $guest) {
            $days = $this->absenceService->getTotalAbsenceDaysForGuest($workspaceId, $guest->id, $asOf);
            $guestAbsence[$guest->id] = $days;
            $totalAbsence += $days;
        }
        $guestsWithGoodAttendance = $guests->filter(fn ($g) => ($guestAbsence[$g->id] ?? 0) <= 3)->count();
        return [
            'guest_absence_days' => $guestAbsence,
            'total_absence_days' => $totalAbsence,
            'guests_with_good_attendance' => $guestsWithGoodAttendance,
            'total_guests' => $guests->count(),
        ];
    }

    protected function getProjectDeliveryMetrics(int $workspaceId, int $mentorId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $projects = Project::where('workspace_id', $workspaceId)
            ->where('created_by_user_id', $mentorId)
            ->whereNotNull('group_id')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with('group.guests')
            ->get();

        $byGroup = [];
        foreach ($projects->groupBy('group_id') as $gid => $projs) {
            $group = $projs->first()->group;
            if (!$group) continue;
            $size = $group->guests()->count();
            if ($size >= self::MIN_GROUP_SIZE) {
                $byGroup[$gid] = [
                    'group' => $group,
                    'count' => $projs->count(),
                    'size' => $size,
                ];
            }
        }
        $groupsMeetingTarget = collect($byGroup)->filter(fn ($v) => $v['count'] >= self::MIN_PROJECTS_PER_GROUP)->count();
        return [
            'projects_count' => $projects->count(),
            'by_group' => $byGroup,
            'groups_with_at_least_2_projects' => $groupsMeetingTarget,
            'total_valid_groups' => count($byGroup),
        ];
    }

    protected function scoreFeedback(array $feedback, int $guestsCount): float
    {
        if ($guestsCount === 0) return 0;
        $guestsWithEnough = $feedback['guests_with_at_least_4'] ?? 0;
        $ratio = $guestsWithEnough / $guestsCount;
        $quantityScore = min(1, $ratio) * 60;
        $avg = $feedback['average_score'] ?? 0;
        $maxRating = 5;
        $qualityScore = $avg ? min(40, ($avg / $maxRating) * 40) : 0;
        return min(100, $quantityScore + $qualityScore);
    }

    protected function scoreAttendance(array $attendance, int $guestsCount): float
    {
        if ($guestsCount === 0) return 100;
        $good = $attendance['guests_with_good_attendance'] ?? 0;
        return min(100, ($good / $guestsCount) * 100);
    }

    protected function scoreProjectDelivery(array $projects): float
    {
        $validGroups = $projects['total_valid_groups'] ?? 0;
        if ($validGroups === 0) return 0;
        $meeting = $projects['groups_with_at_least_2_projects'] ?? 0;
        return min(100, ($meeting / $validGroups) * 100);
    }

    protected function meetsTargets(array $feedback, array $attendance, array $projects, int $guestsCount): bool
    {
        if ($guestsCount === 0) return false;
        $feedbackOk = ($feedback['guests_with_at_least_4'] ?? 0) >= $guestsCount;
        $attendanceOk = ($attendance['guests_with_good_attendance'] ?? 0) >= $guestsCount * 0.8;
        $projectsOk = ($projects['groups_with_at_least_2_projects'] ?? 0) >= 1 || $projects['total_valid_groups'] === 0;
        return $feedbackOk && $attendanceOk && $projectsOk;
    }

    /**
     * Weekly KPI progression for a mentor (last N weeks).
     */
    public function getWeeklyProgression(int $workspaceId, int $mentorId, int $weeks = 8): array
    {
        $end = today();
        $data = [];
        for ($i = 0; $i < $weeks; $i++) {
            $weekEnd = $end->copy()->subWeeks($i);
            $kpi = $this->getKpiForMentors($workspaceId, $weekEnd, 1, $mentorId);
            $data[] = [
                'week_end' => $weekEnd->format('Y-m-d'),
                'kpi' => $kpi['kpi_total'] ?? 0,
            ];
        }
        return array_reverse($data);
    }
}
