<div id="visitorWidget" class="visitor-widget-floating">
    <button id="visitorBtn" class="visitor-float" aria-label="Statistik Pengunjung">
        <i class="fas fa-chart-line" aria-hidden="true"></i>
    </button>
    <div id="visitorPanel" class="visitor-panel" role="dialog" aria-label="Statistik Pengunjung" aria-hidden="true">
        <div class="visitor-panel-header">
            <i class="fas fa-chart-line"></i>
            <span>Statistik Kunjungan</span>
            <button type="button" class="visitor-panel-close" aria-label="Tutup">&times;</button>
        </div>
        <div class="visitor-panel-body">
            <div class="visitor-grid">
                <div class="visitor-item">
                    <span class="v-label">Hari Ini</span>
                    <span class="v-value">{{ number_format($visitorStats['today'] ?? 0) }}</span>
                </div>
                <div class="visitor-item">
                    <span class="v-label">Kemarin</span>
                    <span class="v-value">{{ number_format($visitorStats['yesterday'] ?? 0) }}</span>
                </div>
                <div class="visitor-item">
                    <span class="v-label">Minggu Ini</span>
                    <span class="v-value">{{ number_format($visitorStats['this_week'] ?? 0) }}</span>
                </div>
                <div class="visitor-item">
                    <span class="v-label">Minggu Lalu</span>
                    <span class="v-value">{{ number_format($visitorStats['last_week'] ?? 0) }}</span>
                </div>
                <div class="visitor-item">
                    <span class="v-label">Bulan Ini</span>
                    <span class="v-value">{{ number_format($visitorStats['this_month'] ?? 0) }}</span>
                </div>
                <div class="visitor-item">
                    <span class="v-label">Bulan Lalu</span>
                    <span class="v-value">{{ number_format($visitorStats['last_month'] ?? 0) }}</span>
                </div>
                <div class="visitor-item total">
                    <span class="v-label">Total Kunjungan</span>
                    <span class="v-value">{{ number_format($visitorStats['total'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Visitor Stats Floating Button - Placed to the left of Accessibility icon */
    .visitor-float {
        position: fixed;
        right: 180px;
        /* 24 + (44+8)*3 */
        bottom: 24px;
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: #0284c7;
        /* Sky 600 */
        color: #fff;
        border: 1px solid #0369a1;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(2, 132, 199, 0.35);
        cursor: pointer;
        z-index: 1100;
        transition: all 0.3s ease;
    }

    .visitor-float:hover {
        background: #0369a1;
        transform: scale(1.1);
        box-shadow: 0 12px 28px rgba(3, 105, 161, 0.45);
    }

    /* Panel/Popup - Standard (Light Mode) */
    .visitor-panel {
        position: fixed;
        right: 180px;
        bottom: 80px;
        width: 280px;
        background: #ffffff;
        backdrop-filter: blur(10px);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        transform: translateY(10px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        z-index: 1101;
    }

    .visitor-panel.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }

    .visitor-panel-header {
        background: linear-gradient(to right, #0284c7, #0369a1);
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .visitor-panel-close {
        margin-left: auto;
        background: transparent;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
    }

    .visitor-panel-body {
        padding: 15px;
    }

    .visitor-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .visitor-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .visitor-item.total {
        grid-column: 1 / -1;
        background: #f0f9ff;
        border-color: #bae6fd;
        text-align: center;
    }

    .v-label {
        font-size: 0.7rem;
        color: #64748b;
    }

    .v-value {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }

    .total .v-value {
        font-size: 1.25rem;
        color: #0284c7;
    }

    /* Dark Mode Overrides */
    body.dark-mode .visitor-panel {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    body.dark-mode .visitor-item {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }

    body.dark-mode .visitor-item.total {
        background: rgba(2, 132, 199, 0.15);
        border-color: rgba(2, 132, 199, 0.3);
    }

    body.dark-mode .v-label {
        color: rgba(255, 255, 255, 0.7);
    }

    body.dark-mode .v-value {
        color: #fff;
    }

    body.dark-mode .total .v-value {
        color: #38bdf8;
    }

    @media (max-width: 600px) {
        .visitor-float {
            right: 158px;
            /* 110 + 48 shift to left to avoid a11y overlap */
            bottom: 14px;
            width: 40px;
            height: 40px;
        }

        .visitor-panel {
            right: 14px;
            bottom: 64px;
            width: calc(100vw - 28px);
        }
    }
</style>

<script>
    // Logic moved to global floating-widgets.blade.php to ensure cross-panel coordination
</script>