# Supervisor Assignment Algorithm (Production)

Last Updated: 2025-08-18

This document describes the actual production algorithm implemented for automatically assigning supervisors to thesis groups. It now supports two lottery modes without removing any existing functionality:

- AOI-based Lottery (default): Assigns by matching Area Of Interest (AOI), fairly distributing within each AOI and prioritizing higher-ranking supervisors.
- Ranking-based Lottery (new): Ignores AOI and fairly distributes globally across all active supervisors by ranking.

Both modes enforce supervisor capacity and avoid immediate consecutive assignments to the same supervisor when alternatives exist.


## 1) Super Simplified Overview and Pseudocode

### Modes
- aoi: Area-of-Interest-aware round-robin by rank, per AOI pools
- ranking: Global round-robin by rank, AOI ignored

### AOI-based Lottery (simple steps)
1. Collect active supervisors with available slots and build AOI pools grouped by rank.
2. Sort supervisors inside each rank bucket by current load, then name.
3. Sort groups deterministically by group number suffix (Group 1, 2, 3...).
4. For each eligible group with an AOI:
   - Build cross-rank candidates for the AOI, skipping the last-picked supervisor (for that AOI) and those without capacity.
   - Pick the candidate with the lowest per-run AOI count, then highest rank (lowest rank number), then closest to the bucket cursor.
   - Decrement capacity, advance that bucket's cursor, track last pick and per-run counts.
   - Persist supervisor_id and assignment_priority (= rank).
5. Groups with no AOI or no matching capacity remain unassigned.

### AOI-based Lottery (minimal pseudocode)
```
pools, areaHasSup, avail = buildPoolsByAOI(activeSupWithSlots)
lastPickPerAOI = {}
aoiRunCounts = {}
for group in sortByNumericSuffix(groups):
  if group.aoi is null:
    markUnassigned()
    continue
  candidates = []
  for rank in pools[aoi]:
    for sup in bucketFromCursor(pools[aoi][rank]):
      if sup == lastPickPerAOI[aoi] or avail[sup] == 0: continue
      candidates.add({sup, rank, order, run=aoiRunCounts[aoi][sup]})
  if candidates not empty:
    choice = min(candidates, key=(run, rank, order))
  else:
    choice = tryLastPickedIfHasCapacity()
  if choice is null:
    markUnassigned(noMatches = !areaHasSup[aoi])
    continue
  assign(group, choice.sup)
  avail[choice.sup]--
  advanceCursor(pools[aoi][choice.rank], choice.idx)
  aoiRunCounts[aoi][choice.sup]++
  lastPickPerAOI[aoi] = choice.sup
```

### Ranking-based Lottery (simple steps)
1. Collect all active supervisors with available slots into a single global list.
2. Sort the list by rank (ascending), then current load, then name.
3. Keep a global round-robin cursor; avoid immediate consecutive picks if alternatives exist.
4. Iterate groups (sorted by group number suffix) and pick the next supervisor with available capacity.
5. Decrement capacity, advance cursor, persist assignment_priority (= rank). If none available, mark unassigned.

### Ranking-based Lottery (minimal pseudocode)
```
list = sortBy(rank, load, name)(activeSupWithSlots)
cursor = 0
lastPick = null
for group in sortByNumericSuffix(groups):
  pick = null
  for i in 0..len(list)-1:
    idx = (cursor + i) % len(list)
    if avail[list[idx]] > 0 and (list[idx] != lastPick or len(list) == 1):
      pick = idx; break
  if pick is null:
    # allow consecutive if that’s all we have
    for i in 0..len(list)-1:
      idx = (cursor + i) % len(list)
      if avail[list[idx]] > 0: pick = idx; break
  if pick is null: markUnassigned(); continue
  assign(group, list[pick])
  avail[list[pick]]--
  lastPick = list[pick]
  cursor = (pick + 1) % len(list)
```


## 2) Flowcharts

