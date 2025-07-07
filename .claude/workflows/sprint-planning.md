# SPRINT PLANNING WORKFLOW

## Overview
Comprehensive sprint planning process using Claude CLI to analyze capacity, prioritize features, and create realistic sprint goals.

## 🗓️ Schedule
**Frequency:** Every 2 weeks (Monday)  
**Duration:** 2 hours  
**Participants:** Senior Dev + 3 Interns + CEO (for priorities)

## 📋 Pre-Planning Preparation (Day Before)

### Automated Analysis
```bash
# Sprint retrospective analysis
claude "Analyze last sprint performance:
- Velocity actual vs. planned
- Story points completed vs. committed
- Types of work that took longer than expected
- Blockers that consumed time
- Team satisfaction and learnings
- Technical debt accumulated"

# Backlog grooming
claude "Prepare backlog for planning:
- Prioritize items by business value
- Identify dependencies between items
- Estimate effort for unestimated items
- Flag items needing more requirements
- Identify technical risks"

# Capacity analysis
claude "Calculate team capacity for next sprint:
- Available person-days (accounting for holidays/PTO)
- Velocity trend from last 3 sprints
- Buffer for unexpected issues (20% recommended)
- Time allocated for code reviews and mentoring
- Learning/training time needed"
```

### Data Gathering
```markdown
📊 **SPRINT METRICS TO REVIEW:**
- Previous sprint velocity: X story points
- Team capacity: X person-days
- Critical bugs backlog: X items
- Technical debt items: X hours estimated
- New feature requests: X items
- Performance/security issues: X items
```

## 🎯 Sprint Planning Session

### Phase 1: Sprint Review & Retrospective (30 minutes)

#### What Went Well
```bash
claude "Generate 'What Went Well' analysis:
- Features delivered successfully
- Processes that worked effectively
- Technical solutions that solved problems well
- Team collaboration highlights
- Learning achievements
- Tools/practices that helped productivity"
```

#### What Didn't Go Well
```bash
claude "Identify improvement areas:
- Features that faced unexpected challenges
- Processes that slowed us down
- Technical decisions that created problems
- Communication gaps
- Knowledge gaps that created blockers
- Tools/practices that hindered productivity"
```

#### Action Items from Retrospective
```markdown
**Process Improvements for Next Sprint:**
- [ ] Improvement 1: [Specific action + owner]
- [ ] Improvement 2: [Specific action + owner]
- [ ] Improvement 3: [Specific action + owner]

**Technical Improvements:**
- [ ] Tool/practice to adopt: [Details]
- [ ] Tool/practice to stop: [Reason]
- [ ] Knowledge sharing session: [Topic + presenter]
```

### Phase 2: Sprint Goal Definition (20 minutes)

#### Business Priorities (CEO Input)
```markdown
**Sprint Theme:** [One sentence describing the main focus]

**Business Objectives:**
1. **Primary Goal:** [Most important business outcome]
2. **Secondary Goal:** [Supporting business outcome]
3. **Technical Goal:** [Critical technical improvement needed]

**Success Metrics:**
- Metric 1: [Specific measurable outcome]
- Metric 2: [Specific measurable outcome]
- Metric 3: [Specific measurable outcome]
```

#### Technical Priorities (Senior Dev Input)
```bash
claude "Based on current technical state, prioritize:
1. Critical security fixes (blocking production)
2. Performance issues affecting users
3. Technical debt reducing team velocity
4. Infrastructure/tooling improvements
5. New feature development capacity"
```

### Phase 3: Capacity Planning (15 minutes)

#### Team Capacity Analysis
```bash
claude "Calculate realistic sprint capacity:

**Available Time:**
- Senior Dev: X days (minus Y days for code reviews/mentoring)
- Frontend Intern: X days (minus Y days for learning)
- Backend Intern: X days (minus Y days for learning)
- Full-stack Intern: X days (minus Y days for testing/docs)

**Capacity Adjustments:**
- New team member learning curve: -20%
- Technical debt from last sprint: -15%
- Integration complexity: -10%
- Buffer for unexpected issues: -20%

**Realistic Capacity:** X story points"
```

#### Skill-based Allocation
```markdown
**Frontend Work Capacity:**
- CSS optimization: 8 hours (Frontend Intern)
- JavaScript debugging: 12 hours (Frontend Intern)
- Mobile responsiveness: 6 hours (Frontend Intern)
- UI component development: 10 hours (Frontend Intern)

**Backend Work Capacity:**
- Security implementations: 16 hours (Backend Intern + Senior Dev)
- Database optimization: 8 hours (Backend Intern)
- API development: 12 hours (Backend Intern)
- Integration work: 6 hours (Backend Intern)

**Full-stack Work Capacity:**
- Testing suite: 10 hours (Full-stack Intern)
- Documentation: 6 hours (Full-stack Intern)
- Integration testing: 8 hours (Full-stack Intern)
- Performance monitoring: 4 hours (Full-stack Intern)
```

