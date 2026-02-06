import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 6. Human Resources Management Module
export interface Employee extends BaseEntity {
  employeeId: string;
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  department: string;
  position: string;
  hireDate: Date;
  salary: number;
  status: 'active' | 'on-leave' | 'terminated';
  manager?: string;
  address: string;
  emergencyContact: {
    name: string;
    phone: string;
    relationship: string;
  };
}

export class HRModule extends BaseModule<Employee> {
  constructor() {
    super('hr-employees');
  }

  async getEmployeesByDepartment(context: any, department: string): Promise<Employee[]> {
    return this.search(context, (emp) => emp.department === department);
  }

  async getActiveEmployees(context: any): Promise<Employee[]> {
    return this.search(context, (emp) => emp.status === 'active');
  }

  async calculateTotalPayroll(context: any): Promise<number> {
    const employees = await this.getActiveEmployees(context);
    return employees.reduce((total, emp) => total + emp.salary, 0);
  }
}
