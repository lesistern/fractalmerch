# BUG FIX PROMPT TEMPLATE

## Context
You are diagnosing and fixing bugs in the Fractal Merch e-commerce project. This template ensures systematic bug resolution with proper documentation and prevention.

## Bug Report Template

```markdown
# 🐛 Bug Report: [BUG_TITLE]

## 📋 Bug Information
**Bug ID:** [TICKET_NUMBER]
**Reporter:** [NAME]
**Date Reported:** [DATE]
**Severity:** Critical / High / Medium / Low
**Priority:** P1 / P2 / P3 / P4
**Status:** Open / In Progress / Fixed / Verified / Closed

## 📖 Description
**Summary:** [Brief description of the bug]
**Expected Behavior:** [What should happen]
**Actual Behavior:** [What actually happens]
**Impact:** [How this affects users/system]

## 🔍 Environment
**Browser:** [Chrome 100, Firefox 95, Safari 15, etc.]
**Device:** [Desktop / Mobile / Tablet]
**OS:** [Windows 10, macOS 12, Android 11, iOS 15]
**URL:** [Specific page where bug occurs]
**User Role:** [Admin / User / Guest]

## 📝 Steps to Reproduce
1. Navigate to [specific page]
2. Click on [specific element]
3. Enter [specific data]
4. Observe [unexpected behavior]

## 📸 Evidence
**Screenshots:** [Attach screenshots]
**Console Errors:** [Browser console errors]
**Network Errors:** [Failed requests]
**Server Logs:** [Relevant log entries]

## 🎯 Acceptance Criteria for Fix
- [ ] Bug no longer reproduces
- [ ] No new bugs introduced
- [ ] Performance not degraded
- [ ] All tests pass
- [ ] Documentation updated if needed
```

## Bug Classification System

### 🚨 CRITICAL (Fix within 24 hours)
- System crashes or complete unavailability
- Data loss or corruption
- Security vulnerabilities
- Payment processing failures
- Admin panel completely inaccessible

### ⚠️ HIGH (Fix within 1 week)
- Major feature not working
- Significant UX degradation
- Performance issues affecting most users
- JavaScript errors breaking functionality
- Mobile responsiveness severely broken

### 📋 MEDIUM (Fix within 2 weeks)
- Minor feature issues
- UI/UX improvements needed
- Intermittent issues
- Non-critical performance problems
- Cosmetic issues affecting usability

### 📝 LOW (Fix when possible)
- Cosmetic issues
- Enhancement requests
- Documentation errors
- Minor UI inconsistencies

## Debugging Process Template

### Step 1: Reproduce the Bug
```markdown
🔍 **REPRODUCTION ATTEMPT**

**Environment Setup:**
- Browser: [Specify]
- Data State: [Clean DB / Test data / Production copy]
- User Account: [Specific test account or role]

**Reproduction Steps:**
1. [Detailed step]
2. [Detailed step]
3. [Expected vs Actual result]

**Reproduction Result:**
□ Successfully reproduced
□ Unable to reproduce
□ Intermittent reproduction
□ Need more information

**Additional Notes:**
[Any observations during reproduction]
```

### Step 2: Error Analysis
```markdown
🔍 **ERROR ANALYSIS**

**JavaScript Console Errors:**
```javascript
// Copy exact error messages
TypeError: Cannot read property 'length' of undefined
    at addToCart (enhanced-cart.js:45:12)
    at HTMLButtonElement.onclick (particulares.php:123:1)
```

**PHP Error Logs:**
```
[2025-07-06 10:30:15] PHP Fatal error: Uncaught Error: Call to undefined function sanitizeInput() in /path/to/file.php:67
```

**Network Requests:**
```
POST /api/cart 500 Internal Server Error
Response: {"error": "Database connection failed"}
```

**Database Queries:**
```sql
-- Check if queries are executing correctly
SELECT * FROM products WHERE id = 'invalid_id'; -- This might be the issue
```
```

