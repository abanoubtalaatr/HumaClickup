# ClickUp-Like Project Management System - System Design & Architecture

## PART 1: SYSTEM DESIGN & ARCHITECTURE

### System Architecture Overview

This is a multi-tenant SaaS application built with Laravel 12, following a service-oriented architecture with clear separation of concerns:

- **Presentation Layer**: Blade templates with Alpine.js for interactivity
- **Application Layer**: Controllers, Form Requests, Policies
- **Business Logic Layer**: Service classes (TaskService, TimeTrackingService, etc.)
- **Data Access Layer**: Eloquent Models with Repository pattern (optional)
- **Infrastructure Layer**: Middleware, Queue Jobs, Event Listeners

### Database Schema Design

The database follows a normalized structure with proper foreign keys, indexes, and soft deletes:

**Core Hierarchy:**
- Workspace → Space → Project → List → Task
- Workspace → Tag (workspace-scoped)
- Project → CustomStatus, CustomField
- Task → Comments, Attachments, TimeEntries, Dependencies

**Key Design Decisions:**
1. **Multi-tenancy**: Every entity has `workspace_id` for data isolation
2. **Soft Deletes**: All major entities use soft deletes for recovery
3. **Polymorphic Relations**: Comments, Attachments, Tags use morphToMany/morphMany
4. **Position Fields**: Tasks have `position` for drag-drop ordering
5. **Full-Text Search**: Tasks table has FULLTEXT index on title/description

### Core Models & Eloquent Relationships

All models implement:
- Proper relationships (belongsTo, hasMany, belongsToMany, morphToMany)
- Global scopes for workspace isolation
- Helper methods for business logic
- Accessors/mutators where needed

**Key Relationships:**
- User ↔ Workspace (many-to-many with role pivot)
- Workspace → Space → Project → Task
- Task ↔ User (assignees, watchers)
- Task ↔ Task (dependencies, subtasks)
- Task → Comment → User (with mentions)
- Task → TimeEntry → User

### Authorization Architecture

**Three-Level Permission System:**

1. **Workspace-Level**: Role-based (owner, admin, member, guest)
2. **Project-Level**: Can override workspace permissions
3. **Task-Level**: Private tasks override project permissions

**Implementation:**
- Spatie Laravel Permission for role/permission management
- Policy classes for each model (TaskPolicy, ProjectPolicy, etc.)
- Middleware for workspace access control
- Global scopes to enforce data isolation

**Permission Granularity:**
- view_workspace, manage_workspace_settings
- create_projects, edit_projects, delete_projects
- create_tasks, edit_tasks, delete_tasks, assign_tasks
- track_time, view_time_reports
- manage_comments, upload_files, manage_custom_fields

---

## PART 2: BUSINESS LOGIC

### Task Lifecycle & State Management

**Status Flow:**
- Tasks start in project's default status
- Status can change to any other status (flexible workflow)
- Moving to "done" type status:
  - Auto-stops running timers
  - Marks task complete
  - Notifies watchers
  - Updates project progress
  - Checks if unblocks dependent tasks

**Progress Calculation (3 methods):**
1. **Status-based**: Each status has progress_contribution (0-100%)
2. **Count-based**: done_tasks / total_tasks
3. **Time-based**: logged_time / estimated_time

### Time Tracking System

**Features:**
- Start/Stop timer (one active timer per user globally)
- Manual time logs (date, start, end, description)
- Billable flag for future invoicing
- Auto-stop after X hours (configurable)

**Implementation:**
- `TimeEntry` model with `start_time`, `end_time`, `duration`
- Active timer: `end_time IS NULL`
- Duration calculated on stop
- Timer persists across sessions (stored in DB)

**Edge Cases Handled:**
- User closes browser: Timer continues, warn on return
- Overlapping entries: Warn or prevent
- Task deleted: Keep entries, show task as deleted
- Retroactive logging: Allowed with time limit

### Permissions & Access Control

**Enforcement Points:**
1. **Controllers**: `$this->authorize()` before actions
2. **Policies**: Method per action (view, create, update, delete)
3. **Blade**: `@can` directives to hide/show UI
4. **API**: Middleware for API routes

**Scope Chain:**
```
Workspace Permission
  → Project Override (optional)
    → Task Override (optional - private tasks)
```

### Activity Logging & Audit Trail

