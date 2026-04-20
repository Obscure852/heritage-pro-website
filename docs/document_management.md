# Document Management Module - Requirements Document

## 1. Executive Summary

### 1.1 Purpose
A comprehensive Document Management System (DMS) for Heritage Junior Secondary School that provides:
- **Institutional document repository** for policies, procedures, forms, and templates
- **Personal staff repositories** where each staff member can manage their own documents
- **Document sharing** capabilities between staff members
- **Public document access** for selected institutional documents

### 1.2 Key Objectives
- Centralize institutional document storage and management
- Enable staff collaboration through document sharing
- Implement full document lifecycle management (creation → approval → publication → archival)
- Provide robust search and organization capabilities
- Maintain comprehensive audit trails for compliance

---

## 2. Functional Requirements

### 2.1 Document Repository Types

#### 2.1.1 Institutional Repository
- Central repository for official school documents
- Managed by authorized administrators
- Categories: Policies, Procedures, Forms, Templates, Circulars, Notices
- Public or internal access based on document classification

#### 2.1.2 Personal Staff Repositories
- Each staff member has a private document space
- Quota-based storage allocation (configurable per role)
- Personal folder organization
- Ability to share documents with other staff

#### 2.1.3 Shared Repositories
- Department-level shared folders
- Project/committee-based document spaces
- Configurable access permissions

### 2.2 Document Management Features

#### 2.2.1 Upload & Storage
| Requirement | Description |
|-------------|-------------|
| Multi-file upload | Upload multiple files simultaneously via drag-and-drop |
| File types | PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, images (JPG, PNG), TXT |
| Size limits | Configurable per file (default 50MB) and per user quota |
| Metadata capture | Title, description, tags, category, effective date, expiry date |
| Automatic metadata | Upload date, uploader, file size, MIME type, checksum (SHA-256) |

#### 2.2.2 Folder Organization
| Requirement | Description |
|-------------|-------------|
| Hierarchical folders | Unlimited nesting depth for folder structure |
| Folder permissions | Inherit or override parent folder permissions |
| Folder templates | Pre-defined folder structures for common use cases |
| Drag-and-drop | Move documents/folders via drag-and-drop interface |
| Breadcrumb navigation | Clear path indication for current location |

#### 2.2.3 Version Control
| Requirement | Description |
|-------------|-------------|
| Automatic versioning | New version created on each file update |
| Version history | View all previous versions with timestamps and uploaders |
| Version comparison | Side-by-side comparison (for supported formats) |
| Version restore | Restore any previous version as current |
| Version notes | Optional comment/reason for each version |
| Major/Minor versions | Support for v1.0, v1.1, v2.0 numbering scheme |

#### 2.2.4 Search & Discovery
| Requirement | Description |
|-------------|-------------|
| Full-text search | Search within document content (PDF, DOC, TXT) |
| Metadata search | Search by title, tags, category, date range, uploader |
| Advanced filters | Filter by type, status, folder, date, owner |
| Tag system | Multiple tags per document, tag management interface |
| Recent documents | Quick access to recently viewed/modified documents |
| Favorites | Mark documents as favorites for quick access |

### 2.3 Workflow & Approval

#### 2.3.1 Document Status Lifecycle
```
Draft → Pending Review → Under Review → Approved → Published → Archived
                ↓              ↓
            Rejected      Revision Required
```

#### 2.3.2 Approval Workflow
| Requirement | Description |
|-------------|-------------|
| Submit for review | Author submits document for approval |
| Reviewer assignment | Assign specific reviewer(s) or role-based |
| Review comments | Reviewers can add comments/annotations |
| Approval/Rejection | Approve, reject, or request revisions |
| Multi-level approval | Optional sequential approval chain |
| Notifications | Email/SMS notifications at each workflow stage |
| Deadline tracking | Review deadlines with reminders |

#### 2.3.3 Publication
| Requirement | Description |
|-------------|-------------|
| Publish control | Only approved documents can be published |
| Scheduled publishing | Set future publish date |
| Audience selection | Internal only, specific roles, or public |
| Featured documents | Mark documents as featured for homepage display |
| Announcement integration | Option to create announcement on publish |

### 2.4 Access Control & Sharing

#### 2.4.1 Permission Levels
| Level | Capabilities |
|-------|--------------|
| View | Read and download document |
| Comment | View + add comments |
| Edit | View + modify document content |
| Manage | Full control including delete and share |
| Owner | All permissions + transfer ownership |

#### 2.4.2 Sharing Mechanisms
| Requirement | Description |
|-------------|-------------|
| Share with users | Share with specific staff members |
| Share with roles | Share with all users in a role (e.g., "All Teachers") |
| Share with departments | Share with entire department |
| Public links | Generate public access links (with optional password/expiry) |
| Link expiration | Auto-expire shared links after set period |
| View-only links | Prevent download, allow only online viewing |

#### 2.4.3 Public Access
| Requirement | Description |
|-------------|-------------|
| Public document portal | Dedicated page for public documents |
| No login required | Public documents accessible without authentication |
| Category browsing | Browse public documents by category |
| Download tracking | Track downloads even for public documents |

### 2.5 Expiration & Retention

#### 2.5.1 Document Expiration
| Requirement | Description |
|-------------|-------------|
| Expiry date | Set expiration date on documents |
| Expiry notifications | Alert owners before document expires |
| Auto-archive | Automatically archive expired documents |
| Renewal workflow | Option to renew/extend expiration |
| Grace period | Configurable grace period before archival |

#### 2.5.2 Retention Policies
| Requirement | Description |
|-------------|-------------|
| Retention rules | Define retention periods by category |
| Legal hold | Prevent deletion of documents under legal hold |
| Auto-deletion | Optional permanent deletion after retention period |
| Retention reports | Reports on documents approaching retention limits |

### 2.6 Audit Trail

#### 2.6.1 Tracked Events
- Document created/uploaded
- Document viewed/downloaded
- Document edited/updated
- Document shared/unshared
- Document moved/renamed
- Document deleted/restored
- Permission changes
- Workflow status changes
- Version created
- Comment added

#### 2.6.2 Audit Features
| Requirement | Description |
|-------------|-------------|
| Complete history | Full audit log per document |
| User activity | All document activities by user |
| Export logs | Export audit logs to CSV/Excel |
| Date range filtering | Filter audit logs by date range |
| IP tracking | Record IP address for each action |
| Compliance reports | Pre-built reports for compliance audits |

---

## 3. Non-Functional Requirements

### 3.1 Performance
| Requirement | Target |
|-------------|--------|
| Page load time | < 2 seconds |
| Upload speed | Limited by network, chunked uploads for large files |
| Search response | < 1 second for basic search |
| Concurrent users | Support 100+ simultaneous users |

### 3.2 Storage
| Requirement | Description |
|-------------|-------------|
| Primary storage | Local filesystem (configurable) |
| Cloud storage | Optional S3-compatible storage |
| Backup | Daily automated backups |
| Encryption | At-rest encryption for sensitive documents |

### 3.3 Security
| Requirement | Description |
|-------------|-------------|
| Authentication | Integrate with existing Laravel Sanctum auth |
| Authorization | Role-based via Spatie Permission |
| File validation | MIME type and extension verification |
| Virus scanning | Optional integration with ClamAV |
| Secure downloads | Signed URLs with expiration |
| CSRF protection | All forms protected |

### 3.4 Accessibility
| Requirement | Description |
|-------------|-------------|
| WCAG 2.1 AA | Compliance with accessibility standards |
| Screen reader | Proper ARIA labels and semantic HTML |
| Keyboard navigation | Full keyboard accessibility |

---

## 4. User Interface Requirements

### 4.1 Main Dashboard
- Storage usage indicator (quota)
- Recent documents (viewed/modified)
- Pending approvals (for reviewers)
- Documents shared with me
- Quick upload button
- Search bar

### 4.2 Document Browser
- Dual-pane layout (folders left, documents right)
- List/Grid view toggle
- Sort options (name, date, size, type)
- Bulk selection and actions
- Context menu (right-click)
- Preview pane (optional)

### 4.3 Document Viewer
- In-browser preview for PDFs and images
- Download button
- Share button
- Version history sidebar
- Comments/annotations panel
- Metadata display
- Related documents

### 4.4 Upload Interface
- Drag-and-drop zone
- Progress indicators
- Metadata form (title, description, tags)
- Folder selection
- Batch upload with shared metadata

### 4.5 Admin Interface
- User quota management
- Category/tag management
- Retention policy configuration
- System-wide statistics
- Audit log viewer
- Storage management

---

## 5. Integration Requirements

