# Performance Monitoring Service Flow

```mermaid
flowchart TD
    Start([Request Initiated]) --> Middleware[Performance Middleware]
    Middleware --> StartTimer[Start Request Timer]
    StartTimer --> RecordMetrics[Record Initial Metrics:<br/>- Memory Usage<br/>- CPU Time<br/>- Request URI]
    
    RecordMetrics --> ProcessRequest[Process Request]
    ProcessRequest --> CheckService{Uses Monitoring Service?}
    
    CheckService -->|Yes| CallMonitoring[Call PerformanceMonitoringService]
    CheckService -->|No| ContinueProcess[Continue Processing]
    
    CallMonitoring --> ServiceMethod{Service Method}
    
    ServiceMethod -->|track()| TrackOperation[Track Custom Operation]
    ServiceMethod -->|startTimer()| StartCustomTimer[Start Named Timer]
    ServiceMethod -->|endTimer()| EndCustomTimer[End Named Timer]
    ServiceMethod -->|recordMetric()| RecordCustom[Record Custom Metric]
    ServiceMethod -->|checkThreshold()| CheckThreshold[Check Performance Threshold]
    
    TrackOperation --> LogOperation[Log to performance_metrics table]
    StartCustomTimer --> StoreStartTime[Store Start Timestamp]
    EndCustomTimer --> CalculateDuration[Calculate Duration]
    RecordCustom --> StoreMetric[Store Custom Metric]
    CheckThreshold --> ThresholdCheck{Exceeds Threshold?}
    
    ThresholdCheck -->|Yes| TriggerAlert[Trigger Performance Alert]
    ThresholdCheck -->|No| ContinueMonitor
    
    TriggerAlert --> LogAlert[Log Alert to performance_alerts]
    LogAlert --> NotifyAdmin[Send Admin Notification]
    NotifyAdmin --> ContinueMonitor[Continue Monitoring]
    
    LogOperation --> ContinueProcess
    StoreStartTime --> ContinueProcess
    CalculateDuration --> LogOperation
    StoreMetric --> ContinueProcess
    ContinueMonitor --> ContinueProcess
    
    ContinueProcess --> CheckDatabase{Database Query?}
    
    CheckDatabase -->|Yes| QueryMonitor[Monitor Query Performance]
    QueryMonitor --> QueryMetrics[Record:<br/>- Query Time<br/>- Rows Examined<br/>- Query Type]
    QueryMetrics --> SlowQuery{Slow Query?>1s}
    
    SlowQuery -->|Yes| LogSlowQuery[Log to slow_queries table]
    SlowQuery -->|No| ContinueDB
    
    LogSlowQuery --> OptimizationHint[Generate Optimization Hint]
    OptimizationHint --> ContinueDB[Continue]
    
    CheckDatabase -->|No| CheckCache{Cache Operation?}
    ContinueDB --> CheckCache
    
    CheckCache -->|Yes| CacheMonitor[Monitor Cache Performance]
    CacheMonitor --> CacheMetrics[Record:<br/>- Hit/Miss Rate<br/>- Cache Size<br/>- TTL Stats]
    CacheMetrics --> ContinueCache[Continue]
    
    CheckCache -->|No| CheckAPI{External API Call?}
    ContinueCache --> CheckAPI
    
    CheckAPI -->|Yes| APIMonitor[Monitor API Performance]
    APIMonitor --> APIMetrics[Record:<br/>- Response Time<br/>- Status Code<br/>- Payload Size]
    APIMetrics --> APIHealth{API Healthy?}
    
    APIHealth -->|No| CircuitBreaker[Activate Circuit Breaker]
    APIHealth -->|Yes| ContinueAPI
    
    CircuitBreaker --> UseCache[Switch to Cache/Fallback]
    UseCache --> ContinueAPI[Continue]
    
    CheckAPI -->|No| ResponseReady[Prepare Response]
    ContinueAPI --> ResponseReady
    
    ResponseReady --> EndTimer[End Request Timer]
    EndTimer --> CalculateTotal[Calculate Total Metrics:<br/>- Total Time<br/>- Memory Peak<br/>- DB Queries Count]
    
    CalculateTotal --> StoreMetrics[Store in Database:<br/>- request_metrics<br/>- performance_summary]
    StoreMetrics --> CheckAggregation{Time for Aggregation?}
    
    CheckAggregation -->|Yes| RunAggregation[Run Aggregation Job]
    RunAggregation --> GenerateReports[Generate Performance Reports:<br/>- Hourly<br/>- Daily<br/>- Weekly]
    GenerateReports --> UpdateDashboard[Update Monitoring Dashboard]
    
    CheckAggregation -->|No| ReturnResponse[Return Response]
    UpdateDashboard --> ReturnResponse
    
    ReturnResponse --> CheckSLA{Check SLA Compliance}
    
    CheckSLA -->|Violation| LogSLAViolation[Log SLA Violation]
    LogSLAViolation --> EscalateIssue[Escalate to Team]
    EscalateIssue --> End([Request Complete])
    
    CheckSLA -->|Compliant| End
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#4CAF50,color:#fff
    style ServiceMethod fill:#FFE082
    style ThresholdCheck fill:#FFE082
    style SlowQuery fill:#FFE082
    style APIHealth fill:#FFE082
    style CheckAggregation fill:#FFE082
    style CheckSLA fill:#FFE082
```

## Description
Comprehensive performance monitoring flow using PerformanceMonitoringService.

## Service Location
`app/Services/PerformanceMonitoringService.php`

## Key Monitoring Points

### Request Level
- Total request time
- Memory usage (start, peak, end)
- CPU time consumption
- Request URI and method

### Database Monitoring
- Query execution time
- Number of queries per request
- Slow query detection (>1s)
- Query optimization hints

### Cache Monitoring
- Cache hit/miss rates
- Cache operation times
- Memory usage by cache
- TTL effectiveness

### API Monitoring
- External API response times
- Success/failure rates
- Circuit breaker activation
- Fallback usage statistics

## Performance Thresholds
- **Request Time**: >3s triggers alert
- **Memory Usage**: >128MB triggers warning
- **Database Queries**: >50 per request triggers review
- **Cache Miss Rate**: >30% triggers optimization
- **API Response**: >2s activates circuit breaker

## Data Storage
- **request_metrics**: Individual request data
- **performance_summary**: Aggregated statistics
- **slow_queries**: Queries exceeding threshold
- **performance_alerts**: Threshold violations
- **sla_violations**: SLA compliance tracking

## Reporting Features
1. **Real-time Dashboard**: Current system metrics
2. **Historical Analysis**: Trend identification
3. **Alert Management**: Threshold-based notifications
4. **SLA Reporting**: Compliance tracking
5. **Optimization Suggestions**: AI-driven hints

## Integration Points
- Middleware for automatic tracking
- Service methods for custom metrics
- Queue jobs for aggregation
- Notifications for alerts
- Dashboard for visualization