import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { CSSPlugin } from 'gsap/CSSPlugin'
import { animate, stagger } from 'motion'
import { createStoriesScene } from './three-scenes'

gsap.registerPlugin(ScrollTrigger, CSSPlugin)

// ─────────────────────────────────────────────────────────────────────────────
export function initStoriesIndex() {
    animateHeader()
    initThreeScene()
    revealCards()
}

// ─── Animated header: character-split h1 with spring stagger ─────────────────
function animateHeader() {
    const section = document.querySelector('[data-page="stories"]')
    if (!section) return

    const h1  = section.querySelector('h1')
    const sub = section.querySelector('header p')

    if (!h1) return

    // Split into individual characters
    const raw = h1.textContent
    h1.setAttribute('aria-label', raw)
    h1.setAttribute('aria-hidden', 'false')
    h1.innerHTML = [...raw].map(ch =>
        `<span class="story-char">${ch === ' ' ? '&nbsp;' : ch}</span>`
    ).join('')

    const chars = h1.querySelectorAll('.story-char')

    // Start hidden
    gsap.set(chars, { opacity: 0, y: 48, rotateY: -25 })

    // Animate in from center outward using motion stagger
    animate(chars, { opacity: 1, transform: ['translateY(48px) rotateY(-25deg)', 'translateY(0px) rotateY(0deg)'] }, {
        delay:   stagger(0.038, { from: 'center' }),
        duration: 0.65,
        easing:  [0.16, 1, 0.3, 1],
    })

    if (sub) {
        gsap.fromTo(sub,
            { opacity: 0, y: 16 },
            { opacity: 1, y: 0, delay: 0.7, duration: 0.7, ease: 'power2.out' }
        )
    }
}

// ─── Three.js wave mesh in header ────────────────────────────────────────────
function initThreeScene() {
    const canvas = document.getElementById('stories-bg')
    if (!canvas) return
    createStoriesScene(canvas)
}

// ─── Story card reveal + spring hover ────────────────────────────────────────
function revealCards() {
    const cards = document.querySelectorAll('.story-card')
    if (!cards.length) return

    // Scroll-triggered reveal using native IntersectionObserver
    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return
                const el  = entry.target
                const idx = [...cards].indexOf(el)
                animate(
                    el,
                    { opacity: [0, 1], transform: ['translateY(52px)', 'translateY(0px)'] },
                    { duration: 0.72, delay: (idx % 3) * 0.1, easing: [0.16, 1, 0.3, 1] }
                )
                revealObserver.unobserve(el)
            })
        },
        { threshold: 0.18 }
    )
    cards.forEach(card => {
        card.style.opacity = '0'
        revealObserver.observe(card)
    })

    // Spring hover: lift + breathe via GSAP elastic
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                y: -8,
                scale: 1.018,
                duration: 0.5,
                ease: 'elastic.out(1, 0.55)',
                overwrite: 'auto',
            })
            gsap.to(card.querySelector('img'), {
                scale: 1.06,
                duration: 0.7,
                ease: 'power2.out',
                overwrite: 'auto',
            })
        })

        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                y: 0,
                scale: 1,
                duration: 0.65,
                ease: 'elastic.out(1, 0.55)',
                overwrite: 'auto',
            })
            gsap.to(card.querySelector('img'), {
                scale: 1,
                duration: 0.6,
                ease: 'power2.out',
                overwrite: 'auto',
            })
        })
    })
}
