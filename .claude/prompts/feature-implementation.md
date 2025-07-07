# FEATURE IMPLEMENTATION PROMPT TEMPLATE

## Context
You are implementing a new feature for the Fractal Merch e-commerce project. This template ensures comprehensive feature development from planning to deployment.

## Feature Implementation Framework

### 📋 FEATURE SPECIFICATION TEMPLATE

```markdown
# Feature: [FEATURE_NAME]

## 📖 Overview
**Brief Description:** [One sentence description]
**Business Value:** [Why this feature matters]
**User Story:** As a [user type], I want [functionality] so that [benefit]

## 🎯 Acceptance Criteria
- [ ] Criterion 1: [Specific measurable requirement]
- [ ] Criterion 2: [Specific measurable requirement]
- [ ] Criterion 3: [Specific measurable requirement]

## 🔧 Technical Requirements
**Frontend Components:**
- [ ] UI Component 1
- [ ] UI Component 2

**Backend API:**
- [ ] Endpoint 1: [HTTP method] /api/[resource]
- [ ] Endpoint 2: [HTTP method] /api/[resource]

**Database Changes:**
- [ ] New table: [table_name]
- [ ] Modified table: [table_name]
- [ ] New indexes: [index_details]

**Integration Points:**
- [ ] Component A ↔ Component B
- [ ] External API integration
```

### 🏗️ IMPLEMENTATION PHASES

#### Phase 1: Planning & Design
```markdown
**Database Design:**
```sql
-- Create/modify tables needed
CREATE TABLE feature_table (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**API Design:**
```
GET    /api/feature        - List items
POST   /api/feature        - Create item
GET    /api/feature/{id}   - Get specific item
PUT    /api/feature/{id}   - Update item
DELETE /api/feature/{id}   - Delete item
```

**Frontend Components:**
- FeatureList.js - Display list of items
- FeatureForm.js - Create/edit form
- FeatureDetail.js - Item detail view
```

#### Phase 2: Backend Implementation
```php
// API Controller Template
class FeatureController {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function create($data) {
        // Validate input
        $this->validateInput($data);
        
        // Sanitize data
        $cleanData = $this->sanitizeData($data);
        
        // Insert into database
        $stmt = $this->db->prepare("
            INSERT INTO feature_table (user_id, data) 
            VALUES (?, ?)
        ");
        
        return $stmt->execute([$cleanData['user_id'], $cleanData['data']]);
    }
    
    private function validateInput($data) {
        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            throw new InvalidArgumentException('Valid user_id required');
        }
        // Add more validation
    }
    
    private function sanitizeData($data) {
        return [
            'user_id' => filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT),
            'data' => htmlspecialchars($data['data'], ENT_QUOTES, 'UTF-8')
        ];
    }
}
```

#### Phase 3: Frontend Implementation
```javascript
// Frontend Component Template
class FeatureManager {
    constructor() {
        this.apiBase = '/api/feature';
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadFeatures();
    }
    
    async loadFeatures() {
        try {
            const response = await fetch(this.apiBase);
            const features = await response.json();
            this.renderFeatures(features);
        } catch (error) {
            this.handleError('Failed to load features', error);
        }
    }
    
