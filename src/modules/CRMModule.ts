import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 1. Customer Relationship Management (CRM) Module
export interface Customer extends BaseEntity {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  company: string;
  status: 'lead' | 'prospect' | 'customer' | 'inactive';
  source: string;
  lifetime_value: number;
  notes: string;
}

export class CRMModule extends BaseModule<Customer> {
  constructor() {
    super('crm');
  }

  async getCustomersByStatus(context: any, status: Customer['status']): Promise<Customer[]> {
    return this.search(context, (customer) => customer.status === status);
  }

  async getTopCustomers(context: any, limit: number): Promise<Customer[]> {
    const customers = await this.list(context, 1, 1000);
    return customers.data
      .sort((a, b) => b.lifetime_value - a.lifetime_value)
      .slice(0, limit);
  }
}
