# تقرير الكود الحالي — HumaClickup

## ١) نظرة عامة على البنية

- **التقنيات:** Laravel، Blade، Alpine.js، Spatie Permission، قاعدة بيانات مع علاقات وـ soft deletes.
- **التصميم:** Multi-tenant (كل شيء معزول بـ `workspace_id`)، Controllers + Services + Policies، وثائق في `SYSTEM_DESIGN.md` و `ROUTE_REFERENCE.md`.

---

## ٢) الهيكل الحالي للمجلدات والموديولات

| المسار | المحتوى |
|--------|---------|
| `app/Models/` | ٢٤ موديل (User, Workspace, Project, Task, Group, Track, Sprint, Tag, Attendance, GuestReport, ...) |
| `app/Http/Controllers/` | ١٦ كونترولر (Task, Project, Workspace, Group, Track, Attendance, GuestReport, TimeTracking, ...) |
| `app/Services/` | TaskService، ActivityLogService، TimeTrackingService |
| `app/Policies/` | ProjectPolicy، TaskPolicy، WorkspacePolicy |
| `database/migrations/` | ٤٣ migration (جداول، علاقات، إضافات لاحقة) |
| `resources/views/` | صفحات حسب الموديول (tasks, projects, groups, attendance, reports, dashboard, ...) |

---

## ٣) المستخدمون والأدوار (Workspace)

- **جدول الربط:** `workspace_user`  
  الحقول: `workspace_id`, `user_id`, `role`, `permissions`, `joined_at`, (وإضافات: `track`, `track_id`, `created_by_user_id` من migrations لاحقة).
- **الأدوار:** `owner` | `admin` | `member` | `guest`.
- **الصلاحيات:** محددة في `User::hasPermissionInWorkspace()` (مصفوفة صلاحيات حسب الدور).
- **التراك:** كل مستخدم في الـ workspace له `track_id` (اختياري) يربطه بتراك معين (Frontend, Backend, Testing, …).

---

## ٤) الـ Workspace والـ Tracks

- **Workspace:** اسم، وصف، مالك، إعدادات، حدود تخزين، إلخ.
- **Track:** مرتبط بـ workspace؛ له اسم، لون، ترتيب، نشاط. المستخدمون يُربطون بالتراك عبر pivot `workspace_user.track_id`.
- **لا يوجد حالياً:** ربط “دور داخل التيم” (مثلاً فرونت/باك/تستر داخل نفس الـ Group أو المشروع) — فقط تراك عام على مستوى الـ workspace.

---

## ٥) المجموعات (Groups) والـ Guests

- **Group:** له اسم، وصف، لون، روابط (whatsapp, slack, repo, service)، ومرتبط بـ workspace ومنشئ (member).
- **الربط:** `group_user` (group_id, user_id, assigned_at) — أي مستخدم (غالباً guests) يمكن تعيينه لمجموعة.
- **لا يوجد:** ربط مباشر بين Project و Group في قاعدة البيانات (لا `group_id` على المشروع).
- **لا يوجد:** تمييز داخل الـ Group مين فرونت ومين باك ومين تستر — الـ Group مجرد مجموعة مستخدمين بدون أدوار داخلية.

---

## ٦) المشاريع (Projects)

- **الحقول:** workspace_id, space_id, name, description, color, icon, default_assignee_id, progress, start_date, due_date, is_archived, إلخ.
- **لا يوجد:** group_id، عدد أيام العمل، استبعاد جمعة/سبت، أو قواعد تلقائية لعدد التاسكات/الساعات.
- **العلاقات:** workspace، space، tasks، customStatuses، customFields، lists، sprints (عبر الـ tasks).

---

## ٧) التاسكات (Tasks)

- **الحقول:** workspace_id, project_id, sprint_id, list_id, status_id, creator_id, parent_id, related_task_id, title, type (task|bug), description, priority, due_date, start_date, estimated_time / estimated_minutes, position, إلخ.
- **العلاقات:** assignees (many-to-many)، tags (morphToMany)، attachments، comments، subtasks، dependencies، status، project.
- **الـ Bugs:** نوع `type = bug` ويمكن ربطها بـ `related_task_id` (main task). لا يوجد تحقق أن إجمالي estimation الـ bugs = ٢٠٪ من الـ main task.
- **ملاحظة:** التاسك مرتبط بـ project وليس بـ user؛ حذف الـ guest لا يحذف التاسك — لكن يجب التأكد أن منطق “إزالة الـ guest من الـ workspace” لا يحذف التاسكات ولا يمسها، فقط يُفك ربطه من الـ assignees.

---

## ٨) السبرنتات (Sprints)

