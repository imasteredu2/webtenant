// Export all business modules
export * from './BaseModule';
export * from './CRMModule';
export * from './InventoryModule';
export * from './SalesOrderModule';
export * from './PurchaseOrderModule';
export * from './FinancialAccountingModule';
export * from './HRModule';
export * from './PayrollModule';
export * from './ProjectManagementModule';
export * from './TaskManagementModule';
export * from './TimeTrackingModule';
export * from './InvoiceModule';
export * from './ExpenseModule';
export * from './AssetManagementModule';
export * from './DocumentManagementModule';
export * from './EmailCampaignModule';
export * from './AnalyticsModule';
export * from './SupportTicketModule';
export * from './KnowledgeBaseModule';
export * from './ContractManagementModule';
export * from './VendorManagementModule';
export * from './WarehouseModule';
export * from './ShippingModule';
export * from './ProductCatalogModule';
export * from './PricingModule';
export * from './AuditComplianceModule';

// Module Registry
import { CRMModule } from './CRMModule';
import { InventoryModule } from './InventoryModule';
import { SalesOrderModule } from './SalesOrderModule';
import { PurchaseOrderModule } from './PurchaseOrderModule';
import { FinancialAccountingModule } from './FinancialAccountingModule';
import { HRModule } from './HRModule';
import { PayrollModule } from './PayrollModule';
import { ProjectManagementModule } from './ProjectManagementModule';
import { TaskManagementModule } from './TaskManagementModule';
import { TimeTrackingModule } from './TimeTrackingModule';
import { InvoiceModule } from './InvoiceModule';
import { ExpenseModule } from './ExpenseModule';
import { AssetManagementModule } from './AssetManagementModule';
import { DocumentManagementModule } from './DocumentManagementModule';
import { EmailCampaignModule } from './EmailCampaignModule';
import { AnalyticsModule } from './AnalyticsModule';
import { SupportTicketModule } from './SupportTicketModule';
import { KnowledgeBaseModule } from './KnowledgeBaseModule';
import { ContractManagementModule } from './ContractManagementModule';
import { VendorManagementModule } from './VendorManagementModule';
import { WarehouseModule } from './WarehouseModule';
import { ShippingModule } from './ShippingModule';
import { ProductCatalogModule } from './ProductCatalogModule';
import { PricingModule } from './PricingModule';
import { AuditComplianceModule } from './AuditComplianceModule';

export class ModuleRegistry {
  private static instance: ModuleRegistry;
  private modules: Map<string, any>;

  private constructor() {
    this.modules = new Map();
    this.initializeModules();
  }

  static getInstance(): ModuleRegistry {
    if (!ModuleRegistry.instance) {
      ModuleRegistry.instance = new ModuleRegistry();
    }
    return ModuleRegistry.instance;
  }

  private initializeModules(): void {
    // Register all 25 business modules
    this.modules.set('crm', new CRMModule());
    this.modules.set('inventory', new InventoryModule());
    this.modules.set('sales-orders', new SalesOrderModule());
    this.modules.set('purchase-orders', new PurchaseOrderModule());
    this.modules.set('accounting', new FinancialAccountingModule());
    this.modules.set('hr', new HRModule());
    this.modules.set('payroll', new PayrollModule());
    this.modules.set('projects', new ProjectManagementModule());
    this.modules.set('tasks', new TaskManagementModule());
    this.modules.set('time-tracking', new TimeTrackingModule());
    this.modules.set('invoices', new InvoiceModule());
    this.modules.set('expenses', new ExpenseModule());
    this.modules.set('assets', new AssetManagementModule());
    this.modules.set('documents', new DocumentManagementModule());
    this.modules.set('email-campaigns', new EmailCampaignModule());
    this.modules.set('analytics', new AnalyticsModule());
    this.modules.set('support-tickets', new SupportTicketModule());
    this.modules.set('knowledge-base', new KnowledgeBaseModule());
    this.modules.set('contracts', new ContractManagementModule());
    this.modules.set('vendors', new VendorManagementModule());
    this.modules.set('warehouses', new WarehouseModule());
    this.modules.set('shipments', new ShippingModule());
    this.modules.set('products', new ProductCatalogModule());
    this.modules.set('pricing', new PricingModule());
    this.modules.set('audit', new AuditComplianceModule());
  }

  getModule<T>(moduleName: string): T | undefined {
    return this.modules.get(moduleName) as T;
  }

  getAllModules(): Map<string, any> {
    return this.modules;
  }

  listModuleNames(): string[] {
    return Array.from(this.modules.keys());
  }
}
