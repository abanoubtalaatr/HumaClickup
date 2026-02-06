# Quick Start - Student Training System

**For:** Project Managers & Members  
**Status:** ✅ Ready to Use

---

## 🚀 Create Your First Project (3 Steps)

### Visit: `/projects/create`

---

## Step 1️⃣: Project Info

**Fill out:**
- Project name: "E-commerce Website"
- Start date: Today
- Total days: 20 (system calculates ~14 working days)
- ✅ Exclude weekends (Friday & Saturday)

**Select Team:**

**Option A:** Select Individual Guests
```
Search: Type "ahmed"
✓ Ahmed Ali (Frontend)
✓ Sara Mohamed (Backend)
✓ Mohamed Hassan (UI/UX)
```

**Option B:** Select a Group
```
Search: "Team Alpha"
Click: Team Alpha (5 members)
→ All 5 auto-selected
```

**Preview:**
```
3 guests × 14 working days = 42 required tasks
Each task must be >= 6 hours
```

➡️ **Click "Next"**

---

## Step 2️⃣: Plan Tasks

**Auto-Generated Grid:**
```
42 task cards (one per guest per day)
```

**For Each Task:**

```
┌────────────────────────────────────────┐
│ Day 1 • Ahmed Ali                      │
│                                        │
│ Task Title: Build user authentication │
│ Hours: 6 (min)                        │
│                                        │
│ Subtasks:                + Add Subtask │
│  ├─ Setup auth       2h               │
│  ├─ Login page       2h               │
│  └─ Validation       2h               │
│                                        │
│ Total: 6h ✓ Matches                   │
└────────────────────────────────────────┘
```

**Actions:**
- Edit title
- Increase hours (keep >= 6)
- Add subtasks (optional but recommended)
- Remove subtasks (X button)

**Validation:**
- ❌ Cannot proceed if any task < 6 hours
- ❌ Cannot proceed if any task has no title
- ✅ Subtask mismatch = warning (not blocking)

➡️ **Click "Next"**

---

## Step 3️⃣: Review & Create

**Summary Shows:**
```
✓ Project: E-commerce Website
✓ Duration: 14 working days
✓ Team: 3 guests
✓ Tasks: 42 main tasks
✓ Subtasks: 126 subtasks
✓ Total Hours: 252h
```

**Per-Guest Breakdown:**
```
Ahmed Ali:    14 tasks, 84h
Sara Mohamed: 14 tasks, 84h
Mohamed:      14 tasks, 84h
```

➡️ **Click "Create Project"**

---

## ✅ Done!

**What Happens:**
1. ✅ Project created
2. ✅ 42 main tasks created
3. ✅ 126 subtasks created
4. ✅ All tasks assigned to guests
5. ✅ 2 testers requested (notification sent)
6. ✅ Dates calculated (weekends skipped)
7. ✅ Due dates set to 11:00 PM
8. ✅ Bug time limits calculated (20%)

**Redirects to:** Project page with all tasks visible

---

## 📊 For Guests (Students)

### View Progress: `/guests/progress`

**You'll See:**
```
┌─────────────────────────────────────┐
│ This Week (Feb 1 - Feb 7)          │
│ ████████████░░░░ 68%                │
│ 20.5 / 30 hours                     │
└─────────────────────────────────────┘

Project: E-commerce Website
┌─────────────────────────────────────┐
│ Today (Feb 3)                       │
│ ████████████████ 100%               │
│ 6.0 / 6.0 hours                     │
│                                     │
│ Task: Build user authentication     │
│ Status: Closed ✓                    │
│                                     │
│ Attendance: Present ✓               │
│ Approved by: Mentor Ahmed           │
└─────────────────────────────────────┘
```

**How to Get 100%:**
1. Complete your main task
2. Move to "Closed" status
3. **Before 11:00 PM** ⏰
4. Progress auto-updates to 100%
5. Attendance auto-marked "Present"

**If Completed After 11 PM:**
- Progress: 0% ❌
- Attendance: Absent ❌
- No exceptions!

---

## 🎓 For Mentors

### Dashboard: `/mentor/dashboard`

**Alerts:**
```
⚠️ 3 guests without tasks today
⚠️ 5 guests with incomplete progress
⏳ 12 pending approvals
```

**Approval Queue:**
```
┌────────────────────────────────────────────────┐
│ Guest       │ Date    │ Progress │ Status      │
├────────────────────────────────────────────────│
│ Ahmed Ali   │ Feb 3   │ 100%     │ [Approve]   │
│ Sara        │ Feb 3   │ 100%     │ [Approve]   │
│ Mohamed     │ Feb 3   │ 83%      │ [Approve]   │
└────────────────────────────────────────────────┘
                           [Approve All Selected]
```

