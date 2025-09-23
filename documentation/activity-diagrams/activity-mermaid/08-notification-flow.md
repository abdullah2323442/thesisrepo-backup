# Notification System Flow

```mermaid
flowchart TD
    Start([Event Trigger]) --> EventType{Notification Type}
    
    EventType -->|NewReportAssigned| ReportAssigned[New Report Assigned Event]
    EventType -->|NewReportComment| CommentEvent[New Comment Added]
    EventType -->|NewReportAnnotation| AnnotationEvent[New Annotation Created]
    EventType -->|ReportUpdated| UpdateEvent[Report Updated Event]
    
    ReportAssigned --> CreateReportNotif[Create NewReportAssigned Instance]
    CommentEvent --> CreateCommentNotif[Create NewReportComment Instance]
    AnnotationEvent --> CreateAnnotNotif[Create NewReportAnnotation Instance]
    UpdateEvent --> CreateUpdateNotif[Create ReportUpdated Instance]
    
    CreateReportNotif --> IdentifyRecipients[Identify User Recipients]
    CreateCommentNotif --> IdentifyRecipients
    CreateAnnotNotif --> IdentifyRecipients
    CreateUpdateNotif --> IdentifyRecipients
    
    IdentifyRecipients --> CheckRole{User Role}
    
    CheckRole -->|Student| StudentRecipients[GroupStudent Members]
    CheckRole -->|Supervisor| SupervisorRecipients[Assigned Supervisors]
    CheckRole -->|Co-Supervisor| CoSupervisorRecipients[Co-Supervisors]
    CheckRole -->|Advisor| AdvisorRecipients[Group Advisors]
    CheckRole -->|Panel| PanelRecipients[GroupPanelMember]
    
    StudentRecipients --> BuildNotification[Build Notification Data]
    SupervisorRecipients --> BuildNotification
    CoSupervisorRecipients --> BuildNotification
    AdvisorRecipients --> BuildNotification
    PanelRecipients --> BuildNotification
    
    BuildNotification --> SetPayload["Set Notification Payload<br/>(report_id, comment_id, session_id)"]
    SetPayload --> DeliveryChannels{Delivery Method}
    
    DeliveryChannels -->|Database| StoreNotification["Store in notifications table<br/>(migration: 2025_09_01_172011)"]
    StoreNotification --> SetAttributes["Set id, type, notifiable_type,<br/>notifiable_id, data, read_at"]
    
    DeliveryChannels -->|Email| CheckEmailConfig{Mail Config Set?}
    CheckEmailConfig -->|Yes| QueueEmail[Queue Email Job]
    CheckEmailConfig -->|No| SkipEmail[Skip Email Channel]
    
    DeliveryChannels -->|Broadcast| CheckBroadcast{Broadcasting Enabled?}
    CheckBroadcast -->|Yes| BroadcastEvent[Broadcast Real-time Event]
    CheckBroadcast -->|No| SkipBroadcast[Skip Real-time]
    
    SetAttributes --> MarkUnread["Set read_at = NULL"]
    QueueEmail --> ProcessQueue[Process Queue Job]
    BroadcastEvent --> PushToClient[Push to Client via WebSocket]
    SkipEmail --> MarkUnread
    SkipBroadcast --> MarkUnread
    
    MarkUnread --> UserAccess[User Accesses System]
    ProcessQueue --> UserAccess
    PushToClient --> UserAccess
    
    UserAccess --> LoadNotifications["Load Unread Notifications<br/>WHERE read_at IS NULL"]
    LoadNotifications --> DisplayBadge[Display Notification Count]
    DisplayBadge --> UserInteraction{User Action}
    
    UserInteraction -->|View| OpenNotification[Open Notification]
    OpenNotification --> UpdateReadAt["UPDATE read_at = NOW()"]
    UpdateReadAt --> NavigateContent[Navigate to Related Content]
    
    UserInteraction -->|Mark All Read| BulkUpdate[Bulk Update read_at]
    UserInteraction -->|Ignore| KeepUnread[Keep as Unread]
    
    NavigateContent --> CheckContent{Content Type}
    CheckContent -->|Report| ViewReport[Navigate to Report View]
    CheckContent -->|Comment| ViewComments[Navigate to Comments Section]
    CheckContent -->|Annotation| ViewAnnotations[Open Annotation Session]
    
    ViewReport --> End([Notification Handled])
    ViewComments --> End
    ViewAnnotations --> End
    BulkUpdate --> End
    KeepUnread --> End
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style EventType fill:#FFE082
    style CheckRole fill:#FFE082
    style DeliveryChannels fill:#FFE082
    style CheckEmailConfig fill:#FFE082
    style CheckBroadcast fill:#FFE082
    style UserInteraction fill:#FFE082
    style CheckContent fill:#FFE082
```

## Description
Notification system flow accurately reflecting the notification classes and database structure.

## Notification Classes (app/Notifications/)
- **NewReportAssigned**: Triggered when report assigned to group
- **NewReportComment**: Triggered when comment added to report
- **NewReportAnnotation**: Triggered when annotation session created
- **ReportUpdated**: Triggered when report submission updated

## Database Structure (notifications table)
- **id**: UUID primary key
- **type**: Notification class name
- **notifiable_type**: Polymorphic model type (User)
- **notifiable_id**: User ID
- **data**: JSON payload with context
- **read_at**: Timestamp when read (NULL if unread)
- **created_at/updated_at**: Timestamps

## Recipient Resolution
- **Students**: Via GroupStudent relationship
- **Supervisors**: Via Group supervisor assignment
- **Co-Supervisors**: Via Group co-supervisor field
- **Advisors**: Via Group advisor relationship
- **Panel Members**: Via GroupPanelMember table

## Delivery Channels
1. **Database**: Always stored in notifications table
2. **Email**: Optional via Laravel mail queue
3. **Broadcast**: Real-time via broadcasting (if configured)

## Notification Lifecycle
1. Event triggers notification creation
2. Recipients identified based on relationships
3. Notification stored with unread status (read_at = NULL)
4. Optional email/broadcast delivery
5. User views and marks as read (updates read_at)
6. Navigation to related content based on payload