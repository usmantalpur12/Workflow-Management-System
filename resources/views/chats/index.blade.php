@extends('layouts.app')

@section('title', 'Chat System')

@section('content')
<style>
    .chat-container {
        height: calc(100vh - 200px);
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .chat-sidebar {
        width: 300px;
        border-right: 1px solid #dee2e6;
        background: #f8f9fa;
        overflow-y: auto;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        padding: 15px 20px;
        background: #075e54;
        color: white;
        border-bottom: 1px solid #dee2e6;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #e5ddd5;
    }

    .chat-input {
        padding: 15px 20px;
        background: #f0f0f0;
        border-top: 1px solid #dee2e6;
    }

    .user-item {
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: background 0.2s;
    }

    .user-item:hover {
        background: #e9ecef;
    }

    .user-item.active {
        background: #007bff;
        color: white;
    }

    .user-item.active:hover {
        background: #0056b3;
    }

    .message {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-end;
    }

    .message.sent {
        justify-content: flex-end;
    }

    .message.received {
        justify-content: flex-start;
    }

    .message-bubble {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 18px;
        word-wrap: break-word;
    }

    .message.sent .message-bubble {
        background: #dcf8c6;
        color: #000;
        border-bottom-right-radius: 5px;
    }

    .message.received .message-bubble {
        background: white;
        color: #000;
        border-bottom-left-radius: 5px;
    }

    .message-time {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .message.sent .message-time {
        text-align: right;
    }

    .no-chat-selected {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #666;
        font-size: 18px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .user-role {
        font-size: 12px;
        color: #666;
    }

    .user-item.active .user-role {
        color: #ccc;
    }

    .online-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #28a745;
        margin-left: auto;
    }

    .chat-input-container {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .chat-input-container input {
        flex: 1;
        border: none;
        border-radius: 20px;
        padding: 10px 15px;
        outline: none;
    }

    .send-btn {
        background: #007bff;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .send-btn:hover {
        background: #0056b3;
    }

    .send-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
</style>

<div class="chat-container d-flex">
    <!-- Sidebar - Users List -->
    <div class="chat-sidebar">
        <div class="p-3 border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-comments me-2"></i>Chats
            </h5>
        </div>
        <div class="users-list">
            @foreach($users as $user)
                @if($user->id !== auth()->id())
                    <div class="user-item" onclick="selectUser({{ $user->id }}, '{{ $user->name }}')" id="user-{{ $user->id }}">
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}" class="user-avatar">
                            <div class="user-info">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-role">{{ ucfirst(str_replace('-', ' ', $user->role)) }}</div>
                            </div>
                            <div class="online-indicator"></div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <!-- Chat Header -->
        <div class="chat-header" id="chat-header">
            <div class="no-chat-selected">
                <i class="fas fa-comments me-2"></i>Select a user to start chatting
            </div>
        </div>

        <!-- Messages Area -->
        <div class="chat-messages" id="chat-messages">
            <div class="no-chat-selected">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>Select a user to start chatting</p>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="chat-input" id="chat-input" style="display: none;">
            <div class="chat-input-container">
                <input type="text" id="message-input" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
                <button class="send-btn" onclick="sendMessage()" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for sending messages -->
<form id="message-form" action="{{ route('chats.send') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" id="recipient-id" name="recipient_id">
    <input type="hidden" id="message-text" name="message">
</form>
@endsection

@section('scripts')
<script>
    let selectedUserId = null;
    let selectedUserName = null;

    function selectUser(userId, userName) {
        // Remove active class from all users
        document.querySelectorAll('.user-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to selected user
        document.getElementById('user-' + userId).classList.add('active');

        selectedUserId = userId;
        selectedUserName = userName;

        // Update chat header
        document.getElementById('chat-header').innerHTML = `
            <div class="d-flex align-items-center">
                <div class="user-avatar" style="background: #007bff; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                    ${userName.charAt(0).toUpperCase()}
                </div>
                <div>
                    <div class="fw-bold">${userName}</div>
                    <small>Online</small>
                </div>
            </div>
        `;

        // Show chat input
        document.getElementById('chat-input').style.display = 'block';

        // Load messages
        loadMessages(userId);
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
        const messagesContainer = document.getElementById('chat-messages');
        messagesContainer.innerHTML = '';

        messages.forEach(message => {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_id == {{ auth()->id() }} ? 'sent' : 'received'}`;
            
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    <div>${message.message}</div>
                    <div class="message-time">${formatTime(message.created_at)}</div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
        });

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function sendMessage() {
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();

        console.log('Sending message:', message, 'to user:', selectedUserId);

        if (!message || !selectedUserId) {
            console.log('Message or user not selected');
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('recipient_id', selectedUserId);
        formData.append('message', message);
        formData.append('_token', '{{ csrf_token() }}');

        console.log('Form data created, sending request...');

        // Submit form
        fetch('{{ route("chats.send") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                messageInput.value = '';
                loadMessages(selectedUserId);
                console.log('Message sent successfully');
            } else {
                console.error('Error:', data.message);
                alert('Error sending message: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Error sending message: ' + error.message);
        });
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Auto-refresh messages every 5 seconds
    setInterval(() => {
        if (selectedUserId) {
            loadMessages(selectedUserId);
        }
    }, 5000);
</script>
@endsection 