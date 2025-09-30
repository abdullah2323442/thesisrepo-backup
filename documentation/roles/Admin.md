# Admin — Capabilities (Brief)

Administrative control over core data, global groups, and performance.

Core Modules
- Areas of Interest (AOI)
- Supervisors
- Batches
- Groups (global)
- Performance Monitoring

What you can do
- AOI
  - List, create, edit, delete; bulk-create; toggle active/inactive
- Supervisors
  - Sync from API (throttled), edit thesis_limit and active status
  - Bulk-update limits; toggle AOI memberships; refresh one from API (throttled)
- Batches
  - Sync from API (throttled); activate/deactivate single/multiple
  - Compare local vs API; edit metadata; delete; activate-all/deactivate-all
- Groups (global)
  - Create group (name, batch, multi-AOI, optional pre-assigned supervisor with capacity checks)
  - Assign/remove students (cross-batch; auto-detect advisor from student API; enforce same-advisor per group)
  - Assign/unassign supervisor, co-supervisor (not equal to main), and panel members
  - Assign/unassign AOIs (multi-select)
  - Delete group(s) only if empty; automatic re-numbering of Group N labels; bulk delete supported
  - Query available supervisors filtered by AOI
- Performance
  - View dashboard; retrieve metrics/health/DB/API/security via endpoints
  - Clear performance cache; export JSON; run component tests (db, cache, storage, external API)

Useful paths
- /admin/dashboard
- /admin/areas-of-interest
- /admin/supervisors
- /admin/batches
- /admin/groups
- /admin/performance
