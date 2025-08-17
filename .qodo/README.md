# Qodo Configuration

This directory contains the Qodo configuration for your Laravel project.

## Files Overview

### `config.json`
Main configuration file that defines:
- Project information and type
- Language and framework settings
- Code quality standards
- Testing configuration
- File patterns for inclusion/exclusion
- Review settings
- AI assistant preferences
- Integration settings

### `rules.json`
Defines code quality rules and standards:
- Laravel-specific best practices
- Security checks
- Performance optimizations
- Code quality metrics
- Severity levels for different rule violations

### `templates.json`
Contains code templates and snippets for:
- Laravel controllers
- Eloquent models
- Database migrations
- Pest tests
- Form requests
- Service classes
- Common validation rules
- Eloquent relationships

### `ignore.txt`
Lists files and directories to exclude from Qodo analysis:
- Dependencies (vendor/, node_modules/)
- Build outputs
- Cache and temporary files
- Environment files
- IDE files
- Logs

## Usage

Qodo will automatically use these configuration files to:
1. Analyze your Laravel code according to best practices
2. Provide intelligent suggestions and improvements
3. Generate code using the defined templates
4. Focus on Laravel-specific patterns and conventions
5. Integrate with your development workflow

## Customization

You can modify these files to:
- Adjust code quality rules
- Add custom templates
- Change file inclusion/exclusion patterns
- Configure integration settings
- Set project-specific preferences

## Laravel-Specific Features

This configuration is optimized for Laravel development and includes:
- PSR-12 coding standards
- Eloquent best practices
- Security vulnerability detection
- Performance optimization suggestions
- Laravel convention enforcement
- Pest testing framework support
- Blade template analysis

## Getting Started

With Qodo initialized, you can now:
1. Use Qodo for code analysis and suggestions
2. Generate code using the templates
3. Get Laravel-specific recommendations
4. Integrate with your Git workflow
5. Leverage AI assistance for development tasks

For more information about using Qodo with Laravel, refer to the Qodo documentation.