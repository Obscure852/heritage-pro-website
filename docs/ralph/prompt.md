# Ralph Agent Instructions

You are an autonomous agent working on a Laravel 9.x School Management System.

## Your Task

1. Read `docs/ralph/prd.json`
2. Read `docs/ralph/progress.txt` (check Codebase Patterns at top first)
3. Ensure you're on the correct branch
   - If not: `git checkout -b [branchName]` or `git checkout [branchName]`
4. Pick the highest priority story where `passes: false`
5. Implement that ONE story only
6. Run tests: `php artisan test`
7. Commit: `feat: [ID] - [Title]`
8. Update prd.json: set `passes: true` for completed story
9. Append learnings to progress.txt

## If Tests Fail

- Fix the failing tests before proceeding
- Do NOT mark story as passed if tests fail
- If stuck after 3 attempts, add issue to progress.txt under "## Open Questions" and move to next story

## Progress Format

APPEND to progress.txt after each story:

```
## [Date] - [Story ID]: [Title]
- What was implemented
- Files changed
- **Learnings:**
  - Patterns discovered
  - Gotchas encountered
---
```

## Codebase Patterns

Add reusable patterns to the TOP of progress.txt (below header):

```
## Codebase Patterns
- Migrations: Check if column exists before adding
- Controllers: Split by school type (Senior, Junior, Primary, Reception)
- Routes: Modularized in /routes/ with includes per feature
- Views: Mirror controller organization in /resources/views/
- Validation: Use FormRequests, never $request->all()
- Transactions: Wrap multi-step DB updates in DB::transaction()
```

## Project-Specific Notes

- **Testing:** `php artisan test` or `php artisan test --filter=TestName`
- **Assets:** `npm run dev` (Vite), `npm run build` for production
- **Cache:** Clear with `php artisan cache:clear && php artisan config:clear`
- **Routes:** Check `/routes/` subdirectories for feature-specific routes

## Stop Condition

If ALL stories in prd.json have `passes: true`, reply with:

```
<promise>COMPLETE</promise>
```

Otherwise, end normally after completing one story.
