# Client Setup Wizard Pilot Runbook

This runbook is the evidence and operating procedure for the Phase 7 pilot. It is designed for one real college or a fully synthetic institution that follows the same sequence and data boundaries.

## Pilot objective

Prove that a client can receive a public link, verify access, complete the required academic configuration in stages, stop and resume safely, complete or defer optional work, upload migration templates, respond to CRM change requests, and reach an approved implementation handoff without exposing data or bypassing CRM controls.

The pilot is successful only when every mandatory scenario has a recorded result, evidence reference, tester, timestamp and disposition for any deviation.

## Roles and test accounts

| Role | Account | Purpose |
|---|---|---|
| Pilot client | A dedicated test mailbox controlled by the pilot team | Completes the public wizard and receives workflow mail |
| Implementation owner | Active CRM manager or representative | Reviews the submission and receives assigned notifications |
| CRM administrator | Active CRM administrator | Creates the invitation, handles escalation and approves the final record |
| QA observer | Read-only evidence owner | Captures screenshots, response codes, mail evidence and defect references |

Use synthetic institution, staff, student and policy data unless the pilot has a written data-processing approval. Do not use real identity numbers, live student marks or unredacted production exports.

## Environment gate

Before starting:

- Confirm the application URL is the intended staging or pilot domain.
- Confirm APP_ENV is not production unless the release owner explicitly approved the pilot.
- Confirm the public mail sender, CRM fallback recipients and notification settings in config/client_setup.php.
- Confirm the documents disk is private and readable only through approved CRM download routes.
- Confirm the scheduled command client-setup:reminders is registered once and uses the intended scheduler.
- Confirm the current schema version and migration template version shown in the CRM and public Migration stage.
- Confirm a database backup or snapshot exists before creating pilot data.
- Record the application commit/version, database migration state, browser versions and tester names in the evidence log.

## Evidence log

Create one evidence record per scenario using this minimum structure:

| Field | Value |
|---|---|
| Scenario ID | e.g. P7-SEC-04 |
| Tester | Name |
| Started/finished | Local time with timezone |
| Account/browser | Client or CRM; browser and device |
| Expected result | Exact acceptance statement |
| Actual result | What happened |
| Evidence | Screenshot, email ID, response code, database/audit query or defect ID |
| Result | Pass / Fail / Blocked |
| Follow-up | Owner and due date |

Never paste raw invitation tokens, verification codes, access cookies or private document contents into the evidence log. Redact screenshots and email previews.

## Functional pilot scenarios

### P7-FUN-01 — Invitation and verification

1. CRM administrator creates a Client Setup invitation for the pilot mailbox.
2. Confirm the client receives the invitation email and the CRM record shows the invitation as active.
3. Open the public link in a private browser window.
4. Request a verification code and verify with the latest code.
5. Confirm the client lands on the first incomplete required stage.

Acceptance:

- The URL is public but the draft is not visible before verification.
- The verification code expires and invalid attempts are capped.
- The raw token is not visible in CRM database fields or notification payloads.

### P7-FUN-02 — Partial save and resume on a second device

1. On Browser A, enter only a portion of the Scope stage and choose Save progress.
2. Choose Save and exit.
3. Open the same invitation in Browser B or a second device.
4. Request a fresh code if required, verify, and return to Scope.
5. Confirm the saved values and last-saved state are present.
6. Use the lost-link form once and confirm a new link is delivered without revealing whether an unknown email exists.

Acceptance:

- No saved field is lost on refresh, exit, device change or re-verification.
- The resume request is rate-limited and does not enumerate client records.

### P7-FUN-03 — Repeatable rows and conditional fields

1. Add two campuses, two responsible contacts and two programmes.
2. Remove one row and save.
3. On Finance, select Defer and confirm only deferred-owner/date fields become active.
4. Change Finance to an in-scope option and confirm the in-scope fields become active while deferred fields are no longer required.
5. Repeat the check for migration and integrations where applicable.

Acceptance:

- Added rows use unique submitted indexes.
- Removed rows do not reappear after refresh.
- Conditional fields are visibly explained, keyboard accessible and validated server-side.

### P7-FUN-04 — Academic readiness and submission

1. Attempt academic submission with one required field missing.
2. Confirm the wizard returns to the relevant stage with a useful validation message.
3. Complete the required academic stages and submit.
4. Confirm the academic revision is frozen.
5. Confirm optional Migration, Integrations and Finance stages remain editable.

Acceptance:

- Optional data never blocks academic submission.
- Academic submission creates a revision and audit event.
- A direct attempt to edit a frozen academic stage is rejected.

### P7-FUN-05 — Migration and evidence uploads

1. Download the versioned staff and student templates.
2. Upload one valid staff workbook and one valid student workbook.
3. Upload one workbook with a wrong header and one with row-level errors.
4. Upload a policy document through Evidence and sign-off.
5. Confirm all files are private and have pending scan status.
6. Confirm CRM cannot download or approve a file until the scan result is approved.

Acceptance:

- No upload causes an automatic production import.
- Validation counts, errors, template version and audit events are recorded.
- Invalid file types are rejected before storage.

### P7-FUN-06 — CRM review and change request

1. CRM owner opens the submission and confirms the academic readiness summary.
2. Add a field-level change request.
3. Confirm the client receives a change notification.
4. Client responds through the verified link.
5. Confirm CRM receives the response and the audit trail records it.
6. Resolve the request and move the submission through review.

Acceptance:

- Representatives cannot view submissions assigned to another owner.
- Change requests identify the relevant stage/field and retain the client response.
- Reviewer actions are auditable.

### P7-FUN-07 — Optional completion and approval

1. Complete or explicitly defer Migration, Integrations/access and Finance.
2. Mark supplemental setup complete.
3. Confirm the overall status becomes Complete submission.
4. CRM moves it to Under review, then Approved, then Archived only when appropriate.
5. Confirm approval notifications are sent to the client and CRM owner.

Acceptance:

- Academic data remains frozen.
- Deferred requirements are distinguishable from missing requirements.
- Approval is not possible through an invalid status transition.

## Security checks

- Check invitation token hashing at rest.
- Check raw tokens and verification codes are absent from notification records and logs.
- Check expired and revoked links return a safe rejection.
- Check public routes cannot access CRM pages.
- Check representative ownership scoping.
- Check attachment download ownership, scan gating and private storage.
- Check CSRF protection on all browser mutations.
- Check rate limits for entry, verification-code, verification and resume endpoints.
- Check response headers for no-index behavior on public setup pages.
- Check no email subject contains personal data, a raw token or verification code.
- Check audit records have no ordinary reviewer mutation endpoint.

## Accessibility and responsive checks

- Complete the pilot using keyboard-only navigation for at least one stage.
- Confirm visible labels, required indicators and focusable validation messages.
- Confirm the current stage is announced through semantic headings and current-state attributes.
- Test at 390×844 and desktop width.
- Confirm repeatable rows, conditional fields, upload controls and long CRM tables remain usable without drag-and-drop.
- Confirm browser zoom at 200% does not hide the primary save/continue controls.

## Notification and scheduler checks

1. Run php artisan client-setup:reminders --now="YYYY-MM-DD HH:MM:SS" in the pilot environment.
2. Record the command summary and notification rows created.
3. Run it again with the same timestamp.
4. Confirm no duplicate reminder is created for the same reminder bucket.
5. Mark one delivery failed with a due retry time in a non-production test environment.
6. Run the command again and confirm bounded retry behavior and failure logging.

## Defect severity and pilot decision

### Blockers

- Any unauthorized access to a client submission, CRM record or private attachment.
- Any raw token, verification code or sensitive identity data in persistent logs or email subjects.
- Any automatic production import from an onboarding upload.
- Data loss after save, exit, refresh or second-device resume.
- A client cannot complete the required academic submission.

### Major defects

- Incorrect owner notification or CRM scoping.
- Incorrect conditional validation that blocks a valid path or accepts an invalid path.
- Change-request response not visible in CRM.
- Reminder duplication or unbounded mail retries.

### Minor defects

- Copy, spacing, non-blocking accessibility issue or cosmetic rendering defect with a documented workaround.

Pilot outcome is Pass only when there are no open Blockers or Major defects and every required scenario has evidence. A failed pilot must leave the data in staging, preserve evidence, assign owners and document whether the next action is fix-and-retest, rollback, or defer with written approval.

## Rollback and cleanup

If a blocker is found:

1. Stop further pilot invitations and uploads.
2. Revoke the pilot invitation.
3. Preserve audit events, notification delivery records and evidence.
4. Remove synthetic attachments through the approved retention process.
5. Restore the staging database snapshot only if the release owner approves and evidence has been exported.
6. Record the failed scenario, root cause, fix, retest and release decision.

Do not delete evidence or use destructive database commands as a substitute for the documented rollback decision.