### 5.1 Existing System Integration
| System | Integration |
|--------|-------------|
| User Management | Use existing User model and authentication |
| Permissions | Extend Spatie Permission with document permissions |
| Notifications | Use existing notification system |
| Activity Log | Extend existing ActivityLog model |
| File Storage | Use existing FileUploadService patterns |

### 5.2 External Integrations (Future)
- Microsoft Office Online preview
- Google Docs preview
- Email attachment import
- Bulk import from external systems

---

## 6. Data Model Overview

### 6.1 Core Entities

```
Document
├── id, ulid, title, description
├── file_path, original_name, mime_type, size_bytes, checksum
├── storage_disk, storage_path
├── category_id, folder_id, owner_id
├── status (draft, pending_review, approved, published, archived)
├── visibility (private, internal, public)
├── effective_date, expiry_date
├── current_version, is_featured
├── created_at, updated_at, deleted_at

DocumentVersion
├── id, document_id, version_number (e.g., "1.0", "1.1")
├── file_path, original_name, size_bytes, checksum
├── uploaded_by_user_id, version_notes
├── created_at

DocumentFolder
├── id, ulid, name, description
├── parent_id (self-referential for hierarchy)
├── owner_id, repository_type (institutional, personal, shared)
├── visibility, sort_order
├── created_at, updated_at, deleted_at

DocumentCategory
├── id, name, slug, description
├── parent_id (optional hierarchy)
├── icon, color, sort_order
├── retention_days (default retention)
├── is_active

DocumentTag
├── id, name, slug, color
├── usage_count (denormalized)

DocumentShare
├── id, document_id
├── shared_with_type (user, role, department, public_link)
├── shared_with_id (polymorphic)
├── permission_level (view, comment, edit, manage)
├── shared_by_user_id
├── expires_at, password_hash (for public links)
├── access_token (for public links)
├── created_at

DocumentApproval
├── id, document_id, version_id
├── reviewer_id, status (pending, approved, rejected, revision_required)
├── comments, reviewed_at
├── due_date

DocumentAudit
├── id, document_id, user_id
├── action (viewed, downloaded, edited, shared, etc.)
├── ip_address, user_agent
├── metadata (JSON for additional context)
├── created_at

DocumentComment
├── id, document_id, user_id
├── parent_id (for threaded comments)
├── content, page_number (for PDF annotations)
├── resolved_at, resolved_by
├── created_at, updated_at
```

### 6.2 Pivot Tables
- `document_tag` (document_id, tag_id)
- `document_favorites` (document_id, user_id)

---

## 7. Permission Structure

### 7.1 Module Permissions
```
documents.view              - View document listings
documents.create            - Upload new documents
documents.edit              - Edit own documents
documents.edit_any          - Edit any document
documents.delete            - Delete own documents
documents.delete_any        - Delete any document
documents.share             - Share documents
documents.approve           - Review and approve documents
documents.publish           - Publish approved documents
documents.manage_categories - Manage categories and tags
documents.manage_folders    - Manage institutional folders
documents.view_audit        - View audit logs
documents.manage_settings   - Configure module settings
documents.manage_quotas     - Manage user storage quotas
```

### 7.2 Role Assignments (Suggested)
| Role | Permissions |
|------|-------------|
| Staff | view, create, edit, delete, share |
| Department Head | + approve, publish (department docs) |
| Administrator | + edit_any, delete_any, manage_categories, manage_folders |
| Super Admin | All permissions |

---

## 8. API Endpoints (RESTful)

### 8.1 Documents
```
GET    /documents                    - List documents
POST   /documents                    - Upload document
GET    /documents/{ulid}             - View document details
PUT    /documents/{ulid}             - Update document metadata
DELETE /documents/{ulid}             - Delete document
GET    /documents/{ulid}/download    - Download document
GET    /documents/{ulid}/preview     - Preview document (inline)
POST   /documents/{ulid}/versions    - Upload new version
GET    /documents/{ulid}/versions    - List versions
POST   /documents/{ulid}/share       - Share document
DELETE /documents/{ulid}/share/{id}  - Remove share
POST   /documents/{ulid}/favorite    - Add to favorites
DELETE /documents/{ulid}/favorite    - Remove from favorites
```

### 8.2 Folders
```
GET    /folders                      - List folders
POST   /folders                      - Create folder
GET    /folders/{ulid}               - View folder contents
PUT    /folders/{ulid}               - Update folder
DELETE /folders/{ulid}               - Delete folder
POST   /folders/{ulid}/documents     - Move document to folder
```

### 8.3 Workflow
```
POST   /documents/{ulid}/submit      - Submit for review
POST   /documents/{ulid}/approve     - Approve document
POST   /documents/{ulid}/reject      - Reject document
POST   /documents/{ulid}/publish     - Publish document
POST   /documents/{ulid}/archive     - Archive document
```

### 8.4 Public Access
```
GET    /public/documents             - List public documents
GET    /public/documents/{token}     - Access via share token
GET    /public/download/{token}      - Download via share token
```

---

## 9. Implementation Phases

### Phase 1: Core Foundation
- Database migrations and models
- Basic CRUD operations
- File upload/download
- Folder structure
- Basic permissions

### Phase 2: Organization & Search
- Category and tag management
- Search functionality
- Filtering and sorting
- Favorites

### Phase 3: Version Control
- Version tracking
- Version history UI
- Version restore

### Phase 4: Sharing & Access
- User/role sharing
- Public links
- Permission management
- Public document portal

### Phase 5: Workflow & Approval
- Document status workflow
- Approval process
- Notifications
- Publication control

### Phase 6: Audit & Retention
- Audit trail implementation
- Retention policies
- Expiration handling
- Compliance reports

### Phase 7: Advanced Features
- Full-text search (Elasticsearch/Meilisearch)
- Document preview
- Comments/annotations
- Dashboard analytics

---

## 10. Technical Architecture

### 10.1 Directory Structure
```
app/
├── Http/Controllers/Documents/
│   ├── DocumentController.php
│   ├── FolderController.php
│   ├── ShareController.php
│   ├── ApprovalController.php
│   └── PublicDocumentController.php
├── Models/
│   ├── Document.php
│   ├── DocumentVersion.php
│   ├── DocumentFolder.php
│   ├── DocumentCategory.php
│   ├── DocumentTag.php
│   ├── DocumentShare.php
│   ├── DocumentApproval.php
│   ├── DocumentAudit.php
│   └── DocumentComment.php
├── Services/Documents/
│   ├── DocumentService.php
│   ├── DocumentStorageService.php
│   ├── DocumentShareService.php
│   ├── DocumentApprovalService.php
│   └── DocumentSearchService.php
├── Policies/
│   └── DocumentPolicy.php
└── Jobs/Documents/
    ├── ProcessDocumentUpload.php
    ├── GenerateDocumentPreview.php
    └── CleanupExpiredDocuments.php

config/
└── documents.php

database/migrations/
├── create_document_categories_table.php
├── create_document_folders_table.php
├── create_documents_table.php
├── create_document_versions_table.php
├── create_document_tags_table.php
├── create_document_shares_table.php
├── create_document_approvals_table.php
├── create_document_audits_table.php
├── create_document_comments_table.php
└── add_document_permissions.php

resources/views/documents/
├── index.blade.php
├── show.blade.php
├── create.blade.php
├── edit.blade.php
├── folders/
├── public/
└── partials/

routes/documents/
└── documents.php
```

### 10.2 Storage Configuration
```php
// config/filesystems.php
'documents' => [
    'driver' => 'local',
    'root' => storage_path('app/documents'),
    'visibility' => 'private',
],
```

### 10.3 Configuration File
```php
// config/documents.php
return [
    'storage' => [
        'disk' => env('DOCUMENTS_DISK', 'documents'),
        'max_file_size_mb' => 50,
    ],
    'quotas' => [
        'default_mb' => 500,
        'admin_mb' => 2000,
    ],
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'],
    'retention' => [
        'default_days' => 365 * 7, // 7 years
        'grace_period_days' => 30,
    ],
    'approval' => [
        'require_approval' => true,
        'review_deadline_days' => 7,
    ],
    'public' => [
        'enabled' => true,
        'link_expiry_days' => 30,
    ],
];
```

---

## 11. Success Metrics

| Metric | Target |
|--------|--------|
| Document upload success rate | > 99% |
| Average search response time | < 1 second |
| User adoption rate | 80% of staff within 3 months |
| Storage utilization efficiency | < 80% of allocated storage |
| Audit compliance | 100% of actions logged |

---

## 12. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Storage overflow | High | Quota enforcement, compression, cloud storage option |
| Performance degradation | Medium | Pagination, caching, async processing |
| Data loss | Critical | Regular backups, soft deletes, version history |
| Unauthorized access | Critical | Permission checks, audit logging, encryption |
| User adoption resistance | Medium | Training, intuitive UI, migration assistance |

