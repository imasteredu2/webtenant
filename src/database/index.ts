import { Tenant, User } from '../types';

// In-memory database simulation for demonstration
// In production, this would be replaced with a real database
export class Database {
  private tenants: Map<string, Tenant> = new Map();
  private users: Map<string, User> = new Map();
  private moduleData: Map<string, Map<string, any>> = new Map();

  // Tenant operations
  async getTenant(tenantId: string): Promise<Tenant | undefined> {
    return this.tenants.get(tenantId);
  }

  async createTenant(tenant: Tenant): Promise<Tenant> {
    this.tenants.set(tenant.id, tenant);
    return tenant;
  }

  async updateTenant(tenantId: string, updates: Partial<Tenant>): Promise<Tenant | undefined> {
    const tenant = this.tenants.get(tenantId);
    if (tenant) {
      const updated = { ...tenant, ...updates };
      this.tenants.set(tenantId, updated);
      return updated;
    }
    return undefined;
  }

  // User operations
  async getUser(userId: string): Promise<User | undefined> {
    return this.users.get(userId);
  }

  async getUserByEmail(email: string, tenantId: string): Promise<User | undefined> {
    for (const user of this.users.values()) {
      if (user.email === email && user.tenantId === tenantId) {
        return user;
      }
    }
    return undefined;
  }

  async createUser(user: User): Promise<User> {
    this.users.set(user.id, user);
    return user;
  }

  async getUsersByTenant(tenantId: string): Promise<User[]> {
    const users: User[] = [];
    for (const user of this.users.values()) {
      if (user.tenantId === tenantId) {
        users.push(user);
      }
    }
    return users;
  }

  // Generic module data operations
  async getModuleData<T>(moduleName: string, tenantId: string, id: string): Promise<T | undefined> {
    const moduleKey = `${moduleName}:${tenantId}`;
    const moduleStore = this.moduleData.get(moduleKey);
    return moduleStore?.get(id) as T | undefined;
  }

  async createModuleData<T>(moduleName: string, tenantId: string, id: string, data: T): Promise<T> {
    const moduleKey = `${moduleName}:${tenantId}`;
    let moduleStore = this.moduleData.get(moduleKey);
    if (!moduleStore) {
      moduleStore = new Map();
      this.moduleData.set(moduleKey, moduleStore);
    }
    moduleStore.set(id, data);
    return data;
  }

  async updateModuleData<T>(moduleName: string, tenantId: string, id: string, updates: Partial<T>): Promise<T | undefined> {
    const moduleKey = `${moduleName}:${tenantId}`;
    const moduleStore = this.moduleData.get(moduleKey);
    if (moduleStore) {
      const existing = moduleStore.get(id);
      if (existing) {
        const updated = { ...existing, ...updates };
        moduleStore.set(id, updated);
        return updated as T;
      }
    }
    return undefined;
  }

  async deleteModuleData(moduleName: string, tenantId: string, id: string): Promise<boolean> {
    const moduleKey = `${moduleName}:${tenantId}`;
    const moduleStore = this.moduleData.get(moduleKey);
    if (moduleStore) {
      return moduleStore.delete(id);
    }
    return false;
  }

  async listModuleData<T>(moduleName: string, tenantId: string): Promise<T[]> {
    const moduleKey = `${moduleName}:${tenantId}`;
    const moduleStore = this.moduleData.get(moduleKey);
    if (moduleStore) {
      return Array.from(moduleStore.values()) as T[];
    }
    return [];
  }

  async searchModuleData<T>(
    moduleName: string,
    tenantId: string,
    predicate: (item: T) => boolean
  ): Promise<T[]> {
    const moduleKey = `${moduleName}:${tenantId}`;
    const moduleStore = this.moduleData.get(moduleKey);
    if (moduleStore) {
      const results: T[] = [];
      for (const item of moduleStore.values()) {
        if (predicate(item as T)) {
          results.push(item as T);
        }
      }
      return results;
    }
    return [];
  }
}

export const db = new Database();
