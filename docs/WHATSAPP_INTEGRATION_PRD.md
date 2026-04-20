# WhatsApp and SMS Feature-Gated Integration PRD

## Product Goal
Add WhatsApp as a first-class outbound communications channel for staff messaging while making both SMS and WhatsApp features visible only when enabled from the Communications Setup page.

## Scope
- In scope:
  - Communications Setup channel toggles and provider settings
  - Channel-aware feature gating in Blade, routes/controllers, and services
  - Channel-aware message storage and delivery tracking
  - Staff direct messaging for SMS and WhatsApp
  - Staff broadcast messaging for SMS and WhatsApp
  - WhatsApp template catalog, sync flow, and webhook plumbing
  - Documentation and phase tracking under `/docs`
- Out of scope for v1:
  - In-app WhatsApp inbox/conversation UI
  - Sponsor WhatsApp broadcasts
  - Promotional/marketing WhatsApp campaigns
  - Multi-provider support beyond Twilio for WhatsApp

## Success Criteria
- Admins can enable or disable SMS and WhatsApp independently from the Communications Setup page.
- Disabled channels do not appear in the staff UI, communications UI, sidebar navigation, or admin template screens.
- Disabled channels are blocked at route/controller level and again before transport dispatch.
- Staff direct SMS continues to work when SMS is enabled.
- Staff direct WhatsApp works with approved templates, consent, and delivery tracking when WhatsApp is enabled.
- Staff bulk SMS continues to work when SMS is enabled.
- Staff bulk WhatsApp broadcasts send only to eligible opted-in staff and report skipped recipients.

## Current State
- SMS already exists for:
  - Direct send from staff records
  - Sponsor and staff bulk sends
  - SMS templates
  - SMS delivery webhook logging
- Channel availability is not consistently enforced:
  - Existing interfaces hardcode SMS actions and labels
  - Existing routes do not prevent access when SMS is disabled
  - Existing service/controller logic assumes SMS is the only outbound messaging channel
- Message persistence is SMS-shaped:
  - `messages` stores `sms_count`, SMS pricing, and SMS-oriented status fields
  - There is no consent store for WhatsApp
  - There is no WhatsApp template catalog
  - There is no generic delivery-event log for cross-channel tracking

## Feature Flag Contract
- `features.sms_enabled`
  - Master switch for SMS interface visibility and transport execution
- `features.whatsapp_enabled`
  - Master switch for WhatsApp interface visibility and transport execution

## Feature Visibility Rules

### Visibility Matrix
| SMS Enabled | WhatsApp Enabled | Direct Staff SMS | Direct Staff WhatsApp | Staff Bulk SMS | Staff Bulk WhatsApp | Sponsor Bulk SMS | SMS Templates | WhatsApp Templates | Messaging Menu |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| No | No | Hidden | Hidden | Hidden | Hidden | Hidden | Hidden | Hidden | Hidden |
| Yes | No | Visible | Hidden | Visible | Hidden | Visible | Visible | Hidden | Visible |
| No | Yes | Hidden | Visible | Hidden | Visible | Hidden | Hidden | Visible | Visible |
| Yes | Yes | Visible | Visible | Visible | Visible | Visible | Hidden for WhatsApp-only flows | Visible | Visible |

### Interface Rules
- Staff list:
  - Show the direct message button only if at least one messaging channel is enabled.
  - Inside the modal, show only enabled channels.
- Staff profile:
  - Show communication history when at least one channel is enabled.
  - Show WhatsApp consent controls only when WhatsApp is enabled.
- Communications bulk messaging page:
  - Hide sponsor SMS controls when SMS is disabled.
  - Hide staff SMS controls when SMS is disabled.
  - Hide staff WhatsApp controls when WhatsApp is disabled.
- Sidebar:
  - Show `Messaging` only when at least one outbound messaging channel is enabled.
  - Show `SMS Templates` only when SMS is enabled.
  - Show `WhatsApp Templates` only when WhatsApp is enabled.
- Settings:
  - Communications Setup remains the single source of truth for channel enablement and provider configuration.

## Architecture

### Shared Channel State
- `CommunicationChannelService`
  - Reads `features.sms_enabled` and `features.whatsapp_enabled`
  - Exposes per-channel checks and “any enabled” checks
  - Shared by Blade, middleware, controllers, and services

### Enforcement Layers
- Blade/UI:
  - Buttons, tabs, modals, filters, and navigation must render only for enabled channels
- Route/controller:
  - Channel-specific routes use route middleware
  - Mixed-channel routes check whether at least one channel is enabled
- Service-layer:
  - Final pre-dispatch guard prevents sends for disabled channels

### Messaging Data Model
- Extend `messages` to support:
  - `channel`
  - `provider`
  - `recipient_address`
  - `template_name`
  - `template_external_id`
  - `metadata`
- Add `communication_delivery_events` to normalize provider callbacks and state transitions across channels
- Add `recipient_channel_consents` to store WhatsApp opt-in state
- Add `whatsapp_templates` to store synced provider templates
- Add `communication_inbound_messages` to log inbound replies for audit and future expansion

## Endpoint Contracts

