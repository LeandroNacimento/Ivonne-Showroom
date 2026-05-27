# Git Policy

## Purpose

Use this file to make branch, Gitflow, staging, and commit decisions safely.

## Use When

Use this file when changing:

* branch strategy
* branch naming
* Gitflow continuation
* staging expectations
* commit suggestions
* Git safety boundaries

## Do Not Use For

Do not use this file for:

* domain-rule decisions
* architecture decisions
* deploy mechanics
* security design decisions

## Hard Rules

Apply these rules:

* Treat `main` as production.
* Treat `develop` as integration.
* A new feature MUST start on a new `feature/*` branch.
* A bug fix MUST start on a new `fix/*` branch.
* A critical production correction MUST use `hotfix/*`.
* A refactor or relevant technical improvement MUST start on a new branch.
* Never assume the current branch is valid for new work.
* Never continue new scoped work on the current branch unless the user explicitly says to do so.
* When the user asks to implement or fix something, assume they want execution to begin immediately.
* Before editing files for a new scoped task, create and switch to the correct new branch automatically.
* Do not wait for an extra confirmation to create or switch to that branch when the user already requested implementation or a fix.
* Do not work directly on `main` by initiative.
* Do not commit by initiative.
* Do not push by initiative.
* Do not execute Gitflow actions by initiative.
* Do not perform irreversible Git actions without explicit user instruction.
* Use English Conventional Commits when suggesting commit messages.

## Mandatory Branch Gate

Before proposing implementation or editing files, classify the task.

If the request is:

* a new feature
* a bug fix
* a refactor
* a relevant technical improvement

then you MUST:

1. determine the correct branch type
2. create and switch to the new branch before implementation
3. choose a clear branch name consistent with the task
4. continue with implementation without waiting for extra Git confirmation

Do not skip this check.

## Classification Rules

Classify as `feature/*` when the task introduces:

* a new user-facing capability
* a new admin capability
* a new workflow
* a new module
* a meaningful extension of an existing feature

Classify as `fix/*` when the task corrects:

* broken behavior
* incorrect calculations
* regressions
* inconsistencies against expected behavior

Classify as `hotfix/*` only when:

* the issue is critical
* production is affected
* urgent correction is required

## Defaults

Default to:

* creating and switching to the correct new branch before implementation
* choosing branch names autonomously from the task context
* suggesting commit messages instead of committing autonomously
* stopping before irreversible actions
* proceeding with implementation once the branch is ready

## Required Default Response

When the user requests a new feature, respond by:

1. creating and switching to a new `feature/*` branch
2. stating the branch name chosen
3. outlining the implementation plan
4. proceeding with the requested work

## Escalate When

Escalate when the task may involve:

* branch creation or branch switching
* staging expectations that affect multiple work areas
* commit creation
* push, merge, rebase, reset, or force operations
* a request phrased as full Gitflow execution

## Manual Release and Tag Flow

When closing a stable release (PR merged to `main`), apply this conservative manual flow:

1. **Update and switch to main:** `git fetch origin`, `git checkout main`, `git pull origin main`.
2. **Create Annotated Tag:** Always create annotated tags for releases on `main` (e.g., `git tag -a v1.2-stable -m "Release v1.2-stable"`). Do not use lightweight tags. Do not tag on `fix/*` or `feature/*` branches.
3. **Push Tag:** `git push origin v1.2-stable`.
4. **Sync Develop:** Switch to `develop` (`git checkout develop`, `git pull origin develop`). Verify differences (`git log develop..main --oneline`). If `main` has new commits, sync them back using a normal merge (`git merge main -m "chore: synchronize develop after v1.2-stable release"`). Do not use destructive rebase here.
5. **Push Develop:** `git push origin develop`.
6. **Release Notes:** Draft a GitHub Release detailing the functional improvements, technical fixes, and UX upgrades.

## Task Checklists

For Gitflow continuation:

* verify branch type
* review local changes
* prepare staging plan
* suggest commit message
* create/switch branch automatically when implementation or a fix is requested
* stop before irreversible actions unless explicitly requested

For commit suggestions:

* use English
* use Conventional Commits
* keep scope descriptive
* avoid generic messages
