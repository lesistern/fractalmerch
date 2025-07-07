# SECURITY AUDIT PROMPT TEMPLATE

## Context
You are performing a comprehensive security audit for the Fractal Merch e-commerce project that handles sensitive customer data, payments, and personal information.

## Current Security State
```
🚨 CRITICAL VULNERABILITIES IDENTIFIED:
├── 69 files with directory traversal vulnerabilities
├── 8 files with dangerous functions (eval, exec, system)
├── Only 1 file implementing HTML escape
├── 0 files with CSRF protection
├── 0 files with proper exception handling
└── Inconsistent input validation across the application
```

## Security Audit Checklist

### 🛡️ INPUT VALIDATION & SANITIZATION
```
□ All user inputs sanitized (htmlspecialchars, filter_var)
□ File uploads properly validated (type, size, content)
□ URL parameters validated and sanitized
□ Form data validation (frontend + backend)
□ API inputs properly validated
□ File path validation (prevent directory traversal)
□ Database inputs using prepared statements only
□ Email addresses properly validated
```

### 🔐 AUTHENTICATION & AUTHORIZATION
```
□ Password hashing using password_hash()
□ Session management secure (regenerate ID, secure flags)
□ OAuth implementation secure
□ Multi-factor authentication considered
□ Password reset flows secure
□ User role/permission checks on all admin pages
□ Session timeout implemented
□ Account lockout after failed attempts
```

### 🛠️ SQL INJECTION PREVENTION
```
□ All database queries use prepared statements
□ No dynamic SQL construction with user input
□ Stored procedures used where appropriate
□ Database user has minimal required permissions
□ Error messages don't reveal database structure
□ Input validation before database operations
□ Parameterized queries for complex operations
```

### 🌐 CROSS-SITE SCRIPTING (XSS) PREVENTION
```
□ All output properly escaped (htmlspecialchars)
□ Content Security Policy (CSP) implemented
□ User-generated content sanitized
□ Rich text editors secured
□ JSON responses properly escaped
□ Template engines auto-escape enabled
□ DOM manipulation secure
```

### 🔒 CROSS-SITE REQUEST FORGERY (CSRF) PREVENTION
```
□ CSRF tokens on all state-changing forms
□ CSRF tokens validated server-side
□ SameSite cookie attribute set
□ Referer header validation where appropriate
□ Critical actions require re-authentication
□ AJAX requests include CSRF tokens
```

### 📁 FILE UPLOAD SECURITY
```
□ File type validation (whitelist approach)
□ File size limits enforced
□ File content validation (not just extension)
□ Uploaded files stored outside web root
□ Virus scanning implemented
□ Filename sanitization
□ Image processing security (resize, strip metadata)
```

### 🔍 ERROR HANDLING & LOGGING
```
□ Error messages don't reveal sensitive information
□ Proper exception handling implemented
□ Security events logged (failed logins, etc.)
□ Log files protected from unauthorized access
□ Error reporting disabled in production
□ Custom error pages implemented
□ Debugging information hidden in production
```

### 🌐 HTTPS & COMMUNICATION SECURITY
```
□ HTTPS enforced on all pages
□ Secure cookie flags set (Secure, HttpOnly)
□ HSTS headers implemented
□ TLS version and cipher suite secure
□ Certificate validation proper
□ API communications encrypted
□ Password fields use secure transmission
```

## Security Audit Prompt Template

```markdown
🔒 **SECURITY AUDIT REQUEST**

**Scope:** [Full Application/Specific Component/File]
**Risk Level:** [Critical/High/Medium/Low]

**Files to audit:**
- [List specific files or "entire application"]

**Focus Areas:**
□ Input validation and sanitization
□ SQL injection vulnerabilities
□ XSS vulnerabilities
□ CSRF protection
□ Authentication/authorization
□ File upload security
□ Session management
□ Error handling
□ Data encryption
□ Third-party integrations

**Specific Concerns:**
- [Any particular security concerns or recent changes]

**Audit Request:**
Please perform a comprehensive security audit focusing on:

1. **Immediate Threats:** Critical vulnerabilities that could be exploited
2. **Data Protection:** Customer data, payment info, personal information
3. **Access Control:** Admin panel security, user authentication
4. **Input Security:** All user inputs and file uploads
5. **Output Security:** All data displayed to users
6. **Communication Security:** API endpoints, form submissions

**Expected Deliverables:**
- Risk assessment with severity levels
- Specific vulnerability locations
- Exploit scenarios for critical issues
- Prioritized remediation plan
- Code examples for fixes
```

## Critical Vulnerability Templates

### Directory Traversal Audit
```markdown
🚨 **DIRECTORY TRAVERSAL AUDIT**

**Files to check:** [List files with include/require statements]

**Vulnerability Pattern:**
```php
// VULNERABLE CODE:
include $_GET['page'] . '.php';
require '../' . $_POST['file'];
```

**Audit Questions:**
- Are file paths validated against whitelist?
- Are ../ sequences properly filtered?
- Are absolute paths used where possible?
- Is user input used in file operations?

**Test Cases:**
- `page=../config/database`
- `file=....//....//etc/passwd`
- `include=php://filter/read=convert.base64-encode/resource=config.php`
```

### SQL Injection Audit
```markdown
💉 **SQL INJECTION AUDIT**

**Database Operations to check:** [List files with SQL queries]

