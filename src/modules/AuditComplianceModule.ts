import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 25. Audit & Compliance Module
export interface AuditLog extends BaseEntity {
  eventId: string;
  eventType: string;
  module: string;
  action: 'create' | 'read' | 'update' | 'delete' | 'login' | 'logout' | 'export' | 'import';
  entityType: string;
  entityId: string;
  oldValues?: Record<string, any>;
  newValues?: Record<string, any>;
  ipAddress: string;
  userAgent: string;
  result: 'success' | 'failure';
  errorMessage?: string;
  severity: 'info' | 'warning' | 'error' | 'critical';
  compliance_tags: string[];
  metadata: Record<string, any>;
}

export class AuditComplianceModule extends BaseModule<AuditLog> {
  constructor() {
    super('audit-logs');
  }

  async logEvent(context: any, event: Omit<AuditLog, keyof BaseEntity>): Promise<AuditLog> {
    return this.create(context, event);
  }

  async getEventsByUser(context: any, userId: string): Promise<AuditLog[]> {
    return this.search(context, (log) => log.createdBy === userId);
  }

  async getEventsByModule(context: any, module: string): Promise<AuditLog[]> {
    return this.search(context, (log) => log.module === module);
  }

  async getFailedEvents(context: any): Promise<AuditLog[]> {
    return this.search(context, (log) => log.result === 'failure');
  }

  async getCriticalEvents(context: any): Promise<AuditLog[]> {
    return this.search(context, (log) => log.severity === 'critical');
  }

  async getEventsByDateRange(context: any, startDate: Date, endDate: Date): Promise<AuditLog[]> {
    return this.search(context, (log) =>
      log.createdAt >= startDate && log.createdAt <= endDate
    );
  }

  async getComplianceReport(context: any, tags: string[]): Promise<AuditLog[]> {
    return this.search(context, (log) =>
      log.compliance_tags.some(tag => tags.includes(tag))
    );
  }

  async generateComplianceSummary(context: any, startDate: Date, endDate: Date): Promise<any> {
    const logs = await this.getEventsByDateRange(context, startDate, endDate);
    
    const summary = {
      totalEvents: logs.length,
      successfulEvents: logs.filter(l => l.result === 'success').length,
      failedEvents: logs.filter(l => l.result === 'failure').length,
      criticalEvents: logs.filter(l => l.severity === 'critical').length,
      eventsByModule: {} as Record<string, number>,
      eventsByAction: {} as Record<string, number>,
      eventsByUser: {} as Record<string, number>
    };

    logs.forEach(log => {
      summary.eventsByModule[log.module] = (summary.eventsByModule[log.module] || 0) + 1;
      summary.eventsByAction[log.action] = (summary.eventsByAction[log.action] || 0) + 1;
      summary.eventsByUser[log.createdBy] = (summary.eventsByUser[log.createdBy] || 0) + 1;
    });

    return summary;
  }
}
