/* assets/js/main.js */

document.addEventListener('DOMContentLoaded', () => {
    // Mobile Menu Toggle (Simplified for prototype)
    const toggle = document.querySelector('.menu-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
                if (sidebar.classList.contains('active')) {
                    sidebar.style.display = 'block';
                } else {
                    sidebar.style.display = 'none';
                }
            }
        });
    }

    // Auto-dismiss Alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // Chat Polling (Only if on special chat page)
    const chatBox = document.querySelector('.chat-box');
    if (chatBox) {
        const orderId = chatBox.dataset.orderId;
        const currentUserId = chatBox.dataset.userId;

        const fetchMessages = () => {
            fetch(`../ajax/chat_api.php?order_id=${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        chatBox.innerHTML = '';
                        data.messages.forEach(msg => {
                            const msgDiv = document.createElement('div');
                            msgDiv.classList.add('message');
                            msgDiv.classList.add(msg.sender_id == currentUserId ? 'sent' : 'received');
                            msgDiv.innerHTML = `
                                <div class="msg-content">${msg.message}</div>
                                <div class="msg-time" style="font-size: 0.7rem; opacity: 0.7; margin-top: 5px;">
                                    ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </div>
                            `;
                            chatBox.appendChild(msgDiv);
                        });
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                });
        };

        // Poll every 3 seconds
        fetchMessages();
        setInterval(fetchMessages, 3000);

        // Send Message
        const chatForm = document.getElementById('chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const msgInput = document.getElementById('message-input');
                const message = msgInput.value;
                if (!message.trim()) return;

                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('message', message);

                fetch('../ajax/chat_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        msgInput.value = '';
                        fetchMessages();
                    }
                });
            });
        }
    }
});
