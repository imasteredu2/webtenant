import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 20. Vendor Management Module
export interface Vendor extends BaseEntity {
  vendorCode: string;
  name: string;
  contactPerson: string;
  email: string;
  phone: string;
  address: string;
  website?: string;
  category: string;
  rating: number;
  status: 'active' | 'inactive' | 'suspended' | 'blacklisted';
  paymentTerms: string;
  taxId: string;
  bankDetails: {
    bankName: string;
    accountNumber: string;
    routingNumber: string;
  };
  certifications: string[];
  performanceMetrics: {
    onTimeDelivery: number;
    qualityScore: number;
    responseTime: number;
  };
  totalSpend: number;
}

export class VendorManagementModule extends BaseModule<Vendor> {
  constructor() {
    super('vendors');
  }

  async getActiveVendors(context: any): Promise<Vendor[]> {
    return this.search(context, (vendor) => vendor.status === 'active');
  }

  async getVendorsByCategory(context: any, category: string): Promise<Vendor[]> {
    return this.search(context, (vendor) => vendor.category === category);
  }

  async getTopVendors(context: any, limit: number): Promise<Vendor[]> {
    const vendors = await this.getActiveVendors(context);
    return vendors
      .sort((a, b) => b.totalSpend - a.totalSpend)
      .slice(0, limit);
  }

  async updatePerformance(context: any, id: string, metrics: Vendor['performanceMetrics']): Promise<Vendor | undefined> {
    const avgScore = (metrics.onTimeDelivery + metrics.qualityScore + (100 - metrics.responseTime)) / 3;
    const rating = Math.round(avgScore / 20); // Convert to 5-star rating
    
    return this.update(context, id, {
      performanceMetrics: metrics,
      rating
    });
  }
}
