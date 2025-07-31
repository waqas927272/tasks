# Task Management System - File Upload Feature Setup Guide

## Overview
This guide covers the setup process for the new file upload feature that allows CSM and Client users to:
- Edit task heading and description
- Upload documents and screenshots to tasks
- Receive notifications for status-only changes

## Files Created/Modified

### New Files Created:
1. **`app/Models/TaskAttachment.php`** - Model for handling file attachments
2. **`migrations/create_task_attachments_table.php`** - Database migration for attachments table

### Files Modified:
1. **`app/Controllers/TaskController.php`** - Added file upload handling
2. **`app/Models/Task.php`** - Updated notification logic for status-only changes
3. **`app/Views/tasks/edit.php`** - Added file upload form field
4. **`app/Views/tasks/show.php`** - Added attachments display section
5. **`routes/web.php`** - Added route for deleting attachments
6. **`public/js/app.js`** - Added JavaScript for attachment deletion
7. **`public/css/style.css`** - Added styles for attachments display
8. **`public/setup.php`** - Added new migration to setup process

## Setup Instructions

### Step 1: Create Database Table

Open phpMyAdmin or MySQL console and run:

```sql
USE task_management; -- or your database name

CREATE TABLE IF NOT EXISTS task_attachments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT(11) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_id (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2: Create Upload Directory

1. Navigate to: `C:\MAMP\htdocs\tasks\public\`
2. Create folder: `uploads`
3. Inside uploads, create: `tasks`

Final path: `C:\MAMP\htdocs\tasks\public\uploads\tasks\`

### Step 3: Set Permissions (if on Linux/Mac)

```bash
chmod -R 755 public/uploads
chmod -R 777 public/uploads/tasks
```

### Step 4: Update PHP Settings (if needed)

In `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

## Feature Details

### User Permissions

#### Admin Users Can:
- Edit all task fields (client, CSM, heading, description, due date, status)
- Upload/delete attachments
- View all tasks

#### CSM Users Can:
- Edit heading, description, and status for their assigned tasks
- Upload/delete attachments for their tasks
- View their assigned tasks

#### Client Users Can:
- Edit heading, description, and status for their own tasks
- Upload/delete attachments for their tasks
- View only their own tasks

### File Upload Specifications

**Allowed File Types:**
- Images: JPEG, PNG, GIF, WebP
- Documents: PDF, Word (.doc, .docx), Excel (.xls, .xlsx)

**File Size Limit:** 10MB per file

**Multiple Files:** Yes, users can upload multiple files at once

### Notification System

**Status-Only Changes:**
- When ONLY the status field is changed, notifications are sent to:
  - The Client (if CSM made the change)
  - The CSM (if Client made the change)
- The person making the change does NOT receive a notification

**Other Changes:**
- If any other fields are changed along with status, standard notification rules apply
- File uploads do not trigger notifications

## Testing Guide

### Test User Credentials

**Admin:** admin@example.com / admin123
**CSM:** john.csm@example.com / csm123
**Client:** client1@example.com / client123

### Test Scenarios

1. **Test Edit Permissions:**
   - Login as CSM/Client
   - Edit a task
   - Verify you can edit heading and description
   - Verify you cannot edit client, CSM, or due date

2. **Test File Upload:**
   - Edit any task
   - Upload multiple files
   - Save task
   - Verify files appear in task view

3. **Test File Deletion:**
   - Click delete (×) button on an attachment
   - Confirm deletion
   - Verify file is removed

4. **Test Status Notifications:**
   - Login as CSM
   - Change ONLY status of a task
   - Login as the Client
   - Check notifications for status change

## Troubleshooting

### Common Issues

1. **"File type not allowed" error**
   - Check file extension is in allowed list
   - Verify MIME type is correct

2. **"File too large" error**
   - Check file is under 10MB
   - Update PHP settings if needed

3. **Upload fails silently**
   - Check uploads directory exists
   - Verify directory permissions
   - Check PHP error logs

4. **Attachments not showing**
   - Verify task_attachments table exists
   - Check foreign key constraints
   - Clear browser cache

### Debug Checklist

- [ ] Database table `task_attachments` created
- [ ] Upload directory exists at `public/uploads/tasks/`
- [ ] Directory has write permissions
- [ ] PHP upload settings are adequate
- [ ] All code files are saved
- [ ] Browser cache is cleared

## Security Notes

- Files are validated by type and size
- Unique filenames prevent overwrites
- Only authorized users can delete attachments
- Direct file access is controlled by permissions

## Future Enhancements

Consider adding:
- Image thumbnails generation
- File compression
- Virus scanning
- S3 or cloud storage integration
- File versioning
- Drag-and-drop upload interface