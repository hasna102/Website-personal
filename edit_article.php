<?php
// admin/edit_article.php
session_start();

// Enable error reporting for debugging (hapus di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log'); // pastikan file ini bisa ditulis

// Security: Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$database = new Database();
$conn = $database->getConnection();

// Debug: Cek koneksi database
if (!$conn) {
    die("Koneksi database gagal!");
}

// Validate and sanitize article ID
$article_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$article_id || $article_id <= 0) {
    header('Location: manage_articles.php?error=invalid_id');
    exit;
}

$message = '';
$error = '';

try {
    // Get article data with error handling
    $query = "SELECT * FROM article WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        header('Location: manage_articles.php?error=article_not_found');
        exit;
    }

    // Get article categories
    $cat_query = "SELECT category_id FROM article_category WHERE article_id = :id";
    $cat_stmt = $conn->prepare($cat_query);
    $cat_stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
    $cat_stmt->execute();
    $article_categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get article authors
    $auth_query = "SELECT author_id FROM article_author WHERE article_id = :id";
    $auth_stmt = $conn->prepare($auth_query);
    $auth_stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
    $auth_stmt->execute();
    $article_authors = $auth_stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    error_log("Database error in edit_article.php (SELECT): " . $e->getMessage());
    $error = "Error mengambil data artikel: " . $e->getMessage();
    // Jangan redirect, tampilkan error
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Log POST data
    error_log("POST Data received: " . print_r($_POST, true));
    
    // CSRF Protection
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid!';
        error_log("CSRF token mismatch");
    } else {
        // Sanitize and validate input
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $picture = trim($_POST['picture'] ?? '');
        $categories = $_POST['categories'] ?? [];
        $authors = $_POST['authors'] ?? [];
        $date = $_POST['date'] ?? '';

        // Debug: Log processed data
        error_log("Processed data - Title: $title, Content length: " . strlen($content) . ", Categories: " . print_r($categories, true) . ", Authors: " . print_r($authors, true));

        // Validation
        $validation_errors = [];
        
        if (empty($title)) {
            $validation_errors[] = 'Judul artikel harus diisi!';
        } elseif (strlen($title) < 5) {
            $validation_errors[] = 'Judul artikel minimal 5 karakter!';
        } elseif (strlen($title) > 255) {
            $validation_errors[] = 'Judul artikel maksimal 255 karakter!';
        }

        if (empty($content)) {
            $validation_errors[] = 'Konten artikel harus diisi!';
        } elseif (strlen($content) < 50) {
            $validation_errors[] = 'Konten artikel minimal 50 karakter!';
        }

        if (!empty($picture)) {
            if (!filter_var($picture, FILTER_VALIDATE_URL)) {
                $validation_errors[] = 'Format URL gambar tidak valid!';
            } else {
                // Additional URL validation for security
                $parsed_url = parse_url($picture);
                if (!in_array($parsed_url['scheme'] ?? '', ['http', 'https'])) {
                    $validation_errors[] = 'URL gambar harus menggunakan protokol HTTP atau HTTPS!';
                }
            }
        }

        if (empty($date)) {
            $date = date('Y-m-d');
        } elseif (!validateDate($date)) {
            $validation_errors[] = 'Format tanggal tidak valid!';
        } else {
            // Check if date is not too far in the future
            $inputDate = new DateTime($date);
            $maxDate = new DateTime('+1 year');
            if ($inputDate > $maxDate) {
                $validation_errors[] = 'Tanggal tidak boleh lebih dari 1 tahun ke depan!';
            }
        }

        // Validate categories (must be valid category IDs and exist in database)
        if (!empty($categories)) {
            $categories = array_filter($categories, function($cat_id) {
                return filter_var($cat_id, FILTER_VALIDATE_INT) && $cat_id > 0;
            });
            
            // Check if categories exist in database
            if (!empty($categories)) {
                try {
                    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
                    $cat_check = $conn->prepare("SELECT COUNT(*) FROM category WHERE id IN ($placeholders)");
                    $cat_check->execute(array_values($categories));
                    if ($cat_check->fetchColumn() != count($categories)) {
                        $validation_errors[] = 'Beberapa kategori yang dipilih tidak valid!';
                    }
                } catch (PDOException $e) {
                    error_log("Error validating categories: " . $e->getMessage());
                    $validation_errors[] = 'Error validasi kategori: ' . $e->getMessage();
                }
            }
        }

        // Validate authors (must be valid author IDs and exist in database)
        if (!empty($authors)) {
            $authors = array_filter($authors, function($auth_id) {
                return filter_var($auth_id, FILTER_VALIDATE_INT) && $auth_id > 0;
            });
            
            // Check if authors exist in database
            if (!empty($authors)) {
                try {
                    $placeholders = str_repeat('?,', count($authors) - 1) . '?';
                    $auth_check = $conn->prepare("SELECT COUNT(*) FROM author WHERE id IN ($placeholders)");
                    $auth_check->execute(array_values($authors));
                    if ($auth_check->fetchColumn() != count($authors)) {
                        $validation_errors[] = 'Beberapa penulis yang dipilih tidak valid!';
                    }
                } catch (PDOException $e) {
                    error_log("Error validating authors: " . $e->getMessage());
                    $validation_errors[] = 'Error validasi penulis: ' . $e->getMessage();
                }
            }
        }

        // Debug: Log validation errors
        if (!empty($validation_errors)) {
            error_log("Validation errors: " . print_r($validation_errors, true));
        }

        if (empty($validation_errors)) {
            try {
                // Start transaction
                $conn->beginTransaction();
                error_log("Transaction started");

                // Check if title already exists for other articles
                $title_check = "SELECT COUNT(*) FROM article WHERE LOWER(title) = LOWER(:title) AND id != :id";
                $title_stmt = $conn->prepare($title_check);
                $title_stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $title_stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
                $title_stmt->execute();
                
                if ($title_stmt->fetchColumn() > 0) {
                    throw new Exception('Artikel dengan judul tersebut sudah ada!');
                }

                // Debug: Cek apakah tabel memiliki kolom updated_at
                $columns_query = "SHOW COLUMNS FROM article LIKE 'updated_at'";
                $columns_result = $conn->query($columns_query);
                $has_updated_at = $columns_result->rowCount() > 0;
                
                // Update article with or without updated_at timestamp
                if ($has_updated_at) {
                    $query = "UPDATE article SET title = :title, content = :content, picture = :picture, 
                             date = :date, updated_at = NOW() WHERE id = :id";
                } else {
                    $query = "UPDATE article SET title = :title, content = :content, picture = :picture, 
                             date = :date WHERE id = :id";
                }
                
                error_log("Update query: $query");
                error_log("Update params - title: $title, content length: " . strlen($content) . ", picture: $picture, date: $date, id: $article_id");
                
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':content', $content, PDO::PARAM_STR);
                $stmt->bindParam(':picture', $picture, PDO::PARAM_STR);
                $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                $stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
                
                $update_result = $stmt->execute();
                $affected_rows = $stmt->rowCount();
                
                error_log("Update result: " . ($update_result ? 'true' : 'false') . ", Affected rows: $affected_rows");
                
                if (!$update_result) {
                    $error_info = $stmt->errorInfo();
                    throw new Exception('Gagal mengupdate artikel! Error: ' . implode(' - ', $error_info));
                }
                
                // Delete existing categories and authors
                $del_cat = $conn->prepare("DELETE FROM article_category WHERE article_id = :id");
                $del_cat->bindParam(':id', $article_id, PDO::PARAM_INT);
                $del_cat_result = $del_cat->execute();
                error_log("Delete categories result: " . ($del_cat_result ? 'true' : 'false'));
                
                $del_auth = $conn->prepare("DELETE FROM article_author WHERE article_id = :id");
                $del_auth->bindParam(':id', $article_id, PDO::PARAM_INT);
                $del_auth_result = $del_auth->execute();
                error_log("Delete authors result: " . ($del_auth_result ? 'true' : 'false'));
                
                // Insert new categories
                if (!empty($categories)) {
                    $cat_query = "INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)";
                    $cat_stmt = $conn->prepare($cat_query);
                    foreach ($categories as $category_id) {
                        $cat_stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
                        $cat_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                        if (!$cat_stmt->execute()) {
                            $error_info = $cat_stmt->errorInfo();
                            throw new Exception('Gagal menyimpan kategori artikel! Error: ' . implode(' - ', $error_info));
                        }
                    }
                    error_log("Categories inserted successfully");
                }
                
                // Insert new authors
                if (!empty($authors)) {
                    $auth_query = "INSERT INTO article_author (article_id, author_id) VALUES (:article_id, :author_id)";
                    $auth_stmt = $conn->prepare($auth_query);
                    foreach ($authors as $author_id) {
                        $auth_stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
                        $auth_stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);
                        if (!$auth_stmt->execute()) {
                            $error_info = $auth_stmt->errorInfo();
                            throw new Exception('Gagal menyimpan penulis artikel! Error: ' . implode(' - ', $error_info));
                        }
                    }
                    error_log("Authors inserted successfully");
                }
                
                // Commit transaction
                $conn->commit();
                error_log("Transaction committed successfully");
                
                // Update local data for display
                $article_categories = $categories;
                $article_authors = $authors;
                $article['title'] = $title;
                $article['content'] = $content;
                $article['picture'] = $picture;
                $article['date'] = $date;
                
                $message = 'Artikel berhasil diupdate!';
                error_log("Article updated successfully");
                
            } catch (PDOException $e) {
                $conn->rollback();
                error_log("Database error in edit_article.php (UPDATE): " . $e->getMessage());
                error_log("PDO Error Info: " . print_r($e->errorInfo ?? [], true));
                $error = 'Terjadi kesalahan database: ' . $e->getMessage();
            } catch (Exception $e) {
                $conn->rollback();
                error_log("General error in edit_article.php: " . $e->getMessage());
                $error = $e->getMessage();
            }
        } else {
            $error = implode('<br>', $validation_errors);
        }
    }
}