- مرتبطة بـ workspace و project؛ لها start_date, end_date, status. لا يوجد حساب لأيام العمل بدون جمعة/سبت.

---

## ٩) التاجات (Tags)

- workspace-scoped؛ اسم ولون. ربط بالتاسكات عبر جدول taggables (polymorphic). إنشاء تاج جديد: `TagController@store` (مثلاً للـ members/admins).

---

## ١٠) المرفقات والتعليقات

- **Attachments:** polymorphic (تسك، تعليق، مشروع…)؛ تخزين ملف مع path, size, mime_type. رفع الملفات عند إنشاء التاسك مضاف في الـ create task.
- **Comments:** polymorphic مع إمكانية mentions.

---

## ١١) تتبع الوقت (Time Tracking)

- **TimeEntry:** task_id, user_id, start_time, end_time, duration. تايمر نشط = end_time null.
- **TimeTrackingController:** بدء/إيقاف تايمر، إدخال يدوي.

---

## ١٢) الحضور (Attendance)

- **Attendance:** workspace_id, guest_id, date, checked_in_at, checked_out_at, status, notes.
- **AttendanceController:** check-in, check-out, toggle حضور، mark absent، unsuspend.
- **لا يوجد:** ربط صريح بين “إكمال شغل اليوم” (تاسكات done) والحضور؛ ولا زر “المنتور يضغط check حضور” من لوحة المنتور (المنطق قد يكون جزئياً في الـ views).

---

## ١٣) التقارير (Guest Reports)

- **GuestReport:** workspace_id, guest_id, member_id, week_start_date, week_end_date, weaknesses, strong_points, feedback.
- **الاستخدام:** تقرير من المنتور/العضو عن الضيف لفترة أسبوع. لا يوجد “فيدباك من الطالب يولد تقريراً للمنتور” ولا أسئلة تقيس المنتور والتدريب.

---

## ١٤) الإشعارات والنشاط

- **ActivityLog:** تسجيل أحداث (created, updated, status_changed, …) مع subject و old/new values. لا يوجد نظام إشعارات للمستخدم (لا جدول notifications ولا إشعارات في الواجهة للتستر/العضو/الضيف عند إضافة شخص على تاسك أو طلب تستر على مشروع).

---

## ١٥) التقدير (Estimation)

- **TaskEstimation:** للـ tasks؛ تقدير من الـ guests (polling). **TaskEstimationController** يدير التقدير والنتيجة النهائية.

---

## ١٦) الـ Dashboard

- **DashboardController:** يعرض محتوى مختلف حسب الدور (admin, member, guest)؛ مشاريع، تاسكات، تقدم، تايمر، إلخ. لا يوجد “progress bar يومي للطالب” مبني على إكمال تاسكات الـ day، ولا overview موحد “ناس بدون تسكات كافية” للـ owner.

---

## ١٧) الروابط (Routes)

- ورشة عمل: workspaces (CRUD، أعضاء، تracks، …).
- داخل workspace: projects، tasks (kanban, list, create, update status)، bugs، groups، sprints، time-tracking، estimations، tags (store)، daily-statuses، topics، reports، attendance.
- لا توجد routes مخصصة لـ “تعيين تستر على مشروع” أو “إشعارات” أو “فيدباك الطالب”.

---

## ١٨) الخلاصة السريعة للكود الحالي

| الموجود | غير الموجود أو غير مكتمل |
|---------|---------------------------|
| Workspace، أدوار، tracks على مستوى workspace | ربط Project ↔ Group؛ أيام عمل بدون جمعة/سبت |
| Groups + تعيين guests للمجموعة | أدوار داخل التيم (فرونت/باك/تستر/UI/UX…) |
| Projects، Tasks، Bugs، Tags، Attachments | قواعد عدد تاسكات/ساعات (٦ ساعات/يوم، ٣٠/أسبوع، ٢٠٪ للـ bugs) |
| Time tracking، Estimation | Progress يومي للطالب؛ overview “بدون تسكات” |
| Attendance (check-in/out) | ربط الحضور بإكمال الشغل؛ check حضور من المنتور |
| GuestReport (تقرير المنتور عن الضيف) | فيدباك من الطالب → تقرير للمنتور + قياس المنتور والتدريب |
| Activity log | نظام إشعارات (للتستر، العضو، الضيف) |
| حذف guest (المنطق الحالي لا يحذف التاسكات) | التأكيد الرسمي أن التاسكات تبقى قابلة لإعادة التعيين |

هذا التقرير يلخص حالة الكود الحالي فقط؛ التقرير الثاني يلخص كل ما طلبته أنت وينظمه لوضع خطة التنفيذ.
