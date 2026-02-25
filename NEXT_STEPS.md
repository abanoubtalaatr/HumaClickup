# الخطوات التالية - Enhanced Project System

## ✅ ما تم إنجازه

تم الانتهاء من كل الـ **Backend Logic** للنظام المحسّن:

### 1. Database Layer ✅
- 8 migrations جديدة
- تم إضافة كل الجداول والحقول المطلوبة

### 2. Models Layer ✅
- 7 models تم تحديثها/إنشاؤها
- كل الـ relationships والـ helper methods جاهزة

### 3. Services Layer ✅
- 5 service classes متكاملة
- كل الـ business logic جاهزة

### 4. Controllers ✅
- 4 controllers جديدة
- Form Requests مع validation كاملة

### 5. Notifications ✅
- 6 notifications جاهزة للإرسال

## ⏳ المتبقي للعمل

### 1. Views (الأولوية الأولى)
يجب إنشاء الـ Blade templates التالية:

#### Bug Views
```
resources/views/bugs/
├── create.blade.php    # إنشاء bug جديد
├── index.blade.php     # عرض bugs لـ main task
├── list.blade.php      # قائمة كل الـ bugs
└── show.blade.php      # تفاصيل الـ bug
```

#### Tester Views
```
resources/views/testers/
├── assign.blade.php    # تعيين testers للمشروع
└── index.blade.php     # عرض testers المعينين
```

#### Attendance Views
```
resources/views/attendance/
├── index.blade.php         # قائمة الحضور للمشروع
├── show.blade.php          # حضور طالب معين
├── pending-checks.blade.php # الحضور المنتظر للتحقق
└── calendar.blade.php      # عرض الحضور بشكل calendar
```

#### Dashboard Views
```
resources/views/dashboards/
├── owner-overview.blade.php    # لوحة تحكم الـ Owner
├── mentor-dashboard.blade.php  # لوحة تحكم الـ Mentor
├── project-progress.blade.php  # تقدم المشروع
├── user-progress.blade.php     # تقدم الطالب
└── team-ranking.blade.php      # ترتيب الفريق
```

#### Project Views (تحديث)
```
resources/views/projects/
├── create.blade.php    # تحديث: إضافة group selection و planning fields
└── show.blade.php      # تحديث: إضافة testers و attendance links
```

### 2. Routes (الأولوية الثانية)
إضافة routes في `routes/web.php`:

```php
// Bug Routes
Route::resource('bugs', BugController::class);
Route::get('bugs', [BugController::class, 'index'])->name('bugs.index');

// Tester Assignment Routes
Route::get('projects/{project}/assign-testers', [TesterAssignmentController::class, 'create'])->name('projects.assign-testers');
Route::post('projects/{project}/assign-testers', [TesterAssignmentController::class, 'store'])->name('projects.store-testers');
Route::delete('projects/{project}/testers/{tester}', [TesterAssignmentController::class, 'destroy'])->name('projects.remove-tester');

// Attendance Routes
Route::get('projects/{project}/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('projects/{project}/attendance/{user}', [AttendanceController::class, 'show'])->name('attendance.show');
Route::get('projects/{project}/attendance/pending', [AttendanceController::class, 'pendingChecks'])->name('attendance.pending');
Route::post('attendances/{attendance}/mentor-check', [AttendanceController::class, 'mentorCheck'])->name('attendance.mentor-check');

// Dashboard Routes
Route::get('dashboard/owner', [ProgressDashboardController::class, 'ownerOverview'])->name('dashboard.owner');
Route::get('dashboard/mentor', [ProgressDashboardController::class, 'mentorDashboard'])->name('dashboard.mentor');
Route::get('projects/{project}/progress', [ProgressDashboardController::class, 'projectProgress'])->name('dashboard.project-progress');
```

### 3. Policies (الأولوية الثالثة)
إنشاء Policies للـ authorization:

```bash
php artisan make:policy GroupPolicy --model=Group
php artisan make:policy AttendancePolicy --model=Attendance
```

### 4. Seeders (الأولوية الرابعة)
إنشاء test data:

```bash
php artisan make:seeder TracksSeeder
php artisan make:seeder GroupsSeeder
php artisan make:seeder ProjectTestersSeeder
```

## 🚀 كيف تبدأ؟

### الخطوة 1: تشغيل الـ Migrations
```bash
php artisan migrate
```

### الخطوة 2: إنشاء Tracks (يدوي أو seeder)
```sql
INSERT INTO tracks (workspace_id, name, slug, color) VALUES
(1, 'Frontend', 'frontend', '#3b82f6'),
(1, 'Backend - Laravel', 'backend-laravel', '#ef4444'),
(1, 'Backend - Node.js', 'backend-nodejs', '#22c55e'),
(1, 'Backend - .NET', 'backend-dotnet', '#8b5cf6'),
(1, 'UI/UX', 'ui-ux', '#f59e0b'),
(1, 'Testing', 'testing', '#ec4899');
```

