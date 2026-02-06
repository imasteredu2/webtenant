import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 13. Asset Management Module
export interface Asset extends BaseEntity {
  assetTag: string;
  name: string;
  description: string;
  category: string;
  serialNumber: string;
  purchaseDate: Date;
  purchasePrice: number;
  currentValue: number;
  depreciationRate: number;
  location: string;
  assignedTo?: string;
  status: 'available' | 'in-use' | 'maintenance' | 'retired' | 'disposed';
  warrantyExpiration?: Date;
  lastMaintenanceDate?: Date;
  nextMaintenanceDate?: Date;
}

export class AssetManagementModule extends BaseModule<Asset> {
  constructor() {
    super('assets');
  }

  async getAssetsByLocation(context: any, location: string): Promise<Asset[]> {
    return this.search(context, (asset) => asset.location === location);
  }

  async getAssetsForMaintenance(context: any): Promise<Asset[]> {
    const now = new Date();
    return this.search(context, (asset) =>
      asset.nextMaintenanceDate !== undefined && asset.nextMaintenanceDate <= now
    );
  }

  async assignAsset(context: any, id: string, userId: string): Promise<Asset | undefined> {
    return this.update(context, id, { assignedTo: userId, status: 'in-use' });
  }

  async calculateDepreciation(asset: Asset): Promise<number> {
    const years = (new Date().getTime() - asset.purchaseDate.getTime()) / (1000 * 60 * 60 * 24 * 365);
    return asset.purchasePrice * Math.pow(1 - asset.depreciationRate, years);
  }
}