**Vulnerability Pattern:**
```php
// VULNERABLE CODE:
$query = "SELECT * FROM users WHERE id = " . $_GET['id'];
$result = mysql_query($query); // Deprecated function
```

**Audit Questions:**
- Are all queries using prepared statements?
- Is user input directly concatenated into SQL?
- Are stored procedures used securely?
- Is input validation performed before queries?

**Test Cases:**
- `id=1 OR 1=1`
- `id=1; DROP TABLE users; --`
- `id=1 UNION SELECT password FROM admin_users`
```

### XSS Audit
```markdown
⚠️ **XSS VULNERABILITY AUDIT**

**Output Points to check:** [List areas where user data is displayed]

**Vulnerability Pattern:**
```php
// VULNERABLE CODE:
echo "Welcome " . $_POST['username'];
echo "<script>var data = '" . $_GET['data'] . "';</script>";
```

**Audit Questions:**
- Is all output properly escaped?
- Are user inputs validated before display?
- Is rich text content sanitized?
- Are JSON responses properly escaped?

**Test Cases:**
- `username=<script>alert('XSS')</script>`
- `data='; alert('XSS'); //`
- `comment=<img src=x onerror=alert('XSS')>`
```

## Remediation Priority Matrix

| Vulnerability | Risk Level | Impact | Effort | Priority |
|---------------|------------|---------|---------|----------|
| SQL Injection | Critical | High | Medium | 1 |
| Directory Traversal | Critical | High | Low | 1 |
| XSS | High | Medium | Low | 2 |
| CSRF | High | Medium | Medium | 2 |
| Insecure File Upload | High | High | High | 3 |
| Session Fixation | Medium | Medium | Low | 3 |
| Information Disclosure | Medium | Low | Low | 4 |

## Security Fix Templates

### Input Sanitization Fix
```php
// Before (Vulnerable):
$username = $_POST['username'];
$comment = $_POST['comment'];

// After (Secure):
function sanitizeInput($input, $type = 'text') {
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

$username = sanitizeInput($_POST['username']);
$comment = sanitizeInput($_POST['comment']);
```

### CSRF Protection Implementation
```php
// Generate CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF Token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Usage in forms:
<input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

// Validation:
if (!validateCSRFToken($_POST['csrf_token'])) {
    die('CSRF token validation failed');
}
```

### Secure File Upload
```php
function secureFileUpload($file, $allowedTypes = ['jpg', 'png', 'gif']) {
    // Validate file
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception('Invalid file upload');
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large');
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception('Invalid file type');
    }
    
    // Generate secure filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    
    // Move to secure location (outside web root)
    $uploadPath = '/secure/uploads/' . $newFilename;
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('File upload failed');
    }
    
    return $newFilename;
}
```

## Security Audit Report Template

```markdown
# 🔒 SECURITY AUDIT REPORT

**Project:** Fractal Merch E-commerce
**Audit Date:** [DATE]
**Auditor:** [NAME/AI Assistant]
**Scope:** [Full Application/Specific Components]

## 📊 EXECUTIVE SUMMARY

**Overall Security Rating:** [Critical/High/Medium/Low] Risk
**Critical Issues Found:** [NUMBER]
**High Priority Issues:** [NUMBER]
**Medium Priority Issues:** [NUMBER]

**Immediate Action Required:** [Yes/No]
**Production Deployment Recommended:** [Yes/No]

## 🚨 CRITICAL VULNERABILITIES (Fix Immediately)

### 1. [Vulnerability Name]
- **Risk Level:** Critical
- **CVSS Score:** [Score]
- **Location:** [Files affected]
- **Description:** [What the vulnerability is]
- **Exploit Scenario:** [How it could be exploited]
- **Impact:** [What damage could be done]
- **Fix:** [How to fix it]
- **Timeline:** [How long to fix]

## ⚠️ HIGH PRIORITY ISSUES

### 1. [Issue Name]
- **Risk Level:** High
- **Location:** [Files affected]
- **Description:** [What the issue is]
- **Recommendation:** [How to fix]

## 🔧 REMEDIATION PLAN

### Phase 1: Critical Fixes (Week 1)
- [ ] Fix SQL injection vulnerabilities
- [ ] Implement input sanitization
- [ ] Add CSRF protection
- [ ] Fix directory traversal issues

### Phase 2: High Priority (Week 2)
- [ ] Implement proper error handling
- [ ] Secure file upload functionality
- [ ] Add security headers
- [ ] Review authentication flows

### Phase 3: Medium Priority (Week 3-4)
- [ ] Implement Content Security Policy
- [ ] Add rate limiting
- [ ] Security logging enhancement
- [ ] Third-party security review

## 📋 SECURITY CHECKLIST FOR ONGOING DEVELOPMENT

```
□ All new code reviewed for security issues
□ Input validation implemented for all user inputs
□ Output escaping applied to all user data display
□ CSRF tokens added to all state-changing forms
□ Authentication/authorization checked on all protected pages
□ Security testing included in development workflow
□ Regular security audits scheduled
```

**Next Audit Recommended:** [DATE]
**Estimated Remediation Time:** [X weeks]
**Estimated Cost:** [$ amount or development hours]
```

---
*Use this template to conduct thorough security audits that identify vulnerabilities, assess risks, and provide clear remediation guidance for the development team.*