### Phase 4: Story Selection & Estimation (45 minutes)

#### Critical Items (Must Do)
```bash
claude "Identify critical items for this sprint:
- Security fixes that block production deployment
- Critical bugs affecting user experience
- Dependencies for other team members
- Commitments to stakeholders with deadlines"
```

#### Story Estimation Process
```markdown
**Estimation Guidelines:**
- 1 point = 2-4 hours (simple task)
- 2 points = 4-8 hours (straightforward feature)
- 3 points = 8-16 hours (moderate complexity)
- 5 points = 16-24 hours (complex feature)
- 8 points = 24-40 hours (very complex - consider breaking down)

**Estimation Factors:**
- Technical complexity
- Requirements clarity
- Dependencies on other work
- Testing requirements
- Documentation needs
- Learning curve for implementer
```

#### Sprint Backlog Creation
```bash
claude "Create balanced sprint backlog:
1. Allocate critical items first
2. Balance between bug fixes and new features
3. Ensure each team member has appropriate work
4. Include learning objectives for interns
5. Leave 20% capacity for unexpected work
6. Verify dependencies are manageable"
```

### Phase 5: Task Breakdown & Assignment (10 minutes)

#### Detailed Task Creation
```markdown
**For Each Story:**
- [ ] Requirements analysis and clarification
- [ ] Technical design (if complex)
- [ ] Implementation (frontend/backend/full-stack)
- [ ] Unit testing
- [ ] Integration testing
- [ ] Code review
- [ ] Documentation update
- [ ] Deployment verification

**Assignment Strategy:**
- Match work to individual's learning goals
- Ensure knowledge sharing opportunities
- Balance challenging vs. achievable tasks
- Plan pair programming sessions
- Schedule mentoring check-ins
```

## 📊 Sprint Planning Templates

### Sprint Backlog Template
```markdown
# Sprint [Number] Backlog

**Sprint Goal:** [One sentence goal]
**Sprint Dates:** [Start] - [End]
**Team Capacity:** [X] story points

## 🚨 Critical Items (Must Complete)
- [ ] **[SECURITY]** Fix CSRF vulnerabilities - 5 pts (Backend Intern + Senior Dev)
- [ ] **[BUG]** Resolve cart calculation errors - 3 pts (Frontend Intern)
- [ ] **[PERFORMANCE]** Optimize CSS from 320KB to <100KB - 5 pts (Frontend Intern)

## ⚡ High Priority Features
- [ ] **[FEATURE]** Implement product reviews system - 8 pts (Full-stack Intern)
- [ ] **[IMPROVEMENT]** Add loading states to checkout - 3 pts (Frontend Intern)
- [ ] **[API]** Create REST endpoints for mobile app - 5 pts (Backend Intern)

## 📚 Learning & Improvement
- [ ] **[TESTING]** Setup E2E testing framework - 5 pts (Full-stack Intern)
- [ ] **[DOCS]** Document security implementation - 2 pts (Backend Intern)
- [ ] **[REFACTOR]** Convert procedural code to OOP - 8 pts (Senior Dev)

## 🔄 Ongoing Activities
- [ ] **[REVIEW]** Daily code reviews - 2 pts/day (Senior Dev)
- [ ] **[MENTORING]** Weekly learning sessions - 1 pt/session (Senior Dev)
- [ ] **[MONITORING]** Performance monitoring setup - 3 pts (Full-stack Intern)

**Total Committed:** [X] story points
**Capacity Buffer:** [Y] story points (20%)
```

### Individual Sprint Goals Template
```markdown
## Senior Developer Sprint Goals
**Technical Leadership:**
- [ ] Complete security audit and fixes
- [ ] Mentor team on [specific topic]
- [ ] Architect [specific feature]

**Learning/Growth:**
- [ ] Research [new technology/pattern]
- [ ] Improve team process: [specific improvement]

## Frontend Intern Sprint Goals
**Technical Delivery:**
- [ ] Fix JavaScript errors in 6 files
- [ ] Optimize CSS performance
- [ ] Implement [specific UI feature]

**Learning/Growth:**
- [ ] Master [specific JS concept]
- [ ] Learn [specific CSS technique]
- [ ] Practice debugging skills

## Backend Intern Sprint Goals
**Technical Delivery:**
- [ ] Implement security fixes
- [ ] Create API endpoints
- [ ] Optimize database queries

**Learning/Growth:**
- [ ] Understand [security concept]
- [ ] Learn [database technique]
- [ ] Practice API design

## Full-stack Intern Sprint Goals
**Technical Delivery:**
- [ ] Setup testing framework
- [ ] Create feature documentation
- [ ] Implement end-to-end feature

**Learning/Growth:**
- [ ] Master [testing framework]
- [ ] Learn [integration patterns]
- [ ] Practice system thinking
```

