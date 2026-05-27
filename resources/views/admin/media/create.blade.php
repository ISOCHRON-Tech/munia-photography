@extends('layouts.admin')
@section('title', 'Upload Photos')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-display text-[#f5f0eb] mb-8">Upload Photographs</h1>

    {{--
        Alpine-powered drag-and-drop uploader.
        Files are posted to /admin/media as multipart/form-data via fetch
        so the user gets per-file progress feedback without a page reload.
    --}}
    <div x-data="uploader()" x-init="init()">

        {{-- Drop zone --}}
        <div
            class="border-2 border-dashed border-[#2e2e2e] rounded-lg p-12 text-center cursor-pointer transition-colors"
            :class="dragging ? 'border-[#c9a84c] bg-[#c9a84c]/5' : 'hover:border-[#9e9e9e]'"
            @dragover.prevent="dragging = true"
            @dragleave="dragging = false"
            @drop.prevent="handleDrop($event)"
            @click="$refs.fileInput.click()">

            <input type="file" accept="image/jpeg,image/png,image/webp"
                   multiple class="hidden" x-ref="fileInput"
                   @change="handleFiles($event.target.files)">

            <div class="text-[#9e9e9e]">
                <p class="text-4xl mb-3">+</p>
                <p class="text-sm">Drop images here or <span class="text-[#c9a84c]">click to browse</span></p>
                <p class="text-xs mt-1 opacity-60">JPEG, PNG, WebP — max 25 MB each — up to 20 at once</p>
            </div>
        </div>

        {{-- Metadata fields --}}
        <div class="mt-6 space-y-4">
            <div>
                <label class="block text-xs tracking-widest uppercase text-[#9e9e9e] mb-1">Title (optional)</label>
                <input type="text" x-model="title" maxlength="200"
                       class="w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded px-4 py-2 text-sm text-[#f5f0eb] focus:outline-none focus:border-[#c9a84c]">
            </div>
            <div>
                <label class="block text-xs tracking-widest uppercase text-[#9e9e9e] mb-1">Description</label>
                <textarea x-model="description" rows="3" maxlength="2000"
                          class="w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded px-4 py-2 text-sm text-[#f5f0eb] focus:outline-none focus:border-[#c9a84c] resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs tracking-widest uppercase text-[#9e9e9e] mb-1">Tags (comma-separated)</label>
                <input type="text" x-model="tags"
                       class="w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded px-4 py-2 text-sm text-[#f5f0eb] focus:outline-none focus:border-[#c9a84c]"
                       placeholder="landscape, travel, 35mm">
            </div>
        </div>

        {{-- File queue --}}
        <ul class="mt-6 space-y-2">
            <template x-for="f in queue" :key="f.name">
                <li class="flex items-center gap-3 bg-[#1a1a1a] rounded px-4 py-3 text-sm">
                    <span class="flex-1 truncate text-[#f5f0eb]" x-text="f.name"></span>
                    <span class="text-xs text-[#9e9e9e]" x-text="formatSize(f.size)"></span>
                    <span class="w-20 text-right text-xs"
                          :class="{
                              'text-emerald-400': f.status === 'done',
                              'text-red-400': f.status === 'error',
                              'text-[#c9a84c]': f.status === 'uploading',
                              'text-[#9e9e9e]': f.status === 'queued',
                          }"
                          x-text="f.status === 'uploading' ? f.progress + '%' : f.status">
                    </span>
                </li>
            </template>
        </ul>

        {{-- Upload button --}}
        <div class="mt-6 flex gap-4">
            <button @click="upload()"
                    :disabled="queue.length === 0 || uploading"
                    class="px-6 py-2 bg-[#c9a84c] text-[#0a0a0a] text-sm font-medium rounded hover:bg-[#b8943e] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                Upload <span x-text="queue.length ? '(' + queue.length + ')' : ''"></span>
            </button>
            <button @click="queue = []" class="px-4 py-2 text-sm text-[#9e9e9e] hover:text-[#f5f0eb] transition-colors">
                Clear
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
function uploader() {
    return {
        dragging:    false,
        uploading:   false,
        queue:       [],
        title:       '',
        description: '',
        tags:        '',

        init() {},

        handleDrop(event) {
            this.dragging = false
            this.handleFiles(event.dataTransfer.files)
        },

        handleFiles(fileList) {
            const allowed = ['image/jpeg', 'image/png', 'image/webp']
            Array.from(fileList).forEach(f => {
                if (!allowed.includes(f.type)) return
                if (f.size > 25 * 1024 * 1024) return
                this.queue.push({ file: f, name: f.name, size: f.size, status: 'queued', progress: 0 })
            })
        },

        async upload() {
            if (!this.queue.length || this.uploading) return
            this.uploading = true

            const pending = this.queue.filter(f => f.status === 'queued')

            for (const item of pending) {
                item.status = 'uploading'

                const fd = new FormData()
                fd.append('images[]', item.file)
                fd.append('title',       this.title)
                fd.append('description', this.description)
                this.tags.split(',').map(t => t.trim()).filter(Boolean).forEach(t => fd.append('tags[]', t))
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content)

                try {
                    const xhr = new XMLHttpRequest()
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            item.progress = Math.round((e.loaded / e.total) * 100)
                        }
                    }

                    await new Promise((resolve, reject) => {
                        xhr.onload  = () => xhr.status === 201 ? resolve() : reject(new Error(xhr.statusText))
                        xhr.onerror = () => reject(new Error('Network error'))
                        xhr.open('POST', '{{ route("admin.media.store") }}')
                        xhr.send(fd)
                    })

                    item.status   = 'done'
                    item.progress = 100
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
    }
}
</script>
@endpush
@endsection