---

## 13. Acceptance Criteria

### 13.1 Core Functionality
- [ ] Users can upload, view, download, and delete documents
- [ ] Folder hierarchy works correctly with proper permissions
- [ ] Version control tracks all document changes
- [ ] Search returns relevant results within 1 second

### 13.2 Sharing & Access
- [ ] Documents can be shared with users, roles, and departments
- [ ] Public links work without authentication
- [ ] Permission inheritance works correctly in folder hierarchy

### 13.3 Workflow
- [ ] Approval workflow progresses through all states
- [ ] Notifications sent at each workflow stage
- [ ] Only approved documents can be published

### 13.4 Audit & Retention
- [ ] All document actions are logged
- [ ] Expired documents are auto-archived
- [ ] Audit reports can be exported

---

## 14. User Stories

### 14.1 Document Upload & Management

#### US-001: Upload Single Document
**As a** staff member
**I want to** upload a document to my personal repository
**So that** I can store and organize my work files securely

**Acceptance Criteria:**
- User can select file via file browser or drag-and-drop
- System validates file type and size before upload
- User must provide title (required) and can add description, tags, category (optional)
- User can select destination folder
- Progress indicator shows upload status
- Success message displays with link to view document
- Document appears in selected folder immediately after upload

**Business Rules:**
- Maximum file size: 50MB (configurable)
- Allowed types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, TXT
- Upload fails if user quota exceeded
- Duplicate filename allowed (system generates unique identifier)

---

#### US-002: Upload Multiple Documents
**As a** staff member
**I want to** upload multiple documents at once
**So that** I can save time when adding many files

**Acceptance Criteria:**
- User can select multiple files or drag multiple files to upload zone
- Batch metadata form allows shared title prefix, category, and tags
- Individual progress bars for each file
- Failed uploads don't stop other uploads
- Summary shows successful and failed uploads
- Option to retry failed uploads

---

#### US-003: Create Folder
**As a** staff member
**I want to** create folders in my personal repository
**So that** I can organize my documents logically

**Acceptance Criteria:**
- User can create folder at root level or within existing folder
- Folder name is required (max 100 characters)
- Description is optional
- Folder appears immediately in navigation tree
- User can create nested folders (unlimited depth)

**Business Rules:**
- Folder names must be unique within same parent folder
- Special characters restricted: / \ : * ? " < > |
- Cannot create folders named "." or ".."

---

#### US-004: Move Document to Folder
**As a** staff member
**I want to** move documents between folders
**So that** I can reorganize my document structure

**Acceptance Criteria:**
- User can drag document to folder in navigation tree
- User can select "Move to..." from document context menu
- Modal shows folder tree for destination selection
- Document appears in new location immediately
- Breadcrumb updates to reflect new location
- Audit log records the move action

**Business Rules:**
- User can only move documents they own or have "manage" permission on
- Moving to shared folder grants folder's default permissions
- Cannot move institutional documents without proper permission

---

#### US-005: Delete Document
**As a** staff member
**I want to** delete documents I no longer need
**So that** I can manage my storage quota

**Acceptance Criteria:**
- User can delete via context menu or delete button
- Confirmation dialog warns about deletion
- Document moved to "Trash" (soft delete)
- Document removed from all views except Trash
- Storage quota updated after 30-day retention
- User can restore from Trash within 30 days

**Business Rules:**
- Only owner or users with "delete" permission can delete
- Documents under legal hold cannot be deleted
- Shared documents: sharing removed, original deleted
- Published documents require unpublish before delete

---

### 14.2 Document Viewing & Search

#### US-006: View Document Details
**As a** staff member
**I want to** view document details and preview content
**So that** I can review documents without downloading

**Acceptance Criteria:**
- Click document opens detail/preview page
- PDF and images display inline preview
- Office documents show download option (preview in future phase)
- Metadata panel shows: title, description, owner, dates, size, version
- Tags displayed as clickable chips
- Version history accessible from sidebar
- Comments section visible (if enabled)
- Share status indicator shows who has access

---

#### US-007: Search Documents
**As a** staff member
**I want to** search for documents by keyword
**So that** I can quickly find what I need

**Acceptance Criteria:**
- Search bar in header, accessible from any page
- Search queries title, description, tags, and content (Phase 7)
- Results show: title, folder path, date, owner, relevance
- Click result navigates to document
- Search within specific folder option
- Recent searches saved (last 10)

**Business Rules:**
- Results filtered by user's access permissions
- Archived documents excluded unless filter enabled
- Maximum 100 results per page
- Empty search shows recent documents

---

#### US-008: Filter Documents
**As a** staff member
**I want to** filter document list by various criteria
**So that** I can narrow down to relevant documents

**Acceptance Criteria:**
- Filter panel with options:
  - File type (PDF, Word, Excel, etc.)
  - Category (dropdown)
  - Tags (multi-select)
  - Date range (uploaded/modified)
  - Owner (for shared documents)
  - Status (draft, published, archived)
- Filters combinable (AND logic)
- Active filters shown as removable chips
- "Clear all filters" button
- Filter state preserved in URL (shareable)

---

#### US-009: Mark Document as Favorite
**As a** staff member
**I want to** mark frequently used documents as favorites
**So that** I can access them quickly

**Acceptance Criteria:**
- Star icon on document row and detail page
- Click toggles favorite status
- "Favorites" section on dashboard
- "Favorites" filter in document browser
- Favorites persist across sessions

---

### 14.3 Version Control

#### US-010: Upload New Version
**As a** document owner
**I want to** upload a new version of an existing document
**So that** I can update content while preserving history

**Acceptance Criteria:**
- "Upload new version" button on document detail page
- Version number auto-incremented (1.0 → 1.1 → 2.0)
- User can choose major (x.0) or minor (x.y) version
- Version notes field (optional but recommended)
- Old version preserved and accessible
- New version becomes current
- Notifications sent to users with document shared

**Business Rules:**
- Only owner or users with "edit" permission can version
- Published documents: new version starts as draft
- Version number format: Major.Minor (e.g., 2.3)
- Major version: significant content change
- Minor version: corrections, formatting

---

#### US-011: View Version History
**As a** staff member
**I want to** see the history of document versions
**So that** I can track changes over time

**Acceptance Criteria:**
- Version history panel in document detail view
- List shows: version number, date, uploader, notes
- Current version highlighted
- Click version to preview that version
- Download any version
- Compare button between two versions (future)

---

#### US-012: Restore Previous Version
**As a** document owner
**I want to** restore a previous version as current
**So that** I can revert unwanted changes

**Acceptance Criteria:**
- "Restore this version" button on version preview
- Confirmation dialog with warning
- Restored version becomes new version (not overwrite)
- Version notes auto-filled: "Restored from version X.Y"
- Audit log records restore action

**Business Rules:**
- Cannot restore if document is under review
- Restore creates new version number
- Original version history preserved

---

### 14.4 Sharing & Collaboration

#### US-013: Share Document with Users
**As a** document owner
**I want to** share a document with specific colleagues
**So that** they can access it for collaboration

**Acceptance Criteria:**
- "Share" button opens sharing modal
- User search/select field (autocomplete)
- Permission level dropdown: View, Comment, Edit, Manage
- Optional message to recipients
- Share button confirms action
- Recipients receive notification (email + in-app)
- Shared users appear in "Shared with" list

**Business Rules:**
- Cannot share with self
- Cannot grant higher permission than own level
- Existing share updated if same user selected
- Maximum 50 individual user shares per document

---

#### US-014: Share Document with Role/Department
**As a** document owner
**I want to** share a document with an entire role or department
**So that** I don't have to share individually

**Acceptance Criteria:**
- Role selector shows available roles (Teachers, Admin, etc.)
- Department selector shows organizational units
- Permission level applies to all members
- New members automatically gain access
- Removed members automatically lose access

**Business Rules:**
- Role/department share doesn't notify individuals
- Individual share overrides role/department permission
- Cannot share institutional docs with lower departments

---

#### US-015: Generate Public Link
**As a** document owner
**I want to** create a public link to share externally
**So that** non-staff can access specific documents

**Acceptance Criteria:**
- "Create public link" button in share modal
- Options:
  - Expiration date (required, default 30 days)
  - Password protection (optional)
  - Allow download (checkbox)
  - View limit (optional, e.g., max 100 views)
- Generated link displayed with copy button
- QR code option for easy mobile access
- Active links listed with disable option

**Business Rules:**
- Only published documents can have public links
- Maximum 5 active public links per document
- Link access logged in audit trail
- Expired links return "Link expired" page

