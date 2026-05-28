@extends('layouts.admin')
@section('title', 'Upload Photos')

@section('content')
<div>
    <h1 class="text-2xl font-display text-[#1a0d14] mb-8">Upload Photographs</h1>

    <div x-data="uploader"
         data-upload-url="{{ route('admin.media.store') }}"
         data-category-url="{{ route('admin.categories.index') }}"
         data-image-upload-url="{{ route('admin.editor.image.store') }}"
         class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-8 items-start">

        {{-- ══════════════ LEFT: form ══════════════ --}}
        <div class="space-y-5">

            {{-- ── Drop zone ── --}}
            <div class="border-2 border-dashed border-[#ffb3d9] rounded-lg p-10 text-center cursor-pointer transition-colors"
                 :class="dragging ? 'border-[#ff1493] bg-[#ff1493]/5' : 'hover:border-[#ff1493]/50'"
                 @dragover.prevent="dragging = true"
                 @dragleave="dragging = false"
                 @drop.prevent="handleDrop($event)"
                 @click="$refs.fileInput.click()">

                <input type="file" accept="image/jpeg,image/png,image/webp"
                       multiple class="hidden" x-ref="fileInput"
                       @change="handleFiles($event.target.files)">

                <div class="text-[#8b3a6e]">
                    <p class="text-4xl mb-3">+</p>
                    <p class="text-sm">Drop images here or <span class="text-[#ff1493]">click to browse</span></p>
                    <p class="text-xs mt-1 opacity-60">JPEG, PNG, WebP — max 25 MB each — up to 20 at once</p>
                </div>
            </div>

            {{-- ── Title ── --}}
            <div>
                <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Title (optional)</label>
                <input type="text" x-model="title" maxlength="200"
                       class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2 text-sm text-[#1a0d14] focus:outline-none focus:border-[#ff1493]">
            </div>

            {{-- ── Description — EasyMDE mounts here ── --}}
            <div>
                <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Description</label>
                <textarea id="description-editor" style="display:none"></textarea>
            </div>

            {{-- ── Category + Tags ── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Category combobox --}}
                <div class="relative" @click.outside="categoryOpen = false">
                    <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Category</label>
                    <div class="relative flex items-center">
                        <input type="text"
                               x-model="categorySearch"
                               @input="onCategoryInput()"
                               @focus="categoryOpen = true; fetchCategories(categorySearch)"
                               @keydown.escape="categoryOpen = false"
                               placeholder="Search or create…"
                               class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2 text-sm text-[#1a0d14] focus:outline-none focus:border-[#ff1493] pr-8"
                               :class="categoryId ? 'border-[#ff1493]/60' : ''">
                        <button x-show="categoryId || categorySearch"
                                @click.prevent="clearCategory()"
                                class="absolute right-2 text-[#8b3a6e] hover:text-red-400 transition-colors text-lg leading-none"
                                style="display:none">×</button>
                    </div>
                    <p x-show="categoryId" class="mt-1 text-xs text-[#ff1493]" style="display:none">
                        ✓ <span x-text="categoryName"></span>
                    </p>
                    <p x-show="categoryError" class="mt-1 text-xs text-red-400" x-text="categoryError" style="display:none"></p>
                    <div x-show="categoryOpen"
                         class="absolute z-20 mt-1 w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded shadow-xl overflow-hidden"
                         style="display:none">
                        <ul class="max-h-48 overflow-y-auto">
                            <template x-if="categoryResults.length === 0 && !categoryCanCreate">
                                <li class="px-4 py-3 text-xs text-[#8b3a6e]">No categories found.</li>
                            </template>
                            <template x-for="cat in categoryResults" :key="cat.id">
                                <li @click="selectCategory(cat)"
                                    class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[#ffcde8] flex items-center justify-between"
                                    :class="cat.id === categoryId ? 'text-[#ff1493]' : 'text-[#1a0d14]'">
                                    <span x-text="cat.name"></span>
                                    <span x-show="cat.id === categoryId" class="text-xs opacity-60">✓</span>
                                </li>
                            </template>
                        </ul>
                        <div x-show="categoryCanCreate" class="border-t border-[#ffb3d9]" style="display:none">
                            <button @click.prevent="createCategory()"
                                    :disabled="categoryCreating"
                                    class="w-full text-left px-4 py-2.5 text-sm text-[#ff1493] hover:bg-[#ffcde8] transition-colors flex items-center gap-2 disabled:opacity-50">
                                <span class="text-base leading-none">+</span>
                                <span>Create "<span x-text="categorySearch.trim()"></span>"</span>
                                <span x-show="categoryCreating" class="ml-auto text-xs opacity-60">saving…</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Tags (comma-separated)</label>
                    <input type="text" x-model="tags"
                           class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2 text-sm text-[#1a0d14] focus:outline-none focus:border-[#ff1493]"
                           placeholder="landscape, travel, 35mm">
                </div>
            </div>

            {{-- ── Featured ── --}}
            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" x-model="isFeatured" id="is_featured"
                       class="rounded border-[#ffb3d9] bg-[#ffe4f0] text-[#ff1493] focus:ring-0">
                <label for="is_featured" class="text-sm text-[#8b3a6e] cursor-pointer">
                    Mark as <span class="text-[#ff1493]">Featured</span>
                    <span class="opacity-60 ml-1">— appears in Home page hero &amp; selected work</span>
                </label>
            </div>

            {{-- ── File queue ── --}}
            <ul class="space-y-2">
                <template x-for="f in queue" :key="f.name + f.size">
                    <li class="flex items-center gap-3 bg-[#ffe4f0] rounded px-3 py-2 text-sm">
                        <img :src="f.preview" :alt="f.name"
                             @click="openEditor(f)"
                             :title="f.status === 'queued' ? 'Click to edit / crop' : ''"
                             class="w-14 h-14 rounded object-cover flex-shrink-0 bg-[#ffcde8] transition-all"
                             :class="f.status === 'queued'
                                 ? 'cursor-pointer hover:ring-2 hover:ring-[#c9a84c] hover:scale-105'
                                 : 'opacity-50 cursor-default'">
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-[#1a0d14] text-sm" x-text="f.name"></p>
                            <p class="text-xs text-[#8b3a6e] mt-0.5" x-text="formatSize(f.size)"></p>
                        </div>
                        <button x-show="f.status === 'queued'"
                                @click="openEditor(f)"
                                class="flex-shrink-0 text-xs text-[#8b3a6e] hover:text-[#ff1493] border border-[#ffb3d9] hover:border-[#ff1493] rounded px-2 py-1 transition-colors">
                            Edit
                        </button>
                        <span class="w-16 text-right text-xs flex-shrink-0"
                              :class="{
                                  'text-emerald-400': f.status === 'done',
                                  'text-red-400':     f.status === 'error',
                                  'text-[#ff1493]':   f.status === 'uploading',
                                  'text-[#8b3a6e]':   f.status === 'queued',
                              }"
                              x-text="f.status === 'uploading' ? f.progress + '%' : f.status">
                        </span>
                        <button x-show="f.status === 'queued'"
                                @click="removeFromQueue(f)"
                                class="flex-shrink-0 text-[#8b3a6e] hover:text-red-400 transition-colors text-base leading-none w-5 text-center"
                                title="Remove">×</button>
                    </li>
                </template>
            </ul>

            {{-- ── Actions ── --}}
            <div class="flex gap-4">
                <button @click="upload()"
                        :disabled="queue.filter(f => f.status === 'queued').length === 0 || uploading"
                        class="px-6 py-2 bg-[#ff1493] text-white text-sm font-medium rounded hover:bg-[#c71585] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    Upload
                    <span x-text="queue.filter(f => f.status === 'queued').length
                        ? '(' + queue.filter(f => f.status === 'queued').length + ')'
                        : ''"></span>
                </button>
                <button @click="revokeAllPreviews(); queue = []"
                        class="px-4 py-2 text-sm text-[#8b3a6e] hover:text-[#1a0d14] transition-colors">
                    Clear
                </button>
            </div>

        </div>{{-- /left --}}

        {{-- ══════════════ RIGHT: live preview ══════════════ --}}
        <div class="sticky top-6">
            <div class="bg-[#ffe4f0] border border-[#ffb3d9] rounded-xl overflow-hidden">
                {{-- Panel header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-[#ffb3d9] bg-[#ffd6eb]">
                    <span class="text-xs tracking-widest uppercase text-[#8b3a6e] font-medium">Description Preview</span>
                    <span class="text-[10px] text-[#c4607a] opacity-70">live</span>
                </div>

                {{-- Empty state --}}
                <div x-show="!description.trim()"
                     class="px-6 py-16 text-center"
                     style="display:none">
                    <p class="text-[#c4607a] text-sm italic opacity-60">Start writing a description…</p>
                    <p class="text-[#c4607a] text-xs mt-2 opacity-40">Supports **bold**, _italic_, # headings, links, and images</p>
                </div>

                {{-- Rendered markdown --}}
                <div x-show="description.trim()"
                     x-html="previewHtml"
                     class="px-6 py-5 prose prose-sm max-w-none
                            prose-headings:text-[#1a0d14] prose-headings:border-b prose-headings:border-[#ffb3d9] prose-headings:pb-1
                            prose-p:text-[#1a0d14] prose-p:leading-relaxed
                            prose-a:text-[#ff1493] prose-a:no-underline hover:prose-a:underline
                            prose-strong:text-[#1a0d14]
                            prose-em:text-[#8b3a6e]
                            prose-code:bg-[#fff0f5] prose-code:text-[#c71585] prose-code:rounded prose-code:px-1
                            prose-blockquote:border-l-[#ff1493] prose-blockquote:text-[#8b3a6e]
                            prose-ul:text-[#1a0d14] prose-ol:text-[#1a0d14]
                            prose-img:rounded-lg prose-img:border prose-img:border-[#ffb3d9]"
                     style="display:none">
                </div>
            </div>
        </div>{{-- /right --}}

        {{-- ══════════════ Image Insert Modal ══════════════ --}}
        <div x-show="imgModal"
             x-transition:enter="transition duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4"
             style="display:none"
             @keydown.escape.window="imgModal = false">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40" @click="imgModal = false"></div>

            {{-- Panel --}}
            <div class="relative bg-[#fff0f5] border border-[#ffb3d9] rounded-xl shadow-2xl w-full max-w-sm overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#ffb3d9] bg-[#ffd6eb]">
                    <span class="text-sm font-medium text-[#1a0d14]">Insert Image</span>
                    <button @click="imgModal = false" class="text-[#8b3a6e] hover:text-[#1a0d14] transition-colors text-xl leading-none">&times;</button>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-[#ffb3d9]">
                    <button @click="imgTab = 'url'"
                            class="flex-1 py-2.5 text-sm transition-colors"
                            :class="imgTab === 'url' ? 'bg-[#ffe4f0] text-[#ff1493] font-medium' : 'text-[#8b3a6e] hover:bg-[#ffe4f0]'">
                        <i class="fa fa-link mr-1.5"></i>Paste URL
                    </button>
                    <button @click="imgTab = 'upload'"
                            class="flex-1 py-2.5 text-sm transition-colors"
                            :class="imgTab === 'upload' ? 'bg-[#ffe4f0] text-[#ff1493] font-medium' : 'text-[#8b3a6e] hover:bg-[#ffe4f0]'">
                        <i class="fa fa-upload mr-1.5"></i>Upload File
                    </button>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Alt text (shared) --}}
                    <div>
                        <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Alt text <span class="normal-case opacity-50">(optional)</span></label>
                        <input type="text" x-model="imgAlt" placeholder="Describe the image…"
                               class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-3 py-2 text-sm text-[#1a0d14] focus:outline-none focus:border-[#ff1493]">
                    </div>

                    {{-- URL tab --}}
                    <div x-show="imgTab === 'url'" style="display:none">
                        <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Image URL</label>
                        <input type="url" x-model="imgUrl" placeholder="https://…"
                               @keydown.enter.prevent="insertImageFromUrl()"
                               class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-3 py-2 text-sm text-[#1a0d14] focus:outline-none focus:border-[#ff1493]">
                    </div>

                    {{-- Upload tab --}}
                    <div x-show="imgTab === 'upload'" style="display:none">
                        <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Choose file</label>
                        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                               @change="uploadAndInsertImage($event.target.files[0])"
                               class="w-full text-sm text-[#8b3a6e] file:mr-3 file:py-1.5 file:px-3 file:rounded file:border file:border-[#ffb3d9] file:bg-[#ffe4f0] file:text-[#8b3a6e] file:cursor-pointer hover:file:bg-[#ffcde8] file:transition-colors">
                        <p x-show="imgUploading" class="mt-2 text-xs text-[#ff1493]" style="display:none">
                            <i class="fa fa-spinner fa-spin mr-1"></i>Uploading…
                        </p>
                    </div>

                    {{-- Error --}}
                    <p x-show="imgError" x-text="imgError" class="text-xs text-red-500" style="display:none"></p>

                    {{-- Actions --}}
                    <div x-show="imgTab === 'url'" class="flex gap-3" style="display:none">
                        <button @click="insertImageFromUrl()"
                                :disabled="!imgUrl.trim()"
                                class="flex-1 py-2 bg-[#ff1493] text-white text-sm font-medium rounded hover:bg-[#c71585] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            Insert
                        </button>
                        <button @click="imgModal = false"
                                class="px-4 py-2 text-sm text-[#8b3a6e] hover:text-[#1a0d14] transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════ Upload Success Modal ══════════════ --}}
        <div x-show="successModal"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[70] flex items-center justify-center p-4"
             style="display:none"
             @keydown.escape.window="dismissSuccess()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40" @click="dismissSuccess()"></div>

            {{-- Panel --}}
            <div class="relative bg-[#fff0f5] border border-[#ffb3d9] rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden text-center">

                {{-- Pink top bar --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-[#ffb3d9] via-[#ff1493] to-[#ffb3d9]"></div>

                <div class="px-8 pt-8 pb-7">
                    {{-- Kitty / checkmark icon --}}
                    <div class="w-16 h-16 rounded-full bg-[#ffe4f0] border-2 border-[#ffb3d9] flex items-center justify-center mx-auto mb-5">
                        <i class="fa-solid fa-check text-2xl text-[#ff1493]"></i>
                    </div>

                    <h2 class="text-lg font-display text-[#1a0d14] mb-1">Upload complete!</h2>

                    <p class="text-sm text-[#8b3a6e] mb-1">
                        <span class="font-semibold text-[#ff1493]" x-text="successCount"></span>
                        <span x-text="successCount === 1 ? ' photo' : ' photos'"></span>
                        uploaded successfully.
                    </p>

                    <p x-show="errorCount > 0" class="text-xs text-red-400 mb-3" style="display:none">
                        <span x-text="errorCount"></span>
                        <span x-text="errorCount === 1 ? ' file' : ' files'"></span>
                        failed — they remain in the queue.
                    </p>

                    <p class="text-xs text-[#8b3a6e]/60 mt-2 mb-6">
                        Processing is running in the background&nbsp;&mdash; images will appear shortly.
                    </p>

                    <div class="flex flex-col gap-2.5">
                        <a href="{{ route('admin.media.index') }}"
                           class="w-full py-2.5 bg-[#ff1493] text-white text-sm font-medium rounded-lg hover:bg-[#c71585] transition-colors">
                            View Gallery
                        </a>
                        <button @click="dismissSuccess()"
                                class="w-full py-2.5 text-sm text-[#8b3a6e] hover:text-[#1a0d14] border border-[#ffb3d9] rounded-lg hover:border-[#ff1493] transition-colors">
                            Upload more
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════ Photo Editor Modal ══════════════ --}}
        <div x-show="editTarget !== null"
             x-transition:enter="transition duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/95 flex flex-col"
             style="display:none"
             @keydown.escape.window="cancelEdit()">

            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-[#ffb3d9] flex-shrink-0 flex-wrap">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button @click="rotate(-90)" title="Rotate left" class="editor-tool-btn">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </button>
                    <button @click="rotate(90)" title="Rotate right" class="editor-tool-btn">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                    </button>
                    <div class="w-px h-5 bg-[#ffcde8] mx-0.5"></div>
                    <button @click="flipH()" class="editor-tool-btn text-xs font-medium px-2.5">⇄ H</button>
                    <button @click="flipV()" class="editor-tool-btn text-xs font-medium px-2.5">⇅ V</button>
                    <div class="w-px h-5 bg-[#ffcde8] mx-0.5"></div>
                    <button @click="zoomBy(0.1)"  class="editor-tool-btn text-base leading-none px-2.5">+</button>
                    <button @click="zoomBy(-0.1)" class="editor-tool-btn text-base leading-none px-2.5">−</button>
                    <div class="w-px h-5 bg-[#ffcde8] mx-0.5"></div>
                    <button @click="resetCrop()" class="editor-tool-btn text-xs px-2.5">Reset</button>
                    <div class="w-px h-5 bg-[#ffcde8] mx-0.5"></div>
                    <span class="text-xs text-[#8b3a6e] mr-0.5">Crop:</span>
                    <button @click="setAspect(NaN)"  class="editor-tool-btn text-xs px-2">Free</button>
                    <button @click="setAspect(1)"    class="editor-tool-btn text-xs px-2">1:1</button>
                    <button @click="setAspect(4/3)"  class="editor-tool-btn text-xs px-2">4:3</button>
                    <button @click="setAspect(3/2)"  class="editor-tool-btn text-xs px-2">3:2</button>
                    <button @click="setAspect(16/9)" class="editor-tool-btn text-xs px-2">16:9</button>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <button @click="cancelEdit()" class="px-4 py-1.5 text-sm text-[#8b3a6e] hover:text-[#1a0d14] transition-colors">Cancel</button>
                    <button @click="applyEdit()"  class="px-5 py-1.5 bg-[#ff1493] text-white text-sm font-medium rounded hover:bg-[#c71585] transition-colors">Apply &amp; Close</button>
                </div>
            </div>

            <div class="px-4 pt-2 pb-0 text-xs text-[#8b3a6e] flex-shrink-0">
                Editing: <span class="text-[#1a0d14]" x-text="editTarget?.name"></span>
                <span class="ml-3 opacity-40">Drag to pan · Scroll to zoom · Drag corners to crop</span>
            </div>

            <div class="flex-1 overflow-hidden p-4 min-h-0">
                <img id="editor-img" src="" alt="" class="block max-w-full" style="max-height:100%">
            </div>
        </div>

    </div>{{-- /x-data --}}
</div>

<style>
.editor-tool-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.375rem;
    border-radius: 0.25rem;
    color: #c4607a;
    background: #ffe4f0;
    border: 1px solid #ffb3d9;
    transition: color 0.15s, background 0.15s, border-color 0.15s;
    cursor: pointer;
}
.editor-tool-btn:hover {
    color: #1a0d14;
    background: #ffcde8;
    border-color: #f090c0;
}
</style>


@push('before_alpine')
    @vite('resources/js/admin-upload.js')
@endpush
@endsection
