@if ($cookieConsentConfig['enabled'] && !$alreadyConsentedWithCookies)

    <div class="js-cookie-consent cookie-consent" style="background-color: {{ theme_option('cookie_consent_background_color', '#000') }} !important; color: {{ theme_option('cookie_consent_text_color', '#fff') }} !important; position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999; padding: 10px 0;">
        <div class="cookie-consent-body" style="max-width: {{ theme_option('cookie_consent_max_width', 1170) }}px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; padding: 0 15px;">
            <span class="cookie-consent__message" style="margin-right: 15px; flex: 1; min-width: 200px; margin-bottom: 10px;">
                {{ theme_option('cookie_consent_message', trans('plugins/cookie-consent::cookie-consent.message')) }}
                @if (theme_option('cookie_consent_learn_more_url') && theme_option('cookie_consent_learn_more_text'))
                    <a href="{{ url(theme_option('cookie_consent_learn_more_url')) }}" style="color: inherit; text-decoration: underline;">{{ theme_option('cookie_consent_learn_more_text') }}</a>
                @endif
            </span>

            <button class="js-cookie-consent-agree cookie-consent__agree" style="background-color: {{ theme_option('cookie_consent_background_color', '#000') }} !important; color: {{ theme_option('cookie_consent_text_color', '#fff') }} !important; border: 1px solid {{ theme_option('cookie_consent_text_color', '#fff') }} !important; padding: 8px 15px; border-radius: 3px; font-weight: bold; cursor: pointer; white-space: nowrap;">
                {{ theme_option('cookie_consent_button_text', trans('plugins/cookie-consent::cookie-consent.button_text')) }}
            </button>
        </div>
    </div>
    <div data-site-cookie-name="{{ $cookieConsentConfig['cookie_name'] }}" style="display: none;"></div>
    <div data-site-cookie-lifetime="{{ $cookieConsentConfig['cookie_lifetime'] }}" style="display: none;"></div>
    <div data-site-cookie-domain="{{ config('session.domain') ?? request()->getHost() }}" style="display: none;"></div>
    <div data-site-session-secure="{{ config('session.secure') ? ';secure' : null }}" style="display: none;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Direct event handler for the cookie consent button
            var consentButton = document.querySelector('.js-cookie-consent-agree');
            if (consentButton) {
                consentButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get cookie data
                    var cookieName = document.querySelector('div[data-site-cookie-name]').getAttribute('data-site-cookie-name');
                    var cookieDomain = document.querySelector('div[data-site-cookie-domain]').getAttribute('data-site-cookie-domain');
                    var cookieLifetime = document.querySelector('div[data-site-cookie-lifetime]').getAttribute('data-site-cookie-lifetime');
                    var sessionSecure = document.querySelector('div[data-site-session-secure]').getAttribute('data-site-session-secure');
                    
                    // Set cookie
                    var date = new Date();
                    date.setTime(date.getTime() + (cookieLifetime * 24 * 60 * 60 * 1000));
                    document.cookie = cookieName + '=1;expires=' + date.toUTCString() + ';domain=' + cookieDomain + ';path=/' + sessionSecure;
                    
                    // Hide consent
                    var cookieConsent = document.querySelector('.js-cookie-consent');
                    if (cookieConsent) {
                        cookieConsent.style.display = 'none';
                    }
                    
                    return false;
                });
            }
        });
    </script>
@endif