// Get categories and authors with error handling
try {
    $categories = $conn->query("SELECT * FROM category ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $authors = $conn->query("SELECT * FROM author ORDER BY nickname")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories/authors: " . $e->getMessage());
    $categories = [];
    $authors = [];
    if (empty($error)) {
        $error = "Error mengambil data kategori/penulis: " . $e->getMessage();
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to validate date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Helper function to escape output
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - MyBeauty Admin</title>
    <meta name="description" content="Edit artikel blog - Panel admin MyBeauty">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }

        .admin-header {
            background: linear-gradient(135deg, #c79330 0%, #ea8b76 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .admin-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .admin-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .admin-menu a:hover {
            opacity: 0.8;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .content {
            padding: 2rem 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group label .required {
            color: #dc3545;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #c79330;
            box-shadow: 0 0 0 3px rgba(199, 147, 48, 0.1);
        }

        .form-group input:invalid {
            border-color: #dc3545;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: white;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .checkbox-item:hover {
            background-color: #e9ecef;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
            accent-color: #c79330;
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .submit-btn {
            background: #c79330;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .submit-btn:hover:not(:disabled) {
            background: #b8841f;
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            animation: fadeIn 0.5s ease-in;
            position: relative;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            animation: fadeIn 0.5s ease-in;
        }

        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .char-counter {
            font-size: 0.8rem;
            color: #999;
            text-align: right;
            margin-top: 0.25rem;
        }

        .char-counter.warning {
            color: #fd7e14;
        }

        .char-counter.danger {
            color: #dc3545;
        }

        .article-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .article-info h3 {
            color: #c79330;
            margin-bottom: 0.5rem;
        }

        .article-info p {
            margin: 0.25rem 0;
            color: #424242;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .checkbox-group {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .admin-nav {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-menu {
                gap: 1rem;
                flex-wrap: wrap;
            }

            .form-container {
                padding: 1rem;
            }

            .container {
                padding: 0 10px;
            }
        }

        /* Accessibility improvements */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Focus indicators */
        a:focus,
        button:focus,
        input:focus,
        textarea:focus,
        select:focus {
            outline: 2px solid #c79330;
            outline-offset: 2px;
        }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="container">
        <nav class="admin-nav" role="navigation" aria-label="Admin navigation">
            <div class="admin-logo">MyLogPribadi Admin</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_articles.php">Artikel</a></li>
                <li><a href="manage_categories.php">Kategori</a></li>
                <li><a href="manage_authors.php">Penulis</a></li>
                <li><a href="../index.php" target="_blank" rel="noopener">Lihat Blog</a></li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Edit Artikel</h1>
            <a href="manage_articles.php" class="back-btn">← Kembali</a>
        </div>

        <!-- Debug Information -->
        <div class="debug">
            <h4>Debug Information:</h4>
            <p><strong>Article ID:</strong> <?php echo $article_id; ?></p>
            <p><strong>POST Method:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
            <p><strong>CSRF Token Session:</strong> <?php echo isset($_SESSION['csrf_token']) ? 'Set' : 'Not Set'; ?></p>
            <p><strong>Database Connection:</strong> <?php echo $conn ? 'Connected' : 'Not Connected'; ?></p>
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <p><strong>POST CSRF Token:</strong> <?php echo isset($_POST['csrf_token']) ? 'Received' : 'Not Received'; ?></p>
                <p><strong>Title:</strong> <?php echo isset($_POST['title']) ? escape($_POST['title']) : 'Not Set'; ?></p>
                <p><strong>Content Length:</strong> <?php echo isset($_POST['content']) ? strlen($_POST['content']) : 'Not Set'; ?></p>
            <?php endif; ?>
        </div>

        <div class="article-info">
            <h3>Informasi Artikel</h3>
            <p><strong>ID:</strong> <?php echo $article_id; ?></p>
            <p><strong>Dibuat:</strong> <?php echo isset($article['created_at']) ? date('d/m/Y H:i', strtotime($article['created_at'])) : 'N/A'; ?></p>
            <p><strong>Terakhir Update:</strong> <?php echo isset($article['updated_at']) ? date('d/m/Y H:i', strtotime($article['updated_at'])) : 'Belum pernah'; ?></p>
        </div>

        <?php if ($message): ?>
            <div class="message" role="alert" aria-live="polite"><?php echo escape($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error" role="alert" aria-live="assertive"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" id="articleForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="title">Judul Artikel <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo escape($article['title'] ?? ''); ?>"
                           maxlength="255">
                    <small>Minimal 5 karakter, maksimal 255 karakter</small>
                </div>

                <div class="form-group">
                    <label for="date">Tanggal Publikasi</label>
                    <input type="date" id="date" name="date" 
                           value="<?php echo escape($article['date'] ?? ''); ?>"
                           max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                    <small>Kosongkan untuk menggunakan tanggal hari ini</small>
                </div>

                <div class="form-group">
                    <label for="picture">URL Gambar</label>
                    <input type="url" id="picture" name="picture" 
                           value="<?php echo escape($article['picture'] ?? ''); ?>"
                           placeholder="https://example.com/image.jpg">
                    <small>URL lengkap gambar artikel (opsional)</small>
                </div>

                <div class="form-group">
                    <label for="content">Konten Artikel <span class="required">*</span></label>
                    <textarea id="content" name="content" required 
                              placeholder="Tulis konten artikel di sini..."><?php echo escape($article['content'] ?? ''); ?></textarea>
                    <small>Minimal 50 karakter</small>
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <div class="checkbox-group">
                        <?php if (empty($categories)): ?>
                            <p>Tidak ada kategori tersedia. <a href="add_category.php">Tambah kategori</a></p>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="cat_<?php echo $category['id']; ?>" 
                                           name="categories[]" value="<?php echo $category['id']; ?>"
                                           <?php echo in_array($category['id'], $article_categories ?? []) ? 'checked' : ''; ?>>
                                    <label for="cat_<?php echo $category['id']; ?>">
                                        <?php echo escape($category['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Penulis</label>
                    <div class="checkbox-group">
                        <?php if (empty($authors)): ?>
                            <p>Tidak ada penulis tersedia. <a href="add_author.php">Tambah penulis</a></p>
                        <?php else: ?>
                            <?php foreach ($authors as $author): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="auth_<?php echo $author['id']; ?>" 
                                           name="authors[]" value="<?php echo $author['id']; ?>"
                                           <?php echo in_array($author['id'], $article_authors ?? []) ? 'checked' : ''; ?>>
                                    <label for="auth_<?php echo $author['id']; ?>">
                                        <?php echo escape($author['nickname']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Update Artikel</button>
            </form>
        </div>
    </div>
</main>

<script>
// Basic form validation
document.getElementById('articleForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const content = document.getElementById('content').value.trim();
    
    if (title.length < 5) {
        alert('Judul artikel minimal 5 karakter!');
        e.preventDefault();
        return false;
    }
    
    if (content.length < 50) {
        alert('Konten artikel minimal 50 karakter!');
        e.preventDefault();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.textContent = 'Mengupdate...';
    submitBtn.disabled = true;
});

// Auto-hide messages after 10 seconds
const messages = document.querySelectorAll('.message, .error');
messages.forEach(function(message) {
    setTimeout(function() {
        message.style.opacity = '0.5';
    }, 10000);
});
</script>

</body>
</html>