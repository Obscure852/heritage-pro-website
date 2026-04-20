# Learning Management System (LMS) - Product Requirements Document

**Version:** 1.0
**Date:** January 2026
**Author:** Product Team
**Status:** Draft

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Product Vision & Goals](#2-product-vision--goals)
3. [User Personas & Stories](#3-user-personas--stories)
4. [Functional Specifications](#4-functional-specifications)
5. [Technical Architecture](#5-technical-architecture)
6. [User Interface Specifications](#6-user-interface-specifications)
7. [Security Requirements](#7-security-requirements)
8. [Performance Requirements](#8-performance-requirements)
9. [Implementation Roadmap](#9-implementation-roadmap)
10. [Testing Strategy](#10-testing-strategy)
11. [Appendices](#11-appendices)

---

## 1. Executive Summary

### 1.1 Purpose

This document defines the requirements for a state-of-the-art Learning Management System (LMS) to be integrated into the Heritage Junior Secondary School Management System. The LMS will enable teachers to deliver digital learning content and track student progress while seamlessly integrating with the existing assessment infrastructure.

### 1.2 Scope

The LMS module will provide:
- **SCORM 1.2 & 2004 Compliance** - Support for industry-standard e-learning content packages
- **LTI 1.3 + Advantage** - Integration with external educational tools
- **Full Multimedia Support** - Videos, documents, audio, interactive content
- **Native Assessment Tools** - Quizzes, assignments, rubrics
- **Progress Tracking** - Comprehensive learning analytics
- **Assessment Integration** - Automatic grade sync with existing report cards
- **Gamification** - Badges, points, leaderboards to increase engagement

### 1.3 Key Success Metrics

| Metric | Target |
|--------|--------|
| Course Completion Rate | > 70% |
| Weekly Active Users | > 80% of enrolled students |
| SCORM Package Compatibility | 100% |
| Grade Sync Accuracy | 100% |
| System Availability | 99.9% uptime |

---

## 2. Product Vision & Goals

### 2.1 Vision Statement

Create an engaging, accessible digital learning environment that empowers teachers to deliver rich educational content while providing students with personalized learning experiences that integrate seamlessly with the school's academic assessment system.

### 2.2 Strategic Goals

1. **Enhance Teaching Capabilities** - Enable teachers to create and deliver multimedia courses without technical expertise
2. **Improve Student Engagement** - Use gamification and adaptive learning to increase participation
3. **Standardize E-Learning** - Support SCORM and LTI standards for content interoperability
4. **Integrate Assessments** - Automatically sync LMS grades with the existing academic system
5. **Enable Analytics** - Provide actionable insights into student learning progress

### 2.3 Out of Scope

- Content authoring tools (users will upload pre-created content)
- Video conferencing hosting (will integrate with Zoom/Teams)
- Payment/e-commerce features
- Public course marketplace

---

## 3. User Personas & Stories

### 3.1 User Personas

#### Persona 1: Teacher (Mrs. Mosweu)
- **Role:** Junior Secondary Science Teacher
- **Technical Skill:** Moderate
- **Goals:** Supplement classroom teaching with online resources, track student understanding
- **Pain Points:** Limited time, needs easy content upload, wants automatic grading

#### Persona 2: Student (Thabo)
- **Role:** Form 2 Student
- **Technical Skill:** High (digital native)
- **Goals:** Access learning materials anytime, track own progress, earn achievements
- **Pain Points:** Slow internet at home, needs mobile access, prefers video content

#### Persona 3: HOD/Administrator (Mr. Kgosi)
- **Role:** Head of Science Department
- **Technical Skill:** Moderate
- **Goals:** Monitor teacher course quality, view department analytics, ensure curriculum coverage
- **Pain Points:** No visibility into digital learning activities, manual grade entry

#### Persona 4: Parent/Sponsor (Mrs. Tlhong)
- **Role:** Parent of Form 1 student
- **Technical Skill:** Basic
- **Goals:** See child's learning progress, understand what's being taught
- **Pain Points:** Limited communication about digital learning, unclear on child's performance

### 3.2 User Stories

#### Course Management
| ID | As a... | I want to... | So that... | Priority |
|----|---------|--------------|------------|----------|
| US-001 | Teacher | Create a new course with title, description, and thumbnail | I can organize my digital content | P0 |
| US-002 | Teacher | Add modules to organize content into logical sections | Students can progress through material systematically | P0 |
| US-003 | Teacher | Upload SCORM packages | I can use professional e-learning content | P1 |
| US-004 | Teacher | Embed YouTube videos with tracking | Students can watch educational videos | P0 |
| US-005 | Teacher | Set course availability dates | Content is only accessible during the term | P1 |
| US-006 | Teacher | Duplicate an existing course | I can reuse content for new terms | P1 |
| US-007 | Admin | View all courses across the school | I can monitor content quality | P1 |

#### Content Consumption
| ID | As a... | I want to... | So that... | Priority |
|----|---------|--------------|------------|----------|
| US-010 | Student | Browse available courses | I can find relevant learning materials | P0 |
| US-011 | Student | Enroll in a course | I can access the content | P0 |
| US-012 | Student | Resume from where I left off | I don't lose my progress | P0 |
| US-013 | Student | View my progress percentage | I know how much I've completed | P0 |
| US-014 | Student | Complete SCORM activities | I can learn from interactive content | P1 |
| US-015 | Student | Watch videos with my progress saved | I can pause and continue later | P0 |
| US-016 | Student | Download content for offline viewing | I can study without internet | P2 |

#### Assessment
| ID | As a... | I want to... | So that... | Priority |
|----|---------|--------------|------------|----------|
| US-020 | Teacher | Create quizzes with multiple question types | I can assess student understanding | P0 |
| US-021 | Teacher | Set time limits on quizzes | Students complete assessments within bounds | P0 |
| US-022 | Teacher | Create assignments with file submissions | Students can submit work digitally | P1 |
| US-023 | Teacher | Grade assignments using rubrics | Grading is consistent and transparent | P1 |
| US-024 | Student | Take quizzes and see my score | I know how well I understood the material | P0 |
| US-025 | Student | Submit assignments before deadline | I can complete my coursework | P1 |
| US-026 | Student | View feedback on my submissions | I can learn from my mistakes | P1 |

#### Grade Integration
| ID | As a... | I want to... | So that... | Priority |
|----|---------|--------------|------------|----------|
| US-030 | Teacher | Map LMS course grades to existing assessments | Grades appear in student records | P1 |
| US-031 | Teacher | Configure automatic grade sync | I don't have to manually transfer grades | P1 |
| US-032 | Admin | See LMS grades in report cards | Digital learning is reflected in official records | P1 |

#### Gamification
| ID | As a... | I want to... | So that... | Priority |
|----|---------|--------------|------------|----------|
| US-040 | Student | Earn badges for achievements | I feel rewarded for my progress | P2 |
| US-041 | Student | See my position on leaderboards | I'm motivated by healthy competition | P2 |
| US-042 | Student | Receive a certificate on completion | I have proof of my achievement | P2 |

---

## 4. Functional Specifications

### 4.1 Course Management Module

#### 4.1.1 Course Creation

**Description:** Teachers can create courses to organize their digital learning content.

**Functional Flow:**
```
1. Teacher clicks "Create Course" button
2. System displays course creation form
3. Teacher enters:
   - Course Title (required, max 255 chars)
   - Description (optional, rich text)
   - Thumbnail image (optional, max 2MB)
   - Grade level (dropdown from existing grades)
   - Subject (dropdown, filtered by grade)
   - Term (current term pre-selected)
   - Passing score (default 60%)
   - Allow self-enrollment (checkbox)
   - Sequential content (checkbox, default true)
4. Teacher clicks "Create Course"
5. System creates course in DRAFT status
6. System redirects to course editor
```

**Business Rules:**
- Course slug auto-generated from title (unique)
- Only teachers assigned to the grade/subject can create courses
- Courses created in current term by default
- Maximum 50 courses per teacher

**Data Model:**
```
lms_courses
├── id (bigint, PK)
├── title (varchar 255, required)
├── slug (varchar 255, unique)
├── description (text, nullable)
├── thumbnail_path (varchar 255, nullable)
├── grade_id (FK -> grades.id, required)
├── grade_subject_id (FK -> grade_subject.id, nullable)
├── term_id (FK -> terms.id, required)
├── instructor_id (FK -> users.id, required)
├── status (enum: draft, published, archived)
├── visibility (enum: public, enrolled, private)
├── start_date (date, nullable)
├── end_date (date, nullable)
├── estimated_duration_minutes (int, default 0)
├── passing_score (decimal 5,2, default 60.00)
├── allow_self_enrollment (boolean, default false)
├── certificate_enabled (boolean, default false)
├── max_attempts (int, nullable)
├── sequential_content (boolean, default true)
├── adaptive_learning_enabled (boolean, default false)
├── created_at, updated_at, deleted_at (soft delete)
```

#### 4.1.2 Course Publishing

**Description:** Courses must be published before students can access them.

**Functional Flow:**
```
1. Teacher opens course in editor
2. System validates course has:
   - At least one module
   - At least one content item
   - Start date (if not immediate)
3. Teacher clicks "Publish Course"
4. System changes status to PUBLISHED
5. System sends notification to enrolled students (if any)
6. Course appears in student catalog
```

**Business Rules:**
- Cannot publish empty courses
- Unpublishing hides from catalog but preserves student progress
- Archived courses are read-only

#### 4.1.3 Module Management

**Description:** Modules organize content within a course into logical sections.

**Functional Flow:**
```
1. Teacher opens course editor
2. Teacher clicks "Add Module"
3. System displays module form:
   - Title (required)
   - Description (optional)
   - Unlock date (optional, for drip content)
   - Prerequisites (optional, select other modules)
4. Teacher saves module
5. Module appears in course structure
6. Teacher can drag-drop to reorder modules
```

**Data Model:**
```
lms_modules
├── id (bigint, PK)
├── course_id (FK -> lms_courses.id, required)
├── title (varchar 255, required)
├── description (text, nullable)
├── sequence (int, required)
├── unlock_date (datetime, nullable)
├── prerequisites (json, nullable) -- [module_id, ...]
├── is_locked (boolean, default false)
├── created_at, updated_at, deleted_at
```

**Business Rules:**
- Modules numbered automatically by sequence
- Deleting a module soft-deletes all content within
- Prerequisites only allow previous modules

---

### 4.2 Content Types Module

#### 4.2.1 Content Item Framework

**Description:** A polymorphic content system supporting multiple content types.

**Data Model:**
```
lms_content_items
├── id (bigint, PK)
├── module_id (FK -> lms_modules.id, required)
├── title (varchar 255, required)
├── description (text, nullable)
├── content_type (enum: see below)
├── content_data (json) -- type-specific configuration
├── sequence (int, required)
├── duration_minutes (int, nullable)
├── is_mandatory (boolean, default true)
├── passing_score (decimal 5,2, nullable)
├── max_attempts (int, nullable)
├── unlock_conditions (json, nullable)
├── available_from (datetime, nullable)
├── available_until (datetime, nullable)
├── created_at, updated_at, deleted_at
```

**Content Types:**
| Type | Description | Trackable |
|------|-------------|-----------|
| `text` | Rich HTML content | View only |
| `document` | PDF, DOCX, PPTX | View + Download |
| `video_youtube` | Embedded YouTube | Watch progress |
| `video_upload` | Uploaded MP4/WebM | Watch progress |
| `audio` | MP3, WAV files | Listen progress |
| `image` | Image galleries | View only |
| `scorm12` | SCORM 1.2 package | Full SCORM tracking |
| `scorm2004` | SCORM 2004 package | Full SCORM tracking |
| `h5p` | H5P interactive | Score + interactions |
| `quiz` | Native quiz | Score + responses |
| `assignment` | Submission task | Submission + grade |
| `live_session` | Scheduled meeting | Attendance |
| `external_url` | External link | Click tracking |
| `lti_tool` | LTI tool launch | LTI grade passback |

#### 4.2.2 YouTube Video Content

**Description:** Embed YouTube videos with automatic progress tracking.

**Functional Flow:**
```
1. Teacher selects "Add Content" > "YouTube Video"
2. Teacher pastes YouTube URL
3. System extracts video ID and validates
4. System fetches video metadata via YouTube API:
   - Title
   - Duration
   - Thumbnail
5. Teacher can customize:
   - Display title
   - Completion threshold (default 90%)
   - Required watch percentage
6. Content saved with content_data:
   {
     "youtube_id": "dQw4w9WgXcQ",
     "original_title": "Video Title",
     "duration_seconds": 212,
     "thumbnail_url": "https://...",
     "completion_threshold": 90,
     "allow_seeking": true
   }
```

**Student Playback Flow:**
```
1. Student opens video content
2. System checks enrollment and access rights
3. System loads embedded YouTube player
4. JavaScript tracker monitors:
   - Play/pause events
   - Current position (every 10 seconds)
   - Seeking events
   - Playback rate changes
5. Progress saved to lms_video_progress:
   - current_time: last position
   - furthest_time: maximum reached
   - watch_percentage: furthest/duration * 100
   - completed: watch_percentage >= threshold
6. When completed, system marks content complete
```

**Data Model:**
```
lms_videos
├── id (bigint, PK)
├── content_item_id (FK -> lms_content_items.id)
├── source_type (enum: youtube, vimeo, upload, stream)
├── source_id (varchar 255) -- YouTube video ID
├── duration_seconds (int)
├── thumbnail_path (varchar 255)
├── captions (json, nullable) -- [{lang, vtt_url}]
├── chapters (json, nullable) -- [{time, title}]
├── completion_threshold (int, default 90)
├── created_at, updated_at

lms_video_progress
├── id (bigint, PK)
├── video_id (FK -> lms_videos.id)
├── student_id (FK -> students.id)
├── current_time (int, default 0)
├── furthest_time (int, default 0)
├── total_watch_time (int, default 0)
├── watch_percentage (decimal 5,2, default 0)
├── completed (boolean, default false)
├── playback_rate (decimal 3,2, default 1.00)
├── events (json) -- [{event, time, timestamp}]
├── last_watched_at (datetime)
├── created_at, updated_at
├── UNIQUE (video_id, student_id)
```

**Business Rules:**
- YouTube API key required for metadata
- Videos marked complete when watch_percentage >= completion_threshold
- Seeking forward doesn't increase furthest_time (anti-cheat)
- Playback at >2x speed counts at reduced rate

#### 4.2.3 Document Viewer

**Description:** View PDF, DOCX, and PPTX files in-browser.

**Functional Flow:**
```
1. Teacher uploads document (max 50MB)
2. System validates file type (PDF, DOCX, PPTX, XLSX)
3. System processes document:
   - Extract page count
   - Generate preview images (first 3 pages)
   - Extract text content (for search)
4. Document stored with metadata

Student View Flow:
1. Student opens document content
2. System renders document using:
   - PDF: PDF.js viewer
   - DOCX: docx-preview library
   - PPTX: Custom slide viewer
3. Student can:
   - Navigate pages
   - Zoom in/out
   - Download original (if allowed)
4. View tracked (time spent)
```

**Data Model:**
```
lms_documents
├── id (bigint, PK)
├── content_item_id (FK -> lms_content_items.id)
├── file_path (varchar 255)
├── original_filename (varchar 255)
├── mime_type (varchar 100)
├── file_size_bytes (bigint)
├── document_type (enum: pdf, docx, pptx, xlsx)
├── page_count (int, nullable)
├── preview_images (json, nullable)
├── text_content (longtext, nullable)
├── allow_download (boolean, default true)
├── created_at, updated_at
```

---

### 4.3 SCORM Implementation Module

#### 4.3.1 SCORM Overview

**What is SCORM?**
SCORM (Sharable Content Object Reference Model) is an e-learning standard that allows content packages to communicate with the LMS. The LMS provides a Run-Time Environment (RTE) that the content uses to:
- Report completion status
- Save/retrieve scores
- Store suspend data (bookmarks)
- Track learner interactions

#### 4.3.2 SCORM Package Upload

**Functional Flow:**
```
1. Teacher selects "Add Content" > "SCORM Package"
2. Teacher uploads .zip file (max 500MB)
3. System validates package:
   a. Check for imsmanifest.xml at root
   b. Parse manifest to extract:
      - Package identifier
      - Version (1.2 or 2004)
      - Organizations (content structure)
      - Resources (files and entry points)
      - Sequencing rules (2004 only)
4. System extracts package to storage:
   /storage/scorm/{package_id}/
5. System creates database records
6. Content item created with launch URL
```

**Manifest Parsing:**
```xml
<!-- Example imsmanifest.xml structure -->
<manifest identifier="course_001" version="1.0">
  <metadata>
    <schema>ADL SCORM</schema>
    <schemaversion>2004 4th Edition</schemaversion>
  </metadata>
  <organizations default="org_1">
    <organization identifier="org_1">
      <title>Course Title</title>
      <item identifier="item_1" identifierref="res_1">
        <title>Lesson 1</title>
      </item>
    </organization>
  </organizations>
  <resources>
    <resource identifier="res_1" type="webcontent"
              adlcp:scormType="sco" href="index.html">
      <file href="index.html"/>
      <file href="scripts/api.js"/>
    </resource>
  </resources>
</manifest>
```

**Data Model:**
```
lms_scorm_packages
├── id (bigint, PK)
├── content_item_id (FK -> lms_content_items.id)
├── version (enum: 1.2, 2004_2nd, 2004_3rd, 2004_4th)
├── manifest_identifier (varchar 255)
├── manifest_path (varchar 255)
├── entry_point (varchar 255) -- index.html path
├── package_path (varchar 255) -- storage directory
├── title (varchar 255)
├── metadata (json) -- parsed manifest data
├── organizations (json) -- content structure
├── resources (json) -- file catalog
├── sequencing_rules (json, nullable) -- SCORM 2004
├── total_scos (int, default 1)
├── created_at, updated_at
```

#### 4.3.3 SCORM 1.2 Runtime Environment

**Description:** JavaScript API that SCORM content calls to communicate with the LMS.

**API Methods (Window.API):**
```javascript
// SCORM 1.2 API Object
window.API = {
    // Initialize communication session
    LMSInitialize: function(parameter) {
        // parameter is always empty string ""
        // Returns "true" on success, "false" on failure
        // Creates or resumes attempt in database
    },

    // Terminate communication session
    LMSFinish: function(parameter) {
        // Commits any pending data
        // Marks session as terminated
        // Returns "true" or "false"
    },

    // Get value from data model
    LMSGetValue: function(element) {
        // element: data model element name
        // Returns value as string or empty string
        // Examples:
        //   "cmi.core.student_name" -> "Thabo, Mosweu"
        //   "cmi.core.score.raw" -> "85"
    },

    // Set value in data model
    LMSSetValue: function(element, value) {
        // Validates element and value
        // Stores in memory until commit
        // Returns "true" or "false"
    },

    // Persist data to server
    LMSCommit: function(parameter) {
        // Sends all pending data to server via AJAX
        // Returns "true" or "false"
    },

    // Get last error code
    LMSGetLastError: function() {
        // Returns error code as string
        // "0" = no error
    },

    // Get error description
    LMSGetErrorString: function(errorCode) {
        // Returns human-readable error description
    },

    // Get diagnostic info
    LMSGetDiagnostic: function(errorCode) {
        // Returns detailed diagnostic information
    }
};
```

**SCORM 1.2 Data Model Elements:**
```
cmi.core.student_id          // Read-only: Student's ID
cmi.core.student_name        // Read-only: "Last, First"
cmi.core.lesson_location     // Read/Write: Bookmark location
cmi.core.credit              // Read-only: "credit" or "no-credit"
cmi.core.lesson_status       // Read/Write: passed|completed|failed|incomplete|browsed|not attempted
cmi.core.entry               // Read-only: ab-initio|resume|""
cmi.core.score.raw           // Write: 0-100
cmi.core.score.min           // Write: minimum possible score
cmi.core.score.max           // Write: maximum possible score
cmi.core.total_time          // Read-only: accumulated time
cmi.core.session_time        // Write: current session time (HH:MM:SS.SS)
cmi.core.exit                // Write: timeout|suspend|logout|""
cmi.suspend_data             // Read/Write: max 4096 chars, any data
cmi.launch_data              // Read-only: data from manifest
cmi.comments                 // Read/Write: learner comments
cmi.objectives.n.id          // Read/Write: objective identifier
cmi.objectives.n.score.raw   // Read/Write: objective score
cmi.objectives.n.status      // Read/Write: objective status
cmi.interactions.n.id        // Write-only: interaction identifier
cmi.interactions.n.type      // Write-only: true-false|choice|fill-in|matching|performance|likert|sequencing|numeric
cmi.interactions.n.student_response  // Write-only: learner response
cmi.interactions.n.correct_responses // Write-only: correct answer pattern
cmi.interactions.n.result    // Write-only: correct|wrong|unanticipated|neutral|{decimal}
cmi.interactions.n.latency   // Write-only: time to respond
```

**Runtime Flow:**
```
1. Student launches SCORM content
2. LMS creates iframe with content entry point
3. Content loads and calls LMSInitialize("")
4. LMS API:
   - Creates new attempt or resumes existing
   - Loads suspend_data if resuming
   - Sets entry = "ab-initio" or "resume"
   - Returns "true"
5. Content calls LMSGetValue() for initial data
6. Student interacts with content
7. Content calls LMSSetValue() to report progress:
   - Updates lesson_status
   - Sets score values
   - Records interactions
8. Content periodically calls LMSCommit()
9. LMS API sends data to server via AJAX POST
10. When student exits, content calls LMSFinish("")
11. LMS finalizes attempt and calculates results
```

**Server Endpoints:**
```
POST /api/lms/scorm/runtime/initialize
Request: { package_id, student_id }
Response: {
    attempt_id,
    entry: "ab-initio"|"resume",
    suspend_data: "...",
    data_model: { cmi.core.student_name: "...", ... }
}

POST /api/lms/scorm/runtime/commit
Request: {
    attempt_id,
    data: {
        "cmi.core.lesson_status": "completed",
        "cmi.core.score.raw": "85",
        ...
    }
}
Response: { success: true }

POST /api/lms/scorm/runtime/terminate
Request: { attempt_id, session_time: "00:15:30" }
Response: { success: true, completion_status: "completed" }
```

#### 4.3.4 SCORM 2004 Runtime Environment

**API Methods (Window.API_1484_11):**
```javascript
window.API_1484_11 = {
    Initialize: function(parameter) { ... },
    Terminate: function(parameter) { ... },
    GetValue: function(element) { ... },
    SetValue: function(element, value) { ... },
    Commit: function(parameter) { ... },
    GetLastError: function() { ... },
    GetErrorString: function(errorCode) { ... },
    GetDiagnostic: function(errorCode) { ... }
};
```

**SCORM 2004 Additional Data Model Elements:**
```
cmi.completion_status        // completed|incomplete|not attempted|unknown
cmi.success_status           // passed|failed|unknown
cmi.score.scaled             // -1.0 to 1.0 (normalized score)
cmi.score.raw                // actual score
cmi.score.min                // minimum possible
cmi.score.max                // maximum possible
cmi.progress_measure         // 0.0 to 1.0 (completion progress)
cmi.location                 // bookmark (replaces lesson_location)
cmi.total_time               // ISO 8601 duration (PT1H30M)
cmi.session_time             // ISO 8601 duration
cmi.exit                     // timeout|suspend|logout|normal|""
cmi.objectives.n.success_status
cmi.objectives.n.completion_status
cmi.objectives.n.progress_measure
cmi.interactions.n.objectives.m.id  // link to objectives
```

#### 4.3.5 SCORM 2004 Sequencing and Navigation

**Description:** SCORM 2004 defines rules for content sequencing (order of activities).

**Sequencing Rules:**
```
Pre-Condition Rules: Check before entering activity
- If objective "obj1" satisfied, skip activity
- If attempt limit exceeded, disable activity

Post-Condition Rules: Execute after exiting activity
- If passed, continue to next
- If failed, retry

Exit Condition Rules: Determine when to exit
- Exit if time limit exceeded
```

**Navigation Requests:**
```
{continue}      - Go to next activity
{previous}      - Go to previous activity
{choice}        - Go to specific activity
{exit}          - Exit current activity
{exitAll}       - Exit entire course
{abandon}       - Abandon current activity
{abandonAll}    - Abandon entire course
{suspendAll}    - Suspend and exit
```

**Rollup Rules:**
```
Completion Rollup: Parent completed when all children completed
Score Rollup: Parent score = weighted average of children
Success Rollup: Parent passed when all required children passed
```

**Data Model:**
```
lms_scorm_attempts
├── id (bigint, PK)
├── scorm_package_id (FK)
├── student_id (FK)
├── attempt_number (int)
├── started_at (datetime)
├── completed_at (datetime, nullable)
├── total_time (varchar 50) -- ISO 8601 duration
├── session_time (varchar 50)
├── completion_status (enum: unknown, incomplete, completed)
├── success_status (enum: unknown, passed, failed)
├── score_raw (decimal 10,4, nullable)
├── score_min (decimal 10,4, nullable)
├── score_max (decimal 10,4, nullable)
├── score_scaled (decimal 5,4, nullable)
├── progress_measure (decimal 5,4, nullable)
├── suspend_data (longtext, nullable)
├── location (text, nullable)
├── exit_type (enum: timeout, suspend, logout, normal, null)
├── current_activity_id (varchar 255, nullable)
├── created_at, updated_at
├── INDEX (scorm_package_id, student_id)

lms_scorm_interactions
├── id (bigint, PK)
├── attempt_id (FK -> lms_scorm_attempts.id)
├── interaction_id (varchar 255)
├── type (enum: true-false, choice, fill-in, long-fill-in, matching, performance, sequencing, likert, numeric, other)
├── description (text, nullable)
├── weighting (decimal 5,2, nullable)
├── learner_response (text, nullable)
├── correct_responses (json, nullable)
├── result (varchar 50)
├── latency (varchar 50, nullable)
├── timestamp (datetime, nullable)
├── objectives (json, nullable)
├── created_at
├── INDEX (attempt_id, interaction_id)

lms_scorm_objectives
├── id (bigint, PK)
├── attempt_id (FK -> lms_scorm_attempts.id)
├── objective_id (varchar 255)
├── score_raw (decimal 10,4, nullable)
├── score_min (decimal 10,4, nullable)
├── score_max (decimal 10,4, nullable)
├── score_scaled (decimal 5,4, nullable)
├── success_status (enum: unknown, passed, failed)
├── completion_status (enum: unknown, incomplete, completed)
├── progress_measure (decimal 5,4, nullable)
├── description (text, nullable)
├── created_at, updated_at
├── UNIQUE (attempt_id, objective_id)
```

---

### 4.4 LTI Integration Module

#### 4.4.1 LTI Overview

**What is LTI?**
Learning Tools Interoperability (LTI) is an IMS Global standard that allows external tools (publishers, apps) to integrate with the LMS. The system can act as:
- **Platform (Consumer):** Launch external tools for students
- **Tool Provider:** Allow other systems to launch content from this LMS

#### 4.4.2 LTI 1.3 Launch Flow (Platform Role)

**Registration Flow:**
```
1. Admin navigates to "LTI Tools" settings
2. Admin enters tool registration URL or manual config:
   - Tool name
   - Client ID (from tool provider)
   - OIDC Login URL
   - OIDC Redirect URLs
   - JWKS URL (or public key)
   - Custom parameters
3. System generates platform credentials:
   - Platform ID (issuer)
   - Client ID
   - Deployment ID
   - JWKS URL
   - Token endpoint
   - Authorization endpoint
4. Admin shares platform credentials with tool provider
```

**Launch Flow (OIDC):**
```
1. Student clicks LTI content item
2. Platform redirects to Tool's OIDC Login URL:
   GET {tool_login_url}?
     iss={platform_id}
     &login_hint={encrypted_user_info}
     &target_link_uri={launch_url}
     &lti_message_hint={context_info}
     &client_id={client_id}

3. Tool validates parameters
4. Tool redirects back to Platform authorization:
   GET {platform_auth_url}?
     response_type=id_token
     &redirect_uri={tool_redirect}
     &scope=openid
     &state={random}
     &nonce={random}
     &login_hint={login_hint}
     &lti_message_hint={message_hint}
     &client_id={client_id}
     &response_mode=form_post

5. Platform validates request
6. Platform generates signed JWT (id_token):
   {
     "iss": "https://school.edu",
     "sub": "student_123",
     "aud": ["tool_client_id"],
     "exp": 1234567890,
     "iat": 1234567890,
     "nonce": "random_nonce",
     "https://purl.imsglobal.org/spec/lti/claim/message_type": "LtiResourceLinkRequest",
     "https://purl.imsglobal.org/spec/lti/claim/version": "1.3.0",
     "https://purl.imsglobal.org/spec/lti/claim/deployment_id": "deployment_1",
     "https://purl.imsglobal.org/spec/lti/claim/target_link_uri": "https://tool.com/launch",
     "https://purl.imsglobal.org/spec/lti/claim/resource_link": {
       "id": "resource_123",
       "title": "Lesson 1"
     },
     "https://purl.imsglobal.org/spec/lti/claim/context": {
       "id": "course_456",
       "title": "Mathematics F2",
       "type": ["CourseSection"]
     },
     "https://purl.imsglobal.org/spec/lti/claim/roles": [
       "http://purl.imsglobal.org/vocab/lis/v2/membership#Learner"
     ],
     "name": "Thabo Mosweu",
     "email": "thabo@school.edu"
   }

7. Platform POSTs to Tool redirect:
   POST {tool_redirect}
     id_token={signed_jwt}
     &state={state}

8. Tool validates JWT signature via Platform JWKS
9. Tool displays content to student
```

#### 4.4.3 LTI Advantage - Assignment and Grade Services (AGS)

**Description:** Allows tools to send grades back to the LMS.

**Line Item Management:**
```
GET /api/lms/lti/ags/{context}/lineitems
Authorization: Bearer {access_token}
Accept: application/vnd.ims.lis.v2.lineitemcontainer+json

Response:
[
  {
    "id": "https://school.edu/lti/lineitems/123",
    "scoreMaximum": 100,
    "label": "Quiz 1",
    "tag": "quiz",
    "resourceLinkId": "resource_123",
    "startDateTime": "2026-01-01T00:00:00Z",
    "endDateTime": "2026-01-31T23:59:59Z"
  }
]

POST /api/lms/lti/ags/{context}/lineitems
Content-Type: application/vnd.ims.lis.v2.lineitem+json

{
  "scoreMaximum": 100,
  "label": "Quiz 2",
  "resourceLinkId": "resource_456"
}
```

**Score Submission:**
```
POST /api/lms/lti/ags/lineitems/{lineitem_id}/scores
Content-Type: application/vnd.ims.lis.v1.score+json
Authorization: Bearer {access_token}

{
  "userId": "student_123",
  "scoreGiven": 85,
  "scoreMaximum": 100,
  "activityProgress": "Completed",
  "gradingProgress": "FullyGraded",
  "timestamp": "2026-01-15T14:30:00Z",
  "comment": "Good work!"
}

Response: 200 OK (no body)
```

**Access Token Request:**
```
POST /api/lms/lti/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
&client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer
&client_assertion={signed_jwt}
&scope=https://purl.imsglobal.org/spec/lti-ags/scope/score

Response:
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "https://purl.imsglobal.org/spec/lti-ags/scope/score"
}
```

#### 4.4.4 LTI Advantage - Names and Role Provisioning (NRPS)

**Description:** Allows tools to retrieve course membership.

**Membership Request:**
```
GET /api/lms/lti/nrps/{context}/memberships
Authorization: Bearer {access_token}
Accept: application/vnd.ims.lti-nrps.v2.membershipcontainer+json

Response:
{
  "id": "https://school.edu/lti/nrps/course_456",
  "context": {
    "id": "course_456",
    "title": "Mathematics F2"
  },
  "members": [
    {
      "status": "Active",
      "user_id": "student_123",
      "roles": ["http://purl.imsglobal.org/vocab/lis/v2/membership#Learner"],
      "name": "Thabo Mosweu",
      "email": "thabo@school.edu"
    },
    {
      "status": "Active",
      "user_id": "teacher_789",
      "roles": ["http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor"],
      "name": "Mrs. Mosweu",
      "email": "mosweu@school.edu"
    }
  ]
}
```

**Data Models:**
```
lms_lti_platforms (when acting as Tool Provider)
├── id (bigint, PK)
├── name (varchar 255)
├── platform_id (varchar 255, unique) -- issuer
├── client_id (varchar 255)
├── deployment_id (varchar 255)
├── auth_login_url (varchar 500)
├── auth_token_url (varchar 500)
├── key_set_url (varchar 500)
├── public_key (text, nullable)
├── is_active (boolean, default true)
├── settings (json, nullable)
├── created_at, updated_at

lms_lti_tools (when acting as Platform)
├── id (bigint, PK)
├── name (varchar 255)
├── description (text, nullable)
├── url (varchar 500) -- launch URL
├── client_id (varchar 255, unique)
├── deployment_id (varchar 255)
├── public_key_set (text) -- JWKS
├── initiate_login_url (varchar 500)
├── redirect_urls (json)
├── custom_parameters (json, nullable)
├── claims (json) -- required claims
├── scopes (json) -- AGS, NRPS scopes
├── is_active (boolean, default true)
├── deep_linking_enabled (boolean, default false)
├── grades_passback_enabled (boolean, default true)
├── icon_url (varchar 500, nullable)
├── created_at, updated_at

lms_lti_launches
├── id (bigint, PK)
├── tool_id (FK -> lms_lti_tools.id, nullable)
├── platform_id (FK -> lms_lti_platforms.id, nullable)
├── user_id (FK -> users.id, nullable)
├── student_id (FK -> students.id, nullable)
├── content_item_id (FK -> lms_content_items.id, nullable)
├── message_type (enum: LtiResourceLinkRequest, LtiDeepLinkingRequest)
├── nonce (varchar 255)
├── state (varchar 255)
├── id_token (text)
├── claims (json)
├── resource_link_id (varchar 255)
├── context_id (varchar 255, nullable)
├── launched_at (datetime)
├── expires_at (datetime)
├── created_at
```

---

### 4.5 Quiz System Module

#### 4.5.1 Quiz Creation

**Functional Flow:**
```
1. Teacher adds "Quiz" content type
2. System displays quiz builder:
   - Quiz title
   - Instructions
   - Time limit (optional)
   - Max attempts (optional)
   - Passing score (default 60%)
   - Shuffle questions (checkbox)
   - Shuffle answers (checkbox)
   - Show correct answers after submission
   - Access code (optional)
3. Teacher adds questions using question editor
4. Teacher can import from question bank
5. Quiz saved as draft
6. Teacher previews quiz before publishing
```

**Question Types:**

| Type | Description | Auto-Graded |
|------|-------------|-------------|
| Multiple Choice | Single correct answer | Yes |
| Multiple Answer | Multiple correct answers | Yes |
| True/False | Binary choice | Yes |
| Matching | Match items in two columns | Yes |
| Fill-in-Blank | Text input with answer patterns | Yes |
| Short Answer | Brief text response | Partial |
| Essay | Long-form response | No |
| Ordering | Arrange items in sequence | Yes |

**Question Data Structure:**
```json
// Multiple Choice
{
  "type": "multiple_choice",
  "question_text": "What is the capital of Botswana?",
  "points": 1,
  "options": [
    {"id": "a", "text": "Gaborone", "is_correct": true},
    {"id": "b", "text": "Francistown", "is_correct": false},
    {"id": "c", "text": "Maun", "is_correct": false},
    {"id": "d", "text": "Kasane", "is_correct": false}
  ],
  "feedback_correct": "Correct! Gaborone is the capital.",
  "feedback_incorrect": "Incorrect. The capital is Gaborone."
}

// Matching
{
  "type": "matching",
  "question_text": "Match the countries with their capitals:",
  "points": 4,
  "pairs": [
    {"left": "Botswana", "right": "Gaborone"},
    {"left": "South Africa", "right": "Pretoria"},
    {"left": "Namibia", "right": "Windhoek"},
    {"left": "Zimbabwe", "right": "Harare"}
  ],
  "distractors": ["Lusaka", "Maputo"]
}

// Fill-in-Blank
{
  "type": "fill_blank",
  "question_text": "The chemical symbol for water is ____.",
  "points": 1,
  "correct_answers": ["H2O", "h2o"],
  "case_sensitive": false
}
```

#### 4.5.2 Quiz Taking Flow

```
1. Student opens quiz content
2. System checks:
   - Enrollment status
   - Attempt limit not exceeded
   - Quiz is within availability window
   - Access code (if required)
3. Student clicks "Start Quiz"
4. System creates quiz_attempt record
5. System displays questions:
   - All at once OR one per page (based on settings)
   - Timer visible (if time limit set)
6. Student answers questions
7. Answers auto-saved every 30 seconds
8. Student clicks "Submit Quiz" or timer expires
9. System grades auto-gradeable questions
10. System calculates total score
11. System determines pass/fail
12. Results displayed based on settings
```

**Anti-Cheating Measures:**
- Questions shuffled per attempt
- Answer options shuffled
- Time limit enforcement
- Tab-switch detection (optional warning)
- IP logging

**Data Models:**
```
lms_quizzes
├── id (bigint, PK)
├── content_item_id (FK -> lms_content_items.id)
├── title (varchar 255)
├── instructions (text, nullable)
├── time_limit_minutes (int, nullable)
├── max_attempts (int, nullable)
├── shuffle_questions (boolean, default false)
├── shuffle_answers (boolean, default false)
├── show_correct_answers (boolean, default false)
├── show_correct_answers_after (datetime, nullable)
├── passing_score (decimal 5,2, default 60)
├── allow_review (boolean, default true)
├── one_question_per_page (boolean, default false)
├── require_access_code (boolean, default false)
├── access_code (varchar 50, nullable)
├── created_at, updated_at, deleted_at

lms_quiz_questions
├── id (bigint, PK)
├── quiz_id (FK -> lms_quizzes.id)
├── question_bank_id (FK, nullable)
├── type (enum: multiple_choice, multiple_answer, true_false, matching, fill_blank, short_answer, essay, ordering)
├── question_text (text)
├── question_media (json, nullable)
├── points (decimal 5,2, default 1.00)
├── sequence (int)
├── feedback_correct (text, nullable)
├── feedback_incorrect (text, nullable)
├── options (json) -- answer options
├── correct_answer (json)
├── case_sensitive (boolean, default false)
├── partial_credit (boolean, default false)
├── created_at, updated_at

lms_quiz_attempts
├── id (bigint, PK)
├── quiz_id (FK -> lms_quizzes.id)
├── student_id (FK -> students.id)
├── attempt_number (int)
├── started_at (datetime)
├── submitted_at (datetime, nullable)
├── time_spent_seconds (int, nullable)
├── score (decimal 10,2, nullable)
├── max_score (decimal 10,2)
├── percentage (decimal 5,2, nullable)
├── passed (boolean, nullable)
├── answers (json) -- all responses
├── grading_status (enum: pending, auto_graded, manually_graded, finalized)
├── graded_by (FK -> users.id, nullable)
├── graded_at (datetime, nullable)
├── feedback (text, nullable)
├── ip_address (varchar 45, nullable)
├── created_at, updated_at
├── INDEX (quiz_id, student_id)
```

---

### 4.6 Assignment System Module

#### 4.6.1 Assignment Creation

**Functional Flow:**
```
1. Teacher adds "Assignment" content type
2. System displays assignment form:
   - Title
   - Instructions (rich text)
   - Due date/time
   - Points possible
   - Submission types allowed:
     - Text entry (online)
     - File upload
     - URL link
     - Media recording
   - File restrictions (types, size, count)
   - Allow late submissions
   - Late penalty percentage
   - Rubric (optional)
3. Teacher saves assignment
```

**Data Model:**
```
lms_assignments
├── id (bigint, PK)
├── content_item_id (FK -> lms_content_items.id)
├── title (varchar 255)
├── instructions (text)
├── due_date (datetime, nullable)
├── available_from (datetime, nullable)
├── available_until (datetime, nullable)
├── points_possible (decimal 10,2)
├── submission_types (json) -- ["text", "file", "url"]
├── allowed_file_types (json, nullable) -- [".pdf", ".docx"]
├── max_file_size_mb (int, default 50)
├── max_files (int, default 5)
├── allow_late_submissions (boolean, default false)
├── late_penalty_percent (decimal 5,2, default 0)
├── rubric_id (FK -> lms_rubrics.id, nullable)
├── peer_review_enabled (boolean, default false)
├── anonymous_grading (boolean, default false)
├── created_at, updated_at, deleted_at
```

#### 4.6.2 Assignment Submission

**Functional Flow:**
```
1. Student opens assignment
2. System checks:
   - Enrollment
   - Availability window
   - Previous submissions (if resubmission allowed)
3. Student prepares submission:
   - Enter text (if text type)
   - Upload files (if file type)
   - Enter URL (if URL type)
4. Student clicks "Submit"
5. System validates submission:
   - File types allowed
   - File size within limit
   - Total files within limit
6. System creates submission record
7. System calculates is_late flag
8. System notifies teacher
```

**Data Model:**
```
lms_assignment_submissions
├── id (bigint, PK)
├── assignment_id (FK -> lms_assignments.id)
├── student_id (FK -> students.id)
├── submission_type (enum: text, file, url, media)
├── content (longtext, nullable) -- text submissions
├── files (json, nullable) -- [{"path": "...", "name": "...", "size": 123}]
├── url (varchar 500, nullable)
├── submitted_at (datetime)
├── is_late (boolean, default false)
├── attempt_number (int, default 1)
├── word_count (int, nullable)
├── score (decimal 10,2, nullable)
├── grade_letter (varchar 5, nullable)
├── percentage (decimal 5,2, nullable)
├── feedback (text, nullable)
├── graded_by (FK -> users.id, nullable)
├── graded_at (datetime, nullable)
├── rubric_scores (json, nullable)
├── late_penalty_applied (decimal 10,2, default 0)
├── created_at, updated_at
├── INDEX (assignment_id, student_id)
```

#### 4.6.3 Rubric Grading

**Rubric Structure:**
```json
{
  "title": "Essay Rubric",
  "criteria": [
    {
      "id": "content",
      "title": "Content & Understanding",
      "levels": [
        {"points": 4, "label": "Excellent", "description": "Demonstrates thorough understanding..."},
        {"points": 3, "label": "Good", "description": "Demonstrates good understanding..."},
        {"points": 2, "label": "Satisfactory", "description": "Demonstrates basic understanding..."},
        {"points": 1, "label": "Needs Improvement", "description": "Limited understanding..."}
      ]
    },
    {
      "id": "organization",
      "title": "Organization",
      "levels": [...]
    }
  ]
}
```

**Grading Flow:**
```
1. Teacher opens submission
2. System displays submission content and rubric
3. Teacher selects level for each criterion
4. System calculates total score
5. Teacher adds feedback comments
6. Teacher clicks "Save Grade"
7. System updates submission record
8. System notifies student
```

---

### 4.7 Progress Tracking Module

#### 4.7.1 Progress Calculation

**Content Completion Logic:**
```php
class ProgressService {

    public function calculateContentProgress(ContentItem $content, Student $student): array {
        $progress = LmsContentProgress::firstOrCreate([
            'enrollment_id' => $this->getEnrollmentId($content, $student),
            'content_item_id' => $content->id,
        ]);

        switch ($content->content_type) {
            case 'text':
            case 'document':
            case 'image':
            case 'external_url':
                // Complete on view
                return ['status' => 'completed', 'score' => null];

            case 'video_youtube':
            case 'video_upload':
            case 'audio':
                // Complete when watch percentage >= threshold
                $videoProgress = $this->getVideoProgress($content, $student);
                if ($videoProgress->watch_percentage >= $content->video->completion_threshold) {
                    return ['status' => 'completed', 'score' => null];
                }
                return ['status' => 'in_progress', 'score' => null];

            case 'scorm12':
            case 'scorm2004':
                // Use SCORM completion_status and success_status
                $attempt = $this->getLatestScormAttempt($content, $student);
                return [
                    'status' => $attempt->completion_status ?? 'not_started',
                    'score' => $attempt->score_raw,
                ];

            case 'quiz':
                // Complete when submitted with passing score
                $attempt = $this->getBestQuizAttempt($content, $student);
                if ($attempt && $attempt->passed) {
                    return ['status' => 'completed', 'score' => $attempt->percentage];
                }
                return ['status' => $attempt ? 'in_progress' : 'not_started', 'score' => $attempt?->percentage];

            case 'assignment':
                // Complete when graded
                $submission = $this->getLatestSubmission($content, $student);
                if ($submission && $submission->graded_at) {
                    return ['status' => 'completed', 'score' => $submission->percentage];
                }
                return ['status' => $submission ? 'in_progress' : 'not_started', 'score' => null];

            case 'h5p':
                // Use H5P result
                $result = $this->getH5PResult($content, $student);
                return [
                    'status' => $result ? 'completed' : 'not_started',
                    'score' => $result ? ($result->score / $result->max_score) * 100 : null,
                ];

            case 'lti_tool':
                // Use LTI AGS grade
                $grade = $this->getLtiGrade($content, $student);
                return [
                    'status' => $grade?->activity_progress === 'Completed' ? 'completed' : 'in_progress',
                    'score' => $grade ? ($grade->score / $grade->score_maximum) * 100 : null,
                ];
        }
    }

    public function calculateCourseProgress(Course $course, Student $student): float {
        $enrollment = $course->enrollments()->where('student_id', $student->id)->first();
        if (!$enrollment) return 0;

        $contentItems = $course->modules()
            ->with('contentItems')
            ->get()
            ->pluck('contentItems')
            ->flatten()
            ->where('is_mandatory', true);

        if ($contentItems->isEmpty()) return 0;

        $completed = $contentItems->filter(function ($item) use ($student) {
            $progress = $this->calculateContentProgress($item, $student);
            return $progress['status'] === 'completed';
        })->count();

        return round(($completed / $contentItems->count()) * 100, 2);
    }

    public function calculateCourseGrade(Course $course, Student $student): ?float {
        $scoredItems = $course->modules()
            ->with('contentItems')
            ->get()
            ->pluck('contentItems')
            ->flatten()
            ->whereIn('content_type', ['quiz', 'assignment', 'scorm12', 'scorm2004', 'h5p', 'lti_tool']);

        if ($scoredItems->isEmpty()) return null;

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($scoredItems as $item) {
            $progress = $this->calculateContentProgress($item, $student);
            if ($progress['score'] !== null) {
                $weight = $item->points ?? 1;
                $totalScore += $progress['score'] * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : null;
    }
}
```

**Data Model:**
```
lms_content_progress
├── id (bigint, PK)
├── enrollment_id (FK -> lms_enrollments.id)
├── content_item_id (FK -> lms_content_items.id)
├── status (enum: not_started, in_progress, completed)
├── started_at (datetime, nullable)
├── completed_at (datetime, nullable)
├── time_spent_seconds (int, default 0)
├── score (decimal 10,2, nullable)
├── score_percentage (decimal 5,2, nullable)
├── attempts (int, default 0)
├── best_score (decimal 10,2, nullable)
├── last_position (json, nullable) -- bookmark data
├── created_at, updated_at
├── UNIQUE (enrollment_id, content_item_id)

lms_enrollments
├── id (bigint, PK)
├── course_id (FK -> lms_courses.id)
├── student_id (FK -> students.id)
├── enrolled_by (FK -> users.id, nullable)
├── enrollment_type (enum: self, manual, auto, lti)
├── role (enum: learner, instructor_assistant)
├── status (enum: active, completed, dropped, suspended)
├── enrolled_at (datetime)
├── started_at (datetime, nullable)
├── completed_at (datetime, nullable)
├── progress_percentage (decimal 5,2, default 0)
├── current_module_id (FK, nullable)
├── current_content_id (FK, nullable)
├── grade (decimal 10,2, nullable)
├── grade_letter (varchar 5, nullable)
├── certificate_issued_at (datetime, nullable)
├── last_activity_at (datetime, nullable)
├── created_at, updated_at
├── UNIQUE (course_id, student_id)
```

---

### 4.8 Assessment Integration Module

#### 4.8.1 Grade Mapping

**Description:** Map LMS course completion grades to existing assessment records.

**Mapping Configuration:**
```
1. Teacher opens course settings
2. Teacher selects "Grade Integration"
3. System displays mapping form:
   - Target Test (from existing tests for grade/subject)
   - Sync Type:
     - Completion only (pass/fail)
     - Score passthrough
     - Both
   - Score Weight (percentage, default 100%)
   - Minimum score to sync
   - Auto-sync enabled
4. Teacher saves mapping
```

**Sync Logic:**
```php
class AssessmentSyncService {

    public function syncCourseGrades(LmsCourse $course): SyncResult {
        $mapping = $course->assessmentMapping;
        if (!$mapping || !$mapping->auto_sync) {
            return new SyncResult(false, 'No active mapping');
        }

        $enrollments = $course->enrollments()
            ->where('status', 'completed')
            ->whereNotNull('grade')
            ->get();

        $synced = 0;
        foreach ($enrollments as $enrollment) {
            if ($this->shouldSync($enrollment, $mapping)) {
                $this->syncStudentGrade($enrollment, $mapping);
                $synced++;
            }
        }

        $mapping->update(['last_synced_at' => now()]);
        return new SyncResult(true, "{$synced} grades synced");
    }

    private function shouldSync(LmsEnrollment $enrollment, LmsAssessmentMapping $mapping): bool {
        if ($mapping->minimum_score_to_sync && $enrollment->grade < $mapping->minimum_score_to_sync) {
            return false;
        }
        return true;
    }

    private function syncStudentGrade(LmsEnrollment $enrollment, LmsAssessmentMapping $mapping): void {
        $score = ($enrollment->grade * $mapping->score_weight) / 100;

        StudentTest::updateOrCreate(
            [
                'student_id' => $enrollment->student_id,
                'test_id' => $mapping->test_id,
            ],
            [
                'score' => $score,
                'percentage' => $enrollment->grade,
                'grade' => $this->calculateGradeLetter($enrollment->grade),
                'klass_id' => $enrollment->student->currentKlass?->id,
                'user_id' => $enrollment->course->instructor_id,
            ]
        );
    }
}
```

**Data Model:**
```
lms_assessment_mappings
├── id (bigint, PK)
├── course_id (FK -> lms_courses.id, unique)
├── test_id (FK -> tests.id)
├── grade_subject_id (FK -> grade_subject.id)
├── grade_id (FK -> grades.id)
├── term_id (FK -> terms.id)
├── sync_type (enum: completion, score, both)
├── score_weight (decimal 5,2, default 100)
├── minimum_score_to_sync (decimal 5,2, nullable)
├── auto_sync (boolean, default true)
├── last_synced_at (datetime, nullable)
├── created_at, updated_at
```

---

### 4.9 Gamification Module

#### 4.9.1 Points System

**Point Actions:**
| Action | Points | Limit |
|--------|--------|-------|
| Complete content item | 10 | Per item |
| Complete module | 50 | Per module |
| Complete course | 200 | Per course |
| Pass quiz (first attempt) | 25 | Per quiz |
| Pass quiz (subsequent) | 10 | Per quiz |
| Perfect quiz score | 50 | Per quiz |
| Submit assignment on time | 15 | Per assignment |
| Daily login | 5 | Once per day |
| 7-day streak | 100 | Weekly |

#### 4.9.2 Badge System

**Badge Types:**
```json
{
  "badges": [
    {
      "id": "first_course",
      "name": "First Steps",
      "description": "Complete your first course",
      "icon": "badge-first-course.svg",
      "category": "achievement",
      "criteria": {
        "type": "course_completions",
        "count": 1
      },
      "points": 100
    },
    {
      "id": "quiz_master",
      "name": "Quiz Master",
      "description": "Score 100% on 5 quizzes",
      "icon": "badge-quiz-master.svg",
      "category": "skill",
      "criteria": {
        "type": "perfect_quiz_scores",
        "count": 5
      },
      "points": 200
    },
    {
      "id": "week_streak",
      "name": "Week Warrior",
      "description": "Maintain a 7-day learning streak",
      "icon": "badge-streak.svg",
      "category": "achievement",
      "criteria": {
        "type": "login_streak",
        "days": 7
      },
      "points": 150
    }
  ]
}
```

**Badge Award Logic:**
```php
class BadgeService {

    public function checkBadges(Student $student, string $eventType, array $context = []): void {
        $badges = LmsBadge::where('is_active', true)->get();

        foreach ($badges as $badge) {
            if ($this->studentHasBadge($student, $badge)) continue;
            if ($this->meetsCriteria($student, $badge, $eventType, $context)) {
                $this->awardBadge($student, $badge, $context);
            }
        }
    }

    private function meetsCriteria(Student $student, LmsBadge $badge, string $event, array $context): bool {
        $criteria = $badge->criteria;

        switch ($criteria['type']) {
            case 'course_completions':
                return LmsEnrollment::where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->count() >= $criteria['count'];

            case 'perfect_quiz_scores':
                return LmsQuizAttempt::where('student_id', $student->id)
                    ->where('percentage', 100)
                    ->distinct('quiz_id')
                    ->count() >= $criteria['count'];

            case 'login_streak':
                return $this->getLoginStreak($student) >= $criteria['days'];

            default:
                return false;
        }
    }

    private function awardBadge(Student $student, LmsBadge $badge, array $context): void {
        LmsStudentBadge::create([
            'student_id' => $student->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
            'course_id' => $context['course_id'] ?? null,
        ]);

        // Award points
        $this->pointsService->addPoints($student, $badge->points, 'badge_earned', $badge->id);

        // Send notification
        $this->notificationService->notify($student, 'badge_earned', [
            'badge_name' => $badge->name,
            'badge_icon' => $badge->icon_path,
        ]);
    }
}
```

#### 4.9.3 Leaderboards

**Leaderboard Types:**
- Class leaderboard (within student's class)
- Grade leaderboard (within grade level)
- Course leaderboard (within specific course)
- School-wide leaderboard

**Leaderboard Calculation:**
```php
public function getClassLeaderboard(Klass $klass, int $limit = 10): Collection {
    return LmsLeaderboard::where('klass_id', $klass->id)
        ->orderBy('total_points', 'desc')
        ->limit($limit)
        ->with('student:id,first_name,last_name')
        ->get()
        ->map(function ($entry, $index) {
            return [
                'rank' => $index + 1,
                'student_name' => $entry->student->full_name,
                'points' => $entry->total_points,
                'level' => $entry->level,
                'streak' => $entry->streak_days,
            ];
        });
}
```

**Data Models:**
```
lms_badges
├── id (bigint, PK)
├── name (varchar 100)
├── description (text)
├── icon_path (varchar 255)
├── category (enum: achievement, completion, skill, special)
├── criteria (json)
├── points (int, default 0)
├── is_active (boolean, default true)
├── created_at, updated_at

lms_student_badges
├── id (bigint, PK)
├── student_id (FK -> students.id)
├── badge_id (FK -> lms_badges.id)
├── earned_at (datetime)
├── course_id (FK -> lms_courses.id, nullable)
├── reason (varchar 255, nullable)
├── created_at
├── UNIQUE (student_id, badge_id)

lms_points_log
├── id (bigint, PK)
├── student_id (FK -> students.id)
├── points (int) -- can be negative
├── action (varchar 100)
├── reference_type (varchar 100, nullable)
├── reference_id (bigint, nullable)
├── course_id (FK, nullable)
├── created_at
├── INDEX (student_id, created_at)

lms_leaderboards
├── id (bigint, PK)
├── student_id (FK -> students.id)
├── course_id (FK, nullable) -- null for global
├── klass_id (FK, nullable)
├── grade_id (FK, nullable)
├── total_points (int, default 0)
├── rank (int, nullable)
├── level (int, default 1)
├── streak_days (int, default 0)
├── last_activity_date (date)
├── updated_at
├── INDEX (klass_id, total_points DESC)
├── INDEX (grade_id, total_points DESC)
```

---

### 4.10 Analytics Module

#### 4.10.1 Instructor Dashboard

**Metrics Displayed:**
- Total enrollments
- Active learners (last 7 days)
- Completion rate
- Average score
- Content with lowest completion
- Students at risk (no activity in 14+ days)

#### 4.10.2 Event Tracking

**Tracked Events:**
| Event | Data Captured |
|-------|---------------|
| content_view | content_id, timestamp, duration |
| video_play | video_id, position |
| video_pause | video_id, position |
| video_complete | video_id, watch_percentage |
| quiz_start | quiz_id, attempt_number |
| quiz_submit | quiz_id, score, time_spent |
| assignment_submit | assignment_id |
| scorm_initialize | package_id, attempt_id |
| scorm_terminate | package_id, completion_status |
| course_complete | course_id, grade |

**Data Model:**
```
lms_analytics_events
├── id (bigint, PK)
├── student_id (FK, nullable)
├── user_id (FK, nullable)
├── course_id (FK, nullable)
├── content_item_id (FK, nullable)
├── event_type (varchar 50)
├── event_data (json, nullable)
├── session_id (varchar 100)
├── ip_address (varchar 45, nullable)
├── user_agent (varchar 500, nullable)
├── device_type (enum: desktop, tablet, mobile)
├── created_at
├── INDEX (student_id, event_type, created_at)
├── INDEX (course_id, created_at)
```

---

## 5. Technical Architecture

### 5.1 System Context Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Heritage LMS                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │ Teachers │  │ Students │  │  Parents │  │  Admins  │        │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘        │
│       │             │             │             │                │
│       └─────────────┴──────┬──────┴─────────────┘                │
│                            │                                      │
│                   ┌────────▼────────┐                            │
│                   │   Web Browser   │                            │
│                   │ (Bootstrap/JS)  │                            │
│                   └────────┬────────┘                            │
│                            │                                      │
│                   ┌────────▼────────┐                            │
│                   │  Laravel 9.x    │                            │
│                   │  Application    │                            │
│                   └────────┬────────┘                            │
│                            │                                      │
│            ┌───────────────┼───────────────┐                     │
│            │               │               │                     │
│   ┌────────▼────┐  ┌───────▼─────┐  ┌─────▼──────┐              │
│   │    MySQL    │  │   Storage   │  │   Redis    │              │
│   │  Database   │  │ (SCORM/Video│  │  (Cache)   │              │
│   └─────────────┘  └─────────────┘  └────────────┘              │
│                                                                  │
└──────────────────────────┬───────────────────────────────────────┘
                           │
          ┌────────────────┼────────────────┐
          │                │                │
  ┌───────▼──────┐  ┌──────▼──────┐  ┌─────▼──────┐
  │ YouTube API  │  │  LTI Tools  │  │ Zoom/Teams │
  │              │  │  (External) │  │            │
  └──────────────┘  └─────────────┘  └────────────┘
```

### 5.2 Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Lms/
│           ├── CourseController.php
│           ├── ModuleController.php
│           ├── ContentController.php
│           ├── EnrollmentController.php
│           ├── QuizController.php
│           ├── AssignmentController.php
│           ├── ScormController.php
│           ├── LtiController.php
│           ├── ProgressController.php
│           ├── AnalyticsController.php
│           └── GamificationController.php
├── Models/
│   └── Lms/
│       ├── Course.php
│       ├── Module.php
│       ├── ContentItem.php
│       ├── Enrollment.php
│       ├── ContentProgress.php
│       ├── Quiz.php
│       ├── QuizQuestion.php
│       ├── QuizAttempt.php
│       ├── Assignment.php
│       ├── AssignmentSubmission.php
│       ├── ScormPackage.php
│       ├── ScormAttempt.php
│       ├── LtiTool.php
│       ├── LtiLaunch.php
│       ├── Video.php
│       ├── VideoProgress.php
│       ├── Badge.php
│       ├── StudentBadge.php
│       └── AssessmentMapping.php
├── Services/
│   └── Lms/
│       ├── CourseService.php
│       ├── ProgressService.php
│       ├── ScormRuntimeService.php
│       ├── LtiService.php
│       ├── QuizGradingService.php
│       ├── AssessmentSyncService.php
│       ├── GamificationService.php
│       └── AnalyticsService.php
└── Policies/
    └── Lms/
        ├── CoursePolicy.php
        ├── ContentPolicy.php
        └── EnrollmentPolicy.php

resources/
├── js/
│   └── lms/
│       ├── scorm/
│       │   ├── ScormAPI.js
│       │   └── Scorm2004API.js
│       ├── players/
│       │   ├── VideoPlayer.js
│       │   ├── ScormPlayer.js
│       │   └── QuizPlayer.js
│       └── components/
│           ├── CourseBuilder.js
│           └── ProgressTracker.js
└── views/
    └── lms/
        ├── courses/
        ├── content/
        ├── quizzes/
        ├── assignments/
        └── analytics/

routes/
└── lms/
    ├── courses.php
    ├── content.php
    ├── scorm.php
    ├── lti.php
    ├── quizzes.php
    └── api.php

database/
└── migrations/
    └── lms/
        ├── 2026_01_01_000001_create_lms_courses_table.php
        ├── 2026_01_01_000002_create_lms_modules_table.php
        └── ... (42 migrations)
```

---

## 6. User Interface Specifications

### 6.0 Theming & Design Standards

All LMS module views **MUST** follow the established design patterns used throughout the application, specifically matching the styling from `docs/index.blade.php`, `docs/create.blade.php`, and `docs/edit.blade.php`.

#### 6.0.1 Reference Files
- **Index/List Pages:** `/resources/views/staff/index.blade.php` pattern
- **Create Pages:** `/resources/views/staff/create.blade.php` pattern
- **Edit Pages:** `/resources/views/students/edit.blade.php` pattern

#### 6.0.2 Core Layout Structure

**Base Layout:**
```blade
@extends('layouts.master')

@section('title')
    Page Title
@endsection

@section('css')
    {{-- Page-specific styles --}}
@endsection

@section('content')
    {{-- Page content --}}
@endsection
```

#### 6.0.3 Container Styling

**Index/List Pages (with gradient header):**
```css
.lms-container {
    background: white;
    border-radius: 3px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.lms-header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}

.lms-body {
    padding: 24px;
}
```

**Create/Edit Pages (white container):**
```css
.form-container {
    background: white;
    border-radius: 3px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 22px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}
```

#### 6.0.4 Header Statistics (Index Pages)

```html
<div class="lms-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h3 style="margin:0;">Courses</h3>
            <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage LMS courses</p>
        </div>
        <div class="col-md-6">
            <div class="row text-center">
                <div class="col-4">
                    <div class="stat-item">
                        <h4 class="mb-0 fw-bold text-white">{{ $stats['total'] }}</h4>
                        <small class="opacity-75">Total</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-item">
                        <h4 class="mb-0 fw-bold text-white">{{ $stats['published'] }}</h4>
                        <small class="opacity-75">Published</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-item">
                        <h4 class="mb-0 fw-bold text-white">{{ $stats['enrollments'] }}</h4>
                        <small class="opacity-75">Enrollments</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Stat Item Styling:**
```css
.stat-item {
    padding: 10px 0;
}

.stat-item h4 {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-item small {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
```

#### 6.0.5 Form Styling

**Form Sections:**
```css
.form-section {
    margin-bottom: 28px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}
```

**Form Controls:**
```css
.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.form-control,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-control:focus,
.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
    opacity: 0.7;
}
```

**Form Grid (Responsive):**
```css
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

@media (max-width: 992px) {
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
```

**Required Field Indicator:**
```css
.required::after {
    content: '*';
    color: #dc2626;
    margin-left: 4px;
}
```

#### 6.0.6 Help Text Boxes

```css
.help-text {
    background: #f8f9fa;
    padding: 12px;
    border-left: 4px solid #3b82f6;
    border-radius: 0 3px 3px 0;
    margin-bottom: 20px;
}

.help-text .help-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.help-text .help-content {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.4;
}
```

**Usage:**
```html
<div class="help-text">
    <div class="help-title">About SCORM Packages</div>
    <div class="help-content">
        Upload a SCORM 1.2 or 2004 compliant ZIP package.
        The system will automatically extract and configure the content.
    </div>
</div>
```

#### 6.0.7 Button Styling

**Primary Button (Gradient):**
```css
.btn {
    padding: 10px 20px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-light {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-light:hover {
    background: #e9ecef;
    color: #495057;
    transform: translateY(-1px);
}
```

**Action Buttons (Table rows):**
```css
.action-buttons {
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}

.action-buttons .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-buttons .btn i {
    font-size: 16px;
}
```

**Form Actions Footer:**
```css
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding-top: 24px;
    border-top: 1px solid #f3f4f6;
    margin-top: 32px;
}

.form-actions-left,
.form-actions-right {
    display: flex;
    gap: 8px;
}
```

#### 6.0.8 Status Badges

```css
.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    text-transform: capitalize;
}

/* LMS-specific status colors */
.status-draft {
    background: #fef3c7;
    color: #92400e;
}

.status-published {
    background: #d1fae5;
    color: #065f46;
}

.status-archived {
    background: #f3f4f6;
    color: #4b5563;
}

.status-completed {
    background: #e9d5ff;
    color: #6b21a8;
}

.status-in-progress {
    background: #dbeafe;
    color: #1e40af;
}

.status-not-started {
    background: #fee2e2;
    color: #991b1b;
}
```

#### 6.0.9 Table Styling

**Student/Item Cell with Avatar:**
```css
.item-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.item-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background: #e2e8f0;
}

.item-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
```

**Pagination:**
```css
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
}
```

#### 6.0.10 Color Palette

| Usage | Color | Hex |
|-------|-------|-----|
| Primary Gradient Start | Blue | `#4e73df` |
| Primary Gradient End | Cyan | `#36b9cc` |
| Button Primary Start | Blue | `#3b82f6` |
| Button Primary End | Blue | `#2563eb` |
| Text Primary | Dark Gray | `#1f2937` |
| Text Secondary | Gray | `#374151` |
| Text Muted | Light Gray | `#6b7280` |
| Border | Light Gray | `#d1d5db` |
| Border Light | Lighter Gray | `#e5e7eb` |
| Background Light | Off White | `#f8f9fa` |
| Background Disabled | Light Gray | `#f3f4f6` |
| Success | Green | `#065f46` (text), `#d1fae5` (bg) |
| Warning | Amber | `#92400e` (text), `#fef3c7` (bg) |
| Danger | Red | `#991b1b` (text), `#fee2e2` (bg) |
| Info | Blue | `#1e40af` (text), `#dbeafe` (bg) |

#### 6.0.11 LMS View File Mapping

| View | Template Pattern | Reference |
|------|------------------|-----------|
| `lms/courses/index.blade.php` | Index with gradient header | `docs/index.blade.php` |
| `lms/courses/create.blade.php` | Form with sections | `docs/create.blade.php` |
| `lms/courses/edit.blade.php` | Edit form with status | `docs/edit.blade.php` |
| `lms/modules/index.blade.php` | Nested list | `docs/index.blade.php` |
| `lms/content/create.blade.php` | Multi-step form | `docs/create.blade.php` |
| `lms/quizzes/create.blade.php` | Dynamic form builder | `docs/create.blade.php` |
| `lms/assignments/grade.blade.php` | Split panel layout | `docs/edit.blade.php` |
| `lms/analytics/dashboard.blade.php` | Cards with stats | `docs/index.blade.php` |
| `lms/enrollments/index.blade.php` | Table with actions | `docs/index.blade.php` |

#### 6.0.12 Example: Course Index Page

```blade
@extends('layouts.master')

@section('title')
    Courses
@endsection

@section('css')
    <style>
        .lms-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .lms-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .lms-body {
            padding: 24px;
        }

        /* ... additional styles matching docs/index.blade.php ... */
    </style>
@endsection

@section('content')
    <div class="lms-container">
        <div class="lms-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Courses</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage your LMS courses</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['total'] }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <!-- More stats -->
                    </div>
                </div>
            </div>
        </div>

        <div class="lms-body">
            <!-- Filters -->
            <div class="controls mb-3">
                <!-- Search and filters -->
            </div>

            <!-- Course Table -->
            <table class="table">
                <!-- Table content -->
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                <!-- Pagination -->
            </div>
        </div>
    </div>
@endsection
```

#### 6.0.13 Example: Course Create Page

```blade
@extends('layouts.master')

@section('title')
    Create Course
@endsection

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* ... additional styles matching docs/create.blade.php ... */
    </style>
@endsection

@section('content')
    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Create Course</h1>
            <a href="{{ route('lms.courses.index') }}" class="btn btn-light">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </div>

        <div class="help-text">
            <div class="help-title">Course Setup</div>
            <div class="help-content">
                Create a new course by filling in the details below.
                You can add modules and content after creating the course.
            </div>
        </div>

        <form action="{{ route('lms.courses.store') }}" method="POST">
            @csrf

            <div class="form-section">
                <h5 class="section-title">Basic Information</h5>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">Course Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <!-- More fields -->
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('lms.courses.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-content-save"></i> Create Course
                </button>
            </div>
        </form>
    </div>
@endsection
```

---

### 6.1 Course Catalog (Student View)

```
┌──────────────────────────────────────────────────────────────────┐
│ Course Catalog                                    [Search...] 🔍 │
├──────────────────────────────────────────────────────────────────┤
│ Filter: [All Subjects ▼] [All Grades ▼]                          │
├──────────────────────────────────────────────────────────────────┤
│ ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐   │
│ │  [Thumbnail]    │  │  [Thumbnail]    │  │  [Thumbnail]    │   │
│ │                 │  │                 │  │                 │   │
│ │ Mathematics F2  │  │ Science F2      │  │ English F2      │   │
│ │ Mrs. Mosweu     │  │ Mr. Kgosi       │  │ Ms. Tlhong      │   │
│ │ ⏱️ 10 hours     │  │ ⏱️ 8 hours      │  │ ⏱️ 12 hours     │   │
│ │ 📚 12 lessons   │  │ 📚 10 lessons   │  │ 📚 15 lessons   │   │
│ │                 │  │                 │  │                 │   │
│ │ [Enroll Now]    │  │ [Continue 45%]  │  │ [View Details]  │   │
│ └─────────────────┘  └─────────────────┘  └─────────────────┘   │
└──────────────────────────────────────────────────────────────────┘
```

### 6.2 Course Player (Student View)

```
┌──────────────────────────────────────────────────────────────────┐
│ ← Back to Courses    Mathematics F2    [Progress: 45% ████░░░]   │
├────────────────┬─────────────────────────────────────────────────┤
│                │                                                  │
│ 📁 Module 1    │  ┌────────────────────────────────────────────┐ │
│   ✓ Lesson 1   │  │                                            │ │
│   ✓ Lesson 2   │  │              [Video Player]                │ │
│   ▶ Lesson 3   │  │                                            │ │
│   ○ Quiz 1     │  │    Introduction to Algebra                 │ │
│                │  │                                            │ │
│ 📁 Module 2    │  └────────────────────────────────────────────┘ │
│   🔒 Lesson 4  │                                                  │
│   🔒 Lesson 5  │  Lesson 3: Understanding Variables              │
│                │                                                  │
│ 📁 Module 3    │  In this lesson, you will learn about:          │
│   🔒 Locked    │  • What variables represent                     │
│                │  • How to use variables in equations            │
│                │  • Practice problems                            │
│                │                                                  │
│                │  [← Previous]              [Mark Complete →]    │
└────────────────┴─────────────────────────────────────────────────┘
```

### 6.3 Quiz Interface (Student View)

```
┌──────────────────────────────────────────────────────────────────┐
│ Quiz: Algebra Basics                          ⏱️ Time: 14:32     │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ Question 3 of 10                                    [3 / 10]     │
│                                                                   │
│ ┌───────────────────────────────────────────────────────────────┐│
│ │ What is the value of x in the equation: 2x + 5 = 15?          ││
│ └───────────────────────────────────────────────────────────────┘│
│                                                                   │
│   ○ A) 5                                                         │
│   ● B) 5                                                         │
│   ○ C) 10                                                        │
│   ○ D) 20                                                        │
│                                                                   │
│ ┌─────────────────────────────────────────────────────────────┐  │
│ │ ● ● ● ○ ○ ○ ○ ○ ○ ○                                         │  │
│ │ 1 2 3 4 5 6 7 8 9 10                                        │  │
│ └─────────────────────────────────────────────────────────────┘  │
│                                                                   │
│ [← Previous]                                    [Next →]         │
│                                                                   │
│                              [Submit Quiz]                        │
└──────────────────────────────────────────────────────────────────┘
```

---

## 7. Security Requirements

### 7.1 Authentication & Authorization

| Requirement | Implementation |
|-------------|----------------|
| Role-based access | Gates: `access-lms`, `manage-lms-courses`, `lms-instructor` |
| Enrollment verification | Check enrollment before content access |
| Content isolation | SCORM in sandboxed iframe, signed URLs |

### 7.2 Data Security

| Requirement | Implementation |
|-------------|----------------|
| LTI private keys | Encrypted at rest using Laravel encryption |
| File uploads | Validate MIME type + magic bytes |
| XSS prevention | Sanitize SCORM suspend_data, quiz answers |
| SQL injection | Use Eloquent ORM, parameterized queries |

### 7.3 API Security

| Requirement | Implementation |
|-------------|----------------|
| Rate limiting | 120 req/min for SCORM API, 60 req/min general |
| CSRF protection | Laravel CSRF tokens for web, Sanctum for API |
| LTI security | RS256 JWT signing, nonce validation |

---

## 8. Performance Requirements

### 8.1 Response Time Targets

| Operation | Target |
|-----------|--------|
| Page load | < 2 seconds |
| SCORM API call | < 100ms |
| Video start | < 3 seconds |
| Quiz submission | < 1 second |
| Analytics dashboard | < 3 seconds |

### 8.2 Scalability

| Metric | Target |
|--------|--------|
| Concurrent users | 500 per school |
| SCORM packages | 1000+ |
| Video storage | 500GB+ |
| Analytics events | 1M+ per month |

### 8.3 Optimization Strategies

1. **Database**: Indexes on progress tables, query optimization
2. **Caching**: Redis for course catalog, student progress
3. **Video**: CDN delivery, HLS adaptive streaming
4. **Queues**: Background transcoding, grade sync, analytics aggregation

---

## 9. Implementation Roadmap

### Phase 1: Foundation (Weeks 1-10)
- Core LMS infrastructure (courses, modules, content)
- Native quiz system
- YouTube video integration
- Document viewer
- Basic progress tracking
- Student enrollment

### Phase 2: Advanced Content (Weeks 11-20)
- SCORM 1.2 & 2004 implementation
- Assignment system with rubrics
- H5P integration
- Video upload with transcoding

### Phase 3: Integration (Weeks 21-28)
- LTI 1.3 + Advantage
- Assessment grade sync
- Report card integration

### Phase 4: Engagement (Weeks 29-34)
- Gamification (badges, points, leaderboards)
- Certificates
- Discussion forums

### Phase 5: Intelligence (Weeks 35-40)
- Learning analytics dashboards
- Adaptive learning paths

### Phase 6: Accessibility (Weeks 41-46)
- WCAG 2.1 AA compliance
- PWA/offline support
- Multi-language

---

## 10. Testing Strategy

### 10.1 Test Types

| Type | Scope | Tools |
|------|-------|-------|
| Unit | Models, Services | PHPUnit |
| Integration | API endpoints, SCORM runtime | PHPUnit |
| E2E | User workflows | Laravel Dusk |
| Performance | Load testing | JMeter |
| Accessibility | WCAG compliance | axe, WAVE |

### 10.2 SCORM Conformance Testing

Test with official ADL SCORM test packages:
- ADL SCORM 1.2 Conformance Test Suite
- ADL SCORM 2004 Conformance Test Suite

### 10.3 LTI Certification

Follow IMS Global LTI 1.3 Advantage Conformance Certification process.

---

## 11. Appendices

### Appendix A: Glossary

| Term | Definition |
|------|------------|
| SCORM | Sharable Content Object Reference Model - e-learning standard |
| LTI | Learning Tools Interoperability - tool integration standard |
| RTE | Run-Time Environment - SCORM JavaScript API |
| AGS | Assignment and Grade Services - LTI grade passback |
| NRPS | Names and Role Provisioning Services - LTI roster |
| H5P | HTML5 Package - interactive content framework |
| SCO | Sharable Content Object - SCORM content unit |

### Appendix B: SCORM Error Codes

| Code | Description |
|------|-------------|
| 0 | No error |
| 101 | General exception |
| 201 | Invalid argument |
| 301 | Not initialized |
| 401 | Not implemented |
| 402 | Invalid set value |
| 403 | Element is read-only |

### Appendix C: LTI Role URIs

| Role | URI |
|------|-----|
| Learner | http://purl.imsglobal.org/vocab/lis/v2/membership#Learner |
| Instructor | http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor |
| Administrator | http://purl.imsglobal.org/vocab/lis/v2/membership#Administrator |

---

**Document End**

*This PRD is a living document and will be updated as requirements evolve.*