### Direct Staff Messaging
- `POST /staff/send-message/{recipientType}/{id}`
- Request:
  - SMS:
    - `channel=sms`
    - `message`
  - WhatsApp:
    - `channel=whatsapp`
    - `template_id`
    - `template_variables`
    - optional `record_consent` fields for admin-managed opt-in capture

### Bulk Messaging
- `POST /notifications/send-bulk-message`
- Request:
  - Shared:
    - `channel`
    - `recipientType`
  - SMS:
    - `message`
    - existing staff/sponsor filters
  - WhatsApp:
    - `template_id`
    - `template_variables`
    - staff-only filters

### WhatsApp Templates
- `GET /notifications/whatsapp-templates`
- `GET /notifications/whatsapp-templates/api/list`
- `POST /notifications/whatsapp-templates/sync`

### Consent
- `POST /staff/{user}/communication-consent`

### Webhooks
- `POST /api/webhooks/whatsapp/status`
- `POST /api/webhooks/whatsapp/inbound`

## Schema Changes
- `messages`
  - add channel and provider metadata fields while keeping legacy SMS fields
- `communication_delivery_events`
  - generic status/event history
- `recipient_channel_consents`
  - opt-in/opt-out tracking
- `whatsapp_templates`
  - local cache of approved Twilio content templates
- `communication_inbound_messages`
  - inbound WhatsApp replies audit log

## UX Rules
- SMS remains freeform.
- WhatsApp direct and bulk sends are template-driven only in v1.
- WhatsApp must validate:
  - channel enabled
  - phone number present
  - staff consent present or newly recorded by an authorized admin
  - template exists and is approved
  - required variables supplied
- Communication history must display:
  - channel badge
  - provider
  - body or template summary
  - delivery state
  - timestamps

## Implementation Notes by Phase

### Phase 1: Planning and Documentation Baseline
- Create:
  - `/docs/WHATSAPP_INTEGRATION_PRD.md`
  - `/docs/WHATSAPP_INTEGRATION_PHASE_TRACKER.md`
- Document current SMS entry points, settings model, feature visibility rules, and phase acceptance criteria.

### Phase 2: Communications Setup and Feature-Gating Foundation
- Seed new WhatsApp settings and feature flag.
- Add `CommunicationChannelService`.
- Add route middleware for channel checks.
- Update communications setup screen to expose WhatsApp settings and keep SMS/WhatsApp toggles authoritative.
- Update navigation and SMS UI entry points to respect the shared channel service.

### Phase 3: Channel-Aware Messaging Model
- Extend `messages`.
- Add delivery events, consent, templates, and inbound tables.
- Update `Message` model and related helpers to populate channel metadata.

### Phase 4: Staff Direct Messaging
- Replace the staff SMS-only modal with a channel-aware modal.
- Add WhatsApp template selection and template variable capture.
- Add server-side direct-send endpoint with channel validation.
- Add consent management for staff records.

### Phase 5: Staff Broadcast Messaging
- Keep sponsor bulk SMS under SMS gating only.
- Add staff WhatsApp bulk send path.
- Add template sync and template API endpoints.
- Return skipped-recipient summaries for ineligible staff.

### Phase 6: Delivery Tracking, Webhooks, and History
- Log WhatsApp status callbacks into `communication_delivery_events`.
- Update `messages.delivery_status`.
- Log inbound WhatsApp replies.
- Update staff communication history to render both channels correctly.

### Phase 7: QA, Rollout, and Documentation Closure
- Add targeted tests for channel service and middleware.
- Add regression verification for SMS and direct WhatsApp flows.
- Update the tracker only when acceptance criteria and verification checks are complete.

## Acceptance Criteria by Phase
- Phase 1:
  - PRD and tracker exist in `/docs`
- Phase 2:
  - SMS and WhatsApp toggles control interface visibility and route access
- Phase 3:
  - Messages, consent, templates, delivery events, and inbound records persist cleanly
- Phase 4:
  - Staff direct SMS and WhatsApp send paths work per channel rules
- Phase 5:
  - Staff broadcasts support SMS and WhatsApp with proper channel-specific validation
- Phase 6:
  - Delivery callbacks and inbound WhatsApp messages are logged and surfaced in history
- Phase 7:
  - Tests and manual verification are recorded in the tracker

## Manual QA Checklist
- Turn SMS off and verify:
  - staff direct SMS button disappears
  - bulk SMS controls disappear
  - SMS template menu disappears
  - SMS send endpoints return blocked response
- Turn WhatsApp off and verify:
  - staff WhatsApp controls disappear
  - WhatsApp templates menu disappears
  - WhatsApp send/template sync endpoints return blocked response
- Turn both on and verify:
  - direct staff modal shows both channels
  - staff bulk UI shows SMS and WhatsApp options
  - communication history renders channel badges
- Record staff consent and verify:
  - WhatsApp direct send works
  - WhatsApp direct send fails without consent
- Sync templates and verify they appear in direct/bulk WhatsApp selectors
- Trigger webhook callbacks and verify:
  - delivery events are recorded
  - message delivery state updates
  - inbound replies are logged

## Rollout Notes
- Keep SMS behavior backward compatible.
- Do not remove legacy SMS delivery logs yet.
- Use the phase tracker as the release status source for this workstream.
