# Multiple Areas of Interest Feature

## Overview
Student groups can now be assigned **multiple areas of interest** instead of being limited to just one. This provides greater flexibility for thesis projects that span multiple domains or require interdisciplinary approaches.

## Implementation Details

### Database Changes

1. **New Pivot Table**: `group_area_of_interest`
   - Links groups to multiple areas of interest
   - Maintains timestamps for tracking when areas were assigned
   - Ensures unique combinations of group and area

2. **Data Migration**
   - Existing single area assignments are automatically migrated to the new system
   - Legacy `area_of_interest_id` column is preserved for backward compatibility
   - No data loss during migration

### Model Updates

#### Group Model (`app/Models/Group.php`)
- **New Relationship**: `areasOfInterest()` - Many-to-many relationship
- **Legacy Support**: `areaOfInterest()` - Still available for backward compatibility
- **Helper Methods**:
  - `hasAreaOfInterest($areaId)` - Check if group has a specific area
  - `getAreaOfInterestIds()` - Get all area IDs for the group
  - `syncAreasOfInterest($areaIds)` - Update areas of interest

### Controller Updates

#### GroupController (`app/Http/Controllers/Advisor/GroupController.php`)
- **Updated Method**: `assignAreaOfInterest()`
  - Now accepts `area_of_interest_ids[]` array
  - Supports both single and multiple area assignment
  - Backward compatible with legacy single area assignment

### UI/UX Changes

#### Group Management Interface
1. **Display**: Shows all assigned areas as badges
2. **Modal**: Checkbox-based selection for multiple areas
3. **Flexibility**: Can select one, multiple, or no areas

## User Guide

### For Advisors

#### Assigning Multiple Areas
1. Navigate to Group Management
2. Click "Assign" or "Change" under Area of Interest column
3. Check all relevant areas from the list
4. Click "Save Areas" to apply changes

#### Removing Areas
- Uncheck all boxes and save to remove all areas
- Uncheck specific areas to remove only those

#### Visual Indicators
- Multiple areas display as separate badges
- Each area has its own colored badge for easy identification
- "Not assigned" shows when no areas are selected

## Benefits

1. **Interdisciplinary Projects**: Support for projects spanning multiple domains
2. **Better Supervisor Matching**: Groups can match with supervisors from different specialties
3. **Flexibility**: Adapt to changing project requirements
4. **Research Diversity**: Encourage cross-domain collaboration

## Technical Considerations

### Performance
- Indexed foreign keys for optimal query performance
- Eager loading of relationships to prevent N+1 queries
- Efficient bulk operations for area assignment

### Backward Compatibility
- Legacy single area field still functional
- Automatic migration of existing data
- No breaking changes to existing API endpoints

### Data Integrity
- Foreign key constraints ensure referential integrity
- Cascade deletion prevents orphaned records
- Unique constraints prevent duplicate assignments

## Example Use Cases

1. **AI + Healthcare Project**
   - Areas: Artificial Intelligence, Medical Technology
   
2. **IoT Security System**
   - Areas: Internet of Things, Cybersecurity, Embedded Systems

3. **E-commerce Platform**
   - Areas: Web Development, Database Systems, UI/UX Design

## Migration Path

### From Single to Multiple Areas
1. Existing single area assignments are preserved
2. Groups can add additional areas without losing the original
3. System automatically handles the transition

### Database Migration Commands
```bash
php artisan migrate
```

## API Changes

### Request Format
**Old (still supported):**
```json
{
  "group_id": 1,
  "area_of_interest_id": 5
}
```

**New (recommended):**
```json
{
  "group_id": 1,
  "area_of_interest_ids": [5, 8, 12]
}
```

### Response Format
Groups now include both relationships:
- `areaOfInterest` - Single area (legacy)
- `areasOfInterest` - Multiple areas (new)

## Testing Recommendations

1. **Assignment Tests**
   - Assign single area
   - Assign multiple areas
   - Remove specific areas
   - Clear all areas

2. **Display Tests**
   - Verify badge display for multiple areas
   - Check sorting and filtering
   - Validate area counts

3. **Migration Tests**
   - Verify existing data migration
   - Test backward compatibility
   - Ensure no data loss

## Future Enhancements

Potential improvements:
- Area priority/ranking within groups
- Area-based group recommendations
- Supervisor expertise matching score
- Area combination analytics
- Cross-area collaboration metrics