// Base URL helper - auto-detect from current location
function getBaseUrl() {
    const path = window.location.pathname;
    const publicIndex = path.indexOf('/public/');
    if (publicIndex !== -1) {
        return path.substring(0, publicIndex);
    }
    // Check if we're in a subdirectory
    const parts = path.split('/').filter(p => p);
    if (parts.length > 0 && !parts[parts.length - 1].includes('.')) {
        return '/' + parts[0];
    }
    return '';
}

const BASE_URL = getBaseUrl();

function url(path) {
    return BASE_URL + (path.startsWith('/') ? path : '/' + path);
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
    fetch(url('notifications/count'), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.getElementById('notification-count');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error:', error));
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

function checkForNewNotifications() {
    fetch(url('notifications/recent'), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update notification count
        const badge = document.getElementById('notification-count');
        if (badge && data.count > 0) {
            badge.textContent = data.count;
            badge.style.display = 'inline-block';
        } else if (badge) {
            badge.style.display = 'none';
        }
        
        // Show popup for new notifications
        if (data.notifications && data.notifications.length > 0) {
            data.notifications.forEach(notification => {
                const notificationTime = new Date(notification.created_at);
                if (notificationTime > new Date(lastNotificationCheck)) {
                    showNotificationPopup(notification);
                    
                    // Play notification sound (optional)
                    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBj');
                    audio.volume = 0.3;
                    audio.play().catch(e => console.log('Audio play failed:', e));
                }
            });
        }
        
        lastNotificationCheck = new Date().toISOString();
    })
    .catch(error => console.error('Error checking notifications:', error));
}

// Update notification count on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('notification-count')) {
        createNotificationPopup();
        updateNotificationCount();
        checkForNewNotifications();
        
        // Check for new notifications every second (1000ms)
        setInterval(checkForNewNotifications, 1000);
        
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