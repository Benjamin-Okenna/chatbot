// ==========================================================================
// 1. GLOBAL STATE TRACKER VARIABLES (Keep at the very top)
// ==========================================================================
let currentConversationId = 0; // Starts at 0, meaning "New Conversation Session"

// Initialize workspace setups immediately after the HTML layout mounts
document.addEventListener("DOMContentLoaded", () => {
    // Populate your sidebar panel with real historical session database records
    loadChatHistory();

    // Attach click listener configuration to your "+ New Chat" action button
    const newChatBtn = document.getElementById("new-chat-btn");
    if (newChatBtn) {
        newChatBtn.addEventListener("click", startNewChatWindow);
    }
});

// ==========================================================================
// 2. CORE INTERACTION: SEND MESSAGE PIPELINE
// ==========================================================================
async function sendMessage() {
    const inputField = document.getElementById('user-input');
    const welcomeScreen = document.getElementById('welcome-screen');
    const messageText = inputField.value.trim();

    // If input is empty whitespace, abort processing
    if (messageText === '') return;

    // A. Hide welcome panel suggestion cards using your HTML 'hidden' display logic
    if (welcomeScreen) {
        welcomeScreen.style.display = "none";
    }

    // B. Render the student message bubble onto the screen
    appendMessage(messageText, 'user');
    
    // Clear input bar immediately for rapid continuous typing workflow
    inputField.value = ''; 

    // C. Fire up the thinking/loading indicator bubble layout state
    const typingIndicator = showTypingIndicator();

    try {
        // D. Route message string through your live PHP proxy controller script
        const botReply = await simulateNetworkResponse(messageText);
        
        // Remove typing indicator once backend transmission delivers results
        removeTypingIndicator(typingIndicator);

        // E. Render live AI text reply bubble inside feed box layout
        appendMessage(botReply, 'bot');

        // FIXED INTEGRATION BRIDGE: 
        // Force the sidebar history list view layout to reload right now 
        // so fresh conversation records appear without requiring a browser refresh!
        loadChatHistory();

    } catch (error) {
        removeTypingIndicator(typingIndicator);
        appendMessage("System Error: Unable to process message transaction workflow.", 'error');
    }
}

// ==========================================================================
// 3. STORAGE CONNECTORS: ASYNCHRONOUS BACKEND HANDSHAKES (fetch)
// ==========================================================================
async function simulateNetworkResponse(userText) {
    const response = await fetch('chat_processor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            message: userText,
            conversation_id: currentConversationId 
        })
    });

    if (!response.ok) {
        throw new Error('Server responded with a status error.');
    }

    const data = await response.json();

    if (data.status === 'success') {
        // Lock the generated session token integer ID returned from MySQL engine
        currentConversationId = data.conversation_id; 
        return data.reply; 
    } else {
        return "Error: " + data.reply;
    }
}

// FETCH SYSTEM SESSIONS: Read conversation tables and map lists into sidebar container
async function loadChatHistory() {
    const historyList = document.getElementById("history-list");
    if (!historyList) return;

    try {
        const response = await fetch('get_history.php');
        const data = await response.json();

        if (data.status === 'success') {
            historyList.innerHTML = ''; // Clear hardcoded mock markup items

            if (data.conversations.length === 0) {
                historyList.innerHTML = '<li class="history-item" style="color: #8e8ea0; pointer-events: none; list-style:none; font-size:13px; padding:10px;">No past chats yet</li>';
                return;
            }

            // Dynamically construct structural li elements maps row items data
            data.conversations.forEach(chat => {
                const li = document.createElement("li");
                
                // Keep your precise CSS design styling rules classes intact!
                li.className = "history-item";
                if (chat.id === currentConversationId) {
                    li.classList.add("active"); // Appends conditional theme coloring properties definitions
                }
                
                li.textContent = chat.title;
                li.style.cursor = "pointer";

                // Bind custom execution pointer tracking listener handler elements
                li.addEventListener("click", () => {
                    loadSpecificChatMessages(chat.id);
                });

                historyList.appendChild(li);
            });
        }
    } catch (error) {
        console.error("Error loading chat sessions history logs:", error);
    }
}

