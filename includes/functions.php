<?php
/**
 * Nazarbandi — Helper Functions
 */

require_once __DIR__ . '/config.php';

/**
 * Get all photo categories (top-level folders in photos directory).
 */
function getCategories(): array {
    if (!PHOTOS_DIR || !is_dir(PHOTOS_DIR)) return [];

    $categories = [];
    foreach (scandir(PHOTOS_DIR) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        if (is_dir(PHOTOS_DIR . '/' . $entry)) {
            $categories[] = $entry;
        }
    }
    sort($categories);
    return $categories;
}

/**
 * Convert a raw photo path to its optimized WebP URL.
 */
function toOptimizedUrl(string $categoryPath): string {
    $withoutExt = preg_replace('/\.(jpe?g|png|webp|gif|avif)$/i', '', $categoryPath);
    return PHOTOS_OPTIMIZED_URL . '/' . $withoutExt . '.webp';
}

function toGalleryUrl(string $categoryPath): string {
    $withoutExt = preg_replace('/\.(jpe?g|png|webp|gif|avif)$/i', '', $categoryPath);
    return PHOTOS_GALLERY_URL . '/' . $withoutExt . '.webp';
}

function toThumbUrl(string $categoryPath): string {
    $withoutExt = preg_replace('/\.(jpe?g|png|webp|gif|avif)$/i', '', $categoryPath);
    return PHOTOS_THUMB_URL . '/' . $withoutExt . '.webp';
}

/**
 * Recursively walk a directory for image files.
 */
function walkImages(string $dir, string $relPath = ''): array {
    if (!is_dir($dir)) return [];

    $results = [];
    $entries = scandir($dir);

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $fullPath = $dir . '/' . $entry;
        $currentRel = $relPath ? $relPath . '/' . $entry : $entry;

        if (is_dir($fullPath)) {
            $results = array_merge($results, walkImages($fullPath, $currentRel));
        } else {
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (in_array($ext, IMAGE_EXTENSIONS)) {
                $results[] = $currentRel;
            }
        }
    }

    return $results;
}

/**
 * Get photos for a specific category with optimized URLs.
 */
function getPhotosByCategory(string $category): array {
    $dir = PHOTOS_DIR . '/' . $category;
    $images = walkImages($dir);
    sort($images);

    $photos = [];
    foreach ($images as $relPath) {
        $catPath = $category . '/' . $relPath;
        $photos[] = [
            'src' => toOptimizedUrl($catPath),
            'gallery' => toGalleryUrl($catPath),
            'thumb' => toThumbUrl($catPath),
            'category' => $category,
            'filename' => basename($relPath),
        ];
    }

    return $photos;
}

/**
 * Get all photos across all categories.
 */
function getAllPhotos(): array {
    $photos = [];
    foreach (getCategories() as $category) {
        $photos = array_merge($photos, getPhotosByCategory($category));
    }
    return $photos;
}

/**
 * Get category tree with subcategories.
 */
function getCategoryTree(string $category): array {
    $dir = PHOTOS_DIR . '/' . $category;
    $direct = [];
    $subcategories = [];

    if (is_dir($dir)) {
        $entries = scandir($dir);
        sort($entries);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $fullPath = $dir . '/' . $entry;

            if (is_dir($fullPath)) {
                $subPhotos = [];
                $subImages = walkImages($fullPath);
                sort($subImages);
                foreach ($subImages as $relPath) {
                    $catPath = $category . '/' . $entry . '/' . $relPath;
                    $subPhotos[] = [
                        'src' => toOptimizedUrl($catPath),
                        'gallery' => toGalleryUrl($catPath),
                        'thumb' => toThumbUrl($catPath),
                        'category' => $category,
                        'filename' => basename($relPath),
                    ];
                }
                $subcategories[] = [
                    'slug' => $entry,
                    'label' => formatCategoryLabel($entry),
                    'photos' => $subPhotos,
                ];
            } else {
                $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                if (in_array($ext, IMAGE_EXTENSIONS)) {
                    $catPath = $category . '/' . $entry;
                    $direct[] = [
                        'src' => toOptimizedUrl($catPath),
                        'gallery' => toGalleryUrl($catPath),
                        'thumb' => toThumbUrl($catPath),
                        'category' => $category,
                        'filename' => $entry,
                    ];
                }
            }
        }
    }

    $total = count($direct);
    foreach ($subcategories as $sub) {
        $total += count($sub['photos']);
    }

    return [
        'slug' => $category,
        'label' => formatCategoryLabel($category),
        'direct' => $direct,
        'subcategories' => $subcategories,
        'total' => $total,
    ];
}

