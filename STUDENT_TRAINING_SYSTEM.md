# Student Training System - Implementation Plan

**Branch:** `feature/student-training-system`  
**Date:** 2026-02-06  
**Status:** 🚧 In Progress

---

## 🎯 System Goal

Build a production-ready **student training & accountability system** where:
- Each guest (student) has mandatory daily work
- Progress is measured **per guest, per day**
- Attendance is **derived from completed work**
- Mentors validate progress
- Owners have full visibility

**This is NOT a generic project manager** — it's a training system with strict rules.

---

## 🔴 Critical Fixes (MUST DO FIRST)

### 1. Time Units Standardization (BLOCKER)
**Problem:** Mixed units (minutes in DB, hours in logic)  
**Solution:** Standardize to **HOURS** everywhere

**Changes needed:**
- [ ] Update migration: `tasks.estimated_time` comment from "Minutes" → "Hours"
- [ ] Update all validation to expect hours
- [ ] Update all service calculations to use hours
- [ ] Remove `estimated_minutes` column (redundant with polling system)

### 2. Migration Fixes
**Problems:**
- Duplicate column errors when re-running
- Missing `use Illuminate\Support\Facades\DB;`

**Changes needed:**
- [ ] Add DB import to migrations using `DB::select()`
- [ ] Improve `Schema::hasColumn()` checks to handle edge cases

### 3. Data Model Refactor (CRITICAL)
**Problem:** Current model = 1 project → 1 group → 1 track  
**Need:** 1 project → many guests from different tracks

**Solution:** New `project_members` pivot table

---

## 📊 New Data Model

### Core Tables

#### `project_members` (NEW)
```php
- project_id (FK)
- user_id (FK)
- role (enum: 'guest', 'tester', 'mentor')
- track_id (FK, nullable) // guest's track
- joined_at (timestamp)
```

#### `daily_progress` (REFACTORED)
```php
- id
- user_id (FK)
- project_id (FK)
- date (date)
- task_id (FK, nullable) // main task for the day
- required_hours (decimal: 6.00)
- completed_hours (decimal)
- progress_percentage (decimal: 0-100)
- approved (boolean: default false)
- approved_by_user_id (FK, nullable)
- approved_at (timestamp, nullable)
```

#### `attendances` (REFACTORED)
```php
- id
- user_id (FK)
- project_id (FK)
- date (date)
- status (enum: 'present', 'absent') // DERIVED from progress
- daily_progress_id (FK, nullable) // link to progress
- approved (boolean: default false)
- approved_by_user_id (FK, nullable)
- approved_at (timestamp, nullable)
```

---

## 🎯 Business Rules (Enforced in Services)

### Project Creation Rules
```php
// ProjectPlanningService::initializeProject()

1. Project MUST have:
   - ≥ 1 guest (from project_members)
   - start_date
   - total_days
   - exclude_weekends = true

2. Calculate:
   - working_days = calculateWorkingDays(start, end, exclude_weekends)
   - required_tasks = guests_count × working_days

3. Validation:
   - Each guest must have 1 main task per working day
   - Each main task must be ≥ 6 hours
   - Project cannot start until all tasks created
```

### Daily Progress Rules
```php
// DailyProgressService::calculateDailyProgress()

Input:
- guest_id
- project_id
- date

Logic:
1. Find main task for (guest, project, date)
2. Check if task status = 'done'
3. Calculate:
   completed_hours = task.estimated_hours (if done, else 0)
   progress = (completed_hours / 6) × 100
   progress = min(progress, 100) // cap at 100%

4. Store in daily_progress table
5. Trigger attendance calculation
```

### Attendance Rules
```php
// AttendanceService::deriveAttendance()

Logic:
1. Get daily_progress for (guest, date, project)
2. status = progress ≥ 100% ? 'present' : 'absent'
3. If no main task assigned → status = 'absent'
4. Attendance requires mentor approval to lock
```

### Weekly Progress
```php
// ProgressTrackingService::calculateWeeklyProgress()

weekly_progress = sum(daily_progress.progress_percentage) / working_days_in_week
weekly_hours = sum(daily_progress.completed_hours)
meets_target = weekly_hours ≥ 30
```

---

## 🧑‍💻 Implementation Order

### Phase 1: Critical Fixes & Data Model (Days 1-2)
- [x] Create branch
- [ ] Fix time units (migration comments + docs)
- [ ] Fix migration issues (DB import)
- [ ] Create `project_members` migration
- [ ] Refactor `daily_progress` migration
- [ ] Refactor `attendances` migration
- [ ] Update models with new relationships

