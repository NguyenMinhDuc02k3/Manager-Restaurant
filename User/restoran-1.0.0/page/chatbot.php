<?php
// Thiết lập mã hóa UTF-8
header('Content-Type: text/html; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="chat-container" id="chatContainer">
    <div class="chat-header">
        <h5>Hỗ trợ khách hàng</h5>
        <button class="chat-close" onclick="toggleChat()">×</button>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <div class="message bot-message">
            Xin chào! Tôi có thể giúp gì cho bạn?
            Bạn có thể hỏi về:
            - Menu và món ăn
            - Đặt bàn
            - Khuyến mãi
            - Giờ mở cửa
            - Thông tin liên hệ
        </div>
    </div>
    
    <div class="chat-input">
        <input type="text" id="userMessage" placeholder="Nhập tin nhắn..." onkeypress="handleKeyPress(event)">
        <button onclick="sendMessage()">Gửi</button>
    </div>
</div>

<button class="chat-toggle" onclick="toggleChat()">
    <i class="fas fa-comments"></i>
</button>

<style>
.chat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 350px;
    height: 500px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    display: none;
    flex-direction: column;
    z-index: 1000;
}

.chat-header {
    padding: 15px;
    background: #FEA116;
    color: white;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.message {
    margin: 5px 0;
    padding: 10px 15px;
    border-radius: 15px;
    max-width: 80%;
    word-wrap: break-word;
}

.user-message {
    background: #FEA116;
    color: white;
    margin-left: auto;
}

.bot-message {
    background: #f1f1f1;
    margin-right: auto;
}

.chat-input {
    padding: 15px;
    border-top: 1px solid #ddd;
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    outline: none;
}

.chat-input button {
    padding: 10px 20px;
    background: #FEA116;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.chat-toggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 30px;
    background: #FEA116;
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    z-index: 999;
}

.chat-toggle i {
    font-size: 24px;
}

.typing-indicator {
    display: block;
    padding: 10px 15px;
    background-color: #f1f1f1;
    border-radius: 20px;
    margin-bottom: 15px;
    width: fit-content;
}

.typing-indicator span {
    height: 8px;
    width: 8px;
    float: left;
    margin: 0 1px;
    background-color: #9E9EA1;
    display: block;
    border-radius: 50%;
    opacity: 0.4;
}

.typing-indicator span:nth-of-type(1) {
    animation: 1s blink infinite 0.3333s;
}

.typing-indicator span:nth-of-type(2) {
    animation: 1s blink infinite 0.6666s;
}

.typing-indicator span:nth-of-type(3) {
    animation: 1s blink infinite 0.9999s;
}

@keyframes blink {
    50% {
        opacity: 1;
    }
}
</style>

<script>
// Danh sách câu trả lời cố định để sử dụng khi không kết nối được server
const fixedResponses = {
    "nhà hàng mở cửa mấy giờ": "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "giờ mở cửa": "Nhà hàng mở cửa từ 8:00 - 22:00 mỗi ngày, phục vụ cả ngày không nghỉ trưa. 🕒",
    "địa chỉ": "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM. Rất hân hạnh được đón tiếp quý khách! 📍",
    "ở đâu": "Nhà hàng nằm tại 123 ABC Street, Quận 1, TP.HCM. Rất hân hạnh được đón tiếp quý khách! 📍",
    "liên hệ": "Quý khách có thể liên hệ với chúng tôi qua số điện thoại 0123456789 hoặc email info@restaurant.com. 📞",
    "món ăn": "Nhà hàng có đa dạng món ăn từ khai vị, món chính đến tráng miệng. Các món nổi bật: Vịt quay Bắc Kinh (200,000đ), Cơm chiên hải sản (100,000đ), Bò lúc lắc (95,000đ). 🍽️",
    "khuyến mãi": "Hiện tại chúng tôi có khuyến mãi giảm 20,000đ cho mọi hóa đơn và giảm 5% cho hóa đơn tháng 5. Hạn đến ngày 31/05/2025. 🎉",
    "đặt bàn": "Quý khách có thể đặt bàn trước qua số điện thoại 0123456789 hoặc đặt trực tiếp trên website của nhà hàng. 📅"
};

function toggleChat() {
    const container = document.getElementById('chatContainer');
    const button = document.querySelector('.chat-toggle');
    if (container.style.display === 'none' || !container.style.display) {
        container.style.display = 'flex';
        button.style.display = 'none';
    } else {
        container.style.display = 'none';
        button.style.display = 'block';
    }
}

function handleKeyPress(event) {
    if (event.keyCode === 13) {
        sendMessage();
    }
}

// Hàm tìm câu trả lời từ danh sách cố định
function findLocalResponse(message) {
    message = message.toLowerCase();
    for (const keyword in fixedResponses) {
        if (message.includes(keyword)) {
            return fixedResponses[keyword];
        }
    }
    return null;
}

function sendMessage() {
    const input = document.getElementById('userMessage');
    const message = input.value.trim();
    
    if (message === '') return;
    
    // Hiển thị tin nhắn của user
    addMessage(message, 'user');
    
    // Clear input
    input.value = '';
    
    // Hiển thị typing indicator
    const messagesDiv = document.getElementById('chatMessages');
    const typingIndicator = document.createElement('div');
    typingIndicator.classList.add('typing-indicator');
    typingIndicator.id = 'typingIndicator';
    typingIndicator.innerHTML = '<span></span><span></span><span></span>';
    messagesDiv.appendChild(typingIndicator);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    // Xác định đường dẫn API
    let apiUrl = 'api/direct_chat.php'; // Thử đường dẫn tương đối trước

    // Nếu đường dẫn tương đối không hoạt động, thử đường dẫn tuyệt đối
    if (window.location.pathname.includes('/CNM/')) {
        apiUrl = window.location.origin + '/CNM/User/restoran-1.0.0/api/direct_chat.php';
    } else if (window.location.pathname.includes('/User/')) {
        apiUrl = window.location.origin + '/User/restoran-1.0.0/api/direct_chat.php';
    }

    console.log('Sending request to:', apiUrl);
    
    // Gửi request đến API
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            message: message
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        // Xóa typing indicator
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
        
        // Hiển thị phản hồi từ chatbot
        if (data.status === 'success') {
            addMessage(data.message, 'bot');
        } else {
            addMessage('Xin lỗi, có lỗi xảy ra khi xử lý yêu cầu của bạn.', 'bot');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Xóa typing indicator
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
        
        // Tìm câu trả lời từ danh sách cố định
        const localResponse = findLocalResponse(message);
        if (localResponse) {
            addMessage(localResponse, 'bot');
        } else {
            // Hiển thị thông báo lỗi
            addMessage('Xin lỗi, không thể kết nối đến server chatbot. Vui lòng thử lại sau.', 'bot');
        }
    });
}

function addMessage(message, sender) {
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', `${sender}-message`);
    
    // Xử lý emoji và định dạng
    message = message.replace(/\n/g, '<br>');
    
    messageDiv.innerHTML = message;
    messagesDiv.appendChild(messageDiv);
    
    // Cuộn xuống tin nhắn mới nhất
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}
</script>