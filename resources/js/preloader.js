import { gsap } from 'gsap'

/**
 * SVG-morphing preloader.
 * Fades in the logo, extends the gold line, then slides the whole
 * preloader upward and removes it from the DOM.
 */
export function initPreloader() {
    const preloader = document.getElementById('preloader')
    if (!preloader) return

    const logo = preloader.querySelector('.preloader-logo')
    const line = preloader.querySelector('.preloader-line')

    const tl = gsap.timeline({
        onComplete: () => {
            preloader.remove()
            document.body.classList.remove('overflow-hidden')
        },
    })

    // Block scroll during preloader
    document.body.classList.add('overflow-hidden')

    tl.to(logo, {
        opacity: 1,
        duration: 0.9,
        ease: 'power2.out',
    })
    .to(line, {
        width: '120px',
        duration: 0.6,
        ease: 'power2.inOut',
    }, '-=0.3')
    .to(logo, {
        letterSpacing: '0.5em',
        duration: 0.5,
        ease: 'power1.in',
    }, '+=0.3')
    .to([logo, line], {
        opacity: 0,
        duration: 0.4,
        ease: 'power1.in',
    })
    .to(preloader, {
        yPercent: -100,
        duration: 0.7,
        ease: 'power3.inOut',
    }, '-=0.1')
}
