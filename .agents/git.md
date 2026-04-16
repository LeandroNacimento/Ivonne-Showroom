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
2. warn if the work should start on a new branch
3. suggest a branch name
4. stop before any Git action unless the user explicitly authorizes it

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

* warning when a task should live in a new branch
* suggesting branch names instead of creating them autonomously
* suggesting commit messages instead of committing autonomously
* stopping before irreversible actions
* stopping before implementation when a new branch is required but not yet resolved

## Required Default Response

When the user requests a new feature, respond by:

1. stating that the work should live on a new `feature/*` branch
2. suggesting a branch name
3. outlining the implementation plan
4. stopping before Git actions unless explicitly authorized

## Escalate When

Escalate when the task may involve:

* branch creation or branch switching
* staging expectations that affect multiple work areas
* commit creation
* push, merge, rebase, reset, or force operations
* a request phrased as full Gitflow execution

## Task Checklists

For Gitflow continuation:

* verify branch type
* review local changes
* prepare staging plan
* suggest commit message
* stop before irreversible actions unless explicitly requested

For commit suggestions:

* use English
* use Conventional Commits
* keep scope descriptive
* avoid generic messages
    