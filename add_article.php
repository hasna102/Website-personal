<?php
// admin/add_article.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$database = new Database();
$conn = $database->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $picture = $_POST['picture'];
    $categories = $_POST['categories'] ?? [];
    $authors = $_POST['authors'] ?? [];
    $date = $_POST['date'];

    if (!empty($title) && !empty($content)) {
        try {
            // Insert article
            $query = "INSERT INTO article (title, content, picture, date) VALUES (:title, :content, :picture, :date)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':picture', $picture);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            $article_id = $conn->lastInsertId();
            
            // Insert categories
            if (!empty($categories)) {
                $cat_query = "INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)";
                $cat_stmt = $conn->prepare($cat_query);
                foreach ($categories as $category_id) {
                    $cat_stmt->bindParam(':article_id', $article_id);
                    $cat_stmt->bindParam(':category_id', $category_id);
                    $cat_stmt->execute();
                }
            }
            
            // Insert authors
            if (!empty($authors)) {
                $auth_query = "INSERT INTO article_author (article_id, author_id) VALUES (:article_id, :author_id)";
                $auth_stmt = $conn->prepare($auth_query);
                foreach ($authors as $author_id) {
                    $auth_stmt->bindParam(':article_id', $article_id);
                    $auth_stmt->bindParam(':author_id', $author_id);
                    $auth_stmt->execute();
                }
            }
            
            $message = 'Artikel berhasil ditambahkan!';
        } catch (Exception $e) {
            $error = 'Gagal menambahkan artikel: ' . $e->getMessage();
        }
    } else {
        $error = 'Judul dan konten artikel harus diisi!';
    }
}

// Get categories and authors
$categories = $conn->query("SELECT * FROM category ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$authors = $conn->query("SELECT * FROM author ORDER BY nickname")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel - MyBeauty Admin</title>
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
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #218838;
        }

        .message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
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
            }
        }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="container">
        <nav class="admin-nav">
            <div class="admin-logo">MyLogPribadi Admin</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_articles.php">Artikel</a></li>
                <li><a href="manage_categories.php">Kategori</a></li>
                <li><a href="manage_authors.php">Penulis</a></li>
                <li><a href="../index.php" target="_blank">Lihat Blog</a></li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Tambah Artikel Baru</h1>
            <a href="manage_articles.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Judul Artikel *</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Masukkan judul artikel">
                    </div>

                    <div class="form-group">
                        <label for="date">Tanggal Publikasi</label>
                        <input type="date" id="date" name="date" 
                               value="<?php echo date('Y-m-d'); ?>">
                        <div class="help-text">Kosongkan untuk menggunakan tanggal hari ini</div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="picture">URL Gambar</label>
                    <input type="url" id="picture" name="picture" 
                           placeholder="https://example.com/gambar.jpg">
                    <div class="help-text">Masukkan URL gambar untuk artikel (opsional)</div>
                </div>

                <div class="form-group full-width">
                    <label for="content">Konten Artikel *</label>
                    <textarea id="content" name="content" required 
                              placeholder="Tulis konten artikel di sini..."></textarea>
                    <div class="help-text">Tulis konten lengkap artikel Anda</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <div class="checkbox-group">
                            <?php foreach ($categories as $category): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="cat_<?php echo $category['id']; ?>" 
                                           name="categories[]" value="<?php echo $category['id']; ?>">
                                    <label for="cat_<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($categories)): ?>
                            <div class="help-text">Belum ada kategori. <a href="add_category.php">Tambah kategori</a></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Penulis</label>
                        <div class="checkbox-group">
                            <?php foreach ($authors as $author): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="auth_<?php echo $author['id']; ?>" 
                                           name="authors[]" value="<?php echo $author['id']; ?>">
                                    <label for="auth_<?php echo $author['id']; ?>">
                                        <?php echo htmlspecialchars($author['nickname']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($authors)): ?>
                            <div class="help-text">Belum ada penulis. <a href="add_author.php">Tambah penulis</a></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Simpan Artikel</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
