import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 5. Financial Accounting Module
export interface AccountingEntry extends BaseEntity {
  entryNumber: string;
  entryDate: Date;
  accountCode: string;
  accountName: string;
  type: 'debit' | 'credit';
  amount: number;
  description: string;
  referenceNumber?: string;
  category: string;
  reconciled: boolean;
  fiscalYear: number;
  fiscalPeriod: number;
}

export class FinancialAccountingModule extends BaseModule<AccountingEntry> {
  constructor() {
    super('accounting');
  }

  async getBalance(context: any, accountCode: string): Promise<number> {
    const entries = await this.search(context, (entry) => entry.accountCode === accountCode);
    return entries.reduce((balance, entry) => {
      return entry.type === 'debit' ? balance + entry.amount : balance - entry.amount;
    }, 0);
  }

  async getTrialBalance(context: any, fiscalYear: number, fiscalPeriod: number): Promise<any> {
    const entries = await this.search(context, 
      (entry) => entry.fiscalYear === fiscalYear && entry.fiscalPeriod === fiscalPeriod
    );
    const accounts = new Map<string, { debit: number; credit: number }>();
    
    entries.forEach(entry => {
      if (!accounts.has(entry.accountCode)) {
        accounts.set(entry.accountCode, { debit: 0, credit: 0 });
      }
      const account = accounts.get(entry.accountCode)!;
      if (entry.type === 'debit') {
        account.debit += entry.amount;
      } else {
        account.credit += entry.amount;
      }
    });
    
    return Array.from(accounts.entries()).map(([code, balance]) => ({
      accountCode: code,
      debit: balance.debit,
      credit: balance.credit,
      balance: balance.debit - balance.credit
    }));
  }
}
