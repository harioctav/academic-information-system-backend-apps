# Academic Information System Backend

A robust backend API service built with Laravel for managing academic information systems.

## Features

-   Authentication and Authorization System
-   Role-Based Access Control (RBAC)
    -   Super Admin access
    -   Default user permissions
-   Academic Data Management
    -   Major/Department management
    -   Subject management
    -   Major-Subject relationship handling
-   Bulk Import Support
    -   Excel import for Majors and Subjects
    -   Detailed import feedback and error handling
    -   Transaction-safe imports

## Tech Stack

-   Laravel Framework 11.x
-   PHP 8.x
-   MySQL/PostgreSQL
-   Passport OAuth Authentication
-   Excel Import/Export capabilities

## Getting Started

1. Clone the repository:

```bash
git clone https://github.com/harioctav/academic-information-system-backend-apps.git

```

2. Install dependencies:

```bash
composer install

```

3. Configure environment:

-   Copy .env.example to .env
-   Set up database credentials
-   Configure other necessary environment variables

4. Run migrations and seeders:

```bash
php artisan migrate --seed

```

## API Documentation

The API includes endpoints for:

-   Authentication (Login, Logout)
-   User Management
-   Major/Department Management
-   Subject Management
-   Role & Permission Management

## Security

-   Protected routes with authentication
-   Role-based access control
-   Failed login attempt monitoring
-   Input validation and sanitization

## Contributing

-   Fork the repository
-   Create your feature branch
-   Commit your changes
-   Push to the branch
-   Create a new Pull Request

## Contact

-   Developer: Hari Octavian
-   Email: aldiama.octavian@gmail.com
-   LinkedIn: [Hari Octavian](https://www.linkedin.com/in/aldiama-octavian/)
-   Project: [System Information Academic UT Sukabumi](https://github.com/harioctav/academic-information-system-backend-apps)

## License

This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0). This license requires that:

-   Source code must be made available when distributing the software
-   Modifications must be released under the same license
-   Changes made to the code must be documented
-   If you run a modified version of the software on a server and let others interact with it, you must make the source code available to them

For more details, see the [AGPL-3.0 License](https://www.gnu.org/licenses/agpl-3.0.en.html).
