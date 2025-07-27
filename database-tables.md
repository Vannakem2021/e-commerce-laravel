# Laravel Vue E-Commerce Database Tables

This document provides an overview of the database tables used in the Laravel Vue E-Commerce project.

## Core Tables

### Users Table
The `users` table stores user information for authentication and user management.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | bigint | primary key, auto-increment | Unique identifier for the user |
| name | string | not null | User's full name |
| email | string | unique, not null | User's email address used for login |
| email_verified_at | timestamp | nullable | Timestamp when the email was verified |
| password | string | not null | Hashed password |
| remember_token | string | nullable | Token for "remember me" functionality |
| created_at | timestamp | nullable | Timestamp when the record was created |
| updated_at | timestamp | nullable | Timestamp when the record was last updated |

### Password Reset Tokens Table
The `password_reset_tokens` table stores tokens for password reset functionality.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| email | string | primary key | Email address associated with the reset token |
| token | string | not null | Reset token |
| created_at | timestamp | nullable | Timestamp when the token was created |

### Sessions Table
The `sessions` table stores session information for users.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | string | primary key | Unique session identifier |
| user_id | bigint | foreign key, nullable, indexed | ID of the user associated with the session |
| ip_address | string(45) | nullable | IP address of the user |
| user_agent | text | nullable | User agent information |
| payload | longtext | not null | Session data |
| last_activity | integer | indexed | Timestamp of the last activity |

## Queue and Job Management Tables

### Jobs Table
The `jobs` table stores queued jobs for background processing.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | bigint | primary key, auto-increment | Unique identifier for the job |
| queue | string | indexed | Queue name |
| payload | longtext | not null | Job data |
| attempts | tinyint unsigned | not null | Number of attempts to run the job |
| reserved_at | integer unsigned | nullable | Timestamp when the job was reserved |
| available_at | integer unsigned | not null | Timestamp when the job becomes available |
| created_at | integer unsigned | not null | Timestamp when the job was created |

### Job Batches Table
The `job_batches` table stores information about batches of jobs.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | string | primary key | Unique identifier for the batch |
| name | string | not null | Batch name |
| total_jobs | integer | not null | Total number of jobs in the batch |
| pending_jobs | integer | not null | Number of pending jobs |
| failed_jobs | integer | not null | Number of failed jobs |
| failed_job_ids | longtext | not null | IDs of failed jobs |
| options | mediumtext | nullable | Batch options |
| cancelled_at | integer | nullable | Timestamp when the batch was cancelled |
| created_at | integer | not null | Timestamp when the batch was created |
| finished_at | integer | nullable | Timestamp when the batch was finished |

### Failed Jobs Table
The `failed_jobs` table stores information about failed jobs.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| id | bigint | primary key, auto-increment | Unique identifier for the failed job |
| uuid | string | unique | Universally unique identifier |
| connection | text | not null | Connection information |
| queue | text | not null | Queue name |
| payload | longtext | not null | Job data |
| exception | longtext | not null | Exception information |
| failed_at | timestamp | default current timestamp | Timestamp when the job failed |

## Cache Tables

### Cache Table
The `cache` table stores cached data.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| key | string | primary key | Cache key |
| value | mediumtext | not null | Cached value |
| expiration | integer | not null | Expiration timestamp |

### Cache Locks Table
The `cache_locks` table stores cache locks for preventing race conditions.

| Column Name | Data Type | Constraints | Description |
|-------------|-----------|-------------|-------------|
| key | string | primary key | Lock key |
| owner | string | not null | Lock owner |
| expiration | integer | not null | Expiration timestamp |

## Future Tables

Based on the project documentation, the following tables are planned but not yet implemented:

1. Products
2. Categories
3. Sub-categories
4. Brands
5. Tags
6. Orders
7. Payments
8. Carts
9. Customers
10. Suppliers
11. Warehouses
12. Inventories
13. Units
14. Discounts
15. Taxes
16. Coupons
17. Product Variations
18. Product Attributes
19. Product Stocks
20. Warranty/Guarantees
21. Roles and Permissions

## Database Configuration

The project is configured to use SQLite by default for development, but also supports MySQL, MariaDB, and PostgreSQL for production environments.

```php
// Default database connection from .env.example
DB_CONNECTION=sqlite
```

## Migrations

The migrations are stored in the `database/migrations` directory with timestamps as prefixes to ensure they run in the correct order.

## Relationships

Currently, only basic relationships are defined:
- Users can have many sessions (one-to-many)

More relationships will be added as the project develops and additional tables are implemented.