// PULL SELECTION TEXT FIELDS: Fetch full message strings arrays matched to clicked row token ID
async function loadSpecificChatMessages(conversationId) {
    currentConversationId = conversationId;
    loadChatHistory(); // Re-render selections configurations layouts highlights properties balances

    const chatBox = document.getElementById('chat-box');
    const welcomeScreen = document.getElementById('welcome-screen');
    
    if (!chatBox) return;

    if (welcomeScreen) {
        welcomeScreen.style.display = "none";
    }

    try {
        const response = await fetch(`get_messages.php?conversation_id=${conversationId}`);
        const data = await response.json();

        if (data.status === 'success') {
            chatBox.innerHTML = '<div class="chat-divider">Conversation History Log</div>';

            data.messages.forEach(msg => {
                const wrapper = document.createElement("div");
                wrapper.className = `message-wrapper ${msg.sender}`;

                wrapper.innerHTML = `
                    <div class="message-bubble">
                        ${msg.message_text}
                    </div>
                    <span class="message-time">Saved</span>
                `;
                chatBox.appendChild(wrapper);
            });

            chatBox.scrollTop = chatBox.scrollHeight;
        }
    } catch (error) {
        console.error("Critical error reading backend conversation rows:", error);
    }
}

// ==========================================================================
// 4. WORKSPACE CONTROLLERS: STRUCTURAL LAYOUT HANDLERS
// ==========================================================================
function startNewChatWindow() {
    currentConversationId = 0; // Setting to zero flags PHP engine pipelines to create a clean chat row on next query 
    
    const chatBox = document.getElementById('chat-box');
    if (chatBox) {
        chatBox.innerHTML = `
            <div class="chat-divider">Today</div>
            <div class="message-wrapper bot">
                <div class="message-bubble">
                    Hello! I am your AI Academic Advisor. Feel free to ask me anything about your portal configuration or registration tracks.
                </div>
                <span class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            </div>
        `;
    }

    const welcomeScreen = document.getElementById("welcome-screen");
    if (welcomeScreen) {
        welcomeScreen.style.display = "block"; // Restore center suggestion cards layout panel array map view
    }

    loadChatHistory();
}

// UTILITY UI RECTIFIERS: Append text boxes configurations layouts structures blocks
function appendMessage(text, sender) {
    const chatBox = document.getElementById('chat-box');
    if (!chatBox) return;
    
    const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const wrapper = document.createElement('div');
    wrapper.className = `message-wrapper ${sender}`;

    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.innerText = text;

    const timeSpan = document.createElement('span');
    timeSpan.className = 'message-time';
    timeSpan.innerText = currentTime;

    wrapper.appendChild(bubble);
    wrapper.appendChild(timeSpan);
    chatBox.appendChild(wrapper);

    chatBox.scrollTop = chatBox.scrollHeight;
}

function setPresetQuery(phrase) {
    const inputField = document.getElementById('user-input');
    if (inputField) {
        inputField.value = phrase;
        sendMessage();
    }
}

function showTypingIndicator() {
    const chatBox = document.getElementById('chat-box');
    const indicator = document.createElement('div');
    indicator.className = 'message-wrapper bot dynamic-loading';
    indicator.innerHTML = `
        <div class="message-bubble" style="font-style: italic; color: #949ba4;">
            EspolyBot is thinking...
        </div>`;
    if (chatBox) {
        chatBox.appendChild(indicator);
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    return indicator;
}

function removeTypingIndicator(indicatorElement) {
    if (indicatorElement) {
        indicatorElement.remove();
    }
}

// Keyboard input capture action loop engine configuration triggers mappings
document.getElementById('user-input').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
});