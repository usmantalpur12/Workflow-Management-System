@extends('layouts.app')

@section('title', 'Advanced Chat System')

@section('content')
<style>
    :root {
        --primary-color: #1877f2;
        --secondary-color: #42b883;
        --background-color: #f0f2f5;
        --white: #ffffff;
        --text-primary: #1c1e21;
        --text-secondary: #65676b;
        --border-color: #dadde1;
        --hover-color: #f5f6f7;
        --message-sent: #1877f2;
        --message-received: #e4e6ea;
        --online-color: #31a24c;
        --typing-color: #65676b;
    }

    .chat-app {
        height: calc(100vh - 120px);
        background: var(--background-color);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        background: var(--white);
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .chat-main {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    .chat-sidebar {
        width: 320px;
        background: var(--white);
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--white);
    }

    .search-container {
        position: relative;
        margin-bottom: 16px;
    }

    .search-input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        background: var(--hover-color);
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
    }

    .search-input:focus {
        background: var(--white);
        border-color: var(--primary-color);
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
    }

    .chat-item {
        padding: 12px 20px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }

    .chat-item:hover {
        background: var(--hover-color);
    }

    .chat-item.active {
        background: var(--primary-color);
        color: var(--white);
    }

    .chat-item-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chat-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border-color);
        position: relative;
    }

    .chat-item.active .chat-avatar {
        border-color: var(--white);
    }

    .online-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: var(--online-color);
        border: 2px solid var(--white);
        border-radius: 50%;
    }

    .chat-info {
        flex: 1;
        min-width: 0;
    }

    .chat-name {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-preview {
        font-size: 13px;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-item.active .chat-preview {
        color: rgba(255,255,255,0.8);
    }

    .chat-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .chat-time {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .chat-item.active .chat-time {
        color: rgba(255,255,255,0.8);
    }

    .unread-badge {
        background: var(--primary-color);
        color: var(--white);
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 600;
        min-width: 18px;
        text-align: center;
    }

    .chat-item.active .unread-badge {
        background: var(--white);
        color: var(--primary-color);
    }

    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--white);
    }

    .conversation-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--white);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .conversation-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .conversation-info h6 {
        margin: 0;
        font-weight: 600;
        color: var(--text-primary);
    }

    .conversation-status {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .conversation-actions {
        margin-left: auto;
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: var(--hover-color);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: var(--border-color);
    }

    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        max-width: 70%;
    }

    .message.sent {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message.received {
        align-self: flex-start;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .message-bubble {
        padding: 12px 16px;
        border-radius: 18px;
        position: relative;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message.sent .message-bubble {
        background: var(--message-sent);
        color: var(--white);
        border-bottom-right-radius: 4px;
    }

    .message.received .message-bubble {
        background: var(--message-received);
        color: var(--text-primary);
        border-bottom-left-radius: 4px;
    }

    .message-time {
        font-size: 11px;
        margin-top: 4px;
        opacity: 0.7;
    }

    .message.sent .message-time {
        text-align: right;
    }

    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: var(--message-received);
        border-radius: 18px;
        border-bottom-left-radius: 4px;
        max-width: 80px;
        align-self: flex-start;
    }

    .typing-dots {
        display: flex;
        gap: 3px;
    }

    .typing-dot {
        width: 6px;
        height: 6px;
        background: var(--typing-color);
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typing {
        0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }

    .message-input-container {
        padding: 16px 20px;
        background: var(--white);
        border-top: 1px solid var(--border-color);
    }

    .input-wrapper {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        background: var(--hover-color);
        border-radius: 24px;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        transition: all 0.2s;
    }

    .input-wrapper:focus-within {
        border-color: var(--primary-color);
        background: var(--white);
    }

    .message-input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        padding: 8px 12px;
        font-size: 15px;
        resize: none;
        max-height: 120px;
        min-height: 20px;
    }

    .input-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .input-btn {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: transparent;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .input-btn:hover {
        background: var(--border-color);
    }

    .send-btn {
        background: var(--primary-color);
        color: var(--white);
    }

    .send-btn:hover {
        background: #166fe5;
    }

    .send-btn:disabled {
        background: var(--border-color);
        cursor: not-allowed;
    }

    .emoji-picker {
        position: absolute;
        bottom: 60px;
        right: 20px;
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        display: none;
    }

    .emoji-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 8px;
        max-height: 200px;
        overflow-y: auto;
    }

    .emoji-item {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: background 0.2s;
    }

    .emoji-item:hover {
        background: var(--hover-color);
    }

    .no-chat-selected {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-secondary);
        text-align: center;
    }

    .no-chat-icon {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .file-upload {
        display: none;
    }

    .message-reactions {
        display: flex;
        gap: 4px;
        margin-top: 4px;
        flex-wrap: wrap;
    }

    .reaction {
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2px 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .reaction:hover {
        background: var(--hover-color);
    }

    .reaction.active {
        background: var(--primary-color);
        color: var(--white);
        border-color: var(--primary-color);
    }

    .message-menu {
        position: absolute;
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        display: none;
        min-width: 120px;
    }

    .menu-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }

    .menu-item:hover {
        background: var(--hover-color);
    }

    .menu-item:first-child {
        border-radius: 8px 8px 0 0;
    }

    .menu-item:last-child {
        border-radius: 0 0 8px 8px;
    }

    @media (max-width: 768px) {
        .chat-sidebar {
            width: 100%;
        }
        
        .chat-area {
            display: none;
        }
        
        .chat-area.active {
            display: flex;
        }
    }
</style>

<div class="chat-app">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3">
                <i class="fas fa-comments me-2"></i>Advanced Chat
            </h5>
            <div class="badge bg-primary">Live</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="action-btn" title="Search">
                <i class="fas fa-search"></i>
            </button>
            <button class="action-btn" title="Settings">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>

    <div class="chat-main">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search conversations..." id="searchInput">
                </div>
            </div>
            
            <div class="chat-list" id="chatList">
                @foreach($users as $user)
                    @if($user->id !== auth()->id())
                        <div class="chat-item" onclick="selectChat({{ $user->id }}, '{{ $user->name }}', '{{ $user->profile_picture_url }}')" id="chat-{{ $user->id }}">
                            <div class="chat-item-content">
                                <div class="position-relative">
                                    <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="chat-avatar">
                                    <div class="online-indicator"></div>
                                </div>
                                <div class="chat-info">
                                    <div class="chat-name">{{ $user->name }}</div>
                                    <div class="chat-preview">{{ ucfirst(str_replace('-', ' ', $user->role)) }}</div>
                                </div>
                                <div class="chat-meta">
                                    <div class="chat-time" id="time-{{ $user->id }}">--:--</div>
                                    <div class="unread-badge" id="unread-{{ $user->id }}" style="display: none;">0</div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area" id="chatArea">
            <div class="no-chat-selected">
                <i class="fas fa-comments no-chat-icon"></i>
                <h4>Select a conversation</h4>
                <p>Choose a user from the sidebar to start chatting</p>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Elements -->
<input type="file" class="file-upload" id="fileUpload" accept="image/*,video/*,.pdf,.doc,.docx">
<input type="file" class="file-upload" id="imageUpload" accept="image/*">

<!-- Emoji Picker -->
<div class="emoji-picker" id="emojiPicker">
    <div class="emoji-grid" id="emojiGrid">
        <!-- Emojis will be populated by JavaScript -->
    </div>
</div>

<!-- Message Context Menu -->
<div class="message-menu" id="messageMenu">
    <div class="menu-item" onclick="reactToMessage('👍')">
        <i class="fas fa-thumbs-up me-2"></i>Like
    </div>
    <div class="menu-item" onclick="reactToMessage('❤️')">
        <i class="fas fa-heart me-2"></i>Love
    </div>
    <div class="menu-item" onclick="reactToMessage('😂')">
        <i class="fas fa-laugh me-2"></i>Laugh
    </div>
    <div class="menu-item" onclick="copyMessage()">
        <i class="fas fa-copy me-2"></i>Copy
    </div>
    <div class="menu-item" onclick="deleteMessage()">
        <i class="fas fa-trash me-2"></i>Delete
    </div>
</div>

@endsection

@section('scripts')
<script>
    let currentChatId = null;
    let currentChatName = null;
    let currentChatAvatar = null;
    let typingTimer = null;
    let isTyping = false;
    let messageMenuVisible = false;

    // Emoji data
    const emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'];

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initializeEmojiPicker();
        setupEventListeners();
        startPolling();
    });

    function initializeEmojiPicker() {
        const emojiGrid = document.getElementById('emojiGrid');
        emojis.forEach(emoji => {
            const emojiBtn = document.createElement('button');
            emojiBtn.className = 'emoji-item';
            emojiBtn.textContent = emoji;
            emojiBtn.onclick = () => insertEmoji(emoji);
            emojiGrid.appendChild(emojiBtn);
        });
    }

    function setupEventListeners() {
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const chatItems = document.querySelectorAll('.chat-item');
            
            chatItems.forEach(item => {
                const name = item.querySelector('.chat-name').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // File upload
        document.getElementById('fileUpload').addEventListener('change', handleFileUpload);
        document.getElementById('imageUpload').addEventListener('change', handleImageUpload);

        // Click outside to close menus
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.emoji-picker') && !e.target.closest('.emoji-btn')) {
                document.getElementById('emojiPicker').style.display = 'none';
            }
            
            if (!e.target.closest('.message-menu') && !e.target.closest('.message-bubble')) {
                document.getElementById('messageMenu').style.display = 'none';
                messageMenuVisible = false;
            }
        });
    }

    function selectChat(userId, userName, userAvatar) {
        // Update active chat
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });
        document.getElementById('chat-' + userId).classList.add('active');

        currentChatId = userId;
        currentChatName = userName;
        currentChatAvatar = userAvatar;

        // Update chat area
        updateChatArea();
        loadMessages(userId);
    }

    function updateChatArea() {
        const chatArea = document.getElementById('chatArea');
        chatArea.innerHTML = `
            <div class="conversation-header">
                <img src="${currentChatAvatar}" alt="${currentChatName}" class="conversation-avatar">
                <div class="conversation-info">
                    <h6>${currentChatName}</h6>
                    <div class="conversation-status">Online</div>
                </div>
                <div class="conversation-actions">
                    <button class="action-btn" title="Voice Call">
                        <i class="fas fa-phone"></i>
                    </button>
                    <button class="action-btn" title="Video Call">
                        <i class="fas fa-video"></i>
                    </button>
                    <button class="action-btn" title="More">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            
            <div class="messages-container" id="messagesContainer">
                <!-- Messages will be loaded here -->
            </div>
            
            <div class="message-input-container">
                <div class="input-wrapper">
                    <button class="input-btn" onclick="document.getElementById('imageUpload').click()" title="Send Image">
                        <i class="fas fa-image"></i>
                    </button>
                    <button class="input-btn" onclick="document.getElementById('fileUpload').click()" title="Send File">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <textarea class="message-input" id="messageInput" placeholder="Type a message..." rows="1" onkeydown="handleKeyDown(event)" oninput="handleTyping()"></textarea>
                    <button class="input-btn emoji-btn" onclick="toggleEmojiPicker()" title="Emoji">
                        <i class="fas fa-smile"></i>
                    </button>
                    <button class="input-btn send-btn" onclick="sendMessage()" id="sendBtn" title="Send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;

        // Auto-resize textarea
        const messageInput = document.getElementById('messageInput');
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    function loadMessages(userId) {
        fetch(`/chats/messages/${userId}`)
            .then(response => response.json())
            .then(data => {
                displayMessages(data);
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function displayMessages(messages) {
        const container = document.getElementById('messagesContainer');
        container.innerHTML = '';

        messages.forEach(message => {
            const messageDiv = createMessageElement(message);
            container.appendChild(messageDiv);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    function createMessageElement(message) {
        const isSent = message.sender_id == {{ auth()->id() }};
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
        messageDiv.setAttribute('data-message-id', message.id);

        messageDiv.innerHTML = `
            <img src="${isSent ? '{{ auth()->user()->profile_picture_url }}' : currentChatAvatar}" alt="" class="message-avatar">
            <div class="message-bubble" oncontextmenu="showMessageMenu(event, ${message.id})">
                <div>${formatMessage(message.message)}</div>
                <div class="message-time">${formatTime(message.created_at)}</div>
                ${message.reactions ? `<div class="message-reactions">${formatReactions(message.reactions)}</div>` : ''}
            </div>
        `;

        return messageDiv;
    }

    function formatMessage(message) {
        // Convert URLs to links
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        return message.replace(urlRegex, '<a href="$1" target="_blank" style="color: inherit; text-decoration: underline;">$1</a>');
    }

    function formatReactions(reactions) {
        const reactionCounts = {};
        reactions.forEach(reaction => {
            reactionCounts[reaction.emoji] = (reactionCounts[reaction.emoji] || 0) + 1;
        });

        return Object.entries(reactionCounts)
            .map(([emoji, count]) => `<span class="reaction">${emoji} ${count}</span>`)
            .join('');
    }

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();

        if (!message || !currentChatId) return;

        const formData = new FormData();
        formData.append('recipient_id', currentChatId);
        formData.append('message', message);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("chats.send") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                loadMessages(currentChatId);
                updateLastMessage(currentChatId, message);
            } else {
                alert('Error sending message: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Error sending message: ' + error.message);
        });
    }

    function handleKeyDown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    }

    function handleTyping() {
        if (!isTyping && currentChatId) {
            isTyping = true;
            // Send typing indicator
            fetch('/chats/typing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    recipient_id: currentChatId,
                    is_typing: true
                })
            });
        }

        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            if (isTyping && currentChatId) {
                isTyping = false;
                // Send stop typing indicator
                fetch('/chats/typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        recipient_id: currentChatId,
                        is_typing: false
                    })
                });
            }
        }, 1000);
    }

    function toggleEmojiPicker() {
        const picker = document.getElementById('emojiPicker');
        picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
    }

    function insertEmoji(emoji) {
        const messageInput = document.getElementById('messageInput');
        const cursorPos = messageInput.selectionStart;
        const textBefore = messageInput.value.substring(0, cursorPos);
        const textAfter = messageInput.value.substring(messageInput.selectionEnd);
        
        messageInput.value = textBefore + emoji + textAfter;
        messageInput.selectionStart = messageInput.selectionEnd = cursorPos + emoji.length;
        messageInput.focus();
        
        document.getElementById('emojiPicker').style.display = 'none';
    }

    function handleFileUpload(event) {
        const file = event.target.files[0];
        if (file) {
            // Handle file upload
            console.log('File selected:', file.name);
            // Implement file upload logic here
        }
    }

    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (file) {
            // Handle image upload
            console.log('Image selected:', file.name);
            // Implement image upload logic here
        }
    }

    function showMessageMenu(event, messageId) {
        event.preventDefault();
        
        const menu = document.getElementById('messageMenu');
        menu.style.display = 'block';
        menu.style.left = event.pageX + 'px';
        menu.style.top = event.pageY + 'px';
        menu.setAttribute('data-message-id', messageId);
        
        messageMenuVisible = true;
    }

    function reactToMessage(emoji) {
        const messageId = document.getElementById('messageMenu').getAttribute('data-message-id');
        // Implement reaction logic
        console.log('Reacting with', emoji, 'to message', messageId);
        document.getElementById('messageMenu').style.display = 'none';
    }

    function copyMessage() {
        const messageId = document.getElementById('messageMenu').getAttribute('data-message-id');
        const messageElement = document.querySelector(`[data-message-id="${messageId}"] .message-bubble div`);
        if (messageElement) {
            navigator.clipboard.writeText(messageElement.textContent);
        }
        document.getElementById('messageMenu').style.display = 'none';
    }

    function deleteMessage() {
        const messageId = document.getElementById('messageMenu').getAttribute('data-message-id');
        if (confirm('Are you sure you want to delete this message?')) {
            // Implement delete logic
            console.log('Deleting message', messageId);
        }
        document.getElementById('messageMenu').style.display = 'none';
    }

    function updateLastMessage(chatId, message) {
        const chatItem = document.getElementById('chat-' + chatId);
        if (chatItem) {
            const preview = chatItem.querySelector('.chat-preview');
            const time = chatItem.querySelector('.chat-time');
            
            preview.textContent = message.length > 30 ? message.substring(0, 30) + '...' : message;
            time.textContent = formatTime(new Date().toISOString());
        }
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) { // Less than 1 minute
            return 'Just now';
        } else if (diff < 3600000) { // Less than 1 hour
            return Math.floor(diff / 60000) + 'm';
        } else if (diff < 86400000) { // Less than 1 day
            return Math.floor(diff / 3600000) + 'h';
        } else {
            return date.toLocaleDateString();
        }
    }

    function startPolling() {
        // Poll for new messages every 3 seconds
        setInterval(() => {
            if (currentChatId) {
                loadMessages(currentChatId);
            }
        }, 3000);
    }
</script>
@endsection