---

#### US-016: View Documents Shared with Me
**As a** staff member
**I want to** see all documents shared with me
**So that** I can access collaborative content

**Acceptance Criteria:**
- "Shared with me" section in navigation
- List shows documents grouped by sharer
- Permission level displayed for each document
- Filter by permission level
- Sort by date shared, name, owner
- Remove self from share (stop receiving updates)

---

### 14.5 Workflow & Approval

#### US-017: Submit Document for Review
**As a** document author
**I want to** submit my document for approval
**So that** it can be reviewed before publication

**Acceptance Criteria:**
- "Submit for review" button on draft documents
- Select reviewer(s) or review group
- Add submission notes
- Set review deadline (optional)
- Document status changes to "Pending Review"
- Reviewer(s) notified via email and in-app

**Business Rules:**
- Only draft documents can be submitted
- Document locked for editing during review
- Author can withdraw submission before review starts
- Institutional documents require designated reviewers

---

#### US-018: Review Document
**As a** reviewer
**I want to** review submitted documents
**So that** I can approve or request changes

**Acceptance Criteria:**
- "Pending Reviews" section shows assigned reviews
- Review interface shows document with annotation tools
- Actions: Approve, Reject, Request Revision
- Comment field required for Reject/Revision
- Decision notification sent to author
- Review recorded in approval history

**Business Rules:**
- Reviewer cannot review own documents
- Approval deadline: 7 days default (configurable)
- Overdue reviews escalated to admin
- Single reviewer: their decision is final
- Multiple reviewers: configurable (all must approve / majority)

---

#### US-019: Publish Document
**As a** document owner (or publisher)
**I want to** publish an approved document
**So that** it becomes available to the intended audience

**Acceptance Criteria:**
- "Publish" button appears on approved documents
- Select visibility: Internal, Specific Roles, Public
- Set effective date (immediate or scheduled)
- Optionally create announcement
- Document status changes to "Published"
- Document appears in appropriate listings

**Business Rules:**
- Only approved documents can be published
- Institutional documents require "publish" permission
- Publishing removes draft watermark/indicator
- Re-publishing after edit requires re-approval

---

#### US-020: Archive Document
**As a** document owner
**I want to** archive outdated documents
**So that** they're preserved but not cluttering active views

**Acceptance Criteria:**
- "Archive" button on published/expired documents
- Confirmation dialog
- Document moved to "Archived" status
- Removed from active document listings
- Still accessible via "Archived" filter
- Still searchable (with archive filter)
- Can be unarchived if needed

**Business Rules:**
- Archived documents don't count toward storage quota
- Public links to archived docs show "No longer available"
- Shares preserved but access shows "archived" indicator

---

### 14.6 Administration

#### US-021: Manage Categories
**As an** administrator
**I want to** manage document categories
**So that** documents can be properly classified

**Acceptance Criteria:**
- Category management page (admin only)
- Create category: name, description, icon, color
- Edit existing categories
- Deactivate category (hide from new uploads)
- Set default retention period per category
- Reorder categories (drag-and-drop)
- View document count per category

**Business Rules:**
- Cannot delete category with existing documents
- Deactivated categories still show on existing documents
- Category name must be unique
- Maximum 50 active categories

---

#### US-022: Manage Tags
**As an** administrator
**I want to** manage the tag library
**So that** tagging remains consistent

**Acceptance Criteria:**
- Tag management page (admin only)
- View all tags with usage counts
- Merge duplicate/similar tags
- Delete unused tags
- Create official tags with descriptions
- Restrict tag creation to official tags only (optional)

---

#### US-023: Manage User Quotas
**As an** administrator
**I want to** set storage quotas for users
**So that** storage is fairly allocated

**Acceptance Criteria:**
- User quota management page
- Set default quota per role
- Override quota for specific users
- View current usage per user
- Warning threshold setting (e.g., 80%)
- Bulk update quotas

**Business Rules:**
- Reducing quota below current usage: warning only
- Users at quota cannot upload (clear error message)
- Administrators exempt from quotas (or high limit)

---

#### US-024: View Audit Logs
**As an** administrator
**I want to** view document activity logs
**So that** I can monitor usage and investigate issues

**Acceptance Criteria:**
- Audit log viewer with filters:
  - Date range
  - User
  - Document
  - Action type
  - IP address
- Export to CSV/Excel
- Pagination for large result sets
- Detail view shows full context

---

### 14.7 Public Access

#### US-025: Browse Public Documents
**As a** public visitor (no login)
**I want to** browse publicly available documents
**So that** I can access school information

**Acceptance Criteria:**
- Public documents page (no authentication)
- Browse by category
- Search within public documents
- View document details
- Download permitted documents
- No editing/commenting capabilities

**Business Rules:**
- Only documents with "public" visibility appear
- Download only if "allow download" enabled
- Rate limiting to prevent abuse
- All access logged (IP, timestamp)

---

#### US-026: Access via Public Link
**As a** link recipient
**I want to** access a document via shared link
**So that** I can view content shared with me

**Acceptance Criteria:**
- Link opens document preview page
- Password prompt if protected
- Download button if enabled
- Shows expiration date
- Shows remaining views if limited
- Professional branded page design

**Business Rules:**
- Invalid/expired link: friendly error page
- Wrong password: 3 attempts then locked
- View count incremented on access
- Mobile-responsive design

---

## 15. UI Wireframes (Text-Based Descriptions)

