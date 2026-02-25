<?php

namespace App\Http\Controllers;

use App\Services\MentorKpiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MentorKpiController extends Controller
{
    protected MentorKpiService $kpiService;

    public function __construct(MentorKpiService $kpiService)
    {
        $this->kpiService = $kpiService;
    }

    /**
     * KPI dashboard: admin sees all mentors; mentor sees all (for transparency/benchmarking) but can filter to own.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }
        if (!$user->isMemberInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId)) {
            abort(403, 'Only members and admins can view KPI reports.');
        }
        $weeks = (int) $request->get('weeks', 4);
        $weeks = min(12, max(1, $weeks));
        $periodEnd = $request->date('period_end');
        if (!$periodEnd instanceof Carbon) {
            $periodEnd = today();
        }
        if ($periodEnd->isFuture()) {
            $periodEnd = today();
        }
        $mentorId = $request->get('mentor_id'); // optional filter to one mentor
        $kpiList = $this->kpiService->getKpiForMentors($workspaceId, $periodEnd, $weeks, $mentorId ? (int) $mentorId : null);
        if ($mentorId && is_array($kpiList)) {
            $kpiList = collect([$kpiList]);
        }
        $mentors = $this->kpiService->getMentorsInWorkspace($workspaceId);
        return view('kpi.index', compact('kpiList', 'mentors', 'weeks', 'periodEnd'));
    }

    /**
     * Single mentor KPI detail (progress, feedback received, project completion).
     */
    public function show(Request $request, int $mentorId)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        if (!$workspaceId || (!$user->isMemberInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId))) {
            abort(403);
        }
        $weeks = (int) $request->get('weeks', 4);
        $periodEnd = $request->date('period_end');
        if (!$periodEnd instanceof Carbon) {
            $periodEnd = today();
        }
        if ($periodEnd->isFuture()) {
            $periodEnd = today();
        }
        $data = $this->kpiService->getKpiForMentors($workspaceId, $periodEnd, $weeks, $mentorId);
        if (!$data) {
            abort(404);
        }
        $weeklyProgression = $this->kpiService->getWeeklyProgression($workspaceId, $mentorId, 8);
        return view('kpi.show', compact('data', 'weeklyProgression', 'weeks', 'periodEnd'));
    }
}