    async createFeature(formData) {
        try {
            const response = await fetch(this.apiBase, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCSRFToken()
                },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                this.showSuccess('Feature created successfully');
                this.loadFeatures(); // Refresh list
            } else {
                throw new Error('Failed to create feature');
            }
        } catch (error) {
            this.handleError('Failed to create feature', error);
        }
    }
    
    renderFeatures(features) {
        const container = document.getElementById('features-container');
        container.innerHTML = features.map(feature => `
            <div class="feature-item" data-id="${feature.id}">
                <h3>${this.escapeHtml(feature.title)}</h3>
                <p>${this.escapeHtml(feature.description)}</p>
                <button onclick="featureManager.editFeature(${feature.id})">Edit</button>
                <button onclick="featureManager.deleteFeature(${feature.id})">Delete</button>
            </div>
        `).join('');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    handleError(message, error) {
        console.error(message, error);
        this.showNotification(message, 'error');
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showNotification(message, type) {
        // Implement notification system
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.featureManager = new FeatureManager();
});
```

#### Phase 4: Integration & Testing
```php
// Integration Test Template
class FeatureIntegrationTest extends PHPUnit\Framework\TestCase {
    private $controller;
    private $testDB;
    
    protected function setUp(): void {
        // Setup test database
        $this->testDB = new TestDatabase();
        $this->controller = new FeatureController($this->testDB);
    }
    
    public function testCreateFeature() {
        $testData = [
            'user_id' => 1,
            'data' => 'Test feature data'
        ];
        
        $result = $this->controller->create($testData);
        $this->assertTrue($result);
        
        // Verify in database
        $created = $this->testDB->query("SELECT * FROM feature_table WHERE user_id = 1");
        $this->assertCount(1, $created);
    }
    
    public function testCreateFeatureWithInvalidData() {
        $this->expectException(InvalidArgumentException::class);
        
        $invalidData = [
            'user_id' => '', // Invalid
            'data' => 'Test data'
        ];
        
        $this->controller->create($invalidData);
    }
}
```

### 🧪 TESTING STRATEGY

#### Unit Tests
```javascript
// JavaScript Unit Tests (Jest)
describe('FeatureManager', () => {
    let featureManager;
    
    beforeEach(() => {
        featureManager = new FeatureManager();
        global.fetch = jest.fn();
    });
    
    test('should load features on init', async () => {
        const mockFeatures = [
            { id: 1, title: 'Test Feature', description: 'Test Description' }
        ];
        
        fetch.mockResolvedValueOnce({
            ok: true,
            json: async () => mockFeatures
        });
        
        await featureManager.loadFeatures();
        
        expect(fetch).toHaveBeenCalledWith('/api/feature');
    });
    
    test('should handle API errors gracefully', async () => {
        fetch.mockRejectedValueOnce(new Error('Network error'));
        
        const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
        
        await featureManager.loadFeatures();
        
        expect(consoleSpy).toHaveBeenCalledWith(
            'Failed to load features',
            expect.any(Error)
        );
    });
});
```

#### End-to-End Tests
```javascript
// E2E Tests (Cypress)
describe('Feature Management', () => {
    beforeEach(() => {
        cy.visit('/admin/features');
        cy.login('admin@test.com', 'password');
    });
    
    it('should create a new feature', () => {
        cy.get('[data-testid="add-feature-btn"]').click();
        cy.get('[data-testid="feature-title"]').type('New Feature');
        cy.get('[data-testid="feature-description"]').type('Feature description');
        cy.get('[data-testid="save-feature"]').click();
        
        cy.get('.notification.success').should('contain', 'Feature created successfully');
        cy.get('.feature-item').should('contain', 'New Feature');
    });
    
    it('should validate required fields', () => {
        cy.get('[data-testid="add-feature-btn"]').click();
        cy.get('[data-testid="save-feature"]').click();
        
        cy.get('.field-error').should('contain', 'Title is required');
    });
});
```

### 📚 DOCUMENTATION TEMPLATE

```markdown
# Feature Documentation: [FEATURE_NAME]

## 📖 Overview
[Detailed description of what the feature does]

## 🎯 User Guide
### How to Use
1. Navigate to [page/section]
2. Click [action]
3. Fill in [required fields]
4. Submit [form/action]

### Screenshots
![Feature Screenshot](screenshots/feature-example.png)

## 🔧 Technical Documentation

### API Endpoints
```
POST /api/feature
Request Body:
{
    "title": "string (required)",
    "description": "string (optional)",
    "user_id": "integer (required)"
}

Response:
{
    "success": true,
    "id": 123,
    "message": "Feature created successfully"
}
```

### Database Schema
```sql
CREATE TABLE feature_table (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
);
```

### Frontend Components
- **FeatureList**: Displays paginated list of features
- **FeatureForm**: Create/edit feature form with validation
- **FeatureDetail**: Read-only feature details view

## 🧪 Testing
- Unit Tests: 95% coverage
- Integration Tests: All API endpoints covered
- E2E Tests: Complete user flows tested

## 🚀 Deployment Notes
1. Run database migration: `php migrate.php`
2. Clear cache: `php clear-cache.php`
3. Update dependencies: `composer install --no-dev`
4. Test critical paths after deployment

## 🔍 Troubleshooting
### Common Issues
1. **Feature not saving**: Check CSRF token and validation
2. **List not loading**: Verify API endpoint and database connection
3. **Permission errors**: Ensure user has proper role assignment
```

## Quick Implementation Commands

```bash
# Full feature implementation
!implement [feature-name] - Complete feature from planning to deployment

# Backend focus
!api [feature-name] - Create REST API endpoints
!model [feature-name] - Create database model and migrations
!validate [feature-name] - Add input validation and sanitization

# Frontend focus
!component [feature-name] - Create frontend components
!form [feature-name] - Create forms with validation
!integrate [feature-name] - Connect frontend to backend API

# Testing focus
!test-suite [feature-name] - Generate complete test suite
!unit-tests [feature-name] - Create unit tests
!e2e-tests [feature-name] - Create end-to-end tests

# Documentation
!document [feature-name] - Generate feature documentation
!api-doc [feature-name] - Create API documentation
!user-guide [feature-name] - Write user guide
```

## Implementation Checklist

```
✅ PLANNING PHASE:
□ Feature specification written
□ User stories defined
□ Acceptance criteria clear
□ Technical requirements documented
□ Database schema designed
□ API endpoints planned

✅ DEVELOPMENT PHASE:
□ Database migration created
□ Backend API implemented
□ Frontend components created
□ Input validation added
□ Error handling implemented
□ Security measures applied

✅ TESTING PHASE:
□ Unit tests written (80%+ coverage)
□ Integration tests created
□ E2E tests implemented
□ Manual testing completed
□ Performance tested
□ Security tested

✅ DOCUMENTATION PHASE:
□ Technical documentation written
□ API documentation generated
□ User guide created
□ Code comments added
□ README updated

✅ DEPLOYMENT PHASE:
□ Code reviewed and approved
□ Database migration tested
□ Staging deployment successful
□ Production deployment planned
□ Rollback plan documented
□ Monitoring alerts configured
```

---
*Use this template to ensure consistent, secure, and well-tested feature implementation across the entire development team.*