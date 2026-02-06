import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 7. Payroll Processing Module
export interface PayrollRecord extends BaseEntity {
  employeeId: string;
  payPeriodStart: Date;
  payPeriodEnd: Date;
  grossPay: number;
  deductions: {
    tax: number;
    socialSecurity: number;
    insurance: number;
    retirement: number;
    other: number;
  };
  netPay: number;
  hoursWorked: number;
  overtimeHours: number;
  bonuses: number;
  status: 'draft' | 'approved' | 'paid';
  paymentDate?: Date;
  paymentMethod: 'direct-deposit' | 'check';
}

export class PayrollModule extends BaseModule<PayrollRecord> {
  constructor() {
    super('payroll');
  }

  async calculateNetPay(grossPay: number, deductions: PayrollRecord['deductions']): Promise<number> {
    const totalDeductions = Object.values(deductions).reduce((sum, val) => sum + val, 0);
    return grossPay - totalDeductions;
  }

  async processPayroll(context: any, payPeriodEnd: Date): Promise<PayrollRecord[]> {
    const records = await this.search(context, 
      (record) => record.status === 'approved' && record.payPeriodEnd <= payPeriodEnd
    );
    
    for (const record of records) {
      await this.update(context, record.id, { 
        status: 'paid', 
        paymentDate: new Date() 
      });
    }
    
    return records;
  }
}
