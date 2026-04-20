# WhatsApp Integration Phase Tracker

| Phase | Scope | Status | Owner | Started | Completed | Verification | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Phase 1 | Planning and documentation baseline | Completed | Codex | 2026-03-19 | 2026-03-19 | PRD and tracker created under `/docs` | Decision baseline documented in `WHATSAPP_INTEGRATION_PRD.md` |
| Phase 2 | Communications setup and feature-gating foundation | Completed | Codex | 2026-03-19 | 2026-03-19 | `php artisan test tests/Unit/CommunicationChannelServiceTest.php`; `php artisan test tests/Feature/EnsureCommunicationChannelEnabledTest.php`; syntax checks on updated controllers/services | Added shared `CommunicationChannelService`, route middleware, settings seeding, setup-page WhatsApp section, sidebar gating, and SMS/WhatsApp UI gating hooks |
| Phase 3 | Channel-aware messaging model | Completed | Codex | 2026-03-19 | 2026-03-19 | Syntax checks on messaging services/controllers; schema files added for review | Added channel fields on `messages`, delivery events, recipient consent, WhatsApp template catalog, and inbound message log tables/models |
| Phase 4 | Staff direct messaging | Completed | Codex | 2026-03-19 | 2026-03-19 | Syntax checks on `UserController.php`; manual code-path review of staff list/modal + controller route | Replaced staff direct SMS modal with channel-aware send flow and added consent management endpoint |
| Phase 5 | Staff broadcast messaging | Completed | Codex | 2026-03-19 | 2026-03-19 | Syntax checks on `NotificationController.php`; manual code-path review of bulk messaging UI and endpoint wiring | Added staff WhatsApp bulk modal, eligible/skipped recipient checks, and channel-aware bulk send endpoint while preserving SMS sponsor/staff flows |
| Phase 6 | Delivery tracking, webhooks, and history | Completed | Codex | 2026-03-19 | 2026-03-19 | `php -l app/Http/Controllers/WhatsAppWebhookController.php`; `php -l app/Http/Controllers/WhatsappTemplateController.php`; `php -l app/Console/Commands/SyncWhatsappTemplates.php` | Added WhatsApp webhooks, delivery-event logging, template sync command/page, and channel-aware history rendering in staff and communications views |
| Phase 7 | QA, rollout, and documentation closure | In Progress | Codex | 2026-03-19 |  | Targeted unit and feature tests passed; live provider QA not yet run | Manual QA against a configured Twilio WhatsApp sender and real webhook callbacks is still required before marking complete |

## Status Rules
- `Not Started`
- `In Progress`
- `Blocked`
- `Completed`

## Completion Policy
- A phase moves to `Completed` only after:
  - all scoped code changes for that phase are merged into the workspace
  - acceptance criteria for that phase are met
  - verification evidence is recorded in the `Verification` column
- Verification entries should include:
  - test command or suite name
  - manual QA note
  - related commit or PR reference when available