**Logged Actions:**
- All CRUD on tasks, projects, comments
- Permission changes
- User invites/removals
- Status changes
- Assignments
- Time tracking start/stop

**Storage:**
- Separate `activity_logs` table
- Immutable (never delete, only add)
- JSON columns for old_values/new_values
- Indexed by workspace, user, subject, action, date

---

## PART 3: UI/UX BEHAVIOR (BLADE)

### Dashboard Components

**Workspace Dashboard:**
- Active Projects list with progress bars
- Overall workspace progress
- Time Spent vs Estimated
- Upcoming Deadlines (next 7 days)
- Recently Updated Tasks
- Team Activity Feed
- My Active Timer (if running)
- Quick Stats (total tasks, completed this week, overdue, active members)

**Project Dashboard:**
- Status Distribution (pie/bar chart)
- Progress Bar (% complete)
- Time Analytics (estimated vs logged)
- Team Workload (tasks per assignee, hours per user)
- Task Lists (overdue, due this week, unassigned, blocked)
- Activity Timeline

**Personal Dashboard (My Work):**
- My Active Timer (prominent)
- My Tasks grouped by status
- Due Today (urgent list)
- Due This Week
- Overdue Tasks (red highlight)
- Watched Tasks
- Recently Assigned to Me
- My Time This Week (total hours, breakdown by project, daily graph)
- Tasks Waiting for Me (dependencies)

### Kanban Board & List Views

**Kanban Board:**
- Horizontal columns (one per status)
- Vertical scroll within columns
- Task cards show: title, assignee avatars, priority icon, due date, time logged/estimated, comment count, attachment count, sub-task count
- Drag card to another column = status change
- Drag within column = reorder
- Add card button at top of each column
- Filter by assignee, tags, date range
- Live search

**List View:**
- Table format with sortable columns
- Checkbox for bulk selection
- Bulk actions: change status, assign to, set priority, delete, archive
- Inline edit for quick changes
- Filters Panel (sidebar): status, assignee, priority, tags, date range, custom fields
- Save View button (save filter preset)

### Task Modal & Inline Editing

**Task Modal Design:**
- Full-screen overlay OR large modal (800px wide)
- Two columns: Left (60%) details, Right (40%) metadata

**Left Column:**
- Title (editable inline, large font)
- Description (rich text editor with @mentions)
- Sub-tasks Section (list with checkboxes, progress indicator)
- Comments Section (reverse chronological, rich text, @mentions, file attachments)

**Right Sidebar:**
- Status (dropdown, changes immediately)
- Assignees (multi-select user picker)
- Priority (dropdown with colors)
- Due Date (date + time picker)
- Start Date
- Estimated Time
- Time Tracking (current timer with elapsed time, start/stop, manual logs)
- Tags (add/remove, create new)
- Watchers
- Dependencies (blocked by, blocking, add dependency)
- Custom Fields
- Attachments (grid, upload, preview)
- Activity Log (collapsible timeline)

**Behavior:**
- Auto-save on changes (with "Saving..." indicator)
- Keyboard shortcuts (Esc to close, Ctrl+Enter to add comment)
- Validation errors shown inline
- Confirmation for destructive actions

### User Interaction Patterns

**Inline Editing:**
- Click element → Transform to input/dropdown
- Click outside OR Enter → Save
- Esc → Cancel
- Loading spinner while saving
- Revert if save fails, show error toast

**Confirmation Rules:**
- Delete task (can restore from archive)
- Delete project (warn about tasks inside)
- Remove user from workspace (warn about their tasks)
- Delete custom status with active tasks (force reassignment first)

**Error Handling:**
- Validation errors: Inline below field (red text, highlight border)
- Server errors: Toast notification (top-right, 5 sec auto-dismiss)
- Permission errors: Toast with graceful redirect

---

## PART 4: ADVANCED FEATURES

### Comments & Mentions System

**Implementation:**
- Rich text editor (Quill/Trix/TipTap)
- Parse @mentions on save (regex: `/@\[(.*?)\]\((.*?)\)/`)
- Store mentions in `comment_mentions` pivot table
- Create notification for each mentioned user
- Render mentions as styled links

**Edit History:**
- Store original content in `edit_history` JSON column
- Show "Edited" indicator with tooltip timestamp
- Admins can view edit history

### File Attachments & Storage

