<?php
// article.php - Article Detail Page
if (basename($_SERVER['PHP_SELF']) == 'article.php') 
    include_once 'config.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $article_id = isset($_GET['id']) ? $_GET['id'] : 0;
    $article = getArticleById($conn, $article_id);
    $categories = getCategories($conn);
    
    // Get related articles
    $related_query = "SELECT * FROM article WHERE id != :id ORDER BY RAND() LIMIT 5";
    $related_stmt = $conn->prepare($related_query);
    $related_stmt->bindParam(':id', $article_id);
    $related_stmt->execute();
    $related_articles = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$article) {
        header('Location: index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - MyLogPribadi</title>
    <style>
        /* ======= Import Google Font ======= */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap');

        /* ======= Global Reset & Body ======= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #333;
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        /* ======= Header ======= */
        .header {
            background: linear-gradient(90deg, #c79330 0%, #ea8b76 100%);
            color: #fff;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo:hover {
            color: #f0f0f0;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 0;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* ======= Main Content Layout ======= */
        .main-content {
            display: flex;
            gap: 40px;
            padding: 40px 0;
            align-items: flex-start;
        }

        .content {
            flex: 1;
            min-width: 0;
        }

        .sidebar {
            width: 320px;
            flex-shrink: 0;
        }

        /* ======= Back Button ======= */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(199, 147, 48, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(199, 147, 48, 0.4);
        }

        /* ======= Article Content ======= */
        .article-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .article-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: #333;
            line-height: 1.2;
            padding: 40px 40px 20px;
            margin: 0;
        }

        .article-meta {
            padding: 0 40px 20px;
            color: #666;
            font-size: 1rem;
            font-weight: 500;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }

        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            margin-bottom: 30px;
        }

        .article-content {
            padding: 0 40px 40px;
            line-height: 1.8;
            font-size: 1.1rem;
            color: #444;
        }

        .article-content p {
            margin-bottom: 1.5rem;
        }

        .article-content h1,
        .article-content h2,
        .article-content h3,
        .article-content h4,
        .article-content h5,
        .article-content h6 {
            font-family: 'Playfair Display', serif;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .article-content h2 {
            font-size: 1.8rem;
            border-bottom: 2px solid #ea8b76;
            padding-bottom: 0.5rem;
        }

        .article-content h3 {
            font-size: 1.5rem;
            color: #c79330;
        }

        /* ======= Sidebar Sections ======= */
        .sidebar-section {
            background: white;
            border-radius: 15px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease;
        }

        .sidebar-section:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .sidebar-title {
            font-family: 'Inter', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #ea8b76;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ======= Search Box ======= */
        .search-box {
            display: flex;
            gap: 8px;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #c79330;
        }

        .search-btn {
            padding: 12px 20px;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 147, 48, 0.3);
        }

        /* ======= Related Articles ======= */
        .related-article {
            display: block;
            color: #666;
            text-decoration: none;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }

        .related-article:hover {
            background-color: #f8f9fa;
            border-left-color: #c79330;
            color: #c79330;
            transform: translateX(5px);
        }

        /* ======= Category List ======= */
        .category-list {
            list-style: none;
        }

        .category-list li {
            margin-bottom: 8px;
        }

        .category-list a {
            display: block;
            padding: 8px 12px;
            color: #666;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .category-list a:hover {
            background-color: #f8f9fa;
            border-left-color: #c79330;
            color: #c79330;
            transform: translateX(5px);
        }

        /* ======= Footer ======= */
        .footer {
            background: linear-gradient(90deg, #c79330 0%, #ea8b76 100%);
            color: #fff;
            text-align: center;
            padding: 40px 0;
            margin-top: 60px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .footer p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* ======= Admin Button ======= */
        .admin-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .admin-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #444 0%, #666 100%);
        }

        /* ======= Responsive Design ======= */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
                gap: 20px;
            }

            .sidebar {
                width: 100%;
            }

            .nav {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                gap: 10px;
            }

            .article-title {
                font-size: 2.2rem;
                padding: 30px 20px 15px;
            }

            .article-meta {
                padding: 0 20px 15px;
            }

            .article-content {
                padding: 0 20px 30px;
            }

            .article-image {
                height: 250px;
            }

            .search-box {
                flex-direction: column;
            }

            .search-btn {
                width: 100%;
            }

            .admin-btn {
                bottom: 20px;
                right: 20px;
                padding: 10px 16px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 12px;
            }

            .article-title {
                font-size: 1.8rem;
                padding: 20px 15px 10px;
            }

            .article-meta {
                padding: 0 15px 10px;
                font-size: 0.9rem;
            }

            .article-content {
                padding: 0 15px 20px;
                font-size: 1rem;
            }

            .sidebar-section {
                padding: 20px;
            }

            .logo {
                font-size: 1.5rem;
            }

            .main-content {
                padding: 20px 0;
            }

            .back-btn {
                padding: 10px 20px;
                font-size: 0.8rem;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <nav class="nav">
            <a href="index.php" class="logo">MyLogPribadi</a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="tentang.php">Tentang</a></li>
                <li><a href="kontak.php">Kontak</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <div class="main-content">
        <main class="content">
            <a href="index.php" class="back-btn">← Kembali ke Beranda</a>
            
            <article class="article-container">
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <div class="article-meta">
                    📅 <?php echo date('d F Y', strtotime($article['date'])); ?>
                    <?php if ($article['authors']): ?>
                        | ✍️ <?php echo htmlspecialchars($article['authors']); ?>
                    <?php endif; ?>
                    <?php if ($article['categories']): ?>
                        | 📂 <?php echo htmlspecialchars($article['categories']); ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($article['picture']): ?>
                    <img src="<?php echo htmlspecialchars($article['picture']); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                         class="article-image">
                <?php endif; ?>
                
                <div class="article-content">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
            </article>
        </main>

        <aside class="sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">🔍 Pencarian</h3>
                <form action="index.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari artikel menarik..." class="search-input">
                    <button type="submit" class="search-btn">Cari</button>
                </form>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">📖 Artikel Terkait</h3>
                <?php if (empty($related_articles)): ?>
                    <p style="color: #888; font-style: italic;">Belum ada artikel terkait</p>
                <?php else: ?>
                    <?php foreach ($related_articles as $related): ?>
                        <a href="article.php?id=<?php echo $related['id']; ?>" class="related-article">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">📂 Kategori</h3>
                <ul class="category-list">
                    <?php if (empty($categories)): ?>
                        <li><span style="color: #888; font-style: italic;">Belum ada kategori</span></li>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="category.php?id=<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">📖 Tentang Blog</h3>
                <p><strong>MyLogPribadi</strong> adalah blog yang menghadirkan banyak cerita menyenangkan dan menginspirasi. Kami berbagi pengalaman, tips, dan kisah-kisah menarik yang tidak kalah dengan cerita lainnya.</p>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">💝 Dukungan</h3>
                <p>Jika Anda menyukai artikel ini, jangan lupa untuk membagikannya kepada teman-teman. Dukungan Anda sangat berarti bagi kami untuk terus berkarya!</p>
            </div>
        </aside>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; 2025 MyLogPribadi. Dibuat dengan ❤️ untuk berbagi cerita. Semua hak cipta dilindungi.</p>
    </div>
</footer>

<a href="admin/login.php" class="admin-btn">👤 Admin</a>

</body>
</html>