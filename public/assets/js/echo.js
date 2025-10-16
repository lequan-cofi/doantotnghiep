/**
 * Laravel Echo Configuration
 * Handles real-time features for the application
 */

// Check if Pusher is available
if (typeof Pusher !== 'undefined') {
    // Initialize Pusher
    window.Pusher = Pusher;
    
    // Initialize Echo only if available
    if (typeof Echo !== 'undefined') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'your-pusher-key',
            cluster: 'ap1',
            forceTLS: true,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            }
        });
    }
    
    // Listen for notifications
    if (window.Echo && typeof window.Echo.channel === 'function') {
        // Listen for user-specific notifications
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (userId) {
            window.Echo.private(`user.${userId}`)
                .listen('.notification', (e) => {
                    console.log('Notification received:', e);
                    // Handle notification display
                    if (typeof notification !== 'undefined') {
                        notification.info(e.message || 'Bạn có thông báo mới');
                    }
                });
        }
        
        // Listen for review-related events
        window.Echo.channel('reviews')
            .listen('.review.created', (e) => {
                console.log('New review created:', e);
            })
            .listen('.review.updated', (e) => {
                console.log('Review updated:', e);
            })
            .listen('.review.replied', (e) => {
                console.log('Review replied:', e);
                if (typeof notification !== 'undefined') {
                    notification.success('Chủ nhà đã phản hồi đánh giá của bạn!');
                }
            });
    }
} else {
    console.warn('Pusher is not loaded. Real-time features will not work.');
}

// Fallback Echo object for when Pusher is not available
if (typeof window.Echo === 'undefined') {
    window.Echo = {
        channel: function() {
            return {
                listen: function() { return this; },
                stopListening: function() { return this; }
            };
        },
        private: function() {
            return {
                listen: function() { return this; },
                stopListening: function() { return this; }
            };
        },
        leave: function() {},
        disconnect: function() {}
    };
}
