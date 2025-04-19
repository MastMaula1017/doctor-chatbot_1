document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const userMessageInput = document.getElementById('user-message');
    const chatMessages = document.getElementById('chat-messages');
    const addHistoryForm = document.getElementById('add-history-form');
    
    // Function to add a message to the chat
    function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.classList.add(isUser ? 'user' : 'bot');
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <p>${message}</p>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Handle chat form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const userMessage = userMessageInput.value.trim();
        if (!userMessage) return;
        
        // Add user message to chat
        addMessage(userMessage, true);
        
        // Clear input
        userMessageInput.value = '';
        
        // Show typing indicator
        const typingDiv = document.createElement('div');
        typingDiv.classList.add('message', 'bot');
        typingDiv.innerHTML = `
            <div class="message-content">
                <p>Typing...</p>
            </div>
        `;
        chatMessages.appendChild(typingDiv);
        
        // Send message to server
        fetch('api/chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: userMessage })
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            chatMessages.removeChild(typingDiv);
            
            // Add bot response
            addMessage(data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            // Remove typing indicator
            chatMessages.removeChild(typingDiv);
            
            // Add error message
            addMessage('Sorry, I encountered an error. Please try again later.');
        });
    });
    
    // Handle add history form submission
    if (addHistoryForm) {
        addHistoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const condition = document.getElementById('condition').value.trim();
            const diagnosisDate = document.getElementById('diagnosis_date').value;
            const notes = document.getElementById('notes').value.trim();
            
            if (!condition) return;
            
            fetch('api/history.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    condition: condition,
                    diagnosis_date: diagnosisDate,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear form
                    document.getElementById('condition').value = '';
                    document.getElementById('diagnosis_date').value = '';
                    document.getElementById('notes').value = '';
                    
                    // Reload page to show new history
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
});