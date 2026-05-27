import * as THREE from 'three'

// ─────────────────────────────────────────────────────────────────────────────
// Shared helpers
// ─────────────────────────────────────────────────────────────────────────────

function makeBokehTexture(r, g, b) {
    const c   = document.createElement('canvas')
    c.width   = c.height = 128
    const ctx = c.getContext('2d')
    const grd = ctx.createRadialGradient(64, 64, 0, 64, 64, 64)
    grd.addColorStop(0,    `rgba(${r},${g},${b},1)`)
    grd.addColorStop(0.38, `rgba(${r},${g},${b},0.5)`)
    grd.addColorStop(0.72, `rgba(${r},${g},${b},0.1)`)
    grd.addColorStop(1,    `rgba(0,0,0,0)`)
    ctx.fillStyle = grd
    ctx.fillRect(0, 0, 128, 128)
    return new THREE.CanvasTexture(c)
}

// ─────────────────────────────────────────────────────────────────────────────
// Gallery — ambient floating dust particles
// Returns a dispose function.
// ─────────────────────────────────────────────────────────────────────────────

export function createGalleryScene(canvas) {
    const W = window.innerWidth, H = window.innerHeight

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: false })
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5))
    renderer.setSize(W, H)

    const scene  = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(70, W / H, 0.1, 100)
    camera.position.z = 5

    const COUNT = 220
    const pos   = new Float32Array(COUNT * 3)
    const vel   = new Float32Array(COUNT * 3)
    const phase = new Float32Array(COUNT)

    for (let i = 0; i < COUNT; i++) {
        pos[i*3]   = (Math.random() - 0.5) * 16
        pos[i*3+1] = (Math.random() - 0.5) * 12
        pos[i*3+2] = (Math.random() - 0.5) * 4
        vel[i*3]   = (Math.random() - 0.5) * 0.0025
        vel[i*3+1] = (Math.random() - 0.5) * 0.0018
        phase[i]   = Math.random() * Math.PI * 2
    }

    const goldTex   = makeBokehTexture(255, 105, 180)   // hot pink
    const silverTex = makeBokehTexture(255, 182, 193)   // blush pink

    const geoA = new THREE.BufferGeometry()
    geoA.setAttribute('position', new THREE.BufferAttribute(pos.slice(), 3))
    const geoB = new THREE.BufferGeometry()
    geoB.setAttribute('position', new THREE.BufferAttribute(pos.slice(), 3))

    const matGold = new THREE.PointsMaterial({
        map: goldTex, size: 0.22, transparent: true, depthWrite: false,
        blending: THREE.AdditiveBlending, opacity: 0.5, sizeAttenuation: true,
    })
    const matSilver = new THREE.PointsMaterial({
        map: silverTex, size: 0.14, transparent: true, depthWrite: false,
        blending: THREE.AdditiveBlending, opacity: 0.28, sizeAttenuation: true,
    })

    const ptsGold   = new THREE.Points(geoA, matGold)
    const ptsSilver = new THREE.Points(geoB, matSilver)
    scene.add(ptsGold, ptsSilver)

    let t = 0, rafId

    function tick() {
        rafId = requestAnimationFrame(tick)
        t += 0.007

        const pa = geoA.attributes.position.array
        const pb = geoB.attributes.position.array

        for (let i = 0; i < COUNT; i++) {
            const dx = Math.sin(t * 0.45 + phase[i]) * 0.0009
            const dy = Math.cos(t * 0.35 + phase[i]) * 0.0007

            pa[i*3]   += vel[i*3]   + dx
            pa[i*3+1] += vel[i*3+1] + dy
            pb[i*3]    = pa[i*3]
            pb[i*3+1]  = pa[i*3+1]
            pb[i*3+2]  = pa[i*3+2]

            // Wrap edges
            if (pa[i*3]   >  8.5) pa[i*3]   = -8.5
            if (pa[i*3]   < -8.5) pa[i*3]   =  8.5
            if (pa[i*3+1] >  6.5) pa[i*3+1] = -6.5
            if (pa[i*3+1] < -6.5) pa[i*3+1] =  6.5
        }

        geoA.attributes.position.needsUpdate = true
        geoB.attributes.position.needsUpdate = true

        ptsGold.rotation.z   += 0.0003
        ptsSilver.rotation.z -= 0.00018

        renderer.render(scene, camera)
    }
    tick()

    const onResize = () => {
        camera.aspect = window.innerWidth / window.innerHeight
        camera.updateProjectionMatrix()
        renderer.setSize(window.innerWidth, window.innerHeight)
    }
    window.addEventListener('resize', onResize)

    return function dispose() {
        cancelAnimationFrame(rafId)
        window.removeEventListener('resize', onResize)
        renderer.dispose()
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Stories index — flowing gold wireframe wave mesh
// Returns a dispose function.
// ─────────────────────────────────────────────────────────────────────────────

export function createStoriesScene(canvas) {
    const W = canvas.offsetWidth || 800
    const H = canvas.offsetHeight || 300

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true })
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
    renderer.setSize(W, H)

    const scene  = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(55, W / H, 0.1, 100)
    camera.position.set(0, 1.2, 4)
    camera.lookAt(0, 0, 0)

    // Primary wave plane
    const geo = new THREE.PlaneGeometry(12, 6, 90, 45)
    const mat = new THREE.MeshBasicMaterial({
        color:       new THREE.Color('#ff69b4'),
        wireframe:   true,
        transparent: true,
        opacity:     0.22,
    })
    const mesh = new THREE.Mesh(geo, mat)
    mesh.rotation.x = -0.55
    scene.add(mesh)

    // Secondary deeper plane (silver, offset)
    const geo2 = new THREE.PlaneGeometry(12, 6, 45, 22)
    const mat2 = new THREE.MeshBasicMaterial({
        color:       new THREE.Color('#ffb3d9'),
        wireframe:   true,
        transparent: true,
        opacity:     0.1,
    })
    const mesh2 = new THREE.Mesh(geo2, mat2)
    mesh2.rotation.x = -0.55
    mesh2.position.y = -0.4
    mesh2.position.z = -1.2
    scene.add(mesh2)

    const origPos  = geo.attributes.position.array.slice()
    const origPos2 = geo2.attributes.position.array.slice()

    let t = 0, rafId

    function tick() {
        rafId = requestAnimationFrame(tick)
        t += 0.01

        const pos  = geo.attributes.position.array
        const pos2 = geo2.attributes.position.array

        for (let i = 0; i < pos.length; i += 3) {
            const ox = origPos[i], oy = origPos[i+1]
            pos[i+2] = Math.sin(ox * 0.9  + t * 0.85) * 0.22
                     + Math.cos(oy * 1.2  + t * 0.65) * 0.14
                     + Math.sin((ox + oy) * 0.55 + t * 0.45) * 0.1
        }

        for (let i = 0; i < pos2.length; i += 3) {
            const ox = origPos2[i], oy = origPos2[i+1]
            pos2[i+2] = Math.sin(ox * 0.7  + t * 0.6)  * 0.18
                      + Math.cos(oy * 0.9  + t * 0.5)  * 0.1
        }

        geo.attributes.position.needsUpdate  = true
        geo2.attributes.position.needsUpdate = true

        renderer.render(scene, camera)
    }
    tick()

    const onResize = () => {
        const nW = canvas.offsetWidth
        const nH = canvas.offsetHeight
        camera.aspect = nW / nH
        camera.updateProjectionMatrix()
        renderer.setSize(nW, nH)
    }
    window.addEventListener('resize', onResize)

    return function dispose() {
        cancelAnimationFrame(rafId)
        window.removeEventListener('resize', onResize)
        renderer.dispose()
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Hero ambient — lightweight particle halo for the home hero (post-preloader)
// ─────────────────────────────────────────────────────────────────────────────

export function createHeroAmbient(canvas) {
    const W = window.innerWidth, H = window.innerHeight

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: false })
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5))
    renderer.setSize(W, H)

    const scene  = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(65, W / H, 0.1, 100)
    camera.position.z = 3.5

    const COUNT = 140
    const pos   = new Float32Array(COUNT * 3)
    const ph    = new Float32Array(COUNT)
    const spds  = new Float32Array(COUNT * 3)

    for (let i = 0; i < COUNT; i++) {
        // Distributed in a wide halo ring
        const ang = Math.random() * Math.PI * 2
        const r   = 1.8 + Math.random() * 2.5
        pos[i*3]   = Math.cos(ang) * r
        pos[i*3+1] = Math.sin(ang) * r * 0.55
        pos[i*3+2] = (Math.random() - 0.5) * 2.5
        spds[i*3]  = (Math.random() - 0.5) * 0.002
        spds[i*3+1]= (Math.random() - 0.5) * 0.0015
        ph[i]      = Math.random() * Math.PI * 2
    }

    const tex = makeBokehTexture(255, 105, 180)  // hot pink ambient halo
    const geo = new THREE.BufferGeometry()
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3))

    const mat = new THREE.PointsMaterial({
        map: tex, size: 0.16, transparent: true, depthWrite: false,
        blending: THREE.AdditiveBlending, opacity: 0.6, sizeAttenuation: true,
    })

    const pts = new THREE.Points(geo, mat)
    scene.add(pts)

    let t = 0, rafId
    let mx = 0, my = 0
    const onMouse = e => {
        mx = (e.clientX / window.innerWidth  - 0.5) * 2
        my = (e.clientY / window.innerHeight - 0.5) * 2
    }
    window.addEventListener('mousemove', onMouse)

    function tick() {
        rafId = requestAnimationFrame(tick)
        t += 0.009

        const p = geo.attributes.position.array
        for (let i = 0; i < COUNT; i++) {
            p[i*3]   += spds[i*3]   + Math.sin(t * 0.5 + ph[i]) * 0.0008
            p[i*3+1] += spds[i*3+1] + Math.cos(t * 0.4 + ph[i]) * 0.0006
            if (p[i*3]   >  5) p[i*3]   = -5
            if (p[i*3]   < -5) p[i*3]   =  5
            if (p[i*3+1] >  3) p[i*3+1] = -3
            if (p[i*3+1] < -3) p[i*3+1] =  3
        }
        geo.attributes.position.needsUpdate = true

        camera.position.x += (mx * 0.12 - camera.position.x) * 0.03
        camera.position.y += (-my * 0.08 - camera.position.y) * 0.03
        camera.lookAt(scene.position)

        pts.rotation.z += 0.0005
        renderer.render(scene, camera)
    }
    tick()

    const onResize = () => {
        camera.aspect = window.innerWidth / window.innerHeight
        camera.updateProjectionMatrix()
        renderer.setSize(window.innerWidth, window.innerHeight)
    }
    window.addEventListener('resize', onResize)

    return function dispose() {
        cancelAnimationFrame(rafId)
        window.removeEventListener('mousemove', onMouse)
        window.removeEventListener('resize', onResize)
        renderer.dispose()
    }
}
