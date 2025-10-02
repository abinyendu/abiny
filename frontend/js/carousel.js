/**
 * Carousel and Slider Functionality
 * Handles hero sliders, product carousels, and image galleries
 */

// Hero Slider
let currentSlide = 0;
let slideInterval;

/**
 * Initialize hero slider
 */
function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (slides.length === 0) return;
    
    // Auto-advance slides
    slideInterval = setInterval(() => {
        nextSlide();
    }, 5000);
    
    // Pause on hover
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        heroSection.addEventListener('mouseleave', () => {
            slideInterval = setInterval(() => {
                nextSlide();
            }, 5000);
        });
    }
}

/**
 * Change to specific slide
 */
function currentSlideChange(slideIndex) {
    const slides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    // Remove active class from all slides and indicators
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));
    
    // Add active class to current slide and indicator
    if (slides[slideIndex]) {
        slides[slideIndex].classList.add('active');
    }
    if (indicators[slideIndex]) {
        indicators[slideIndex].classList.add('active');
    }
    
    currentSlide = slideIndex;
}

/**
 * Navigate to specific slide
 */
function goToSlide(slideIndex) {
    currentSlideChange(slideIndex);
}

/**
 * Go to next slide
 */
function nextSlide() {
    const slides = document.querySelectorAll('.hero-slide');
    const nextIndex = (currentSlide + 1) % slides.length;
    currentSlideChange(nextIndex);
}

/**
 * Go to previous slide
 */
function previousSlide() {
    const slides = document.querySelectorAll('.hero-slide');
    const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
    currentSlideChange(prevIndex);
}

/**
 * Change slide by direction
 */
function changeSlide(direction) {
    if (direction > 0) {
        nextSlide();
    } else {
        previousSlide();
    }
}

// Product Carousels
const carousels = new Map();

/**
 * Initialize all carousels
 */
function initCarousels() {
    document.querySelectorAll('.products-carousel').forEach(carousel => {
        initCarousel(carousel);
    });
}

/**
 * Initialize single carousel
 */
function initCarousel(carouselElement) {
    const carouselId = carouselElement.id || 'carousel-' + Date.now();
    carouselElement.id = carouselId;
    
    const track = carouselElement.querySelector('.products-track');
    const slides = carouselElement.querySelectorAll('.product-slide');
    const prevBtn = carouselElement.querySelector('.carousel-btn.prev');
    const nextBtn = carouselElement.querySelector('.carousel-btn.next');
    
    if (!track || slides.length === 0) return;
    
    const carousel = {
        element: carouselElement,
        track: track,
        slides: slides,
        currentIndex: 0,
        slidesToShow: getSlidesToShow(carouselElement),
        slideWidth: 0,
        maxIndex: 0
    };
    
    // Calculate dimensions
    updateCarouselDimensions(carousel);
    
    // Set up event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => moveCarousel(carouselId, -1));
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => moveCarousel(carouselId, 1));
    }
    
    // Touch/swipe support
    addTouchSupport(carousel);
    
    // Responsive handling
    window.addEventListener('resize', debounce(() => {
        updateCarouselDimensions(carousel);
        updateCarouselPosition(carousel);
    }, 250));
    
    carousels.set(carouselId, carousel);
    updateCarouselButtons(carousel);
}

/**
 * Get number of slides to show based on screen size
 */
function getSlidesToShow(carouselElement) {
    const width = window.innerWidth;
    
    if (width < 480) return 1;
    if (width < 768) return 2;
    if (width < 1024) return 3;
    return 4;
}

/**
 * Update carousel dimensions
 */
function updateCarouselDimensions(carousel) {
    const containerWidth = carousel.element.offsetWidth;
    carousel.slidesToShow = getSlidesToShow(carousel.element);
    carousel.slideWidth = containerWidth / carousel.slidesToShow;
    carousel.maxIndex = Math.max(0, carousel.slides.length - carousel.slidesToShow);
    
    // Update slide widths
    carousel.slides.forEach(slide => {
        slide.style.width = carousel.slideWidth + 'px';
    });
    
    // Ensure current index is valid
    if (carousel.currentIndex > carousel.maxIndex) {
        carousel.currentIndex = carousel.maxIndex;
    }
}

