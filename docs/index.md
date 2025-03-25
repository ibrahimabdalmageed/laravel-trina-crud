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

- [Installation Guide](installation.md)
- [Quick Start Tutorial](quick-start.md)
- [Configuration Options](configuration.md)

### Core Concepts

- [The HasCrud Trait](has-crud-trait.md)
- [Automatic Validation](schema-validation.md)
- [Custom Validation Rules](custom-validation.md)
- [Controller Integration](controller-integration.md)

### Security & Authorization

- [Permission Management](permissions.md)
- [Integration with Spatie Permission](spatie-integration.md)
- [Model & Attribute Security](model-attribute-security.md)
- [Ownership & Data Access Control](ownership.md)

### API Development

- [API Endpoints](api-endpoints.md)
- [Response Formatting](api-responses.md)
- [Error Handling](error-handling.md)
- [API Versioning](api-versioning.md)
- [Schema Endpoint](schema-endpoint.md)

### User Interface

- [Admin Permissions UI](admin-ui.md)
- [Permission Matrix Management](permission-matrix.md)
- [Role Assignment Interface](role-assignment.md)

### Advanced Topics

- [Custom Controller Actions](custom-actions.md)
- [Extending Base Controllers](extending-controllers.md)
- [Event Handling](events.md)
- [Testing CRUD Endpoints](testing.md)
- [Performance Optimization](performance.md)

### Examples & Tutorials

- [Complete Application Example](example-app.md)
- [User Management System](user-management-example.md)
- [Product Catalog with Categories](product-catalog-example.md)
- [Building a Blog with TrinaCrud](blog-example.md)

### Reference

- [API Reference](api-reference.md)
- [Configuration Reference](config-reference.md)
- [Troubleshooting & FAQ](troubleshooting.md)

## Contributing

We welcome contributions to both the package and this documentation. Please see our [contribution guidelines](contributing.md) for more information.

## Support

If you encounter any issues or have questions, please:

- Check the [Troubleshooting & FAQ](troubleshooting.md) page
- Open an issue on our [GitHub repository](https://github.com/trinavo/laravel-trina-crud/issues)
- Ask a question on the [Laravel community channels](https://laravel.com/docs/master/contributions#support-questions)

## License

The TrinaCrud package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
