@php
    $publicInfoSetting = $publicInfoSetting ?? \App\Models\PublicInfoSetting::first();
@endphp

<!-- WhatsApp Floating Widget -->
<div id="whatsappWidget" class="whatsapp-widget"
    data-wa-number="{{ $publicInfoSetting->whatsapp_number ?? '6281234567890' }}"
    data-wa-message="Halo, saya ingin bertanya mengenai layanan Desa Tanjung Kesuma.">
    <button id="whatsappBtn" class="whatsapp-float" aria-label="Chat WhatsApp">
        <i class="fab fa-whatsapp" aria-hidden="true"></i>
    </button>
    <div id="whatsappPanel" class="whatsapp-panel" role="dialog" aria-label="Informasi WhatsApp" aria-hidden="true">
        <div class="whatsapp-panel-header">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp Desa</span>
            <button type="button" class="whatsapp-panel-close" aria-label="Tutup">&times;</button>
        </div>
        <div class="whatsapp-panel-body">
            @php
                $waNumber = $publicInfoSetting->whatsapp_number ?? '6281234567890';
                $formattedWa = '+' . substr($waNumber, 0, 2) . ' ' . substr($waNumber, 2, 3) . '-' . substr($waNumber, 5, 4) . '-' . substr($waNumber, 9);
            @endphp
            <div class="wa-info-number" id="whatsappNumberText">{{ $formattedWa }}</div>
            <div class="wa-info-text">Klik tombol di bawah untuk chat via WhatsApp.</div>
            <a id="whatsappLink" class="wa-chat-button" target="_blank" rel="noopener">Chat via WhatsApp</a>
        </div>
    </div>
</div>

<!-- Accessibility (Ramah Disabilitas) Floating Widget -->
<div id="a11yWidget" class="a11y-widget">
    <button id="a11yBtn" class="accessibility-float" aria-label="Fitur Ramah Disabilitas">
        <i class="fas fa-universal-access" aria-hidden="true"></i>
    </button>
    <div id="a11yPanel" class="a11y-panel" role="dialog" aria-label="Pengaturan Aksesibilitas" aria-hidden="true">
        <div class="whatsapp-panel-header a11y-header">
            <i class="fas fa-universal-access"></i>
            <span>Pengaturan Aksesibilitas</span>
            <button type="button" class="a11y-panel-close" aria-label="Tutup">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="a11y-panel-body">
            <div class="a11y-grid">
                <button type="button" class="a11y-tile" id="feat-invert" aria-pressed="false">
                    <i class="fas fa-adjust"></i>
                    <span>Balikkan warna</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-contrast-dark" aria-pressed="false">
                    <i class="fas fa-moon"></i>
                    <span>Kontras gelap</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-contrast-light" aria-pressed="false">
                    <i class="fas fa-sun"></i>
                    <span>Kontras terang</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-highlight-links" aria-pressed="false">
                    <i class="fas fa-link"></i>
                    <span>Sorot tautan</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-big-text" aria-pressed="false">
                    <i class="fas fa-text-height"></i>
                    <span>Teks Lebih Besar</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-text-spacing" aria-pressed="false">
                    <i class="fas fa-arrows-left-right"></i>
                    <span>Spasi teks</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-reduce-motion" aria-pressed="false">
                    <i class="fas fa-pause-circle"></i>
                    <span>Animasi dijeda</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-hide-images" aria-pressed="false">
                    <i class="fas fa-image"></i>
                    <span>Sembunyikan Gambar</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-dyslexia" aria-pressed="false">
                    <i class="fas fa-book-open"></i>
                    <span>Ramah Disleksia</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-cursor" aria-pressed="false">
                    <i class="fas fa-arrow-pointer"></i>
                    <span>Kursor</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-narrator" aria-pressed="false">
                    <i class="fas fa-volume-high"></i>
                    <span>Narator</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-line-height" aria-pressed="false">
                    <i class="fas fa-arrows-up-down"></i>
                    <span>Tinggi garis</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-text-align" aria-pressed="false">
                    <i class="fas fa-align-left"></i>
                    <span>Perataan Teks</span>
                </button>
                <button type="button" class="a11y-tile" id="feat-saturation" aria-pressed="false" data-state="off">
                    <i class="fas fa-droplet"></i>
                    <span>Kejenuhan</span>
                </button>
            </div>
            <div class="a11y-divider"></div>
            <div class="a11y-section">
                <div class="a11y-label">Ukuran Teks: <span id="a11yScaleText">100%</span></div>
                <div class="a11y-actions">
                    <button type="button" class="a11y-btn" id="a11yDec" aria-label="Perkecil Teks">A-</button>
                    <button type="button" class="a11y-btn" id="a11yReset" aria-label="Reset Ukuran Teks">Reset</button>
                    <button type="button" class="a11y-btn" id="a11yInc" aria-label="Perbesar Teks">A+</button>
                </div>
            </div>
            <button type="button" class="a11y-reset-all" id="a11yResetAll">Atur Ulang Semua Pengaturan
                Aksesibilitas</button>
        </div>
    </div>
