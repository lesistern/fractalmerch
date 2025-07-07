# DAILY STANDUP WORKFLOW

## Overview
Automated daily standup process using Claude CLI to track progress, identify blockers, and coordinate team activities.

## 🕘 Schedule
**Time:** 9:00 AM daily  
**Duration:** 15 minutes maximum  
**Participants:** Senior Dev + 3 Interns + Optional CEO

## 📋 Pre-Standup Preparation (5 minutes before)

### Individual Team Member Tasks
```bash
# Each team member runs this before standup
!standup-prep
```

### Claude CLI Preparation Commands
```bash
# Generate personal standup report
claude "Generate my standup report for today:
- What I completed yesterday (check git commits)
- What I'm working on today (check assigned tickets)
- Any blockers or concerns
- Code review requests needed"

# Check project health
claude "Quick project health check:
- Any critical errors in logs
- Failed tests in CI/CD
- Performance alerts
- Security issues needing attention"

# Review team progress
claude "Team progress summary:
- Sprint burndown status
- Critical path items
- Cross-team dependencies
- Upcoming deadlines"
```

## 🎯 Standup Structure

### Round 1: Individual Updates (8 minutes)

#### Senior Developer Update
```markdown
**Role:** Tech Lead & Mentor

**Yesterday Completed:**
- [ ] Security vulnerability fixes
- [ ] Code reviews for [intern names]
- [ ] Architecture decisions on [topics]
- [ ] Technical blockers resolved

**Today's Focus:**
- [ ] Continue security audit
- [ ] Mentor [specific intern] on [topic]
- [ ] Review/approve [specific PRs]
- [ ] [Specific technical task]

**Blockers:**
- [ ] None / [Specific blocker + escalation needed]

**Team Support Needed:**
- [ ] [Intern name] needs help with [topic]
- [ ] Code review priority: [PR #]
- [ ] Decision needed on [technical choice]
```

#### Frontend Intern Update
```markdown
**Role:** UI/UX & Client-side Development

**Yesterday Completed:**
- [ ] JavaScript bug fixes
- [ ] CSS optimization work
- [ ] Mobile responsiveness improvements
- [ ] Cart functionality testing

**Today's Focus:**
- [ ] Fix remaining JS errors in [files]
- [ ] Optimize CSS from 320KB to <100KB
- [ ] Implement [specific UI feature]
- [ ] Cross-browser testing

**Blockers:**
- [ ] None / Need help with [specific technical issue]
- [ ] Waiting for [dependency/approval/review]

**Learning Goal Today:**
- [ ] [Specific concept or skill to learn]
```

#### Backend Intern Update
```markdown
**Role:** Server-side Logic & Database

**Yesterday Completed:**
- [ ] Security fixes implemented
- [ ] Database queries optimized
- [ ] API endpoints created/updated
- [ ] Input validation added

**Today's Focus:**
- [ ] Continue CSRF protection implementation
- [ ] Database migration for [feature]
- [ ] Payment gateway integration
- [ ] API testing and documentation

**Blockers:**
- [ ] None / Need guidance on [security/database topic]
- [ ] Third-party API documentation unclear

**Learning Goal Today:**
- [ ] [Security concept or database technique]
```

#### Full-stack Intern Update
```markdown
**Role:** Testing & Integration

**Yesterday Completed:**
- [ ] Test suite expansion
- [ ] Integration testing
- [ ] Documentation updates
- [ ] Bug fixes across stack

**Today's Focus:**
- [ ] E2E testing for [feature]
- [ ] Performance testing setup
- [ ] Documentation for [feature/API]
- [ ] Cross-component integration

**Blockers:**
- [ ] None / Testing environment issues
- [ ] Need clarification on [requirements]

**Learning Goal Today:**
- [ ] [Testing framework or documentation tool]
```

### Round 2: Team Coordination (5 minutes)

#### Dependency Mapping
```bash
# Generate dependency visualization
claude "Map today's dependencies:
- Who is blocked by whom?
- What needs to be completed first?
- Which tasks can run in parallel?
- Any critical path changes?"
```

#### Priority Alignment
```markdown
**Today's Team Priorities (in order):**
1. 🚨 **CRITICAL:** [Security fixes blocking production]
2. ⚡ **HIGH:** [JavaScript errors breaking features]
3. 📈 **MEDIUM:** [Performance optimizations]
4. 📚 **ONGOING:** [Testing and documentation]

**Resource Allocation:**
- **Senior Dev:** 70% security, 30% mentoring
- **Frontend Intern:** 100% JS errors and CSS optimization
- **Backend Intern:** 100% security implementation
- **Full-stack Intern:** 60% testing, 40% documentation
```

#### Risk Assessment
```bash
# Quick risk check
claude "Identify risks for today:
- What could block our sprint goals?
- Are there any single points of failure?
- Do we have knowledge transfer risks?
- Any external dependencies at risk?"
```

### Round 3: Action Items (2 minutes)

