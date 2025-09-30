# Advisor — Capabilities (Brief)

Manages own students and advisor-created groups; assigns supervisors.

Core Modules
- Students
- Groups (advisor-created; capacity 3)
- Supervisor Assignment
- Excel Integration

What you can do
- Students
  - List/filter/search students from external API; view details; refresh data
- Groups
  - Create groups for a batch automatically; add group by name
  - Assign/unassign students (validated across advisor’s batches and groups)
  - Assign AOIs (multi-select)
  - Remove all AOIs in a batch (legacy cleanup)
  - Remove all advisor-created groups in a batch
  - Download Excel template; upload Excel to bulk-assign groups (validation + randomized mapping)
- Supervisor Assignment
  - Manual assign (AOI match + capacity; avoid picking current co-supervisor)
  - Lottery: AOI, ranking, combined; preview; unassign one or all (with optional batch filter)
  - Fetch available supervisors with multiple AOIs filter

Useful paths
- /advisor/dashboard
- /advisor/students
- /advisor/groups
- /advisor/supervisor-assignment
