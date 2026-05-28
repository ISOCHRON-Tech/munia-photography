import '../css/app.css'
import Alpine from 'alpinejs'
import Focus from '@alpinejs/focus'
import { initPreloader } from './preloader'
import { initLanding } from './landing'
import { initGallery } from './gallery'
import { initStory } from './story'
import { initNav } from './nav'
import { initStoriesIndex } from './stories-index'

// ─── Alpine ──────────────────────────────────────────────────────────────────
Alpine.plugin(Focus)
window.Alpine = Alpine
Alpine.start()

// ─── Page-specific modules ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initPreloader()
    initNav()

    if (document.querySelector('[data-page="home"]')) {
        initLanding()
    }

    if (document.querySelector('[data-page="gallery"]')) {
        initGallery()
    }

    if (document.querySelector('[data-page="stories"]')) {
        initStoriesIndex()
    }

    if (document.querySelector('[data-page="story"]')) {
        initStory()
    }
})

