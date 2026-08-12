# Client Setup Wizard — Production Release and Operations Runbook

This runbook is the operational gate for releasing the public Client Setup Wizard. It complements the implementation plan and the pilot runbook. The wizard must not be advertised as production-ready until the release checks, pilot evidence and environment-owned controls in this document are complete.

## Release principle

The workflow may accept public submissions, but it must never be treated as an unattended production import. Uploaded staff and student workbooks are onboarding evidence and staged migration inputs. They remain private, are scanned, validated and approved in the CRM, and are imported only through a separately approved production process.

The release owner must record a signed decision of one of:

- Go — all mandatory gates pass and the rollback owner is available.
- Go with exception — only documented minor issues remain, each with an owner, workaround and expiry date.
- No-go — any security, privacy, data-loss, mail-delivery, backup, scanner, importer, permission or pilot gate is incomplete.

## Release roles

| Role | Responsibility |
|---|---|
| Release owner | Approves the release decision, timing and rollback. |
| Technical owner | Runs migrations, preflight checks, queue workers, scheduler and monitoring. |
| CRM owner | Confirms permissions, assignment rules, review queues and approval workflow. |
| Data migration owner | Confirms template versions, validation reports, scanner status and import approvals. |
| Support owner | Owns client instructions, support response, escalation and defect triage. |
| Privacy/security reviewer | Reviews storage, access, retention, logs and evidence handling. |

One person may hold more than one role in a small team, but the release decision and verification should still be recorded separately from the implementation work.

## 1. Pre-release inventory

Record the following before changing the production environment:

| Item | Evidence required | Status |
|---|---|---|
| Application version | Commit, release tag or deployment identifier | [ ] |
| Database target | Host/database identifier without credentials | [ ] |
| APP_URL | Intended HTTPS public domain | [ ] |
| Mail provider | Delivery-capable mailer, sender and test recipient | [ ] |
| Queue | Connection, worker count, retry policy and failed-job handling | [ ] |
| Scheduler | One scheduler entry for reminders and its host | [ ] |
| Private storage | Disk driver, root/bucket and access policy | [ ] |
| Database backup | Timestamp, retention and restore owner | [ ] |
| Document backup | Timestamp, retention and restore owner | [ ] |
| Scanner | Registered adapter, result callback and failure behavior | [ ] |
| Importer | Registered staff/student importer and approval boundary | [ ] |
| CRM users | Admin, manager and representative accounts for testing | [ ] |
| Pilot approval | Completed pilot evidence and decision | [ ] |

Do not put passwords, tokens, verification codes, private keys or raw client data in this record.

## 2. Environment configuration

The following controls are read by the release preflight command:

- Application URL must be the production HTTPS domain.
- Mail must use a delivery-capable mailer with a valid sender address.
- Queue must be durable; synchronous queue execution is not acceptable for production notification delivery.
- The documents disk must remain private and its storage root or bucket must be available.
- Client Setup rate limits and invitation expiry must be positive and reviewed.
- Backup and pilot flags must be explicitly marked after evidence is verified.
- A production malware-scanner adapter and production staff/student importer adapter must be registered before production approval.

Run the command after configuration and after deployment:

    php artisan client-setup:release-check

Run the strict machine-readable form for the release evidence pack:

    php artisan client-setup:release-check --strict --json

The command is a gate, not a substitute for evidence. A warning in a local environment can become a failure in production. Store the output with the deployment record and attach the independent evidence for backup, pilot, scanner, importer and browser checks.

## 3. Database deployment

1. Put the public link-generation process on hold.
2. Confirm the release version and database target.
3. Take a database backup and record its completion time and restore identifier.
4. Run migrations using the approved deployment procedure.
5. Confirm all required Client Setup tables exist:
   - submissions
   - invitations
   - stage progress
   - revisions
   - events
   - attachments
   - migration uploads
   - notifications
6. Confirm migration status shows no pending Client Setup migration.
7. Run the release preflight command.
8. If the deployment fails, do not create invitations. Preserve logs and use the rollback procedure below.

The release owner must separately verify that the backup can be restored. A successful backup command without a restore check is not a complete backup gate.

## 4. Mail, queue and scheduler

### Mail

Send a controlled test invitation to an internal mailbox. Confirm:

- the sender and reply-to address are correct;
- the public link points to the production domain;
- the subject contains no personal data, raw token or verification code;
- the verification email contains only the transient code and expected expiry guidance;
- CRM workflow notifications reach the assigned owner or configured fallback;
- a provider failure is recorded and does not expose secrets.

### Queue

Confirm at least one worker is running with the intended queue, timeout and retry settings. Confirm failed jobs are visible to the technical owner. Send one controlled notification through the real queue and record the delivery identifier.

### Scheduler

Register the Client Setup reminder command exactly once. Run it in a staging or controlled production window and verify:

