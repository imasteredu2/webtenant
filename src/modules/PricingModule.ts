import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 24. Pricing Management Module
export interface PricingRule extends BaseEntity {
  ruleName: string;
  description: string;
  ruleType: 'base' | 'tier' | 'volume' | 'promotional' | 'customer-specific';
  productIds: string[];
  customerSegments: string[];
  priority: number;
  active: boolean;
  startDate: Date;
  endDate?: Date;
  conditions: PricingCondition[];
  adjustments: PriceAdjustment[];
  stackable: boolean;
}

export interface PricingCondition {
  field: string;
  operator: 'equals' | 'greater-than' | 'less-than' | 'between' | 'in';
  value: any;
}

export interface PriceAdjustment {
  type: 'percentage' | 'fixed' | 'set-price';
  value: number;
  applyTo: 'base-price' | 'subtotal' | 'total';
}

export class PricingModule extends BaseModule<PricingRule> {
  constructor() {
    super('pricing-rules');
  }

  async getActiveRules(context: any): Promise<PricingRule[]> {
    const now = new Date();
    return this.search(context, (rule) =>
      rule.active &&
      rule.startDate <= now &&
      (!rule.endDate || rule.endDate >= now)
    );
  }

  async getRulesForProduct(context: any, productId: string): Promise<PricingRule[]> {
    const activeRules = await this.getActiveRules(context);
    return activeRules
      .filter(rule => rule.productIds.includes(productId))
      .sort((a, b) => b.priority - a.priority);
  }

  async calculatePrice(
    context: any,
    productId: string,
    basePrice: number,
    quantity: number,
    customerId?: string
  ): Promise<number> {
    const rules = await this.getRulesForProduct(context, productId);
    let finalPrice = basePrice;

    for (const rule of rules) {
      for (const adjustment of rule.adjustments) {
        if (adjustment.type === 'percentage') {
          finalPrice = finalPrice * (1 - adjustment.value / 100);
        } else if (adjustment.type === 'fixed') {
          finalPrice = finalPrice - adjustment.value;
        } else if (adjustment.type === 'set-price') {
          finalPrice = adjustment.value;
        }
        
        if (!rule.stackable) break;
      }
    }

    return Math.max(0, finalPrice);
  }
}