### 15.1 Main Dashboard

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Logo] Heritage DMS          [Search Bar________________] [🔔] [👤 User ▼] │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  📊 Storage Usage                              [+ Upload Document]   │   │
│  │  ████████████░░░░░░░░░░░░░  234 MB of 500 MB (47%)                  │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌──────────────────────────────┐  ┌──────────────────────────────┐        │
│  │  📁 Quick Access             │  │  ⏳ Pending Reviews (3)       │        │
│  │  ├─ My Documents             │  │  ┌────────────────────────┐  │        │
│  │  ├─ Shared with Me           │  │  │ Policy Update v2.docx  │  │        │
│  │  ├─ Recent                   │  │  │ Due: Dec 15, 2024      │  │        │
│  │  ├─ Favorites ⭐             │  │  └────────────────────────┘  │        │
│  │  └─ Trash 🗑️                 │  │  ┌────────────────────────┐  │        │
│  └──────────────────────────────┘  │  │ Budget Report Q4.xlsx  │  │        │
│                                     │  │ Due: Dec 12, 2024      │  │        │
│  ┌──────────────────────────────┐  │  └────────────────────────┘  │        │
│  │  📄 Recent Documents         │  └──────────────────────────────┘        │
│  │  ┌─────────────────────────────────────────────────────────────┐       │
│  │  │ 📕 Annual Report 2024.pdf          Modified: 2 hours ago   │       │
│  │  │ 📗 Staff Meeting Notes.docx        Modified: Yesterday     │       │
│  │  │ 📊 Enrollment Stats.xlsx           Modified: Dec 5, 2024   │       │
│  │  │ 📘 HR Policy Manual.pdf            Modified: Dec 3, 2024   │       │
│  │  │ 📄 Leave Request Form.docx         Modified: Dec 1, 2024   │       │
│  │  └─────────────────────────────────────────────────────────────┘       │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │  📤 Shared with Me (12 documents)                        [View All]  │  │
│  │  ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐     │  │
│  │  │ 📕 Curriculum    │ │ 📗 Budget Draft  │ │ 📘 Event Plan   │     │  │
│  │  │ Guide 2025.pdf   │ │ 2025.xlsx        │ │ Dec.docx        │     │  │
│  │  │ From: J. Smith   │ │ From: Finance    │ │ From: Admin     │     │  │
│  │  │ Can: Edit        │ │ Can: View        │ │ Can: Comment    │     │  │
│  │  └──────────────────┘ └──────────────────┘ └──────────────────┘     │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.2 Document Browser (Dual-Pane)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Logo] Heritage DMS          [Search Bar________________] [🔔] [👤 User ▼] │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  📁 My Documents > Policies > HR                    [+ New Folder] [⬆ Upload]│
│                                                                             │
│  ┌────────────────────┬─────────────────────────────────────────────────┐  │
│  │  📁 FOLDERS        │  [Grid ▢] [List ≡]  Sort: [Name ▼]  [Filter 🔽] │  │
│  │  ──────────────    │                                                  │  │
│  │  ▼ 📁 My Documents │  ☐ Name                    Modified    Size     │  │
│  │    ├─ 📁 Policies  │  ─────────────────────────────────────────────  │  │
│  │    │  ├─ 📁 HR  ◀──│  ☐ 📕 Leave Policy.pdf     Dec 5      245 KB   │  │
│  │    │  ├─ 📁 Finance│  ☐ 📕 Code of Conduct.pdf  Dec 3      512 KB   │  │
│  │    │  └─ 📁 IT     │  ☐ 📗 Dress Code.docx      Nov 28     128 KB   │  │
│  │    ├─ 📁 Reports   │  ☐ 📕 Grievance Process.pdf Nov 25    342 KB   │  │
│  │    ├─ 📁 Templates │  ☐ 📗 Onboarding Guide.docx Nov 20    856 KB   │  │
│  │    └─ 📁 Archive   │                                                  │  │
│  │                    │                                                  │  │
│  │  ▶ 📁 Shared       │  Showing 5 of 5 documents                       │  │
│  │  ▶ 📁 Institutional│                                                  │  │
│  │                    │                                                  │  │
│  └────────────────────┴─────────────────────────────────────────────────┘  │
│                                                                             │
│  [Selected: 0 documents]              [Move] [Share] [Download] [Delete]   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.3 Document Detail/Preview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Logo] Heritage DMS          [Search Bar________________] [🔔] [👤 User ▼] │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ← Back to Documents    Leave Policy.pdf                                    │
│                                                                             │
│  ┌───────────────────────────────────────────────┬──────────────────────┐  │
│  │                                               │  📋 DETAILS          │  │
│  │     ┌─────────────────────────────────┐      │                      │  │
│  │     │                                 │      │  Title: Leave Policy │  │
│  │     │                                 │      │  Owner: John Smith   │  │
│  │     │         PDF PREVIEW             │      │  Created: Nov 15     │  │
│  │     │                                 │      │  Modified: Dec 5     │  │
│  │     │      [Page 1 of 12]             │      │  Size: 245 KB        │  │
│  │     │                                 │      │  Version: 2.1        │  │
│  │     │                                 │      │  Status: Published   │  │
│  │     │                                 │      │                      │  │
│  │     │                                 │      │  📁 Folder:          │  │
│  │     │                                 │      │  Policies > HR       │  │
│  │     │                                 │      │                      │  │
│  │     └─────────────────────────────────┘      │  🏷️ Tags:            │  │
│  │                                               │  [HR] [Policy] [2024]│  │
│  │     [◀ Prev] [Page 1 ▼] [Next ▶]             │                      │  │
│  │                                               │  📝 Description:     │  │
│  │  ─────────────────────────────────────────── │  Official leave      │  │
│  │  [⬇ Download] [🔗 Share] [✏️ Edit] [📋 Copy] │  policy for all      │  │
│  │  [⭐ Favorite] [🗑️ Delete] [📜 Versions]     │  staff members...    │  │
│  │                                               │                      │  │
│  └───────────────────────────────────────────────┴──────────────────────┘  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  📜 VERSION HISTORY                                      [View All] │   │
│  │  ┌─────────────────────────────────────────────────────────────┐   │   │
│  │  │ v2.1 (Current) - Dec 5, 2024 - John Smith                   │   │   │
│  │  │ "Updated section 3.2 per HR feedback"              [Restore]│   │   │
│  │  ├─────────────────────────────────────────────────────────────┤   │   │
│  │  │ v2.0 - Nov 20, 2024 - John Smith                            │   │   │
│  │  │ "Major revision for 2024 compliance"               [Restore]│   │   │
│  │  ├─────────────────────────────────────────────────────────────┤   │   │
│  │  │ v1.0 - Jan 10, 2023 - Jane Doe                              │   │   │
│  │  │ "Initial version"                                  [Restore]│   │   │
│  │  └─────────────────────────────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  👥 SHARED WITH                                          [+ Share]  │   │
│  │  • HR Department (View)                                             │   │
│  │  • All Staff (View)                                                 │   │
│  │  • 🔗 Public Link: Active (expires Dec 31)              [Manage]   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.4 Upload Modal

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│    ┌─────────────────────────────────────────────────────────────────┐     │
│    │                        UPLOAD DOCUMENTS                     [X] │     │
│    ├─────────────────────────────────────────────────────────────────┤     │
│    │                                                                 │     │
│    │    ┌─────────────────────────────────────────────────────┐     │     │
│    │    │                                                     │     │     │
│    │    │           📤 Drag & Drop Files Here                │     │     │
│    │    │                    or                               │     │     │
│    │    │              [Browse Files]                         │     │     │
│    │    │                                                     │     │     │
│    │    │    Supported: PDF, DOC, DOCX, XLS, XLSX, PPT,      │     │     │
│    │    │               PPTX, JPG, PNG, TXT (Max 50MB)        │     │     │
│    │    │                                                     │     │     │
│    │    └─────────────────────────────────────────────────────┘     │     │
│    │                                                                 │     │
│    │    📄 Selected Files (2):                                       │     │
│    │    ┌─────────────────────────────────────────────────────┐     │     │
│    │    │ 📕 Annual_Report.pdf (2.3 MB)              [Remove] │     │     │
│    │    │ ████████████████████████████████░░░░  85%          │     │     │
│    │    ├─────────────────────────────────────────────────────┤     │     │
│    │    │ 📗 Budget_Draft.xlsx (456 KB)              [Remove] │     │     │
│    │    │ ████████████████████████████████████████  Complete ✓│     │     │
│    │    └─────────────────────────────────────────────────────┘     │     │
│    │                                                                 │     │
│    │    ─────────────────────────────────────────────────────────   │     │
│    │                                                                 │     │
│    │    Title*:        [Annual Report 2024__________________]       │     │
│    │                                                                 │     │
│    │    Description:   [Optional description of the document]       │     │
│    │                   [_________________________________________]  │     │
│    │                                                                 │     │
│    │    Category:      [-- Select Category -- ▼]                    │     │
│    │                                                                 │     │
│    │    Tags:          [HR] [Finance] [+ Add Tag]                   │     │
│    │                                                                 │     │
│    │    Folder:        📁 My Documents > Reports [Change]           │     │
│    │                                                                 │     │
│    │    ─────────────────────────────────────────────────────────   │     │
│    │                                                                 │     │
│    │    ☐ Submit for review after upload                            │     │
│    │    ☐ Notify me when upload completes                           │     │
│    │                                                                 │     │
│    │                              [Cancel]  [Upload Documents]      │     │
│    │                                                                 │     │
│    └─────────────────────────────────────────────────────────────────┘     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.5 Share Modal

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│    ┌─────────────────────────────────────────────────────────────────┐     │
│    │                    SHARE "Leave Policy.pdf"                 [X] │     │
│    ├─────────────────────────────────────────────────────────────────┤     │
│    │                                                                 │     │
│    │    Share with:                                                  │     │
│    │    ┌────────────────────────────────────────────────┐          │     │
│    │    │ [👤 Search users, roles, or departments...   ] │          │     │
│    │    └────────────────────────────────────────────────┘          │     │
│    │                                                                 │     │
│    │    ┌────────────────────────────────────────────────────────┐  │     │
│    │    │  PEOPLE & GROUPS                                       │  │     │
│    │    │  ┌──────────────────────────────────────────────────┐  │  │     │
│    │    │  │ 👤 Jane Doe (jane.doe@school.edu)    [View ▼] [X]│  │  │     │
│    │    │  ├──────────────────────────────────────────────────┤  │  │     │
│    │    │  │ 👥 HR Department                     [Edit ▼] [X]│  │  │     │
│    │    │  ├──────────────────────────────────────────────────┤  │  │     │
│    │    │  │ 🎭 All Teachers (Role)               [View ▼] [X]│  │  │     │
│    │    │  └──────────────────────────────────────────────────┘  │  │     │
│    │    └────────────────────────────────────────────────────────┘  │     │
│    │                                                                 │     │
│    │    Message (optional):                                          │     │
│    │    ┌────────────────────────────────────────────────────────┐  │     │
│    │    │ Please review the updated leave policy...              │  │     │
│    │    └────────────────────────────────────────────────────────┘  │     │
│    │                                                                 │     │
│    │    ☐ Notify recipients via email                               │     │
│    │                                                                 │     │
│    │    ═══════════════════════════════════════════════════════════ │     │
│    │                                                                 │     │
│    │    🔗 PUBLIC LINK                                               │     │
│    │                                                                 │     │
│    │    ☐ Enable public link (anyone with link can access)          │     │
│    │                                                                 │     │
│    │    Link expires: [30 days ▼]                                   │     │
│    │    ☐ Password protect                                          │     │
│    │    ☐ Allow download                                            │     │
│    │    Max views: [Unlimited ▼]                                    │     │
│    │                                                                 │     │
│    │    Generated Link:                                              │     │
│    │    ┌────────────────────────────────────────────────────────┐  │     │
│    │    │ https://school.edu/docs/p/a3f8c2... │ [📋 Copy] [QR]  │  │     │
│    │    └────────────────────────────────────────────────────────┘  │     │
│    │                                                                 │     │
│    │                                    [Cancel]  [Save & Share]    │     │
│    │                                                                 │     │
│    └─────────────────────────────────────────────────────────────────┘     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.6 Review/Approval Interface

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Logo] Heritage DMS          [Search Bar________________] [🔔] [👤 User ▼] │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ← Back to Reviews    📋 REVIEW: "Staff Handbook 2025.pdf"                  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Submitted by: John Smith | Date: Dec 5, 2024 | Due: Dec 12, 2024   │   │
│  │  Version: 1.0 | Status: 🟡 Under Review                              │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌───────────────────────────────────────────┬──────────────────────────┐  │
│  │                                           │  📝 REVIEW NOTES         │  │
│  │    ┌─────────────────────────────────┐   │                          │  │
│  │    │                                 │   │  Submission Notes:       │  │
│  │    │                                 │   │  "Updated handbook for   │  │
│  │    │       DOCUMENT PREVIEW          │   │   2025 with new HR       │  │
│  │    │                                 │   │   policies and benefits" │  │
│  │    │      [Page 1 of 45]             │   │                          │  │
│  │    │                                 │   │  ─────────────────────   │  │
│  │    │   [Click to add annotation]     │   │                          │  │
│  │    │                                 │   │  Your Comments:          │  │
│  │    │                                 │   │  ┌────────────────────┐  │  │
│  │    └─────────────────────────────────┘   │  │ Add your review    │  │  │
│  │                                           │  │ comments here...   │  │  │
│  │    Annotations (2):                       │  │                    │  │  │
│  │    • Page 5: "Clarify leave accrual"     │  │                    │  │  │
│  │    • Page 12: "Update salary figures"    │  │  └────────────────────┘  │  │
│  │                                           │                          │  │
│  │    [+ Add Annotation]                     │                          │  │
│  │                                           │                          │  │
│  └───────────────────────────────────────────┴──────────────────────────┘  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                           REVIEW DECISION                            │   │
│  │                                                                      │   │
│  │   [✅ APPROVE]    [🔄 REQUEST REVISION]    [❌ REJECT]              │   │
│  │                                                                      │   │
│  │   Decision comment (required for Revision/Reject):                   │   │
│  │   ┌──────────────────────────────────────────────────────────────┐  │   │
│  │   │                                                              │  │   │
│  │   └──────────────────────────────────────────────────────────────┘  │   │
│  │                                                                      │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.7 Admin - Audit Log Viewer

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Logo] Heritage DMS          [Search Bar________________] [🔔] [👤 Admin▼] │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ⚙️ Admin > Audit Logs                                    [Export CSV]     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  FILTERS                                                            │   │
│  │  Date Range: [Dec 1, 2024] to [Dec 8, 2024]  User: [All Users ▼]   │   │
│  │  Action: [All Actions ▼]  Document: [Search document...]            │   │
│  │                                                    [Apply] [Clear]  │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Showing 1-25 of 1,234 entries                    [< Prev] [Next >] │   │
│  ├─────────────────────────────────────────────────────────────────────┤   │
│  │  Timestamp          User           Action       Document            │   │
│  │  ──────────────────────────────────────────────────────────────────│   │
│  │  Dec 8, 10:45 AM    John Smith     Downloaded   Leave Policy.pdf    │   │
│  │  Dec 8, 10:42 AM    Jane Doe       Viewed       Budget Report.xlsx  │   │
│  │  Dec 8, 10:30 AM    Admin          Published    Staff Handbook.pdf  │   │
│  │  Dec 8, 10:15 AM    John Smith     Shared       Leave Policy.pdf    │   │
│  │  Dec 8, 09:55 AM    Mike Brown     Uploaded     New Form.docx       │   │
│  │  Dec 8, 09:30 AM    Jane Doe       Approved     Budget Report.xlsx  │   │
│  │  Dec 8, 09:15 AM    System         Archived     Old Policy.pdf      │   │
│  │  Dec 7, 04:30 PM    Admin          Deleted      Draft.docx          │   │
│  │  ...                                                                │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  Click any row for details                                                  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  SELECTED ENTRY DETAILS                                             │   │
│  │  ───────────────────────────────────────────────────────────────── │   │
│  │  Action: Downloaded                                                 │   │
│  │  Document: Leave Policy.pdf (ID: doc_abc123)                        │   │
│  │  User: John Smith (john.smith@school.edu)                           │   │
│  │  Timestamp: December 8, 2024 10:45:32 AM                            │   │
│  │  IP Address: 192.168.1.100                                          │   │
│  │  User Agent: Chrome 120.0 / Windows 10                              │   │
│  │  Additional Data: { "version": "2.1", "file_size": "245KB" }        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 15.8 Public Documents Portal

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│     [School Logo]  HERITAGE JUNIOR SECONDARY SCHOOL                        │
│                    Public Documents Portal                                  │
│                                                                             │
│     [Search documents...________________________________] [🔍]              │
│                                                                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  BROWSE BY CATEGORY                                                         │
│                                                                             │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │ 📋 Policies │ │ 📝 Forms    │ │ 📅 Calendar │ │ 📰 Notices  │          │
│  │  (12 docs)  │ │  (8 docs)   │ │  (4 docs)   │ │  (15 docs)  │          │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘          │
│                                                                             │
│  ═══════════════════════════════════════════════════════════════════════   │
│                                                                             │
│  📌 FEATURED DOCUMENTS                                                      │
│                                                                             │
│  ┌────────────────────────────────────────────────────────────────────┐    │
│  │ 📕 Student Handbook 2025                              [Download]   │    │
│  │ Everything students and parents need to know about our school      │    │
│  │ Updated: Dec 1, 2024 | PDF | 2.5 MB                                │    │
│  ├────────────────────────────────────────────────────────────────────┤    │
│  │ 📗 Admission Application Form                          [Download]   │    │
│  │ Application form for new student enrollment                        │    │
│  │ Updated: Nov 15, 2024 | PDF | 156 KB                               │    │
│  ├────────────────────────────────────────────────────────────────────┤    │
│  │ 📘 Academic Calendar 2025                              [Download]   │    │
│  │ Important dates for the 2025 academic year                         │    │
│  │ Updated: Nov 20, 2024 | PDF | 89 KB                                │    │
│  └────────────────────────────────────────────────────────────────────┘    │
│                                                                             │
│  RECENT UPDATES                                                             │
│                                                                             │
│  • Dec 5: Fee Structure 2025 published                                     │
│  • Dec 3: Updated School Rules document                                    │
│  • Dec 1: New Sports Program schedule available                            │
│                                                                             │
│  ───────────────────────────────────────────────────────────────────────   │
│                                                                             │
│  [Staff Login]                           © 2024 Heritage Junior Secondary  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 16. Database Schema (Detailed)

