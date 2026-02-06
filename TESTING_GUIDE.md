# Testing Guide - All 25 Modules

This guide provides curl commands to test each of the 25 business modules.

## Setup

First, create a tenant and user:

```bash
# Create Tenant
TENANT_RESPONSE=$(curl -s -X POST http://localhost:3000/api/tenants \
  -H "Content-Type: application/json" \
  -d '{"name": "Test Company", "domain": "test.example.com", "settings": {}}')
TENANT_ID=$(echo $TENANT_RESPONSE | jq -r '.data.id')

# Create User
USER_RESPONSE=$(curl -s -X POST http://localhost:3000/api/users \
  -H "Content-Type: application/json" \
  -d "{\"tenantId\": \"$TENANT_ID\", \"email\": \"admin@test.com\", \"password\": \"password123\", \"firstName\": \"Admin\", \"lastName\": \"User\", \"role\": \"admin\"}")
USER_ID=$(echo $USER_RESPONSE | jq -r '.data.id')

echo "Tenant ID: $TENANT_ID"
echo "User ID: $USER_ID"
```

## Module Tests

### 1. CRM Module
```bash
# Create customer
curl -X POST http://localhost:3000/api/$TENANT_ID/crm \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "phone": "555-1234",
    "company": "Acme Corp",
    "status": "lead",
    "source": "website",
    "lifetime_value": 0,
    "notes": "Potential enterprise customer"
  }' | jq
```

### 2. Inventory Module
```bash
# Create inventory item
curl -X POST http://localhost:3000/api/$TENANT_ID/inventory \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "sku": "WIDGET-001",
    "name": "Premium Widget",
    "description": "High-quality widget",
    "category": "Electronics",
    "quantity": 100,
    "reorderPoint": 20,
    "unitPrice": 99.99,
    "supplier": "Widget Supplier Inc",
    "location": "Warehouse A",
    "status": "in-stock"
  }' | jq
```

### 3. Sales Orders Module
```bash
# Create sales order
curl -X POST http://localhost:3000/api/$TENANT_ID/sales-orders \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "orderNumber": "SO-2024-001",
    "customerId": "customer-123",
    "orderDate": "2024-01-15",
    "status": "pending",
    "items": [
      {
        "productId": "prod-001",
        "name": "Widget",
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
  }' | jq
```

### 4. Purchase Orders Module
```bash
# Create purchase order
curl -X POST http://localhost:3000/api/$TENANT_ID/purchase-orders \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "poNumber": "PO-2024-001",
    "vendorId": "vendor-456",
    "orderDate": "2024-01-15",
    "expectedDeliveryDate": "2024-02-15",
    "status": "draft",
    "items": [
      {
        "itemId": "item-001",
        "description": "Raw materials",
        "quantity": 100,
        "unitPrice": 50,
        "total": 5000
      }
    ],
    "subtotal": 5000,
    "tax": 500,
    "shipping": 100,
    "total": 5600,
    "terms": "Net 30"
  }' | jq
```

### 5. Accounting Module
```bash
# Create accounting entry
curl -X POST http://localhost:3000/api/$TENANT_ID/accounting \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "entryNumber": "JE-2024-001",
    "entryDate": "2024-01-15",
    "accountCode": "1000",
    "accountName": "Cash",
    "type": "debit",
    "amount": 10000,
    "description": "Initial capital investment",
    "category": "Asset",
    "reconciled": false,
    "fiscalYear": 2024,
    "fiscalPeriod": 1
  }' | jq
```

### 6. HR Module
```bash
# Create employee
curl -X POST http://localhost:3000/api/$TENANT_ID/hr \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "employeeId": "EMP-001",
    "firstName": "Jane",
    "lastName": "Smith",
    "email": "jane.smith@company.com",
    "phone": "555-5678",
    "department": "Engineering",
    "position": "Senior Developer",
    "hireDate": "2024-01-15",
    "salary": 120000,
    "status": "active",
    "address": "456 Oak Ave, City, ST 67890",
    "emergencyContact": {
      "name": "John Smith",
      "phone": "555-9999",
      "relationship": "Spouse"
    }
  }' | jq
```

### 7. Payroll Module
```bash
# Create payroll record
curl -X POST http://localhost:3000/api/$TENANT_ID/payroll \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "employeeId": "EMP-001",
    "payPeriodStart": "2024-01-01",
    "payPeriodEnd": "2024-01-15",
    "grossPay": 5000,
    "deductions": {
      "tax": 1000,
      "socialSecurity": 310,
      "insurance": 250,
      "retirement": 200,
      "other": 0
    },
    "netPay": 3240,
    "hoursWorked": 80,
    "overtimeHours": 0,
    "bonuses": 0,
    "status": "draft",
    "paymentMethod": "direct-deposit"
  }' | jq
```

