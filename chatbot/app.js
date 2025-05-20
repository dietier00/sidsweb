const chatToggle = document.getElementById('chat-toggle');
const chatWidget = document.getElementById('chat-widget');
const closeChat = document.getElementById('close-chat');
const sendBtn = document.getElementById('send-btn');
const chatBody = document.getElementById('chat-body');
const userInput = document.getElementById('user-input');
const quickRepliesContainer = document.getElementById('quick-replies');

// Event Listeners
chatToggle?.addEventListener('click', () => {
    console.log('Toggle clicked');
    chatWidget.classList.toggle('hidden');
});

closeChat?.addEventListener('click', () => {
    chatWidget.classList.add('hidden');
});

userInput?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

sendBtn?.addEventListener('click', sendMessage);

function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;

    appendMessage(message, 'user');
    userInput.value = '';
    quickRepliesContainer.innerHTML = ''; // Clear quick replies after sending
    simulateBotResponse(message);
}

function appendMessage(text, sender) {
    if (!chatBody) return;
    
    const msgDiv = document.createElement('div');
    msgDiv.className = sender === 'user' ? 'user-message' : 'bot-message';
    
    // Handle emojis and formatting
    const formattedText = text.replace(/\n/g, '<br>');
    msgDiv.innerHTML = formattedText;
    
    chatBody.appendChild(msgDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function addQuickReply(text) {
    if (!quickRepliesContainer) return;

    const quickReply = document.createElement('button');
    quickReply.className = 'quick-reply';
    quickReply.textContent = text;
    quickReply.addEventListener('click', () => {
        userInput.value = text;
        sendMessage();
    });
    quickRepliesContainer.appendChild(quickReply);
}

async function simulateBotResponse(text) {
    try {
        // Show typing indicator
        appendMessage('Typing...', 'bot');
        
        // Call the server API
        const response = await fetch('/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: text })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        
        // Remove typing indicator
        if (chatBody.lastChild) {
            chatBody.removeChild(chatBody.lastChild);
        }

        // Add the bot's response
        appendMessage(data.reply, 'bot');

        // Add contextual quick replies based on the response
        if (data.reply.toLowerCase().includes('product')) {
            addQuickReply("Show Roller Blinds");
            addQuickReply("Show Venetian Blinds");
        } else if (data.reply.toLowerCase().includes('price')) {
            addQuickReply("Get a quote");
            addQuickReply("View price list");
        } else {
            addQuickReply("Products");
            addQuickReply("Pricing");
            addQuickReply("Contact Support");
        }

    } catch (error) {
        console.error('Error:', error);
        // Remove typing indicator
        if (chatBody.lastChild) {
            chatBody.removeChild(chatBody.lastChild);
        }
        appendMessage("Sorry, I'm having trouble connecting right now. Please try again later.", 'bot');
    }
}

// Update initial message to use the API
window.addEventListener('load', async () => {
    try {
        const response = await fetch('/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: 'start' })
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        appendMessage(data.reply, 'bot');
        
        // Default quick replies
        addQuickReply("Show me your products");
        addQuickReply("I need pricing information");
    } catch (error) {
        console.error('Error:', error);
        appendMessage("👋 Welcome to Kye! How can I assist you today?", 'bot');
        addQuickReply("Show me your products");
        addQuickReply("I need pricing information");
    }
});

// Refresh chat
document.getElementById('refresh-chat')?.addEventListener('click', () => {
    chatBody.innerHTML = '';
    quickRepliesContainer.innerHTML = '';
    appendMessage("👋 Welcome to Kye! How can I assist you today?", 'bot');
    addQuickReply("Show me your products");
    addQuickReply("I need pricing information");
});
