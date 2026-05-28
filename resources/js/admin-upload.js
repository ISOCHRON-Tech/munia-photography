import Cropper from 'cropperjs'
import EasyMDE from 'easymde'

document.addEventListener('alpine:init', () => {
    Alpine.data('uploader', () => ({
        // ── Core state ────────────────────────────────────────────────────
        dragging:   false,
        uploading:  false,
        queue:      [],
        title:      '',
        description:'',
        tags:       '',
        isFeatured: false,
        uploadUrl:  '',
        categoryUrl:'',

        // ── Category combobox state ───────────────────────────────────────
        categoryId:       '',
        categoryName:     '',
        categorySearch:   '',
        categoryResults:  [],
        categoryOpen:     false,
        categoryCreating: false,
        categoryError:    '',
        _searchTimer:     null,

        // ── Editor (Cropper) state ────────────────────────────────────────
        editTarget: null,
        _cropper:   null,
        _scaleX:    1,
        _scaleY:    1,

        // ── EasyMDE instance ──────────────────────────────────────────────
        _mde: null,

        init() {
            this.uploadUrl   = this.$el.dataset.uploadUrl   || ''
            this.categoryUrl = this.$el.dataset.categoryUrl || ''

            // Init EasyMDE on the description textarea
            this.$nextTick(() => {
                const ta = document.getElementById('description-editor')
                if (!ta) return
                this._mde = new EasyMDE({
                    element: ta,
                    spellChecker: false,
                    autoDownloadFontAwesome: false,
                    autosave: { enabled: false },
                    status: false,
                    toolbar: [
                        'bold', 'italic', 'heading', '|',
                        'quote', 'unordered-list', 'ordered-list', '|',
                        'link', '|',
                        'preview', 'side-by-side', 'fullscreen',
                    ],
                    placeholder: 'Write a description…',
                    minHeight: '120px',
                })
                this._mde.codemirror.on('change', () => {
                    this.description = this._mde.value()
                })
            })

            // Initial category load (all, no query)
            this.fetchCategories('')
        },

        // ── Category combobox ─────────────────────────────────────────────
        fetchCategories(q) {
            clearTimeout(this._searchTimer)
            this._searchTimer = setTimeout(async () => {
                const url = this.categoryUrl + (q ? '?q=' + encodeURIComponent(q) : '')
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                })
                this.categoryResults = await res.json()
            }, 200)
        },

        onCategoryInput() {
            this.categoryId   = ''
            this.categoryOpen = true
            this.fetchCategories(this.categorySearch)
        },

        selectCategory(cat) {
            this.categoryId     = cat.id
            this.categoryName   = cat.name
            this.categorySearch = cat.name
            this.categoryOpen   = false
            this.categoryError  = ''
        },

        clearCategory() {
            this.categoryId     = ''
            this.categoryName   = ''
            this.categorySearch = ''
            this.categoryResults= []
            this.categoryOpen   = false
            this.fetchCategories('')
        },

        async createCategory() {
            const name = this.categorySearch.trim()
            if (!name) return
            this.categoryCreating = true
            this.categoryError    = ''
            try {
                const res = await fetch(this.categoryUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ name }),
                })
                if (!res.ok) {
                    const err = await res.json()
                    this.categoryError = err.errors?.name?.[0] || 'Could not create category'
                    return
                }
                const cat = await res.json()
                this.categoryResults.unshift(cat)
                this.selectCategory(cat)
            } finally {
                this.categoryCreating = false
            }
        },

        // Returns true if the search text doesn't match any existing result exactly
        get categoryCanCreate() {
            const q = this.categorySearch.trim().toLowerCase()
            return q.length > 0 && !this.categoryResults.some(c => c.name.toLowerCase() === q)
        },

        // ── Drop zone / file selection ────────────────────────────────────
        handleDrop(event) {
            this.dragging = false
            this.handleFiles(event.dataTransfer.files)
        },

        handleFiles(fileList) {
            const allowed = ['image/jpeg', 'image/png', 'image/webp']
            Array.from(fileList).forEach(f => {
                if (!allowed.includes(f.type)) return
                if (f.size > 25 * 1024 * 1024) return
                if (this.queue.some(q => q.name === f.name && q.size === f.size)) return
                this.queue.push({
                    file: f, name: f.name, size: f.size,
                    status: 'queued', progress: 0,
                    preview: URL.createObjectURL(f),
                })
            })
        },

        removeFromQueue(item) {
            URL.revokeObjectURL(item.preview)
            this.queue = this.queue.filter(f => f !== item)
        },

        revokeAllPreviews() {
            this.queue.forEach(f => URL.revokeObjectURL(f.preview))
        },

        // ── Photo editor ──────────────────────────────────────────────────
        openEditor(item) {
            if (item.status !== 'queued') return
            this.editTarget = item
            this._scaleX = 1
            this._scaleY = 1
            this.$nextTick(() => {
                const img = document.getElementById('editor-img')
                if (!img) return
                if (this._cropper) { this._cropper.destroy(); this._cropper = null }
                img.src = ''
                const temp = new Image()
                temp.onload = () => {
                    img.src = temp.src
                    this._cropper = new Cropper(img, {
                        viewMode: 2,
                        autoCropArea: 1,
                        movable: true,
                        zoomable: true,
                        rotatable: true,
                        scalable: true,
                        background: false,
                        toggleDragModeOnDblclick: false,
                    })
                }
                temp.src = item.preview
            })
        },

        rotate(deg)        { this._cropper?.rotate(deg) },
        zoomBy(val)        { this._cropper?.zoom(val) },
        flipH()            { this._scaleX *= -1; this._cropper?.scaleX(this._scaleX) },
        flipV()            { this._scaleY *= -1; this._cropper?.scaleY(this._scaleY) },
        setAspect(ratio)   { this._cropper?.setAspectRatio(ratio) },
        resetCrop()        { this._cropper?.reset() },

        applyEdit() {
            if (!this._cropper || !this.editTarget) return
            const canvas = this._cropper.getCroppedCanvas({
                maxWidth: 4096, maxHeight: 4096, imageSmoothingQuality: 'high',
            })
            const mime = this.editTarget.file.type === 'image/png' ? 'image/png' : 'image/jpeg'
            canvas.toBlob(blob => {
                if (!blob) return
                const newFile = new File([blob], this.editTarget.name, { type: mime })
                URL.revokeObjectURL(this.editTarget.preview)
                this.editTarget.preview = URL.createObjectURL(newFile)
                this.editTarget.file    = newFile
                this.editTarget.size    = newFile.size
                this._cropper.destroy()
                this._cropper   = null
                this.editTarget = null
            }, mime, 0.93)
        },

        cancelEdit() {
            this._cropper?.destroy()
            this._cropper   = null
            this.editTarget = null
        },

        // ── Upload ────────────────────────────────────────────────────────
        async upload() {
            if (!this.queue.length || this.uploading) return
            // Sync description from EasyMDE in case it wasn't synced
            if (this._mde) this.description = this._mde.value()

            this.uploading = true
            const pending = this.queue.filter(f => f.status === 'queued')

            for (const item of pending) {
                item.status = 'uploading'
                const fd = new FormData()
                fd.append('images[]',    item.file)
                fd.append('title',       this.title)
                fd.append('description', this.description)
                this.tags.split(',').map(t => t.trim()).filter(Boolean)
                    .forEach(t => fd.append('tags[]', t))
                if (this.categoryId) fd.append('category_id', this.categoryId)
                fd.append('is_featured', this.isFeatured ? '1' : '0')
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content)

                try {
                    const xhr = new XMLHttpRequest()
                    xhr.upload.onprogress = e => {
                        if (e.lengthComputable)
                            item.progress = Math.round((e.loaded / e.total) * 100)
                    }
                    await new Promise((resolve, reject) => {
                        xhr.onload  = () => xhr.status === 201 ? resolve() : reject(new Error(xhr.responseText))
                        xhr.onerror = () => reject(new Error('Network error'))
                        xhr.open('POST', this.uploadUrl)
                        xhr.send(fd)
                    })
                    item.status   = 'done'
                    item.progress = 100
                    URL.revokeObjectURL(item.preview)
                } catch {
                    item.status = 'error'
                }
            }
            this.uploading = false
        },

        formatSize(bytes) {
            const mb = bytes / 1024 / 1024
            return mb > 1 ? mb.toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB'
        },
    }))
})
