<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1" name="viewport"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Performance optimization - load critical CSS first -->
        <style>
            :root {
                --color-1st: #D4AF37; /* Gold color */
                --color-2nd: #000000; /* Black color */
                --primary-font: '{{ theme_option('primary_font', 'Poppins') }}', sans-serif;
            }
            /* Critical CSS for above-the-fold content */
            body {visibility: hidden;}
            .preloader {display: none;}
            .header_wrap, .navbar-brand img {display: block;}
        </style>

        <!-- Google Font - load only what's needed -->
        <link href="https://fonts.googleapis.com/css?family={{ urlencode(theme_option('primary_font', 'Poppins')) }}:400,500,600,700&display=swap" rel="stylesheet">
        
        <!-- Performance optimization script -->
        <script src="{{ asset('platform/themes/shopwise/public/js/performance.js') }}" defer></script>

        {!! Theme::header() !!}
        <link media="all" type="text/css" rel="stylesheet" href="{{ asset('platform/themes/shopwise/public/css/custom.css') }}">
        
        <script>
            // Show page content quickly
            document.addEventListener('DOMContentLoaded', function() {
                document.body.style.visibility = 'visible';
            });
        </script>
    </head>
    <body @if (BaseHelper::siteLanguageDirection() == 'rtl') dir="rtl" @endif>
    @if (theme_option('preloader_enabled', 'no') == 'yes')
        <!-- LOADER -->
        <div class="preloader" style="display: none;">
            <div class="lds-ellipsis">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <!-- END LOADER -->
    @endif

    <div id="alert-container"></div>

    @if (is_plugin_active('newsletter') && theme_option('enable_newsletter_popup', 'yes') === 'yes')
        <div data-session-domain="{{ config('session.domain') ?? request()->getHost() }}"></div>
        <!-- Home Popup Section -->
        <div class="modal fade subscribe_popup" id="newsletter-modal" data-time="{{ (int)theme_option('newsletter_show_after_seconds', 10) * 1000 }}" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="ion-ios-close-empty"></i></span>
                        </button>
                        <div class="row no-gutters">
                            <div class="col-sm-5">
                                @if (theme_option('newsletter_image'))
                                    <div class="background_bg h-100" data-img-src="{{ RvMedia::getImageUrl(theme_option('newsletter_image')) }}"></div>
                                @endif
                            </div>
                            <div class="col-sm-7">
                                <div class="popup_content">
                                    <div class="popup-text">
                                        <div class="heading_s1">
                                            <h3>{{ __('Subscribe Newsletter and Get 25% Discount!') }}</h3>
                                        </div>
                                        <p>{{ __('Subscribe to the newsletter to receive updates about new products.') }}</p>
                                    </div>
                                    <div class="newsletter-form rounded_input mt-3">
                                        <form method="post" action="{{ route('public.newsletter.subscribe') }}">
                                            @csrf
                                            <input name="email" type="email" class="form-control" placeholder="{{ __('Enter Your Email') }}">
                                            <button type="submit" class="btn btn-fill-out btn-block">{{ __('Subscribe') }}</button>
                                            <div class="form-group mt-3">
                                                <input type="checkbox" name="dont_show_again" id="dont_show_again" value="1">
                                                <label for="dont_show_again">{{ __("Don't show this popup again") }}</label>
                                            </div>
                                        </form>
                                        <div class="newsletter-message newsletter-success-message" style="display: none"></div>
                                        <div class="newsletter-message newsletter-error-message" style="display: none"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        if (is_plugin_active('ecommerce')) {
            $categories = get_product_categories(['status' => \Botble\Base\Enums\BaseStatusEnum::PUBLISHED], ['slugable', 'children', 'children.slugable', 'icon'], [], true);
        } else {
            $categories = [];
        }
    @endphp

    <!-- START HEADER -->
    <header class="header_wrap @if (theme_option('enable_sticky_header', 'yes') == 'yes') fixed-top header_with_topbar @endif">
        <div class="top-header d-none d-md-block">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                            <ul class="contact_detail text-center text-lg-left">
                                <li><i class="ti-mobile"></i><span>{{ theme_option('hotline') }}</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-end">
                            @if (is_plugin_active('ecommerce'))
                                <ul class="header_list">
                                    @if (!auth('customer')->check())
                                        <li><a href="{{ route('customer.login') }}"><i class="ti-user"></i><span>{{ __('Login') }}</span></a></li>
                                    @else
                                        <li><a href="{{ route('customer.overview') }}"><i class="ti-user"></i><span>{{ auth('customer')->user()->name }}</span></a></li>
                                        <li><a href="{{ route('customer.logout') }}"><i class="ti-lock"></i><span>{{ __('Logout') }}</span></a></li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="middle-header dark_skin">
            <div class="container">
                <div class="nav_block">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img class="logo_dark" src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" />
                    </a>
                    <div class="contact_phone order-md-last">
                        <i class="linearicons-phone-wave"></i>
                        <span>{{ theme_option('hotline') }}</span>
                    </div>
                    @if (is_plugin_active('ecommerce'))
                        <div class="product_search_form">
                            <form action="{{ route('public.products') }}" method="GET">
                                <div class="input-group">
                                    <input class="form-control" name="q" value="{{ request()->input('q') }}" placeholder="{{ __('Search for premium clothing...') }}" required  type="text">
                                    <button type="submit" class="search_btn"><i class="linearicons-magnifier"></i></button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="bottom_header light_skin main_menu_uppercase bg_dark @if (url()->current() === url('')) mb-4 @endif">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <nav class="navbar navbar-expand-lg">
                            <button class="navbar-toggler side_navbar_toggler" type="button" data-toggle="collapse" data-target="#navbarSidetoggle" aria-expanded="false">
                                <span class="ion-android-menu"></span>
                            </button>
                            <div class="collapse navbar-collapse mobile_side_menu" id="navbarSidetoggle">
                                {!! Menu::renderMenuLocation('main-menu', ['view' => 'menu', 'options' => ['class' => 'navbar-nav justify-content-center mx-auto']]) !!}
                            </div>
                            @if (is_plugin_active('ecommerce'))
                                <ul class="navbar-nav attr-nav align-items-center">
                                    @if (EcommerceHelper::isCartEnabled())
                                        <li class="dropdown cart_dropdown"><a class="nav-link cart_trigger btn-shopping-cart" href="#" data-toggle="dropdown"><i class="linearicons-cart"></i><span class="cart_count">{{ Cart::instance('cart')->count() }}</span></a>
                                            <div class="cart_box dropdown-menu dropdown-menu-right">
                                                {!! Theme::partial('cart') !!}
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            @endif
                            <div class="pr_search_icon">
                                <a href="javascript:void(0);" class="nav-link pr_search_trigger"><i class="linearicons-magnifier"></i></a>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- END HEADER -->
