/**
 * js/main.js
 * -----------------------------------------------
 * Shared JavaScript — runs on both index.php and player.php
 * Features:
 *  - Navbar scroll behaviour (transparent → solid)
 *  - Mobile nav hamburger toggle (genre links)
 *  - Search bar keyboard shortcut (press '/' to focus)
 *  - Lazy-load intersection observer for thumbnails
 * -----------------------------------------------
 */

(function () {
    'use strict';

    /* ─── Navbar: add .navbar--scrolled class when page is scrolled ──────── */
    const navbar = document.getElementById('navbar');

    if (navbar) {
        const handleScroll = () => {
            if (window.scrollY > 60) {
                navbar.classList.add('navbar--scrolled');
            } else {
                navbar.classList.remove('navbar--scrolled');
            }
        };

        // Run once on load (in case page is refreshed mid-scroll)
        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });
    }

    /* ─── Search: press "/" to focus the search input ────────────────────── */
    const searchInput = document.getElementById('search-input');

    if (searchInput) {
        document.addEventListener('keydown', (e) => {
            // Don't steal focus if user is already in an input
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            // Escape to blur search
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.blur();
            }
        });
    }

    /* ─── Like / Watchlist buttons (UI-only toggle, no backend) ─────────── */
    const likeBtn      = document.getElementById('like-btn');
    const watchlistBtn = document.getElementById('watchlist-btn');
    const likeCount    = document.getElementById('like-count');

    if (likeBtn && likeCount) {
        let liked = false;
        likeBtn.addEventListener('click', () => {
            liked = !liked;
            likeBtn.classList.toggle('active', liked);
            likeCount.textContent = liked ? '1' : '0';

            // Animate button
            likeBtn.style.transform = 'scale(1.2)';
            setTimeout(() => { likeBtn.style.transform = ''; }, 200);
        });
    }

    if (watchlistBtn) {
        let inList = false;
        watchlistBtn.addEventListener('click', () => {
            inList = !inList;
            watchlistBtn.classList.toggle('active', inList);

            // Update icon and label
            const icon = watchlistBtn.querySelector('svg');
            if (inList) {
                // Show checkmark
                icon.innerHTML = '<polyline points="20 6 9 17 4 12"/>';
                watchlistBtn.lastChild.textContent = ' Saved';
            } else {
                icon.innerHTML = '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>';
                watchlistBtn.lastChild.textContent = ' My List';
            }
        });
    }

    /* ─── Smooth card entrance animation (Intersection Observer) ─────────── */
    const videoCards = document.querySelectorAll('.video-card, .related-card');

    if (videoCards.length > 0 && 'IntersectionObserver' in window) {
        // Add initial hidden state via inline style
        videoCards.forEach((card, i) => {
            card.style.opacity    = '0';
            card.style.transform  = 'translateY(20px)';
            card.style.transition = `opacity 0.4s ease ${i * 40}ms, transform 0.4s ease ${i * 40}ms`;
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity   = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target); // Animate once only
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        videoCards.forEach((card) => observer.observe(card));
    }

})();