#### Immediate Actions
```markdown
**Action Items:**
- [ ] [Senior Dev] Review [Frontend Intern]'s CSS optimization approach
- [ ] [Backend Intern] Pair with [Senior Dev] on CSRF implementation
- [ ] [Full-stack Intern] Setup staging environment for testing
- [ ] [All] Update ticket status in project management tool

**Scheduled Check-ins:**
- 11:00 AM: [Backend Intern] + [Senior Dev] CSRF pairing session
- 2:00 PM: [Frontend Intern] CSS optimization review
- 4:00 PM: Daily progress check-in

**Decisions Needed:**
- [ ] [Technology choice] by [person] by [time]
- [ ] [Architecture decision] escalate to CEO if needed
```

## 🤖 Automated Claude CLI Commands

### Pre-Standup Automation
```bash
# Generate team report (run by Senior Dev)
claude "Generate daily team report:
1. Yesterday's commits analysis
2. CI/CD status and any failures
3. Open PRs needing review
4. Critical issues from logs
5. Sprint progress vs. timeline
6. Suggestions for today's priorities"

# Individual preparation (each team member)
alias standup-prep="claude 'Prepare my standup update based on:
- My git commits from yesterday
- My assigned tickets and their status
- Any blockers I logged
- My learning goals progress
- Code reviews I requested or received'"
```

### During Standup Automation
```bash
# Real-time blockers identification
claude "Analyze current blockers mentioned in standup:
- Technical blockers: [categorize by type]
- Process blockers: [identify process improvements needed]
- Knowledge blockers: [match with mentoring opportunities]
- External blockers: [identify escalation needs]"

# Workload balancing
claude "Based on today's updates, suggest workload adjustments:
- Who might be overloaded?
- Who has capacity for additional tasks?
- Any skills mismatches in current assignments?
- Opportunities for knowledge sharing?"
```

### Post-Standup Automation
```bash
# Generate standup summary
claude "Create standup summary:
- Key decisions made
- Action items with owners and deadlines
- Blockers identified and resolution plans
- Priorities for today
- Follow-up meetings scheduled"

# Update project tracking
claude "Update project status based on standup:
- Ticket status changes
- New blockers to log
- Progress against sprint goals
- Risk register updates"
```

## 📊 Metrics Tracking

### Daily Metrics Dashboard
```bash
# Generate daily metrics
claude "Daily metrics report:
- Sprint burndown: X story points remaining
- Velocity: X points per day average
- Blockers resolved vs. new blockers
- Code review cycle time
- Critical bug count
- Team satisfaction indicators"
```

### Weekly Trends
```bash
# Weekly trend analysis (Fridays)
claude "Weekly standup trends:
- Most common blocker types
- Knowledge transfer effectiveness
- Cross-team collaboration patterns
- Learning goal achievement rate
- Predictability vs. actuals"
```

## 🎯 Standup Quality Guidelines

### What Makes a Good Update
```markdown
✅ **GOOD UPDATE:**
- Specific and measurable progress
- Clear blockers with context
- Realistic today's commitments
- Asks for help when needed
- Shares knowledge/learnings

❌ **POOR UPDATE:**
- Vague progress statements
- No specific plans for today
- Doesn't mention obvious blockers
- No collaboration/learning mentioned
- Same update every day
```

### Facilitation Best Practices
```markdown
**For Senior Dev (Facilitator):**
- Keep updates time-boxed (2 min per person)
- Ask follow-up questions for clarity
- Identify patterns across team updates
- Suggest immediate solutions for blockers
- Schedule deeper discussions for after standup
- Encourage knowledge sharing opportunities
```

## 🔄 Integration with Project Tools

### Git Integration
```bash
# Generate commit-based updates
claude "Based on git commits since yesterday standup:
- [Developer name]: [summary of changes]
- Files most actively changed
- Any large commits needing review
- Potential merge conflicts"
```

### Ticket System Integration
```bash
# Sync with project management
claude "Ticket status report:
- Moved to 'Done' since yesterday
- Currently 'In Progress'
- Blocked tickets and reasons
- Tickets without recent activity
- Sprint goal progress"
```

### CI/CD Integration
```bash
# Build and test status
claude "CI/CD health report:
- Last successful build time
- Current failing tests
- Performance regression alerts
- Security scan results
- Deployment pipeline status"
```

## 📝 Templates for Specific Scenarios

### When CEO Joins
```markdown
**Extended Update for CEO:**
- Business impact of yesterday's work
- Progress toward sprint goals
- Risk assessment for delivery timeline
- Resource needs or blockers requiring executive action
- Notable technical achievements or learnings
```

### When Team Member is Absent
```bash
# Generate absent member report
claude "Report on [absent member]'s work:
- Recent commits and progress
- Assigned tickets status
- Any blockers they logged
- Work others are depending on
- Suggested coverage plan"
```

### Crisis Mode Standup
```markdown
**Crisis Standup Format:**
- Current crisis status
- Who is working on what aspect
- Immediate next steps (next 2 hours)
- Communication plan with stakeholders
- Contingency plans if current approach fails
```

---
*This daily standup workflow ensures consistent communication, rapid blocker resolution, and effective team coordination while maintaining focus on sprint goals and continuous learning.*