## 🎯 Sprint Planning Commands

### Pre-Planning Analysis
```bash
# Comprehensive sprint preparation
!sprint-prep - Analyze velocity, capacity, and backlog priorities

# Backlog analysis
!backlog-analyze - Review and prioritize backlog items

# Team capacity calculation
!capacity-calc - Calculate realistic team capacity for sprint

# Dependency mapping
!dependencies - Identify task dependencies and critical path
```

### During Planning
```bash
# Story estimation assistance
!estimate [story] - Help estimate story points for task

# Capacity validation
!capacity-check - Verify sprint commitment against capacity

# Risk assessment
!sprint-risks - Identify potential risks for sprint

# Balance analysis
!sprint-balance - Analyze work distribution across team
```

### Post-Planning
```bash
# Sprint summary generation
!sprint-summary - Generate comprehensive sprint plan summary

# Communication artifacts
!sprint-comm - Create stakeholder communication about sprint

# Setup tracking
!sprint-tracking - Setup progress tracking and daily standup templates
```

## 🔍 Risk Assessment Framework

### Technical Risks
```bash
claude "Assess technical risks for sprint:
- New technologies or patterns being used
- Complex integrations required
- Dependencies on external systems
- Performance requirements
- Security considerations
- Browser/device compatibility needs"
```

### Process Risks
```bash
claude "Assess process risks:
- Team member availability
- Knowledge gaps in critical areas
- Communication dependencies
- Review/approval bottlenecks
- Testing environment availability
- Deployment complexity"
```

### Mitigation Strategies
```markdown
**Risk Mitigation Plan:**
- **Risk 1:** [Description]
  - **Probability:** High/Medium/Low
  - **Impact:** High/Medium/Low
  - **Mitigation:** [Specific actions]
  - **Owner:** [Person responsible]

- **Risk 2:** [Description]
  - **Probability:** High/Medium/Low
  - **Impact:** High/Medium/Low
  - **Mitigation:** [Specific actions]
  - **Owner:** [Person responsible]
```

## 📈 Success Metrics & Tracking

### Sprint Success Criteria
```markdown
**Definition of Done for Sprint:**
- [ ] All critical items completed
- [ ] No new critical bugs introduced
- [ ] Performance metrics maintained or improved
- [ ] Security requirements met
- [ ] Code review standards maintained
- [ ] Documentation updated
- [ ] Team satisfaction score > [target]
```

### Daily Tracking
```bash
# Daily progress check
claude "Daily sprint progress:
- Burndown vs. ideal trajectory
- Completed vs. remaining story points
- Blockers identified and resolution time
- Quality metrics (bugs, performance)
- Team velocity trend"
```

### Mid-Sprint Adjustment
```bash
# Mid-sprint checkpoint
claude "Mid-sprint analysis:
- Are we on track for sprint goal?
- What scope adjustments might be needed?
- Any team members overloaded or underutilized?
- Learning objectives being met?
- Process improvements needed immediately?"
```

## 🎓 Learning Integration

### Knowledge Sharing Plan
```markdown
**Sprint Learning Objectives:**
- **Frontend Intern:** [Specific skill/concept]
- **Backend Intern:** [Specific skill/concept]  
- **Full-stack Intern:** [Specific skill/concept]

**Knowledge Sharing Sessions:**
- Day 3: [Topic] - presented by [person]
- Day 7: [Topic] - presented by [person]
- Day 10: [Topic] - presented by [person]

**Pair Programming Sessions:**
- [Intern] + [Senior Dev]: [Topic/Task]
- [Intern] + [Intern]: [Knowledge exchange]
```

### Skill Development Tracking
```bash
claude "Track skill development progress:
- Learning objectives set vs. achieved
- Knowledge sharing effectiveness
- Mentoring session outcomes
- Technical confidence growth
- Areas needing additional support"
```

---
*This sprint planning workflow ensures realistic commitments, balanced workloads, continuous learning, and clear success criteria while maintaining flexibility for the dynamic nature of software development.*