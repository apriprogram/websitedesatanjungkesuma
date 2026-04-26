<div class="ai-widget-container" id="aiAssistantWidget">
    {{-- Chat Window --}}
    <div class="ai-chat-window" id="aiChatWindow">
        <div class="ai-chat-inner">
        <header class="ai-chat-header">
            <div class="ai-chat-header-info">
                <div class="ai-header-avatar">
                    <img src="{{ asset('img/Logo/logo_apriprogram.jpg') }}" alt="ApriProgram" class="ai-header-logo">
                </div>
                <div class="ai-chat-title">
                    <h4>Asisten AI Desa</h4>
                    <span class="ai-status-dot">● Online &amp; Terintegrasi</span>
                </div>
            </div>
            <button class="ai-chat-close" id="closeAiChat" title="Tutup Chat">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M14 4L4 14M4 4l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </header>

        <div class="ai-date-divider">
            <span>Hari Ini</span>
        </div>

        <div class="ai-chat-body" id="aiChatBody">
            <div class="message-row message-row--ai">
                <div class="ai-msg-avatar">
                    <img src="{{ asset('img/Logo/logo_apriprogram.jpg') }}" alt="Bot" class="ai-msg-logo">
                </div>
                <div class="message message--ai">
                    Halo! Saya Asisten AI Desa Tanjung Kesuma. Ada yang bisa saya bantu hari ini? 😊
                </div>
            </div>
        </div>

        <div class="ai-quick-replies" id="aiQuickReplies">
            <button class="ai-chip" data-msg="Apa saja agenda desa minggu ini?">Agenda minggu ini</button>
            <button class="ai-chip" data-msg="Bagaimana cara menambah data penduduk?">Tambah penduduk</button>
            <button class="ai-chip" data-msg="Berapa jumlah penduduk saat ini?">Jumlah penduduk</button>
            <button class="ai-chip" data-msg="Cara export data ke Excel?">Export data</button>
        </div>

        <footer class="ai-chat-footer">
            <form id="aiChatForm" class="ai-input-group">
                <button type="button" class="ai-attach-btn" title="Lampiran">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                    </svg>
                </button>
                <input type="text" id="aiChatInput" placeholder="Tanya apa saja..." autocomplete="off">
                <button type="submit" class="ai-send-btn" id="sendAiMsg" title="Kirim Pesan">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
        </footer>
        </div>{{-- /.ai-chat-inner --}}
    </div>

    {{-- Floating Action Button --}}
    <button class="ai-fab" id="toggleAiChat" title="Tanya Asisten AI">
        <img src="{{ asset('img/Logo/logo_apriprogram.jpg') }}" alt="ApriProgram AI" class="ai-fab-logo">
        <span class="ai-badge">AI</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleAiChat');
    const closeBtn = document.getElementById('closeAiChat');
    const chatWindow = document.getElementById('aiChatWindow');
    const chatForm = document.getElementById('aiChatForm');
    const chatInput = document.getElementById('aiChatInput');
    const chatBody = document.getElementById('aiChatBody');
    const sendBtn = document.getElementById('sendAiMsg');
    const quickReplies = document.getElementById('aiQuickReplies');

    let isTyping = false;
    let chatHistory = [];

    const toggleChat = () => {
        chatWindow.classList.toggle('active');
        if (chatWindow.classList.contains('active')) {
            chatInput.focus();
        }
    };

    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    // Quick reply chips
    document.querySelectorAll('.ai-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            chatInput.value = chip.dataset.msg;
            chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
        });
    });

    const makeBotAvatar = () => `
        <div class="ai-msg-avatar">
            <img src="{{ asset('img/Logo/logo_apriprogram.jpg') }}" alt="Bot" class="ai-msg-logo">
        </div>
    `;

    const appendMessage = (role, content) => {
        if (role === 'ai') {
            const row = document.createElement('div');
            row.className = 'message-row message-row--ai';
            row.innerHTML = `${makeBotAvatar()}<div class="message message--ai"></div>`;
            row.querySelector('.message--ai').innerHTML = typeof marked !== 'undefined' ? marked.parse(content) : content;
            chatBody.appendChild(row);
        } else {
            const row = document.createElement('div');
            row.className = 'message-row message-row--user';
            const msgDiv = document.createElement('div');
            msgDiv.className = 'message message--user';
            msgDiv.textContent = content;
            row.appendChild(msgDiv);
            chatBody.appendChild(row);
        }
        chatBody.scrollTop = chatBody.scrollHeight;
    };

    const setTyping = (state) => {
        isTyping = state;
        if (state) {
            const row = document.createElement('div');
            row.className = 'message-row message-row--ai';
            row.id = 'aiTypingIndicator';
            row.innerHTML = `${makeBotAvatar()}
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>`;
            chatBody.appendChild(row);
            chatBody.scrollTop = chatBody.scrollHeight;
            sendBtn.disabled = true;
            chatInput.disabled = true;
        } else {
            const indicator = document.getElementById('aiTypingIndicator');
            if (indicator) indicator.remove();
            sendBtn.disabled = false;
            chatInput.disabled = false;
            chatInput.focus();
        }
    };

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg || isTyping) return;

        chatInput.value = '';
        quickReplies.style.display = 'none';
        appendMessage('user', msg);
        setTyping(true);

        try {
            const response = await fetch('{{ route("admin.ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: msg, history: chatHistory })
            });

            if (response.ok) {
                const data = await response.json();
                setTyping(false);

                if (data.message) {
                    appendMessage('ai', data.message);
                    chatHistory.push({ role: 'user', content: msg });
                    chatHistory.push({ role: 'assistant', content: data.message });
                    if (chatHistory.length > 10) { chatHistory.shift(); chatHistory.shift(); }
                } else if (data.error) {
                    appendMessage('ai', 'Sistem Error: ' + data.error);
                } else {
                    appendMessage('ai', 'Maaf, terjadi gangguan pada layanan AI. Silakan coba beberapa saat lagi.');
                }
            } else {
                setTyping(false);
                const errorText = await response.text().catch(() => 'Unknown error');
                appendMessage('ai', `Gagal menghubungi server (Status: ${response.status}). Hubungi teknisi.`);
                console.error('AI Server Error:', response.status, errorText);
            }
        } catch (err) {
            setTyping(false);
            appendMessage('ai', 'Hubungan terputus. Pastikan koneksi internet Anda stabil.');
            console.error('AI Widget Network Error:', err);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && chatWindow.classList.contains('active')) {
            toggleChat();
        }
    });
});
</script>
