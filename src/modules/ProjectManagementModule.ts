import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 8. Project Management Module
export interface Project extends BaseEntity {
  projectCode: string;
  name: string;
  description: string;
  status: 'planning' | 'active' | 'on-hold' | 'completed' | 'cancelled';
  startDate: Date;
  endDate: Date;
  budget: number;
  actualCost: number;
  manager: string;
  team: string[];
  priority: 'low' | 'medium' | 'high' | 'critical';
  progress: number;
  milestones: Milestone[];
  client?: string;
}

export interface Milestone {
  id: string;
  name: string;
  dueDate: Date;
  completed: boolean;
  completedDate?: Date;
}

export class ProjectManagementModule extends BaseModule<Project> {
  constructor() {
    super('projects');
  }

  async getActiveProjects(context: any): Promise<Project[]> {
    return this.search(context, (project) => project.status === 'active');
  }

  async getProjectsByManager(context: any, managerId: string): Promise<Project[]> {
    return this.search(context, (project) => project.manager === managerId);
  }

  async updateProgress(context: any, id: string, progress: number): Promise<Project | undefined> {
    if (progress >= 100) {
      return this.update(context, id, { progress, status: 'completed' });
    }
    return this.update(context, id, { progress });
  }
}
