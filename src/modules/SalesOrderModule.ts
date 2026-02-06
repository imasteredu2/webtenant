import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 3. Sales Order Processing Module
export interface SalesOrder extends BaseEntity {
  orderNumber: string;
  customerId: string;
  orderDate: Date;
  status: 'draft' | 'pending' | 'approved' | 'shipped' | 'delivered' | 'cancelled';
  items: OrderItem[];
  subtotal: number;
  tax: number;
  shipping: number;
  total: number;
  shippingAddress: string;
  paymentMethod: string;
  paymentStatus: 'pending' | 'paid' | 'failed';
}

export interface OrderItem {
  productId: string;
  name: string;
  quantity: number;
  unitPrice: number;
  total: number;
}

export class SalesOrderModule extends BaseModule<SalesOrder> {
  constructor() {
    super('sales-orders');
  }

  async getOrdersByStatus(context: any, status: SalesOrder['status']): Promise<SalesOrder[]> {
    return this.search(context, (order) => order.status === status);
  }

  async calculateOrderTotal(items: OrderItem[], tax: number, shipping: number): Promise<number> {
    const subtotal = items.reduce((sum, item) => sum + item.total, 0);
    return subtotal + tax + shipping;
  }

  async approveOrder(context: any, id: string): Promise<SalesOrder | undefined> {
    return this.update(context, id, { status: 'approved' });
  }
}
