@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $socialLinks = $socialLinks ?? collect();
    $publicInfoSetting = $publicInfoSetting ?? new \App\Models\PublicInfoSetting(['is_published' => false]);

    $footerMapEmbed = $publicInfoSetting->map_embed_url ?? null;
    $footerMapSrc = null;

    if (!empty($footerMapEmbed) && preg_match('/src="([^"]+)"/', $footerMapEmbed, $matchFooter)) {
        $footerMapSrc = $matchFooter[1];
    }

    $defaultFooterSrc = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15899.602539798221!2d105.51701121149443!3d-4.956181492759378!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e409c7586b5d587%3A0xfa2e376879bfa2dd!2sTanjung%20Kesuma%2C%20Purbolinggo%2C%20East%20Lampung%20Regency%2C%20Lampung!5e0!3m2!1sen!2sid!4v1755350967514!5m2!1sen!2sid';
@endphp

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>Desa Tanjung Kesuma</h3>
            @php $footerLinks = collect($publicInfoSetting->footer_links ?? []); @endphp
            @if($footerLinks->count())
                <ul>
                    @foreach($footerLinks as $link)
                        @php $label = $link['label'] ?? '';
                        $url = $link['url'] ?? '#'; @endphp
                        <li><a href="{{ $url }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="footer-section">
            <h3>Reach us</h3>
            @php
                $socials = collect($socialLinks ?? []);
                $iconSocials = $socials->filter(fn($s) => (($s->type ?? 'icon') === 'icon') && !empty($s->url));
                $textLinks = collect(\App\Models\FooterLink::orderBy('sort_order')->get());
            @endphp
            @if($iconSocials->count())
                <div class="social-icons">
                    @foreach($iconSocials as $soc)
                        @php
                            $iconClass = $soc->icon ?? 'fa-link';
                            if (!Str::startsWith($iconClass, 'fa')) {
                                $iconClass = 'fab fa-' . Str::slug($iconClass);
                            }
                            if (!Str::startsWith($iconClass, 'fa')) {
                                $iconClass = 'fa-solid fa-link';
                            }
                        @endphp
                        <a href="{{ $soc->url }}" class="social-icon" target="_blank" rel="noopener">
                            <i class="{{ $iconClass }}"></i>
                            <span class="sr-only">{{ $soc->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
            @if($textLinks->count())
                <ul>
                    @foreach($textLinks as $soc)
                        <li><a href="{{ $soc->url }}" target="_blank" rel="noopener">{{ $soc->name }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if($publicInfoSetting->is_published)
            <div class="footer-section footer-map">
                <h3>Lokasi &amp; Alamat</h3>
                @if(!empty($footerMapEmbed))
                    {!! $footerMapEmbed !!}
                @else
                    <iframe src="{{ $defaultFooterSrc }}" width="100%" height="200" style="border:0; border-radius: 12px;"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @endif
                <div class="footer-address">
                    {{ $publicInfoSetting->footer_address ?? 'Desa Tanjung Kesuma, Kecamatan Purbolinggo, Kabupaten Lampung Timur, Provinsi Lampung, Indonesia' }}
                </div>
            </div>
        @endif
    </div>

    @include('frontend.partials.floating-widgets')

    <div class="footer-bottom">
        <div class="footer-bottom-left">
            @php
                $lastUpdated = optional($publicInfoSetting->updated_at ?? $publicInfoSetting->created_at)->format('d F Y') ?? now()->format('d F Y');
            @endphp
            <span>© {{ now()->year }} Desa Tanjung Kesuma, updated {{ $lastUpdated }}</span>
        </div>
        <div class="footer-bottom-right">
            <div class="footer-logo">
                <img src="{{ asset('img/Logo/logo_lampung_timur.png') }}" alt="Logo Desa Tanjung Kesuma"
                    class="logo-image">
                <span>Desa Tanjung Kesuma</span>
            </div>
        </div>
    </div>
</footer>