### 16.1 document_categories

```sql
CREATE TABLE document_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    icon VARCHAR(50) NULL DEFAULT 'folder',
    color VARCHAR(7) NULL DEFAULT '#6c757d',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    retention_days INT UNSIGNED NULL DEFAULT 2555,  -- 7 years default
    requires_approval BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (parent_id) REFERENCES document_categories(id) ON DELETE SET NULL,
    INDEX idx_categories_slug (slug),
    INDEX idx_categories_parent (parent_id),
    INDEX idx_categories_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.2 document_folders

```sql
CREATE TABLE document_folders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ulid CHAR(26) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    repository_type ENUM('institutional', 'personal', 'shared', 'department') NOT NULL DEFAULT 'personal',
    department_id BIGINT UNSIGNED NULL,
    visibility ENUM('private', 'internal', 'public') NOT NULL DEFAULT 'private',
    inherit_permissions BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    path VARCHAR(1000) NULL,  -- Materialized path for hierarchy queries
    depth TINYINT UNSIGNED NOT NULL DEFAULT 0,
    document_count INT UNSIGNED NOT NULL DEFAULT 0,  -- Denormalized count
    total_size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,  -- Denormalized size
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (parent_id) REFERENCES document_folders(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,

    INDEX idx_folders_ulid (ulid),
    INDEX idx_folders_owner (owner_id),
    INDEX idx_folders_parent (parent_id),
    INDEX idx_folders_type (repository_type),
    INDEX idx_folders_path (path(255)),
    INDEX idx_folders_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.3 documents

```sql
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ulid CHAR(26) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    -- File Storage
    storage_disk VARCHAR(50) NOT NULL DEFAULT 'documents',
    storage_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    extension VARCHAR(20) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    checksum_sha256 CHAR(64) NOT NULL,

    -- Organization
    folder_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    owner_id BIGINT UNSIGNED NOT NULL,

    -- Status & Workflow
    status ENUM('draft', 'pending_review', 'under_review', 'revision_required', 'approved', 'published', 'archived', 'deleted') NOT NULL DEFAULT 'draft',
    visibility ENUM('private', 'internal', 'public') NOT NULL DEFAULT 'private',

    -- Version Control
    current_version VARCHAR(10) NOT NULL DEFAULT '1.0',
    version_count INT UNSIGNED NOT NULL DEFAULT 1,

    -- Dates
    effective_date DATE NULL,
    expiry_date DATE NULL,
    published_at TIMESTAMP NULL,
    archived_at TIMESTAMP NULL,

    -- Flags
    is_featured BOOLEAN NOT NULL DEFAULT FALSE,
    is_template BOOLEAN NOT NULL DEFAULT FALSE,
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    locked_by_user_id BIGINT UNSIGNED NULL,
    locked_at TIMESTAMP NULL,

    -- Legal Hold
    legal_hold BOOLEAN NOT NULL DEFAULT FALSE,
    legal_hold_reason TEXT NULL,
    legal_hold_by_user_id BIGINT UNSIGNED NULL,
    legal_hold_at TIMESTAMP NULL,

    -- Metadata
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    view_count INT UNSIGNED NOT NULL DEFAULT 0,

    -- Full-text Search (for Phase 7)
    content_indexed_at TIMESTAMP NULL,
    content_text LONGTEXT NULL,  -- Extracted text for search

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (folder_id) REFERENCES document_folders(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (locked_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (legal_hold_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_documents_ulid (ulid),
    INDEX idx_documents_folder (folder_id),
    INDEX idx_documents_category (category_id),
    INDEX idx_documents_owner (owner_id),
    INDEX idx_documents_status (status),
    INDEX idx_documents_visibility (visibility),
    INDEX idx_documents_expiry (expiry_date),
    INDEX idx_documents_featured (is_featured),
    INDEX idx_documents_deleted (deleted_at),
    INDEX idx_documents_search (title, description(100)),
    FULLTEXT idx_documents_fulltext (title, description, content_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.4 document_versions

```sql
CREATE TABLE document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_number VARCHAR(10) NOT NULL,  -- e.g., "1.0", "1.1", "2.0"
    version_type ENUM('major', 'minor') NOT NULL DEFAULT 'minor',

    -- File Storage
    storage_disk VARCHAR(50) NOT NULL DEFAULT 'documents',
    storage_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    checksum_sha256 CHAR(64) NOT NULL,

    -- Metadata
    version_notes TEXT NULL,
    uploaded_by_user_id BIGINT UNSIGNED NOT NULL,

    -- Status
    is_current BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY uk_document_version (document_id, version_number),
    INDEX idx_versions_document (document_id),
    INDEX idx_versions_current (document_id, is_current),
    INDEX idx_versions_uploader (uploaded_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.5 document_tags

```sql
CREATE TABLE document_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    color VARCHAR(7) NULL DEFAULT '#007bff',
    is_official BOOLEAN NOT NULL DEFAULT FALSE,  -- Official tags can only be created by admins
    usage_count INT UNSIGNED NOT NULL DEFAULT 0,  -- Denormalized for performance
    created_by_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_tags_slug (slug),
    INDEX idx_tags_usage (usage_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.6 document_tag (Pivot Table)

```sql
CREATE TABLE document_tag (
    document_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    tagged_by_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,

    PRIMARY KEY (document_id, tag_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES document_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (tagged_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_document_tag_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.7 document_shares

```sql
CREATE TABLE document_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,

    -- Polymorphic share target
    shareable_type VARCHAR(50) NOT NULL,  -- 'user', 'role', 'department', 'public_link'
    shareable_id BIGINT UNSIGNED NULL,     -- NULL for public links

    -- Permission
    permission_level ENUM('view', 'comment', 'edit', 'manage') NOT NULL DEFAULT 'view',

    -- Share metadata
    shared_by_user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NULL,

    -- Public link specific
    access_token VARCHAR(64) NULL UNIQUE,  -- For public links
    password_hash VARCHAR(255) NULL,        -- Optional password protection
    allow_download BOOLEAN NOT NULL DEFAULT TRUE,
    max_views INT UNSIGNED NULL,            -- NULL = unlimited
    view_count INT UNSIGNED NOT NULL DEFAULT 0,

    -- Expiration
    expires_at TIMESTAMP NULL,

    -- Status
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    revoked_at TIMESTAMP NULL,
    revoked_by_user_id BIGINT UNSIGNED NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (revoked_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_shares_document (document_id),
    INDEX idx_shares_shareable (shareable_type, shareable_id),
    INDEX idx_shares_token (access_token),
    INDEX idx_shares_active (is_active),
    INDEX idx_shares_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.8 document_approvals

```sql
CREATE TABLE document_approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_id BIGINT UNSIGNED NOT NULL,

    -- Workflow
    workflow_step INT UNSIGNED NOT NULL DEFAULT 1,  -- For multi-level approval

    -- Reviewer
    reviewer_id BIGINT UNSIGNED NOT NULL,

    -- Status
    status ENUM('pending', 'in_review', 'approved', 'rejected', 'revision_required') NOT NULL DEFAULT 'pending',

    -- Submission
    submitted_by_user_id BIGINT UNSIGNED NOT NULL,
    submission_notes TEXT NULL,
    submitted_at TIMESTAMP NOT NULL,

    -- Review
    review_comments TEXT NULL,
    reviewed_at TIMESTAMP NULL,

    -- Deadline
    due_date DATE NULL,
    reminder_sent_at TIMESTAMP NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES document_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by_user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_approvals_document (document_id),
    INDEX idx_approvals_reviewer (reviewer_id),
    INDEX idx_approvals_status (status),
    INDEX idx_approvals_due (due_date),
    INDEX idx_approvals_pending (reviewer_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.9 document_audits

```sql
CREATE TABLE document_audits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,  -- NULL for anonymous/public access

    -- Action
    action ENUM(
        'created', 'viewed', 'downloaded', 'previewed',
        'updated', 'versioned', 'renamed', 'moved',
        'shared', 'unshared', 'permission_changed',
        'submitted', 'approved', 'rejected', 'revision_requested',
        'published', 'unpublished', 'archived', 'unarchived',
        'deleted', 'restored', 'permanently_deleted',
        'locked', 'unlocked', 'legal_hold_applied', 'legal_hold_removed',
        'commented', 'favorited', 'unfavorited',
        'public_link_created', 'public_link_accessed', 'public_link_revoked'
    ) NOT NULL,

    -- Context
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    session_id VARCHAR(100) NULL,

    -- Additional metadata (JSON)
    metadata JSON NULL,
    /*
        Examples:
        - moved: {"from_folder_id": 1, "to_folder_id": 2}
        - shared: {"shared_with": "user:5", "permission": "edit"}
        - versioned: {"old_version": "1.0", "new_version": "1.1"}
        - public_link_accessed: {"access_token": "abc...", "referer": "..."}
    */

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES document_versions(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_audits_document (document_id),
    INDEX idx_audits_user (user_id),
    INDEX idx_audits_action (action),
    INDEX idx_audits_created (created_at),
    INDEX idx_audits_user_action (user_id, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.10 document_comments

```sql
CREATE TABLE document_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,  -- For threaded replies

    -- Content
    content TEXT NOT NULL,

    -- Location (for annotations)
    page_number INT UNSIGNED NULL,
    position_x DECIMAL(5,2) NULL,  -- Percentage from left
    position_y DECIMAL(5,2) NULL,  -- Percentage from top

    -- Status
    is_resolved BOOLEAN NOT NULL DEFAULT FALSE,
    resolved_by_user_id BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,

    -- Edit tracking
    is_edited BOOLEAN NOT NULL DEFAULT FALSE,
    edited_at TIMESTAMP NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES document_versions(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES document_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_comments_document (document_id),
    INDEX idx_comments_user (user_id),
    INDEX idx_comments_parent (parent_id),
    INDEX idx_comments_resolved (is_resolved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.11 document_favorites

```sql
CREATE TABLE document_favorites (
    user_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,

    PRIMARY KEY (user_id, document_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,

    INDEX idx_favorites_document (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.12 document_folder_permissions

```sql
CREATE TABLE document_folder_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    folder_id BIGINT UNSIGNED NOT NULL,

    -- Polymorphic permission target
    permissionable_type VARCHAR(50) NOT NULL,  -- 'user', 'role', 'department'
    permissionable_id BIGINT UNSIGNED NOT NULL,

    -- Permission level
    permission_level ENUM('view', 'upload', 'edit', 'manage') NOT NULL DEFAULT 'view',

    granted_by_user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (folder_id) REFERENCES document_folders(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY uk_folder_permission (folder_id, permissionable_type, permissionable_id),
    INDEX idx_folder_perms_permissionable (permissionable_type, permissionable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.13 user_document_quotas

```sql
CREATE TABLE user_document_quotas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,

    -- Quota
    quota_bytes BIGINT UNSIGNED NOT NULL DEFAULT 524288000,  -- 500MB default
    used_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,

    -- Warnings
    warning_threshold_percent TINYINT UNSIGNED NOT NULL DEFAULT 80,
    warning_sent_at TIMESTAMP NULL,

    -- Override
    is_unlimited BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 16.14 document_retention_policies

```sql
CREATE TABLE document_retention_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,

    -- Conditions (JSON for flexibility)
    conditions JSON NOT NULL,
    /*
        Example:
        {
            "category_ids": [1, 2, 3],
            "folder_ids": [5, 6],
            "age_days_min": 365,
            "status": ["archived"]
        }
    */

    -- Action
    action ENUM('archive', 'delete', 'notify_owner') NOT NULL DEFAULT 'archive',
    retention_days INT UNSIGNED NOT NULL,
    grace_period_days INT UNSIGNED NOT NULL DEFAULT 30,

    -- Scheduling
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_run_at TIMESTAMP NULL,
    next_run_at TIMESTAMP NULL,

    created_by_user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 17. Business Rules

### 17.1 Document Upload Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-UP-001 | Maximum file size is 50MB per file (configurable) | Server-side validation |
| BR-UP-002 | Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG | MIME type + extension validation |
| BR-UP-003 | User cannot upload if storage quota is exceeded | Pre-upload quota check |
| BR-UP-004 | File checksum (SHA-256) must be calculated and stored for integrity | Post-upload processing |
| BR-UP-005 | Original filename preserved but storage uses generated unique path | Storage service |
| BR-UP-006 | Duplicate filenames allowed (system generates unique identifiers) | Database ULID |
| BR-UP-007 | Files must pass virus scan before being accessible (if enabled) | Queue job |
| BR-UP-008 | Upload to institutional folders requires appropriate permission | Policy check |

### 17.2 Folder Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-FO-001 | Folder names must be unique within the same parent folder | Database unique constraint |
| BR-FO-002 | Folder names cannot contain: / \ : * ? " < > | | Validation rule |
| BR-FO-003 | Maximum folder name length: 100 characters | Validation rule |
| BR-FO-004 | Folders cannot be named "." or ".." | Validation rule |
| BR-FO-005 | Deleting a folder moves all contents to Trash | Soft delete cascade |
| BR-FO-006 | Folder cannot be moved into its own descendant | Hierarchy validation |
| BR-FO-007 | Personal folders are created automatically for new staff | User creation hook |
| BR-FO-008 | Institutional folders can only be created by administrators | Permission check |

### 17.3 Permission Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-PE-001 | Users cannot grant permissions higher than their own | Share service validation |
| BR-PE-002 | Owner always has full permissions regardless of shares | Policy override |
| BR-PE-003 | Individual user permission overrides role/department permission | Permission resolution |
| BR-PE-004 | Child folders inherit parent permissions unless explicitly overridden | Permission inheritance |
| BR-PE-005 | Removing share does not delete document, only access | Share deletion logic |
| BR-PE-006 | Public links require document to be published first | Status validation |
| BR-PE-007 | Maximum 50 individual user shares per document | Share count limit |
| BR-PE-008 | Maximum 5 active public links per document | Link count limit |

### 17.4 Version Control Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-VE-001 | Version numbers follow Major.Minor format (e.g., 1.0, 1.1, 2.0) | Version service |
| BR-VE-002 | Minor version increments for small changes (1.0 → 1.1) | User selection or auto |
| BR-VE-003 | Major version increments for significant changes (1.5 → 2.0) | User selection |
| BR-VE-004 | Previous versions are retained and accessible | Storage preservation |
| BR-VE-005 | Restoring a version creates a new version (doesn't overwrite) | Restore logic |
| BR-VE-006 | Only current version counts toward storage quota | Quota calculation |
| BR-VE-007 | Version notes are optional but recommended for audit purposes | UI guidance |
| BR-VE-008 | Cannot create new version while document is under review | Status check |

### 17.5 Workflow Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-WF-001 | Only draft documents can be submitted for review | Status validation |
| BR-WF-002 | Document is locked for editing during review process | Lock mechanism |
| BR-WF-003 | Author can withdraw submission before review begins | Status check |
| BR-WF-004 | Reviewer cannot review their own documents | Policy check |
| BR-WF-005 | Default review deadline is 7 days (configurable) | Configuration |
| BR-WF-006 | Overdue reviews are escalated to administrator | Scheduled job |
| BR-WF-007 | Only approved documents can be published | Status validation |
| BR-WF-008 | Re-publishing edited document requires re-approval | Status reset |
| BR-WF-009 | Institutional documents require designated reviewers | Category configuration |
| BR-WF-010 | Rejection requires mandatory comment explaining reason | Form validation |

### 17.6 Expiration & Retention Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-EX-001 | Expiry date is optional but recommended for time-sensitive documents | UI guidance |
| BR-EX-002 | Expired documents are auto-archived after grace period (30 days default) | Scheduled job |
| BR-EX-003 | Owners are notified 7 days before document expires | Notification job |
| BR-EX-004 | Expired documents can be renewed by owner | Status update |
| BR-EX-005 | Documents under legal hold cannot be deleted or archived | Delete prevention |
| BR-EX-006 | Only administrators can apply or remove legal hold | Permission check |
| BR-EX-007 | Retention policies run daily at configured time | Scheduler |
| BR-EX-008 | Soft-deleted documents are permanently deleted after 30 days | Cleanup job |

### 17.7 Public Access Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-PU-001 | Public links must have an expiration date (max 365 days) | Validation |
| BR-PU-002 | Public link expiration default is 30 days | Configuration |
| BR-PU-003 | Password-protected links require minimum 8 characters | Validation |
| BR-PU-004 | Failed password attempts (3) temporarily lock the link | Rate limiting |
| BR-PU-005 | View-limited links become inactive when limit reached | Access count check |
| BR-PU-006 | All public access is logged with IP and timestamp | Audit logging |
| BR-PU-007 | Public links to archived documents show "unavailable" message | Status check |
| BR-PU-008 | Rate limiting: 100 requests per minute per IP for public endpoints | Middleware |

### 17.8 Audit Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-AU-001 | All document actions must be logged | Event listeners |
| BR-AU-002 | Audit logs cannot be modified or deleted | No update/delete operations |
| BR-AU-003 | Audit logs must include: user, action, timestamp, IP | Required fields |
| BR-AU-004 | Audit logs retained for minimum 7 years | Retention policy |
| BR-AU-005 | Sensitive actions (delete, permission change) require additional context | Metadata capture |
| BR-AU-006 | Audit log export requires administrator permission | Permission check |
| BR-AU-007 | Failed access attempts are logged | Authentication logging |

### 17.9 Storage & Quota Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-ST-001 | Default storage quota is 500MB per user | Configuration |
| BR-ST-002 | Administrators can override individual user quotas | Admin interface |
| BR-ST-003 | Users receive warning at 80% quota usage | Notification |
| BR-ST-004 | Uploads blocked when quota is 100% reached | Pre-upload check |
| BR-ST-005 | Archived documents don't count toward active quota | Quota calculation |
| BR-ST-006 | Only current version of each document counts toward quota | Quota calculation |
| BR-ST-007 | Shared documents count toward owner's quota only | Quota calculation |
| BR-ST-008 | Institutional documents have separate unlimited storage | Repository type check |

### 17.10 Search Rules

| Rule ID | Rule Description | Enforcement |
|---------|------------------|-------------|
| BR-SE-001 | Search results are filtered by user's access permissions | Query filter |
| BR-SE-002 | Archived documents excluded from search unless filter enabled | Default filter |
| BR-SE-003 | Deleted documents excluded from all searches | Soft delete scope |
| BR-SE-004 | Maximum 100 results per page | Pagination limit |
| BR-SE-005 | Empty search query returns recent documents | Default behavior |
| BR-SE-006 | Search queries are logged for analytics (anonymized) | Analytics logging |
| BR-SE-007 | Full-text content search available only after indexing | Index check |

---

*Document Version: 2.0*
*Created: December 2024*
*Last Updated: December 2024*
*Status: Requirements Complete*