- stale draft reminders are bounded;
- review reminders are bounded;
- invitation expiry warnings are bounded;
- failed delivery retries stop at the configured maximum;
- running the same time window twice does not create duplicate reminder records.

## 5. Storage and attachment controls

The documents disk must be private. The public wizard must never expose a direct storage URL.

Verify all of the following:

- an uploaded file is stored outside the public web root;
- a guessed path returns no file;
- a pending or rejected scan cannot be downloaded by a client or CRM reviewer;
- a file from another submission returns not found;
- only an authorized CRM route can download an approved file;
- upload size, extension, MIME and workbook validation limits are active;
- malware scanning failure leaves the file blocked and creates an actionable CRM event;
- document backup and retention are covered by the same recovery plan as database records.

## 6. Scanner and importer release gates

### Malware scanner

Before go-live, document the scanner adapter contract:

1. New files enter pending status.
2. The scanner receives an isolated file reference.
3. A clean result changes the attachment to approved.
4. A malicious, unavailable or timed-out result changes the attachment to rejected or keeps it blocked.
5. The result records scanner name, timestamp, status and a non-sensitive reference.
6. Retry behavior is bounded and visible.
7. No file is downloadable or importable while pending.

Manual CRM approval is not a production substitute for malware scanning. If the scanner is unavailable, the release is no-go.

### Staff and student importer

The importer must be a separate, explicitly authorized operation:

1. CRM reviewer downloads the validated workbook and validation report.
2. Data migration owner confirms the template version and row-level errors.
3. Scanner status is approved.
4. CRM reviewer approves the upload for import.
5. Importer runs in a controlled job or command with an idempotency key.
6. Import result records created, updated, skipped and failed rows.
7. Failed rows are exported for correction; the original upload remains unchanged.
8. A second reviewer signs off high-risk changes before production import.

No upload route may call the production importer automatically.

The CRM import action is a separate administrator-only operation. It requires a current compatible template, successful validation, approved malware scan and CRM approval. The upload moves through not started, queued, running, completed or failed states. The importer job uses the upload UUID as its idempotency key, so a repeated job cannot import the same upload twice. A failure records a bounded error and leaves the upload eligible for a deliberate retry after the cause is resolved.

The CRM review record provides a complete CSV validation report. The on-screen preview may show only the first issues for usability, but the report is streamed from the normalized row-error table and must include every recorded row error, including errors beyond the preview limit.

## 7. Permissions and workflow verification

Use test accounts for administrator, manager and representative roles. Confirm:

- representatives see only submissions assigned to them;
- managers see the intended review queue and can perform their permitted actions;
- administrators can assign, request changes, approve uploads and archive records;
- public clients cannot open CRM routes;
- invitation revocation and expiry work;
- approval cannot bypass required scanner and import gates;
- audit events are append-only from the ordinary CRM interface;
- notifications do not reveal other institutions or recipients.

Record the route, role, expected result, actual result and evidence reference. Do not use a real client invitation for permission tests.

### Retry-safe submission checks

During staging and the pilot, deliberately repeat the final academic submission request and the final supplemental submission request. A repeated academic request must leave one academic submission transition, one audit event and one notification set. A repeated supplemental completion request must preserve the original completion timestamp and must not create a second completion audit event or duplicate notification set. Requesting a verification code must send only the verification-code message; it must not emit submission-complete notifications.

Record the invitation UUID, request timestamps, response status, resulting submission status, audit-event count and notification-event count in the pilot evidence pack. Treat any additional transition, audit event or notification as a release-blocking defect until it is explained and approved.

## 8. Client-facing operating instructions

Send clients one public invitation link with these instructions:

1. Open the link and verify the email address.
2. Complete Scope, Institution, Programmes and Curriculum first.
3. Save after each stage; Save and exit is safe.
4. Use the resume link if work is interrupted or the original link is lost.
5. Finance, Integrations and Migration may be completed later unless the implementation owner has made them required for the agreed scope.
6. Download the current staff and student templates from the Migration stage.
7. Do not email passwords, identity documents or raw verification codes to support.
8. Upload only the requested files and keep a copy of the source workbook.
9. Contact the support owner with the institution name and invitation email, never with a token or verification code.

The implementation owner should send the client the expected deadline, support hours, accepted file types, template version and definition of academic submission.

## 9. Support and incident handling

### First response checklist

- Confirm the institution and invitation email without asking for a token.
- Check invitation status, expiry and last activity in CRM.
- Check notification delivery status and failed-job records.
- Check whether the issue affects one stage, one browser or all clients.
- Ask the client to use the resume flow if the link is lost.
- Never request a verification code by email or chat.

### Escalate immediately

- suspected unauthorized access or cross-institution visibility;
- raw token or verification code in logs, email or support tickets;
- data loss after a successful save;
- malware scanner failure or an attachment marked clean incorrectly;
- any unexpected production import;
- repeated queue, mail or scheduler failure;
- backup or restore uncertainty.

