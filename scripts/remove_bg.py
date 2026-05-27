#!/usr/bin/env python3
"""
Remove the baked-in checkerboard background from citypng images.
The background alternates between white (255,255,255) and light grey
(~235,235,235) fully opaque pixels. We flood-fill from the image borders
AND also remove enclosed checkerboard pockets (areas between illustration lines).
"""

import os
import numpy as np
from collections import deque
from PIL import Image


WHITE_THRESH = 220   # pixels with R,G,B all >= this are "background candidate"
GREY_MAX_DIFF = 30   # max channel-to-channel difference allowed for background


def is_background_pixel(r, g, b):
    """True if the pixel looks like part of the checkerboard background."""
    return (int(r) >= WHITE_THRESH and int(g) >= WHITE_THRESH and int(b) >= WHITE_THRESH
            and max(abs(int(r) - int(g)), abs(int(r) - int(b)), abs(int(g) - int(b))) < GREY_MAX_DIFF)


def has_checkerboard_texture(pixels, y, x, radius=12):
    """
    Check if the local neighbourhood (radius x radius) around (y,x)
    has the ALTERNATING texture of a checkerboard (both white AND grey squares).
    Pure solid-white areas (like kitty's body) fail this test.
    """
    h, w = pixels.shape[:2]
    y0, y1 = max(0, y - radius), min(h, y + radius)
    x0, x1 = max(0, x - radius), min(w, x + radius)
    region = pixels[y0:y1, x0:x1, :3]

    # A genuine background region will contain BOTH near-white AND near-grey pixels.
    # Near-white: all channels >= 245  |  Near-grey: all channels 220–245
    has_white = np.any((region[:, :, 0] >= 245) & (region[:, :, 1] >= 245) & (region[:, :, 2] >= 245))
    has_grey  = np.any((region[:, :, 0] >= WHITE_THRESH) & (region[:, :, 0] < 245)
                       & (region[:, :, 1] >= WHITE_THRESH) & (region[:, :, 1] < 245)
                       & (region[:, :, 2] >= WHITE_THRESH) & (region[:, :, 2] < 245))
    return bool(has_white and has_grey)


def remove_background(input_path, output_path):
    img = Image.open(input_path).convert('RGBA')
    pixels = np.array(img, dtype=np.uint8)
    h, w = pixels.shape[:2]

    # ── Pass 1: BFS flood-fill from every matching border pixel ──────────────
    visited = np.zeros((h, w), dtype=bool)
    queue = deque()

    def enqueue_if_bg(x, y):
        if not visited[y, x]:
            r, g, b, _ = pixels[y, x]
            if is_background_pixel(r, g, b):
                visited[y, x] = True
                queue.append((x, y))

    for x in range(w):
        enqueue_if_bg(x, 0)
        enqueue_if_bg(x, h - 1)
    for y in range(h):
        enqueue_if_bg(0, y)
        enqueue_if_bg(w - 1, y)

    while queue:
        x, y = queue.popleft()
        for nx, ny in ((x - 1, y), (x + 1, y), (x, y - 1), (x, y + 1)):
            if 0 <= nx < w and 0 <= ny < h:
                enqueue_if_bg(nx, ny)

    outer_count = int(visited.sum())

    # ── Pass 2: find enclosed background pockets with checkerboard texture ───
    # Iterate over all unvisited background-coloured pixels.  For each, check
    # if the local neighbourhood looks like checkerboard (not solid kitty body).
    bg_mask = np.zeros((h, w), dtype=bool)
    r_ch, g_ch, b_ch = pixels[:, :, 0], pixels[:, :, 1], pixels[:, :, 2]
    candidate = ((r_ch >= WHITE_THRESH) & (g_ch >= WHITE_THRESH) & (b_ch >= WHITE_THRESH)
                 & (np.maximum(np.maximum(np.abs(r_ch.astype(int) - g_ch.astype(int)),
                                          np.abs(r_ch.astype(int) - b_ch.astype(int))),
                               np.abs(g_ch.astype(int) - b_ch.astype(int))) < GREY_MAX_DIFF)
                 & ~visited)

    ys, xs = np.where(candidate)
    inner_count = 0
    # Check representative points in every 8-pixel grid cell (checkerboard tile size)
    checked_cells = set()
    for y, x in zip(ys.tolist(), xs.tolist()):
        cell = (y // 8, x // 8)
        if cell in checked_cells:
            continue
        checked_cells.add(cell)
        if has_checkerboard_texture(pixels, y, x):
            # BFS this pocket too
            r, g, b, _ = pixels[y, x]
            if not visited[y, x] and is_background_pixel(r, g, b):
                visited[y, x] = True
                inner_count += 1
                pq = deque([(x, y)])
                while pq:
                    px, py = pq.popleft()
                    for nx, ny in ((px - 1, py), (px + 1, py), (px, py - 1), (px, py + 1)):
                        if 0 <= nx < w and 0 <= ny < h and not visited[ny, nx]:
                            nr, ng, nb, _ = pixels[ny, nx]
                            if is_background_pixel(nr, ng, nb):
                                visited[ny, nx] = True
                                inner_count += 1
                                pq.append((nx, ny))

    # ── Apply mask ────────────────────────────────────────────────────────────
    pixels[visited, 3] = 0
    result = Image.fromarray(pixels, 'RGBA')
    result.save(output_path, 'PNG')
    print(f"  {os.path.basename(output_path)}: removed {outer_count:,} outer + {inner_count:,} inner = {outer_count+inner_count:,} px")


if __name__ == '__main__':
    kitty_dir = os.path.join(os.path.dirname(__file__), '..', 'public', 'images', 'kitty')
    targets = [
        'kitty-balloons.png',
        'kitty-kawaii.png',
        'kitty-colorful.png',
        'kitty-full.png',
        'kitty-head.png',
    ]
    # Restore from backup first so we always start fresh
    backup_dir = os.path.join(kitty_dir, 'backup')
    for name in targets:
        bak = os.path.join(backup_dir, name)
        if os.path.exists(bak):
            import shutil
            shutil.copy2(bak, os.path.join(kitty_dir, name))

    print("Removing checkerboard backgrounds...")
    for name in targets:
        path = os.path.join(kitty_dir, name)
        if os.path.exists(path):
            remove_background(path, path)
        else:
            print(f"  SKIP (not found): {name}")
    print("Done.")