/**
 * Get all category trees.
 */
function getAllCategoryTrees(): array {
    $trees = [];
    foreach (getCategories() as $category) {
        $trees[] = getCategoryTree($category);
    }
    return $trees;
}

/**
 * Format a slug into a readable label.
 */
function formatCategoryLabel(string $slug): string {
    $words = str_replace(['-', '_'], ' ', trim($slug));
    return ucfirst($words);
}

/**
 * Get site content (hero, about, services) from database.
 * Falls back to defaults if DB is unavailable.
 */
function getSiteContent(): array {
    $defaults = [
        'hero' => [
            'kicker' => 'Photography & videography',
            'headingLine1' => 'People, places,',
            'headingLine2' => 'cultures & food.',
            'sub' => "I'm Ishan Kothari — capturing the essence of people, places, cultures and food, one honest frame at a time.",
            'ctaLabel' => 'View the gallery',
            'ctaHref' => '/gallery',
        ],
        'about' => [
            'kicker' => 'About',
            'heading' => "Hello, I'm Ishan.",
            'paragraphs' => [
                "I'm a photographer and videographer with a passion for capturing the essence of people, places, cultures and food.",
                "Starting out in journalism, I developed a keen eye for detail and a deep appreciation for the power of imagery. Now I dive into various photography realms — from lively streets to serene landscapes, from product showcases to delicious food shots.",
                "I'm fueled by curiosity and driven by passion, always seeking to uncover beauty in the ordinary. Photography isn't just my job; it's my way of life — a constant journey of exploration and growth.",
            ],
            'signature' => '— IK',
            'photo' => null,
        ],
        'services' => [
            'kicker' => 'What I offer',
            'heading' => 'Services',
            'items' => [
                ['title' => 'People', 'desc' => 'Portraits and candid moments, natural light, minimal direction.'],
                ['title' => 'Places & Culture', 'desc' => 'Street, travel and documentary work on location, worldwide.'],
                ['title' => 'Food & Product', 'desc' => 'Editorial-style food and product photography for brands.'],
                ['title' => 'Videography', 'desc' => 'Short-form and documentary video, shot and edited end to end.'],
            ],
        ],
        'watermark' => 'ik',
        'contact' => [
            'email' => 'ishankothari1999@gmail.com',
            'links' => [
                ['name' => 'Instagram', 'url' => 'https://www.instagram.com/ishan_kothari/'],
            ],
        ],
    ];

    try {
        $db = getDB();
        $rows = $db->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        if (!empty($settings)) {
            $content = [
                'hero' => [
                    'kicker' => $settings['hero_kicker'] ?? $defaults['hero']['kicker'],
                    'headingLine1' => $settings['hero_heading_line1'] ?? $defaults['hero']['headingLine1'],
                    'headingLine2' => $settings['hero_heading_line2'] ?? $defaults['hero']['headingLine2'],
                    'sub' => $settings['hero_sub'] ?? $defaults['hero']['sub'],
                    'ctaLabel' => $settings['hero_cta_label'] ?? $defaults['hero']['ctaLabel'],
                    'ctaHref' => $settings['hero_cta_href'] ?? $defaults['hero']['ctaHref'],
                ],
                'about' => [
                    'kicker' => $settings['about_kicker'] ?? $defaults['about']['kicker'],
                    'heading' => $settings['about_heading'] ?? $defaults['about']['heading'],
                    'paragraphs' => isset($settings['about_paragraphs'])
                        ? array_filter(array_map('trim', explode("\n\n", str_replace('\\n\\n', "\n\n", $settings['about_paragraphs']))))
                        : $defaults['about']['paragraphs'],
                    'signature' => $settings['about_signature'] ?? $defaults['about']['signature'],
                    'photo' => ($settings['about_photo'] ?? '') ?: null,
                ],
                'services' => [
                    'kicker' => $settings['services_kicker'] ?? $defaults['services']['kicker'],
                    'heading' => $settings['services_heading'] ?? $defaults['services']['heading'],
                    'items' => isset($settings['services_items'])
                        ? (json_decode($settings['services_items'], true) ?: $defaults['services']['items'])
                        : $defaults['services']['items'],
                ],
                'watermark' => $settings['watermark_text'] ?? $defaults['watermark'],
                'contact' => [
                    'email' => $settings['contact_email'] ?? $defaults['contact']['email'],
                    'links' => isset($settings['contact_links'])
                        ? (json_decode($settings['contact_links'], true) ?: $defaults['contact']['links'])
                        : $defaults['contact']['links'],
                ],
            ];
            return $content;
        }
    } catch (Exception $e) {
        // DB unavailable, use defaults
    }

    return $defaults;
}

