# WebTenant - Multi-Tenant Business Management System

A comprehensive multi-tenant web application with 25 advanced business modules for complete enterprise resource planning (ERP).

## 🚀 Features

### Core System
- **Multi-tenant architecture** with complete data isolation
- **RESTful API** for all operations
- **TypeScript** for type safety and better developer experience
- **In-memory database** (easily replaceable with PostgreSQL, MySQL, MongoDB, etc.)
- **Comprehensive testing** with Jest

### 25 Business Modules

1. **Customer Relationship Management (CRM)** - Manage customers, leads, and sales pipeline
2. **Inventory Management** - Track stock levels, reorder points, and warehouse locations
3. **Sales Order Processing** - Handle customer orders from quote to delivery
4. **Purchase Order Management** - Manage supplier orders and procurement
5. **Financial Accounting** - Double-entry bookkeeping, trial balance, and financial reports
6. **Human Resources Management** - Employee records, departments, and organizational structure
7. **Payroll Processing** - Calculate wages, deductions, and process payments
8. **Project Management** - Track projects, milestones, and budgets
9. **Task Management** - Assign and track tasks with dependencies
10. **Time Tracking** - Track billable and non-billable hours
11. **Invoice Generation** - Create and send invoices, track payments
12. **Expense Tracking** - Record and approve employee expenses
13. **Asset Management** - Track company assets, depreciation, and maintenance
14. **Document Management** - Store and version control documents
15. **Email Campaign Management** - Create and track marketing campaigns
16. **Analytics & Reporting** - Generate business intelligence reports
17. **Customer Support Ticketing** - Manage customer support requests
18. **Knowledge Base** - Maintain self-service help articles
19. **Contract Management** - Track contracts, renewals, and obligations
20. **Vendor Management** - Manage supplier relationships and performance
21. **Warehouse Management** - Organize warehouse zones and capacity
22. **Shipping & Logistics** - Track shipments and deliveries
23. **Product Catalog** - Manage product listings with variants
24. **Pricing Management** - Dynamic pricing rules and promotions
25. **Audit & Compliance** - Complete audit trail for all operations

## 📦 Installation

```bash
# Install dependencies
npm install

# Build the project
npm run build

# Run tests
npm test

# Run with coverage
npm run test:coverage
```

## 🏃‍♂️ Running the Application

```bash
# Development mode with auto-reload
npm run dev

# Production mode
npm start
```

The server will start on port 3000 (configurable via PORT environment variable).

## 🔧 API Documentation

### System Endpoints

- `GET /health` - Health check
- `GET /api/modules` - List all available modules

### Tenant Management

- `POST /api/tenants` - Create a new tenant
- `GET /api/tenants/:id` - Get tenant details

### User Management

- `POST /api/users` - Create a new user
- `GET /api/tenants/:tenantId/users` - List users for a tenant

### Module Operations

All modules follow the same RESTful pattern:

- `GET /api/:tenantId/:moduleName` - List items (with pagination)
- `POST /api/:tenantId/:moduleName` - Create new item
- `GET /api/:tenantId/:moduleName/:id` - Get specific item
- `PUT /api/:tenantId/:moduleName/:id` - Update item
- `DELETE /api/:tenantId/:moduleName/:id` - Delete item

#### Available Module Names

- `crm` - Customer Relationship Management
- `inventory` - Inventory Management
- `sales-orders` - Sales Order Processing
- `purchase-orders` - Purchase Order Management
- `accounting` - Financial Accounting
- `hr` - Human Resources
- `payroll` - Payroll Processing
- `projects` - Project Management
- `tasks` - Task Management
- `time-tracking` - Time Tracking
- `invoices` - Invoice Generation
- `expenses` - Expense Tracking
- `assets` - Asset Management
- `documents` - Document Management
- `email-campaigns` - Email Campaign Management
- `analytics` - Analytics & Reporting
- `support-tickets` - Customer Support Ticketing
- `knowledge-base` - Knowledge Base
- `contracts` - Contract Management
- `vendors` - Vendor Management
- `warehouses` - Warehouse Management
- `shipments` - Shipping & Logistics
- `products` - Product Catalog
- `pricing` - Pricing Management
- `audit` - Audit & Compliance

### Headers

Include these headers in your requests:

- `x-user-id` - User identifier
- `x-user-role` - User role (admin, manager, user, viewer)

## 📝 Example Usage

### Create a Tenant

```bash
curl -X POST http://localhost:3000/api/tenants \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Corporation",
    "domain": "acme.example.com",
    "settings": {}
  }'
```

### Create a Customer

```bash
curl -X POST http://localhost:3000/api/tenant-123/crm \
  -H "Content-Type: application/json" \
  -H "x-user-id: user-456" \
  -d '{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "phone": "555-1234",
    "company": "Doe Industries",
    "status": "lead",
    "source": "website",
    "lifetime_value": 0,
    "notes": "Interested in enterprise plan"
  }'
```

### Create a Sales Order

```bash
curl -X POST http://localhost:3000/api/tenant-123/sales-orders \
  -H "Content-Type: application/json" \
  -H "x-user-id: user-456" \
  -d '{
    "orderNumber": "SO-2024-001",
    "customerId": "customer-789",
    "orderDate": "2024-01-15",
    "status": "pending",
    "items": [
      {
        "productId": "prod-001",
        "name": "Widget Pro",
        "quantity": 10,
        "unitPrice": 99.99,
        "total": 999.90
      }
    ],
    "subtotal": 999.90,
    "tax": 99.99,
    "shipping": 25.00,
    "total": 1124.89,
    "shippingAddress": "123 Main St, City, ST 12345",
    "paymentMethod": "credit_card",
    "paymentStatus": "pending"
  }'
```

## 🧪 Testing

The system includes comprehensive tests:

```bash
# Run all tests
npm test

# Run with coverage report
npm run test:coverage

# Run specific test file
npm test -- modules.test.ts
```

### Test Coverage

- Unit tests for all 25 modules
- Integration tests for API endpoints
- Multi-tenant isolation tests
- CRUD operation tests for each module

## 🏗️ Architecture

### Multi-Tenant Design

The system uses a shared database with tenant isolation at the application layer. Each request must include a `tenantId`, and all data operations are scoped to that tenant.

### Module System

All business modules extend a `BaseModule` class that provides:
- Standard CRUD operations
- Automatic tenant scoping
- User tracking (created by, updated by)
- Pagination support
- Search capabilities

### Database Layer

The current implementation uses an in-memory database for demonstration. In production, replace with:
- PostgreSQL for relational data
- MongoDB for document storage
- Redis for caching
- Elasticsearch for search

## 🔐 Security Considerations

For production deployment, implement:
- JWT authentication
- Password hashing with bcrypt
- Rate limiting
- Input validation and sanitization
- HTTPS/TLS encryption
- Database encryption at rest
- Role-based access control (RBAC)
- API key management

## 📊 Monitoring & Observability

Consider adding:
- Application Performance Monitoring (APM)
- Structured logging
- Error tracking (e.g., Sentry)
- Metrics collection (e.g., Prometheus)
- Distributed tracing

## 🚀 Deployment

The application can be deployed to:
- Docker containers
- Kubernetes clusters
- AWS (EC2, ECS, Lambda)
- Google Cloud Platform
- Azure
- Heroku
- Vercel/Netlify (with serverless functions)

## 📄 License

Unlicense - This is free and unencumbered software released into the public domain.

## 🤝 Contributing

This is a demonstration project. Feel free to fork and modify for your needs.

## 📞 Support

For questions or issues, please open an issue on GitHub.