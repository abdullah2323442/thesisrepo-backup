# Panel Member Evaluation Process

```mermaid
flowchart TD
    Start([Panel Member Login]) --> ViewAssignments[View Assigned Groups]
    ViewAssignments --> SelectGroup[Select Group to Evaluate]
    
    SelectGroup --> ViewReports[View Final Reports]
    ViewReports --> SelectReport[Select Report]
    
    SelectReport --> ReviewOptions{Review Type}
    
    ReviewOptions -->|Document Review| OpenReport[Open Report Document]
    OpenReport --> ReadContent[Read Thesis Content]
    ReadContent --> MakeNotes[Make Evaluation Notes]
    
    ReviewOptions -->|Annotate| StartAnnotation[Start Annotation]
    StartAnnotation --> AddPanelComments[Add Panel-Specific Comments]
    AddPanelComments --> SaveAnnotations[Save Annotations]
    
    MakeNotes --> EvaluationCriteria[Apply Evaluation Criteria]
    SaveAnnotations --> EvaluationCriteria
    
    EvaluationCriteria --> Criteria{Evaluation Areas}
    
    Criteria -->|Content| EvalContent[Evaluate Content Quality]
    Criteria -->|Methodology| EvalMethod[Evaluate Methodology]
    Criteria -->|Presentation| EvalPresent[Evaluate Presentation]
    Criteria -->|Innovation| EvalInnovation[Evaluate Innovation]
    
    EvalContent --> ScoreAssignment[Assign Scores]
    EvalMethod --> ScoreAssignment
    EvalPresent --> ScoreAssignment
    EvalInnovation --> ScoreAssignment
    
    ScoreAssignment --> DefenseRequired{Defense Required?}
    
    DefenseRequired -->|Yes| ScheduleDefense[Schedule Defense Session]
    ScheduleDefense --> ConductDefense[Conduct Defense]
    ConductDefense --> DefenseEval[Evaluate Defense Performance]
    DefenseEval --> FinalScore[Calculate Final Score]
    
    DefenseRequired -->|No| DirectEval[Direct Evaluation]
    DirectEval --> FinalScore
    
    FinalScore --> AssignGrade{Assign Grade}
    
    AssignGrade -->|A| ExcellentWork[Excellent Work]
    AssignGrade -->|B| GoodWork[Good Work]
    AssignGrade -->|C| SatisfactoryWork[Satisfactory Work]
    AssignGrade -->|F| FailedWork[Failed - Needs Rework]
    
    ExcellentWork --> SubmitEvaluation[Submit Evaluation]
    GoodWork --> SubmitEvaluation
    SatisfactoryWork --> SubmitEvaluation
    FailedWork --> SubmitEvaluation
    
    SubmitEvaluation --> NotifyStudent[Notify Student & Supervisor]
    NotifyStudent --> End([Evaluation Complete])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style ReviewOptions fill:#FFE082
    style Criteria fill:#FFE082
    style DefenseRequired fill:#FFE082
    style AssignGrade fill:#FFE082
```

## Description
Panel member's evaluation workflow for final thesis assessment.

## Evaluation Process
1. **Document Review**: Read and analyze thesis content
2. **Annotation**: Add panel-specific feedback
3. **Criteria Assessment**: Evaluate multiple aspects
4. **Defense Session**: Optional oral defense
5. **Grade Assignment**: Final grade determination

## Evaluation Criteria
- **Content Quality**: Depth and accuracy of research
- **Methodology**: Research methods appropriateness
- **Presentation**: Document structure and clarity
- **Innovation**: Originality and contribution

## Grading Scale
- **A**: Excellent (90-100%)
- **B**: Good (75-89%)
- **C**: Satisfactory (60-74%)
- **F**: Failed (<60%)