### Phase 2: Service Layer (Days 3-4)
- [ ] Refactor `ProjectPlanningService` for multi-guest
- [ ] Create `DailyProgressService` with proper calculation
- [ ] Refactor `AttendanceService` for derived status
- [ ] Create `MentorApprovalService` for approval workflow
- [ ] Update `ProgressTrackingService` for per-guest calculation

### Phase 3: Controllers (Days 5-6)
- [ ] Update `ProjectController@store` with new workflow
- [ ] Create `GuestProgressController` for guest view
- [ ] Create `MentorApprovalController` for mentor workflow
- [ ] Create `OwnerDashboardController` for visibility
- [ ] Wire tester routes (existing controller)

### Phase 4: Views (Days 7-8)
- [ ] Update `projects/create.blade.php` with guest selection
- [ ] Create `guests/progress.blade.php` with daily/weekly bars
- [ ] Create `mentor/approval-dashboard.blade.php`
- [ ] Create `owner/overview-dashboard.blade.php`
- [ ] Create tester assignment views

### Phase 5: Testing & Documentation (Day 9-10)
- [ ] Write feature tests for each workflow
- [ ] Update `IMPLEMENTATION_STATUS.md`
- [ ] Update `SYSTEM_DESIGN.md`
- [ ] Create seed data for demo
- [ ] Write deployment guide

---

## 🔒 Success Criteria

### Must Pass Tests

#### Test 1: No Task = No Progress
```
Given: Guest has no main task today
When: System checks progress
Then: progress = 0%, attendance = absent
```

#### Test 2: Incomplete Task = No Progress
```
Given: Guest has main task (status != done)
When: System checks progress
Then: progress = 0%, attendance = absent
```

#### Test 3: Complete Task = Full Progress
```
Given: Guest completes 6-hour main task
When: System calculates progress
Then: progress = 100%, attendance = present (pending approval)
```

#### Test 4: Mentor Approval Locks Data
```
Given: Guest has progress = 100%
When: Mentor approves
Then: progress & attendance are locked (immutable)
```

#### Test 5: Bug Work Doesn't Count
```
Given: Guest completes bug task (not main task)
When: System calculates progress
Then: progress = 0% (bugs don't count)
```

---

## 📁 File Structure

```
app/
├── Models/
│   ├── ProjectMember.php (NEW)
│   ├── DailyProgress.php (REFACTORED)
│   ├── Attendance.php (REFACTORED)
│   └── Project.php (UPDATED)
├── Services/
│   ├── ProjectPlanningService.php (REFACTORED)
│   ├── DailyProgressService.php (NEW)
│   ├── AttendanceService.php (REFACTORED)
│   ├── MentorApprovalService.php (NEW)
│   └── ProgressTrackingService.php (UPDATED)
├── Http/
│   ├── Controllers/
│   │   ├── ProjectController.php (UPDATED)
│   │   ├── GuestProgressController.php (NEW)
│   │   ├── MentorApprovalController.php (NEW)
│   │   └── OwnerDashboardController.php (NEW)
│   └── Requests/
│       ├── StoreProjectRequest.php (UPDATED)
│       └── ApproveDailyProgressRequest.php (NEW)
└── Policies/
    └── ProjectPolicy.php (UPDATED)

database/migrations/
├── 2026_02_07_000001_create_project_members_table.php (NEW)
├── 2026_02_07_000002_refactor_daily_progress_table.php (NEW)
└── 2026_02_07_000003_refactor_attendances_table.php (NEW)

resources/views/
├── projects/
│   └── create.blade.php (UPDATED)
├── guests/
│   ├── progress.blade.php (NEW)
│   └── dashboard.blade.php (NEW)
├── mentor/
│   ├── approval-dashboard.blade.php (NEW)
│   └── guest-progress.blade.php (NEW)
└── owner/
    ├── overview.blade.php (NEW)
    └── project-stats.blade.php (NEW)
```

---

## 🚫 Forbidden Patterns

❌ **NEVER** calculate progress in Blade or JavaScript  
✅ Always use `DailyProgressService`

❌ **NEVER** manually set attendance status  
✅ Always derive from progress via `AttendanceService`

❌ **NEVER** mix time units  
✅ Always use HOURS

❌ **NEVER** use project progress for guest evaluation  
✅ Always use per-guest daily progress

---

## 📝 Notes

- All timestamps use `Carbon` for consistency
- All services return arrays with `['success' => bool, 'data' => ...]`
- All controllers use thin orchestration pattern
- All validation in Form Requests
- All authorization in Policies

---

**Status Legend:**
- 🔴 Blocker
- 🟡 In Progress
- 🟢 Complete
- ⚪ Not Started