### 8. Projects Module
```bash
# Create project
curl -X POST http://localhost:3000/api/$TENANT_ID/projects \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d "{
    \"projectCode\": \"PROJ-2024-001\",
    \"name\": \"Website Redesign\",
    \"description\": \"Redesign company website with modern UI\",
    \"status\": \"active\",
    \"startDate\": \"2024-01-15\",
    \"endDate\": \"2024-06-30\",
    \"budget\": 75000,
    \"actualCost\": 0,
    \"manager\": \"$USER_ID\",
    \"team\": [\"$USER_ID\"],
    \"priority\": \"high\",
    \"progress\": 0,
    \"milestones\": []
  }" | jq
```

### 9. Tasks Module
```bash
# Create task
curl -X POST http://localhost:3000/api/$TENANT_ID/tasks \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d "{
    \"title\": \"Design homepage mockup\",
    \"description\": \"Create initial mockup for new homepage design\",
    \"status\": \"todo\",
    \"priority\": \"high\",
    \"assignedTo\": \"$USER_ID\",
    \"dueDate\": \"2024-02-01\",
    \"estimatedHours\": 16,
    \"actualHours\": 0,
    \"tags\": [\"design\", \"ui\", \"homepage\"],
    \"dependencies\": [],
    \"attachments\": [],
    \"comments\": []
  }" | jq
```

### 10. Time Tracking Module
```bash
# Create time entry
curl -X POST http://localhost:3000/api/$TENANT_ID/time-tracking \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d "{
    \"userId\": \"$USER_ID\",
    \"projectId\": \"project-123\",
    \"taskId\": \"task-456\",
    \"startTime\": \"2024-01-15T09:00:00Z\",
    \"duration\": 480,
    \"description\": \"Working on homepage design\",
    \"billable\": true,
    \"billRate\": 150,
    \"status\": \"stopped\",
    \"category\": \"Development\"
  }" | jq
```

### 11. Invoices Module
```bash
# Create invoice
curl -X POST http://localhost:3000/api/$TENANT_ID/invoices \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "invoiceNumber": "INV-2024-001",
    "customerId": "customer-123",
    "invoiceDate": "2024-01-15",
    "dueDate": "2024-02-15",
    "status": "sent",
    "items": [
      {
        "description": "Consulting Services - January 2024",
        "quantity": 40,
        "unitPrice": 150,
        "amount": 6000,
        "taxable": true
      }
    ],
    "subtotal": 6000,
    "tax": 600,
    "discount": 0,
    "total": 6600,
    "amountPaid": 0,
    "balance": 6600,
    "terms": "Net 30",
    "notes": "Thank you for your business"
  }' | jq
```

### 12. Expenses Module
```bash
# Create expense
curl -X POST http://localhost:3000/api/$TENANT_ID/expenses \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d "{
    \"expenseNumber\": \"EXP-2024-001\",
    \"employeeId\": \"$USER_ID\",
    \"category\": \"Travel\",
    \"amount\": 450,
    \"currency\": \"USD\",
    \"expenseDate\": \"2024-01-15\",
    \"description\": \"Client meeting in New York\",
    \"merchant\": \"United Airlines\",
    \"paymentMethod\": \"corporate_card\",
    \"status\": \"submitted\",
    \"billable\": true,
    \"reimbursable\": false
  }" | jq
```

### 13. Assets Module
```bash
# Create asset
curl -X POST http://localhost:3000/api/$TENANT_ID/assets \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "assetTag": "LAPTOP-001",
    "name": "MacBook Pro 16-inch",
    "description": "M2 Max MacBook Pro for development",
    "category": "Computer Equipment",
    "serialNumber": "C02ABC123456",
    "purchaseDate": "2024-01-15",
    "purchasePrice": 3500,
    "currentValue": 3500,
    "depreciationRate": 0.25,
    "location": "Office - Floor 3",
    "status": "available"
  }' | jq
```

### 14. Documents Module
```bash
# Create document
curl -X POST http://localhost:3000/api/$TENANT_ID/documents \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "documentNumber": "DOC-2024-001",
    "title": "Project Requirements Document",
    "description": "Detailed requirements for website redesign project",
    "category": "Project Documentation",
    "fileType": "pdf",
    "fileSize": 2048000,
    "filePath": "/documents/projects/requirements.pdf",
    "version": 1,
    "status": "approved",
    "tags": ["requirements", "project", "website"],
    "accessLevel": "internal",
    "relatedDocuments": []
  }' | jq
```

