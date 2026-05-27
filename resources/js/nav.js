import { gsap } from 'gsap'

export function initNav() {
    navEntrance()
    scrollBehavior()
    logoHover()
    mobileMenu()
}

// ── Entrance animation ────────────────────────────────────────────────────────
function navEntrance() {
    const header  = document.getElementById('main-nav')
    const logo    = document.getElementById('nav-logo')
    const links   = document.querySelectorAll('#nav-links > li')
    const burger  = document.getElementById('nav-burger')
    if (!header) return

    // Delay on home so it enters after the preloader starts sliding away
    const isHome  = !!document.querySelector('[data-page="home"]')
    const delay   = isHome ? 2.8 : 0.25

    gsap.set([logo, links, burger], { opacity: 0, y: -16 })

    gsap.timeline({ delay })
        .to(logo,  { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' })
        .to(links, { opacity: 1, y: 0, duration: 0.55, stagger: 0.1, ease: 'power3.out' }, '-=0.4')
        .to(burger,{ opacity: 1, y: 0, duration: 0.4, ease: 'power3.out' }, '<')
}

// ── Scroll: glass effect + smart hide ─────────────────────────────────────────
function scrollBehavior() {
    const header = document.getElementById('main-nav')
    if (!header) return

    let lastY = 0

    const onScroll = () => {
        const y = window.scrollY

        // Glass panel after 60px
        if (y > 60) {
            header.classList.add('nav--scrolled')
        } else {
            header.classList.remove('nav--scrolled')
        }

        // Hide going down, reveal coming up (only after 120px)
        if (y > lastY && y > 120) {
            header.classList.add('nav--hidden')
        } else {
            header.classList.remove('nav--hidden')
        }

        lastY = y
    }

    window.addEventListener('scroll', onScroll, { passive: true })
}

// ── Logo: letter wave on hover ────────────────────────────────────────────────
function logoHover() {
    const logo    = document.getElementById('nav-logo')
    const letters = logo?.querySelectorAll('.nav-logo__l')
    if (!letters?.length) return

    logo.addEventListener('mouseenter', () => {
        gsap.to(letters, {
            y:        -5,
            color:    'var(--color-gold)',
            duration: 0.4,
            stagger:  { amount: 0.22, ease: 'sine.inOut' },
            ease:     'power2.out',
        })
    })

    logo.addEventListener('mouseleave', () => {
        gsap.to(letters, {
            y:        0,
            color:    'var(--color-ivory)',
            duration: 0.45,
            stagger:  { amount: 0.18, ease: 'sine.inOut', from: 'end' },
            ease:     'power2.inOut',
        })
    })
}

// ── Mobile menu ────────────────────────────────────────────────────────────────
function mobileMenu() {
    const burger  = document.getElementById('nav-burger')
    const drawer  = document.getElementById('nav-drawer')
    const items   = document.querySelectorAll('.nav-drawer__item')
    const lines   = burger?.querySelectorAll('.burger-line')
    if (!burger || !drawer) return

    let isOpen = false

    const openMenu = () => {
        isOpen = true
        document.body.classList.add('overflow-hidden')
        drawer.classList.remove('pointer-events-none')

        gsap.timeline()
            .to(drawer,  { opacity: 1, duration: 0.35, ease: 'power2.out' })
            .to(items,   { opacity: 1, x: 0, duration: 0.55,
                           stagger: 0.1, ease: 'expo.out' }, '-=0.1')

        // Animate burger to X
        gsap.to(lines[0], { rotate:  45, y:  7, duration: 0.3, ease: 'power2.inOut' })
        gsap.to(lines[1], { opacity: 0,         duration: 0.2, ease: 'power2.in'   })
        gsap.to(lines[2], { rotate: -45, y: -7, duration: 0.3, ease: 'power2.inOut' })
    }

    const closeMenu = () => {
        isOpen = false
        document.body.classList.remove('overflow-hidden')

        gsap.timeline({
            onComplete: () => drawer.classList.add('pointer-events-none'),
        })
            .to(items,   { opacity: 0, x: -20, duration: 0.3, stagger: { amount: 0.12, from: 'end' }, ease: 'power2.in' })
            .to(drawer,  { opacity: 0, duration: 0.3, ease: 'power2.in' }, '-=0.15')

        // Revert burger
        gsap.to(lines[0], { rotate: 0, y: 0, duration: 0.3, ease: 'power2.inOut' })
        gsap.to(lines[1], { opacity: 1,        duration: 0.3, ease: 'power2.out'  })
        gsap.to(lines[2], { rotate: 0, y: 0, duration: 0.3, ease: 'power2.inOut' })
    }

    burger.addEventListener('click', () => isOpen ? closeMenu() : openMenu())
    // Close on backdrop click
    drawer.addEventListener('click', (e) => {
        if (e.target === drawer) closeMenu()
    })

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) closeMenu()
    })

    // Pre-set for opening animation
    gsap.set(items,  { opacity: 0, x: -30 })
    gsap.set(close,  { opacity: 0, rotate: -90 })
    gsap.set(drawer, { opacity: 0 })
}
