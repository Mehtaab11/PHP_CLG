/**
 * js/player.js
 * -----------------------------------------------
 * Custom HTML5 Video Player Controls
 * Features:
 *  - Play / Pause (click + spacebar)
 *  - Seek bar with drag support + time tooltip
 *  - Buffered progress
 *  - Volume slider + mute toggle (M key)
 *  - Skip ±10 seconds (← → arrow keys)
 *  - Playback speed menu
 *  - Fullscreen toggle (F key)
 *  - Picture-in-Picture (P key)
 *  - Auto-hide controls after 3s of inactivity
 *  - Center ripple animation on click
 *  - Keyboard shortcuts summary
 * -----------------------------------------------
 */

(function () {
    'use strict';

    /* ═══════════════════════════════════════════════════════════════════════
       1. ELEMENT REFERENCES
    ═══════════════════════════════════════════════════════════════════════ */
    const video           = document.getElementById('main-video');
    const playerWrapper   = document.getElementById('player-wrapper');
    const customControls  = document.getElementById('custom-controls');

    // If player elements don't exist (not on player page), bail out
    if (!video || !playerWrapper) return;

    const playPauseBtn    = document.getElementById('play-pause-btn');
    const playIcon        = document.getElementById('play-icon');
    const pauseIcon       = document.getElementById('pause-icon');

    const skipBackBtn     = document.getElementById('skip-back-btn');
    const skipFwdBtn      = document.getElementById('skip-fwd-btn');

    const muteBtn         = document.getElementById('mute-btn');
    const volIcon         = document.getElementById('vol-icon');
    const muteIcon        = document.getElementById('mute-icon');
    const volumeSlider    = document.getElementById('volume-slider');

    const currentTimeEl   = document.getElementById('current-time');
    const totalTimeEl     = document.getElementById('total-time');
    const timeDisplay     = document.getElementById('time-display');

    const progressContainer = document.getElementById('progress-container');
    const progressPlayed    = document.getElementById('progress-played');
    const progressBuffered  = document.getElementById('progress-buffered');
    const progressThumb     = document.getElementById('progress-thumb');
    const seekTooltip       = document.getElementById('seek-tooltip');

    const speedBtn        = document.getElementById('speed-btn');
    const speedMenu       = document.getElementById('speed-menu');
    const speedLabel      = document.getElementById('speed-label');
    const speedOptions    = document.querySelectorAll('.speed-option');

    const pipBtn          = document.getElementById('pip-btn');
    const fullscreenBtn   = document.getElementById('fullscreen-btn');
    const expandIcon      = document.getElementById('expand-icon');
    const collapseIcon    = document.getElementById('collapse-icon');

    const centerOverlay   = document.getElementById('center-overlay');
    const centerRipple    = document.getElementById('center-ripple');

    /* ═══════════════════════════════════════════════════════════════════════
       2. STATE
    ═══════════════════════════════════════════════════════════════════════ */
    let isDraggingSeek   = false;   // Whether user is dragging seek bar
    let hideControlsTimer = null;   // Timer ID for auto-hiding controls
    let lastVolume       = 1;       // Remember volume before mute

    /* ═══════════════════════════════════════════════════════════════════════
       3. UTILITIES
    ═══════════════════════════════════════════════════════════════════════ */

    /**
     * Format seconds → "h:mm:ss" or "m:ss"
     * @param {number} secs
     * @returns {string}
     */
    function formatTime(secs) {
        if (isNaN(secs) || secs < 0) return '0:00';
        const h = Math.floor(secs / 3600);
        const m = Math.floor((secs % 3600) / 60);
        const s = Math.floor(secs % 60);
        const mm = String(m).padStart(h > 0 ? 2 : 1, '0');
        const ss = String(s).padStart(2, '0');
        return h > 0 ? `${h}:${mm}:${ss}` : `${mm}:${ss}`;
    }

    /**
     * Get the fraction (0–1) of a pointer event within the progress bar
     * @param {MouseEvent|TouchEvent} e
     * @returns {number}
     */
    function getSeekFraction(e) {
        const rect     = progressContainer.getBoundingClientRect();
        const clientX  = e.touches ? e.touches[0].clientX : e.clientX;
        const fraction = (clientX - rect.left) / rect.width;
        return Math.max(0, Math.min(1, fraction));
    }

    /* ═══════════════════════════════════════════════════════════════════════
       4. PLAY / PAUSE
    ═══════════════════════════════════════════════════════════════════════ */

    function togglePlay() {
        if (video.paused || video.ended) {
            video.play();
        } else {
            video.pause();
        }
    }

    // Update UI to reflect play state
    function updatePlayUI(isPlaying) {
        playIcon.style.display  = isPlaying ? 'none' : 'block';
        pauseIcon.style.display = isPlaying ? 'block' : 'none';
        playPauseBtn.setAttribute('aria-label', isPlaying ? 'Pause' : 'Play');
        playerWrapper.classList.toggle('paused', !isPlaying);
    }

    // Center ripple animation
    function showRipple() {
        centerRipple.classList.remove('ripple-active');
        // Force reflow to restart animation
        void centerRipple.offsetWidth;
        centerRipple.classList.add('ripple-active');
        setTimeout(() => centerRipple.classList.remove('ripple-active'), 400);
    }

    // Click on video area → toggle play + show ripple
    playerWrapper.addEventListener('click', (e) => {
        // Don't toggle if clicking a control button
        if (e.target.closest('#custom-controls')) return;
        togglePlay();
        showRipple();
    });

    playPauseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        togglePlay();
    });

    video.addEventListener('play',  () => updatePlayUI(true));
    video.addEventListener('pause', () => updatePlayUI(false));
    video.addEventListener('ended', () => {
        updatePlayUI(false);
        showControls(true); // Keep controls visible at end
    });

    /* ═══════════════════════════════════════════════════════════════════════
       5. PROGRESS / SEEK BAR
    ═══════════════════════════════════════════════════════════════════════ */

    // Update played + buffered bars + thumb position + time display
    function updateProgress() {
        if (isDraggingSeek || !video.duration) return;

        const fraction = video.currentTime / video.duration;

        progressPlayed.style.width = (fraction * 100) + '%';
        progressThumb.style.left   = (fraction * 100) + '%';
        currentTimeEl.textContent  = formatTime(video.currentTime);

        // Buffered range (use the last buffered end)
        if (video.buffered.length > 0) {
            const buffEnd        = video.buffered.end(video.buffered.length - 1);
            const buffFraction   = buffEnd / video.duration;
            progressBuffered.style.width = (buffFraction * 100) + '%';
        }
    }

    // Update total time when metadata loads
    video.addEventListener('loadedmetadata', () => {
        totalTimeEl.textContent = formatTime(video.duration);
    });

    video.addEventListener('timeupdate',  updateProgress);
    video.addEventListener('progress',    updateProgress); // buffered updates

    // ── Seek bar interaction ─────────────────────────────────────────────

    function seekTo(fraction) {
        if (!video.duration) return;
        video.currentTime             = fraction * video.duration;
        progressPlayed.style.width    = (fraction * 100) + '%';
        progressThumb.style.left      = (fraction * 100) + '%';
        currentTimeEl.textContent     = formatTime(video.currentTime);
    }

    function updateTooltip(e) {
        const fraction    = getSeekFraction(e);
        const rect        = progressContainer.getBoundingClientRect();
        const clientX     = e.touches ? e.touches[0].clientX : e.clientX;
        const posX        = Math.max(0, Math.min(clientX - rect.left, rect.width));

        seekTooltip.style.left    = posX + 'px';
        seekTooltip.textContent   = formatTime(fraction * (video.duration || 0));
    }

    // Mouse / Touch events on progress bar
    progressContainer.addEventListener('mousedown', (e) => {
        isDraggingSeek = true;
        seekTo(getSeekFraction(e));
        updateTooltip(e);
    });

    progressContainer.addEventListener('mousemove', (e) => {
        updateTooltip(e);
        if (isDraggingSeek) seekTo(getSeekFraction(e));
    });

    progressContainer.addEventListener('touchstart', (e) => {
        isDraggingSeek = true;
        seekTo(getSeekFraction(e));
    }, { passive: true });

    progressContainer.addEventListener('touchmove', (e) => {
        if (isDraggingSeek) seekTo(getSeekFraction(e));
    }, { passive: true });

    // Stop dragging on mouseup anywhere
    document.addEventListener('mouseup', () => { isDraggingSeek = false; });
    document.addEventListener('touchend', () => { isDraggingSeek = false; });

    // Keyboard seek on focused progress bar
    progressContainer.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') { video.currentTime += 5; e.preventDefault(); }
        if (e.key === 'ArrowLeft')  { video.currentTime -= 5; e.preventDefault(); }
    });

    /* ═══════════════════════════════════════════════════════════════════════
       6. SKIP ±10 SECONDS
    ═══════════════════════════════════════════════════════════════════════ */
    skipBackBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        video.currentTime = Math.max(0, video.currentTime - 10);
    });

    skipFwdBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        video.currentTime = Math.min(video.duration || 0, video.currentTime + 10);
    });

    /* ═══════════════════════════════════════════════════════════════════════
       7. VOLUME + MUTE
    ═══════════════════════════════════════════════════════════════════════ */

    function updateVolumeUI() {
        const muted = video.muted || video.volume === 0;
        volIcon.style.display  = muted ? 'none'  : 'block';
        muteIcon.style.display = muted ? 'block' : 'none';
        muteBtn.setAttribute('aria-label', muted ? 'Unmute' : 'Mute');
        volumeSlider.value = muted ? 0 : video.volume;
    }

    muteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (video.muted || video.volume === 0) {
            video.muted  = false;
            video.volume = lastVolume || 0.5;
        } else {
            lastVolume   = video.volume;
            video.muted  = true;
        }
        updateVolumeUI();
    });

    volumeSlider.addEventListener('input', (e) => {
        e.stopPropagation();
        video.volume = parseFloat(volumeSlider.value);
        video.muted  = video.volume === 0;
        if (video.volume > 0) lastVolume = video.volume;
        updateVolumeUI();
    });

    video.addEventListener('volumechange', updateVolumeUI);

    /* ═══════════════════════════════════════════════════════════════════════
       8. PLAYBACK SPEED
    ═══════════════════════════════════════════════════════════════════════ */

    // Toggle speed menu
    speedBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isHidden = speedMenu.hidden;
        speedMenu.hidden = !isHidden;
        speedBtn.setAttribute('aria-expanded', String(!isHidden));
    });

    // Select a speed
    speedOptions.forEach((option) => {
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            const speed      = parseFloat(option.dataset.speed);
            video.playbackRate = speed;
            speedLabel.textContent = speed + '×';

            // Update active state
            speedOptions.forEach((o) => {
                o.classList.toggle('active', o === option);
                o.setAttribute('aria-selected', o === option ? 'true' : 'false');
            });

            speedMenu.hidden = true;
        });

        // Keyboard select
        option.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); option.click(); }
        });
    });

    // Close speed menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!speedBtn.contains(e.target) && !speedMenu.contains(e.target)) {
            speedMenu.hidden = true;
        }
    });

    /* ═══════════════════════════════════════════════════════════════════════
       9. PICTURE-IN-PICTURE
    ═══════════════════════════════════════════════════════════════════════ */

    if (document.pictureInPictureEnabled) {
        pipBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            try {
                if (document.pictureInPictureElement) {
                    await document.exitPictureInPicture();
                } else {
                    await video.requestPictureInPicture();
                }
            } catch (err) {
                console.warn('PiP not supported or blocked:', err);
            }
        });
    } else {
        pipBtn.style.display = 'none'; // Hide if not supported
    }

    /* ═══════════════════════════════════════════════════════════════════════
       10. FULLSCREEN
    ═══════════════════════════════════════════════════════════════════════ */

    function isFullscreen() {
        return !!(
            document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.mozFullScreenElement
        );
    }

    function updateFullscreenUI() {
        const fs = isFullscreen();
        expandIcon.style.display   = fs ? 'none'  : 'block';
        collapseIcon.style.display = fs ? 'block' : 'none';
        fullscreenBtn.setAttribute('aria-label', fs ? 'Exit Fullscreen' : 'Fullscreen');
    }

    async function toggleFullscreen() {
        try {
            if (!isFullscreen()) {
                if (playerWrapper.requestFullscreen) {
                    await playerWrapper.requestFullscreen();
                } else if (playerWrapper.webkitRequestFullscreen) {
                    await playerWrapper.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    await document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    await document.webkitExitFullscreen();
                }
            }
        } catch (err) {
            console.warn('Fullscreen error:', err);
        }
    }

    fullscreenBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleFullscreen(); });

    document.addEventListener('fullscreenchange',       updateFullscreenUI);
    document.addEventListener('webkitfullscreenchange', updateFullscreenUI);

    /* ═══════════════════════════════════════════════════════════════════════
       11. AUTO-HIDE CONTROLS
    ═══════════════════════════════════════════════════════════════════════ */

    function showControls(keepVisible = false) {
        playerWrapper.classList.add('controls-visible');
        clearTimeout(hideControlsTimer);
        if (!keepVisible && !video.paused) {
            hideControlsTimer = setTimeout(() => {
                if (!video.paused) {
                    playerWrapper.classList.remove('controls-visible');
                }
            }, 3000);
        }
    }

    playerWrapper.addEventListener('mousemove', () => showControls());
    playerWrapper.addEventListener('touchstart', () => showControls(), { passive: true });

    // Always show controls when paused
    video.addEventListener('pause', () => showControls(true));
    video.addEventListener('play',  () => showControls());

    /* ═══════════════════════════════════════════════════════════════════════
       12. KEYBOARD SHORTCUTS
    ═══════════════════════════════════════════════════════════════════════ */

    document.addEventListener('keydown', (e) => {
        // Don't interfere if user is typing in an input
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (!video) return;

        showControls(); // Show controls on any key press

        switch (e.key) {
            case ' ':          // Space → play/pause
            case 'k':          // K → play/pause
                e.preventDefault();
                togglePlay();
                showRipple();
                break;

            case 'ArrowRight': // → skip +10s
            case 'l':
                e.preventDefault();
                video.currentTime = Math.min(video.duration || 0, video.currentTime + 10);
                break;

            case 'ArrowLeft':  // ← skip −10s
            case 'j':
                e.preventDefault();
                video.currentTime = Math.max(0, video.currentTime - 10);
                break;

            case 'ArrowUp':    // ↑ volume up
                e.preventDefault();
                video.volume = Math.min(1, video.volume + 0.1);
                updateVolumeUI();
                break;

            case 'ArrowDown':  // ↓ volume down
                e.preventDefault();
                video.volume = Math.max(0, video.volume - 0.1);
                updateVolumeUI();
                break;

            case 'm':          // M → mute
            case 'M':
                e.preventDefault();
                muteBtn.click();
                break;

            case 'f':          // F → fullscreen
            case 'F':
                e.preventDefault();
                toggleFullscreen();
                break;

            case 'p':          // P → picture-in-picture
            case 'P':
                e.preventDefault();
                if (pipBtn) pipBtn.click();
                break;

            case '0': case '1': case '2': case '3': case '4':
            case '5': case '6': case '7': case '8': case '9':
                // Number keys → seek to % of video
                if (video.duration) {
                    video.currentTime = (parseInt(e.key, 10) / 10) * video.duration;
                }
                break;
        }
    });

    /* ═══════════════════════════════════════════════════════════════════════
       13. DOUBLE-CLICK TO FULLSCREEN
    ═══════════════════════════════════════════════════════════════════════ */
    playerWrapper.addEventListener('dblclick', (e) => {
        if (!e.target.closest('#custom-controls')) {
            toggleFullscreen();
        }
    });

    /* ═══════════════════════════════════════════════════════════════════════
       14. LOADING STATE
    ═══════════════════════════════════════════════════════════════════════ */
    video.addEventListener('waiting', () => {
        playerWrapper.classList.add('loading');
    });

    video.addEventListener('canplay', () => {
        playerWrapper.classList.remove('loading');
    });

    /* ═══════════════════════════════════════════════════════════════════════
       15. INITIALISE UI STATE
    ═══════════════════════════════════════════════════════════════════════ */
    updatePlayUI(!video.paused);
    updateVolumeUI();

    // Show controls on load
    showControls(true);
    setTimeout(() => {
        if (!video.paused) playerWrapper.classList.remove('controls-visible');
    }, 2500);

    console.log('🎬 Player.js loaded. Keyboard shortcuts: SPACE=play/pause, ←/→=seek, ↑↓=volume, M=mute, F=fullscreen, P=pip, 0-9=jump');

})();
