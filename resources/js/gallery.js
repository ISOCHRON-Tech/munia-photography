import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { animate } from 'motion'
import { createGalleryScene } from './three-scenes'

gsap.registerPlugin(ScrollTrigger)

// ─── Lightbox state ──────────────────────────────────────────────────────────
let lightboxActive = false
let backdrop       = null

export function initGallery() {
    initGalleryBg()
    animateGalleryHeader()
    revealOnScroll()
    initCardTilts()
    initLazyImages()
    initLightbox()
    initFilterPills()
}

// ─── Three.js ambient dust background ────────────────────────────────────────
function initGalleryBg() {
    const canvas = document.getElementById('gallery-bg')
    if (!canvas) return
    createGalleryScene(canvas)
    // Fade in gradually
    gsap.to(canvas, { opacity: 1, duration: 3.5, ease: 'power2.out', delay: 0.3 })
}

// ─── Gallery header character entrance ───────────────────────────────────────
function animateGalleryHeader() {
    const h1 = document.querySelector('[data-page="gallery"] h1')
    if (!h1) return

    const raw = h1.textContent.trim()
    h1.setAttribute('aria-label', raw)
    h1.innerHTML = [...raw].map(ch =>
        `<span class="gallery-char" style="display:inline-block">${ch === ' ' ? '&nbsp;' : ch}</span>`
    ).join('')

    const chars = h1.querySelectorAll('.gallery-char')
    animate(chars, {
        opacity:   [0, 1],
        transform: ['translateY(40px) skewY(4deg)', 'translateY(0px) skewY(0deg)'],
    }, {
        delay:    (_, i) => i * 0.045,
        duration: 0.65,
        easing:   [0.16, 1, 0.3, 1],
    })
}

// ─── 3D perspective card tilt ─────────────────────────────────────────────────
function initCardTilts(root = document) {
    const cards = root.querySelectorAll('.gallery-item')
    if (!cards.length) return

    cards.forEach(card => {
        // Mouse move: live tilt
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect()
            const cx   = (e.clientX - rect.left) / rect.width  - 0.5
            const cy   = (e.clientY - rect.top)  / rect.height - 0.5

            gsap.to(card, {
                rotateX:             -cy * 14,
                rotateY:              cx * 14,
                transformPerspective: 900,
                scale:                1.035,
                duration:             0.22,
                ease:                 'power2.out',
                overwrite:            'auto',
            })

            // Moving glint highlight via CSS custom property
            card.style.setProperty('--gx', `${(cx + 0.5) * 100}%`)
            card.style.setProperty('--gy', `${(cy + 0.5) * 100}%`)
        })

        // Mouse leave: spring back with elastic overshoot
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                rotateX:  0,
                rotateY:  0,
                scale:    1,
                duration: 0.85,
                ease:     'elastic.out(1, 0.65)',
                overwrite: 'auto',
            })
        })

        // Click pulse
        card.addEventListener('mousedown', () => {
            gsap.to(card, { scale: 0.97, duration: 0.12, overwrite: 'auto' })
        })
        card.addEventListener('mouseup', () => {
            gsap.to(card, { scale: 1.035, duration: 0.2, ease: 'power2.out', overwrite: 'auto' })
        })
    })
}

// ─── Scroll-triggered reveal ─────────────────────────────────────────────────
function revealOnScroll() {
    const cards = document.querySelectorAll('.gallery-item')
    if (!cards.length) return

    gsap.set(cards, { opacity: 0, y: 40 })

    ScrollTrigger.batch(cards, {
        onEnter: (batch) => {
            gsap.to(batch, {
                opacity: 1,
                y: 0,
                duration: 0.7,
                ease: 'power3.out',
                stagger: 0.07,
            })
        },
        start: 'top 92%',
        once: true,
    })
}

// ─── Native lazy load + LQIP reveal ─────────────────────────────────────────
function initLazyImages(root = document) {
    const imgs = root.querySelectorAll('img[data-src]')

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return
                const img = entry.target

                img.src     = img.dataset.src
                img.srcset  = img.dataset.srcset ?? ''
                img.sizes   = img.dataset.sizes  ?? '100vw'

                img.onload = () => {
                    img.dataset.loaded = 'true'
                    img.classList.add('loaded')
                }

                observer.unobserve(img)
            })
        },
        { rootMargin: '200px' }
    )

    imgs.forEach((img) => observer.observe(img))
}

