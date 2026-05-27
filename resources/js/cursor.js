import { animate } from 'motion'

// ─────────────────────────────────────────────────────────────────────────────
// Magnetic spring cursor — desktop only
// ─────────────────────────────────────────────────────────────────────────────

export function initCursor() {
    // Skip on touch/hybrid devices
    if (window.matchMedia('(pointer: coarse)').matches) return

    const dot  = document.getElementById('cursor-dot')
    const ring = document.getElementById('cursor-ring')
    if (!dot || !ring) return

    // Current positions
    let mouseX = -200, mouseY = -200
    let ringX  = -200, ringY  = -200
    let expanded = false

    // ── Dot follows mouse exactly ──────────────────────────────────────────
    window.addEventListener('mousemove', e => {
        mouseX = e.clientX
        mouseY = e.clientY
        dot.style.transform = `translate(${mouseX}px,${mouseY}px) translate(-50%,-50%)`
    })

    // ── Ring lags with exponential lerp ───────────────────────────────────
    function trackRing() {
        ringX += (mouseX - ringX) * 0.1
        ringY += (mouseY - ringY) * 0.1
        ring.style.transform = `translate(${ringX}px,${ringY}px) translate(-50%,-50%)`
        requestAnimationFrame(trackRing)
    }
    requestAnimationFrame(trackRing)

    // ── Interactive targets ────────────────────────────────────────────────
    const TARGETS = 'a, button, .gallery-item, .filter-pill, .story-card, .nav-link, #nav-burger, #nav-logo, [role="button"]'

    document.addEventListener('mouseover', e => {
        if (!expanded && e.target.closest(TARGETS)) {
            expanded = true
            animate(ring, { scale: 2.4, opacity: 0.35 }, {
                duration: 0.3,
                easing: [0.23, 1, 0.32, 1],
            })
            animate(dot, { scale: 0.35, opacity: 0.5 }, { duration: 0.2 })
        }
    })

    document.addEventListener('mouseout', e => {
        if (expanded) {
            const rel = e.relatedTarget
            if (!rel || !rel.closest(TARGETS)) {
                expanded = false
                animate(ring, { scale: 1, opacity: 0.7 }, {
                    duration: 0.4,
                    easing: [0.23, 1, 0.32, 1],
                })
                animate(dot, { scale: 1, opacity: 1 }, { duration: 0.25 })
            }
        }
    })

    // ── Press feedback ─────────────────────────────────────────────────────
    document.addEventListener('mousedown', () => {
        animate(ring, { scale: expanded ? 1.8 : 0.75 }, { duration: 0.12 })
        animate(dot,  { scale: 0.5  }, { duration: 0.12 })
    })

    document.addEventListener('mouseup', () => {
        animate(ring, { scale: expanded ? 2.4 : 1 }, {
            duration: 0.35,
            easing: [0.34, 1.56, 0.64, 1],
        })
        animate(dot, { scale: expanded ? 0.35 : 1 }, { duration: 0.2 })
    })

    // Fade in
    animate(dot,  { opacity: [0, 1] }, { duration: 0.4, delay: 0.2 })
    animate(ring, { opacity: [0, 0.7] }, { duration: 0.5, delay: 0.3 })
}
