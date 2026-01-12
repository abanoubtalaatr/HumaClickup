# HumaClickup - ClickUp-Like Project Management System

A comprehensive, production-ready project management system built with Laravel 12, featuring multi-tenant workspaces, task management, time tracking, and collaboration features.

## 🎯 Project Status

### ✅ Completed (Foundation)

1. **Database Architecture**
   - Complete migrations for all entities (workspaces, users, projects, tasks, comments, attachments, time entries, etc.)
   - Proper foreign keys, indexes, and soft deletes
   - Full-text search indexes
   - Polymorphic relationships for flexibility

2. **Eloquent Models**
   - All models with comprehensive relationships
   - Global scopes for workspace isolation
   - Helper methods for business logic
   - Accessors/mutators where needed

3. **Authorization System**
   - Policy classes (TaskPolicy, ProjectPolicy, WorkspacePolicy)
   - Granular permission checks
   - Workspace-level, project-level, and task-level permissions
   - Middleware for workspace access control

4. **Business Logic Layer**
   - TaskService: Task creation, updates, status changes, dependencies
   - TimeTrackingService: Timer management, manual entries, reports
   - ActivityLogService: Comprehensive audit trail

5. **System Design Documentation**
   - Complete architecture documentation (SYSTEM_DESIGN.md)
   - Implementation roadmap
   - Technical decisions and justifications

### ✅ UI/Blade Templates Created

1. **Layouts & Components**
   - Main app layout (`layouts/app.blade.php`)
   - Navigation bar with workspace switcher
   - Toast notifications for success/error messages

2. **Dashboard**
   - Dashboard index with stats cards
   - Active projects list
   - Recent activity feed
   - Upcoming deadlines

3. **Tasks**
   - Kanban board view with drag-drop (Sortable.js)
   - List view with filtering
   - Task card component
   - Task modal with full details
   - Filters panel

4. **Projects**
   - Projects index (grid view)
   - Project show page with stats
   - Quick links to Kanban/List views

5. **Frontend Setup**
   - Alpine.js integrated
   - Sortable.js for drag-drop
   - Tailwind CSS styling
   - Responsive design

### 🚧 Remaining Work

1. **Controllers & Routes**
   - TaskController, ProjectController, WorkspaceController
   - TimeTrackingController, CommentController
   - Form Requests for validation
   - API routes (if needed)
   - Connect views to controllers

2. **Additional UI Components**
   - Task create/edit forms
   - Project create/edit forms
   - Time tracking interface
   - File upload components
   - Search implementation

3. **Frontend Enhancements**
   - AJAX for real-time updates
   - Form submission handling
   - Modal interactions
   - Toast notifications (backend integration)

4. **Additional Features**
   - Notifications system (in-app + email)
   - File upload handling
   - Search implementation
   - Reports and analytics
   - Email templates

5. **Testing & Deployment**
   - Unit tests
   - Feature tests
   - Integration tests
   - Deployment configuration

## 📋 Installation

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+ or PostgreSQL 14+
- Node.js & NPM (for frontend assets)

### Setup Steps

1. **Clone and Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   Edit `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=humaclickup
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Publish Spatie Permissions**
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

6. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

7. **Build Frontend Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start Development Server**
   ```bash
   php artisan serve
   ```

## 🏗️ Architecture Overview

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/        # Request handlers
│   ├── Middleware/         # CheckWorkspaceAccess, etc.
│   └── Requests/           # Form validation
├── Models/                 # Eloquent models
├── Policies/              # Authorization policies
└── Services/              # Business logic
    ├── TaskService.php
    ├── TimeTrackingService.php
    └── ActivityLogService.php

database/
├── migrations/             # Database schema
└── seeders/                # Development data

resources/
├── views/                  # Blade templates
└── js/                     # Alpine.js components

routes/
├── web.php                 # Web routes
└── api.php                 # API routes (if needed)
```

### Key Design Patterns

1. **Service Layer Pattern**: Business logic separated from controllers
2. **Policy-Based Authorization**: Granular permission checks
3. **Repository Pattern**: (Optional, can be added later)
4. **Global Scopes**: Automatic workspace isolation
5. **Soft Deletes**: Data recovery capability

## 🔐 Security Features