/**
 * Move carousel
 */
function moveCarousel(carouselId, direction) {
    const carousel = carousels.get(carouselId);
    if (!carousel) return;
    
    const newIndex = carousel.currentIndex + direction;
    
    if (newIndex >= 0 && newIndex <= carousel.maxIndex) {
        carousel.currentIndex = newIndex;
        updateCarouselPosition(carousel);
        updateCarouselButtons(carousel);
    }
}

/**
 * Update carousel position
 */
function updateCarouselPosition(carousel) {
    const translateX = -carousel.currentIndex * carousel.slideWidth;
    carousel.track.style.transform = `translateX(${translateX}px)`;
}

/**
 * Update carousel button states
 */
function updateCarouselButtons(carousel) {
    const prevBtn = carousel.element.querySelector('.carousel-btn.prev');
    const nextBtn = carousel.element.querySelector('.carousel-btn.next');
    
    if (prevBtn) {
        prevBtn.disabled = carousel.currentIndex === 0;
        prevBtn.style.opacity = carousel.currentIndex === 0 ? '0.5' : '1';
    }
    
    if (nextBtn) {
        nextBtn.disabled = carousel.currentIndex >= carousel.maxIndex;
        nextBtn.style.opacity = carousel.currentIndex >= carousel.maxIndex ? '0.5' : '1';
    }
}

/**
 * Add touch/swipe support to carousel
 */
function addTouchSupport(carousel) {
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    
    carousel.track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        carousel.track.style.transition = 'none';
    });
    
    carousel.track.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        currentX = e.touches[0].clientX;
        const deltaX = currentX - startX;
        const currentTransform = -carousel.currentIndex * carousel.slideWidth;
        
        carousel.track.style.transform = `translateX(${currentTransform + deltaX}px)`;
    });
    
    carousel.track.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        
        isDragging = false;
        carousel.track.style.transition = '';
        
        const deltaX = currentX - startX;
        const threshold = carousel.slideWidth * 0.3;
        
        if (Math.abs(deltaX) > threshold) {
            if (deltaX > 0 && carousel.currentIndex > 0) {
                moveCarousel(carousel.element.id, -1);
            } else if (deltaX < 0 && carousel.currentIndex < carousel.maxIndex) {
                moveCarousel(carousel.element.id, 1);
            } else {
                updateCarouselPosition(carousel);
            }
        } else {
            updateCarouselPosition(carousel);
        }
    });
    
    // Mouse drag support for desktop
    let mouseDown = false;
    
    carousel.track.addEventListener('mousedown', (e) => {
        startX = e.clientX;
        mouseDown = true;
        carousel.track.style.cursor = 'grabbing';
        carousel.track.style.transition = 'none';
        e.preventDefault();
    });
    
    carousel.track.addEventListener('mousemove', (e) => {
        if (!mouseDown) return;
        
        currentX = e.clientX;
        const deltaX = currentX - startX;
        const currentTransform = -carousel.currentIndex * carousel.slideWidth;
        
        carousel.track.style.transform = `translateX(${currentTransform + deltaX}px)`;
    });
    
    carousel.track.addEventListener('mouseup', (e) => {
        if (!mouseDown) return;
        
        mouseDown = false;
        carousel.track.style.cursor = '';
        carousel.track.style.transition = '';
        
        const deltaX = currentX - startX;
        const threshold = carousel.slideWidth * 0.3;
        
        if (Math.abs(deltaX) > threshold) {
            if (deltaX > 0 && carousel.currentIndex > 0) {
                moveCarousel(carousel.element.id, -1);
            } else if (deltaX < 0 && carousel.currentIndex < carousel.maxIndex) {
                moveCarousel(carousel.element.id, 1);
            } else {
                updateCarouselPosition(carousel);
            }
        } else {
            updateCarouselPosition(carousel);
        }
    });
    
    carousel.track.addEventListener('mouseleave', () => {
        if (mouseDown) {
            mouseDown = false;
            carousel.track.style.cursor = '';
            carousel.track.style.transition = '';
            updateCarouselPosition(carousel);
        }
    });
}

/**
 * Image Gallery Functionality
 */
