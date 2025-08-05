# XShop E-commerce Database Schema

This document outlines the complete database structure for the XShop e-commerce platform based on Laravel migrations.

## Table of Contents

- [Authentication & User Management](#authentication--user-management)
- [Permission System](#permission-system)
- [Product Catalog](#product-catalog)
- [Order Management](#order-management)
- [Customer Management](#customer-management)
- [Discounts & Promotions](#discounts--promotions)
- [Shipping & Transport](#shipping--transport)
- [Content Management](#content-management)
- [Media Management](#media-management)
- [Additional Features](#additional-features)
- [System Tables](#system-tables)

---

## Authentication & User Management

### `users` table

Admin and staff user accounts.

| Field             | Type         | Attributes                  | Description                  |
| ----------------- | ------------ | --------------------------- | ---------------------------- |
| id                | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier            |
| name              | varchar(255) | NOT NULL                    | User full name               |
| email             | varchar(255) | UNIQUE, NOT NULL            | User email address           |
| email_verified_at | timestamp    | NULLABLE                    | Email verification timestamp |
| password          | varchar(255) | NOT NULL                    | Hashed password              |
| remember_token    | varchar(100) | NULLABLE                    | Remember me token            |
| created_at        | timestamp    | NULLABLE                    | Record creation time         |
| updated_at        | timestamp    | NULLABLE                    | Record update time           |

### `customers` table

Customer accounts with extended profile information.

| Field             | Type            | Attributes                  | Description                  |
| ----------------- | --------------- | --------------------------- | ---------------------------- |
| id                | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier            |
| name              | varchar(255)    | NULLABLE                    | Customer full name           |
| email             | varchar(255)    | UNIQUE, NULLABLE            | Customer email               |
| email_verified_at | timestamp       | NULLABLE                    | Email verification timestamp |
| password          | varchar(255)    | NULLABLE                    | Hashed password              |
| state             | int unsigned    | NULLABLE                    | State/province ID            |
| city              | int unsigned    | NULLABLE                    | City ID                      |
| mobile            | varchar(15)     | UNIQUE, NULLABLE            | Mobile phone number          |
| address           | varchar(2048)   | NULLABLE                    | Customer address             |
| postal_code       | varchar(15)     | NULLABLE                    | Postal/ZIP code              |
| sms               | varchar(10)     | NULLABLE                    | SMS verification code        |
| code              | varchar(10)     | NULLABLE                    | Verification code            |
| colleague         | boolean         | DEFAULT false               | Is colleague flag            |
| description       | text            | NULLABLE                    | Customer description         |
| credit            | bigint unsigned | DEFAULT 0                   | Customer credit balance      |
| remember_token    | varchar(100)    | NULLABLE                    | Remember me token            |
| created_at        | timestamp       | NULLABLE                    | Record creation time         |
| updated_at        | timestamp       | NULLABLE                    | Record update time           |

---

## Permission System

### `permissions` table

System permissions using Spatie Laravel Permission package.

| Field      | Type         | Attributes                  | Description          |
| ---------- | ------------ | --------------------------- | -------------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| name       | varchar(255) | NOT NULL                    | Permission name      |
| guard_name | varchar(255) | NOT NULL                    | Guard name           |
| created_at | timestamp    | NULLABLE                    | Record creation time |
| updated_at | timestamp    | NULLABLE                    | Record update time   |

**Indexes:** UNIQUE(name, guard_name)

### `roles` table

User roles for permission management.

| Field      | Type         | Attributes                  | Description          |
| ---------- | ------------ | --------------------------- | -------------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| name       | varchar(255) | NOT NULL                    | Role name            |
| guard_name | varchar(255) | NOT NULL                    | Guard name           |
| created_at | timestamp    | NULLABLE                    | Record creation time |
| updated_at | timestamp    | NULLABLE                    | Record update time   |

**Indexes:** UNIQUE(name, guard_name)

### `model_has_permissions` table

Many-to-many relationship between models and permissions.

| Field         | Type            | Attributes  | Description       |
| ------------- | --------------- | ----------- | ----------------- |
| permission_id | bigint unsigned | FOREIGN KEY | Permission ID     |
| model_type    | varchar(255)    | NOT NULL    | Model class name  |
| model_id      | bigint unsigned | NOT NULL    | Model instance ID |

**Foreign Keys:** permission_id → permissions(id)

### `model_has_roles` table

Many-to-many relationship between models and roles.

| Field      | Type            | Attributes  | Description       |
| ---------- | --------------- | ----------- | ----------------- |
| role_id    | bigint unsigned | FOREIGN KEY | Role ID           |
| model_type | varchar(255)    | NOT NULL    | Model class name  |
| model_id   | bigint unsigned | NOT NULL    | Model instance ID |

**Foreign Keys:** role_id → roles(id)

### `role_has_permissions` table

Many-to-many relationship between roles and permissions.

| Field         | Type            | Attributes  | Description   |
| ------------- | --------------- | ----------- | ------------- |
| permission_id | bigint unsigned | FOREIGN KEY | Permission ID |
| role_id       | bigint unsigned | FOREIGN KEY | Role ID       |

**Foreign Keys:**

- permission_id → permissions(id)
- role_id → roles(id)

---

## Product Catalog

### `cats` table

Product categories with hierarchical structure.

| Field       | Type         | Attributes                  | Description           |
| ----------- | ------------ | --------------------------- | --------------------- |
| id          | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| name        | varchar(128) | NOT NULL                    | Category name         |
| slug        | varchar(128) | UNIQUE, NOT NULL            | URL-friendly name     |
| description | text         | NULLABLE                    | Category description  |
| sort        | int          | DEFAULT 0                   | Sort order            |
| image       | varchar(255) | NULLABLE                    | Category image path   |
| parent_id   | int unsigned | NULLABLE, INDEX             | Parent category ID    |
| deleted_at  | timestamp    | NULLABLE                    | Soft delete timestamp |
| created_at  | timestamp    | NULLABLE                    | Record creation time  |
| updated_at  | timestamp    | NULLABLE                    | Record update time    |

### `products` table

Main product information with comprehensive e-commerce fields.

| Field          | Type             | Attributes                  | Description           |
| -------------- | ---------------- | --------------------------- | --------------------- |
| id             | bigint           | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| name           | varchar(255)     | NOT NULL                    | Product name          |
| slug           | varchar(255)     | UNIQUE, INDEX               | URL-friendly name     |
| description    | longtext         | NULLABLE                    | Product description   |
| excerpt        | text             | NULLABLE                    | Short product summary |
| sku            | varchar(255)     | UNIQUE, NULLABLE            | Stock Keeping Unit    |
| virtual        | boolean          | DEFAULT false, INDEX        | Is virtual product    |
| downloadable   | boolean          | DEFAULT false, INDEX        | Is downloadable       |
| price          | bigint unsigned  | NULLABLE, INDEX             | Product price         |
| cat_id         | bigint unsigned  | FOREIGN KEY                 | Main category ID      |
| user_id        | bigint unsigned  | FOREIGN KEY                 | Creator user ID       |
| on_sale        | boolean          | DEFAULT true, INDEX         | Is on sale            |
| stock_quantity | bigint unsigned  | DEFAULT 0                   | Stock quantity        |
| stock_status   | enum             | DEFAULT 'IN_STOCK', INDEX   | Stock status          |
| rating_count   | bigint unsigned  | DEFAULT 0                   | Number of ratings     |
| average_rating | decimal(3,2)     | DEFAULT 0.00                | Average rating        |
| total_sales    | bigint unsigned  | DEFAULT 0                   | Total sales count     |
| active         | boolean          | DEFAULT true                | Is active             |
| view_count     | bigint unsigned  | DEFAULT 0                   | View count            |
| sell_count     | bigint unsigned  | DEFAULT 0                   | Sell count            |
| image_index    | tinyint unsigned | DEFAULT 0                   | Main image index      |
| deleted_at     | timestamp        | NULLABLE                    | Soft delete timestamp |
| created_at     | timestamp        | NULLABLE                    | Record creation time  |
| updated_at     | timestamp        | NULLABLE                    | Record update time    |

**Foreign Keys:**

- cat_id → cats(id)
- user_id → users(id)

**Enums:**

- stock_status: 'IN_STOCK', 'OUT_STOCK', 'BACK_ORDER'

### `cat_product` table

Many-to-many relationship between categories and products.

| Field      | Type            | Attributes  | Description |
| ---------- | --------------- | ----------- | ----------- |
| cat_id     | bigint unsigned | FOREIGN KEY | Category ID |
| product_id | bigint unsigned | FOREIGN KEY | Product ID  |

**Foreign Keys:**

- cat_id → cats(id) ON DELETE CASCADE
- product_id → products(id) ON DELETE CASCADE



### `quantities` table

Product variants and inventory management.

| Field      | Type            | Attributes                  | Description           |
| ---------- | --------------- | --------------------------- | --------------------- |
| id         | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| product_id | bigint unsigned | FOREIGN KEY                 | Product ID            |
| count      | int unsigned    | DEFAULT 0                   | Stock count           |
| price      | bigint unsigned | DEFAULT 0                   | Variant price         |
| image      | bigint unsigned | NULLABLE                    | Variant image ID      |
| data       | longtext        | NULLABLE                    | Variant data (JSON)   |
| deleted_at | timestamp       | NULLABLE                    | Soft delete timestamp |
| created_at | timestamp       | NULLABLE                    | Record creation time  |
| updated_at | timestamp       | NULLABLE                    | Record update time    |

**Foreign Keys:** product_id → products(id) ON DELETE CASCADE

---

## Order Management

### `invoices` table

Order/invoice management with comprehensive e-commerce features.

| Field           | Type            | Attributes                  | Description            |
| --------------- | --------------- | --------------------------- | ---------------------- |
| id              | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier      |
| customer_id     | bigint unsigned | FOREIGN KEY                 | Customer ID            |
| status          | enum            | DEFAULT 'PENDING'           | Order status           |
| total_price     | bigint unsigned | DEFAULT 0                   | Total order price      |
| meta            | json            | NULLABLE                    | Additional metadata    |
| discount_id     | bigint unsigned | NULLABLE, FOREIGN KEY       | Applied discount ID    |
| desc            | text            | NULLABLE                    | Order description      |
| hash            | varchar(32)     | UNIQUE, NULLABLE            | Order hash             |
| transport_id    | bigint unsigned | NULLABLE, FOREIGN KEY       | Shipping method ID     |
| transport_price | bigint unsigned | DEFAULT 0                   | Shipping cost          |
| credit_price    | bigint unsigned | DEFAULT 0                   | Credit used            |
| reserve         | boolean         | DEFAULT 0                   | Is reserved order      |
| invoice_id      | bigint unsigned | NULLABLE, FOREIGN KEY       | Parent invoice ID      |
| address_alt     | varchar(255)    | NULLABLE                    | Alternative address    |
| address_id      | bigint unsigned | NULLABLE, FOREIGN KEY       | Address ID             |
| tracking_code   | varchar(255)    | NULLABLE                    | Shipping tracking code |
| deleted_at      | timestamp       | NULLABLE                    | Soft delete timestamp  |
| created_at      | timestamp       | NULLABLE                    | Record creation time   |
| updated_at      | timestamp       | NULLABLE                    | Record update time     |

**Foreign Keys:**

- customer_id → customers(id) ON DELETE CASCADE
- discount_id → discounts(id) ON DELETE CASCADE
- transport_id → transports(id) ON DELETE CASCADE
- address_id → addresses(id) ON DELETE CASCADE
- invoice_id → invoices(id) ON DELETE CASCADE

**Enums:**

- status: 'PENDING', 'PROCESSING', 'COMPLETED', 'CANCELED', 'FAILED'

### `invoice_product` table

Order line items linking invoices to products.

| Field       | Type            | Attributes                  | Description           |
| ----------- | --------------- | --------------------------- | --------------------- |
| id          | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| invoice_id  | bigint unsigned | FOREIGN KEY                 | Invoice ID            |
| product_id  | bigint unsigned | FOREIGN KEY                 | Product ID            |
| quantity_id | bigint unsigned | NOT NULL                    | Quantity/variant ID   |
| count       | int             | DEFAULT 1                   | Item quantity         |
| price_total | int unsigned    | NOT NULL                    | Total price for items |
| data        | json            | NULLABLE                    | Additional item data  |
| created_at  | timestamp       | NULLABLE                    | Record creation time  |
| updated_at  | timestamp       | NULLABLE                    | Record update time    |

**Foreign Keys:**

- invoice_id → invoices(id) ON DELETE CASCADE
- product_id → products(id) ON DELETE CASCADE

### `payments` table

Payment transactions and processing.

| Field        | Type            | Attributes                  | Description          |
| ------------ | --------------- | --------------------------- | -------------------- |
| id           | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| invoice_id   | bigint unsigned | FOREIGN KEY                 | Invoice ID           |
| amount       | bigint unsigned | NULLABLE                    | Payment amount       |
| type         | enum            | DEFAULT 'ONLINE'            | Payment method       |
| status       | enum            | DEFAULT 'PENDING'           | Payment status       |
| order_id     | varchar(255)    | UNIQUE                      | Payment order ID     |
| reference_id | varchar(255)    | NULLABLE                    | Payment reference    |
| comment      | text            | NULLABLE                    | Payment comment      |
| meta         | json            | NULLABLE                    | Payment metadata     |
| created_at   | timestamp       | NULLABLE                    | Record creation time |
| updated_at   | timestamp       | NULLABLE                    | Record update time   |

**Foreign Keys:** invoice_id → invoices(id) ON DELETE CASCADE

**Enums:**

- type: 'ONLINE', 'CHEQUE', 'CASH', 'CARD', 'CASH_ON_DELIVERY'
- status: 'PENDING', 'SUCCESS', 'FAIL', 'CANCEL'

---

## Customer Management

### `addresses` table

Customer shipping and billing addresses.

| Field       | Type            | Attributes                  | Description             |
| ----------- | --------------- | --------------------------- | ----------------------- |
| id          | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier       |
| address     | text            | NOT NULL                    | Full address            |
| customer_id | bigint unsigned | FOREIGN KEY                 | Customer ID             |
| lat         | double          | NULLABLE                    | Latitude coordinate     |
| lng         | double          | NULLABLE                    | Longitude coordinate    |
| state       | int unsigned    | NULLABLE                    | State/province ID       |
| city        | int unsigned    | NULLABLE                    | City ID                 |
| data        | json            | NOT NULL                    | Additional address data |
| deleted_at  | timestamp       | NULLABLE                    | Soft delete timestamp   |
| created_at  | timestamp       | NULLABLE                    | Record creation time    |
| updated_at  | timestamp       | NULLABLE                    | Record update time      |

**Foreign Keys:** customer_id → customers(id) ON DELETE CASCADE

### `customer_product` table

Customer favorites/wishlist functionality.

| Field       | Type            | Attributes  | Description          |
| ----------- | --------------- | ----------- | -------------------- |
| customer_id | bigint unsigned | FOREIGN KEY | Customer ID          |
| product_id  | bigint unsigned | FOREIGN KEY | Product ID           |
| created_at  | timestamp       | NULLABLE    | Record creation time |
| updated_at  | timestamp       | NULLABLE    | Record update time   |

**Foreign Keys:**

- customer_id → customers(id) ON DELETE CASCADE
- product_id → products(id) ON DELETE CASCADE

### `credits` table

Customer credit/wallet system for store credit management.

| Field       | Type            | Attributes                  | Description          |
| ----------- | --------------- | --------------------------- | -------------------- |
| id          | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| customer_id | bigint unsigned | FOREIGN KEY                 | Customer ID          |
| amount      | bigint          | NOT NULL                    | Credit amount        |
| type        | enum            | NOT NULL                    | Credit type          |
| description | text            | NULLABLE                    | Credit description   |
| created_at  | timestamp       | NULLABLE                    | Record creation time |
| updated_at  | timestamp       | NULLABLE                    | Record update time   |

**Foreign Keys:** customer_id → customers(id) ON DELETE CASCADE

---

## Discounts & Promotions

### `discounts` table

Discount codes and promotional offers.

| Field      | Type            | Attributes                  | Description           |
| ---------- | --------------- | --------------------------- | --------------------- |
| id         | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| product_id | bigint unsigned | NULLABLE, FOREIGN KEY       | Specific product ID   |
| type       | enum            | NOT NULL                    | Discount type         |
| code       | varchar(100)    | NULLABLE                    | Discount code         |
| amount     | bigint unsigned | NOT NULL                    | Discount amount       |
| expire     | datetime        | NULLABLE                    | Expiration date       |
| deleted_at | timestamp       | NULLABLE                    | Soft delete timestamp |
| created_at | timestamp       | NULLABLE                    | Record creation time  |
| updated_at | timestamp       | NULLABLE                    | Record update time    |

**Foreign Keys:** product_id → products(id) ON DELETE CASCADE

**Enums:**

- type: 'price', 'percent'

---

## Shipping & Transport

### `transports` table

Available shipping methods and their configurations.

| Field       | Type         | Attributes                  | Description           |
| ----------- | ------------ | --------------------------- | --------------------- |
| id          | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| title       | varchar(250) | NOT NULL                    | Transport method name |
| description | text         | NULLABLE                    | Method description    |
| sort        | int unsigned | DEFAULT 0                   | Sort order            |
| is_default  | boolean      | DEFAULT 0                   | Is default method     |
| price       | int unsigned | DEFAULT 0                   | Shipping price        |
| deleted_at  | timestamp    | NULLABLE                    | Soft delete timestamp |
| created_at  | timestamp    | NULLABLE                    | Record creation time  |
| updated_at  | timestamp    | NULLABLE                    | Record update time    |

---

## Content Management

### `settings` table

System configuration and settings storage.

| Field      | Type         | Attributes                  | Description          |
| ---------- | ------------ | --------------------------- | -------------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| section    | varchar(255) | NOT NULL                    | Settings section     |
| type       | varchar(255) | NOT NULL                    | Setting type         |
| title      | varchar(255) | NOT NULL                    | Setting title        |
| active     | boolean      | DEFAULT true                | Is setting active    |
| key        | varchar(255) | UNIQUE, NOT NULL            | Setting key          |
| value      | text         | NULLABLE                    | Setting value        |
| created_at | timestamp    | NULLABLE                    | Record creation time |
| updated_at | timestamp    | NULLABLE                    | Record update time   |

### `contacts` table

Contact form submissions and inquiries.

| Field      | Type         | Attributes                  | Description          |
| ---------- | ------------ | --------------------------- | -------------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| full_name  | varchar(255) | NOT NULL                    | Contact full name    |
| email      | varchar(255) | NOT NULL                    | Contact email        |
| subject    | varchar(255) | NULLABLE                    | Message subject      |
| phone      | varchar(15)  | NOT NULL                    | Contact phone        |
| body       | text         | NOT NULL                    | Message body         |
| created_at | timestamp    | NULLABLE                    | Record creation time |
| updated_at | timestamp    | NULLABLE                    | Record update time   |

---

## Media Management

### `media` table

File uploads and media management using Spatie Media Library.

| Field                 | Type            | Attributes                  | Description              |
| --------------------- | --------------- | --------------------------- | ------------------------ |
| id                    | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier        |
| model_type            | varchar(255)    | NOT NULL                    | Associated model class   |
| model_id              | bigint unsigned | NOT NULL                    | Associated model ID      |
| uuid                  | uuid            | UNIQUE, NULLABLE            | Unique media identifier  |
| collection_name       | varchar(255)    | NOT NULL                    | Media collection name    |
| name                  | varchar(255)    | NOT NULL                    | Original file name       |
| file_name             | varchar(255)    | NOT NULL                    | Stored file name         |
| mime_type             | varchar(255)    | NULLABLE                    | File MIME type           |
| disk                  | varchar(255)    | NOT NULL                    | Storage disk             |
| conversions_disk      | varchar(255)    | NULLABLE                    | Conversions storage disk |
| size                  | bigint unsigned | NOT NULL                    | File size in bytes       |
| manipulations         | json            | NOT NULL                    | Image manipulations      |
| custom_properties     | json            | NOT NULL                    | Custom properties        |
| generated_conversions | json            | NOT NULL                    | Generated conversions    |
| responsive_images     | json            | NOT NULL                    | Responsive image data    |
| order_column          | int unsigned    | NULLABLE, INDEX             | Sort order               |
| created_at            | timestamp       | NULLABLE                    | Record creation time     |
| updated_at            | timestamp       | NULLABLE                    | Record update time       |

---

## Additional Features

### `questions` table

Product Q&A or FAQ system.

| Field       | Type            | Attributes                  | Description          |
| ----------- | --------------- | --------------------------- | -------------------- |
| id          | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| question    | text            | NOT NULL                    | Question text        |
| answer      | text            | NULLABLE                    | Answer text          |
| product_id  | bigint unsigned | NULLABLE, FOREIGN KEY       | Related product ID   |
| customer_id | bigint unsigned | NULLABLE, FOREIGN KEY       | Customer who asked   |
| created_at  | timestamp       | NULLABLE                    | Record creation time |
| updated_at  | timestamp       | NULLABLE                    | Record update time   |

**Foreign Keys:**

- product_id → products(id) ON DELETE CASCADE
- customer_id → customers(id) ON DELETE CASCADE

### `tickets` table

Customer support ticket system.

| Field       | Type            | Attributes                  | Description          |
| ----------- | --------------- | --------------------------- | -------------------- |
| id          | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| title       | varchar(255)    | NOT NULL                    | Ticket title         |
| body        | text            | NOT NULL                    | Ticket content       |
| status      | enum            | DEFAULT 'OPEN'              | Ticket status        |
| customer_id | bigint unsigned | FOREIGN KEY                 | Customer ID          |
| user_id     | bigint unsigned | NULLABLE, FOREIGN KEY       | Assigned staff ID    |
| created_at  | timestamp       | NULLABLE                    | Record creation time |
| updated_at  | timestamp       | NULLABLE                    | Record update time   |

**Foreign Keys:**

- customer_id → customers(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE SET NULL

### `attachments` table

File attachments for tickets and support.

| Field           | Type            | Attributes                  | Description          |
| --------------- | --------------- | --------------------------- | -------------------- |
| id              | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| file_path       | varchar(255)    | NOT NULL                    | File storage path    |
| attachable_type | varchar(255)    | NOT NULL                    | Parent model type    |
| attachable_id   | bigint unsigned | NOT NULL                    | Parent model ID      |
| created_at      | timestamp       | NULLABLE                    | Record creation time |
| updated_at      | timestamp       | NULLABLE                    | Record update time   |

### `sms` table

SMS notifications and logs.

| Field      | Type        | Attributes                  | Description          |
| ---------- | ----------- | --------------------------- | -------------------- |
| id         | bigint      | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| mobile     | varchar(15) | NOT NULL                    | Mobile phone number  |
| message    | text        | NOT NULL                    | SMS message content  |
| status     | enum        | DEFAULT 'PENDING'           | SMS status           |
| created_at | timestamp   | NULLABLE                    | Record creation time |
| updated_at | timestamp   | NULLABLE                    | Record update time   |

**Enums:**

- status: 'PENDING', 'SENT', 'FAILED'

### `accesses` table

User access logs and tracking.

| Field      | Type            | Attributes                  | Description          |
| ---------- | --------------- | --------------------------- | -------------------- |
| id         | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| user_type  | varchar(255)    | NOT NULL                    | User model type      |
| user_id    | bigint unsigned | NOT NULL                    | User ID              |
| ip         | varchar(45)     | NOT NULL                    | IP address           |
| user_agent | text            | NULLABLE                    | Browser user agent   |
| created_at | timestamp       | NULLABLE                    | Record creation time |
| updated_at | timestamp       | NULLABLE                    | Record update time   |

---

## System Tables

### `failed_jobs` table

Laravel failed queue jobs tracking.

| Field      | Type         | Attributes                  | Description       |
| ---------- | ------------ | --------------------------- | ----------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| uuid       | varchar(255) | UNIQUE, NOT NULL            | Job UUID          |
| connection | text         | NOT NULL                    | Queue connection  |
| queue      | text         | NOT NULL                    | Queue name        |
| payload    | longtext     | NOT NULL                    | Job payload       |
| exception  | longtext     | NOT NULL                    | Exception details |
| failed_at  | timestamp    | DEFAULT CURRENT_TIMESTAMP   | Failure timestamp |

### `password_resets` table

Password reset tokens for users.

| Field      | Type         | Attributes | Description         |
| ---------- | ------------ | ---------- | ------------------- |
| email      | varchar(255) | INDEX      | User email          |
| token      | varchar(255) | NOT NULL   | Reset token         |
| created_at | timestamp    | NULLABLE   | Token creation time |

### `personal_access_tokens` table

API tokens for authentication (Laravel Sanctum).

| Field          | Type            | Attributes                  | Description          |
| -------------- | --------------- | --------------------------- | -------------------- |
| id             | bigint          | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| tokenable_type | varchar(255)    | NOT NULL                    | Model type           |
| tokenable_id   | bigint unsigned | NOT NULL                    | Model ID             |
| name           | varchar(255)    | NOT NULL                    | Token name           |
| token          | varchar(64)     | UNIQUE, NOT NULL            | Token hash           |
| abilities      | text            | NULLABLE                    | Token abilities      |
| last_used_at   | timestamp       | NULLABLE                    | Last usage time      |
| created_at     | timestamp       | NULLABLE                    | Record creation time |
| updated_at     | timestamp       | NULLABLE                    | Record update time   |

### `xlangs` table

Multi-language support system.

| Field      | Type         | Attributes                  | Description          |
| ---------- | ------------ | --------------------------- | -------------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| name       | varchar(255) | NOT NULL                    | Language name        |
| tag        | varchar(10)  | UNIQUE, NOT NULL            | Language code        |
| rtl        | boolean      | DEFAULT false               | Is right-to-left     |
| default    | boolean      | DEFAULT false               | Is default language  |
| active     | boolean      | DEFAULT true                | Is active            |
| created_at | timestamp    | NULLABLE                    | Record creation time |
| updated_at | timestamp    | NULLABLE                    | Record update time   |

### `starter_kit` table

Initial setup and configuration data.

| Field      | Type         | Attributes                  | Description          |
| ---------- | ------------ | --------------------------- | -------------------- |
| id         | bigint       | PRIMARY KEY, AUTO_INCREMENT | Unique identifier    |
| name       | varchar(255) | NOT NULL                    | Kit component name   |
| data       | json         | NULLABLE                    | Configuration data   |
| installed  | boolean      | DEFAULT false               | Is installed         |
| created_at | timestamp    | NULLABLE                    | Record creation time |
| updated_at | timestamp    | NULLABLE                    | Record update time   |

---

## Database Relationships Summary

### Key Relationships

- **Users & Customers**: Separate authentication systems for admin/staff vs customers
- **Products & Categories**: Many-to-many relationship with hierarchical categories
- **Orders (Invoices)**: Complete order management with line items, payments, and shipping
- **Media**: Polymorphic relationship for file attachments to any model
- **Permissions**: Role-based access control using Spatie Permission package
- **Addresses**: Customer shipping/billing addresses with geolocation support
- **Discounts**: Flexible discount system supporting both percentage and fixed amounts
- **Support System**: Tickets with file attachments for customer support

### Features Supported

- ✅ Multi-role user management (admin/customer)
- ✅ Hierarchical product categories
- ✅ Product variants and inventory tracking
- ✅ Complete order processing workflow
- ✅ Multiple payment methods
- ✅ Shipping method management
- ✅ Discount and promotion system
- ✅ Customer favorites/wishlist
- ✅ Customer credit/wallet system
- ✅ Multi-language support
- ✅ Media file management
- ✅ Customer support ticketing
- ✅ SMS notifications
- ✅ Access logging and tracking
- ✅ Contact form management

### Database Statistics

- **Total Tables**: 29 tables
- **Core E-commerce Tables**: 15 tables
- **System/Framework Tables**: 8 tables
- **Support/Feature Tables**: 6 tables

This comprehensive database schema provides a solid foundation for a full-featured e-commerce platform with all the essential features needed for online retail operations.