### Step 3: Root Cause Analysis
```markdown
🎯 **ROOT CAUSE ANALYSIS**

**Category:** [Logic Error / Syntax Error / Configuration / Data Issue / Integration Issue]

**Root Cause:**
[Detailed explanation of what's causing the bug]

**Contributing Factors:**
- Factor 1: [e.g., Missing validation]
- Factor 2: [e.g., Incorrect data type]
- Factor 3: [e.g., Race condition]

**Code Location:**
- File: [/path/to/file.php]
- Function: [functionName()]
- Line Numbers: [67-75]

**Why It Wasn't Caught Earlier:**
- [ ] Missing test coverage
- [ ] Edge case not considered
- [ ] Recent code change introduced it
- [ ] Environmental difference
```

## Bug Fix Implementation Templates

### JavaScript Bug Fix
```javascript
// ❌ BEFORE (Buggy Code):
function addToCart(productId, quantity) {
    const cart = JSON.parse(localStorage.getItem('cart'));
    cart.push({ // Error: cart might be null
        id: productId,
        quantity: quantity
    });
    localStorage.setItem('cart', JSON.stringify(cart));
}

// ✅ AFTER (Fixed Code):
function addToCart(productId, quantity) {
    try {
        // Initialize cart if it doesn't exist
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        // Validate inputs
        if (!productId || !quantity || quantity < 1) {
            throw new Error('Invalid product ID or quantity');
        }
        
        // Check if product already exists
        const existingIndex = cart.findIndex(item => item.id === productId);
        
        if (existingIndex >= 0) {
            cart[existingIndex].quantity += parseInt(quantity);
        } else {
            cart.push({
                id: productId,
                quantity: parseInt(quantity),
                addedAt: Date.now()
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartBadge();
        showNotification('Product added to cart', 'success');
        
    } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('Failed to add product to cart', 'error');
    }
}
```

### PHP Bug Fix
```php
// ❌ BEFORE (Buggy Code):
function getProduct($id) {
    global $pdo;
    $query = "SELECT * FROM products WHERE id = " . $id; // SQL injection risk
    $result = $pdo->query($query);
    return $result->fetch(); // Might fail if query fails
}

// ✅ AFTER (Fixed Code):
function getProduct($id) {
    global $pdo;
    
    try {
        // Validate input
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException('Invalid product ID');
        }
        
        // Use prepared statement
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$id]);
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception('Product not found');
        }
        
        return $product;
        
    } catch (PDOException $e) {
        error_log("Database error in getProduct: " . $e->getMessage());
        throw new Exception('Database error occurred');
    } catch (Exception $e) {
        error_log("Error in getProduct: " . $e->getMessage());
        throw $e;
    }
}
```

### CSS Bug Fix
```css
/* ❌ BEFORE (Buggy CSS): */
.cart-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%; /* Issue: Too wide on mobile */
    max-height: 80vh;
    overflow: auto; /* Issue: Content might be cut off */
}

/* ✅ AFTER (Fixed CSS): */
.cart-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 750px; /* Fix: Maximum width for desktop */
    max-height: 90vh; /* Fix: More height on mobile */
    overflow-y: auto;
    overflow-x: hidden; /* Fix: Prevent horizontal scroll */
    
    /* Mobile specific fixes */
    @media (max-width: 768px) {
        width: 95%;
        height: 95vh;
        max-height: 95vh;
        border-radius: 0; /* Full screen feel on mobile */
    }
}

/* Fix scrolling issues */
.cart-modal-content {
    padding: 1rem;
    min-height: 100%;
    display: flex;
    flex-direction: column;
}

.cart-modal-body {
    flex: 1;
    overflow-y: auto;
}
```

## Testing After Bug Fix

### Verification Checklist
```markdown
✅ **BUG FIX VERIFICATION**

**Direct Testing:**
□ Original bug no longer reproduces
□ All reproduction steps work correctly
□ Edge cases tested
□ Different browsers tested (if UI bug)
□ Mobile devices tested (if responsive issue)

**Regression Testing:**
□ Related functionality still works
□ Core user flows unaffected
□ Performance not degraded
□ No new console errors
□ Database integrity maintained

**Automated Testing:**
□ Unit tests pass
□ Integration tests pass
□ E2E tests pass
□ New tests added for this bug (if needed)

**Code Quality:**
□ Code follows project standards
□ Security best practices applied
□ Error handling added
□ Logging added where appropriate
□ Documentation updated
```

