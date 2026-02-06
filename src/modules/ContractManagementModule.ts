import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 19. Contract Management Module
export interface Contract extends BaseEntity {
  contractNumber: string;
  title: string;
  type: 'vendor' | 'customer' | 'partnership' | 'employment' | 'service';
  partyA: string;
  partyB: string;
  startDate: Date;
  endDate: Date;
  value: number;
  currency: string;
  status: 'draft' | 'pending-approval' | 'active' | 'expired' | 'terminated' | 'renewed';
  terms: string;
  paymentTerms: string;
  autoRenew: boolean;
  renewalNotice: number; // days before expiration
  documents: string[];
  milestones: ContractMilestone[];
  notes: string;
}

export interface ContractMilestone {
  id: string;
  description: string;
  dueDate: Date;
  value: number;
  completed: boolean;
}

export class ContractManagementModule extends BaseModule<Contract> {
  constructor() {
    super('contracts');
  }

  async getActiveContracts(context: any): Promise<Contract[]> {
    return this.search(context, (contract) => contract.status === 'active');
  }

  async getExpiringContracts(context: any, days: number): Promise<Contract[]> {
    const futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + days);
    
    return this.search(context, (contract) =>
      contract.status === 'active' &&
      contract.endDate <= futureDate
    );
  }

  async renewContract(context: any, id: string, newEndDate: Date): Promise<Contract | undefined> {
    return this.update(context, id, {
      status: 'active',
      startDate: new Date(),
      endDate: newEndDate
    });
  }

  async terminateContract(context: any, id: string, reason: string): Promise<Contract | undefined> {
    return this.update(context, id, {
      status: 'terminated',
      notes: reason
    });
  }
}
