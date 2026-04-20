# Staff Presence and Direct Messaging - Product Requirements Document

**Version:** 1.0  
**Date:** March 2026  
**Author:** Product Team  
**Status:** Draft

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Product Vision & Goals](#2-product-vision--goals)
3. [Current State](#3-current-state)
4. [Problem Statement](#4-problem-statement)
5. [User Personas & User Stories](#5-user-personas--user-stories)
6. [UX Principles and Interaction Model](#6-ux-principles-and-interaction-model)
7. [Functional Specifications](#7-functional-specifications)
8. [Communications Setup Configuration](#8-communications-setup-configuration)
9. [Data Model and Interfaces](#9-data-model-and-interfaces)
10. [Security and Authorization](#10-security-and-authorization)
11. [Performance and Operational Requirements](#11-performance-and-operational-requirements)
12. [Implementation Roadmap by Phase](#12-implementation-roadmap-by-phase)
13. [Testing Strategy](#13-testing-strategy)
14. [Assumptions and Out of Scope](#14-assumptions-and-out-of-scope)

---

## 1. Executive Summary

### 1.1 Purpose

This document defines the requirements for a staff-only internal messaging feature that adds ambient online presence awareness and persistent direct messaging to the main staff portal. The feature enables staff to quietly see which colleagues are currently active in the system and start a direct in-app conversation when needed, without disrupting normal work.

### 1.2 Scope

The feature will provide:
- A quiet topbar launcher that shows the number of currently online staff users
- A non-disruptive dropdown panel listing online staff users
- One-click creation or reopening of staff-to-staff direct conversations
- A full inbox page for viewing, replying to, archiving, and restoring conversations
- Heartbeat-based presence tracking for authenticated staff users
- Communications Setup controls for enabling the feature and adjusting presence behavior

### 1.3 Key Success Metrics

| Metric | Target |
|--------|--------|
| Start a DM from presence UI | 2 clicks or fewer |
| Presence freshness window | Within configured timeout, default 2 minutes |
| Launcher disruption level | No modal, no toast, no auto-open |
| Unread badge accuracy | 100% for active conversations |
| Conversation duplication rate | 0 duplicate threads for the same staff pair |

---

## 2. Product Vision & Goals

### 2.1 Vision Statement

Provide staff with a lightweight internal communication layer that fits naturally into the existing portal, helps them notice who is available, and lets them start a conversation without interrupting the flow of work.

### 2.2 Strategic Goals

1. **Improve internal coordination**  
   Give staff a fast way to contact available colleagues without leaving the platform.

2. **Reduce communication friction**  
   Replace the need for ad hoc external channels when a quick internal message is sufficient.

3. **Preserve focus**  
   Make online presence visible but ignorable, so staff can continue working uninterrupted.

4. **Use existing administration patterns**  
   Manage enablement and behavior from the existing Communications Setup page.

5. **Fit current architecture**  
   Deliver a reliable v1 using Laravel, Blade, jQuery, polling, and settings-based feature gating.

### 2.3 Product Goals

- Show online staff in a quiet, ambient surface
- Let staff open a direct conversation from the online list
- Support persistent 1:1 message history
- Support unread counts and archive behavior
- Keep the feature configurable and easy to disable system-wide

---

## 3. Current State

### 3.1 Existing Platform Capabilities

The system already includes:
- Authenticated staff access through the main `web` guard
- A shared topbar, sidebar, and staff-facing layout
- Communications Setup backed by `SMSApiSetting` and `SettingsService`
- Existing feature flags for outbound communications such as SMS, WhatsApp, and email
- An LMS direct-messaging feature for student-to-instructor conversations

### 3.2 Existing Constraints

The current application also has the following relevant technical conditions:
- `SESSION_DRIVER=file`
- `BROADCAST_DRIVER=null`
- No existing staff presence-tracking table
- No existing staff-to-staff internal inbox
- No reusable chat launcher in the authenticated shell

### 3.3 Gaps

The current platform does not provide:
- A reliable list of staff who are active in the application right now
- A low-friction, internal staff messaging surface
- A configuration area for internal presence and messaging behavior
- A quiet awareness pattern for online staff availability

### 3.4 Important Distinctions

This feature is explicitly separate from:
- LMS student/instructor messaging
- Outbound SMS, WhatsApp, and email sending
- Communication history stored in the `messages` table for external channels

---

## 4. Problem Statement

Staff members currently lack a simple internal way to notice which colleagues are active and contact them while they are working in the system. Existing options are not a good fit:

- LMS messaging is designed for students and instructors, not general staff collaboration
- SMS and WhatsApp are external channels, not internal threaded conversations
- Email is too heavy for many quick internal exchanges
- There is no online-user signal that helps staff decide whom to contact now

At the same time, any visibility feature that is too noisy will be ignored or resented. The solution must therefore make online status visible without becoming a distraction.

---

## 5. User Personas & User Stories

### 5.1 User Personas

#### Persona 1: Teacher
- **Role:** Classroom teacher
- **Primary need:** Quickly contact another teacher or HOD while preparing lessons, marking, or reviewing records
- **Constraint:** Cannot be interrupted constantly by popups or live chat behavior

#### Persona 2: Administrative Officer
- **Role:** Office-based operational staff user
- **Primary need:** Reach active colleagues for coordination on records, schedules, and follow-ups
- **Constraint:** Needs fast access from any authenticated page

#### Persona 3: Head of Department
- **Role:** Supervisory or academic lead
- **Primary need:** Quietly see if relevant staff are active before starting a discussion
- **Constraint:** Needs an efficient overview, not another busy dashboard widget

#### Persona 4: System Administrator
- **Role:** Setup and platform owner
- **Primary need:** Enable, disable, and tune the feature from Communications Setup
- **Constraint:** Wants one settings authority, not fragmented toggles across the app

### 5.2 User Stories

| ID | As a... | I want to... | So that... | Priority |
|----|---------|--------------|------------|----------|
| US-001 | Staff user | See a quiet indicator of online colleagues | I know who may be available without leaving my page | P0 |
| US-002 | Staff user | Ignore the online indicator completely | I can keep working without interruption | P0 |
| US-003 | Staff user | Click the launcher and see online staff | I can choose who to message when I need help | P0 |
| US-004 | Staff user | Start a message from the online list | I can contact someone immediately | P0 |
| US-005 | Staff user | Open my inbox and continue earlier conversations | I can manage ongoing discussions | P0 |
| US-006 | Staff user | See unread conversation counts | I know when someone responded | P0 |
| US-007 | Staff user | Archive a conversation | I can keep my inbox focused | P1 |
| US-008 | Admin | Disable internal messaging entirely | The feature can be switched off centrally | P0 |
| US-009 | Admin | Disable only the presence launcher | Staff can still use the inbox without ambient presence UI | P1 |
| US-010 | Admin | Adjust online timeout and polling interval | The feature can be tuned for performance and UX | P1 |

---

## 6. UX Principles and Interaction Model

### 6.1 UX Principles

1. **Ambient, not intrusive**  
   The feature must be visible enough to be useful, but quiet enough to ignore.

2. **Intentional interaction**  
   Staff should open the launcher only when they choose to do so.

3. **Familiar navigation**  
   The feature should live in the existing application shell and follow established patterns.

4. **Silent updates**  
   Presence counts and unread badges should update without attention-grabbing notifications.

5. **Clear separation of concerns**  
   The launcher is for awareness and quick action. The inbox is for message management.

### 6.2 Non-Disruptive Behavior Rules

The online presence surface must follow these mandatory behaviors:
- Launcher stays collapsed by default
- No modal
- No toast
- No auto-open
- No sound
- No focus stealing
- No blocking overlay
- User can ignore it completely or click it only when needed

### 6.3 Primary Surface: Topbar Launcher

The feature should appear in the authenticated staff topbar as a compact launcher that includes:
- An icon or pill-style trigger
- A current online count
- A subtle visual treatment consistent with the shell

Click behavior:
- Opens a dropdown panel anchored to the topbar trigger
- Shows online staff users
- Includes search
- Includes a quick start/resume message action
- Includes a link to the full inbox

Default behavior:
- Closed on page load
- Closed on unread changes
- Closed on online count changes
- Open only when the user clicks it

### 6.4 Secondary Surface: Full Inbox

The full inbox page is the deeper management view and should include:
- Active conversations list
- Archived conversations view
- Conversation unread indicators
- Latest message previews
- Conversation thread view
- Reply input

The inbox is not required for ambient awareness, but it is required for persistent message management.

### 6.5 Mobile and Smaller Viewports

- Launcher remains available in the topbar or responsive header area
- Dropdown content should be scrollable and compact
- Full inbox should remain usable on narrower screens with stacked sections where needed

---

## 7. Functional Specifications

### 7.1 Staff Presence Tracking

**Description:** The system should determine active staff presence using a heartbeat model rather than login state alone.

**Behavior:**
- Record one presence row per authenticated browser session
- Send heartbeat:
  - on page load
  - on window focus
  - every configured polling interval while the tab is visible
- Consider a user online if at least one session heartbeat is within the configured timeout window

**Business Rules:**
- Only active, non-deleted staff users can appear online
- Current user is excluded from the online list
- Multiple sessions for one user should still resolve to a single online user entry
- Abandoned sessions should disappear automatically after timeout

### 7.2 Topbar Launcher

**Description:** The launcher is the quiet entry point for presence awareness and quick messaging.

**Displayed Content:**
- Online count
- Top online staff list
- Search field
- Name, position, and department for each visible user
- Action to start or resume a direct conversation
- Link to full inbox

**Behavior:**
- Silent polling refreshes content and count
- No animated interruptive behavior
- No popover shown automatically

### 7.3 Start or Resume Conversation

**Description:** Clicking an online user should open the existing conversation if one exists, or create a new one if not.

**Functional Flow:**
1. User opens launcher
2. User clicks an online colleague
3. System checks for an existing normalized staff-pair conversation
4. If found, redirect to the conversation
5. If not found, create a new conversation and redirect to it

**Business Rules:**
- A user cannot message themselves
- There must be only one conversation per staff pair
- Conversation creation must be idempotent

### 7.4 Inbox Listing

**Description:** The inbox page lists conversations for the current staff user.

**Capabilities:**
- Show active conversations ordered by `last_message_at`
- Show archived conversations separately
- Show latest message preview
- Show unread state
- Show participant identity

### 7.5 Conversation View

**Description:** The conversation page shows a single 1:1 staff thread.

**Capabilities:**
- Show messages in chronological order
- Support plain-text reply
- Mark the conversation as read when opened
- Keep archive/unarchive actions available

**Out of Scope for V1:**
- Attachments
- Message edits
- Message deletion UI
- Reactions
- Typing indicators

### 7.6 Unread Count Behavior

The system should:
- Show unread conversation count in navigation
- Update unread count silently through polling
- Reset unread status when the user opens the conversation

### 7.7 Archive and Restore

Archive behavior must be participant-specific:
- User A can archive without affecting User B
- A conversation can be restored by the user who archived it

### 7.8 Feature Gating

The feature must respect Communications Setup configuration:
- If staff direct messaging is disabled, the feature is unavailable everywhere
- If only the launcher is disabled, inbox routes remain available
- Hidden features should be blocked both in UI and at controller/service level

---

## 8. Communications Setup Configuration

### 8.1 Configuration Goal

Communications Setup remains the single administrative source of truth for enabling and tuning this feature.

### 8.2 New Settings Keys

| Key | Type | Default | Purpose |
|-----|------|---------|---------|
| `features.staff_direct_messages_enabled` | boolean | `1` | Master switch for staff internal direct messaging |
| `features.staff_presence_launcher_enabled` | boolean | `1` | Controls visibility of the topbar online-staff launcher |
| `internal_messaging.online_window_minutes` | integer | `2` | Number of minutes a heartbeat remains valid for online presence |
| `internal_messaging.launcher_poll_seconds` | integer | `45` | Polling interval for launcher data and heartbeat refresh |

### 8.3 Configuration Surface

Add a new `Internal Messaging` tab to Communications Setup with these sections:

1. **Availability**
   - Enable Staff Direct Messaging
   - Enable Online Staff Launcher

2. **Presence Behavior**
   - Online Window (minutes)
   - Launcher Poll Interval (seconds)

3. **UX Guidance**
   - Static helper text clarifying that the launcher:
     - stays collapsed by default
     - updates silently
     - can be ignored by staff users

### 8.4 Configuration Rules

- If `features.staff_direct_messages_enabled = 0`
  - hide topbar launcher
  - hide inbox navigation
  - block messaging routes
  - stop unread badge updates

- If `features.staff_direct_messages_enabled = 1` and `features.staff_presence_launcher_enabled = 0`
  - hide the topbar launcher
  - keep inbox navigation and conversations available

- Changes should persist through the existing settings flow using `SettingsService` and refresh immediately after save.

---

## 9. Data Model and Interfaces

### 9.1 Data Model

#### `staff_direct_conversations`

| Field | Type | Notes |
|------|------|-------|
| id | bigint | Primary key |
| user_one_id | bigint | Normalized lower user id |
| user_two_id | bigint | Normalized higher user id |
| last_message_at | timestamp | Most recent message time |
| user_one_read_at | timestamp nullable | Read timestamp for user one |
| user_two_read_at | timestamp nullable | Read timestamp for user two |
| is_archived_by_user_one | boolean | Archive state |
| is_archived_by_user_two | boolean | Archive state |
| created_at | timestamp | Standard |
| updated_at | timestamp | Standard |

**Rules:**
- `user_one_id < user_two_id`
- unique constraint on `(user_one_id, user_two_id)`

#### `staff_direct_messages`

| Field | Type | Notes |
|------|------|-------|
| id | bigint | Primary key |
| conversation_id | bigint | FK to conversation |
| sender_id | bigint | FK to users |
| body | text | Plain-text message body |
| read_at | timestamp nullable | Optional message-level read timestamp |
| created_at | timestamp | Standard |
| updated_at | timestamp | Standard |
| deleted_at | timestamp nullable | Soft delete |

#### `staff_user_presence`

| Field | Type | Notes |
|------|------|-------|
| id | bigint | Primary key |
| session_id | string | Unique per browser session |
| user_id | bigint | FK to users |
| last_seen_at | timestamp | Most recent heartbeat |
| last_path | string nullable | Optional path snapshot |
| created_at | timestamp | Standard |
| updated_at | timestamp | Standard |

### 9.2 Services

Add service responsibilities such as:
- `StaffPresenceService`
  - heartbeat upsert
  - online users query
  - stale-presence interpretation
- `StaffMessagingService`
  - normalized staff-pair resolution
  - conversation lookup/create
  - unread count calculations
  - inbox queries
  - archive/unarchive behavior

### 9.3 Routes

Recommended route surface:

```text
GET    /staff/messages
GET    /staff/messages/unread-count
GET    /staff/messages/launcher
POST   /staff/messages/heartbeat
POST   /staff/messages/conversations
GET    /staff/messages/{conversation}
POST   /staff/messages/{conversation}/reply
POST   /staff/messages/{conversation}/archive
POST   /staff/messages/{conversation}/unarchive
```

### 9.4 Launcher Payload Contract

The launcher endpoint should return a compact JSON payload containing:
- launcher enabled state
- online count
- unread conversation count
- top online users
- optional search-filtered results when query is present

### 9.5 Navigation and Shell Integration

The feature integrates with:
- topbar launcher for presence awareness
- sidebar or navigation link for full inbox
- shared authenticated staff layout for heartbeat and polling scripts

---

## 10. Security and Authorization

### 10.1 Access Control

Only authenticated staff users on the main `web` guard may use this feature.

### 10.2 Conversation Authorization

The system must enforce:
- Only conversation participants may view a conversation
- Only conversation participants may reply
- Users may not create or access conversations involving inactive or deleted staff
- Users may not message themselves

### 10.3 Data Exposure

The launcher should expose only safe profile data:
- full name
- avatar if available
- position
- department
- online status or last seen state

The launcher must not expose:
- personal contact information
- hidden account fields
- unrelated profile metadata

### 10.4 Defense in Depth

Feature enforcement should exist at multiple levels:
- Blade and layout visibility checks
- Route/controller guards
- Final service-level checks before state changes

---

## 11. Performance and Operational Requirements

### 11.1 Performance Expectations

- Launcher polling must be lightweight
- Presence queries must use indexed fields
- Online-user aggregation must remain efficient with multiple sessions per user
- Unread badge updates must not require full page reloads

### 11.2 Operational Behavior

- The feature must work without websockets
- Polling interval must be configurable
- Heartbeat writes should be cheap and idempotent
- Presence should degrade gracefully if polling temporarily fails

### 11.3 Logging and Monitoring

The system should log:
- unauthorized conversation access attempts
- unexpected heartbeat failures
- settings-gating failures where blocked features are requested directly

### 11.4 Reliability Requirements

- No duplicate conversations for the same staff pair
- Conversation ordering based on latest activity must remain correct
- Settings changes should apply after settings cache refresh

---

## 12. Implementation Roadmap by Phase

### Phase 1: Settings Model and Presence Storage

**Scope**
- Add settings keys and seed values
- Add Communications Setup tab and controls
- Create `staff_user_presence`
- Implement heartbeat endpoint and presence service

**Acceptance Intent**
- Admin can enable or disable the feature from Communications Setup
- Heartbeats are stored and online users can be derived from presence rows

### Phase 2: Quiet Topbar Launcher and Silent Polling

**Scope**
- Add topbar launcher UI
- Add launcher payload endpoint
- Add polling and heartbeat JS to shared staff layout
- Ensure launcher remains collapsed and silent

**Acceptance Intent**
- Staff can see online count in the topbar
- Launcher never auto-opens and does not interrupt users

### Phase 3: Inbox and Conversation Views

**Scope**
- Create staff conversation and message tables
- Build full inbox page
- Build conversation page
- Implement create-or-open conversation flow from launcher
- Add unread counts and archive/unarchive behavior

**Acceptance Intent**
- Staff can start a conversation from the launcher and continue it in the inbox
- Unread and archive behavior works correctly

### Phase 4: Hardening, Optimization, and Regression Protection

**Scope**
- Optimize queries and indexes
- Add error states, empty states, and mobile-fit behavior
- Add authorization hardening
- Verify no regressions in LMS messaging and Communications Setup

**Acceptance Intent**
- Feature performs acceptably in normal usage
- Existing messaging and settings flows are unaffected

### Phase 5: Optional V2 Backlog

**Potential Enhancements**
- Realtime updates through websockets
- Attachments
- Typing indicators
- Last seen labels inside threads
- Department/team filters
- Pinned or favorite contacts

---

## 13. Testing Strategy

### 13.1 Document-Level Validation

The delivered implementation should be validated against this PRD to confirm:
- it remains staff-only
- it remains distinct from LMS messaging
- it remains distinct from outbound SMS and WhatsApp
- it includes Communications Setup controls
- it preserves the quiet and ignorable launcher behavior

### 13.2 Functional Test Areas

1. **Settings**
   - direct messaging can be enabled and disabled
   - launcher can be enabled and disabled independently
   - timeout and polling values persist correctly

2. **Presence**
   - heartbeat creates and updates presence rows
   - multiple sessions resolve to one online user
   - users fall offline after the configured timeout

3. **Messaging**
   - same staff pair reuses one conversation
   - unread counts update correctly
   - opening a conversation marks it read
   - archive/unarchive is participant-specific

4. **Authorization**
   - non-participants receive `403`
   - self-messaging is blocked
   - inactive or deleted users cannot be targeted

5. **UX Behavior**
   - launcher starts collapsed
   - launcher does not show toast or modal
   - launcher does not auto-open on updates
   - users can ignore the feature while continuing normal work

6. **Regression Coverage**
   - LMS student/instructor messaging remains unchanged
   - outbound communications remain unchanged
   - Communications Setup still behaves correctly for existing channels

---

## 14. Assumptions and Out of Scope

### 14.1 Assumptions

- The feature applies only to authenticated staff users on the main portal
- Polling is acceptable for v1
- The existing settings infrastructure remains the source of truth
- The main topbar is the preferred place for ambient presence awareness
- Text-only messaging is sufficient for first release

### 14.2 Out of Scope for V1

- Student, sponsor, or parent participation
- LMS message model reuse for staff messaging
- SMS, WhatsApp, or email fallback from the internal chat UI
- Group conversations
- Attachments
- Message reactions
- Typing indicators
- Auto-popup notifications
- Sound alerts
- Websocket infrastructure

---

## Appendix: Product Positioning Statement

This feature should feel like a quiet internal utility rather than a live-chat system. Staff should always be able to ignore the online-user display and keep working. The launcher exists to provide awareness and optional action, while the inbox exists to support deliberate communication when the user chooses to engage.
