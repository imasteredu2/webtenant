import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 4. Purchase Order Management Module
export interface PurchaseOrder extends BaseEntity {
  poNumber: string;
  vendorId: string;
  orderDate: Date;
  expectedDeliveryDate: Date;
  status: 'draft' | 'sent' | 'acknowledged' | 'received' | 'cancelled';
  items: PurchaseOrderItem[];
  subtotal: number;
  tax: number;
  shipping: number;
  total: number;
  terms: string;
  approvedBy?: string;
}

export interface PurchaseOrderItem {
  itemId: string;
  description: string;
  quantity: number;
  unitPrice: number;
  total: number;
}

export class PurchaseOrderModule extends BaseModule<PurchaseOrder> {
  constructor() {
    super('purchase-orders');
  }

  async getPendingOrders(context: any): Promise<PurchaseOrder[]> {
    return this.search(context, (po) => po.status === 'sent' || po.status === 'acknowledged');
  }

  async receiveOrder(context: any, id: string): Promise<PurchaseOrder | undefined> {
    return this.update(context, id, { status: 'received' });
  }
}
