import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 10. Time Tracking Module
export interface TimeEntry extends BaseEntity {
  userId: string;
  projectId?: string;
  taskId?: string;
  startTime: Date;
  endTime?: Date;
  duration: number; // in minutes
  description: string;
  billable: boolean;
  billRate?: number;
  status: 'running' | 'stopped' | 'approved' | 'invoiced';
  category: string;
}

export class TimeTrackingModule extends BaseModule<TimeEntry> {
  constructor() {
    super('time-entries');
  }

  async startTimer(context: any, data: Omit<TimeEntry, keyof BaseEntity>): Promise<TimeEntry> {
    return this.create(context, { ...data, status: 'running', startTime: new Date() });
  }

  async stopTimer(context: any, id: string): Promise<TimeEntry | undefined> {
    const entry = await this.get(context, id);
    if (entry && entry.status === 'running') {
      const endTime = new Date();
      const duration = Math.floor((endTime.getTime() - entry.startTime.getTime()) / 60000);
      return this.update(context, id, { endTime, duration, status: 'stopped' });
    }
    return undefined;
  }

  async getTotalHours(context: any, userId: string, startDate: Date, endDate: Date): Promise<number> {
    const entries = await this.search(context, (entry) =>
      entry.userId === userId &&
      entry.startTime >= startDate &&
      entry.startTime <= endDate
    );
    return entries.reduce((total, entry) => total + entry.duration, 0) / 60;
  }
}
