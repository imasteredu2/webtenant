import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 9. Task Management Module
export interface Task extends BaseEntity {
  title: string;
  description: string;
  status: 'todo' | 'in-progress' | 'review' | 'done' | 'blocked';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  assignedTo: string;
  projectId?: string;
  dueDate: Date;
  estimatedHours: number;
  actualHours: number;
  tags: string[];
  dependencies: string[];
  attachments: string[];
  comments: TaskComment[];
}

export interface TaskComment {
  id: string;
  userId: string;
  comment: string;
  timestamp: Date;
}

export class TaskManagementModule extends BaseModule<Task> {
  constructor() {
    super('tasks');
  }

  async getTasksByAssignee(context: any, userId: string): Promise<Task[]> {
    return this.search(context, (task) => task.assignedTo === userId);
  }

  async getOverdueTasks(context: any): Promise<Task[]> {
    const now = new Date();
    return this.search(context, (task) => 
      task.dueDate < now && task.status !== 'done'
    );
  }

  async getTasksByProject(context: any, projectId: string): Promise<Task[]> {
    return this.search(context, (task) => task.projectId === projectId);
  }
}
