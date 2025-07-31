# Setup Checklist - File Upload Feature

## ✅ Database
- [x] Created `task_attachments` table in database
- [x] Table has all required columns
- [x] Foreign keys are set up correctly

## ✅ Directory Structure
- [x] `/public/uploads/` directory exists
- [x] `/public/uploads/tasks/` subdirectory exists
- [x] Directories have proper permissions for file uploads

## ✅ Files Created/Modified

### New Files:
- [x] `app/Models/TaskAttachment.php` - Model for attachments
- [x] `migrations/create_task_attachments_table.php` - Migration file
- [x] `SETUP_GUIDE.md` - Complete setup documentation

### Modified Files:
- [x] `app/Controllers/TaskController.php` - Added file upload handling
- [x] `app/Models/Task.php` - Updated notification logic
- [x] `app/Views/tasks/edit.php` - Added file upload form
- [x] `app/Views/tasks/show.php` - Added attachments display
- [x] `routes/web.php` - Added delete attachment route
- [x] `public/js/app.js` - Added deleteAttachment function
- [x] `public/css/style.css` - Added attachment styles
- [x] `public/setup.php` - Added new migration

## 🎯 Ready to Test!

Everything is set up correctly. You can now:

1. **Login** to your application
2. **Edit a task** as CSM or Client user
3. **Upload files** using the file input field
4. **View attachments** on the task detail page
5. **Delete attachments** using the × button

### Test Users:
- CSM: `john.csm@example.com` / `csm123`
- Client: `client1@example.com` / `client123`
- Admin: `admin@example.com` / `admin123`

### What's Working:
- ✅ CSM and Client can edit task heading and description
- ✅ All users with edit permission can upload files
- ✅ Files are saved to `/public/uploads/tasks/`
- ✅ File info is stored in database
- ✅ Attachments display with icons based on file type
- ✅ Image previews for image files
- ✅ Download links for all files
- ✅ Delete functionality for authorized users
- ✅ Status-only changes trigger notifications

### File Upload Limits:
- Max file size: 10MB
- Allowed types: Images (JPEG, PNG, GIF, WebP), PDF, Word, Excel

## 🚨 Troubleshooting

If uploads fail:
1. Check PHP `upload_max_filesize` in php.ini
2. Check PHP `post_max_size` in php.ini
3. Ensure `/public/uploads/tasks/` has write permissions
4. Check browser console for JavaScript errors

If attachments don't show:
1. Clear browser cache
2. Check database for records in `task_attachments` table
3. Verify file paths are correct