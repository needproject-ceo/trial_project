/**
 * Gauri Collections - Main JavaScript
 * E-commerce functionality for antique Indian god statues store
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // =========================================================================
    // Site URL Detection
    // =========================================================================
    const SITE_URL = (() => {
        const path = window.location.pathname;
        // Strip /pages/... or /admin/... or trailing file to get base
        const base = path.replace(/\/(pages|admin)(\/.*)?$/, '').replace(/\/[^/]*\.\w+$/, '');
        return window.location.origin + base;
    })();

    // =========================================================================
    // Utility: CSRF Token
    // =========================================================================
    function getCSRFToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const input = document.querySelector('input[name="csrf_token"]');
        if (input) return input.value;
        return '';
    }

    // =========================================================================
    // 12. Toast Notification System
    // =========================================================================
    function showToast(message, type = 'info') {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            Object.assign(container.style, {
                position: 'fixed', top: '20px', right: '20px',
                zIndex: '9999', display: 'flex', flexDirection: 'column', gap: '10px'
            });
            document.body.appendChild(container);
        }

        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        Object.assign(toast.style, {
            padding: '12px 20px', borderRadius: '8px', color: '#fff',
            fontSize: '14px', display: 'flex', alignItems: 'center', gap: '10px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.2)', opacity: '0',
            transform: 'translateX(100%)', transition: 'all 0.3s ease',
            background: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'
        });
        toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${message}</span>`;
        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Expose globally for inline usage
    window.showToast = showToast;

    // =========================================================================
    // 1. Theme Toggle (Dark/Light Mode)
    // =========================================================================
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }

        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            const ico = themeToggle.querySelector('i');
            if (ico) {
                ico.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        });
    }

    // =========================================================================
    // 2. Mobile Menu Toggle
    // =========================================================================
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.getElementById('mainNav');
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            mainNav.classList.toggle('active');
            const icon = mobileMenuToggle.querySelector('i');
            if (icon) {
                icon.className = mainNav.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
            }
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (mainNav.classList.contains('active') &&
                !mainNav.contains(e.target) &&
                !mobileMenuToggle.contains(e.target)) {
                mainNav.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                if (icon) icon.className = 'fas fa-bars';
            }
        });
    }

    // =========================================================================
    // 3. Sticky Header
    // =========================================================================
    const mainHeader = document.getElementById('mainHeader');
    if (mainHeader) {
        const onScroll = () => {
            mainHeader.classList.toggle('scrolled', window.scrollY > 100);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // =========================================================================
    // 4. Back to Top Button
    // =========================================================================
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('visible', window.scrollY > 300);
        }, { passive: true });

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // =========================================================================
    // 5. Cart Operations (Add to Cart)
    // =========================================================================
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const productId = btn.dataset.productId;
            if (!productId) return;

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

            try {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', '1');
                formData.append('action', 'add');
                formData.append('csrf_token', getCSRFToken());

                const res = await fetch(`${SITE_URL}/pages/cart_actions.php`, {
                    method: 'POST', body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message || 'Added to cart!', 'success');
                    updateCartCount();
                } else {
                    showToast(data.message || 'Could not add to cart.', 'error');
                }
            } catch {
                showToast('Network error. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });

    /** Fetch and update the cart count badge */
    async function updateCartCount() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_count');
            formData.append('csrf_token', getCSRFToken());

            const res = await fetch(`${SITE_URL}/pages/cart_actions.php`, {
                method: 'POST', body: formData
            });
            const data = await res.json();
            const el = document.getElementById('cartCount');
            if (el && data.count !== undefined) {
                el.textContent = data.count;
            }
        } catch {
            // Silently fail – cart badge is non-critical
        }
    }

    // =========================================================================
    // 6. Quantity Controls
    // =========================================================================
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrapper = btn.closest('.qty-wrapper') || btn.parentElement;
            const input = wrapper.querySelector('.qty-input');
            if (!input) return;

            let val = parseInt(input.value, 10) || 1;
            const min = parseInt(input.min, 10) || 1;
            const max = parseInt(input.max, 10) || 999;

            if (btn.dataset.action === 'increase') {
                val = Math.min(val + 1, max);
            } else if (btn.dataset.action === 'decrease') {
                val = Math.max(val - 1, min);
            }
            input.value = val;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // =========================================================================
    // 7. Product Image Gallery
    // =========================================================================
    const thumbContainer = document.querySelector('.product-thumbnails');
    const mainImage = document.querySelector('.product-main-image');
    if (thumbContainer && mainImage) {
        thumbContainer.querySelectorAll('img').forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImage.src = thumb.dataset.full || thumb.src;
                mainImage.alt = thumb.alt || mainImage.alt;
                // Highlight active thumbnail
                thumbContainer.querySelectorAll('img').forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
    }

    // =========================================================================
    // 8. Flash Message Auto-dismiss
    // =========================================================================
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.transition = 'opacity 0.5s ease';
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 500);
        }, 5000);
    }

    // =========================================================================
    // 9. Newsletter Form
    // =========================================================================
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const emailInput = newsletterForm.querySelector('input[name="email"]');
            const email = emailInput ? emailInput.value.trim() : '';

            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast('Please enter a valid email address.', 'error');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('email', email);

                const res = await fetch(`${SITE_URL}/pages/newsletter.php`, {
                    method: 'POST', body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message || 'Subscribed successfully!', 'success');
                    newsletterForm.reset();
                } else {
                    showToast(data.message || 'Subscription failed.', 'error');
                }
            } catch {
                showToast('Network error. Please try again.', 'error');
            }
        });
    }

    // =========================================================================
    // 10. Dropdown Navigation
    // =========================================================================
    document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        if (!toggle || !menu) return;

        // Mobile: click to toggle
        toggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('open');
            }
        });
    });

    // Close open dropdowns on outside click (mobile)
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.nav-dropdown.open').forEach(d => {
                if (!d.contains(e.target)) d.classList.remove('open');
            });
        }
    });

    // =========================================================================
    // 11. Form Validation
    // =========================================================================
    function validateForm(form) {
        let isValid = true;
        // Clear previous errors
        form.querySelectorAll('.field-error').forEach(el => el.remove());
        form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        form.querySelectorAll('[required]').forEach(field => {
            const value = field.value.trim();
            let errorMsg = '';

            if (!value) {
                errorMsg = 'This field is required.';
            } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                errorMsg = 'Please enter a valid email.';
            } else if (field.type === 'password' && value.length < 6) {
                errorMsg = 'Password must be at least 6 characters.';
            } else if (field.type === 'tel' && !/^[\d\s\-+()]{7,}$/.test(value)) {
                errorMsg = 'Please enter a valid phone number.';
            }

            if (errorMsg) {
                isValid = false;
                field.classList.add('input-error');
                const span = document.createElement('span');
                span.className = 'field-error';
                span.style.cssText = 'color:#dc3545;font-size:12px;display:block;margin-top:4px;';
                span.textContent = errorMsg;
                field.parentNode.insertBefore(span, field.nextSibling);
            }
        });

        // Confirm password match
        const pass = form.querySelector('input[name="password"]');
        const confirm = form.querySelector('input[name="confirm_password"]');
        if (pass && confirm && pass.value && confirm.value && pass.value !== confirm.value) {
            isValid = false;
            confirm.classList.add('input-error');
            const span = document.createElement('span');
            span.className = 'field-error';
            span.style.cssText = 'color:#dc3545;font-size:12px;display:block;margin-top:4px;';
            span.textContent = 'Passwords do not match.';
            confirm.parentNode.insertBefore(span, confirm.nextSibling);
        }

        return isValid;
    }

    // Attach to known forms
    ['checkoutForm', 'contactForm', 'loginForm', 'registerForm'].forEach(id => {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!validateForm(form)) e.preventDefault();
            });
        }
    });

    // =========================================================================
    // 13. Smooth Scroll for Anchor Links
    // =========================================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const id = anchor.getAttribute('href');
            if (id === '#' || id.length < 2) return;
            const target = document.querySelector(id);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // =========================================================================
    // 14. Scroll Animations (IntersectionObserver)
    // =========================================================================
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        animatedElements.forEach(el => observer.observe(el));
    }

    // =========================================================================
    // 15. Search Functionality
    // =========================================================================
    const searchForm = document.getElementById('searchForm') ||
                       document.querySelector('form[role="search"]');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            const input = searchForm.querySelector('input[name="q"], input[type="search"]');
            if (input && !input.value.trim()) {
                e.preventDefault();
                showToast('Please enter a search term.', 'info');
            }
        });
    }

    // =========================================================================
    // 16. Confirm Delete
    // =========================================================================
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // =========================================================================
    // 17. Admin Sidebar Toggle (Mobile)
    // =========================================================================
    const adminSidebarToggle = document.getElementById('adminSidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    if (adminSidebarToggle && adminSidebar) {
        adminSidebarToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('active');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (adminSidebar.classList.contains('active') &&
                !adminSidebar.contains(e.target) &&
                !adminSidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('active');
            }
        });
    }

    // =========================================================================
    // 18. Image Preview for Admin Product Forms
    // =========================================================================
    const imageInput = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showToast('Please select a valid image file.', 'error');
                imageInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    // =========================================================================
    // Wishlist Toggle
    // =========================================================================
    document.querySelectorAll('.wishlist-toggle').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const productId = btn.dataset.productId;
            const action = btn.classList.contains('active') ? 'remove' : 'add';

            try {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', action);
                formData.append('csrf_token', getCSRFToken());

                const res = await fetch(`${SITE_URL}/pages/wishlist_actions.php`, {
                    method: 'POST', body: formData
                });
                const data = await res.json();

                if (data.success) {
                    btn.classList.toggle('active');
                    showToast(data.message || (action === 'add' ? 'Added to wishlist!' : 'Removed from wishlist.'), 'success');
                } else {
                    showToast(data.message || 'Please log in to use the wishlist.', 'error');
                }
            } catch {
                showToast('Network error. Please try again.', 'error');
            }
        });
    });

}); // End DOMContentLoaded
