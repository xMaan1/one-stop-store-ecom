/**
 * AlWaqar Performance Optimizations
 */

// Fast page loading
document.addEventListener('DOMContentLoaded', function() {
    // Hide preloader quickly
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        preloader.style.display = 'none';
    }
    
    // Apply lazy loading to all images
    const images = document.querySelectorAll('img:not([loading])');
    images.forEach(img => {
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
    });
    
    // Defer non-critical CSS
    const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
    stylesheets.forEach(sheet => {
        if (!sheet.hasAttribute('media')) {
            sheet.setAttribute('media', 'all');
        }
    });
    
    // Optimize animations
    const animatedElements = document.querySelectorAll('.hover_effect1, .hover_effect2, .product_img_box');
    animatedElements.forEach(el => {
        el.style.transition = 'transform 0.3s ease-in-out';
    });
});

// Optimize window load event
window.addEventListener('load', function() {
    // Remove any remaining loading indicators
    const loadingElements = document.querySelectorAll('.loading, .spinner');
    loadingElements.forEach(element => {
        element.style.display = 'none';
    });
    
    // Optimize carousel/slider initialization
    const carousels = document.querySelectorAll('.carousel_slider, .slick-slider');
    if (window.jQuery && carousels.length > 0) {
        jQuery(carousels).each(function() {
            const $this = jQuery(this);
            if ($this.hasClass('slick-initialized')) {
                $this.slick('setOption', 'lazyLoad', 'progressive', true);
            }
        });
    }
});

// Optimize scrolling
let isScrolling;
window.addEventListener('scroll', function() {
    // Clear our timeout throughout the scroll
    window.clearTimeout(isScrolling);
    
    // Set a timeout to run after scrolling ends
    isScrolling = setTimeout(function() {
        // Load visible images
        const visibleImages = document.querySelectorAll('img[data-src]');
        visibleImages.forEach(img => {
            const rect = img.getBoundingClientRect();
            const isVisible = (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
            
            if (isVisible) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            }
        });
    }, 100);
}, false); 