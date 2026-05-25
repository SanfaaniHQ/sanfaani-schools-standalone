# Guided Onboarding

The guided onboarding foundation provides role-based checklists, progress tracking, dashboard widgets, and event logs.

## Current Roles

- Super Admin.
- School Admin.
- Teacher.
- Parent.
- Student.
- Result Officer.
- Accountant.

## Current Models

- `OnboardingChecklist`
- `OnboardingStep`
- `UserOnboardingProgress`
- `OnboardingEventLog`

## Current Services

- `OnboardingChecklistService`
- `OnboardingProgressService`
- `OnboardingVisibilityService`
- `OnboardingEventService`

## Visibility Rules

Checklist steps are filtered by:

- deployment mode;
- license mode;
- feature access;
- user role;
- school context;
- demo/trial configuration.

## Events

- `OnboardingStepCompleted`
- `OnboardingChecklistCompleted`

These can feed marketing conversion activity.

## Not Implemented Yet

There is no full checklist builder UI or sales automation listener beyond the current foundation.
