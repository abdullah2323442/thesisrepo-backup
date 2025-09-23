# Notification System Flow

```mermaid
flowchart TD
    Start([Event Occurs]) --> EventType{Event Type}
    
    EventType -->|Report| ReportEvent[Report Assigned/Updated]
    EventType -->|Meeting| MeetingEvent[Meeting Scheduled]
    EventType -->|Feedback| FeedbackEvent[Feedback Available]
    EventType -->|Annotation| AnnotationEvent[New Annotation]
    
    ReportEvent --> IdentifyRecipients[Identify Recipients]
    MeetingEvent --> IdentifyRecipients
    FeedbackEvent --> IdentifyRecipients
    AnnotationEvent --> IdentifyRecipients
    
    IdentifyRecipients --> CreateNotif[Create Notification Object]
    CreateNotif --> SetData[Set Notification Data]
    
    SetData --> DeliveryChannels{Delivery Channels}
    
    DeliveryChannels -->|Database| StoreDB[Store in Database]
    StoreDB --> MarkUnread[Mark as Unread]
    
    DeliveryChannels -->|Email| CheckEmail{Email Enabled?}
    CheckEmail -->|Yes| SendEmail[Send Email]
    CheckEmail -->|No| SkipEmail[Skip Email]
    
    DeliveryChannels -->|Real-time| CheckOnline{User Online?}
    CheckOnline -->|Yes| PushUpdate[Push Real-time Update]
    CheckOnline -->|No| WaitLogin[Wait for Login]
    
    MarkUnread --> UserLogin[User Logs In]
    SkipEmail --> UserLogin
    SendEmail --> UserLogin
    PushUpdate --> UserLogin
    WaitLogin --> UserLogin
    
    UserLogin --> ShowBadge[Show Notification Badge]
    ShowBadge --> UserClick{User Clicks?}
    
    UserClick -->|Yes| ViewNotif[View Notification]
    ViewNotif --> MarkRead[Mark as Read]
    MarkRead --> Navigate[Navigate to Related Page]
    
    UserClick -->|No| KeepBadge[Keep Badge Visible]
    
    Navigate --> End([End])
    KeepBadge --> End
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style EventType fill:#FFE082
    style DeliveryChannels fill:#FFE082
    style CheckEmail fill:#FFE082
    style CheckOnline fill:#FFE082
    style UserClick fill:#FFE082
```

## Description
Multi-channel notification delivery system for various system events.

## Event Types
- **Report Events**: Assignment, status updates
- **Meeting Events**: Scheduling, reminders
- **Feedback Events**: New feedback available
- **Annotation Events**: PDF annotations added

## Delivery Channels
1. **Database**: Persistent storage for all notifications
2. **Email**: Optional email notifications
3. **Real-time**: Instant updates for online users

## User Interaction
- Badge display for unread notifications
- Click to view and mark as read
- Navigation to related content