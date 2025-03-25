# Laravel TrinaCrud Documentation

## Introduction

TrinaCrud is a Laravel package that simplifies and accelerates the development of CRUD APIs with built-in authorization, validation, and permission management. It follows Laravel's philosophy of elegant, expressive syntax while providing powerful features out of the box.

By using TrinaCrud, you can reduce boilerplate code and focus on building your application's unique features rather than implementing repetitive CRUD operations and authorization logic.

## Key Features

- 🚀 **Quick Setup**: Add a simple trait to your models to instantly get CRUD endpoints
- 🔒 **Built-in Security**: Integrated with Spatie Permissions for robust authorization
- 🧩 **Flexible**: Customizable routes, middleware, and validation
- 📱 **API Ready**: Perfect for building backends for SPA and mobile applications
- 🛠️ **Permission Management**: Visual interface for managing roles and permissions
- 📊 **Single Source of Truth**: Your model and database schema drive validation and security
- 🔄 **Auto-Generated Validation**: Rules automatically derived from database schema
- 👤 **Ownership Control**: Integration with the Ownable package for user-based data access

## Documentation Index

### Getting Started

- [Installation Guide](pages/installation.md)
- [Quick Start Tutorial](pages/quick-start.md)
- [Configuration Options](pages/configuration.md)

### Core Concepts

- [The HasCrud Trait](pages/has-crud-trait.md)
- [Automatic Validation](pages/schema-validation.md)
- [Custom Validation Rules](pages/custom-validation.md)
- [Controller Integration](pages/controller-integration.md)

### Security & Authorization

- [Permission Management](pages/permissions.md)
- [Integration with Spatie Permission](pages/spatie-integration.md)
- [Model & Attribute Security](pages/model-attribute-security.md)
- [Ownership & Data Access Control](pages/ownership.md)

### API Development

- [API Endpoints](pages/api-endpoints.md)
- [Response Formatting](pages/api-responses.md)
- [Error Handling](pages/error-handling.md)
- [API Versioning](pages/api-versioning.md)
- [Schema Endpoint](pages/schema-endpoint.md)

### User Interface

- [Admin Permissions UI](pages/admin-ui.md)
- [Permission Matrix Management](pages/permission-matrix.md)
- [Role Assignment Interface](pages/role-assignment.md)

### Advanced Topics

- [Custom Controller Actions](pages/custom-actions.md)
- [Extending Base Controllers](pages/extending-controllers.md)
- [Event Handling](pages/events.md)
- [Testing CRUD Endpoints](pages/testing.md)
- [Performance Optimization](pages/performance.md)

### Examples & Tutorials

- [Complete Application Example](pages/example-app.md)
- [User Management System](pages/user-management-example.md)
- [Product Catalog with Categories](pages/product-catalog-example.md)
- [Building a Blog with TrinaCrud](pages/blog-example.md)

### Reference

- [API Reference](pages/api-reference.md)
- [Configuration Reference](pages/config-reference.md)
- [Troubleshooting & FAQ](pages/troubleshooting.md)

## Contributing

We welcome contributions to both the package and this documentation. Please see our [contribution guidelines](pages/contributing.md) for more information.

## Support

If you encounter any issues or have questions, please:

- Check the [Troubleshooting & FAQ](pages/troubleshooting.md) page
- Open an issue on our [GitHub repository](https://github.com/doonfrs/laravel-trina-crud/issues)
- Ask a question on the [Laravel community channels](https://laravel.com/docs/master/contributions#support-questions)

## License

The TrinaCrud package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
