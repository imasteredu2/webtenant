import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 2. Inventory Management Module
export interface InventoryItem extends BaseEntity {
  sku: string;
  name: string;
  description: string;
  category: string;
  quantity: number;
  reorderPoint: number;
  unitPrice: number;
  supplier: string;
  location: string;
  status: 'in-stock' | 'low-stock' | 'out-of-stock';
}

export class InventoryModule extends BaseModule<InventoryItem> {
  constructor() {
    super('inventory');
  }

  async getLowStockItems(context: any): Promise<InventoryItem[]> {
    return this.search(context, (item) => item.quantity <= item.reorderPoint);
  }

  async updateQuantity(context: any, id: string, quantityChange: number): Promise<InventoryItem | undefined> {
    const item = await this.get(context, id);
    if (item) {
      const newQuantity = item.quantity + quantityChange;
      const status = newQuantity === 0 ? 'out-of-stock' : 
                     newQuantity <= item.reorderPoint ? 'low-stock' : 'in-stock';
      return this.update(context, id, { quantity: newQuantity, status });
    }
    return undefined;
  }
}
