<style>
    @media (min-width: 769px) {
        .fab-container {
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
            align-items: center !important;
        }
        .fab-bottom-row {
            justify-content: center !important;
        }

        /* Standardized Windows (Agenda & AI) */
        .fab-card, .ai-chat-window {
            position: absolute !important;
            left: 50% !important;
            right: auto !important;
            top: auto !important;
            bottom: calc(100% + 1.25rem) !important;
            width: 850px !important;
            max-width: 95vw !important;
            transform: translateX(-50%) translateY(20px) scale(0.95) !important;
            opacity: 0;
            visibility: hidden;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
            transform-origin: bottom center !important;
            z-index: 9999 !important;
        }

        .fab-card.active, .ai-chat-window.active {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateX(-50%) translateY(0) scale(1) !important;
        }
    }

    /* Shrink the widgets */
    .fab-button, .ai-fab {
        width: 50px !important;
        height: 50px !important;
    }
    .fab-button i {
        font-size: 1.1rem !important;
    }
    .ai-fab-logo {
        width: 32px !important;
        height: 32px !important;
    }
    .ai-badge {
        font-size: 8px !important;
        padding: 1px 4px !important;
    }
    .fab-clock {
        padding: 0.4rem 0.8rem !important;
        font-size: 0.85rem !important;
    }
    .fab-clock-time {
        font-size: 0.85rem !important;
    }
    .fab-clock-date {
        font-size: 0.7rem !important;
    }
    
    /* Restore card sizes to be larger like before */
    .fab-card {
        width: 850px !important;
    }
    .fab-body {
        max-height: 550px !important;
    }
    .ai-chat-window {
        width: 850px !important;
    }
    .ai-chat-inner {
        height: 700px !important;
    }
    .fab-header, .ai-chat-header {
        padding: 1.5rem 1.75rem !important;
    }
    .fab-title, .ai-chat-title h4 {
        font-size: 1.1rem !important;
    }
    /* Modal Detail Redesign (Clean & Stable) */
    .fab-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        background: rgba(15, 23, 42, 0.5) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 999999 !important;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .fab-modal-backdrop.active {
        opacity: 1 !important;
        visibility: visible !important;
    }
    .fab-modal {
        background: white !important;
        width: 650px !important;
        max-width: 90vw !important;
        border-radius: 24px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
        overflow: hidden !important;
        transform: scale(0.95) translateY(10px) !important;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
    }
    .fab-modal-backdrop.active .fab-modal {
        transform: scale(1) translateY(0) !important;
    }
    .fab-modal-header {
        padding: 1.5rem 2rem !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }
    .fab-modal-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        margin: 0 !important;
    }
    .fab-modal-close {
        background: #f1f5f9 !important;
        border: none !important;
        color: #64748b !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        display: grid !important;
        place-items: center !important;
        cursor: pointer !important;
        transition: all 0.2s !important;
    }
    .fab-modal-close:hover {
        background: #fee2e2 !important;
        color: #ef4444 !important;
    }
    .fab-modal-body {
        padding: 2rem !important;
    }
    .fab-modal-section {
        margin-bottom: 1.5rem !important;
    }
    .fab-detail-label {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: var(--primary-color) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }
    .fab-detail-value {
        font-size: 1.15rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        line-height: 1.4 !important;
    }
    .fab-modal-desc-box {
        background: #f8fafc !important;
        padding: 1.25rem !important;
        border-radius: 12px !important;
        font-size: 0.95rem !important;
        line-height: 1.6 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        margin-top: 0.5rem !important;
    }
    .fab-modal-meta-row {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 1.5rem !important;
        margin-top: 2rem !important;
        padding-top: 1.5rem !important;
        border-top: 1px dashed #e2e8f0 !important;
    }
    .fab-meta-item {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.25rem !important;
    }
    .fab-meta-label {
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        text-transform: uppercase !important;
    }
    .fab-meta-value {
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
    }

    /* Dark Mode Support */
    body.dark-mode .fab-modal { background: #1e293b !important; border-color: #334155 !important; }
    body.dark-mode .fab-modal-header { background: #111827 !important; border-color: #334155 !important; }
    body.dark-mode .fab-modal-title { color: #f8fafc !important; }
    body.dark-mode .fab-modal-close { background: #334155 !important; color: #94a3b8 !important; }
    body.dark-mode .fab-detail-value { color: #f1f5f9 !important; }
    body.dark-mode .fab-modal-desc-box { background: #0f172a !important; border-color: #334155 !important; color: #cbd5e1 !important; }
    body.dark-mode .fab-modal-meta-row { border-color: #334155 !important; }
    body.dark-mode .fab-meta-value { color: #e2e8f0 !important; }

    /* ===== MOBILE RESPONSIVE ===== */
    @media (max-width: 768px) {
        /* AI Chat Window full-screen on mobile */
        .ai-chat-window {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100dvh !important;
            max-width: 100vw !important;
            bottom: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            transform: translateY(100%) !important;
            border-radius: 0 !important;
            z-index: 9999 !important;
            opacity: 1 !important;
            visibility: hidden !important;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s !important;
        }

        .ai-chat-window.active {
            transform: translateY(0) !important;
            visibility: visible !important;
        }

        .ai-chat-inner {
            height: 100dvh !important;
            height: 100vh !important;
            border-radius: 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .ai-chat-header {
            padding: 1rem 1.25rem !important;
            flex-shrink: 0 !important;
        }

        .ai-chat-title h4 {
            font-size: 1rem !important;
        }

        .ai-chat-body {
            flex: 1 !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 1rem !important;
            font-size: 0.9rem !important;
        }

        .ai-quick-replies {
            flex-shrink: 0 !important;
            padding: 0.5rem 1rem !important;
            overflow-x: auto !important;
            white-space: nowrap !important;
        }

        .ai-input-row {
            flex-shrink: 0 !important;
            padding: 0.75rem 1rem !important;
            gap: 0.5rem !important;
        }

        .ai-input-row input,
        .ai-input-row textarea {
            font-size: 0.95rem !important;
        }

        /* Agenda Card full-screen on mobile */
        .fab-card {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            max-width: 100vw !important;
            height: 100dvh !important;
            bottom: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            transform: translateY(100%) !important;
            border-radius: 0 !important;
            z-index: 9999 !important;
            opacity: 1 !important;
            visibility: hidden !important;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s !important;
        }

        .fab-card.active {
            transform: translateY(0) !important;
            visibility: visible !important;
        }

        .fab-inner-wrap {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
        }

        .fab-body {
            flex: 1 !important;
            overflow-y: auto !important;
            max-height: none !important;
            -webkit-overflow-scrolling: touch !important;
        }

        /* Smaller FAB bottom bar on mobile */
        .fab-container {
            bottom: 0.75rem !important;
            gap: 0.5rem !important;
        }

        .fab-clock {
            font-size: 0.75rem !important;
            padding: 0.3rem 0.6rem !important;
        }
    }
</style>
<div class="fab-container">
    <!-- Floating Card -->
    <div class="fab-card" id="fabAgendaCard">
        <div class="fab-inner-wrap">
            <div class="fab-header">
                <div>
                    <div class="fab-badge">Agenda Terbaru</div>
                    <h4 class="fab-title">Pengingat Desa</h4>
                    <p class="fab-subtitle">Monitor kegiatan yang akan datang.</p>
                </div>
                <button type="button" style="background:none; border:none; color:#9ca3af; cursor:pointer;"
                    onclick="toggleFab()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="fab-body">
                @forelse($widgetAgendas as $agenda)
                    <div class="fab-list-item" data-agenda-id="{{ $agenda->id }}"
                        data-agenda-title="{{ e($agenda->title) }}"
                        data-agenda-description="{{ e(strip_tags(html_entity_decode($agenda->description))) ?: 'Tidak ada deskripsi.' }}"
                        data-agenda-due="{{ $agenda->due_date?->translatedFormat('d F Y') ?? '-' }}"
                        data-agenda-priority="{{ $agenda->priority }}"
                        data-agenda-is-completed="{{ $agenda->is_completed ? 'true' : 'false' }}"
                        data-toggle-url="{{ route('admin.agendas.toggle', $agenda) }}"
                        onclick="showAgendaDetail(this)">
                        @php
                            $priorityColor = match ($agenda->priority) {
                                'high' => 'var(--danger-color)',
                                'medium' => 'var(--warning-color)',
                                default => 'var(--info-color)',
                            };

                            $iconClass = match ($agenda->priority) {
                                'high' => 'fa-exclamation',
                                'medium' => 'fa-clock',
                                default => 'fa-calendar-day',
                            };

                            if ($agenda->is_completed) {
                                $priorityColor = 'var(--secondary-color)';
                                $iconClass = 'fa-check';
                            }
                        @endphp

                        <div class="fab-icon" style="background-color: {{ $priorityColor }}; color: #ffffff;">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>

                        <div style="flex:1;">
                            <div class="fab-title-click"
                                style="font-size:0.9rem; font-weight:600; line-height:1.4;">
                                {{ $agenda->title }}
                            </div>
                            <div class="fab-subtitle-wrap" style="font-size:0.75rem; margin-top:2px; font-weight:500;">
                                {{ $agenda->due_date?->translatedFormat('d F Y') ?? 'N/A' }}
                                @if(!$agenda->is_completed && $agenda->priority == 'high')
                                    <span class="fab-item-urgent">Penting</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding:1.5rem; text-align:center; color:var(--text-secondary); font-size:0.875rem;">
                        Tidak ada agenda saat ini.
                    </div>
                @endforelse
            </div>
            <div class="fab-footer">
                <a href="{{ route('admin.agendas.index') }}" class="fab-btn-full">
                    Kelola Agenda <i class="fas fa-arrow-right" style="margin-left:0.5rem; font-size:0.75rem;"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- AI Widget Chat Window -->
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
            <div class="ai-date-divider"><span>Hari Ini</span></div>
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
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </footer>
        </div>
    </div>



    <!-- Bottom Area: Clock + AI Button + Agenda Button -->
    <div class="fab-bottom-row">
        <!-- Clock -->
        <div class="fab-clock" id="fabClockInfo">
            <i class="fas fa-clock"></i>
            <span class="fab-clock-time" id="fabTimeDisplay">--:--:--</span>
            <span class="fab-clock-date" id="fabDateDisplay">--------</span>
        </div>

        <!-- AI Widget FAB — inline dengan agenda button -->
        <div class="ai-fab-inline-wrap" id="aiWidgetInline">
            <!-- AI FAB Button -->
            <button class="ai-fab" id="toggleAiChat" title="Tanya Asisten AI">
                <img src="{{ asset('img/Logo/logo_apriprogram.jpg') }}" alt="ApriProgram AI" class="ai-fab-logo">
                <span class="ai-badge">AI</span>
            </button>
        </div>

        <!-- Agenda Floating Button -->
        <button class="fab-button" onclick="toggleFab()" aria-label="Lihat Agenda">
            <i class="fas fa-calendar-alt"></i>
        </button>
    </div>
</div>

<!-- Modal Detail Widget (Outside container for perfect centering) -->
<div class="fab-modal-backdrop" id="fabAgendaModalBackdrop" onclick="if(event.target === this) closeFabModal()">
    <div class="fab-modal">
        <header class="fab-modal-header">
            <h4 class="fab-modal-title">Detail Agenda</h4>
            <button type="button" class="fab-modal-close" onclick="closeFabModal()">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="fab-modal-body">
            <div class="fab-modal-section">
                <div class="fab-detail-label">
                    <i class="fas fa-bookmark"></i> NAMA KEGIATAN
                </div>
                <div class="fab-detail-value" id="fabModalTitle"></div>
            </div>

            <div class="fab-modal-section">
                <div class="fab-detail-label">
                    <i class="fas fa-align-left"></i> DESKRIPSI LENGKAP
                </div>
                <div class="fab-modal-desc-box" id="fabModalDesc"></div>
            </div>

            <div class="fab-modal-meta-row">
                <div class="fab-meta-item">
                    <div class="fab-meta-label">WAKTU PELAKSANAAN</div>
                    <div class="fab-meta-value" id="fabModalDue"></div>
                </div>
                <div class="fab-meta-item">
                    <div class="fab-meta-label">TINGKAT PRIORITAS</div>
                    <div class="fab-meta-value" id="fabModalPriority" style="text-transform: capitalize;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFab() {
        const card = document.getElementById('fabAgendaCard');
        if (card) card.classList.toggle('active');
    }

    // Close when clicking outside FAB Card
    document.addEventListener('click', function (event) {
        const container = document.querySelector('.fab-container');
        const card = document.getElementById('fabAgendaCard');
        if (container && card && !container.contains(event.target) && card.classList.contains('active')) {
            card.classList.remove('active');
        }
    });

    function showAgendaDetail(element) {
        const data = element.closest('.fab-list-item').dataset;
        const { agendaTitle: title, agendaDescription: desc, agendaDue: due, agendaPriority: priority } = data;

        const priorityMap = {
            'low': 'Rendah',
            'medium': 'Sedang',
            'high': 'Tinggi'
        };

        document.getElementById('fabModalTitle').innerText = title;
        document.getElementById('fabModalDesc').innerText = desc || 'Tidak ada deskripsi.';
        document.getElementById('fabModalDue').innerText = due;
        document.getElementById('fabModalPriority').innerText = priorityMap[priority.toLowerCase()] || priority;

        const backdrop = document.getElementById('fabAgendaModalBackdrop');
        if (backdrop) {
            backdrop.classList.add('active');
            // Otomatis tutup menu agenda list
            const agendaCard = document.getElementById('fabAgendaCard');
            if (agendaCard && agendaCard.classList.contains('active')) {
                agendaCard.classList.remove('active');
            }
        }
    }

    function closeFabModal() {
        const backdrop = document.getElementById('fabAgendaModalBackdrop');
        if (backdrop) backdrop.classList.remove('active');
    }
    function updateClock() {
        const now = new Date();
        const jam = String(now.getHours()).padStart(2, '0');
        const menit = String(now.getMinutes()).padStart(2, '0');
        const detik = String(now.getSeconds()).padStart(2, '0');
        
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const hari = days[now.getDay()];
        const tanggal = now.getDate();
        const bulan = months[now.getMonth()];
        const tahun = now.getFullYear();

        document.getElementById('fabTimeDisplay').innerText = `${jam}:${menit}:${detik}`;
        document.getElementById('fabDateDisplay').innerText = `${hari}, ${tanggal} ${bulan} ${tahun}`;
    }

    // Run clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);

    // ── AI Widget Logic ──────────────────────────
    (function() {
        const toggleBtn = document.getElementById('toggleAiChat');
        const closeBtn  = document.getElementById('closeAiChat');
        const chatWindow = document.getElementById('aiChatWindow');
        const chatForm  = document.getElementById('aiChatForm');
        const chatInput = document.getElementById('aiChatInput');
        const chatBody  = document.getElementById('aiChatBody');
        const sendBtn   = document.getElementById('sendAiMsg');
        const quickReplies = document.getElementById('aiQuickReplies');

        if (!toggleBtn) return;

        let isTyping = false;
        let chatHistory = [];

        const toggleChat = () => {
            chatWindow.classList.toggle('active');
            // Close agenda card if open
            const agendaCard = document.getElementById('fabAgendaCard');
            if (agendaCard && agendaCard.classList.contains('active')) {
                agendaCard.classList.remove('active');
            }
            if (chatWindow.classList.contains('active')) {
                chatInput.focus();
            }
        };

        toggleBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleChat(); });
        closeBtn.addEventListener('click', toggleChat);

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            const wrap = document.getElementById('aiWidgetInline');
            const windowEl = document.getElementById('aiChatWindow');
            if (windowEl && windowEl.classList.contains('active')) {
                const isInsideWrap = wrap && wrap.contains(e.target);
                const isInsideWindow = windowEl.contains(e.target);
                if (!isInsideWrap && !isInsideWindow) {
                    windowEl.classList.remove('active');
                }
            }
        });

        // Quick reply chips
        document.querySelectorAll('.ai-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                chatInput.value = chip.dataset.msg;
                chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        });

        const makeBotAvatar = () => `
            <div class="ai-msg-avatar">
                <img src="/img/Logo/logo_apriprogram.jpg" alt="Bot" class="ai-msg-logo">
            </div>`;

        const appendMessage = (role, content) => {
            const row = document.createElement('div');
            if (role === 'ai') {
                row.className = 'message-row message-row--ai';
                row.innerHTML = `${makeBotAvatar()}<div class="message message--ai"></div>`;
                row.querySelector('.message--ai').innerHTML = typeof marked !== 'undefined' ? marked.parse(content) : content;
                chatBody.appendChild(row);
            } else {
                row.className = 'message-row message-row--user';
                const msgDiv = document.createElement('div');
                msgDiv.className = 'message message--user';
                msgDiv.textContent = content;
                row.appendChild(msgDiv);
            }
            chatBody.appendChild(row);
            chatBody.scrollTop = chatBody.scrollHeight;
        };

        const setTyping = (state) => {
            isTyping = state;
            if (state) {
                const row = document.createElement('div');
                row.id = 'aiTypingIndicator';
                row.className = 'message-row message-row--ai';
                row.innerHTML = makeBotAvatar() + `
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
                const ind = document.getElementById('aiTypingIndicator');
                if (ind) ind.remove();
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
            if (quickReplies) quickReplies.style.display = 'none';
            appendMessage('user', msg);
            setTyping(true);

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const res = await fetch('/admin/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ message: msg, history: chatHistory })
                });

                if (res.ok) {
                    const data = await res.json();
                    setTyping(false);

                    if (data.message) {
                        appendMessage('ai', data.message);
                        chatHistory.push({ role: 'user', content: msg });
                        chatHistory.push({ role: 'assistant', content: data.message });
                        if (chatHistory.length > 10) { chatHistory.shift(); chatHistory.shift(); }
                    } else if (data.error) {
                        appendMessage('ai', 'Sistem Error: ' + data.error);
                    } else {
                        appendMessage('ai', 'Maaf, terjadi gangguan pada layanan AI. Silakan coba lagi.');
                    }
                } else {
                    setTyping(false);
                    const errText = await res.text().catch(() => 'Unknown Error');
                    appendMessage('ai', `Gagal menghubungi server (Status: ${res.status}). Silakan hubungi teknisi.`);
                    console.error('AI Server Error:', res.status, errText);
                }
            } catch (err) {
                setTyping(false);
                appendMessage('ai', 'Koneksi terputus. Pastikan internet Anda aktif dan asisten AI dikonfigurasi dengan benar.');
                console.error('AI Widget Network Error:', err);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && chatWindow.classList.contains('active')) {
                chatWindow.classList.remove('active');
            }
        });
    })();
</script>
