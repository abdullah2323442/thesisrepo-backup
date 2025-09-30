# Role Guides (Brief)

This directory provides brief, role-based capability summaries derived from the current codebase (routes, controllers, models, middleware). Use this as a quick reference for what each actor can do.

Planned files (to be added):
- Admin.md
- Advisor.md
- Supervisor.md
- Co-Supervisor.md
- PanelMember.md
- Teacher.md
- Student.md

Below is a single-page brief for all roles.

---

## Admin

Administrative control over core catalogs and global group management.

Key capabilities:
- Areas of Interest (AOI)
  - List, create, edit, delete, bulk-create; toggle active/inactive
- Supervisors
  - Sync from API (throttled), edit thesis_limit and active status, bulk limit updates, toggle AOI membership, refresh one from API
- Batches
  - Sync from API (throttled), activate/deactivate single/multiple, compare with API, edit metadata, delete, activate-all/deactivate-all
- Groups (global)
  - Create group (name, batch, multi-AOI, optional pre-assigned supervisor with capacity checks)
  - Assign/remove students (cross-batch, auto-detect advisor from external API; enforce same-advisor per group)
  - Assign/unassign supervisor, co-supervisor (not equal to main supervisor), and panel members (no capacity checks)
  - Assign/unassign areas of interest (multi-select)
  - Delete single or bulk delete (only empty groups) with automatic re-numbering of Group N labels
  - Query available supervisors filtered by AOI
- Performance
  - Dashboard with metrics and health; AJAX endpoints for metrics/DB/API/security; clear cache; export; component tests

---

## Advisor

Manages own students and advisor-created groups; runs supervisor assignment.

Key capabilities:
- Students
  - List/filter/search students from external API; view details; refresh data (clear cache)
- Groups (advisor-created; capacity 3)
  - Create groups automatically for batch, add group by name
  - Assign/unassign students (validated across advisor’s batches); assign AOIs (multi-select)
  - Remove all AOIs in batch (legacy cleanup); remove all advisor-created groups in batch
  - Excel template download; Excel upload to bulk-assign with validation and randomized group mapping
- Supervisor Assignment
  - Manual assign (AOI match + capacity; prevents co-supervisor duplication)
  - Lottery assign: AOI, ranking, combined; preview mode; unassign one or all (optional batch filter)
  - Available supervisors endpoint supports multiple AOIs and excludes group’s co-supervisor

---

## Supervisor (Main)

Owns supervised groups; manages meetings, reports, and approvals.

Key capabilities:
- Groups
  - View groups where user is main/co/panel (visibility); toggle co-supervisor meeting permissions per group
- Meetings
  - Create/view/edit for permitted groups (main by default; co-supervisor when enabled); filter; fetch group students (JSON); export group meetings PDF
- Reports
  - Create/view/edit/delete; mark under review; finalize and approve final reports (publish to public repository)
  - View inline/download student submissions (PDF/PPT/PPTX)
- Report Annotations
  - Annotate PDF submission; save draft; send feedback notifications; view annotation history

---

## Co-Supervisor

Reviewer/co-manager for groups where assigned as co-supervisor.

Key capabilities:
- Meetings
  - Create/edit meetings only when main supervisor grants permission on the group
- Reports
  - View details and submissions; mark under review; view inline/download submissions
- Report Annotations
  - Annotate, save draft, send feedback; view history

---

## Panel Member

Reviewer role on assigned groups.

Key capabilities:
- Reports
  - View details and submissions; mark under review; view inline/download submissions
- Report Annotations
  - Annotate, save draft, send feedback; view history

---

## Teacher

Umbrella faculty access; can act within supervisor/co-supervisor/panel contexts where applicable.

Key capabilities:
- Dashboard access to faculty panels (supervisor, co-supervisor, panel member)
- Report comments (as main supervisor)
  - Add comments to reports for own supervised groups; students notified

---

## Student

End-user thesis participant with group and report access.

Key capabilities:
- Dashboard and meetings
  - View group details, AOIs, supervisor info; view group meetings and history
- Reports and submissions
  - Reports index/show with comments and approval badges; create/edit/delete/download own submission; file types: PDF/PPT/PPTX (≤ 20MB)
- Annotations (read-only)
  - View annotation history and download annotated sessions where available
- Notifications
  - In-app bell dropdown (latest 10) and full history; mark one/all read

---

Links
- Full system features: ../FEATURES_GUIDE.md
- Algorithm details: ../SUPERVISOR_ASSIGNMENT_ALGORITHM.md
- Performance: ../PERFORMANCE_MONITORING_GUIDE.md (if available)