- **Multi-tenant Isolation**: Global scopes ensure data separation
- **Policy-Based Authorization**: Granular permission checks
- **Workspace Access Middleware**: Validates user access
- **HTML Sanitization**: HTMLPurifier for rich text
- **Mass Assignment Protection**: Only fillable fields allowed
- **CSRF Protection**: Laravel's built-in protection
- **SQL Injection Prevention**: Eloquent ORM

## 📊 Database Schema

### Core Entities

- **Workspaces**: Top-level containers
- **Spaces**: Grouping within workspaces
- **Projects**: Main work containers
- **Lists**: Optional sub-containers
- **Tasks**: Core work items
- **CustomStatuses**: Project-specific statuses
- **CustomFields**: Project-specific fields
- **Tags**: Workspace-scoped labels
- **Comments**: Threaded discussions
- **Attachments**: File uploads
- **TimeEntries**: Time tracking records
- **ActivityLogs**: Audit trail

See `SYSTEM_DESIGN.md` for detailed schema documentation.

## 🚀 Next Steps

### Immediate Priorities

1. **Create Controllers**
   ```bash
   php artisan make:controller TaskController --resource
   php artisan make:controller ProjectController --resource
   php artisan make:controller WorkspaceController --resource
   php artisan make:controller TimeTrackingController
   ```

2. **Create Form Requests**
   ```bash
   php artisan make:request StoreTaskRequest
   php artisan make:request UpdateTaskRequest
   php artisan make:request StoreProjectRequest
   ```

3. **Set Up Routes**
   - Register middleware in `bootstrap/app.php`
   - Define routes in `routes/web.php`
   - Group workspace routes with middleware

4. **Create Basic Views**
   - Layout template
   - Dashboard view
   - Task list/Kanban view
   - Task modal/form

5. **Implement Frontend**
   - Install Alpine.js
   - Add drag-drop library (Sortable.js)
   - Create interactive components
   - Add AJAX for real-time updates

### Example Controller Structure

```php
class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request, Project $project)
    {
        $this->authorize('viewAny', [Task::class, $project->workspace_id]);
        
        $tasks = $project->tasks()
            ->with(['assignees', 'status', 'tags'])
            ->get();
            
        return view('tasks.index', compact('tasks', 'project'));
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('create', [Task::class, $project->workspace_id, $project->id]);
        
        $task = $this->taskService->create(
            $request->validated(),
            auth()->user(),
            $project
        );
        
        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }
    
    // ... other methods
}
```

### Example Route Structure

```php
Route::middleware(['auth', 'workspace.access'])->group(function () {
    Route::prefix('workspaces/{workspace}')->group(function () {
        Route::resource('projects', ProjectController::class);
        
        Route::prefix('projects/{project}')->group(function () {
            Route::resource('tasks', TaskController::class);
            Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus']);
            Route::post('tasks/reorder', [TaskController::class, 'reorder']);
        });
        
        Route::prefix('time-tracking')->group(function () {
            Route::post('start', [TimeTrackingController::class, 'start']);
            Route::post('stop', [TimeTrackingController::class, 'stop']);
            Route::post('manual', [TimeTrackingController::class, 'createManual']);
        });
    });
});
```

## 🧪 Testing

### Running Tests

```bash
php artisan test
```

### Test Structure

- **Unit Tests**: Models, services, helpers
- **Feature Tests**: Controllers, authorization, workflows
- **Integration Tests**: Multi-tenant isolation, permissions

## 📚 Documentation

- **SYSTEM_DESIGN.md**: Complete system architecture and design decisions
- **README.md**: This file - setup and overview
- **Code Comments**: Inline documentation in all classes

## 🔧 Configuration

### Key Configuration Files

- `.env`: Environment variables
- `config/permission.php`: Spatie permissions config
- `config/filesystems.php`: Storage configuration
- `config/queue.php`: Queue configuration

### Important Environment Variables

```env
APP_NAME="HumaClickup"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=humaclickup

QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
# For S3: FILESYSTEM_DISK=s3
```

## 🤝 Contributing

This is a production application. Follow these guidelines:

1. Follow PSR-12 coding standards
2. Write tests for new features
3. Update documentation
4. Use meaningful commit messages
5. Follow the existing architecture patterns

## 📝 License

[Your License Here]

## 🙏 Acknowledgments

Built following best practices for Laravel applications and inspired by ClickUp, Jira, Monday.com, and Asana.

---

**Status**: Foundation Complete ✅ | UI & Controllers: In Progress 🚧

For detailed architecture information, see `SYSTEM_DESIGN.md`.
