@php
Theme::layout('homepage');
$featuredProducts = get_featured_products();
@endphp

<div id="app">
    {!! do_shortcode('[simple-slider key="home-slider"][/simple-slider]') !!}
    {!! do_shortcode('[featured-product-categories title="' . __('Top Categories') . '"][/featured-product-categories]') !!}
    {!! do_shortcode('[product-collections title="' . __('Exclusive Products') . '"][/product-collections]') !!}
    {!! do_shortcode('[trending-products title="' . __('Trending Products') . '"][/trending-products]') !!}
    {!! do_shortcode('[flash-sale title="' . __('Deal Of The Day') . '" flash_sale_id="1"][/flash-sale]') !!}
    {!! do_shortcode('[banners image1="general/b-1.jpg" url1="/product-categories/headphone" image2="general/b-2.jpg" url2="/product-categories/camera" image3="general/b-3.jpg" url3="/product-categories/watches"][/banners]') !!}
    {!! do_shortcode('[product-blocks featured_product_title="Featured Products" top_rated_product_title="Top Rated Products" on_sale_product_title="On Sale Products"][/product-blocks]') !!}
    {!! do_shortcode('[testimonials title="Our Client Say!"][/testimonials]') !!}
    {!! do_shortcode('[our-features icon1="flaticon-shipped" title1="Free Delivery" description1="Free shipping on all US order or order above $200" icon2="flaticon-money-back" title2="30 Day Returns Guarantee" description2="Simply return it within 30 days for an exchange" icon3="flaticon-support" title3="27/4 Online Support" description3="Contact us 24 hours a day, 7 days a week"][/our-features]') !!}
    {!! do_shortcode('[newsletter-form title="Join Our Newsletter Now" description="Register now to get updates on promotions."][/newsletter-form]') !!}
</div>
