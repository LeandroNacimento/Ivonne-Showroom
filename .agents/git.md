# Git Policy

## Purpose

Use this file to make branch, Gitflow, staging, and commit decisions safely.

## Use When

Use this file when changing:

- branch strategy
- branch naming
- Gitflow continuation
- staging expectations
- commit suggestions
- Git safety boundaries

## Do Not Use For

Do not use this file for:

- domain-rule decisions
- architecture decisions
- deploy mechanics
- security design decisions

## Hard Rules

Apply these rules:

- Treat `main` as production.
- Treat `develop` as integration.
- Use `feature/*` for new functionality.
- Use `fix/*` for corrections.
- Use `hotfix/*` for critical errors.
- Prefer a new branch for new features, bug fixes, refactors, and relevant technical improvements.
- Do not work directly on `main` by initiative.
- Do not commit by initiative.
- Do not push by initiative.
- Do not execute Gitflow actions by initiative.
- Do not perform irreversible Git actions without explicit user instruction.
- Use English Conventional Commits when suggesting commit messages.

## Defaults

Default to:

- warning when a task should live in a new branch
- suggesting branch names instead of creating them autonomously
- suggesting commit messages instead of committing autonomously
- stopping before irreversible Git actions

## Escalate When

Escalate when the task may involve:

- branch creation or branch switching
- staging expectations that affect multiple work areas
- commit creation
- push, merge, rebase, reset, or force operations
- a request phrased as full Gitflow execution

## Task Checklists

For Gitflow continuation:

- verify branch type
- review local changes
- prepare staging plan
- suggest commit message
- stop before irreversible actions unless explicitly requested

For commit suggestions:

- use English
- use Conventional Commits
- keep scope descriptive
- avoid generic messages