### Test Cases for Bug Fix
```javascript
// Test Case Template
describe('Bug Fix: Cart Addition', () => {
    beforeEach(() => {
        localStorage.clear();
    });
    
    test('should handle empty cart correctly', () => {
        // This was the original bug
        expect(() => addToCart('product-1', 1)).not.toThrow();
        
        const cart = JSON.parse(localStorage.getItem('cart'));
        expect(cart).toHaveLength(1);
        expect(cart[0].id).toBe('product-1');
    });
    
    test('should validate inputs', () => {
        expect(() => addToCart(null, 1)).toThrow('Invalid product ID');
        expect(() => addToCart('product-1', 0)).toThrow('Invalid quantity');
        expect(() => addToCart('product-1', -1)).toThrow('Invalid quantity');
    });
    
    test('should update existing product quantity', () => {
        addToCart('product-1', 1);
        addToCart('product-1', 2);
        
        const cart = JSON.parse(localStorage.getItem('cart'));
        expect(cart).toHaveLength(1);
        expect(cart[0].quantity).toBe(3);
    });
});
```

## Bug Prevention Strategies

### Code Review Checklist
```markdown
🔍 **BUG PREVENTION CHECKLIST**

**Input Validation:**
□ All user inputs validated
□ Type checking implemented
□ Range/length limits enforced
□ SQL injection prevention (prepared statements)
□ XSS prevention (output escaping)

**Error Handling:**
□ Try-catch blocks around risky operations
□ Graceful degradation for failed requests
□ User-friendly error messages
□ Proper logging for debugging

**Edge Cases:**
□ Empty data scenarios tested
□ Large data sets considered
□ Network failure scenarios handled
□ Concurrent access scenarios considered

**Performance:**
□ Database queries optimized
□ Large loops avoided
□ Memory usage considered
□ Asset sizes reasonable
```

## Quick Bug Fix Commands

```bash
# Debugging assistance
!debug [error-message] - Help debug specific error
!reproduce [bug-description] - Steps to reproduce bug
!analyze [code-snippet] - Analyze code for potential issues
!trace [function-name] - Trace function execution

# Fix implementation
!fix [bug-type] - Implement bug fix with best practices
!validate [fix] - Validate that fix doesn't introduce new issues
!test [bug-fix] - Generate tests for bug fix
!prevent [bug-pattern] - Suggest prevention measures

# Documentation
!document-fix [bug] - Document the bug fix
!root-cause [issue] - Perform root cause analysis
!impact [bug] - Assess bug impact and priority
!timeline [fix] - Estimate fix timeline
```

## Bug Fix Documentation Template

```markdown
# 🔧 Bug Fix Report

**Bug:** [BUG_TITLE]
**Fix Date:** [DATE]
**Developer:** [NAME]
**Review:** [REVIEWER_NAME]

## 🐛 Original Issue
[Copy from bug report]

## 🎯 Root Cause
[Detailed explanation of what caused the bug]

## 🔧 Solution Implemented
[Explain how the bug was fixed]

### Code Changes:
**Files Modified:**
- [file1.php] - [description of changes]
- [file2.js] - [description of changes]

**Database Changes:**
- [any schema changes or data fixes]

## 🧪 Testing Performed
- [ ] Unit tests
- [ ] Integration tests
- [ ] Manual testing
- [ ] Browser compatibility
- [ ] Mobile testing

## 📚 Lessons Learned
[What can we learn to prevent similar bugs in the future?]

## 🔮 Prevention Measures
[What processes/checks can prevent this type of bug?]

**Deployment Notes:**
[Any special instructions for deploying this fix]
```

---
*Use this template to ensure systematic, thorough bug fixing that not only resolves the immediate issue but also prevents similar bugs in the future.*