import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 21. Warehouse Management Module
export interface Warehouse extends BaseEntity {
  warehouseCode: string;
  name: string;
  location: string;
  type: 'distribution' | 'storage' | 'fulfillment';
  capacity: number;
  currentOccupancy: number;
  zones: WarehouseZone[];
  manager: string;
  status: 'active' | 'maintenance' | 'closed';
  operatingHours: string;
  contact: {
    phone: string;
    email: string;
  };
}

export interface WarehouseZone {
  id: string;
  name: string;
  type: 'receiving' | 'storage' | 'picking' | 'packing' | 'shipping';
  capacity: number;
  currentOccupancy: number;
}

export class WarehouseModule extends BaseModule<Warehouse> {
  constructor() {
    super('warehouses');
  }

  async getActiveWarehouses(context: any): Promise<Warehouse[]> {
    return this.search(context, (warehouse) => warehouse.status === 'active');
  }

  async getWarehouseUtilization(context: any, id: string): Promise<number> {
    const warehouse = await this.get(context, id);
    if (warehouse && warehouse.capacity > 0) {
      return (warehouse.currentOccupancy / warehouse.capacity) * 100;
    }
    return 0;
  }

  async updateOccupancy(context: any, id: string, change: number): Promise<Warehouse | undefined> {
    const warehouse = await this.get(context, id);
    if (warehouse) {
      const currentOccupancy = Math.max(0, warehouse.currentOccupancy + change);
      return this.update(context, id, { currentOccupancy });
    }
    return undefined;
  }
}