### 15. Email Campaigns Module
```bash
# Create email campaign
curl -X POST http://localhost:3000/api/$TENANT_ID/email-campaigns \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "campaignName": "Spring Sale 2024",
    "subject": "Get 30% off this Spring!",
    "content": "<html><body><h1>Spring Sale</h1><p>Special offer just for you!</p></body></html>",
    "status": "draft",
    "totalRecipients": 5000,
    "sent": 0,
    "delivered": 0,
    "opened": 0,
    "clicked": 0,
    "bounced": 0,
    "unsubscribed": 0,
    "tags": ["sale", "spring", "promotion"],
    "fromEmail": "marketing@company.com",
    "fromName": "Company Marketing",
    "recipientList": "all-active-customers"
  }' | jq
```

### 16. Analytics Module
```bash
# Create analytics report
curl -X POST http://localhost:3000/api/$TENANT_ID/analytics \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "reportName": "Q1 Sales Report",
    "reportType": "sales",
    "dateRange": {
      "start": "2024-01-01",
      "end": "2024-03-31"
    },
    "metrics": [],
    "filters": {},
    "generatedBy": "admin",
    "format": "pdf",
    "status": "generating",
    "scheduledRun": false
  }' | jq
```

### 17. Support Tickets Module
```bash
# Create support ticket
curl -X POST http://localhost:3000/api/$TENANT_ID/support-tickets \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "ticketNumber": "TKT-2024-001",
    "customerId": "customer-123",
    "subject": "Unable to login to dashboard",
    "description": "Getting 401 error when trying to access the dashboard",
    "status": "open",
    "priority": "high",
    "category": "Technical Support",
    "responses": [],
    "attachments": []
  }' | jq
```

### 18. Knowledge Base Module
```bash
# Create knowledge article
curl -X POST http://localhost:3000/api/$TENANT_ID/knowledge-base \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "articleNumber": "KB-2024-001",
    "title": "How to Reset Your Password",
    "content": "Follow these steps to reset your password: 1. Click Forgot Password...",
    "summary": "Step-by-step guide for password reset",
    "category": "Account Management",
    "tags": ["password", "reset", "account", "security"],
    "status": "published",
    "author": "admin",
    "views": 0,
    "helpful": 0,
    "notHelpful": 0,
    "relatedArticles": [],
    "attachments": []
  }' | jq
```

### 19. Contracts Module
```bash
# Create contract
curl -X POST http://localhost:3000/api/$TENANT_ID/contracts \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "contractNumber": "CNT-2024-001",
    "title": "Annual Service Agreement",
    "type": "customer",
    "partyA": "Our Company Inc",
    "partyB": "Customer Corp",
    "startDate": "2024-01-01",
    "endDate": "2024-12-31",
    "value": 120000,
    "currency": "USD",
    "status": "active",
    "terms": "Annual service agreement with monthly billing",
    "paymentTerms": "Monthly - Net 30",
    "autoRenew": true,
    "renewalNotice": 60,
    "documents": [],
    "milestones": [],
    "notes": ""
  }' | jq
```

### 20. Vendors Module
```bash
# Create vendor
curl -X POST http://localhost:3000/api/$TENANT_ID/vendors \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "vendorCode": "VEN-2024-001",
    "name": "Tech Supplies Inc",
    "contactPerson": "Mike Johnson",
    "email": "mike@techsupplies.com",
    "phone": "555-7777",
    "address": "789 Industrial Blvd, Tech City, TC 12345",
    "category": "Technology",
    "rating": 0,
    "status": "active",
    "paymentTerms": "Net 30",
    "taxId": "12-3456789",
    "bankDetails": {
      "bankName": "Tech Bank",
      "accountNumber": "1234567890",
      "routingNumber": "987654321"
    },
    "certifications": ["ISO9001", "ISO27001"],
    "performanceMetrics": {
      "onTimeDelivery": 0,
      "qualityScore": 0,
      "responseTime": 0
    },
    "totalSpend": 0
  }' | jq
```

