# Client Setup Wizard — Implementation Team Training

This guide is the short operational handoff for CRM users who create, review and approve public client setup submissions. It assumes the production runbook and the implementation plan have been read.

## Learning objectives

After the session, a team member should be able to:

- create and securely share an invitation;
- explain the academic-first submission model to a client;
- distinguish incomplete information from an explicit deferral;
- review readiness, revisions, uploads and audit history;
- request and resolve a client correction;
- approve a migration upload only after every safety gate passes; and
- escalate delivery, scanner, import, privacy or retention incidents.

## Role boundaries

| Role | Can do | Must not do |
|---|---|---|
| Representative | Review assigned submissions, add notes, request changes and follow progress | Open another representative’s submission or approve a production import without the required permission |
| Manager | Review the team queue, assign work, request changes and approve permitted workflow actions | Bypass scanner, compatibility, validation or administrator import gates |
| Administrator | Create/revoke/resend invitations, manage assignment, approve eligible uploads and queue controlled imports | Treat a client workbook as a direct production import or share raw access tokens in support channels |
| Data migration owner | Validate template/version compatibility, reconcile import results and own retention evidence | Approve an unscanned, incompatible or unvalidated file |
| Release/support owner | Own pilot evidence, incidents, rollback and retention decisions | Delete audit or notification evidence as a troubleshooting shortcut |

## Standard client handoff

1. Open **CRM → Client Setup → New invitation**.
2. Enter the client contact details and implementation owner. Confirm the email address before sending.
3. Share the emailed link through the approved channel. If copying the one-time preview, clear it from the clipboard after use and never put it in a public ticket.
4. Tell the client to complete the academic stages first, save after each stage, and use **Save and exit** when work is interrupted.
5. Explain that Finance, Integrations and Migration can be completed later or explicitly deferred where permitted.
6. Tell the client to download the current templates from the Migration stage and keep the source workbooks unchanged for reconciliation.

## Review workflow

1. Open the submission from the review inbox and check owner, academic readiness and last activity.
2. Read the readiness summary before opening individual stages. A missing requirement is different from a deliberate deferral.
3. Review revisions when a value appears to have changed. Use comparison to identify removed values and confirm the client’s intended correction.
4. Add private implementation notes for internal context. Do not copy verification codes, raw tokens or unnecessary identity data into notes.
5. Use a change request when the client must amend information. Include the stage and field key where possible, state the required outcome, and resolve it only after the client response is reviewed.
6. Move the review status only when the corresponding evidence exists. Keep the academic revision frozen after academic submission.

## Migration safety gate

Before approving a staff or student workbook, confirm:

- the template version and header fingerprint are compatible;
- validation completed with no blocking row errors;
- the attachment scan is approved;
- the correct submission and data owner are shown; and
- the import is authorized for the intended environment.

Approval stages the upload for controlled import; it does not itself create production records. The administrator-only import action is the next deliberate step. Record the import result and reconcile counts before closing the migration task.

## Practice exercise

Use synthetic data and the pilot runbook to complete this sequence:

1. Create an invitation and verify the public link.
2. Save partial Scope data, leave the flow, and resume it.
3. Complete the academic stages and submit them.
4. Defer one optional section and upload one valid and one invalid workbook.
5. Request a field-level change, respond as the client, and resolve it.
6. Confirm the invalid upload is blocked and the valid upload cannot bypass scan/approval gates.
7. Move the synthetic submission through review and record the evidence reference.

The exercise passes only when the evidence log contains the tester, timestamp, expected/actual result and any follow-up owner. Never use a real client token, verification code or unredacted workbook in training.

## Escalation quick reference

- **Cannot open or resume:** check invitation status/expiry, last activity, notification delivery and rate-limit state; do not ask the client to send a token or code.
- **Upload blocked:** check extension/MIME, template version, validation report and scan status; do not manually mark the file clean.
- **Wrong owner or visibility:** stop review and contact the CRM owner; do not forward the submission or use a direct URL to bypass scoping.
- **Unexpected import or data exposure:** freeze invitations/uploads/imports, preserve evidence and invoke the production runbook incident/rollback procedure.
- **Retention request:** record the request with the data owner; never delete records from a support console.

