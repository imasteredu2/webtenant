import request from 'supertest';
import app from '../index';
import { db } from '../database';
import { UserRole } from '../types';
import { v4 as uuidv4 } from 'uuid';

describe('Integration Tests - Full System', () => {
  let tenantId: string;
  let userId: string;

  beforeAll(async () => {
    // Create a test tenant
    tenantId = uuidv4();
    await db.createTenant({
      id: tenantId,
      name: 'Test Company',
      domain: 'test.example.com',
      createdAt: new Date(),
      active: true,
      settings: {}
    });

    // Create a test user
    userId = uuidv4();
    await db.createUser({
      id: userId,
      tenantId,
      email: 'test@example.com',
      passwordHash: 'hashed_password',
      firstName: 'Test',
      lastName: 'User',
      role: UserRole.ADMIN,
      active: true,
      createdAt: new Date()
    });
  });

  describe('System Health', () => {
    test('GET /health should return healthy status', async () => {
      const response = await request(app).get('/health');
      
      expect(response.status).toBe(200);
      expect(response.body.status).toBe('healthy');
      expect(response.body.modules).toBe(25);
    });
  });

  describe('Module Listing', () => {
    test('GET /api/modules should list all 25 modules', async () => {
      const response = await request(app).get('/api/modules');
      
      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.total).toBe(25);
      expect(response.body.data.modules).toContain('crm');
      expect(response.body.data.modules).toContain('inventory');
      expect(response.body.data.modules).toContain('audit');
    });
  });

  describe('Tenant Management', () => {
    test('POST /api/tenants should create a new tenant', async () => {
      const response = await request(app)
        .post('/api/tenants')
        .send({
          name: 'New Company',
          domain: 'new.example.com',
          settings: { theme: 'dark' }
        });

      expect(response.status).toBe(201);
      expect(response.body.success).toBe(true);
      expect(response.body.data.name).toBe('New Company');
      expect(response.body.data.id).toBeDefined();
    });

    test('GET /api/tenants/:id should retrieve a tenant', async () => {
      const response = await request(app).get(`/api/tenants/${tenantId}`);
      
      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.id).toBe(tenantId);
    });
  });

  describe('User Management', () => {
    test('POST /api/users should create a new user', async () => {
      const response = await request(app)
        .post('/api/users')
        .send({
          tenantId,
          email: 'newuser@example.com',
          password: 'password123',
          firstName: 'New',
          lastName: 'User',
          role: UserRole.USER
        });

      expect(response.status).toBe(201);
      expect(response.body.success).toBe(true);
      expect(response.body.data.email).toBe('newuser@example.com');
    });

    test('GET /api/tenants/:tenantId/users should list users', async () => {
      const response = await request(app).get(`/api/tenants/${tenantId}/users`);
      
      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.length).toBeGreaterThan(0);
    });
  });

  describe('Module CRUD Operations', () => {
    test('CRM Module - Full CRUD cycle', async () => {
      // Create
      const createResponse = await request(app)
        .post(`/api/${tenantId}/crm`)
        .set('x-user-id', userId)
        .set('x-user-role', UserRole.ADMIN)
        .send({
          firstName: 'Jane',
          lastName: 'Customer',
          email: 'jane@customer.com',
          phone: '555-1234',
          company: 'Customer Inc',
          status: 'lead',
          source: 'website',
          lifetime_value: 0,
          notes: 'Interested in our services'
        });

      expect(createResponse.status).toBe(201);
      expect(createResponse.body.success).toBe(true);
      const customerId = createResponse.body.data.id;

      // Read
      const readResponse = await request(app)
        .get(`/api/${tenantId}/crm/${customerId}`)
        .set('x-user-id', userId);

      expect(readResponse.status).toBe(200);
      expect(readResponse.body.data.firstName).toBe('Jane');

      // Update
      const updateResponse = await request(app)
        .put(`/api/${tenantId}/crm/${customerId}`)
        .set('x-user-id', userId)
        .send({
          status: 'customer',
          lifetime_value: 5000
        });

      expect(updateResponse.status).toBe(200);
      expect(updateResponse.body.data.status).toBe('customer');

      // List
      const listResponse = await request(app)
        .get(`/api/${tenantId}/crm`)
        .set('x-user-id', userId);

      expect(listResponse.status).toBe(200);
      expect(listResponse.body.data.data.length).toBeGreaterThan(0);

      // Delete
      const deleteResponse = await request(app)
        .delete(`/api/${tenantId}/crm/${customerId}`)
        .set('x-user-id', userId);

      expect(deleteResponse.status).toBe(200);
      expect(deleteResponse.body.data.deleted).toBe(true);
    });

    test('Inventory Module - Create and update', async () => {
      const createResponse = await request(app)
        .post(`/api/${tenantId}/inventory`)
        .set('x-user-id', userId)
        .send({
          sku: 'WIDGET-001',
          name: 'Super Widget',
          description: 'The best widget',
          category: 'Widgets',
          quantity: 100,
          reorderPoint: 20,
          unitPrice: 29.99,
          supplier: 'Widget Supplier',
          location: 'Warehouse A',
          status: 'in-stock'
        });

      expect(createResponse.status).toBe(201);
      expect(createResponse.body.data.sku).toBe('WIDGET-001');
    });

    test('Sales Orders - Create order', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/sales-orders`)
        .set('x-user-id', userId)
        .send({
          orderNumber: 'SO-12345',
          customerId: 'customer-123',
          orderDate: new Date().toISOString(),
          status: 'pending',
          items: [
            {
              productId: 'product-1',
              name: 'Widget',
              quantity: 5,
              unitPrice: 29.99,
              total: 149.95
            }
          ],
          subtotal: 149.95,
          tax: 15.00,
          shipping: 10.00,
          total: 174.95,
          shippingAddress: '123 Main St, City, ST 12345',
          paymentMethod: 'credit_card',
          paymentStatus: 'pending'
        });

      expect(response.status).toBe(201);
      expect(response.body.data.total).toBe(174.95);
    });

    test('Projects - Create project', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/projects`)
        .set('x-user-id', userId)
        .send({
          projectCode: 'PROJ-2024-001',
          name: 'Q1 Marketing Campaign',
          description: 'Launch new marketing campaign for Q1',
          status: 'planning',
          startDate: new Date().toISOString(),
          endDate: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toISOString(),
          budget: 50000,
          actualCost: 0,
          manager: userId,
          team: [userId],
          priority: 'high',
          progress: 0,
          milestones: []
        });

      expect(response.status).toBe(201);
      expect(response.body.data.name).toBe('Q1 Marketing Campaign');
    });

    test('Tasks - Create task', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/tasks`)
        .set('x-user-id', userId)
        .send({
          title: 'Design landing page',
          description: 'Create responsive landing page design',
          status: 'todo',
          priority: 'high',
          assignedTo: userId,
          dueDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
          estimatedHours: 8,
          actualHours: 0,
          tags: ['design', 'frontend'],
          dependencies: [],
          attachments: [],
          comments: []
        });

      expect(response.status).toBe(201);
      expect(response.body.data.title).toBe('Design landing page');
    });

    test('Invoices - Create invoice', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/invoices`)
        .set('x-user-id', userId)
        .send({
          invoiceNumber: 'INV-2024-001',
          customerId: 'customer-123',
          invoiceDate: new Date().toISOString(),
          dueDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
          status: 'draft',
          items: [
            {
              description: 'Consulting Services - January 2024',
              quantity: 40,
              unitPrice: 150,
              amount: 6000,
              taxable: true
            }
          ],
          subtotal: 6000,
          tax: 600,
          discount: 0,
          total: 6600,
          amountPaid: 0,
          balance: 6600,
          terms: 'Net 30',
          notes: 'Thank you for your business'
        });

      expect(response.status).toBe(201);
      expect(response.body.data.total).toBe(6600);
    });

    test('Support Tickets - Create ticket', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/support-tickets`)
        .set('x-user-id', userId)
        .send({
          ticketNumber: 'TKT-2024-001',
          customerId: 'customer-123',
          subject: 'Cannot access dashboard',
          description: 'Getting 404 error when trying to access the dashboard',
          status: 'open',
          priority: 'high',
          category: 'Technical Support',
          responses: [],
          attachments: []
        });

      expect(response.status).toBe(201);
      expect(response.body.data.subject).toBe('Cannot access dashboard');
    });

    test('Products - Create product', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/products`)
        .set('x-user-id', userId)
        .send({
          sku: 'PROD-WIDGET-001',
          name: 'Premium Widget',
          description: 'High-quality premium widget with advanced features',
          shortDescription: 'Premium widget',
          category: 'Widgets',
          subcategory: 'Premium',
          brand: 'WidgetCorp',
          tags: ['premium', 'widget', 'bestseller'],
          status: 'active',
          visibility: 'public',
          images: [],
          specifications: {
            material: 'Titanium',
            weight: '500g',
            warranty: '2 years'
          },
          variants: []
        });

      expect(response.status).toBe(201);
      expect(response.body.data.name).toBe('Premium Widget');
    });

    test('Audit Logs - Create audit log', async () => {
      const response = await request(app)
        .post(`/api/${tenantId}/audit`)
        .set('x-user-id', userId)
        .send({
          eventId: `evt-${Date.now()}`,
          eventType: 'USER_ACTION',
          module: 'crm',
          action: 'create',
          entityType: 'customer',
          entityId: 'customer-123',
          ipAddress: '192.168.1.100',
          userAgent: 'Mozilla/5.0',
          result: 'success',
          severity: 'info',
          compliance_tags: ['gdpr', 'audit'],
          metadata: {
            changes: ['created new customer record']
          }
        });

      expect(response.status).toBe(201);
      expect(response.body.data.module).toBe('crm');
    });
  });

  describe('Multi-Tenant Isolation', () => {
    test('should isolate data between tenants', async () => {
      // Create second tenant
      const tenant2Id = uuidv4();
      await db.createTenant({
        id: tenant2Id,
        name: 'Second Company',
        domain: 'second.example.com',
        createdAt: new Date(),
        active: true,
        settings: {}
      });

      // Create customer in first tenant
      const tenant1Customer = await request(app)
        .post(`/api/${tenantId}/crm`)
        .set('x-user-id', userId)
        .send({
          firstName: 'Tenant1',
          lastName: 'Customer',
          email: 'tenant1@example.com',
          phone: '555-0001',
          company: 'Company 1',
          status: 'lead',
          source: 'website',
          lifetime_value: 0,
          notes: ''
        });

      // Create customer in second tenant
      const tenant2Customer = await request(app)
        .post(`/api/${tenant2Id}/crm`)
        .set('x-user-id', userId)
        .send({
          firstName: 'Tenant2',
          lastName: 'Customer',
          email: 'tenant2@example.com',
          phone: '555-0002',
          company: 'Company 2',
          status: 'lead',
          source: 'website',
          lifetime_value: 0,
          notes: ''
        });

      // Verify tenant 1 only sees their customer
      const tenant1List = await request(app)
        .get(`/api/${tenantId}/crm`)
        .set('x-user-id', userId);

      const tenant1Emails = tenant1List.body.data.data.map((c: any) => c.email);
      expect(tenant1Emails).toContain('tenant1@example.com');
      expect(tenant1Emails).not.toContain('tenant2@example.com');

      // Verify tenant 2 only sees their customer
      const tenant2List = await request(app)
        .get(`/api/${tenant2Id}/crm`)
        .set('x-user-id', userId);

      const tenant2Emails = tenant2List.body.data.data.map((c: any) => c.email);
      expect(tenant2Emails).toContain('tenant2@example.com');
      expect(tenant2Emails).not.toContain('tenant1@example.com');
    });
  });

  describe('Error Handling', () => {
    test('should return 404 for non-existent module', async () => {
      const response = await request(app)
        .get(`/api/${tenantId}/non-existent-module`)
        .set('x-user-id', userId);

      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
    });

    test('should return 404 for non-existent item', async () => {
      const response = await request(app)
        .get(`/api/${tenantId}/crm/non-existent-id`)
        .set('x-user-id', userId);

      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
    });
  });
});
