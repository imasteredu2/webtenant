import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 11. Invoice Generation Module
export interface Invoice extends BaseEntity {
  invoiceNumber: string;
  customerId: string;
  invoiceDate: Date;
  dueDate: Date;
  status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
  items: InvoiceItem[];
  subtotal: number;
  tax: number;
  discount: number;
  total: number;
  amountPaid: number;
  balance: number;
  terms: string;
  notes: string;
  paymentMethod?: string;
}

export interface InvoiceItem {
  description: string;
  quantity: number;
  unitPrice: number;
  amount: number;
  taxable: boolean;
}

export class InvoiceModule extends BaseModule<Invoice> {
  constructor() {
    super('invoices');
  }

  async getOverdueInvoices(context: any): Promise<Invoice[]> {
    const now = new Date();
    return this.search(context, (invoice) => 
      invoice.dueDate < now && invoice.status === 'sent' && invoice.balance > 0
    );
  }

  async recordPayment(context: any, id: string, amount: number): Promise<Invoice | undefined> {
    const invoice = await this.get(context, id);
    if (invoice) {
      const amountPaid = invoice.amountPaid + amount;
      const balance = invoice.total - amountPaid;
      const status = balance <= 0 ? 'paid' : invoice.status;
      return this.update(context, id, { amountPaid, balance, status });
    }
    return undefined;
  }
}
