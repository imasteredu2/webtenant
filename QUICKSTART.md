# WebTenant Quick Start Guide

This guide will help you get WebTenant up and running quickly.

## Prerequisites

- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache with mod_rewrite or Nginx)

## Installation Steps

### 1. Database Setup

Create a MySQL database:

```sql
CREATE DATABASE webtenant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'webtenant_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON webtenant.* TO 'webtenant_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configure Application

Edit `config/config.php` or set environment variables:

```bash
export DB_HOST=localhost
export DB_NAME=webtenant
export DB_USER=webtenant_user
export DB_PASS=your_secure_password
export DEBUG_MODE=true  # Disable in production
```

### 3. Run Database Setup

**Option A: Using the web interface**
Navigate to: `http://yourdomain.com/setup.php`

**Option B: Using MySQL command line**
```bash
mysql -u webtenant_user -p webtenant < database/schema.sql
```

### 4. Create Your First Tenant

```sql
USE webtenant;

-- Create a tenant
INSERT INTO tenants (name, subdomain, slug, admin_email, active, created_at)
VALUES ('My Company', 'mycompany', 'mycompany', 'admin@mycompany.com', 1, NOW());

-- Register the modules
INSERT INTO modules (slug, name, version, description, active, created_at)
VALUES 
    ('contacts', 'Contacts/CRM Module', '1.0.0', 'Contact management', 1, NOW()),
    ('tasks', 'Task Management Module', '1.0.0', 'Task tracking', 1, NOW());

-- Enable modules for the tenant
INSERT INTO tenant_modules (tenant_id, module_id, active, created_at)
SELECT 1, id, 1, NOW() FROM modules WHERE active = 1;
```

### 5. Access Your Application

**With subdomain mode (recommended):**
- Configure DNS: `*.yourdomain.com` → your server
- Access: `http://mycompany.yourdomain.com`

**With parameter mode:**
- Change `TENANT_MODE` in `config/config.php` to `'parameter'`
- Access: `http://yourdomain.com?tenant=mycompany`

## Testing the Installation

### 1. Login
Navigate to your tenant URL and login with any email/password (authentication is basic for demo).

### 2. Test Contacts Module
1. Click "Contacts" in the navigation
2. Add a new contact with name, email, phone, company
3. Edit the contact
4. Verify data is saved

### 3. Test Tasks Module
1. Click "Tasks" in the navigation
2. Add a new task with title, priority, due date
3. Filter by status (To Do, In Progress, Done)
4. Mark a task as complete
5. Verify filtering and status changes work

### 4. Test Multi-Tenancy
Create a second tenant:

```sql
INSERT INTO tenants (name, subdomain, slug, admin_email, active, created_at)
VALUES ('Second Company', 'second', 'second', 'admin@second.com', 1, NOW());

-- Enable modules for second tenant
INSERT INTO tenant_modules (tenant_id, module_id, active, created_at)
SELECT 2, id, 1, NOW() FROM modules WHERE active = 1;
```

Access: `http://second.yourdomain.com`

Add contacts and tasks to both tenants and verify:
- Data is completely isolated
- Each tenant only sees their own data
- Modules work independently for each tenant

## Common Issues

### Cannot connect to database
- Verify database credentials in `config/config.php`
- Ensure MySQL service is running
- Check firewall settings

### Setup page shows errors
- Enable DEBUG_MODE in config.php to see detailed errors
- Check PHP error logs
- Verify database user has proper permissions

### Subdomain not working
- DNS: Ensure wildcard DNS record exists: `*.yourdomain.com`
- Apache: Enable mod_rewrite and virtual host
- Nginx: Configure server block with wildcard

### Module not loading
- Check module is registered in `modules` table
- Verify module is enabled in `tenant_modules` table
- Check module.json syntax is valid
- Look for errors in PHP error log

## Web Server Configuration

### Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to index.php if file/directory doesn't exist
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Nginx

```nginx
server {
    listen 80;
    server_name *.yourdomain.com yourdomain.com;
    root /path/to/webtenant;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Next Steps

1. **Secure the Application**
   - Implement proper authentication (see TODO)
   - Enable HTTPS
   - Set DEBUG_MODE to false
   - Use strong passwords
   - Configure CSRF protection

2. **Create Custom Modules**
   - Read `docs/MODULE_DEVELOPMENT.md`
   - Start with a simple module
   - Test with multiple tenants

3. **Customize for Your Needs**
   - Modify the dashboard
   - Add your branding
   - Create domain-specific modules
   - Add user management

## Sample Data (Optional)

Add some sample data for testing:

```sql
-- Sample contacts for tenant 1
INSERT INTO contacts (tenant_id, first_name, last_name, email, phone, company, created_at)
VALUES 
    (1, 'John', 'Doe', 'john.doe@example.com', '555-0100', 'Example Corp', NOW()),
    (1, 'Jane', 'Smith', 'jane.smith@example.com', '555-0101', 'Test Inc', NOW()),
    (1, 'Bob', 'Johnson', 'bob.j@example.com', '555-0102', 'Demo LLC', NOW());

-- Sample tasks for tenant 1
INSERT INTO tasks (tenant_id, title, description, status, priority, due_date, created_at)
VALUES 
    (1, 'Setup CRM system', 'Configure and test the CRM module', 'in_progress', 'high', DATE_ADD(NOW(), INTERVAL 3 DAY), NOW()),
    (1, 'Contact new leads', 'Reach out to potential customers', 'todo', 'medium', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW()),
    (1, 'Review monthly reports', 'Check all monthly metrics', 'done', 'low', DATE_SUB(NOW(), INTERVAL 2 DAY), NOW());
```

## Support

For issues or questions:
- Check the main README.md
- Review module development guide
- Check GitHub issues: https://github.com/imasteredu2/webtenant

## Production Checklist

Before going to production:

- [ ] Set DEBUG_MODE to false
- [ ] Use environment variables for credentials
- [ ] Enable HTTPS
- [ ] Configure proper authentication
- [ ] Set up database backups
- [ ] Configure error logging
- [ ] Test tenant isolation thoroughly
- [ ] Implement rate limiting
- [ ] Add CSRF protection
- [ ] Review security settings
- [ ] Set up monitoring