function initImageGallery() {
    document.querySelectorAll('.image-gallery').forEach(gallery => {
        const mainImage = gallery.querySelector('.main-image img');
        const thumbnails = gallery.querySelectorAll('.thumbnail');
        
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', () => {
                // Update main image
                const newSrc = thumbnail.dataset.fullsize || thumbnail.src;
                mainImage.src = newSrc;
                
                // Update active thumbnail
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                thumbnail.classList.add('active');
                
                // Update zoom if available
                if (window.initImageZoom) {
                    window.initImageZoom();
                }
            });
        });
        
        // Keyboard navigation
        gallery.addEventListener('keydown', (e) => {
            const activeThumbnail = gallery.querySelector('.thumbnail.active');
            const activeIndex = Array.from(thumbnails).indexOf(activeThumbnail);
            
            let newIndex = activeIndex;
            
            if (e.key === 'ArrowLeft') {
                newIndex = Math.max(0, activeIndex - 1);
            } else if (e.key === 'ArrowRight') {
                newIndex = Math.min(thumbnails.length - 1, activeIndex + 1);
            }
            
            if (newIndex !== activeIndex) {
                thumbnails[newIndex].click();
                e.preventDefault();
            }
        });
    });
}

/**
 * Image Zoom Functionality
 */
function initImageZoom() {
    document.querySelectorAll('.zoomable-image').forEach(image => {
        let isZoomed = false;
        
        image.addEventListener('click', () => {
            if (!isZoomed) {
                image.style.transform = 'scale(2)';
                image.style.cursor = 'zoom-out';
                isZoomed = true;
            } else {
                image.style.transform = 'scale(1)';
                image.style.cursor = 'zoom-in';
                isZoomed = false;
            }
        });
        
        image.addEventListener('mouseleave', () => {
            if (isZoomed) {
                image.style.transform = 'scale(1)';
                image.style.cursor = 'zoom-in';
                isZoomed = false;
            }
        });
    });
}

/**
 * Lightbox Functionality
 */
function initLightbox() {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <button class="lightbox-close">&times;</button>
            <button class="lightbox-prev">&#8249;</button>
            <img class="lightbox-image" src="" alt="">
            <button class="lightbox-next">&#8250;</button>
            <div class="lightbox-counter"></div>
        </div>
    `;
    document.body.appendChild(lightbox);
    
    let currentImages = [];
    let currentImageIndex = 0;
    
    // Open lightbox
    function openLightbox(images, startIndex = 0) {
        currentImages = images;
        currentImageIndex = startIndex;
        updateLightboxImage();
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Close lightbox
    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Update lightbox image
    function updateLightboxImage() {
        const image = lightbox.querySelector('.lightbox-image');
        const counter = lightbox.querySelector('.lightbox-counter');
        
        image.src = currentImages[currentImageIndex];
        counter.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
    }
    
    // Navigation
    function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % currentImages.length;
        updateLightboxImage();
    }
    
    function prevImage() {
        currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
        updateLightboxImage();
    }
    
    // Event listeners
    lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    lightbox.querySelector('.lightbox-next').addEventListener('click', nextImage);
    lightbox.querySelector('.lightbox-prev').addEventListener('click', prevImage);
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;
        
        switch (e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowLeft':
                prevImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    });
    
    // Add lightbox trigger to images
    document.addEventListener('click', (e) => {
        if (e.target.matches('.lightbox-trigger')) {
            e.preventDefault();
            
            const gallery = e.target.closest('.image-gallery') || document;
            const images = Array.from(gallery.querySelectorAll('.lightbox-trigger')).map(img => 
                img.dataset.fullsize || img.src
            );
            const startIndex = Array.from(gallery.querySelectorAll('.lightbox-trigger')).indexOf(e.target);
            
            openLightbox(images, startIndex);
        }
    });
}

/**
 * Utility function for debouncing
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize all carousel functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initHeroSlider();
    initCarousels();
    initImageGallery();
    initImageZoom();
    initLightbox();
});

// Export functions for global use
window.changeSlide = changeSlide;
window.goToSlide = goToSlide;
window.moveCarousel = moveCarousel;
window.initCarousels = initCarousels;
window.initHeroSlider = initHeroSlider;