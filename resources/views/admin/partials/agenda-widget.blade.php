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

    <!-- Modal Detail Widget -->
    <div class="fab-modal-backdrop" id="fabAgendaModalBackdrop" onclick="if(event.target === this) closeFabModal()">
        <div class="fab-modal">
            <div class="fab-modal-inner">
            <header class="fab-modal-header">
                <h4 class="fab-modal-title">Detail Agenda</h4>
                <button type="button" class="fab-modal-close" onclick="closeFabModal()">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="fab-modal-body">
                <div class="fab-modal-grid">
                    <div class="fab-modal-main">
                        <div class="fab-detail-label">
                            <i class="fas fa-bookmark"></i> NAMA KEGIATAN
                        </div>
                        <div class="fab-detail-value" id="fabModalTitle"></div>

                        <div class="fab-detail-label">
                            <i class="fas fa-align-left"></i> DESKRIPSI LENGKAP
                        </div>
                        <div class="fab-modal-desc-box" id="fabModalDesc"></div>
                    </div>

                    <div class="fab-modal-side">
                        <div class="fab-meta-card">
                            <div class="fab-detail-label"><i class="fas fa-calendar-day"></i> WAKTU</div>
                            <div class="fab-detail-value" id="fabModalDue"></div>
                        </div>

                        <div class="fab-meta-card">
                            <div class="fab-detail-label"><i class="fas fa-flag"></i> PRIORITAS</div>
                            <div class="fab-detail-value" id="fabModalPriority" style="text-transform: capitalize;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
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
        if (backdrop) backdrop.classList.add('active');
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
            if (wrap && !wrap.contains(e.target) && chatWindow.classList.contains('active')) {
                chatWindow.classList.remove('active');
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