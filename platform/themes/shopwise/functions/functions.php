<?php

use Botble\SimpleSlider\Models\SimpleSliderItem;
use Botble\Ecommerce\Models\ProductCategory;

register_page_template([
    'homepage'     => __('Homepage'),
    'blog-sidebar' => __('Blog Sidebar'),
]);

register_sidebar([
    'id'          => 'footer_sidebar',
    'name'        => __('Footer sidebar'),
    'description' => __('Sidebar in the footer of site'),
]);

RvMedia::setUploadPathAndURLToPublic();

RvMedia::addSize('medium', 540, 600)->addSize('small', 540, 300);

if (is_plugin_active('ecommerce')) {
    add_action(BASE_ACTION_META_BOXES, function ($context, $object) {
        if (get_class($object) == ProductCategory::class && $context == 'advanced') {
            MetaBox::addMetaBox('additional_product_category_fields', __('Addition Information'), function () {
                $icon = null;
                $args = func_get_args();
                if (!empty($args[0])) {
                    $icon = MetaBox::getMetaData($args[0], 'icon', true);
                }

                return Theme::partial('product-category-fields', compact('icon'));
            }, get_class($object), $context);
        }
    }, 24, 2);

    add_action(BASE_ACTION_AFTER_CREATE_CONTENT, function ($type, $request, $object) {
        if (get_class($object) == ProductCategory::class) {
            MetaBox::saveMetaBoxData($object, 'icon', $request->input('icon'));
        }
    }, 230, 3);

    add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, function ($type, $request, $object) {
        if (get_class($object) == ProductCategory::class) {
            MetaBox::saveMetaBoxData($object, 'icon', $request->input('icon'));
        }
    }, 231, 3);

}

Form::component('themeIcon', Theme::getThemeNamespace() . '::partials.icons-field', [
    'name',
    'value'      => null,
    'attributes' => [],
]);

app()->booted(function () {
    if (is_plugin_active('ecommerce')) {
        ProductCategory::resolveRelationUsing('icon', function ($model) {
            return $model->morphOne(\Botble\Base\Models\MetaBox::class, 'reference')->where('meta_key', 'icon');
        });
    }
});

if (is_plugin_active('simple-slider')) {
    add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
        if (get_class($data) == SimpleSliderItem::class) {

            $value = MetaBox::getMetaData($data, 'button_text', true);

            $form
                ->addAfter('link', 'button_text', 'text', [
                    'label'      => __('Button text'),
                    'label_attr' => ['class' => 'control-label'],
                    'value'      => $value,
                    'attr'       => [
                        'placeholder' => __('Ex: Shop now'),
                    ],
                ]);
        }

        return $form;
    }, 124, 3);

    add_action(BASE_ACTION_AFTER_CREATE_CONTENT, 'save_addition_slider_fields', 120, 3);
    add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, 'save_addition_slider_fields', 120, 3);

    /**
     * @param string $screen
     * @param Request $request
     * @param \Botble\Base\Models\BaseModel $data
     */
    function save_addition_slider_fields($screen, $request, $data)
    {
        if (get_class($data) == SimpleSliderItem::class) {
            MetaBox::saveMetaBoxData($data, 'button_text', $request->input('button_text'));
        }
    }
}

add_action(THEME_FRONT_FOOTER, function () {
    // Add custom JavaScript to fix scrolling issues
    echo Theme::asset()->container('footer')->add('custom-scroll', 'js/custom-scroll.js', ['jquery']);
    
    if (is_plugin_active('cookie-consent') && theme_option('cookie_consent_enable', 'yes') == 'yes') {
        echo '<script>
            $(document).ready(function() {
                $(document).on("click", ".js-cookie-consent-agree", function(e) {
                    e.preventDefault();
                    
                    // Get cookie data from hidden divs
                    var cookieName = $("div[data-site-cookie-name]").data("site-cookie-name");
                    var cookieDomain = $("div[data-site-cookie-domain]").data("site-cookie-domain");
                    var cookieLifetime = $("div[data-site-cookie-lifetime]").data("site-cookie-lifetime");
                    var sessionSecure = $("div[data-site-session-secure]").data("site-session-secure");
                    
                    // Set the cookie
                    var date = new Date();
                    date.setTime(date.getTime() + (cookieLifetime * 24 * 60 * 60 * 1000));
                    document.cookie = cookieName + "=1" + 
                        ";expires=" + date.toUTCString() + 
                        ";domain=" + cookieDomain + 
                        ";path=/" + sessionSecure;
                    
                    // Hide the cookie consent dialog
                    $(".js-cookie-consent").fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    return false;
                });
            });
        </script>';
    }
}, 1347);

add_action(THEME_FRONT_HEADER, function () {
    // Add custom CSS for mobile responsiveness and fixing navbar issues
    echo Theme::asset()->container('footer')->add('custom-mobile', 'css/custom-mobile.css', ['style']);
    
    echo '<style>
        /* Fix for cookie consent popup and other popups */
        .modal-open {
            overflow: auto !important;
            padding-right: 0 !important;
        }
        
        /* Make cookie consent responsive */
        @media (max-width: 767px) {
            .cookie-consent-body {
                flex-direction: column;
                text-align: center;
            }
            
            .cookie-consent__message {
                margin-right: 0 !important;
                margin-bottom: 15px !important;
            }
            
            .js-cookie-consent-agree {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
        /* Fix for newsletter popup */
        .subscribe_popup .modal-dialog {
            max-width: 95%;
        }
        
        @media (min-width: 576px) {
            .subscribe_popup .modal-dialog {
                max-width: 500px;
            }
        }
        
        @media (min-width: 992px) {
            .subscribe_popup .modal-dialog {
                max-width: 800px;
            }
        }
        
        /* Ensure popups are properly styled */
        .modal {
            overflow-y: auto !important;
        }
        
        /* Fix z-index issues */
        .modal-backdrop {
            z-index: 9998;
        }
        
        .modal {
            z-index: 9999;
        }
        
        /* Cookie consent should be above everything */
        .cookie-consent {
            z-index: 10000 !important;
        }
    </style>';
}, 15);
