import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import * as THREE from 'three'

gsap.registerPlugin(ScrollTrigger)

export function initLanding() {
    runPreloader()
}

// ── Three.js Bokeh Preloader ───────────────────────────────────────────────────
function runPreloader() {
    const el      = document.getElementById('hp-preloader')
    const canvas  = document.getElementById('hp-preloader-canvas')
    const letters = document.querySelectorAll('.hp-pre-letter')
    const fill    = document.getElementById('hp-preloader-fill')
    const sub     = document.getElementById('hp-preloader-sub')

    if (!el) {
        initAnimations()
        return
    }

    // ── Three.js scene ─────────────────────────────────────────────────────
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true })
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
    renderer.setSize(window.innerWidth, window.innerHeight)

    const scene  = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100)
    camera.position.z = 4

    // ── Bokeh sprite texture (soft radial gradient) ────────────────────────
    const spriteCanvas = document.createElement('canvas')
    spriteCanvas.width  = 128
    spriteCanvas.height = 128
    const ctx  = spriteCanvas.getContext('2d')
    const grad = ctx.createRadialGradient(64, 64, 0, 64, 64, 64)
    grad.addColorStop(0,    'rgba(201,168,76,1)')
    grad.addColorStop(0.35, 'rgba(201,168,76,0.55)')
    grad.addColorStop(0.7,  'rgba(245,240,235,0.12)')
    grad.addColorStop(1,    'rgba(0,0,0,0)')
    ctx.fillStyle = grad
    ctx.fillRect(0, 0, 128, 128)
    const bokehTex = new THREE.CanvasTexture(spriteCanvas)

    // ── Particle system ────────────────────────────────────────────────────
    const COUNT   = 380
    const pos     = new Float32Array(COUNT * 3)
    const vel     = new Float32Array(COUNT * 3)   // drift velocity
    const phases  = new Float32Array(COUNT)        // oscillation phase
    const opacs   = new Float32Array(COUNT)        // per-particle base opacity
    const scales  = new Float32Array(COUNT)        // per-particle size

    for (let i = 0; i < COUNT; i++) {
        const r   = 5 + Math.random() * 3
        const phi = Math.random() * Math.PI * 2
        const th  = Math.acos(2 * Math.random() - 1)
        pos[i * 3]     = r * Math.sin(th) * Math.cos(phi)
        pos[i * 3 + 1] = r * Math.sin(th) * Math.sin(phi)
        pos[i * 3 + 2] = (Math.random() - 0.5) * 5

        vel[i * 3]     = (Math.random() - 0.5) * 0.004
        vel[i * 3 + 1] = (Math.random() - 0.5) * 0.004
        vel[i * 3 + 2] = 0

        phases[i] = Math.random() * Math.PI * 2
        opacs[i]  = 0.25 + Math.random() * 0.55
        scales[i] = 0.06 + Math.random() * 0.28
    }

    const geo = new THREE.BufferGeometry()
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3))

    const mat = new THREE.PointsMaterial({
        map:          bokehTex,
        transparent:  true,
        depthWrite:   false,
        blending:     THREE.AdditiveBlending,
        sizeAttenuation: true,
        size:         0.22,
        opacity:      0,
    })

    const points = new THREE.Points(geo, mat)
    scene.add(points)

    // ── Mouse parallax ────────────────────────────────────────────────────
    let mx = 0, my = 0
    const onMouse = (e) => {
        mx = (e.clientX / window.innerWidth  - 0.5) * 2
        my = (e.clientY / window.innerHeight - 0.5) * 2
    }
    window.addEventListener('mousemove', onMouse)

    // ── Resize handler ─────────────────────────────────────────────────────
    const onResize = () => {
        camera.aspect = window.innerWidth / window.innerHeight
        camera.updateProjectionMatrix()
        renderer.setSize(window.innerWidth, window.innerHeight)
    }
    window.addEventListener('resize', onResize)

    // ── Render loop ────────────────────────────────────────────────────────
    let rafId
    let phase = 'idle'   // idle → burst → collapse → done
    let t     = 0

    function tick() {
        rafId = requestAnimationFrame(tick)
        t += 0.012

        const posArr = geo.attributes.position.array

        for (let i = 0; i < COUNT; i++) {
            const ox = phases[i]
            // gentle sinusoidal drift
            posArr[i * 3]     += vel[i * 3]     + Math.sin(t * 0.7  + ox) * 0.0012
            posArr[i * 3 + 1] += vel[i * 3 + 1] + Math.cos(t * 0.55 + ox) * 0.0012

            if (phase === 'collapse') {
                // pull particles toward center at increasing speed
                posArr[i * 3]     *= 0.965
                posArr[i * 3 + 1] *= 0.965
                posArr[i * 3 + 2] *= 0.965
            }
        }
        geo.attributes.position.needsUpdate = true

        // subtle camera drift following mouse
        camera.position.x += (mx * 0.18 - camera.position.x) * 0.04
        camera.position.y += (-my * 0.12 - camera.position.y) * 0.04
        camera.lookAt(scene.position)

        // slow rotation
        points.rotation.z += 0.0008
        points.rotation.x  = Math.sin(t * 0.15) * 0.06

        renderer.render(scene, camera)
    }
    tick()

    // ── Text entrance animation ─────────────────────────────────────────────
    gsap.set(letters, { opacity: 0, y: 50, rotateX: -60 })
    gsap.set(sub, { opacity: 0, y: 14 })
    gsap.set(fill, { scaleX: 0, transformOrigin: 'left center' })

    const tl = gsap.timeline({
        onComplete: () => exitPreloader(),
    })

    // fade in the particle cloud
    tl.to(mat, { opacity: 0.85, duration: 1.0, ease: 'power2.out' })

    // letters stagger in
    tl.to(letters, {
        opacity:  1,
        y:        0,
        rotateX:  0,
        duration: 0.65,
        stagger:  0.09,
        ease:     'expo.out',
    }, '-=0.45')

    // gold line fills
    tl.to(fill, { scaleX: 1, duration: 0.7, ease: 'power2.inOut' }, '-=0.1')

    // sub-tagline fades in
    tl.to(sub, { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' }, '-=0.35')

    // brief hold
    tl.to({}, { duration: 0.55 })

    // ── Exit sequence ──────────────────────────────────────────────────────
    function exitPreloader() {
        phase = 'collapse'

        const exitTl = gsap.timeline({
            onComplete: () => {
                cancelAnimationFrame(rafId)
                window.removeEventListener('mousemove', onMouse)
                window.removeEventListener('resize', onResize)
                renderer.dispose()
                el.remove()
                document.body.classList.remove('overflow-hidden')
                initAnimations()
            },
        })

        // text fades & lifts out
        exitTl.to([...letters, sub], {
            opacity:  0,
            y:       -40,
            duration: 0.45,
            stagger:  0.04,
            ease:     'power2.in',
        })

        // particles fade as they collapse
        exitTl.to(mat, { opacity: 0, duration: 0.6, ease: 'power2.in' }, '-=0.3')

        // whole overlay slides up
        exitTl.to(el, {
            yPercent:  -105,
            duration:   0.75,
            ease:       'power4.inOut',
        }, '-=0.25')
    }

    document.body.classList.add('overflow-hidden')
}

function initAnimations() {
    animateHero()
    heroParallax()
    revealSections()
    animateCards()
}

// ── Hero entrance ─────────────────────────────────────────────────────────────
function animateHero() {
    const eyebrow = document.getElementById('hp-eyebrow')
    const name    = document.getElementById('hp-name')
    const tagline = document.getElementById('hp-tagline')
    const actions = document.getElementById('hp-actions')
    const scroll  = document.getElementById('hp-scroll')

    if (!eyebrow) return

    const nameM    = name?.querySelector('.hp-hero__name-m')
    const nameRest = name?.querySelector('.hp-hero__name-rest')

    gsap.set([eyebrow, tagline, actions, scroll], { opacity: 0, y: 24 })
    gsap.set(nameM,    { opacity: 0, x: -44 })
    gsap.set(nameRest, { opacity: 0, x: -14 })

    const tl = gsap.timeline({ delay: 0.15 })
    tl.to(eyebrow,  { opacity: 1, y: 0, duration: 0.7, ease: 'power2.out' })
      .to(nameM,    { opacity: 1, x: 0, duration: 1.0, ease: 'expo.out'   }, '-=0.3')
      .to(nameRest, { opacity: 1, x: 0, duration: 0.8, ease: 'expo.out'   }, '-=0.65')
      .to(tagline,  { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, '-=0.2')
      .to(actions,  { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, '-=0.35')
      .to(scroll,   { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' }, '-=0.25')
}

// ── Hero background parallax ──────────────────────────────────────────────────
function heroParallax() {
    const img = document.getElementById('hp-hero-bg-img')
    if (!img) return

    gsap.to(img, {
        y: '15%',
        ease: 'none',
        scrollTrigger: {
            trigger: '#hp-hero',
            start: 'top top',
            end: 'bottom top',
            scrub: true,
        },
    })
}

// ── Generic scroll reveals ────────────────────────────────────────────────────
function revealSections() {
    const items = document.querySelectorAll('[data-hp-reveal]')
    if (!items.length) return

    gsap.set(items, { opacity: 0, y: 40 })

    ScrollTrigger.batch(items, {
        start: 'top 90%',
        onEnter: batch => gsap.to(batch, {
            opacity: 1,
            y: 0,
            duration: 0.9,
            ease: 'power3.out',
            stagger: 0.1,
        }),
        once: true,
    })
}

// ── Work cards staggered fade + scale-in ─────────────────────────────────────
function animateCards() {
    const cards = document.querySelectorAll('[data-hp-card]')
    if (!cards.length) return

    cards.forEach(card => {
        const img = card.querySelector('.hp-work-card__img')

        gsap.from(card, {
            opacity: 0,
            y: 48,
            duration: 1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: card,
                start: 'top 88%',
                once: true,
            },
        })

        if (img) {
            gsap.fromTo(img,
                { scale: 1.1 },
                {
                    scale: 1,
                    duration: 1.4,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: card,
                        start: 'top 88%',
                        once: true,
                    },
                }
            )
        }
    })
}