### AOI-based Lottery
```mermaid
flowchart TD
  A[Start] --> B[Load eligible groups and active supervisors]
  B --> C[Build AOI pools grouped by rank; compute availability]
  C --> D[Sort groups by numeric suffix]
  D --> E{Next group has AOI?}
  E -- No --> F[Mark Unassigned (no_area_of_interest)] --> J
  E -- Yes --> G[Select supervisor:
- Build candidates across ranks
- Skip last-picked for this AOI
- Skip no-capacity
- Choose by (lowest per-run count, best rank, closest to cursor)
- Fallback: allow last-picked if only option]
  G --> H{Found pick?}
  H -- No --> I[Mark Unassigned (no_matches or no_capacity)] --> J
  H -- Yes --> K[Assign supervisor; decrement capacity; advance cursor; track counts]
  K --> J{More groups?}
  J -- Yes --> E
  J -- No --> L[End]
```

### Ranking-based Lottery
```mermaid
flowchart TD
  A[Start] --> B[Load eligible groups and active supervisors]
  B --> C[Build global list; sort by rank, load, name]
  C --> D[Initialize global cursor, lastPick]
  D --> E[Sort groups by numeric suffix]
  E --> F[For each group, find next sup with capacity in round-robin; avoid consecutive if possible]
  F --> G{Found pick?}
  G -- No --> H[Mark Unassigned (no_available_slots)] --> J
  G -- Yes --> I[Assign; dec capacity; advance cursor; update lastPick]
  I --> J{More groups?}
  J -- Yes --> F
  J -- No --> K[End]
```


## 3) Professional Detailed Specification

### 3.1 Scope and Modes
- AOI-based Lottery (default): Matches groups to supervisors sharing the group’s Area of Interest.
  - Fair per-AOI distribution using round-robin cursors and low-watermark balancing.
  - Prioritizes higher academic rank (lower numeric rank_priority value means higher rank).
- Ranking-based Lottery (new): Ignores AOI; assigns globally by rank with round-robin fairness.
  - Ensures distribution reaches even the lowest-ranked supervisors when capacity exists.

Mode selection in requests/UI:
- mode=aoi (default)
- mode=ranking

### 3.2 Inputs and Outputs
- Inputs (per run):
  - groups: collection of lottery-eligible groups for an advisor
    - AOI mode: Group::lotteryEligible() (no supervisor, not manual, has AOI, has students)
    - Ranking mode: whereNull(supervisor_id), is_manual_assignment=false, has students; AOI may be null
  - supervisors: all active supervisors and their areas/capacities
- Outputs:
  - Persistent run: updates group.supervisor_id, assigned_at, is_manual_assignment=false, assignment_priority=selected rank
  - Preview: non-persistent plan with assignments/unassigned lists and summary stats
  - Method tags:
    - AOI mode: method = "aoi_round_robin_low_watermark"
    - Ranking mode: method = "global_ranking_round_robin"

### 3.3 Data Structures
- availability: map[supervisorId] -> remaining slots (computed as thesis_limit - assigned_theses_count)
- AOI pools: pools[aoiId][rank] = {
    cursor: int,
    supervisors: [ { id, model }, ... ]  // sorted by current load, then name
  }
- AOI state: lastPickPerAOI[aoiId] = supervisorId; aoiRunCounts[aoiId][supervisorId] = count used this run
- Global ranking list (ranking mode): list of { id, model, rank } sorted by (rank asc, load asc, name asc)

### 3.4 Ordering and Determinism
- Groups are processed in a deterministic order by extracting the first numeric suffix from the group name (e.g., "Group 7"). If absent, those groups fall to the end.
- Within AOI rank buckets, supervisors are initially ordered by current assigned load, then name, for a stable and fair start.

### 3.5 AOI-based Selection Details
- Candidate construction across all ranks in the AOI:
  - Skip last-picked supervisor within the same AOI if there’s any alternative to prevent consecutive picks.
  - Skip supervisors without remaining capacity.
  - Track per-run AOI usage counts (aoiRunCounts) to balance usage in this run.
- Candidate prioritization:
  - 1) Minimum aoiRunCounts[aoi][sup]
  - 2) Best rank (rank_priority ascending: 1 best)
  - 3) Closest to the bucket’s cursor (rotation fairness)
- Post-pick updates:
  - Decrement availability
  - Advance the chosen bucket’s cursor to just after the chosen supervisor
  - Update aoiRunCounts and lastPickPerAOI
- Fallback:
  - If no alternatives exist across ranks but the last-picked supervisor still has capacity, allow consecutive selection.

### 3.6 Ranking-based Selection Details
- Build a single global list of all active supervisors with available capacity, sorted by:
  - rank_priority asc (1 best)
  - assigned load asc
  - fullname asc (case-insensitive)
