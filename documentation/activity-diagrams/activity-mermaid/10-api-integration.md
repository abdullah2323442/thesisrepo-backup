# External API Integration Flow

```mermaid
flowchart TD
    Start([API Request Initiated]) --> ServiceType{Which Service?}
    
    ServiceType -->|Batch| BatchService[BatchApiService]
    ServiceType -->|Student| StudentService[StudentApiService]
    ServiceType -->|Supervisor| SupervisorService[SupervisorApiService]
    
    BatchService --> LoadConfig["Load config/external_api.php"]
    StudentService --> LoadConfig
    SupervisorService --> LoadConfig
    
    LoadConfig --> CheckCache{Check Laravel Cache}
    
    CheckCache -->|Cache Hit| ValidateCache{Cache Valid?}
    ValidateCache -->|Yes| ReturnCached[Return Cached Data]
    ValidateCache -->|No| PrepareRequest
    
    CheckCache -->|Cache Miss| PrepareRequest[Prepare HTTP Request]
    
    PrepareRequest --> SetHeaders["Set Headers:<br/>- Authorization Bearer<br/>- Content-Type<br/>- Accept"]
    SetHeaders --> SelectEndpoint{Select API Endpoint}
    
    SelectEndpoint -->|Batch Sync| BatchEndpoint["POST /api/batches<br/>Sync Batch Model Data"]
    SelectEndpoint -->|Student Data| StudentEndpoint["GET /api/students/{registration}<br/>Fetch Student Details"]
    SelectEndpoint -->|Supervisor Load| SupervisorEndpoint["GET /api/supervisors/{id}/load<br/>Check Supervisor Capacity"]
    SelectEndpoint -->|Assignment Sync| AssignmentEndpoint["POST /api/assignments<br/>Sync AssignmentHistory"]
    
    BatchEndpoint --> CallAPI[Execute HTTP Request]
    StudentEndpoint --> CallAPI
    SupervisorEndpoint --> CallAPI
    AssignmentEndpoint --> CallAPI
    
    CallAPI --> Response{Response Status}
    
    Response -->|200 OK| ParseResponse[Parse JSON Response]
    ParseResponse --> ValidateSchema{Validate Data Schema}
    
    ValidateSchema -->|Valid| UpdateModels["Update Local Models:<br/>- Batch<br/>- User<br/>- Supervisor"]
    ValidateSchema -->|Invalid| LogSchemaError[Log Schema Mismatch]
    
    UpdateModels --> CacheResponse["Cache Response<br/>(TTL from config)"]
    CacheResponse --> ReturnSuccess[Return Processed Data]
    ReturnSuccess --> End1([Success])
    
    LogSchemaError --> UseFallback
    
    Response -->|429 Rate Limited| CheckRetries{"Retry Attempts < 3?"}
    CheckRetries -->|Yes| ExponentialBackoff["Wait: 2^attempt seconds"]
    ExponentialBackoff --> IncrementRetry[Increment Retry Counter]
    IncrementRetry --> CallAPI
    
    CheckRetries -->|No| LogRateLimit["Log to storage/logs/api.log"]
    LogRateLimit --> UseFallback[Use Database Fallback]
    
    Response -->|401 Unauthorized| RefreshToken[Attempt Token Refresh]
    RefreshToken --> TokenResponse{Token Refreshed?}
    TokenResponse -->|Success| UpdateConfig[Update Bearer Token]
    UpdateConfig --> CallAPI
    TokenResponse -->|Failed| AlertAdmin[Send Admin Notification]
    AlertAdmin --> DisableIntegration[Disable External API Features]
    
    Response -->|500/503 Server Error| LogServerError[Log Server Error]
    LogServerError --> CheckFallback{Local Data Available?}
    
    Response -->|Network Error| LogNetworkError[Log Connection Error]
    LogNetworkError --> CheckFallback
    
    CheckFallback -->|Yes| UseFallback
    CheckFallback -->|No| QueueJob[Queue Laravel Job for Retry]
    
    UseFallback --> LoadLocalData["Load from Local Models:<br/>- Batch::all()<br/>- Supervisor::with('areaOfInterests')<br/>- User::where('role', 'student')"]
    LoadLocalData --> ReturnDegraded[Return with Degraded Flag]
    
    QueueJob --> DispatchJob["Dispatch to Queue:<br/>php artisan queue:work"]
    DispatchJob --> End3([Queued for Later])
    
    DisableIntegration --> End4([API Disabled])
    ReturnDegraded --> End2([Degraded Mode])
    
    ReturnCached --> MonitorPerformance["PerformanceMonitoringService::track()"]
    ReturnSuccess --> MonitorPerformance
    ReturnDegraded --> MonitorPerformance
    
    MonitorPerformance --> End5([Request Complete])
    
    style Start fill:#4CAF50,color:#fff
    style End1 fill:#4CAF50,color:#fff
    style End2 fill:#FFA726,color:#fff
    style End3 fill:#FFA726,color:#fff
    style End4 fill:#f44336,color:#fff
    style End5 fill:#4CAF50,color:#fff
    style ServiceType fill:#FFE082
    style CheckCache fill:#FFE082
    style ValidateCache fill:#FFE082
    style SelectEndpoint fill:#FFE082
    style Response fill:#FFE082
    style ValidateSchema fill:#FFE082
    style CheckRetries fill:#FFE082
    style TokenResponse fill:#FFE082
    style CheckFallback fill:#FFE082
```

## Description
External API integration flow showing the three API services and their error handling strategies.

## API Services (app/Services/)
- **BatchApiService**: Synchronizes batch data with external system
- **StudentApiService**: Fetches and validates student information
- **SupervisorApiService**: Manages supervisor load and availability

## Configuration (config/external_api.php)
- Base URL configuration
- Authentication tokens
- Timeout settings
- Cache TTL values
- Retry configuration

## API Endpoints
- **Batch Sync**: `POST /api/batches` - Sync Batch model data
- **Student Data**: `GET /api/students/{registration}` - Fetch student details
- **Supervisor Load**: `GET /api/supervisors/{id}/load` - Check capacity
- **Assignment Sync**: `POST /api/assignments` - Sync AssignmentHistory

## Error Handling Strategies
1. **Rate Limiting (429)**: Exponential backoff with max 3 retries
2. **Auth Errors (401)**: Token refresh with admin notification fallback
3. **Server Errors (500/503)**: Fallback to local database
4. **Network Errors**: Queue for later processing via Laravel jobs

## Caching Strategy
- Laravel Cache facade used
- TTL configured per endpoint in external_api.php
- Cache invalidation on model updates
- Fallback to database when cache unavailable

## Performance Monitoring
- All requests tracked via PerformanceMonitoringService
- Metrics: response time, cache hit rate, error rate
- Degraded mode tracking for SLA monitoring

## Fallback Data Sources
- **Batch**: Local Batch model with relationships
- **Student**: User model with role='student'
- **Supervisor**: Supervisor model with area_of_interests
- **Assignment**: AssignmentHistory model records