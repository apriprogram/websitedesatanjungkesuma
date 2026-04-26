@php
    $alertToasts = [];

    if (session('status')) {
        $variantMap = [
            'success' => 'success',
            'info' => 'info',
            'warning' => 'warning',
            'danger' => 'error',
            'error' => 'error',
        ];

        $variant = session('status_variant', 'success');

        $alertToasts[] = [
            'variant' => $variantMap[$variant] ?? 'success',
            'title' => session('status'),
            'message' => session('status_description'),
        ];
    }

    if (isset($errors) && $errors->any()) {
        $alertToasts[] = [
            'variant' => 'error',
            'title' => 'Terjadi kesalahan',
            'message' => $errors->first(),
        ];
    }
@endphp

@if (! empty($alertToasts))
    <div class="toast-stack" id="globalToastStack" role="region" aria-live="polite">
        @foreach ($alertToasts as $toast)
            @php $variant = $toast['variant'] ?? 'neutral'; @endphp
            <article class="toast" data-toast data-variant="{{ $variant }}">
                <div class="toast__icon" aria-hidden="true">
                    @switch($variant)
                        @case('success') <i class="fas fa-check"></i> @break
                        @case('error') <i class="fas fa-xmark"></i> @break
                        @case('warning') <i class="fas fa-exclamation"></i> @break
                        @default <i class="fas fa-info"></i>
                    @endswitch
                </div>
                <div class="toast__content">
                    <strong>{{ $toast['title'] }}</strong>
                    @if (! empty($toast['message']))
                        <p>{{ $toast['message'] }}</p>
                    @endif
                </div>
                <button type="button" class="toast__close" data-toast-close aria-label="Tutup notifikasi">
                    <i class="fas fa-times"></i>
                </button>
                <span class="toast__progress" aria-hidden="true"></span>
            </article>
        @endforeach
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const TOAST_DURATION = 5000;

                document.querySelectorAll('#globalToastStack [data-toast]').forEach((toast) => {
                    const closeButton = toast.querySelector('[data-toast-close]');
                    let timerId;

                    const dismissToast = () => {
                        if (toast.classList.contains('is-leaving')) return;
                        toast.classList.add('is-leaving');
                        window.setTimeout(() => toast.remove(), 250);
                    };

                    const startTimer = () => {
                        timerId = window.setTimeout(dismissToast, TOAST_DURATION);
                        toast.classList.remove('is-paused');
                    };

                    const stopTimer = () => {
                        if (timerId) {
                            window.clearTimeout(timerId);
                            timerId = null;
                        }
                        toast.classList.add('is-paused');
                    };

                    toast.addEventListener('mouseenter', stopTimer);
                    toast.addEventListener('mouseleave', startTimer);
                    closeButton?.addEventListener('click', () => {
                        stopTimer();
                        dismissToast();
                    });

                    requestAnimationFrame(() => toast.classList.add('is-visible'));
                    startTimer();
                });
            });
        </script>
    @endpush
@endif