/**
 * Parse markdown blog posts from the content/blog directory.
 */
function getBlogPosts(): array {
    if (!is_dir(BLOG_DIR)) return [];

    $posts = [];
    foreach (glob(BLOG_DIR . '/*.md') as $file) {
        $post = parseBlogPost($file);
        if ($post) $posts[] = $post;
    }

    // Sort by date descending
    usort($posts, function ($a, $b) {
        return strtotime($b['pubDate']) - strtotime($a['pubDate']);
    });

    return $posts;
}

/**
 * Parse a single markdown blog post file.
 */
function parseBlogPost(string $file): ?array {
    $content = file_get_contents($file);
    if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)/s', $content, $matches)) {
        return null;
    }

    $frontmatter = $matches[1];
    $body = trim($matches[2]);
    $slug = pathinfo($file, PATHINFO_FILENAME);

    $data = [];
    foreach (explode("\n", $frontmatter) as $line) {
        $pos = strpos($line, ':');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        // Remove surrounding quotes
        if (preg_match('/^"(.*)"$/', $value, $m)) {
            $value = str_replace('\\"', '"', $m[1]);
        }
        $data[$key] = $value;
    }

    return [
        'slug' => $slug,
        'title' => $data['title'] ?? 'Untitled',
        'description' => $data['description'] ?? '',
        'pubDate' => $data['pubDate'] ?? date('Y-m-d'),
        'coverImage' => $data['coverImage'] ?? null,
        'body' => $body,
    ];
}

/**
 * Simple markdown to HTML conversion (basic support).
 */
function markdownToHtml(string $md): string {
    // Headers
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $md);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

    // Bold and italic
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);

    // Images
    $html = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1">', $html);

    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);

    // Paragraphs — wrap lines separated by blank lines
    $paragraphs = preg_split('/\n\s*\n/', $html);
    $paragraphs = array_map(function ($p) {
        $p = trim($p);
        if (preg_match('/^<(h[1-6]|ul|ol|li|blockquote|pre|img)/', $p)) {
            return $p;
        }
        return '<p>' . nl2br($p) . '</p>';
    }, $paragraphs);

    return implode("\n", $paragraphs);
}

/**
 * Get current page for active nav highlighting.
 */
function currentPage(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    return $path ?: '/';
}

/**
 * Escape HTML output.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


/**
 * Turn arbitrary text into a URL-safe slug.
 */
function slugify(string $input): string {
    $slug = strtolower(trim($input));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}


/**
 * Return SVG icon for a social link based on name, with brand colors.
 */
function getSocialIcon(string $name): string {
    $n = strtolower(trim($name));
    $icons = [
        'instagram' => '<svg viewBox="0 0 24 24" width="20" height="20"><defs><linearGradient id="ig" x1="0%" y1="100%" x2="100%" y2="0%"><stop offset="0%" style="stop-color:#FFDC80"/><stop offset="25%" style="stop-color:#F77737"/><stop offset="50%" style="stop-color:#E1306C"/><stop offset="75%" style="stop-color:#C13584"/><stop offset="100%" style="stop-color:#833AB4"/></linearGradient></defs><rect x="2" y="2" width="20" height="20" rx="5" fill="none" stroke="url(#ig)" stroke-width="2"/><circle cx="12" cy="12" r="4.5" fill="none" stroke="url(#ig)" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="url(#ig)"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" fill="#25D366" width="20" height="20"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.019-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 0 1-4.264-1.227l-.306-.183-2.87.852.852-2.87-.183-.306A8 8 0 1 1 12 20z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="#FF0000" width="20" height="20"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        'pinterest' => '<svg viewBox="0 0 24 24" fill="#E60023" width="20" height="20"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" fill="#000" width="20" height="20"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" fill="#000" width="20" height="20"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="#1877F2" width="20" height="20"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="#0A66C2" width="20" height="20"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" fill="#000"/></svg>',
    ];

    if (isset($icons[$n])) return $icons[$n];
    foreach ($icons as $key => $svg) {
        if (strpos($n, $key) !== false) return $svg;
    }

    // Default link icon
    return '<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8" width="20" height="20"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
