# CODE REVIEW PROMPT TEMPLATE

## Context
You are performing a comprehensive code review for the Fractal Merch e-commerce project.

## Review Criteria

### 🔒 SECURITY (CRITICAL - Block if issues found)
```
□ Input sanitization with htmlspecialchars()
□ SQL injection prevention (prepared statements)
□ CSRF protection on forms
□ XSS prevention (proper output escaping)
□ File upload validation
□ Directory traversal prevention
□ Authentication/authorization checks
□ Sensitive data exposure prevention
```

### ⚡ PERFORMANCE
```
□ Database query optimization (avoid N+1)
□ Proper indexing usage
□ Asset optimization (CSS/JS minification)
□ Image optimization and lazy loading
□ Caching strategies implementation
□ Memory usage optimization
□ Network request minimization
```

### 🏗️ CODE QUALITY
```
□ PSR-12 PHP coding standards
□ ES6+ JavaScript best practices
□ DRY principle (Don't Repeat Yourself)
□ SOLID principles adherence
□ Proper error handling (try/catch)
□ Meaningful variable/function names
□ Code documentation and comments
□ Function length and complexity
```

### 🧪 TESTING & VALIDATION
```
□ Input validation (frontend + backend)
□ Error handling for edge cases
□ Unit tests written/updated
□ Integration tests coverage
□ Cross-browser compatibility
□ Mobile responsiveness
□ Accessibility compliance (WCAG)
```

### 📚 DOCUMENTATION
```
□ Code comments for complex logic
□ Function/method documentation
□ API endpoint documentation
□ README updates if needed
□ Changelog entries
```

## Prompt Template

Use this template when requesting a code review:

```markdown
Please review the following code according to our project standards:

**File:** [FILENAME]
**Type:** [Security Fix/Feature/Bug Fix/Optimization]
**Priority:** [Critical/High/Medium/Low]

**Code to review:**
```[LANGUAGE]
[PASTE CODE HERE]
```

**Specific concerns:**
- [LIST ANY SPECIFIC AREAS OF CONCERN]

**Context:**
- [EXPLAIN WHAT THIS CODE DOES]
- [MENTION ANY CONSTRAINTS OR REQUIREMENTS]

Please provide feedback on:
1. Security vulnerabilities (blocking issues)
2. Performance implications
3. Code quality and best practices
4. Testing recommendations
5. Documentation needs

**Format response as:**
- ✅ **Approved** / ⚠️ **Approved with changes** / ❌ **Rejected**
- **Critical Issues:** [List blocking issues]
- **Recommendations:** [List improvements]
- **Learning Notes:** [Educational feedback for intern]
```

## Security-Focused Review Template

```markdown
🔒 **SECURITY REVIEW REQUEST**

**File:** [FILENAME]
**Risk Level:** [High/Medium/Low]

**Code:**
```[LANGUAGE]
[PASTE CODE HERE]
```

**Security Checklist:**
□ Are all inputs sanitized?
□ Are SQL queries using prepared statements?
□ Is CSRF protection implemented?
□ Are file uploads properly validated?
□ Is authentication/authorization checked?
□ Could this lead to information disclosure?
□ Are error messages revealing sensitive info?

**Threat Model:**
- **Attack Vectors:** [What could an attacker exploit?]
- **Impact Assessment:** [What's the worst-case scenario?]
- **Mitigation Required:** [What security measures are needed?]
```

## Performance Review Template

```markdown
⚡ **PERFORMANCE REVIEW REQUEST**

**File:** [FILENAME]
**Component:** [Frontend/Backend/Database]

**Code:**
```[LANGUAGE]
[PASTE CODE HERE]
```

**Performance Concerns:**
- **Database Queries:** [Number and complexity]
- **Asset Size:** [CSS/JS file sizes]
- **Network Requests:** [API calls, image loads]
- **Memory Usage:** [Large arrays, objects]
- **Processing Time:** [Complex calculations]

**Metrics to Check:**
□ Page load time impact
□ Database query execution time
□ Memory consumption
□ Network payload size
□ Render blocking resources
□ Mobile performance

**Optimization Goals:**
- Target load time: <2 seconds
- Database queries: <100ms
- Asset size: CSS <100KB, JS <500KB
```

## Quick Review Commands

```bash
# Security-focused review
!review-security [file] - Focus on security vulnerabilities

# Performance-focused review  
!review-performance [file] - Focus on performance optimization

# Code quality review
!review-quality [file] - Focus on code standards and best practices

# Complete review
!review-complete [file] - Comprehensive review all criteria

# Intern-friendly review
!review-mentor [file] - Educational review with learning notes
```

## Review Response Format

```markdown
## 📋 CODE REVIEW RESULTS

**File:** [FILENAME]
**Reviewer:** [Senior Dev/AI Assistant]
**Status:** ✅ Approved / ⚠️ Approved with Changes / ❌ Rejected

### 🚨 BLOCKING ISSUES (Must fix before merge)
- [ ] Issue 1: Description and solution
- [ ] Issue 2: Description and solution

### ⚠️ IMPROVEMENTS RECOMMENDED
- [ ] Suggestion 1: Why and how to improve
- [ ] Suggestion 2: Why and how to improve

### ✅ WHAT'S WORKING WELL
- Good practice 1: Explanation
- Good practice 2: Explanation

### 📚 LEARNING NOTES (For interns)
- Concept 1: Educational explanation
- Best Practice: Why it matters

### 🔧 ACTION ITEMS
1. Fix blocking issues
2. Consider improvements
3. Add tests for [specific scenarios]
4. Update documentation for [specific areas]

**Estimated fix time:** [X hours/days]
**Next steps:** [What to do after fixes]
```

## Common Issues and Solutions

### Security Issues
```php
// ❌ BAD: Unsanitized input
$name = $_POST['name'];
echo "Hello " . $name;

// ✅ GOOD: Sanitized output
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
echo "Hello " . $name;
```

### Performance Issues
```php
// ❌ BAD: N+1 Query
foreach ($products as $product) {
    $category = getCategory($product['category_id']); // N queries
}

// ✅ GOOD: Single query with JOIN
$products = getProductsWithCategories(); // 1 query
```

### Code Quality Issues
```javascript
// ❌ BAD: Unclear variable names
function calc(a, b) { return a * b * 0.21; }

// ✅ GOOD: Clear and descriptive
function calculateTaxAmount(subtotal, taxRate) {
    return subtotal * taxRate;
}
```

---
*Use this template to ensure consistent, thorough code reviews that maintain high quality and security standards while providing educational value to intern developers.*