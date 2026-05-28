import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { CSSPlugin } from 'gsap/CSSPlugin'

gsap.registerPlugin(ScrollTrigger, CSSPlugin)

export function initStory() {
    parallaxBanner()
    revealSections()
    progressBar()
}

// ─── Parallax hero banner ────────────────────────────────────────────────────
function parallaxBanner() {
    const img = document.querySelector('.parallax-img')
    if (!img) return

    gsap.to(img, {
        yPercent: 15,
        ease: 'none',
        scrollTrigger: {
            trigger: img.closest('.parallax-banner'),
            start: 'top top',
            end: 'bottom top',
            scrub: 1.2,
        },
    })
}

// ─── Section text fade-ins ───────────────────────────────────────────────────
function revealSections() {
    const paragraphs = document.querySelectorAll('.story-body p, .story-body h2, .story-body h3, .story-body blockquote')

    gsap.set(paragraphs, { opacity: 0, y: 25 })

    ScrollTrigger.batch(paragraphs, {
        onEnter: (batch) => {
            gsap.to(batch, {
                opacity: 1,
                y: 0,
                duration: 0.75,
                ease: 'power2.out',
                stagger: 0.06,
            })
        },
        start: 'top 90%',
        once: true,
    })
}

// ─── Reading progress bar ────────────────────────────────────────────────────
function progressBar() {
    const bar = document.getElementById('reading-progress')
    if (!bar) return

    gsap.to(bar, {
        scaleX: 1,
        ease: 'none',
        scrollTrigger: {
            trigger: document.body,
            start: 'top top',
            end: 'bottom bottom',
            scrub: 0.3,
        },
    })

    gsap.set(bar, { scaleX: 0, transformOrigin: 'left center' })
}
