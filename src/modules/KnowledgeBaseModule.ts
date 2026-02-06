import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 18. Knowledge Base Module
export interface KnowledgeArticle extends BaseEntity {
  articleNumber: string;
  title: string;
  content: string;
  summary: string;
  category: string;
  tags: string[];
  status: 'draft' | 'review' | 'published' | 'archived';
  author: string;
  views: number;
  helpful: number;
  notHelpful: number;
  lastReviewedDate?: Date;
  publishedDate?: Date;
  relatedArticles: string[];
  attachments: string[];
}

export class KnowledgeBaseModule extends BaseModule<KnowledgeArticle> {
  constructor() {
    super('knowledge-base');
  }

  async getPublishedArticles(context: any): Promise<KnowledgeArticle[]> {
    return this.search(context, (article) => article.status === 'published');
  }

  async searchArticles(context: any, query: string): Promise<KnowledgeArticle[]> {
    const lowerQuery = query.toLowerCase();
    return this.search(context, (article) =>
      article.title.toLowerCase().includes(lowerQuery) ||
      article.content.toLowerCase().includes(lowerQuery) ||
      article.tags.some(tag => tag.toLowerCase().includes(lowerQuery))
    );
  }

  async getPopularArticles(context: any, limit: number): Promise<KnowledgeArticle[]> {
    const articles = await this.getPublishedArticles(context);
    return articles
      .sort((a, b) => b.views - a.views)
      .slice(0, limit);
  }

  async recordView(context: any, id: string): Promise<KnowledgeArticle | undefined> {
    const article = await this.get(context, id);
    if (article) {
      return this.update(context, id, { views: article.views + 1 });
    }
    return undefined;
  }

  async recordFeedback(context: any, id: string, helpful: boolean): Promise<KnowledgeArticle | undefined> {
    const article = await this.get(context, id);
    if (article) {
      if (helpful) {
        return this.update(context, id, { helpful: article.helpful + 1 });
      } else {
        return this.update(context, id, { notHelpful: article.notHelpful + 1 });
      }
    }
    return undefined;
  }
}
