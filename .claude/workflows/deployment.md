# DEPLOYMENT WORKFLOW

## Overview
Automated deployment process using Claude CLI to ensure secure, tested, and reliable production deployments.

## 🚀 Deployment Pipeline

### Phase 1: Pre-Deployment Validation
```bash
# Security validation
claude --context=".claude/contexts/senior-dev.md" \
"Perform final security audit before deployment:
- All CSRF protection implemented
- Input sanitization complete
- SQL injection prevention verified
- XSS protection in place
- Error handling secure"

# Performance check
claude --context=".claude/contexts/intern-frontend.md" \
"Validate frontend performance metrics:
- CSS optimized (<100KB)
- JavaScript errors resolved
- Mobile responsiveness verified
- Loading times acceptable"

# Database integrity
claude --context=".claude/contexts/intern-backend.md" \
"Verify database readiness:
- All migrations applied
- Indexes optimized
- Backup completed
- Connection strings updated"
```

### Phase 2: Testing Verification
```bash
# Test suite execution
claude --context=".claude/contexts/intern-fullstack.md" \
"Execute complete test suite:
- Unit tests passing
- Integration tests verified
- E2E tests successful
- Performance tests within limits
- Security tests passed"
```

### Phase 3: Production Deployment
```bash
# Deployment execution
claude --context=".claude/contexts/senior-dev.md" \
"Execute production deployment:
- Code package prepared
- Database migrations applied
- Environment variables configured
- SSL certificates verified
- Monitoring alerts activated"
```

### Phase 4: Post-Deployment Verification
```bash
# Health checks
claude --context=".claude/contexts/senior-dev.md" \
"Perform post-deployment verification:
- Application responding correctly
- All critical features functional
- No error logs generated
- Performance metrics stable
- Security measures active"
```

## 📋 Deployment Checklist

### Pre-Deployment
- [ ] All security vulnerabilities resolved
- [ ] JavaScript errors fixed
- [ ] CSS optimized
- [ ] Database migrations ready
- [ ] Backup completed
- [ ] Test suite passing
- [ ] Performance benchmarks met

### During Deployment
- [ ] Maintenance mode activated
- [ ] Code deployed
- [ ] Database migrations applied
- [ ] Cache cleared
- [ ] Environment configured
- [ ] SSL verified

### Post-Deployment
- [ ] Health checks passed
- [ ] Critical paths tested
- [ ] Error monitoring active
- [ ] Performance monitoring active
- [ ] Rollback plan ready
- [ ] Team notified

## 🔧 Emergency Rollback
```bash
# Quick rollback procedure
claude --context=".claude/contexts/senior-dev.md" \
"Execute emergency rollback:
- Revert to previous version
- Restore database backup
- Clear caches
- Verify functionality
- Document issues for analysis"
```