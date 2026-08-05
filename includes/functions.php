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
