# Testing Guide - HumaClickup

## Quick Start Testing

### 1. Setup Database

Make sure your `.env` file has the correct database credentials, then run:

```bash
php artisan migrate
```

### 2. Create a Test User

You can use Laravel Tinker to create a test user:

```bash
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);
```

Or use the default user if you have seeders.

### 3. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### 4. Login

1. Go to `http://localhost:8000/login`
2. Use the credentials you created (e.g., `test@example.com` / `password`)

### 5. Create a Workspace

After login, you'll be redirected to create a workspace (if you don't have one).

1. Click "Create Workspace" or go to `/workspaces/create`
2. Enter a workspace name
3. Click "Create"

### 6. Create a Project

1. Go to `/projects` or click "Projects" in navigation
2. Click "New Project"
3. Fill in the project details
4. Click "Create"

The system will automatically create default statuses:
- To Do
- In Progress
- Done

### 7. Create Tasks

1. Go to your project
2. Click "New Task" or go to Kanban view
3. Fill in task details
4. Assign to users
5. Set priority, due date, etc.

### 8. Test Kanban Board

1. Go to `/projects/{project}/tasks/kanban`
2. You should see columns for each status
3. Try dragging tasks between columns (drag-drop should work)
4. Tasks will update their status automatically

### 9. Test List View

1. Go to `/projects/{project}/tasks/list` or click "List" view
2. You should see all tasks in a table format
3. Use filters to filter by status, assignee, priority

### 10. Test Time Tracking

1. Open a task
2. Click "Start Timer" in the time tracking section
3. You should see the timer running in the navigation bar
4. Click "Stop" to stop the timer
5. Time will be logged to the task

## Available Routes

### Public Routes
- `GET /` - Redirects to login
- `GET /login` - Login page
- `POST /login` - Login form submission

### Authenticated Routes
- `GET /dashboard` - Main dashboard
- `GET /projects` - List all projects
- `GET /tasks` - List all tasks
- `GET /tasks/kanban` - Kanban board view

### Workspace Routes
- `GET /workspaces` - List workspaces
- `GET /workspaces/create` - Create workspace form
- `POST /workspaces` - Store workspace
- `GET /workspaces/{workspace}` - Show workspace
- `POST /workspaces/{workspace}/switch` - Switch active workspace

### Project Routes (within workspace)
- `GET /workspaces/{workspace}/projects` - List projects
- `GET /workspaces/{workspace}/projects/create` - Create project
- `POST /workspaces/{workspace}/projects` - Store project
- `GET /workspaces/{workspace}/projects/{project}` - Show project
- `GET /workspaces/{workspace}/projects/{project}/tasks/kanban` - Kanban view
- `GET /workspaces/{workspace}/projects/{project}/tasks/list` - List view

## Common Issues

### Issue: "No workspace selected"
**Solution**: Create a workspace first, or the system will redirect you to create one.

### Issue: "403 Forbidden" errors
**Solution**: Make sure you're a member of the workspace. Check the `workspace_user` table.

### Issue: Drag-drop not working
**Solution**: 
1. Make sure Alpine.js and Sortable.js are loaded
2. Check browser console for JavaScript errors
3. Run `npm run build` or `npm run dev`

### Issue: Routes not found
**Solution**: 
1. Clear route cache: `php artisan route:clear`
2. Clear config cache: `php artisan config:clear`
3. Check that routes are registered in `routes/web.php`

### Issue: Database errors
**Solution**: 
1. Make sure migrations ran: `php artisan migrate`
2. Check database connection in `.env`
3. Check that all tables exist

## Testing Checklist

- [ ] Can login
- [ ] Can create workspace
- [ ] Can switch between workspaces
- [ ] Can create project
- [ ] Can view project details
- [ ] Can create task
- [ ] Can view tasks in Kanban
- [ ] Can drag-drop tasks in Kanban
- [ ] Can view tasks in List view
- [ ] Can filter tasks
- [ ] Can start/stop timer
- [ ] Can view dashboard with stats
- [ ] Can see recent activity
- [ ] Can assign users to tasks
- [ ] Can change task status
- [ ] Can set task priority
- [ ] Can add due dates

## Next Steps for Full Functionality

1. **Add Seeders**: Create database seeders for test data
2. **Add Tests**: Write feature tests for all functionality
3. **Add Forms**: Create forms for task/project creation/editing
4. **Add AJAX**: Implement AJAX for real-time updates
5. **Add Notifications**: Implement notification system
6. **Add File Uploads**: Implement file attachment functionality

## Development Tips

- Use `php artisan tinker` to interact with models
- Check logs in `storage/logs/laravel.log`
- Use browser DevTools to debug JavaScript
- Check Laravel Debugbar if installed
- Use `php artisan route:list` to see all routes

