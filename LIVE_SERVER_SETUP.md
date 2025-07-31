# Live Server Setup Guide

## Common Issues and Solutions

### 1. Setup.php Not Working

If setup.php is not accessible on your live server, try these solutions:

#### Solution A: Direct Access
Instead of accessing:
```
https://yourdomain.com/setup.php
```

Try:
```
https://yourdomain.com/public/setup.php
```

#### Solution B: Check .htaccess Support
1. Ensure your hosting supports `.htaccess` files
2. Enable `mod_rewrite` in your hosting control panel
3. Check if `AllowOverride All` is set for your directory

#### Solution C: Manual Database Setup
If setup.php still doesn't work, manually set up the database:

1. **Create database** through your hosting control panel
2. **Import tables** using phpMyAdmin:
   - Upload and run each file from `/migrations/` folder
3. **Create .env file** in root directory with your database details:
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

### 2. Base URL Issues

The application now auto-detects the base URL, but if you have issues:

1. **For subdirectory installation** (e.g., yourdomain.com/tasks/):
   - The system should auto-detect this
   - If not, temporarily edit `config/app.php` line 20:
   ```php
   define('BASE_URL', '/tasks'); // Change to your subdirectory
   ```

2. **For root domain installation** (e.g., yourdomain.com):
   - The system should auto-detect this
   - If not, temporarily edit `config/app.php` line 20:
   ```php
   define('BASE_URL', ''); // Empty for root domain
   ```

### 3. File Upload Issues

1. **Create upload directory**:
   ```
   /public/uploads/tasks/
   ```

2. **Set permissions** (via FTP or hosting file manager):
   - Set `/public/uploads/tasks/` to 755 or 777

3. **Check PHP settings** in hosting control panel:
   - `upload_max_filesize = 10M`
   - `post_max_size = 10M`

### 4. Session Issues

If login doesn't work:
1. Check if sessions are enabled on your hosting
2. Create `/storage/sessions/` directory if needed
3. Ensure session path is writable

### 5. Database Connection Issues

1. **Check credentials** in `.env` file
2. **Common hosting values**:
   - Host: Often `localhost` or `127.0.0.1`
   - Port: Usually `3306`
   - Some hosts use prefixed database names (e.g., `username_dbname`)

### 6. Alternative Setup Method

If automatic setup fails:

1. **Create .env manually**:
   ```bash
   cp .env.example .env
   # Edit .env with your database details
   ```

2. **Run SQL manually** in phpMyAdmin:
   ```sql
   -- Run contents of each file in /migrations/ folder
   -- In order:
   -- 1. create_users_table.php
   -- 2. create_tasks_table.php  
   -- 3. create_notifications_table.php
   -- 4. create_task_history_table.php
   -- 5. create_task_attachments_table.php
   ```

3. **Run seeders manually** (optional):
   ```sql
   -- Run contents of files in /seeders/ folder
   ```

### 7. Debug Mode

To see detailed errors, temporarily edit `public/index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Remember to disable this in production!**

### 8. Quick Checklist

- [ ] `.htaccess` files are uploaded
- [ ] `mod_rewrite` is enabled
- [ ] Database is created
- [ ] `.env` file exists with correct credentials
- [ ] `/public/uploads/tasks/` directory exists and is writable
- [ ] PHP version is 7.4 or higher
- [ ] PDO MySQL extension is enabled

### 9. Contact Hosting Support

If issues persist, contact your hosting support and ask:
1. Is `mod_rewrite` enabled?
2. Is `.htaccess` override allowed?
3. What's the correct database host?
4. Are there any security restrictions?

### 10. Emergency Access

If nothing works, you can temporarily access the app directly:
```
https://yourdomain.com/public/index.php
https://yourdomain.com/public/setup.php
```

Then work on fixing the URL rewriting later.