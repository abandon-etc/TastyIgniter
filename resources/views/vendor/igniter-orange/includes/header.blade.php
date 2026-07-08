<nav class="navbar navbar-light navbar-top navbar-expand-md py-sm-2 py-md-0">
    <div class="container">
        <a class="navbar-brand" href="{{ page_url('home') }}">
            @if($theme->logo_image)
                <img
                    class="img-logo"
                    alt="{{ setting('site_name') }}"
                    src="{{ media_url($theme->logo_image) }}"
                />
            @elseif($theme->logo_text)
                <span class="text-logo">{{ $theme->logo_text }}</span>
            @else
                <img
                    class="img-logo"
                    alt="{{ $site_name }}"
                    src="{{ $site_logo !== 'no_photo.png'
                        ? media_thumb($site_logo)
                        : asset('vendor/igniter-orange/images/favicon.ico') }}"
                />
            @endif
        </a>

        <div class="d-flex align-items-center ms-auto order-md-3">
            <div class="language-switcher small text-nowrap me-2 me-md-0 ms-md-3">
                <a
                    href="{{ route('language.switch', ['locale' => 'fr_CA']) }}"
                    @class(['fw-bold text-decoration-none' => app()->getLocale() === 'fr_CA'])
                    @if(app()->getLocale() === 'fr_CA') aria-current="true" @endif
                >Français</a>
                <span class="text-muted mx-1">|</span>
                <a
                    href="{{ route('language.switch', ['locale' => 'en_CA']) }}"
                    @class(['fw-bold text-decoration-none' => app()->getLocale() === 'en_CA'])
                    @if(app()->getLocale() === 'en_CA') aria-current="true" @endif
                >English</a>
            </div>

            <button
                class="navbar-toggler border-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMainHeader"
                aria-controls="navbarMainHeader"
                aria-expanded="false"
                aria-label="Toggle navigation"
            ><span class="navbar-toggler-icon"></span></button>
        </div>

        <div class="justify-content-end collapse navbar-collapse order-md-2" id="navbarMainHeader">
            <x-igniter-orange::nav code="main-menu"/>
        </div>
    </div>
</nav>
