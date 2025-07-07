# Enterprise Dashboard Backend System

Professional-grade PHP backend implementation for the FractalMerch Admin Dashboard. This system provides enterprise-level features including caching, real-time monitoring, security hardening, and performance optimization.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Architecture](#architecture)
- [Installation](#installation)
- [API Reference](#api-reference)
- [Configuration](#configuration)
- [Performance](#performance)
- [Security](#security)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

## 🎯 Overview

The Enterprise Dashboard Backend is a comprehensive system designed to power modern admin dashboards with:

- **Real-time data updates** via AJAX polling and WebSocket support
- **Professional caching layers** with intelligent invalidation
- **Security hardening** with CSRF protection and rate limiting
- **Performance monitoring** with detailed metrics collection
- **Business intelligence** with advanced analytics and reporting
- **Enterprise-grade error handling** with comprehensive logging

## ✨ Features

### Core Features
- ✅ **Multi-layer Caching System** - File-based caching with TTL and automatic cleanup
- ✅ **Real-time Analytics** - Live user tracking and system monitoring
- ✅ **API-First Architecture** - RESTful endpoints with comprehensive error handling
- ✅ **Security Hardening** - CSRF protection, input validation, rate limiting
- ✅ **Performance Monitoring** - CPU, memory, and database performance tracking
- ✅ **Audit Logging** - Complete activity tracking and security event logging
- ✅ **Health Monitoring** - System health checks and status reporting
- ✅ **Notification System** - Real-time alerts and notifications

### Advanced Features
- 🚀 **Query Optimization** - Prepared statements with performance indexes
- 🚀 **Business Intelligence** - Revenue analytics and customer insights
- 🚀 **Error Recovery** - Graceful degradation and fallback mechanisms
- 🚀 **Mobile Optimization** - Responsive design with touch support
- 🚀 **Offline Support** - Service Worker integration for offline functionality

## 🏗️ Architecture

```
admin/
├── backend/
│   ├── DashboardBackend.php       # Core backend class
│   ├── DashboardExtensions.php    # Extended functionality
│   └── README.md                  # This documentation
├── api/
│   └── dashboard_api.php          # RESTful API endpoints
├── assets/js/
│   └── dashboard-realtime.js      # Real-time client
└── sql/
    └── enterprise_tables.sql     # Database schema
```

### Component Overview

#### DashboardBackend.php
Main backend class providing:
- **CacheManager** - Professional caching with TTL
- **Logger** - Multi-level logging system
- **SecurityManager** - CSRF and rate limiting
- **MetricsCollector** - Performance monitoring

#### dashboard_api.php
REST API providing endpoints:
- `GET /stats` - Dashboard statistics
- `GET /real-time` - Real-time metrics
- `POST /cache/clear` - Cache management
- `GET /health` - System health check

#### dashboard-realtime.js
JavaScript client featuring:
- **WebSocket support** for instant updates
- **AJAX polling** with retry logic
- **Performance monitoring** client-side
- **Notification system** with browser alerts

## 🚀 Installation

### Prerequisites
- PHP 7.4+ with PDO extension
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Step 1: Database Setup
```sql
-- Import the enterprise schema
mysql -u root -p proyecto_web < admin/sql/enterprise_tables.sql
```

### Step 2: Directory Permissions
```bash
# Create required directories
mkdir -p admin/cache admin/logs

# Set permissions
chmod 755 admin/cache admin/logs
chown www-data:www-data admin/cache admin/logs
```

### Step 3: Configuration
```php
// In your dashboard.php
$config = [
    'cache_enabled' => true,
    'debug_mode' => false,
    'rate_limit_enabled' => true,
    'security_enabled' => true,
    'realtime_enabled' => true,
    'performance_monitoring' => true
];

$backend = new DashboardBackend($pdo, $config);
```

### Step 4: JavaScript Integration
```html
<!-- Include real-time client -->
<script src="assets/js/dashboard-realtime.js"></script>

<script>
// Initialize with configuration
window.dashboardRealTime = new DashboardRealTime({
    apiBaseUrl: './api/dashboard_api.php',
    updateInterval: 30000,
    enableWebSocket: true,
    enableNotifications: true
});
</script>
```

## 📡 API Reference

### Authentication
All API endpoints require admin authentication except `/health`.

```javascript
// Headers required
{
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
}
```

### Endpoints

#### GET /stats
Get comprehensive dashboard statistics.

**Response:**
```json
{
    "success": true,
    "data": {
        "total_users": 1250,
        "total_orders": 850,
        "total_revenue": 125000.50,
        "conversion_rate": 3.2,
        "top_products": [...],
        "recent_orders": [...],
        "performance_metrics": {...}
    }
}
```

#### GET /real-time
Get real-time system metrics.

**Response:**
```json
{
    "success": true,
    "data": {
        "active_users": 45,
        "current_cart_value": 15750.00,
        "system_health": {...},
        "server_load": {...}
    }
}
```

#### POST /cache/clear
Clear system cache.

**Request:**
```json
{
    "pattern": "dashboard_stats" // optional
}
```

#### GET /health
System health check (no auth required).

**Response:**
```json
{
    "success": true,
    "health": {
        "overall": "healthy",
        "database": {"score": 100, "status": "good"},
        "cache": {"score": 95, "status": "good"},
        "storage": {"score": 85, "status": "warning"}
    }
}
```

### Rate Limiting
- **Stats endpoint:** 50 requests/minute
- **Real-time endpoint:** 120 requests/minute
- **Cache operations:** 5 requests/5 minutes

## ⚙️ Configuration

### Backend Configuration
```php
$config = [
    // Core settings
    'cache_enabled' => true,          // Enable file-based caching
    'debug_mode' => false,            // Show detailed error messages
    'rate_limit_enabled' => true,     // Enable API rate limiting
    'security_enabled' => true,       // Enable CSRF protection
    'realtime_enabled' => true,       // Enable real-time features
    'performance_monitoring' => true, // Track performance metrics
    
    // Cache settings
    'cache_ttl' => 300,               // Default cache TTL (seconds)
    'cache_dir' => '../cache/',       // Cache directory
    
    // Rate limiting
    'rate_limit_window' => 3600,      // Rate limit window (seconds)
    'rate_limit_max' => 100,          // Max requests per window
    
    // Logging
    'log_level' => 'info',            // Minimum log level
    'log_dir' => '../logs/',          // Log directory
];
```

### JavaScript Configuration
```javascript
window.dashboardConfig = {
    // API settings
    apiBaseUrl: './api/dashboard_api.php',
    updateInterval: 30000,            // Update frequency (ms)
    retryInterval: 5000,              // Retry delay (ms)
    maxRetries: 3,                    // Max retry attempts
    
    // Features
    enableWebSocket: true,            // Use WebSocket if available
    enableNotifications: true,        // Browser notifications
    enableCache: true,                // Client-side caching
    
    // Performance
    enablePerformanceMonitoring: true,
    enableVisibilityHandling: true,   // Reduce updates when hidden
    
    // Debug
    debug: false                      // Console logging
};
```

## ⚡ Performance

### Caching Strategy
The system implements a multi-layer caching approach:

1. **Application Cache** - PHP file-based caching with TTL
2. **Database Query Cache** - MySQL query cache optimization
3. **Client Cache** - JavaScript-based response caching
4. **Browser Cache** - HTTP cache headers for static assets

### Query Optimization
- **Prepared Statements** - All queries use prepared statements
- **Indexed Queries** - Strategic database indexes for performance
- **Query Batching** - Multiple metrics in single queries
- **Connection Pooling** - Efficient database connection management

### Performance Metrics
```php
// Record custom performance metrics
record_performance_metric('api_call', 'dashboard_stats', 150, 'ms');

// Built-in metrics collected:
- API response times
- Database query duration
- Memory usage
- CPU load
- Cache hit rates
```

## 🔒 Security

### Security Features
- **CSRF Protection** - Tokens with 30-minute expiration
- **Input Validation** - Comprehensive sanitization
- **Rate Limiting** - Per-IP and per-user limits
- **SQL Injection Prevention** - Prepared statements only
- **XSS Protection** - Output sanitization
- **Session Security** - Secure session handling

### Security Headers
```php
// Automatically added to API responses
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
```

### Audit Logging
All admin actions are logged with:
- User ID and session information
- IP address and user agent
- Action type and details
- Timestamp and request ID

### Security Events
The system monitors and logs:
- Failed login attempts
- Permission denied events
- Suspicious activity patterns
- Rate limit violations

## 📊 Monitoring

### System Health
Real-time monitoring of:
- **Database Performance** - Query times and connection count
- **File System** - Disk usage and directory permissions
- **Memory Usage** - Current and peak memory consumption
- **CPU Load** - System load averages
- **Cache Performance** - Hit rates and size metrics

### Performance Metrics
Automated collection of:
- API response times
- Database query performance
- Memory and CPU usage
- Error rates and types
- User activity patterns

### Alerts and Notifications
- **System Health Alerts** - Critical system issues
- **Performance Warnings** - Slow queries or high load
- **Security Events** - Suspicious activity detection
- **Business Metrics** - Revenue and conversion alerts

### Dashboard Visualization
- Real-time system status indicators
- Performance charts and graphs
- Health score visualization
- Alert notification center

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Errors
```bash
# Check database connection
mysql -u root -p -e "SELECT 1"

# Verify table creation
mysql -u root -p proyecto_web -e "SHOW TABLES LIKE '%audit%'"
```

#### Cache Issues
```bash
# Clear file cache
rm -rf admin/cache/*.cache

# Check permissions
ls -la admin/cache/
```

#### Performance Issues
```php
// Enable debug mode
$config['debug_mode'] = true;

// Check logs
tail -f admin/logs/dashboard_*.log
```

### Debug Mode
Enable detailed error reporting:
```php
$config['debug_mode'] = true;
```

This will:
- Show detailed error messages
- Log all API calls
- Display performance metrics
- Enable JavaScript console logging

### Log Files
- `admin/logs/dashboard_YYYY-MM-DD.log` - General application logs
- `admin/logs/errors/error_YYYY-MM-DD.log` - Error logs
- `admin/logs/api_access_YYYY-MM-DD.log` - API access logs

### Health Check
Monitor system health:
```bash
curl http://localhost/proyecto/admin/api/dashboard_api.php?endpoint=health
```

### Performance Monitoring
Check system performance:
```javascript
// In browser console
console.log(window.dashboardRealTime.performanceMonitor.getStats());
```

## 📈 Maintenance

### Daily Maintenance
- Log rotation and cleanup
- Cache cleanup and optimization
- Performance metric analysis
- Security event review

### Weekly Maintenance
- Database optimization
- Index analysis and updates
- Cache hit rate analysis
- System health trends

### Monthly Maintenance
- Full system backup
- Security audit review
- Performance baseline updates
- Capacity planning review

## 🤝 Contributing

1. Follow PSR-12 coding standards
2. Add comprehensive PHPDoc comments
3. Include unit tests for new features
4. Update documentation for API changes
5. Test in both development and production environments

## 📄 License

This enterprise dashboard backend is part of the FractalMerch project and follows the same licensing terms.

---

**Enterprise Dashboard Backend v1.0.0**  
Built with ❤️ for professional e-commerce management

For support or questions, please refer to the main project documentation or create an issue in the project repository.