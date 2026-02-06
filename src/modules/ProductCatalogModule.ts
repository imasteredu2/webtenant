import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 23. Product Catalog Module
export interface Product extends BaseEntity {
  sku: string;
  name: string;
  description: string;
  shortDescription: string;
  category: string;
  subcategory: string;
  brand: string;
  tags: string[];
  status: 'active' | 'inactive' | 'discontinued';
  visibility: 'public' | 'private' | 'hidden';
  images: string[];
  specifications: Record<string, string>;
  variants: ProductVariant[];
  seoTitle?: string;
  seoDescription?: string;
  seoKeywords?: string[];
}

export interface ProductVariant {
  id: string;
  sku: string;
  name: string;
  attributes: Record<string, string>; // e.g., { color: 'red', size: 'M' }
  price: number;
  compareAtPrice?: number;
  cost: number;
  stock: number;
  weight: number;
  dimensions?: {
    length: number;
    width: number;
    height: number;
  };
}

export class ProductCatalogModule extends BaseModule<Product> {
  constructor() {
    super('products');
  }

  async getActiveProducts(context: any): Promise<Product[]> {
    return this.search(context, (product) => product.status === 'active');
  }

  async getProductsByCategory(context: any, category: string): Promise<Product[]> {
    return this.search(context, (product) => product.category === category);
  }

  async searchProducts(context: any, query: string): Promise<Product[]> {
    const lowerQuery = query.toLowerCase();
    return this.search(context, (product) =>
      product.name.toLowerCase().includes(lowerQuery) ||
      product.description.toLowerCase().includes(lowerQuery) ||
      product.tags.some(tag => tag.toLowerCase().includes(lowerQuery))
    );
  }

  async getProductsByBrand(context: any, brand: string): Promise<Product[]> {
    return this.search(context, (product) => product.brand === brand);
  }
}