**Actions:**
- Click "Approve" for individual
- Select multiple → "Approve All"
- Once approved → data locked (immutable)

---

## 👔 For Owners

### Overview: `/owner/overview`

**Global Stats:**
```
┌──────────────┬──────────────┬──────────────┐
│ 15 Projects  │ 75 Guests    │ 12 Alerts    │
│ Active       │ Total        │ Require      │
│              │              │ Action       │
└──────────────┴──────────────┴──────────────┘
```

**Per-Project Table:**
```
┌─────────────┬──────────┬────────────┬────────┐
│ Project     │ Progress │ Attendance │ Issues │
├─────────────┼──────────┼────────────┼────────│
│ E-commerce  │ 85%      │ 90% ✓      │ None   │
│ Mobile App  │ 62%      │ 75% ⚠️     │ 2      │
│ Dashboard   │ 45%      │ 60% ⚠️     │ 5      │
└─────────────┴──────────┴────────────┴────────┘
```

**Quick Actions:**
- View alerts (guests without tasks)
- Approve pending progress (bulk)
- Drill down to project details

---

## 🧪 Testing Tips

### Create Test Project:
```bash
1. Login as Member
2. Go to /projects/create
3. Name: "Test Project"
4. Select 2 guests
5. Start: Today
6. Days: 5 (= ~3 working days)
7. Result: 6 tasks created (2 guests × 3 days)
```

### Test Progress:
```bash
1. Login as Guest
2. Find task for today
3. Move to "Closed" (before 11 PM)
4. Go to /guests/progress
5. Verify: 100% progress, Present
```

### Test Approval:
```bash
1. Login as Mentor
2. Go to /mentor/dashboard
3. See pending progress
4. Click "Approve"
5. Verify: Data locked
```

---

## ⚡ Key Rules (Enforced!)

### Project Creation:
- ✅ Required tasks = guests × working_days
- ✅ Each main task >= 6 hours
- ✅ Weekends (Fri/Sat) excluded
- ✅ All tasks assigned during creation

### Daily Work:
- ✅ 1 main task per guest per day
- ✅ Complete before 11:00 PM
- ✅ Progress = HOURS-based (not percentage)
- ✅ Attendance derived from progress

### Weekly Target:
- ✅ 30 hours per week (5 days × 6 hours)
- ✅ Visible in progress dashboard
- ✅ Alerts if below target

### Bug Tracking:
- ✅ Testers create bugs on main tasks
- ✅ Bug budget = 20% of main task
- ✅ Auto-distributes if multiple bugs
- ✅ Example: 18h main → 3.6h bugs total

---

## 🔍 Where To Find Things

| What | URL |
|------|-----|
| **Create Project** | `/projects/create` |
| **Guest Progress** | `/guests/progress` |
| **Mentor Dashboard** | `/mentor/dashboard` |
| **Owner Overview** | `/owner/overview` |
| **Project Details** | `/projects/{id}` |

---

## 📚 Full Documentation

For complete details, see:
- **`COMPLETE_IMPLEMENTATION_SUMMARY.md`** - Everything in one place
- **`WIZARD_IMPLEMENTATION.md`** - Wizard-specific guide
- **`DELIVERY_REPORT.md`** - Technical deep dive

---

## ✅ Quick Checklist

**Before Creating Your First Real Project:**

- [ ] Run migrations: `php artisan migrate`
- [ ] Create tracks (Frontend, Backend, Testing, etc.)
- [ ] Create guests as member
- [ ] Create groups (optional)
- [ ] Test wizard with 1-2 guests
- [ ] Complete a task before 11 PM
- [ ] Check progress shows 100%
- [ ] Approve as mentor
- [ ] Verify data locked

**Then:**
- [ ] Create real projects with full teams
- [ ] Monitor daily progress
- [ ] Approve daily
- [ ] Check weekly targets
- [ ] Review owner dashboard weekly

---

## 🎉 You're Ready!

**The system enforces discipline automatically:**
- Cannot skip weekends
- Cannot have tasks < 6 hours
- Cannot complete after 11 PM and get credit
- Cannot fake attendance
- Progress is backend-calculated
- Approval required to lock data

**Students will learn:**
- Daily consistency (6h/day)
- Time management (finish before 11 PM)
- Weekly targets (30h/week)
- Professional workflow (tasks → subtasks → done)

**Start creating projects!** 🚀
