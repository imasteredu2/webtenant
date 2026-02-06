import { BaseModule } from './BaseModule';
import { BaseEntity } from '../types';

// 17. Customer Support Ticketing Module
export interface SupportTicket extends BaseEntity {
  ticketNumber: string;
  customerId: string;
  subject: string;
  description: string;
  status: 'open' | 'in-progress' | 'waiting-customer' | 'resolved' | 'closed';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  category: string;
  assignedTo?: string;
  responses: TicketResponse[];
  attachments: string[];
  slaDeadline?: Date;
  resolvedAt?: Date;
  resolution?: string;
  satisfaction?: number;
}

export interface TicketResponse {
  id: string;
  userId: string;
  message: string;
  timestamp: Date;
  isCustomer: boolean;
}

export class SupportTicketModule extends BaseModule<SupportTicket> {
  constructor() {
    super('support-tickets');
  }

  async getOpenTickets(context: any): Promise<SupportTicket[]> {
    return this.search(context, (ticket) => 
      ticket.status === 'open' || ticket.status === 'in-progress'
    );
  }

  async getTicketsByAssignee(context: any, userId: string): Promise<SupportTicket[]> {
    return this.search(context, (ticket) => ticket.assignedTo === userId);
  }

  async addResponse(context: any, id: string, message: string, isCustomer: boolean): Promise<SupportTicket | undefined> {
    const ticket = await this.get(context, id);
    if (ticket) {
      const response: TicketResponse = {
        id: Date.now().toString(),
        userId: context.userId,
        message,
        timestamp: new Date(),
        isCustomer
      };
      const responses = [...ticket.responses, response];
      return this.update(context, id, { responses });
    }
    return undefined;
  }

  async resolveTicket(context: any, id: string, resolution: string): Promise<SupportTicket | undefined> {
    return this.update(context, id, {
      status: 'resolved',
      resolution,
      resolvedAt: new Date()
    });
  }
}