**Storage Strategy:**
- Use Laravel Storage facade
- Generate unique filenames (UUID)
- Store in `/storage/app/uploads/{workspace_id}/{year}/{month}/`
- Validation: max file size (workspace limit), allowed mime types
- Image thumbnails (Jobs for processing)
- Direct download links with signed URLs

**S3 Integration (Future):**
- Environment flag to switch storage
- Pre-signed URLs for private files
- CDN for public files (avatars)

### Notifications System

**Database:**
- Laravel's default `notifications` table
- In-app notifications + email queue

**Notification Types:**
- TaskAssignedNotification
- MentionedInCommentNotification
- TaskDueSoonNotification
- TaskOverdueNotification
- StatusChangedNotification

**Delivery:**
- Database: In-app notifications
- Mail: Email via queues (Mailgun, SES)
- User preferences: JSON on user (email vs in-app, daily digest time)

**UI:**
- Bell icon in header (badge with unread count)
- Dropdown with latest 10 notifications
- "Mark all as read" button
- Polling every 30 seconds OR Livewire events

### Search & Filtering

**Search Scope:**
- Tasks (title, description, comments)
- Projects (name, description)
- Users (name, email)

**Implementation:**
- MVP: Simple LIKE queries with indexes
- Future: Laravel Scout + Meilisearch/Algolia

**Advanced Filters:**
- Saved filter presets (per user)
- Shareable filter URLs
- Export filtered results

### Templates & Automation

**Task Templates:**
- Save task structure (title, description, status, priority, estimated_time, checklist_items, custom_fields)
- "Create from template" button

**Project Templates:**
- Save entire project structure (tasks, statuses, custom fields)
- "Use Template" when creating new project

**Recurring Tasks (Future):**
- JSON column on task: `recurring_rules`
- Cron job creates new tasks based on rules

### Tags & Custom Fields

**Tags:**
- Workspace-scoped
- Many-to-many with tasks/projects (polymorphic)
- Name, color

**Custom Fields:**
- Defined at Project level
- Types: text, textarea, number, date, dropdown, checkbox, user, url, email
- Values stored in `custom_field_values` (polymorphic)
- Filterable and searchable

---

## PART 5: PRODUCTION READINESS

### Security & Data Isolation

**Workspace Data Isolation:**
- Global Scopes on all models: `where('workspace_id', session('current_workspace_id'))`
- Middleware: `CheckWorkspaceAccess` - verify user belongs to workspace
- Route grouping: All workspace routes under `workspaces/{workspace}` prefix
- Never trust `request('workspace_id')` - always use session/route binding

**Policy-Based Authorization:**
- Policy for each model (TaskPolicy, ProjectPolicy, etc.)
- Check workspace membership + permission
- Use in controllers: `$this->authorize('update', $task)`
- Use in Blade: `@can('update', $task)`

**Prevent Cross-Workspace Access:**
- Direct ID manipulation: Policy check + workspace scope
- Mass assignment: Never fillable, set in controller
- Relationship exploitation: Validate assignee belongs to same workspace

**Validation & Sanitization:**
- Form Requests for all inputs
- HTML Purifier for rich text content
- Whitelist safe HTML tags
- Strip dangerous attributes (onclick, etc.)

### Performance & Scalability

**Database Optimization:**
- Indexes on foreign keys, frequently queried columns
- Full-text indexes for search
- Partition activity_logs by date (for large workspaces)
- Archive old logs after X months

**Caching Strategy:**
- Redis for session storage
- Cache workspace settings, user permissions
- Cache project progress calculations
- Cache frequently accessed data (tags, statuses)

**Queue System:**
- Database queues for MVP
- Horizon for monitoring (future)
- Jobs for: email notifications, image processing, activity logging

**Eager Loading:**
- Always eager load relationships to prevent N+1 queries
- Use `with()` in controllers
- Use `load()` for lazy eager loading

### Edge Cases & Solutions

**Task Dependencies:**
- Prevent circular dependencies (check before creating)
- Show warnings in UI if task has blockers
- Auto-unblock when blocking task completes

**Time Tracking:**
- User forgets to stop timer: Auto-stop after X hours (configurable)
- Overlapping entries: Warn or prevent
- Task deleted while tracking: Keep entries, show task as deleted

