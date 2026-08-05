<?php
/**
 * Admin — Create/Edit Blog Post (Rich Text Editor)
 */
$adminTitle = 'New Post';
$adminActive = 'blog';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$db = getDB();

$postId = (int)($_GET['id'] ?? 0);
$post = null;

if ($postId) {
    $stmt = $db->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if ($post) $adminTitle = 'Edit: ' . $post['title'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pubDate = $_POST['pubDate'] ?? date('Y-m-d');
    $body = $_POST['body'] ?? '';
    $coverImage = trim($_POST['coverImage'] ?? '');

    if (!$title) {
        header('Location: ' . $_SERVER['REQUEST_URI'] . '&error=Title is required.');
        exit;
    }

    if ($postId && $post) {
        $stmt = $db->prepare('UPDATE blog_posts SET title = ?, description = ?, body = ?, cover_image = ?, published_at = ? WHERE id = ?');
        $stmt->execute([$title, $description ?: null, $body, $coverImage ?: null, $pubDate, $postId]);
    } else {
        $slug = slugify($title) ?: 'post-' . time();
        $n = 2;
        $baseSlug = $slug;
        while (true) {
            $check = $db->prepare('SELECT id FROM blog_posts WHERE slug = ?');
            $check->execute([$slug]);
            if (!$check->fetch()) break;
            $slug = $baseSlug . '-' . $n;
            $n++;
        }
        $stmt = $db->prepare('INSERT INTO blog_posts (slug, title, description, body, cover_image, published_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$slug, $title, $description ?: null, $body, $coverImage ?: null, $pubDate]);
    }

    header('Location: ' . BASE_URL . '/admin/blog.php?notice=Post saved.');
    exit;
}

require_once __DIR__ . '/layout_head.php';
?>

<!-- Quill CSS -->
<link href="<?= BASE_URL ?>/assets/vendor/quill.snow.css" rel="stylesheet">
<style>
    #editor-container { height: 400px; background: #fff; border-radius: 0 0 6px 6px; }
    .ql-toolbar { border-radius: 6px 6px 0 0; }
    .ql-container { border-radius: 0 0 6px 6px; font-size: 1rem; line-height: 1.7; }
    .ql-editor img, #editor-container img { max-width: 20% !important; height: auto !important; border-radius: 4px; margin: 0.5rem 0; display: block; }
</style>

<form method="POST" class="form-stack" id="post-form">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($post['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description (optional — shows as subtitle)</label>
        <input type="text" id="description" name="description" value="<?= e($post['description'] ?? '') ?>">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="pubDate">Publish Date</label>
            <input type="date" id="pubDate" name="pubDate" value="<?= e($post['published_at'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group">
            <label for="coverImage">Cover Image</label>
            <div style="display:flex;gap:0.5rem;align-items:flex-end;">
                <input type="text" id="coverImage" name="coverImage" value="<?= e($post['cover_image'] ?? '') ?>" placeholder="/uploads/blog/..." style="flex:1;">
                <button type="button" class="btn btn-secondary" onclick="uploadCover()">Upload</button>
            </div>
            <?php if (!empty($post['cover_image'])): ?>
            <img src="<?= e($post['cover_image']) ?>" style="margin-top:0.5rem;max-height:100px;border-radius:4px;">
            <?php endif; ?>
            <input type="file" id="cover-file" accept="image/*" style="display:none;">
        </div>
    </div>

    <div class="form-group">
        <label>Content</label>
        <div id="editor-container"></div>
        <input type="hidden" name="body" id="body-input">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Post</button>
        <a href="<?= BASE_URL ?>/admin/blog" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<!-- Quill JS -->
<script src="<?= BASE_URL ?>/assets/vendor/quill.min.js"></script>
<script>
var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Write your blog post here...',
    modules: {
        toolbar: {
            container: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ],
            handlers: {
                image: imageHandler
            }
        }
    }
});

// Load existing content
<?php if ($post && $post['body']): ?>
quill.root.innerHTML = <?= json_encode($post['body']) ?>;
<?php endif; ?>

// Image upload handler
function imageHandler() {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = function() {
        var file = input.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('image', file);

        fetch('<?= BASE_URL ?>/api/upload-image.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.url) {
                var range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', data.url);
                quill.setSelection(range.index + 1);
            } else {
                alert(data.error || 'Upload failed');
            }
        })
        .catch(function() { alert('Upload failed'); });
    };
}

// Cover image upload
function uploadCover() {
    var input = document.getElementById('cover-file');
    input.click();
    input.onchange = function() {
        var file = input.files[0];
        if (!file) return;
        var formData = new FormData();
        formData.append('image', file);
        fetch('<?= BASE_URL ?>/api/upload-image.php', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.url) {
                    document.getElementById('coverImage').value = data.url;
                } else {
                    alert(data.error || 'Upload failed');
                }
            })
            .catch(function() { alert('Upload failed'); });
    };
}

// On form submit, copy editor HTML to hidden input
document.getElementById('post-form').addEventListener('submit', function() {
    document.getElementById('body-input').value = quill.root.innerHTML;
});
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
