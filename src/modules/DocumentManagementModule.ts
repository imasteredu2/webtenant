import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 14. Document Management Module
export interface Document extends BaseEntity {
  documentNumber: string;
  title: string;
  description: string;
  category: string;
  fileType: string;
  fileSize: number;
  filePath: string;
  version: number;
  status: 'draft' | 'review' | 'approved' | 'archived';
  tags: string[];
  accessLevel: 'public' | 'internal' | 'confidential' | 'restricted';
  expirationDate?: Date;
  relatedDocuments: string[];
  checkoutBy?: string;
  checkoutDate?: Date;
}

export class DocumentManagementModule extends BaseModule<Document> {
  constructor() {
    super('documents');
  }

  async getDocumentsByCategory(context: any, category: string): Promise<Document[]> {
    return this.search(context, (doc) => doc.category === category);
  }

  async searchByTags(context: any, tags: string[]): Promise<Document[]> {
    return this.search(context, (doc) =>
      tags.some(tag => doc.tags.includes(tag))
    );
  }

  async checkoutDocument(context: any, id: string, userId: string): Promise<Document | undefined> {
    const doc = await this.get(context, id);
    if (doc && !doc.checkoutBy) {
      return this.update(context, id, {
        checkoutBy: userId,
        checkoutDate: new Date()
      });
    }
    return undefined;
  }

  async checkinDocument(context: any, id: string): Promise<Document | undefined> {
    const doc = await this.get(context, id);
    if (doc && doc.checkoutBy) {
      return this.update(context, id, {
        checkoutBy: undefined,
        checkoutDate: undefined,
        version: doc.version + 1
      });
    }
    return undefined;
  }
}
