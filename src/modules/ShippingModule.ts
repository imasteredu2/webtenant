import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 22. Shipping & Logistics Module
export interface Shipment extends BaseEntity {
  shipmentNumber: string;
  orderId: string;
  carrier: string;
  trackingNumber: string;
  method: 'ground' | 'air' | 'sea' | 'express';
  status: 'pending' | 'picked-up' | 'in-transit' | 'out-for-delivery' | 'delivered' | 'failed' | 'returned';
  origin: Address;
  destination: Address;
  weight: number;
  dimensions: {
    length: number;
    width: number;
    height: number;
    unit: string;
  };
  estimatedDelivery: Date;
  actualDelivery?: Date;
  cost: number;
  items: ShipmentItem[];
  insurance?: number;
  signature_required: boolean;
  trackingEvents: TrackingEvent[];
}

export interface Address {
  street: string;
  city: string;
  state: string;
  zipCode: string;
  country: string;
}

export interface ShipmentItem {
  itemId: string;
  quantity: number;
  description: string;
}

export interface TrackingEvent {
  timestamp: Date;
  location: string;
  status: string;
  description: string;
}

export class ShippingModule extends BaseModule<Shipment> {
  constructor() {
    super('shipments');
  }

  async getActiveShipments(context: any): Promise<Shipment[]> {
    return this.search(context, (shipment) =>
      shipment.status === 'in-transit' || shipment.status === 'out-for-delivery'
    );
  }

  async getShipmentsByCarrier(context: any, carrier: string): Promise<Shipment[]> {
    return this.search(context, (shipment) => shipment.carrier === carrier);
  }

  async addTrackingEvent(context: any, id: string, event: TrackingEvent): Promise<Shipment | undefined> {
    const shipment = await this.get(context, id);
    if (shipment) {
      const trackingEvents = [...shipment.trackingEvents, event];
      return this.update(context, id, { trackingEvents });
    }
    return undefined;
  }

  async markDelivered(context: any, id: string): Promise<Shipment | undefined> {
    return this.update(context, id, {
      status: 'delivered',
      actualDelivery: new Date()
    });
  }
}
