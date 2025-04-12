    <footer class="footer_dark">
        <div class="footer_top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="widget">
                            @if (theme_option('logo_footer') || theme_option('logo'))
                                <div class="footer_logo">
                                    <a href="{{ url('/') }}">
                                        <img src="{{ RvMedia::getImageUrl(theme_option('logo_footer') ? theme_option('logo_footer') : theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" />
                                    </a>
                                </div>
                            @endif
                            <p class="premium-text">{{ theme_option('about-us') }}</p>
                            <div class="widget">
                                <ul class="social_icons social_white">
                                    @if (theme_option('facebook'))
                                        <li><a href="{{ theme_option('facebook') }}" class="sc_facebook" target="_blank"><i class="ion-social-facebook"></i></a></li>
                                    @endif
                                    @if (theme_option('twitter'))
                                        <li><a href="{{ theme_option('twitter') }}" class="sc_twitter" target="_blank"><i class="ion-social-twitter"></i></a></li>
                                    @endif
                                    @if (theme_option('youtube'))
                                        <li><a href="{{ theme_option('youtube') }}" class="sc_youtube" target="_blank"><i class="ion-social-youtube-outline"></i></a></li>
                                    @endif
                                    @if (theme_option('instagram'))
                                        <li><a href="{{ theme_option('instagram') }}" class="sc_instagram" target="_blank"><i class="ion-social-instagram-outline"></i></a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="widget">
                            <h6 class="widget_title">COLLECTIONS</h6>
                            <ul class="widget_links">
                                @foreach(get_featured_product_categories(['take' => 5]) as $category)
                                    <li><a href="{{ $category->url }}">{{ $category->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="widget">
                            <h6 class="widget_title">INFORMATION</h6>
                            {!!
                                Menu::renderMenuLocation('useful-links', [
                                    'options' => ['class' => 'widget_links'],
                                    'view'    => 'menu',
                                ])
                            !!}
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="widget">
                            <h6 class="widget_title">CONTACT</h6>
                            <ul class="contact_info contact_info_light">
                                <li>
                                    <i class="ti-location-pin gold-icon"></i>
                                    <p>{{ theme_option('address') }}</p>
                                </li>
                                <li>
                                    <i class="ti-email gold-icon"></i>
                                    <a href="mailto:{{ theme_option('email') }}">{{ theme_option('email') }}</a>
                                </li>
                                <li>
                                    <i class="ti-mobile gold-icon"></i>
                                    <p>{{ theme_option('hotline') }}</p>
                                </li>
                            </ul>
                        </div>
                        <div class="widget">
                            <h6 class="widget_title">NEWSLETTER</h6>
                            <div class="newsletter_form form_style3">
                                <form method="post" action="{{ route('public.newsletter.subscribe') }}" class="newsletter-form">
                                    @csrf
                                    <div class="outline-input-group">
                                        <input name="email" type="email" class="form-control" placeholder="{{ __('Your email address') }}">
                                        <button type="submit" class="btn-send" title="{{ __('Subscribe') }}">
                                            <i class="linearicons-envelope-open"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <p class="newsletter-notice">Sign up for our newsletter to receive exclusive updates and offers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom_footer border-top-tran">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-md-0 text-center text-md-left">© {{ date('Y') }} {{ theme_option('site_title') }}. Premium Unstitched Clothing.</p>
                    </div>
                    <div class="col-md-6">
                        <ul class="footer_payment text-center text-lg-right">
                            @foreach(json_decode(theme_option('payment_methods', []), true) as $method)
                                @if (!empty($method))
                                    <li><img src="{{ RvMedia::getImageUrl($method) }}" alt="payment method"></li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

     @if (is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled())
         <div id="remove-item-modal" class="modal" tabindex="-1" role="dialog">
             <div class="modal-dialog modal-dialog-centered" role="document">
                 <div class="modal-content">
                     <div class="modal-header">
                         <h5 class="modal-title">{{ __('Warning') }}</h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                             <span aria-hidden="true">&times;</span>
                         </button>
                     </div>
                     <div class="modal-body">
                         <p>{{ __('Are you sure you want to remove this product from cart?') }}</p>
                     </div>
                     <div class="modal-footer">
                         <button type="button" class="btn btn-fill-out" data-dismiss="modal">{{ __('Cancel') }}</button>
                         <button type="button" class="btn btn-fill-line confirm-remove-item-cart">{{ __('Yes, remove it!') }}</button>
                     </div>
                 </div>
             </div>
         </div>
     @endif

    <a href="#" class="scrollup" style="display: none;"><i class="ion-ios-arrow-up"></i></a>

    <script>
        window.siteUrl = "{{ url('') }}";
    </script>

    {!! Theme::footer() !!}

    @if (session()->has('success_msg') || session()->has('error_msg') || (isset($errors) && $errors->count() > 0) || isset($error_msg))
        <script type="text/javascript">
            $(document).ready(function () {
                @if (session()->has('success_msg'))
                    window.showAlert('alert-success', '{{ session('success_msg') }}');
                @endif

                @if (session()->has('error_msg'))
                    window.showAlert('alert-danger', '{{ session('error_msg') }}');
                @endif

                @if (isset($error_msg))
                    window.showAlert('alert-danger', '{{ $error_msg }}');
                @endif

                @if (isset($errors))
                    @foreach ($errors->all() as $error)
                        window.showAlert('alert-danger', '{!! $error !!}');
                    @endforeach
                @endif
            });
        </script>
    @endif

    </body>
</html>
