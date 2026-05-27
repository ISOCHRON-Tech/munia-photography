import Alpine from 'alpinejs'
import Focus from '@alpinejs/focus'
import { initPreloader } from './preloader'
import { initLanding } from './landing'
import { initGallery } from './gallery'
import { initStory } from './story'

// ─── Alpine ──────────────────────────────────────────────────────────────────
Alpine.plugin(Focus)
window.Alpine = Alpine
Alpine.start()

// ─── Page-specific modules ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initPreloader()

    if (document.querySelector('[data-page="home"]')) {
        initLanding()
    }

    if (document.querySelector('[data-page="gallery"]')) {
        initGallery()
    }

    if (document.querySelector('[data-page="story"]')) {
        initStory()
    }
})

