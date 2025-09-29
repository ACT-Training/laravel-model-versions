# Technical Stack

## Core Framework
- **Application Framework:** Laravel 10.0|11.0|12.0
- **PHP Version:** PHP 8.1+
- **Package Type:** Laravel Package with PSR-4 autoloading

## Database
- **Database System:** Any Laravel-supported database (MySQL, PostgreSQL, SQLite, SQL Server)
- **Schema:** Single polymorphic `versions` table with JSON storage
- **Migrations:** Included migration with proper indexing
- **ORM:** Eloquent ORM with polymorphic relationships

## Testing Framework
- **Primary Testing:** Pest 2.0|3.0 (preferred framework)
- **Fallback Testing:** PHPUnit 10.0|11.0
- **Package Testing:** Orchestra Testbench for Laravel package testing
- **Test Database:** In-memory SQLite for testing

## Package Management
- **Dependency Manager:** Composer
- **Autoloading:** PSR-4 autoloading standard
- **Service Provider:** Laravel Service Provider for integration
- **Configuration:** Publishable configuration file

## Core Dependencies
- **illuminate/database:** Laravel Database package
- **illuminate/support:** Laravel Support package for Service Provider
- **illuminate/events:** For Eloquent event handling

## Development Dependencies
- **pestphp/pest:** Testing framework
- **orchestra/testbench:** Laravel package testing
- **phpunit/phpunit:** Unit testing framework

## Frontend/JavaScript
- **JavaScript Framework:** n/a (Backend package)
- **Import Strategy:** n/a (Backend package)
- **CSS Framework:** n/a (Backend package)
- **UI Component Library:** n/a (Backend package)

## Assets and Hosting
- **Fonts Provider:** n/a (Backend package)
- **Icon Library:** n/a (Backend package)
- **Application Hosting:** Any Laravel-compatible hosting (Forge, Vapor, VPS)
- **Database Hosting:** Depends on application deployment
- **Asset Hosting:** n/a (Backend package)

## Deployment and Repository
- **Deployment Solution:** Packagist for package distribution
- **Code Repository URL:** GitHub repository for open source package
- **License:** MIT License
- **Distribution:** Composer package manager

## Architecture Patterns
- **Design Pattern:** Trait-based functionality extension
- **Event System:** Laravel Eloquent model events
- **Data Storage:** JSON column for efficient version data storage
- **Relationship Pattern:** Polymorphic relationships for universal model support