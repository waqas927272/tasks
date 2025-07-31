// Base URL helper - works for both localhost and production
function getBaseUrl() {
    const path = window.location.pathname;
    
    // Remove filename if present
    let cleanPath = path;
    if (path.includes('.php')) {
        cleanPath = path.substring(0, path.lastIndexOf('/'));
    }
    
    // Remove trailing slash
    cleanPath = cleanPath.replace(/\/$/, '');
    
    // For production site (tasks.waqaskhattak.com)
    if (window.location.hostname.includes('waqaskhattak.com')) {
        // Always return /tasks for the production site
        return '/tasks';
    }
    
    // For localhost, find the /tasks directory
    const parts = cleanPath.split('/').filter(p => p);
    const tasksIndex = parts.indexOf('tasks');
    
    if (tasksIndex !== -1) {
        // Build path up to and including 'tasks'
        return '/' + parts.slice(0, tasksIndex + 1).join('/');
    }
    
    // Default for localhost at root
    return '';
}

const BASE_URL = getBaseUrl();

function url(path) {
    // Clean the path
    path = path.replace(/^\/+/, '').replace(/\/+$/, '');
    
    // Handle empty base URL
    if (!BASE_URL) {
        return '/' + path;
    }
    
    // Build the full URL - ensure it starts with /
    const fullUrl = BASE_URL + '/' + path;
    
    // For absolute URLs, ensure they start from the root
    if (!fullUrl.startsWith('/')) {
        return '/' + fullUrl;
    }
    
    return fullUrl;
}

// Delete Task
function deleteTask(id) {
    if (confirm('Are you sure you want to delete this task?')) {
        fetch(url(`tasks/${id}`), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = url('tasks');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Delete User
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(url(`users/${id}`), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Mark Notification as Read
function markAsRead(id) {
    fetch(url(`notifications/${id}/read`), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Mark All Notifications as Read
function markAllAsRead() {
    fetch(url('notifications/read-all'), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Update Notification Count
function updateNotificationCount() {
    const countUrl = url('notifications/count');
    console.log('[UpdateCount] Fetching from:', countUrl);
    
    fetch(countUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('[UpdateCount] Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('[UpdateCount] Data received:', data);
        const badge = document.getElementById('notification-count');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-flex';
                console.log('[UpdateCount] Badge updated with count:', data.count);
            } else {
                badge.style.display = 'none';
                console.log('[UpdateCount] Badge hidden (count is 0)');
            }
        } else {
            console.error('[UpdateCount] Badge element not found!');
        }
    })
    .catch(error => console.error('[UpdateCount] Error:', error));
}

// Real-time notification system
let lastNotificationCheck = new Date().toISOString();
let notificationPopupContainer;

function createNotificationPopup() {
    if (!notificationPopupContainer) {
        notificationPopupContainer = document.createElement('div');
        notificationPopupContainer.id = 'notification-popup-container';
        notificationPopupContainer.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            max-width: 400px;
            z-index: 9999;
        `;
        document.body.appendChild(notificationPopupContainer);
    }
}

function showNotificationPopup(notification) {
    const popup = document.createElement('div');
    popup.className = 'notification-popup';
    popup.style.cssText = `
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
        cursor: pointer;
    `;
    
    popup.innerHTML = `
        <div style="font-weight: bold; margin-bottom: 5px; color: #2c3e50;">
            Task: ${notification.task_heading}
        </div>
        <div style="color: #666; font-size: 14px;">
            ${notification.message}
        </div>
        <div style="color: #999; font-size: 12px; margin-top: 5px;">
            ${new Date(notification.created_at).toLocaleString()}
        </div>
    `;
    
    popup.onclick = function() {
        window.location.href = url('tasks/' + notification.task_id);
    };
    
    notificationPopupContainer.appendChild(popup);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        popup.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => popup.remove(), 300);
    }, 5000);
}

// Keep track of shown notification IDs to avoid duplicates
let shownNotificationIds = new Set();

function checkForNewNotifications() {
    fetch(url('notifications/recent'), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update notification count badge
        const badge = document.getElementById('notification-count');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-flex';
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
            }
        }
        
        // Show popup for new notifications
        if (data.notifications && data.notifications.length > 0) {
            data.notifications.forEach(notification => {
                // Only show popup if we haven't shown this notification before
                if (!shownNotificationIds.has(notification.id)) {
                    const notificationTime = new Date(notification.created_at);
                    
                    // Only show popup for notifications created after our last check
                    if (notificationTime > new Date(lastNotificationCheck)) {
                        showNotificationPopup(notification);
                        shownNotificationIds.add(notification.id);
                        
                        // Play notification sound
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBj');
                            audio.volume = 0.3;
                            audio.play().catch(e => console.log('Audio play failed:', e));
                        } catch (e) {
                            console.log('Audio not supported:', e);
                        }
                    }
                }
            });
        }
        
        lastNotificationCheck = new Date().toISOString();
    })
    .catch(error => console.error('Error checking notifications:', error));
}

// Update notification count on page load
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log URL information
    console.log('=== URL Debug Info ===');
    console.log('Hostname:', window.location.hostname);
    console.log('Pathname:', window.location.pathname);
    console.log('Base URL detected:', BASE_URL);
    console.log('Test URLs:');
    console.log('  - notifications/recent:', url('notifications/recent'));
    console.log('  - notifications/count:', url('notifications/count'));
    console.log('===================');
    
    if (document.getElementById('notification-count')) {
        createNotificationPopup();
        
        // Initial load - get unread count immediately
        updateNotificationCount();
        
        // Set last check time to now to avoid showing old notifications as popups
        lastNotificationCheck = new Date().toISOString();
        
        // Start checking for new notifications after a delay
        setTimeout(() => {
            checkForNewNotifications();
            
            // Check for new notifications every 3 seconds
            setInterval(checkForNewNotifications, 3000);
        }, 1000);
        
        // Update count every 30 seconds (as backup)
        setInterval(updateNotificationCount, 30000);
    }
});

// Delete Attachment
function deleteAttachment(id) {
    if (confirm('Are you sure you want to delete this attachment?')) {
        fetch(url(`attachments/${id}`), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});