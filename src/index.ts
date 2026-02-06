import express, { Request, Response, NextFunction } from 'express';
import { ModuleRegistry } from './modules';
import { db } from './database';
import { v4 as uuidv4 } from 'uuid';
import { Tenant, User, UserRole, ModuleContext } from './types';

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Request logging middleware
app.use((req: Request, res: Response, next: NextFunction) => {
  console.log(`${new Date().toISOString()} - ${req.method} ${req.path}`);
  next();
});

// Initialize module registry
const moduleRegistry = ModuleRegistry.getInstance();

// Health check endpoint
app.get('/health', (req: Request, res: Response) => {
  res.json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    modules: moduleRegistry.listModuleNames().length
  });
});

// List all available modules
app.get('/api/modules', (req: Request, res: Response) => {
  res.json({
    success: true,
    data: {
      modules: moduleRegistry.listModuleNames(),
      total: moduleRegistry.listModuleNames().length
    }
  });
});

// Tenant management endpoints
app.post('/api/tenants', async (req: Request, res: Response) => {
  try {
    const tenant: Tenant = {
      id: uuidv4(),
      name: req.body.name,
      domain: req.body.domain,
      createdAt: new Date(),
      active: true,
      settings: req.body.settings || {}
    };
    
    const created = await db.createTenant(tenant);
    res.status(201).json({ success: true, data: created });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

app.get('/api/tenants/:id', async (req: Request, res: Response) => {
  try {
    const tenant = await db.getTenant(req.params.id);
    if (tenant) {
      res.json({ success: true, data: tenant });
    } else {
      res.status(404).json({ success: false, error: 'Tenant not found' });
    }
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

// User management endpoints
app.post('/api/users', async (req: Request, res: Response) => {
  try {
    const user: User = {
      id: uuidv4(),
      tenantId: req.body.tenantId,
      email: req.body.email,
      passwordHash: 'hashed_' + req.body.password, // In production, use bcrypt
      firstName: req.body.firstName,
      lastName: req.body.lastName,
      role: req.body.role || UserRole.USER,
      active: true,
      createdAt: new Date()
    };
    
    const created = await db.createUser(user);
    res.status(201).json({ success: true, data: created });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

app.get('/api/tenants/:tenantId/users', async (req: Request, res: Response) => {
  try {
    const users = await db.getUsersByTenant(req.params.tenantId);
    res.json({ success: true, data: users });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

// Generic module data endpoints
app.get('/api/:tenantId/:moduleName', async (req: Request, res: Response) => {
  try {
    const { tenantId, moduleName } = req.params;
    const module = moduleRegistry.getModule(moduleName) as any;
    
    if (!module) {
      return res.status(404).json({ success: false, error: 'Module not found' });
    }

    const context: ModuleContext = {
      tenantId,
      userId: req.headers['x-user-id'] as string || 'system',
      userRole: (req.headers['x-user-role'] as UserRole) || UserRole.USER
    };

    const page = parseInt(req.query.page as string) || 1;
    const pageSize = parseInt(req.query.pageSize as string) || 10;

    const result = await module.list(context, page, pageSize);
    res.json({ success: true, data: result });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

app.post('/api/:tenantId/:moduleName', async (req: Request, res: Response) => {
  try {
    const { tenantId, moduleName } = req.params;
    const module = moduleRegistry.getModule(moduleName) as any;
    
    if (!module) {
      return res.status(404).json({ success: false, error: 'Module not found' });
    }

    const context: ModuleContext = {
      tenantId,
      userId: req.headers['x-user-id'] as string || 'system',
      userRole: (req.headers['x-user-role'] as UserRole) || UserRole.USER
    };

    const result = await module.create(context, req.body);
    res.status(201).json({ success: true, data: result });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

app.get('/api/:tenantId/:moduleName/:id', async (req: Request, res: Response) => {
  try {
    const { tenantId, moduleName, id } = req.params;
    const module = moduleRegistry.getModule(moduleName) as any;
    
    if (!module) {
      return res.status(404).json({ success: false, error: 'Module not found' });
    }

    const context: ModuleContext = {
      tenantId,
      userId: req.headers['x-user-id'] as string || 'system',
      userRole: (req.headers['x-user-role'] as UserRole) || UserRole.USER
    };

    const result = await module.get(context, id);
    if (result) {
      res.json({ success: true, data: result });
    } else {
      res.status(404).json({ success: false, error: 'Item not found' });
    }
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

app.put('/api/:tenantId/:moduleName/:id', async (req: Request, res: Response) => {
  try {
    const { tenantId, moduleName, id } = req.params;
    const module = moduleRegistry.getModule(moduleName) as any;
    
    if (!module) {
      return res.status(404).json({ success: false, error: 'Module not found' });
    }

    const context: ModuleContext = {
      tenantId,
      userId: req.headers['x-user-id'] as string || 'system',
      userRole: (req.headers['x-user-role'] as UserRole) || UserRole.USER
    };

    const result = await module.update(context, id, req.body);
    if (result) {
      res.json({ success: true, data: result });
    } else {
      res.status(404).json({ success: false, error: 'Item not found' });
    }
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

app.delete('/api/:tenantId/:moduleName/:id', async (req: Request, res: Response) => {
  try {
    const { tenantId, moduleName, id } = req.params;
    const module = moduleRegistry.getModule(moduleName) as any;
    
    if (!module) {
      return res.status(404).json({ success: false, error: 'Module not found' });
    }

    const context: ModuleContext = {
      tenantId,
      userId: req.headers['x-user-id'] as string || 'system',
      userRole: (req.headers['x-user-role'] as UserRole) || UserRole.USER
    };

    const result = await module.delete(context, id);
    res.json({ success: true, data: { deleted: result } });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message });
  }
});

// Error handling middleware
app.use((err: Error, req: Request, res: Response, next: NextFunction) => {
  console.error('Error:', err);
  res.status(500).json({
    success: false,
    error: err.message || 'Internal server error'
  });
});

// 404 handler
app.use((req: Request, res: Response) => {
  res.status(404).json({
    success: false,
    error: 'Endpoint not found'
  });
});

// Start server
if (require.main === module) {
  app.listen(PORT, () => {
    console.log(`\n🚀 WebTenant Server Started`);
    console.log(`📡 Server running on port ${PORT}`);
    console.log(`📊 ${moduleRegistry.listModuleNames().length} business modules loaded`);
    console.log(`\nAvailable modules:`);
    moduleRegistry.listModuleNames().forEach((name, index) => {
      console.log(`  ${index + 1}. ${name}`);
    });
    console.log(`\n✅ System ready to accept requests\n`);
  });
}

export default app;
