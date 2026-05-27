🚀 Prompt for Claude: High-Performance Photography Portfolio & Story Blog
Context & Goal
We are building a highly professional, visually stunning, secure Personal Photography Showcasing and Story Uploader website. The platform must feel fluid, premium, and cinematic, using modern motion design while being optimized to run seamlessly on a cPanel shared or semi-dedicated hosting environment.
Technical Stack Guidelines (Latest 2026 Stable Versions)
•	Backend: PHP 8.4+ / Laravel 13.x (Monolithic approach optimized for cPanel deployment, keeping the directory structure cPanel-friendly with a clear separation of public/ files).
•	Database: MySQL / MariaDB (Standard cPanel availability).
•	Frontend Interactivity: Alpine.js or Livewire 3 (To keep the PHP stack native without requiring a separate Node.js daemon running on cPanel).
•	Styling & Motion: Tailwind CSS + GSAP (GreenSock Animation Platform) for ultra-smooth, hardware-accelerated timeline animations and scroll-triggered visual effects.
•	Media Processing: Intervention Image v3 (for high-performance image optimization, WebP/AVIF generation, and watermarking).
Step-by-Step Execution Plan
Please break down the development of this application into the following phased implementation steps, providing production-ready code with maximum security defaults.
Phase 1: Architecture Setup & cPanel Compliance
	1.	Directory Mapping: Configure the Laravel 13 setup so that the public/ folder contents can easily map to cPanel's public_html while keeping core application files securely above the web root.
	2.	Asset Pipeline: Set up a Vite configuration that compiles production assets into clean, static CSS/JS with long-term caching headers.
	3.	Database Schema:
•	media_items: High-res source path, optimized web-ready path, placeholders (blurhash/lqip), metadata (EXIF data like ISO, Aperture, Camera body), categories, tags.
•	stories: Slug, Title, Rich text content, Banner image, SEO fields, status (draft/published), published_at.
Phase 2: Ultimate Security Hardening (cPanel Constraints)
Since this runs on cPanel, we need airtight software-level security. Provide implementations for:
	1.	Airtight File Upload Security:
•	Strict MIME-type validation (only allowing exact image/jpeg, image/png, image/webp).
•	Filename obfuscation (UUID renames) to prevent directory traversal or remote code execution (RCE).
•	Disabling execution permissions on the upload storage directory via .htaccess.
	2.	Request Protection:
•	Enforce Laravel 13's PreventRequestForgery middleware with origin-aware verification.
•	Implement strict Rate Limiting (RateLimiter) specifically on the custom admin login, story submission endpoints, and contact forms.
	3.	Secure Headers: A robust .htaccess template including Content Security Policy (CSP), X-Frame-Options, X-Content-Type-Options, and Link prefetching configurations for images.
Phase 3: Premium UI & Cinematic Animations (GSAP + Tailwind)
The front-end must look like an elite creative portfolio. Deliver clean, componentized templates using Tailwind and GSAP for:
	1.	The Core Gallery (Photography Showcase):
•	An asymmetrical grid layout that utilizes modern fluid typography.
•	GSAP Animation: Implement infinite smooth scroll, a refined reveal effect as images enter the viewport, and a slick, high-performance lightbox component.
	2.	The Story Uploader & Blog Layout:
•	An immersive editorial typography layout designed for narrative reading.
•	GSAP Animation: Scroll-driven parallax effects on banner images, text fade-ins triggered by section entering, and elegant transition effects between articles.
	3.	Global Assets: A customized, high-performance page preloader that masks image-heavy initial asset loading with a smooth webgl-like SVG morphing animation.
Phase 4: Extreme Performance & Media Optimization
Photographs are heavy; shared hosting resources are limited. Provide:
	1.	On-the-Fly / Queue Optimization: Implement automated image processing using Intervention Image. Convert uploads to modern formats (WebP/AVIF), strip heavy metadata profiles (while storing EXIF attributes safely in the database), and generate multiple responsive source sizes (srcset).
	2.	Caching Strategy: Configure Laravel's cache layer for aggressive query caching on portfolio items, generating static HTML fragments for the public-facing pages where possible.
	3.	Lazy Loading: Set up native loading="lazy" paired with low-quality image placeholders (LQIP) or CSS-based gradient blurs to ensure page speed scores remain close to 100 on desktop and mobile.
Phase 5: Production Deployment Guide
	1.	Provide a step-by-step checklist detailing how to deploy this Laravel 13 monolithic build to a standard cPanel account.
	2.	Include specific optimization commands to run via SSH terminal or cPanel Cron jobs (e.g., php artisan config:cache, route:cache, view:cache, and automated image optimization queues running over a database driver).
Instructions for Code Generation
•	Write clean, defensive code: Use strict typing (declare(strict_types=1);), modern PHP 8.4+ property hooks where applicable, and clean separation of concerns.
•	No placeholders: Do not leave empty comments like // implement logic here. Provide fully fleshed-out, functional controller logic, middleware, and front-end scripts.
•	Start with Phase 1 and Phase 2: Let's begin by generating the directory configuration, secure .htaccess, and the core database schema.