</div>

@include('frontend.partials.visitor-widget')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Widget logic initialized

        const widgets = {
            whatsapp: {
                btn: document.getElementById('whatsappBtn'),
                panel: document.getElementById('whatsappPanel'),
                close: document.querySelector('.whatsapp-panel-close')
            },
            a11y: {
                btn: document.getElementById('a11yBtn'),
                panel: document.getElementById('a11yPanel'),
                close: document.querySelector('.a11y-panel-close')
            },
            visitor: {
                btn: document.getElementById('visitorBtn'),
                panel: document.getElementById('visitorPanel'),
                close: document.querySelector('.visitor-panel-close')
            }
        };

        function closeAllPanels(exceptId = null) {
            Object.keys(widgets).forEach(key => {
                if (key !== exceptId && widgets[key].panel) {
                    widgets[key].panel.classList.remove('active');
                    widgets[key].panel.classList.remove('open');
                    // Force hide via style just in case CSS class fails
                    widgets[key].panel.style.visibility = '';
                    widgets[key].panel.style.opacity = '';
                    widgets[key].panel.style.transform = '';
                }
            });
        }

        // Force show all buttons on mobile/scroll
        function ensureButtonsVisible() {
            Object.keys(widgets).forEach(key => {
                if (widgets[key].btn) {
                    widgets[key].btn.classList.add('show');
                    widgets[key].btn.style.opacity = '1';
                    widgets[key].btn.style.pointerEvents = 'auto';
                    widgets[key].btn.style.transform = 'translateY(0)';
                }
            });
        }

        // Generic toggle function
        function setupToggle(key) {
            if (widgets[key].btn && widgets[key].panel) {
                widgets[key].btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    console.log(`Clicked ${key} widget`);

                    const isOpen = widgets[key].panel.classList.contains('open') || widgets[key].panel.classList.contains('active');

                    closeAllPanels(key);

                    if (!isOpen) {
                        // If it was closed, open it
                        widgets[key].panel.classList.add('open');
                        widgets[key].panel.classList.add('active'); // Add both for safety
                        console.log(`Opened ${key} panel`);
                    } else {
                        // If it was open, close it (already done by closeAllPanels but let's be explicit)
                        widgets[key].panel.classList.remove('open');
                        widgets[key].panel.classList.remove('active');
                        console.log(`Closed ${key} panel`);
                    }
                });
            } else {
                console.warn(`Widget ${key} missing elements`);
            }
        }

        setupToggle('whatsapp');
        setupToggle('a11y');

        // Visitor might be special if existing logic uses it, but let's standardize
        if (widgets.visitor.btn) {
            setupToggle('visitor');
        }

        // Global click listener to close everything
        document.addEventListener('click', function (e) {
            let isClickInside = false;
            Object.keys(widgets).forEach(key => {
                if (widgets[key].btn?.contains(e.target) || widgets[key].panel?.contains(e.target)) {
                    isClickInside = true;
                }
            });

            if (!isClickInside) {
                closeAllPanels();
            }
        });

        // Handle close buttons
        Object.keys(widgets).forEach(key => {
            if (widgets[key].close) {
                widgets[key].close.addEventListener('click', (e) => {
                    e.stopPropagation(); // prevent bubbling to global click
                    closeAllPanels();
                });
            }
        });

        // Ensure they appear immediately and on scroll
        ensureButtonsVisible();
        window.addEventListener('scroll', ensureButtonsVisible);
    });
</script>
