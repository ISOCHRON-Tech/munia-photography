import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

// ─── Lightbox state ──────────────────────────────────────────────────────────
let lightboxActive = false

export function initGallery() {
    revealOnScroll()
    initLazyImages()
    initLightbox()
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
function initLazyImages() {
    const imgs = document.querySelectorAll('img[data-src]')

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
    const cards = document.querySelectorAll('.photo-card[data-full]')
    if (!cards.length) return

    const backdrop = createBackdrop()
    document.body.appendChild(backdrop)

    cards.forEach((card) => {
        card.addEventListener('click', () => openLightbox(backdrop, card))
    })

    // Close on backdrop click or Escape key
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
