import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 12. Expense Tracking Module
export interface Expense extends BaseEntity {
  expenseNumber: string;
  employeeId: string;
  category: string;
  amount: number;
  currency: string;
  expenseDate: Date;
  description: string;
  merchant: string;
  paymentMethod: string;
  status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'reimbursed';
  receipt?: string;
  projectId?: string;
  billable: boolean;
  reimbursable: boolean;
  approvedBy?: string;
  approvedDate?: Date;
}

export class ExpenseModule extends BaseModule<Expense> {
  constructor() {
    super('expenses');
  }

  async getPendingExpenses(context: any): Promise<Expense[]> {
    return this.search(context, (expense) => expense.status === 'submitted');
  }

  async approveExpense(context: any, id: string, approverId: string): Promise<Expense | undefined> {
    return this.update(context, id, {
      status: 'approved',
      approvedBy: approverId,
      approvedDate: new Date()
    });
  }

  async getExpensesByEmployee(context: any, employeeId: string): Promise<Expense[]> {
    return this.search(context, (expense) => expense.employeeId === employeeId);
  }

  async getTotalExpenses(context: any, startDate: Date, endDate: Date): Promise<number> {
    const expenses = await this.search(context, (expense) =>
      expense.expenseDate >= startDate && expense.expenseDate <= endDate
    );
    return expenses.reduce((total, expense) => total + expense.amount, 0);
  }
}