- Maintain one global cursor and lastPick variable.
- For each group:
  - Round-robin search from cursor to find a supervisor with capacity. Avoid choosing the same supervisor as lastPick if there is any alternative.
  - If no alternative exists but some capacity exists, allow choosing lastPick again.
  - Update availability, lastPick, and advance the cursor to after the chosen supervisor.

### 3.7 Capacity and Concurrency
- Capacity is enforced per pick by consulting the availability map.
- The controller wraps the persistent run in a DB transaction. Rows are updated atomically to avoid concurrent oversubscription.

### 3.8 Edge Cases and Handling
- Group without AOI (AOI mode): marked unassigned with reason no_area_of_interest.
- AOI has no matching supervisors: marked unassigned with reason no_matches.
- All supervisors at capacity (either mode): unassigned with reason no_available_slots.
- Single supervisor available: algorithm may assign consecutively if no other alternative exists.

### 3.9 Complexity Considerations
- Let G be the number of eligible groups and S the number of active supervisors with capacity.
- Ranking mode: worst-case per group scan can touch up to O(S) in round-robin search (typically far less); total O(G*S) worst-case.
- AOI mode: per group, we scan the AOI’s rank buckets. If R is total supervisors matching that AOI with capacity, selection is O(R), typically small. Building pools is O(S log S) due to sorting inside buckets.
- In practice, both modes are efficient for typical department sizes and scale linearly in G with small constants.

### 3.10 Implementation Notes (Code Mapping)
- Service: App\Services\SupervisorAssignmentService
  - runLotteryAssignment(Collection $groups, string $mode = 'aoi')
    - mode === 'aoi' → AOI algorithm
    - mode === 'ranking' → global ranking algorithm
  - previewLotteryAssignment(Collection $groups, string $mode = 'aoi')
    - Same logic as run, but simulated without persistence
  - Key helpers:
    - buildAOIPools(Collection $areaIds)
    - selectSupervisor(...) for AOI picking
    - runRankingAssignment(...), previewRankingAssignment(...)
- Controller: App\Http\Controllers\Advisor\SupervisorAssignmentController
  - runLottery(Request): passes mode, wraps in transaction
  - previewLottery(Request): passes mode, returns JSON
- UI: Advisor page provides a "Lottery Mode" selector and supports preview/run for both modes.

### 3.11 Result Annotations
- assignment_priority is set to the numeric rank_priority of the selected supervisor.
- method annotation on results/previews indicates which algorithm ran:
  - "aoi_round_robin_low_watermark"
  - "global_ranking_round_robin"

### 3.12 Testing Guidance
- Unit tests:
  - AOI pools contain only supervisors matching that AOI and with availability
  - AOI selection avoids immediate consecutive picks when alternatives exist
  - Global ranking selection reaches lower-ranked supervisors when higher ranks have limited capacity
  - Capacity is never exceeded
- Integration tests:
  - Preview vs Run produce consistent plans when no external changes occur during the transaction
  - Batch filtering limits affected groups as expected
- Edge tests:
  - No AOI on some groups (AOI mode) → unassigned with correct reasons
  - Only one supervisor available → allowed consecutive picks


## 4) API-Level Behavior Summary

- Preview (GET /advisor/supervisor-assignment/preview-lottery):
  - Query params: batch (optional), mode in {aoi, ranking}
  - Returns planned assignments, unassigned list, and stats
- Run (POST /advisor/supervisor-assignment/run-lottery):
  - Form fields: batch (optional), mode in {aoi, ranking}
  - Persists assignments as per selected mode


## 5) Glossary
- AOI: Area Of Interest associated with a group and a supervisor’s expertise areas
- Rank priority: Numeric value indicating academic rank; lower numbers mean higher rank
- Cursor: Rotating index used to ensure round-robin fairness within a rank bucket (AOI mode) or globally (ranking mode)
- Low-watermark balancing: Prefer supervisors with the least picks in the current run within the AOI to spread assignments evenly


---

Historical note: Earlier design docs referenced a generic Hungarian algorithm and multi-factor scoring. The current production implementation is a deterministic, capacity-aware lottery using ranked round-robin selection tailored to the thesis assignment domain. This document reflects the implemented behavior.