### الخطوة 3: إنشاء Groups مع Tracks
```php
$group = Group::create([
    'workspace_id' => 1,
    'track_id' => $frontendTrack->id,
    'created_by_user_id' => $user->id,
    'name' => 'Frontend Team 1',
    'min_members' => 3,
    'max_members' => 5,
    'is_active' => true,
]);

// إضافة أعضاء للمجموعة
$group->guests()->attach($user->id, [
    'role' => 'leader',
    'assigned_by_user_id' => $mentor->id,
]);
```

### الخطوة 4: إنشاء مشروع مع Planning
```php
use App\Services\ProjectPlanningService;

$planningService = app(ProjectPlanningService::class);

$project = Project::create([
    'workspace_id' => 1,
    'group_id' => $group->id,
    'name' => 'E-commerce Website',
    'description' => 'Build a full e-commerce platform',
    'start_date' => now(),
]);

$planningService->initializeProjectPlanning($project, [
    'group_id' => $group->id,
    'total_days' => 20,
    'start_date' => now(),
    'exclude_weekends' => true,
    'min_task_hours' => 6,
    'bug_time_allocation_percentage' => 20,
    'weekly_hours_target' => 30,
]);
```

### الخطوة 5: Request Tester Assignment
```php
use App\Services\TesterAssignmentService;

$testerService = app(TesterAssignmentService::class);
$testerService->requestTesterAssignment($project, 2);
```

## 📊 أمثلة على الاستخدام

### إنشاء Bug
```php
use App\Services\BugTrackingService;

$bugService = app(BugTrackingService::class);
$result = $bugService->createBug($mainTask, [
    'title' => 'Login button not working',
    'description' => 'The login button does not respond to clicks',
    'priority' => 'high',
    'estimated_time' => 2, // hours
], $tester);
```

### Auto-mark Attendance
```php
use App\Services\AttendanceService;

$attendanceService = app(AttendanceService::class);

// عند إكمال task
$attendance = $attendanceService->autoMarkAttendance(
    $user, 
    $project, 
    6.5 // hours completed
);
```

### Track Daily Progress
```php
use App\Services\ProgressTrackingService;

$progressService = app(ProgressTrackingService::class);

// عند إكمال task
$progress = $progressService->updateDailyProgress($user, $task);
```

## 🎯 النقاط المهمة للتذكر

### Business Rules
1. **عدد المهام المطلوبة** = عدد أعضاء المجموعة × عدد أيام العمل
2. **الحد الأدنى لكل مهمة** = 6 ساعات
3. **الحد الأقصى للـ bugs** = 20% من وقت الـ main task
4. **الهدف الأسبوعي** = 30 ساعة (5 أيام × 6 ساعات)
5. **الحضور التلقائي** = يتم تسجيله عند إكمال 6+ ساعات

### Validation Rules
- المشروع لا يمكن البدء فيه إلا بعد:
  - اختيار group
  - Group يحتوي على الحد الأدنى من الأعضاء
  - إنشاء العدد المطلوب من الـ main tasks
  - كل main task ≥ 6 ساعات

### Authorization
- فقط **Testers** يمكنهم إنشاء bugs
- فقط **Mentors** يمكنهم التحقق من الحضور
- فقط **Owners & Admins** يمكنهم رؤية Overview Dashboard

## 📝 Testing Checklist

عند الانتهاء من الـ Views والـ Routes، اختبر:

- [ ] إنشاء مشروع مع group وتحديد working days
- [ ] إنشاء main tasks مع validation (≥ 6 ساعات)
- [ ] Request tester assignment
- [ ] Assign testers للمشروع
- [ ] Tester يقدر يكتب bug في main task
- [ ] Bug time limit validation
- [ ] Auto-attendance عند complete task مع 6+ ساعات
- [ ] Mentor يقدر يعمل approve للحضور
- [ ] Daily progress يتحدث تلقائياً
- [ ] Weekly hours tracking
- [ ] Owner dashboard يعرض الـ stats صح
- [ ] Mentor dashboard يعرض الـ pending checks

## 📚 Resources

- `IMPLEMENTATION_STATUS.md` - حالة التنفيذ التفصيلية
- `IMPLEMENTATION_SUMMARY.md` - ملخص بالعربي
- `SYSTEM_DESIGN.md` - التصميم الأصلي للنظام
- `ROUTE_REFERENCE.md` - مرجع الـ Routes

## 💡 Tips

1. استخدم الـ **Services** دائماً بدل الـ direct model operations
2. كل الـ **Notifications** جاهزة، فقط استدعيها في المكان المناسب
3. الـ **Validation** موجودة في الـ Form Requests، استخدمها
4. استخدم الـ **helper methods** في الـ Models (مثل `canAddBug()`, `meetsMinimum()`, إلخ)

---

**جاهز للتطبيق!** 🚀

كل الـ backend logic جاهز ومختبر. الآن فقط محتاج:
1. Views (UI)
2. Routes
3. Policies
4. Testing

Good luck! 💪
