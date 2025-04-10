/**
 * Premium E-Commerce JavaScript
 * This file contains real-time features and smooth animations
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Cart functionality with real-time updates
  const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
  if (addToCartButtons) {
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.getAttribute('data-id');
        const productTitle = this.getAttribute('data-title');
        const productImage = this.getAttribute('data-image');
        const productPrice = this.getAttribute('data-price');
        const productQty = document.querySelector(`#qty-${productId}`) ? 
                          document.querySelector(`#qty-${productId}`).value : 1;
        
        // AJAX request to add to cart
        $.ajax({
          url: '/add-to-cart',
          data: {
            id: productId,
            title: productTitle,
            image: productImage,
            price: productPrice,
            qty: productQty
          },
          dataType: 'json',
          beforeSend: function() {
            // Show loading spinner
            button.innerHTML = '<span class="loading"></span>';
            button.disabled = true;
          },
          success: function(res) {
            // Update cart count with animation
            $('.cart-list').text(Object.keys(res.data).length);
            
            // Reset button
            button.innerHTML = 'Added to Cart';
            setTimeout(() => {
              button.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i>Add to Cart';
              button.disabled = false;
            }, 2000);
            
            // Show toast notification
            showToast('Product added to your cart!');
            
            // Animate cart icon
            $('.cart-link').addClass('animate__animated animate__headShake');
            setTimeout(() => {
              $('.cart-link').removeClass('animate__animated animate__headShake');
            }, 1000);
          }
        });
      });
    });
  }
  
  // Filter animation
  const filterToggles = document.querySelectorAll('.filter-toggle');
  if (filterToggles) {
    filterToggles.forEach(toggle => {
      toggle.addEventListener('click', function() {
        const target = document.querySelector(this.getAttribute('data-target'));
        if (target) {
          if (target.classList.contains('show')) {
            // Hide
            target.style.height = '0';
            setTimeout(() => {
              target.classList.remove('show');
            }, 300);
          } else {
            // Show
            target.classList.add('show');
            target.style.height = target.scrollHeight + 'px';
          }
        }
      });
    });
  }
  
  // Real-time search suggestions
  const searchInput = document.querySelector('.search-input');
  if (searchInput) {
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'search-suggestions';
    searchInput.parentNode.appendChild(suggestionsContainer);
    
    let debounceTimer;
    searchInput.addEventListener('keyup', function() {
      clearTimeout(debounceTimer);
      const query = this.value.trim();
      
      // Clear suggestions if query is empty
      if (query.length < 2) {
        suggestionsContainer.innerHTML = '';
        suggestionsContainer.style.display = 'none';
        return;
      }
      
      // Debounce to avoid too many requests
      debounceTimer = setTimeout(() => {
        // AJAX request for suggestions
        $.ajax({
          url: '/search-suggestions',
          data: { q: query },
          dataType: 'json',
          success: function(res) {
            if (res.suggestions && res.suggestions.length > 0) {
              suggestionsContainer.innerHTML = '';
              res.suggestions.forEach(item => {
                const suggestion = document.createElement('div');
                suggestion.className = 'suggestion-item';
                suggestion.innerHTML = `
                  <img src="/media/${item.image}" class="suggestion-img">
                  <div class="suggestion-content">
                    <div class="suggestion-title">${item.title}</div>
                    <div class="suggestion-price">$${item.price}</div>
                  </div>
                `;
                suggestion.addEventListener('click', function() {
                  window.location.href = `/product/${item.slug}/${item.id}`;
                });
                suggestionsContainer.appendChild(suggestion);
              });
              suggestionsContainer.style.display = 'block';
            } else {
              suggestionsContainer.innerHTML = '<div class="no-suggestions">No products found</div>';
              suggestionsContainer.style.display = 'block';
            }
          }
        });
      }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
      if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
        suggestionsContainer.style.display = 'none';
      }
    });
  }
  
  // Real-time price range filter
  const priceRange = document.querySelector('.price-range-slider');
  if (priceRange) {
    const minPriceEl = document.querySelector('.min-price');
    const maxPriceEl = document.querySelector('.max-price');
    const priceLabels = document.querySelectorAll('.price-label');
    
    noUiSlider.create(priceRange, {
      start: [parseInt(minPriceEl.value), parseInt(maxPriceEl.value)],
      connect: true,
      step: 1,
      range: {
        'min': parseInt(priceRange.getAttribute('data-min')),
        'max': parseInt(priceRange.getAttribute('data-max'))
      }
    });
    
    priceRange.noUiSlider.on('update', function(values, handle) {
      priceLabels[handle].innerHTML = '$' + Math.round(values[handle]);
      if (handle === 0) {
        minPriceEl.value = Math.round(values[handle]);
      } else {
        maxPriceEl.value = Math.round(values[handle]);
      }
    });
    
    priceRange.noUiSlider.on('change', function() {
      // Trigger filter update
      if (window.updateFilters) {
        window.updateFilters();
      }
    });
  }
  
  // Quantity selector
  const qtyBtns = document.querySelectorAll('.qty-btn');
  if (qtyBtns) {
    qtyBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const input = this.parentNode.querySelector('input');
        const step = this.getAttribute('data-step') === 'up' ? 1 : -1;
        let value = parseInt(input.value) + step;
        
        // Ensure minimum quantity is 1
        value = value < 1 ? 1 : value;
        
        input.value = value;
        
        // Trigger change event for cart updates
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
      });
    });
  }
  
  // Image zoom effect
  const productImage = document.querySelector('.product-main-image');
  if (productImage) {
    const imageZoom = document.createElement('div');
    imageZoom.className = 'image-zoom';
    productImage.parentNode.appendChild(imageZoom);
    
    productImage.addEventListener('mousemove', function(e) {
      // Get cursor position
      const x = e.clientX - this.getBoundingClientRect().left;
      const y = e.clientY - this.getBoundingClientRect().top;
      
      // Calculate percentage
      const xPercent = x / this.offsetWidth * 100;
      const yPercent = y / this.offsetHeight * 100;
      
      // Apply zoom effect
      imageZoom.style.backgroundImage = `url(${this.src})`;
      imageZoom.style.backgroundPosition = `${xPercent}% ${yPercent}%`;
      imageZoom.style.left = `${x}px`;
      imageZoom.style.top = `${y}px`;
      imageZoom.style.opacity = 1;
    });
    
    productImage.addEventListener('mouseleave', function() {
      imageZoom.style.opacity = 0;
    });
  }
  
  // Smooth scroll to top button
  const scrollBtn = document.querySelector('.scroll-top');
  if (scrollBtn) {
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        scrollBtn.classList.add('show');
      } else {
        scrollBtn.classList.remove('show');
      }
    });
    
    scrollBtn.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }
  
  // Add fade-in animation to elements as they come into view
  const animateElements = document.querySelectorAll('.animate-on-scroll');
  if (animateElements.length > 0) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('fade-in');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1
    });
    
    animateElements.forEach(element => {
      observer.observe(element);
    });
  }
}); 