<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SettingAuditLog;
use Illuminate\Http\Request;
class GlobalSettingsController extends Controller
{
    /**
     * Show the global settings page (Admin / Owner only).
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        $user = auth()->user();
        if (!$user->isAdminInWorkspace($workspaceId) && !$user->isOwnerInWorkspace($workspaceId)) {
            abort(403, 'Only workspace administrators or owners can access global settings.');
        }

        $roundStartDate = Setting::getValue($workspaceId, Setting::KEY_ROUND_START_DATE);
        $auditLogs = SettingAuditLog::where('workspace_id', $workspaceId)
            ->with('changedBy:id,name')
            ->orderByDesc('changed_at')
            ->limit(20)
            ->get();

        return view('settings.index', [
            'roundStartDate' => $roundStartDate,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Update global settings (Admin / Owner only).
     */
    public function update(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        $user = auth()->user();
        if (!$user->isAdminInWorkspace($workspaceId) && !$user->isOwnerInWorkspace($workspaceId)) {
            abort(403, 'Only workspace administrators or owners can update global settings.');
        }

        $validated = $request->validate([
            'round_start_date' => ['nullable', 'date', 'after_or_equal:2000-01-01'],
        ], [
            'round_start_date.date' => 'Round start date must be a valid date.',
            'round_start_date.after_or_equal' => 'Round start date must be on or after 2000-01-01.',
        ]);

        $value = isset($validated['round_start_date']) ? $validated['round_start_date'] : null;
        Setting::setValue($workspaceId, Setting::KEY_ROUND_START_DATE, $value, $user->id);

        return redirect()->route('settings.index')
            ->with('success', 'Global settings have been saved.');
    }
}