**Permissions:**
- User loses permission while viewing page: Next action fails with 403, graceful error message
- Guest restrictions: Minimal permission set, can grant specific project access

**Data Recovery:**
- Soft deletes throughout
- Archive instead of delete where possible
- Activity logs for audit trail

### Testing Strategy

**Unit Tests:**
- Model relationships
- Service methods
- Helper functions

**Feature Tests:**
- Controller actions
- Authorization checks
- Business logic flows

**Integration Tests:**
- Multi-tenant isolation
- Permission enforcement
- Time tracking workflows

### Deployment Considerations

**Environment Setup:**
- `.env` configuration for database, cache, queue, storage
- Queue workers must be running
- Storage link: `php artisan storage:link`
- Migrations: `php artisan migrate`

**Monitoring:**
- Log errors to file/Sentry
- Monitor queue workers
- Monitor database performance
- Monitor storage usage

**Backup Strategy:**
- Daily database backups
- Backup file storage (S3)
- Test restore procedures

---

## PART 6: IMPLEMENTATION ROADMAP

### MVP vs Advanced Feature Matrix

**MVP (Phase 1):**
- ✅ Workspace management
- ✅ Projects, Spaces, Lists
- ✅ Tasks with basic fields
- ✅ Custom statuses
- ✅ Task assignees
- ✅ Comments (basic)
- ✅ Time tracking (start/stop, manual logs)
- ✅ Basic dashboard
- ✅ Kanban board
- ✅ List view
- ✅ Search (simple LIKE queries)
- ✅ Activity logging

**Advanced Features (Phase 2):**
- Task dependencies
- Custom fields
- Tags
- File attachments
- @mentions in comments
- Notifications (in-app + email)
- Advanced filtering
- Reports
- Templates
- Recurring tasks

**Future Enhancements:**
- Real-time updates (WebSockets/Pusher)
- Advanced automation
- Time approval workflow
- Integrations (Slack, GitHub, etc.)
- Mobile app
- API for third-party integrations

### Implementation Priority Order

1. **Core Infrastructure** (Week 1-2)
   - Database migrations ✅
   - Models with relationships ✅
   - Authorization system
   - Middleware for workspace isolation
   - Basic routing

2. **Task Management** (Week 3-4)
   - Task CRUD
   - Kanban board
   - List view
   - Task modal
   - Status management

3. **Time Tracking** (Week 5)
   - Timer start/stop
   - Manual time logs
   - Time reports

4. **Collaboration** (Week 6)
   - Comments
   - Assignees/Watchers
   - Basic notifications

5. **Advanced Features** (Week 7-8)
   - Custom fields
   - Tags
   - File attachments
   - Search & filtering

6. **Polish & Testing** (Week 9-10)
   - UI/UX improvements
   - Performance optimization
   - Testing
   - Documentation

---

## Technical Decisions & Justifications

### Why Blade + Alpine.js instead of React/Vue?
- **Simplicity**: No build step required, faster development
- **Server-side rendering**: Better SEO, faster initial load
- **Laravel integration**: Direct access to Laravel helpers, policies
- **Alpine.js**: Lightweight, perfect for interactive components

### Why Database Queues instead of Redis/RabbitMQ?
- **MVP simplicity**: No additional infrastructure required
- **Easy migration**: Can switch to Redis later without code changes
- **Sufficient for MVP**: Handles email notifications, background jobs

### Why Service Layer Pattern?
- **Separation of concerns**: Business logic separate from controllers
- **Testability**: Easy to unit test service methods
- **Reusability**: Services can be used by controllers, jobs, commands
- **Maintainability**: Clear structure, easy to find and modify logic

### Why Global Scopes for Workspace Isolation?
- **Automatic enforcement**: Can't accidentally query across workspaces
- **DRY principle**: Don't repeat `where('workspace_id', ...)` everywhere
- **Security**: Harder to bypass, enforced at model level

---

## Next Steps

1. Create Policies for all models
2. Create Service classes (TaskService, TimeTrackingService, etc.)
3. Create Middleware (CheckWorkspaceAccess, etc.)
4. Create Controllers with proper authorization
5. Create Form Requests for validation
6. Create Blade layouts and components
7. Implement JavaScript for interactivity (Alpine.js)
8. Create Seeders for development data
9. Write tests
10. Deploy and monitor

---

*This document is a living document and should be updated as the system evolves.*