### 21. Warehouses Module
```bash
# Create warehouse
curl -X POST http://localhost:3000/api/$TENANT_ID/warehouses \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d "{
    \"warehouseCode\": \"WH-001\",
    \"name\": \"Main Distribution Center\",
    \"location\": \"123 Warehouse Rd, City, ST 12345\",
    \"type\": \"distribution\",
    \"capacity\": 50000,
    \"currentOccupancy\": 0,
    \"zones\": [],
    \"manager\": \"$USER_ID\",
    \"status\": \"active\",
    \"operatingHours\": \"Mon-Fri 6AM-10PM\",
    \"contact\": {
      \"phone\": \"555-3333\",
      \"email\": \"warehouse@company.com\"
    }
  }" | jq
```

### 22. Shipments Module
```bash
# Create shipment
curl -X POST http://localhost:3000/api/$TENANT_ID/shipments \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "shipmentNumber": "SHP-2024-001",
    "orderId": "order-123",
    "carrier": "FedEx",
    "trackingNumber": "1234567890",
    "method": "ground",
    "status": "pending",
    "origin": {
      "street": "123 Warehouse Rd",
      "city": "City",
      "state": "ST",
      "zipCode": "12345",
      "country": "USA"
    },
    "destination": {
      "street": "456 Customer Ave",
      "city": "Town",
      "state": "ST",
      "zipCode": "67890",
      "country": "USA"
    },
    "weight": 10,
    "dimensions": {
      "length": 24,
      "width": 18,
      "height": 12,
      "unit": "inches"
    },
    "estimatedDelivery": "2024-01-20",
    "cost": 45.99,
    "items": [],
    "signature_required": true,
    "trackingEvents": []
  }' | jq
```

### 23. Products Module
```bash
# Create product
curl -X POST http://localhost:3000/api/$TENANT_ID/products \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "sku": "PROD-WIDGET-001",
    "name": "Professional Widget Pro",
    "description": "Enterprise-grade widget with advanced features and lifetime warranty",
    "shortDescription": "Professional widget",
    "category": "Widgets",
    "subcategory": "Professional",
    "brand": "WidgetPro",
    "tags": ["professional", "enterprise", "widget", "featured"],
    "status": "active",
    "visibility": "public",
    "images": [],
    "specifications": {
      "material": "Aircraft-grade aluminum",
      "weight": "750g",
      "warranty": "Lifetime",
      "color_options": "Silver, Space Gray, Gold"
    },
    "variants": []
  }' | jq
```

### 24. Pricing Module
```bash
# Create pricing rule
curl -X POST http://localhost:3000/api/$TENANT_ID/pricing \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d '{
    "ruleName": "Volume Discount - 10+ Units",
    "description": "15% discount for orders of 10 or more units",
    "ruleType": "volume",
    "productIds": ["prod-001", "prod-002"],
    "customerSegments": [],
    "priority": 1,
    "active": true,
    "startDate": "2024-01-01",
    "conditions": [],
    "adjustments": [
      {
        "type": "percentage",
        "value": 15,
        "applyTo": "base-price"
      }
    ],
    "stackable": false
  }' | jq
```

### 25. Audit Module
```bash
# Create audit log
curl -X POST http://localhost:3000/api/$TENANT_ID/audit \
  -H "Content-Type: application/json" \
  -H "x-user-id: $USER_ID" \
  -d "{
    \"eventId\": \"evt-$(date +%s)\",
    \"eventType\": \"USER_ACTION\",
    \"module\": \"crm\",
    \"action\": \"create\",
    \"entityType\": \"customer\",
    \"entityId\": \"customer-123\",
    \"ipAddress\": \"192.168.1.100\",
    \"userAgent\": \"Mozilla/5.0\",
    \"result\": \"success\",
    \"severity\": \"info\",
    \"compliance_tags\": [\"gdpr\", \"audit_trail\"],
    \"metadata\": {
      \"action_description\": \"Created new customer record\"
    }
  }" | jq
```

## Listing Data

To list data from any module:

```bash
# List items (with pagination)
curl -X GET "http://localhost:3000/api/$TENANT_ID/[MODULE_NAME]?page=1&pageSize=10" \
  -H "x-user-id: $USER_ID" | jq
```

Replace `[MODULE_NAME]` with any of:
- crm
- inventory
- sales-orders
- purchase-orders
- accounting
- hr
- payroll
- projects
- tasks
- time-tracking
- invoices
- expenses
- assets
- documents
- email-campaigns
- analytics
- support-tickets
- knowledge-base
- contracts
- vendors
- warehouses
- shipments
- products
- pricing
- audit

## Complete Test Script

Save all commands above to a file named `test-all-modules.sh` and run:

```bash
chmod +x test-all-modules.sh
./test-all-modules.sh
```

This will test all 25 modules in sequence and output the results.