// ─── Lightbox ────────────────────────────────────────────────────────────────
function initLightbox() {
    if (backdrop) return  // already initialised

    backdrop = createBackdrop()
    document.body.appendChild(backdrop)

    // Event delegation — handles cards loaded dynamically via AJAX filter too
    document.addEventListener('click', e => {
        const card = e.target.closest('.photo-card[data-full]')
        if (card) openLightbox(backdrop, card)
    })

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop || e.target.classList.contains('lb-close')) {
            closeLightbox(backdrop)
        }
    })

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightboxActive) closeLightbox(backdrop)
    })
}

function createBackdrop() {
    const div = document.createElement('div')
    div.className = 'lightbox-backdrop hidden'
    div.innerHTML = `
        <button class="lb-close absolute top-5 right-6 text-white/70 hover:text-white text-3xl leading-none z-10" aria-label="Close">&#x2715;</button>
        <div class="lb-inner flex flex-col items-center gap-4 max-w-5xl w-full px-4">
            <picture class="w-full">
                <source data-avif type="image/avif">
                <source data-webp type="image/webp">
                <img class="lb-img max-h-[85vh] max-w-full mx-auto object-contain rounded" alt="">
            </picture>
            <div class="lb-meta text-center text-white/60 text-sm space-y-1"></div>
        </div>
    `
    return div
}

function openLightbox(backdrop, card) {
    lightboxActive = true
    const { full, webp, avif, alt, camera, iso, aperture, shutter } = card.dataset

    const source1 = backdrop.querySelector('source[data-avif]')
    const source2 = backdrop.querySelector('source[data-webp]')
    const img     = backdrop.querySelector('.lb-img')
    const meta    = backdrop.querySelector('.lb-meta')

    if (avif)  source1.srcset = avif; else source1.removeAttribute('srcset')
    if (webp)  source2.srcset = webp; else source2.removeAttribute('srcset')
    img.src = full
    img.alt = alt ?? ''

    meta.innerHTML = [
        alt ? `<p class="text-white font-medium">${alt}</p>` : '',
        camera ? `<p>${camera}</p>` : '',
        [iso && `ISO ${iso}`, aperture, shutter].filter(Boolean).join(' &nbsp;·&nbsp; '),
    ].filter(Boolean).join('')

    backdrop.classList.remove('hidden')
    backdrop.classList.add('flex')

    gsap.fromTo(backdrop, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: 'power2.out' })
    gsap.fromTo(backdrop.querySelector('.lb-inner'), { scale: 0.94 }, { scale: 1, duration: 0.35, ease: 'power3.out' })

    document.body.style.overflow = 'hidden'
}

function closeLightbox(backdrop) {
    lightboxActive = false

    gsap.to(backdrop, {
        opacity: 0,
        duration: 0.25,
        ease: 'power2.in',
        onComplete: () => {
            backdrop.classList.add('hidden')
            backdrop.classList.remove('flex')
            document.body.style.overflow = ''
        },
    })
}

// ─── AJAX Category Filter ─────────────────────────────────────────────────────
function initFilterPills() {
    const nav = document.querySelector('[aria-label="Filter by category"]')
    if (!nav) return

    nav.addEventListener('click', async e => {
        const pill = e.target.closest('a.filter-pill')
        if (!pill) return
        e.preventDefault()

        const url    = pill.href
        const grid   = document.getElementById('gallery-grid')
        const pgWrap = document.getElementById('gallery-pagination')
        if (!grid) { window.location.href = url; return }

        // Swap active pill state immediately
        nav.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('filter-pill--active'))
        pill.classList.add('filter-pill--active')

        // Fade out grid while fetching
        gsap.to(grid, { opacity: 0, duration: 0.2, ease: 'power2.in' })

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
            if (!res.ok) throw new Error()
            const { grid: gridHtml, pagination: paginationHtml } = await res.json()

            // Swap content
            grid.innerHTML = gridHtml
            if (pgWrap) pgWrap.innerHTML = paginationHtml ?? ''

            // Re-init interactions for newly inserted cards
            initCardTilts(grid)
            initLazyImages(grid)

            // Stagger fade-in
            const newCards = grid.querySelectorAll('.gallery-item')
            gsap.fromTo(newCards,
                { opacity: 0, y: 20 },
                { opacity: 1, y: 0, duration: 0.5, stagger: 0.04, ease: 'power2.out' }
            )

            // Reflect URL change without a full reload
            history.pushState({}, '', url)
        } catch {
            window.location.href = url  // graceful fallback
        }
    })
}