Freeze new invitations and uploads while a security or data-integrity incident is investigated. Preserve audit events, notification records and deployment evidence. Do not delete evidence during incident cleanup.

## 10. Retention and privacy

Before go-live, agree retention periods with the data owner for:

- draft and submitted questionnaire data;
- rejected and superseded attachments;
- validated migration workbooks;
- audit events and notification delivery records;
- abandoned invitations and expired verification attempts;
- import reports and failed-row extracts.

Use the following operating procedure as the baseline retention schedule. The data owner must replace the placeholders with approved periods before production release.

| Data class | Default handling | Owner | Evidence required |
|---|---|---|---|
| Draft and submitted questionnaire data | Retain until implementation handoff plus the approved support period; archive before deletion | Implementation owner | Archive/deletion job record and submission UUID list |
| Rejected and superseded attachments | Retain only for the approved dispute/support window, then delete from private storage | Data migration owner | Attachment path/hash inventory and deletion confirmation |
| Validated migration workbooks | Retain until the controlled import and reconciliation are signed off; then delete or archive the approved extract | Data migration owner | Import result, reconciliation and retention decision |
| Audit events and notification delivery records | Preserve for the approved audit/legal period; do not delete individual events through the CRM | Privacy/security reviewer | Immutable event export and retention review |
| Abandoned invitations and expired verification attempts | Expire access immediately; retain minimal operational evidence for the approved abuse/support period | Support owner | Expiry/revocation report |
| Import reports and failed-row extracts | Retain through reconciliation and the approved remediation period | Data migration owner | Report archive reference and deletion/extension decision |

The release owner must record the retention period, legal basis, system of record, deletion/archival owner, schedule, exception process and recovery implications for each class. Run retention only through an approved, logged job or documented storage operation. Do not use ad-hoc database deletion from support tools, and do not delete audit events to satisfy a client correction request. Preserve the minimum audit record required by policy after a submission or attachment is archived.

Before the first production cleanup, perform a dry run that outputs counts and exact record identifiers without deleting anything. Have the data owner approve the output, take the required database/private-document backup, run the cleanup, and retain the job result with the release evidence pack. A failed cleanup must stop safely and create an operational alert for the owner.

## 11. Rollback procedure

Use rollback when a blocker is found or a release gate becomes invalid.

1. Release owner declares rollback and records the reason.
2. Stop new invitation creation, uploads and importer execution.
3. Keep the public entry point available only if it can safely display maintenance guidance; otherwise disable new sessions.
4. Revoke or pause invitations created by the faulty release where required.
5. Preserve database, document, queue, mail, scanner and audit evidence.
6. Stop the new queue worker or deploy the previous application version according to the hosting procedure.
7. Restore the database and private-document snapshot only when the restore owner confirms the checkpoint and the release owner approves.
8. Re-run schema, storage, permission and public-boundary checks.
9. Confirm no new notifications or imports are running against the rolled-back version.
10. Communicate the client impact and next support action.
11. Create a defect record with root cause, affected submissions, evidence, remediation and retest result.

Rollback is not complete until a controlled invitation, save, resume, CRM review and private attachment check pass on the restored version.

## 12. Go-live evidence pack

The release owner should retain:

- preflight output in normal and strict mode;
- migration status and backup/restore evidence;
- mail, queue and scheduler test evidence;
- private storage and attachment access evidence;
- scanner and importer adapter evidence;
- permission matrix results;
- completed pilot runbook and decision;
- deployment identifier and timing;
- support owner and escalation contacts;
- approved exceptions and expiry dates;
- rollback checkpoint and owner.

## 13. Post-release checks

Within the first operating day:

- create one controlled invitation and verify delivery;
- confirm the first client save creates stage progress and audit events;
- confirm the CRM queue shows the submission to the correct owner;
- confirm no unexpected failed jobs, scanner failures or duplicate reminders;
- confirm private attachment access with an approved test file;
- review logs for tokens, verification codes and sensitive subjects;
- contact the support owner and verify the escalation path.

Within the first week, review completion rates by stage, abandonment, resume requests, validation errors, change-request turnaround, notification failures and support themes. Feed the findings back into the implementation plan before expanding to more colleges.

## Release decision

| Gate | Owner | Evidence | Result |
|---|---|---|---|
| Preflight passes in production configuration | Technical owner | Command output | [ ] |
| Backup and restore verified | Technical owner | Backup and restore record | [ ] |
| Scanner adapter operational | Security/data owner | Clean and blocked test evidence | [ ] |
| Importer approval boundary operational | Data migration owner | Approval and dry-run evidence | [ ] |
| Pilot has no open blockers or majors | Release owner | Pilot runbook | [ ] |
| Permissions and private storage pass | CRM/security owner | Access test evidence | [ ] |
| Support and rollback owners available | Release owner | Contact and checkpoint record | [ ] |
| Final decision | Release owner | Signed Go / Exception / No-go | [ ] |
