# Activity logs

## Responsibility

Logs and provides to get entity activities create/update/delete etc.

## Settings

### Settings in config/FKS-activity-log.php

```PHP
return [
    'enable' => true,
    'activity-log-model-class' => ActivityLog::class,
    'entity-types' => [
        'entity_name' => 1,
    ],
    'activity-types' => [
        'activity_name' => 1,
    ],
    'activity-type-formatters' => [
        'activity_name' => UpdateFormatter::class,
    ],
];
```
#### Config description
enable


After enable 