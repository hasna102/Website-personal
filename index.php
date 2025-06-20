<?php
// index.php - Homepage (Fixed Category Section)
include_once 'config.php';

$database = new Database();
$conn = $database->getConnection();

$articles = getLatestArticles($conn, 7);
$categories = getCategories($conn);

// Handle search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_results = searchArticles($conn, $_GET['search']);
}

// Handle category filter
$category_articles = null;
$selected_category = null;
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_articles = getArticlesByCategory($conn, $_GET['category']);
    $selected_category = getCategoryById($conn, $_GET['category']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLogPribadi - Blog Cerita Menyenangkan</title>
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

        /* ======= Hero Section ======= */
        .hero {
            background: linear-gradient(135deg, #c79330 0%, #ea8b76 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="0,0 1000,0 1000,100 0,80"/></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .hero p {
            font-size: 1.3rem;
            font-weight: 400;
            opacity: 0.95;
            position: relative;
            z-index: 2;
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

        /* ======= Search Results Header ======= */
        .search-header {
            background: white;
            padding: 32px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #c79330;
        }

        .search-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 8px;
        }

        .search-header p {
            color: #666;
            font-size: 1rem;
        }

        /* ======= Category Filter Header ======= */
        .category-header {
            background: white;
            padding: 32px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #ea8b76;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 8px;
        }

        .category-header p {
            color: #666;
            font-size: 1rem;
        }

        .clear-filter {
            display: inline-block;
            padding: 8px 16px;
            background: #f8f9fa;
            color: #666;
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .clear-filter:hover {
            background: #e9ecef;
            color: #333;
        }

        /* ======= Article Cards ======= */
        .article-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .article-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .article-card:hover .article-image {
            transform: scale(1.05);
        }

        .article-meta {
            padding: 20px 24px 0;
            font-size: 0.9rem;
            color: #888;
            font-weight: 500;
        }

        .article-title {
            display: block;
            padding: 12px 24px 0;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
            line-height: 1.4;
        }

        .article-title:hover {
            color: #c79330;
        }

        .article-excerpt {
            padding: 12px 24px;
            color: #666;
            line-height: 1.6;
            font-size: 1rem;
        }

        .read-more {
            display: inline-block;
            margin: 0 24px 24px;
            padding: 10px 20px;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .read-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 147, 48, 0.3);
        }

        /* ======= No Articles State ======= */
        .no-articles {
            background: white;
            padding: 60px 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .no-articles h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #666;
            margin-bottom: 16px;
        }

        .no-articles p {
            color: #888;
            font-size: 1.1rem;
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

        /* ======= Category List ======= */
        .category-list {
            list-style: none;
        }

        .category-list li {
            margin-bottom: 8px;
        }

        .category-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            background: #f8f9fa;
        }

        .category-list a:hover {
            background-color: #e9ecef;
            border-left-color: #c79330;
            color: #c79330;
            transform: translateX(5px);
        }

        .category-list a.active {
            background: linear-gradient(135deg, #c79330 0%, #ea8b76 100%);
            color: white;
            border-left-color: #c79330;
            font-weight: 600;
        }

        .category-list a.active:hover {
            transform: translateX(0);
            background: linear-gradient(135deg, #b8842b 0%, #d97c67 100%);
        }

        .category-count {
            background: rgba(255, 255, 255, 0.2);
            color: inherit;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }

        .category-list a:not(.active) .category-count {
            background: #e0e0e0;
            color: #666;
        }

        .all-categories {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e0e0e0;
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

            .hero {
                padding: 60px 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .article-card {
                margin-bottom: 20px;
            }

            .article-title {
                font-size: 1.3rem;
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

            .category-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 12px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .article-card,
            .sidebar-section {
                padding: 20px;
            }

            .logo {
                font-size: 1.5rem;
            }

            .main-content {
                padding: 20px 0;
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

<section class="hero">
    <div class="container">
        <h1>Selamat Datang di MyLogPribadi</h1>
        <p>Blog Mencari Cerita yang Menyenangkan dan Menginspirasi</p>
    </div>
</section>

<div class="container">
    <div class="main-content">
        <main class="content">
            <?php if ($search_results !== null): ?>
                <div class="search-header">
                    <h2>Hasil Pencarian untuk "<?php echo htmlspecialchars($_GET['search']); ?>"</h2>
                    <p><?php echo count($search_results); ?> artikel ditemukan</p>
                </div>
                <?php $articles = $search_results; ?>
            <?php elseif ($category_articles !== null): ?>
                <div class="category-header">
                    <div>
                        <h2>Kategori: <?php echo htmlspecialchars($selected_category['name']); ?></h2>
                        <p><?php echo count($category_articles); ?> artikel dalam kategori ini</p>
                    </div>
                    <a href="index.php" class="clear-filter">🗑️ Hapus Filter</a>
                </div>
                <?php $articles = $category_articles; ?>
            <?php endif; ?>

            <?php if (empty($articles)): ?>
                <div class="no-articles">
                    <h2>Belum Ada Artikel</h2>
                    <p>
                        <?php if ($search_results !== null): ?>
                            Tidak ada artikel yang sesuai dengan kata kunci "<?php echo htmlspecialchars($_GET['search']); ?>". 
                            Coba dengan kata kunci yang berbeda.
                        <?php elseif ($category_articles !== null): ?>
                            Belum ada artikel dalam kategori "<?php echo htmlspecialchars($selected_category['name']); ?>".
                        <?php else: ?>
                            Artikel menarik akan segera hadir! Pantau terus blog ini untuk mendapatkan cerita-cerita menyenangkan.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <?php if ($article['picture']): ?>
                            <img src="<?php echo htmlspecialchars($article['picture']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                 class="article-image">
                        <?php endif; ?>
                        
                        <div class="article-meta">
                                📅 <?php echo date('d M Y', strtotime($article['date'])); ?>
                                <?php if ($article['authors']): ?>
                                    | ✍️ <?php echo htmlspecialchars($article['authors']); ?>
                                <?php endif; ?>
                                <?php if ($article['categories']): ?>
                                    | 📂 <?php echo htmlspecialchars($article['categories']); ?>
                                <?php endif; ?>
                            </div>
                                                    <a href="article.php?id=<?php echo $article['id']; ?>" class="article-title">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                        
                        <div class="article-excerpt">
                            <?php echo substr(strip_tags($article['content']), 0, 200) . '...'; ?>
                        </div>
                        
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="read-more">
                            Baca Selengkapnya →
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <aside class="sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">🔍 Pencarian</h3>
                <form action="index.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari artikel menarik..." class="search-input" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-btn">Cari</button>
                </form>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">📂 Kategori</h3>
                <ul class="category-list">
                    <li class="all-categories">
                        <a href="index.php" class="<?php echo (!isset($_GET['category']) && !isset($_GET['search'])) ? 'active' : ''; ?>">
                            <span>📋 Semua Artikel</span>
                            <span class="category-count"><?php echo count(getLatestArticles($conn, 999)); ?></span>
                        </a>
                    </li>
                    <?php if (empty($categories)): ?>
                        <li><span style="color: #888; font-style: italic; padding: 12px 16px; display: block;">Belum ada kategori</span></li>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <?php 
                                $category_article_count = count(getArticlesByCategory($conn, $category['id']));
                                $is_active = (isset($_GET['category']) && $_GET['category'] == $category['id']);
                            ?>
                            <li>
                                <a href="index.php?category=<?php echo $category['id']; ?>" 
                                   class="<?php echo $is_active ? 'active' : ''; ?>">
                                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                                    <span class="category-count"><?php echo $category_article_count; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">📖 Tentang Blog</h3>
                <p><strong>MyLogPribadi</strong> adalah blog yang menghadirkan banyak cerita menyenangkan dan menginspirasi. Kami berbagi pengalaman, tips, dan kisah-kisah menarik yang tidak kalah dengan cerita lainnya. Mari bergabung dalam perjalanan mencari cerita yang bermakna!</p>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">💝 Dukungan</h3>
                <p>Jika Anda menyukai konten kami, jangan lupa untuk membagikannya kepada teman-teman. Dukungan Anda sangat berarti bagi kami untuk terus berkarya!</p>
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