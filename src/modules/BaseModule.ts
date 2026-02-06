import { v4 as uuidv4 } from 'uuid';
import { BaseEntity, ModuleContext, PaginatedResponse } from '../types';
import { db } from '../database';

// Base class for all business modules
export abstract class BaseModule<T extends BaseEntity> {
  protected moduleName: string;

  constructor(moduleName: string) {
    this.moduleName = moduleName;
  }

  async create(context: ModuleContext, data: Omit<T, keyof BaseEntity>): Promise<T> {
    const entity: T = {
      ...data,
      id: uuidv4(),
      tenantId: context.tenantId,
      createdAt: new Date(),
      updatedAt: new Date(),
      createdBy: context.userId,
      updatedBy: context.userId,
    } as T;

    return db.createModuleData<T>(this.moduleName, context.tenantId, entity.id, entity);
  }

  async get(context: ModuleContext, id: string): Promise<T | undefined> {
    const entity = await db.getModuleData<T>(this.moduleName, context.tenantId, id);
    return entity;
  }

  async update(context: ModuleContext, id: string, updates: Partial<T>): Promise<T | undefined> {
    const updateData = {
      ...updates,
      updatedAt: new Date(),
      updatedBy: context.userId,
    };
    return db.updateModuleData<T>(this.moduleName, context.tenantId, id, updateData);
  }

  async delete(context: ModuleContext, id: string): Promise<boolean> {
    return db.deleteModuleData(this.moduleName, context.tenantId, id);
  }

  async list(context: ModuleContext, page = 1, pageSize = 10): Promise<PaginatedResponse<T>> {
    const allData = await db.listModuleData<T>(this.moduleName, context.tenantId);
    const start = (page - 1) * pageSize;
    const end = start + pageSize;
    const data = allData.slice(start, end);
    
    return {
      data,
      total: allData.length,
      page,
      pageSize,
      totalPages: Math.ceil(allData.length / pageSize),
    };
  }

  async search(context: ModuleContext, predicate: (item: T) => boolean): Promise<T[]> {
    return db.searchModuleData<T>(this.moduleName, context.tenantId, predicate);
  }

  // Validation method to be overridden by subclasses
  protected validate(data: any): { valid: boolean; errors: string[] } {
    return { valid: true, errors: [] };
  }
}
