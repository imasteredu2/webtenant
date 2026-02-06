import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 15. Email Campaign Management Module
export interface EmailCampaign extends BaseEntity {
  campaignName: string;
  subject: string;
  content: string;
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'cancelled';
  scheduledDate?: Date;
  sentDate?: Date;
  recipientList: string;
  totalRecipients: number;
  sent: number;
  delivered: number;
  opened: number;
  clicked: number;
  bounced: number;
  unsubscribed: number;
  template?: string;
  tags: string[];
  fromEmail: string;
  fromName: string;
}

export class EmailCampaignModule extends BaseModule<EmailCampaign> {
  constructor() {
    super('email-campaigns');
  }

  async getActiveCampaigns(context: any): Promise<EmailCampaign[]> {
    return this.search(context, (campaign) => 
      campaign.status === 'scheduled' || campaign.status === 'sending'
    );
  }

  async getCampaignMetrics(context: any, id: string): Promise<any> {
    const campaign = await this.get(context, id);
    if (campaign) {
      const openRate = campaign.sent > 0 ? (campaign.opened / campaign.sent) * 100 : 0;
      const clickRate = campaign.sent > 0 ? (campaign.clicked / campaign.sent) * 100 : 0;
      const deliveryRate = campaign.sent > 0 ? (campaign.delivered / campaign.sent) * 100 : 0;
      
      return {
        totalRecipients: campaign.totalRecipients,
        sent: campaign.sent,
        delivered: campaign.delivered,
        opened: campaign.opened,
        clicked: campaign.clicked,
        bounced: campaign.bounced,
        unsubscribed: campaign.unsubscribed,
        openRate: openRate.toFixed(2),
        clickRate: clickRate.toFixed(2),
        deliveryRate: deliveryRate.toFixed(2)
      };
    }
    return null;
  }
}
