# Route Reference Guide

## Route Naming Convention

To avoid conflicts, we use different route name prefixes:

### Standalone Routes (uses workspace from session)
- `projects.*` - Project routes
- `tasks.*` - Task routes  
- `time-tracking.*` - Time tracking routes

### Workspace-Scoped Routes (explicit workspace in URL)
- `workspace.projects.*` - Project routes within workspace
- `workspace.time-tracking.*` - Time tracking routes within workspace

### Project-Scoped Routes (explicit project in URL)
- `project.tasks.*` - Task routes within project

## Common Routes

### Projects
```php
// Standalone (recommended for most use cases)
route('projects.index')
route('projects.show', $project)
route('projects.create')
route('projects.store', [...])
route('projects.edit', $project)
route('projects.update', $project, [...])
route('projects.destroy', $project)

// Workspace-scoped (if you need explicit workspace)
route('workspace.projects.show', [$workspace, $project])
```

### Tasks
```php
// Standalone (all tasks in workspace)
route('tasks.index')
route('tasks.show', $task)
route('tasks.create')
route('tasks.kanban')

// Project-scoped (tasks within a project)
route('project.tasks.index', $project)
route('project.tasks.show', [$project, $task])
route('project.tasks.create', $project)
route('project.tasks.kanban', $project)
```

### Time Tracking
```php
// Standalone (recommended)
route('time-tracking.index')
route('time-tracking.start', [...])
route('time-tracking.stop')
route('time-tracking.manual', [...])

// Workspace-scoped
route('workspace.time-tracking.index', $workspace)
```

## Route Model Binding

All routes automatically scope models to the current workspace:
- Projects are scoped to `session('current_workspace_id')`
- Tasks are scoped to `session('current_workspace_id')`

This ensures data isolation even when using standalone routes.

## Best Practices

1. **Use standalone routes** (`projects.*`, `tasks.*`) in views and controllers when possible
2. **Use workspace-scoped routes** only when you need to explicitly pass the workspace
3. **Use project-scoped routes** (`project.tasks.*`) when you want to show tasks within a specific project context
4. **Always pass model instances** to routes (not just IDs) to leverage route model binding

## Examples

```blade
{{-- ✅ Good - Standalone route --}}
<a href="{{ route('projects.show', $project) }}">View Project</a>

{{-- ✅ Good - Project-scoped route --}}
<a href="{{ route('project.tasks.kanban', $project) }}">Kanban Board</a>

{{-- ❌ Bad - Missing parameter --}}
<a href="{{ route('projects.show') }}">View Project</a>

{{-- ❌ Bad - Wrong route name --}}
<a href="{{ route('tasks.kanban', $project) }}">Kanban</a>
{{-- Should be: route('project.tasks.kanban', $project) --}}
```

