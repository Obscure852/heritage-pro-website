# Client Setup Wizard — P7 Local QA Evidence

This record captures repository-level and local-browser evidence gathered during the P7 QA/security hardening pass. It is not a substitute for the real college pilot or production-environment sign-off.

| Item | Value |
|---|---|
| Date | 2026-08-11 |
| Timezone | Africa/Gaborone (CAT) |
| Environment | Local Laravel server, `APP_ENV` non-production |
| Browser target | Codex in-app browser |
| Local URL | `http://127.0.0.1:8010/setup/resume` |
| Data | No real client data; resume page inspected without submitting an email |

## Interactive checks

| Check | Result | Evidence |
|---|---|---|
| Public resume page renders | Pass | Page heading is “Continue your client setup”; the form exposes a labeled invitation-email field. |
| Mobile viewport at 390×844 | Pass | `document.documentElement.scrollWidth` equals `clientWidth` at 390px; no horizontal overflow was detected. |
| Resume form labeling | Pass | The email input is associated with the visible “Invitation email” label; the form method is `POST`. |
| CSRF field present | Pass | The rendered resume form contains a hidden `_token` input. |
| Missing-CSRF mutation rejected | Pass | A local POST without `_token` returned HTTP 419 “Page Expired”. No email was submitted. |

## Repository-level security checks

| Check | Result | Evidence |
|---|---|---|
| Audit event update blocked | Pass | `ClientSetupQualityTest::test_client_setup_audit_events_cannot_be_updated` expects the immutable-model exception. |
| Audit event deletion blocked | Pass | `ClientSetupQualityTest::test_client_setup_audit_events_cannot_be_deleted` expects the immutable-model exception. |
| Focused quality suite | Pass | `php artisan test --filter='ClientSetupQualityTest'` — 12 tests passed. |
| Synthetic end-to-end pilot | Pass | `ClientSetupPilotTest` completes academic submission, optional deferrals, a staged workbook, change request/response, review and archive. |
| Client-setup regression suite | Pass | The focused client-setup/CRM render suite passes 53 tests. |
| Full application suite | Pass with known baseline | 331 tests passed; six unrelated existing CRM failures remain documented in the implementation tracker. |
| Syntax and whitespace | Pass | PHP lint passed for changed model/controller files and `git diff --check` passed. |

## Remaining pilot evidence

The following remain environment-owned and must not be marked complete from this local record: second-device resume, browser back navigation across a real saved wizard journey, keyboard-only completion of every stage, 200% zoom, long CRM-table inspection on a real mobile browser, production CSRF verification, production mail/queue/storage checks, scanner/importer adapters and the real college pilot.
