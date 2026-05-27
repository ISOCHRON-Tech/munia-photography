import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

export function initLanding() {
    animateHero()
    revealOnScroll()
    animateWorkCards()
}

// ── Hero entrance ─────────────────────────────────────────────────────────────
function animateHero() {
    const eyebrow = document.getElementById('hero-eyebrow')
    const name    = document.getElementById('hero-name')
    const nameM   = name?.querySelector('.hero-name-m')
    const nameRest = name?.querySelector('.hero-name-rest')
    const tagline = document.getElementById('hero-tagline')
    const cta     = document.getElementById('hero-cta')
    const scroll  = document.getElementById('hero-scroll')
    const rule    = document.getElementById('hero-rule')

    if (!eyebrow) return

    // Set initial states
    gsap.set([eyebrow, tagline, cta, scroll], { opacity: 0, y: 22 })
    gsap.set(rule, { scaleX: 0, transformOrigin: 'left center' })
    gsap.set(nameM,   { opacity: 0, x: -30 })
    gsap.set(nameRest,{ opacity: 0, x: -10 })

    const tl = gsap.timeline({ delay: 0.1 })

    tl.to(eyebrow, { opacity: 1, y: 0, duration: 0.7, ease: 'power2.out' })
      .to(nameM,   { opacity: 1, x: 0,  duration: 0.8, ease: 'expo.out' }, '-=0.3')
      .to(nameRest,{ opacity: 1, x: 0,  duration: 0.6, ease: 'expo.out' }, '-=0.55')
      .to(tagline, { opacity: 1, y: 0,  duration: 0.7, ease: 'power2.out' }, '-=0.2')
      .to(cta,     { opacity: 1, y: 0,  duration: 0.6, ease: 'power2.out' }, '-=0.3')
      .to(rule,    { scaleX: 1,          duration: 0.8, ease: 'power3.inOut' }, '-=0.4')
      .to(scroll,  { opacity: 1, y: 0,  duration: 0.5, ease: 'power2.out' }, '-=0.3')
}

// ── Generic scroll reveals (.reveal-item) ────────────────────────────────────
function revealOnScroll() {
    const items = document.querySelectorAll('[data-page="home"] .reveal-item')
    if (!items.length) return

    gsap.set(items, { opacity: 0, y: 36 })

    ScrollTrigger.batch(items, {
        start: 'top 88%',
        onEnter: batch => gsap.to(batch, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power3.out',
            stagger: 0.1,
        }),
        once: true,
    })
}

// ── Work cards — staggered image scale-in ────────────────────────────────────
function animateWorkCards() {
    const cards = document.querySelectorAll('[data-page="home"] .work-card')
    if (!cards.length) return

    cards.forEach(card => {
        const img = card.querySelector('.work-img')
        if (!img) return

        gsap.fromTo(img,
            { scale: 1.08 },
            {
                scale: 1,
                duration: 1.2,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: card,
                    start: 'top 85%',
                    once: true,
                },
            }
        )
    })
}
