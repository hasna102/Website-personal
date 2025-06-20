<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - MyLogPribadi</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            color: #333;
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        /* ======= Header ======= */
        .site-header {
            background: linear-gradient(90deg, #c79330 0%, #ea8b76 100%);
            color: #fff;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .navbar h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .navbar nav a {
            color: #fff;
            margin-left: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
            padding: 8px 12px;
            border-radius: 6px;
        }

        .navbar nav a:hover,
        .navbar nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
            text-decoration: none;
        }

        /* ======= Layout Container ======= */
        .container {
            display: flex;
            gap: 32px;
            padding: 40px 24px;
            max-width: 1200px;
            margin: 0 auto;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        /* ======= Main Content ======= */
        .main-content {
            flex: 1 1 65%;
            min-width: 300px;
        }

        /* ======= Card Styling ======= */
        .card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 24px;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #444;
            margin: 32px 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ea8b76;
            display: inline-block;
        }

        .card p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: justify;
            color: #555;
        }

        .card ul {
            margin: 16px 0 24px 20px;
        }

        .card li {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 12px;
            color: #555;
            position: relative;
        }

        .card li::before {
            content: "✓";
            position: absolute;
            left: -20px;
            color: #c79330;
            font-weight: bold;
        }

        .card strong {
            color: #c79330;
            font-weight: 600;
        }

        /* ======= Sidebar ======= */
        .sidebar {
            width: 300px;
            flex-shrink: 0;
        }

        .tentang-box {
            background: linear-gradient(180deg, #ffffff, #f8f9fa);
            border: 1px solid #e0e0e0;
            padding: 24px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .tentang-box h4 {
            font-size: 1.2rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tentang-box p {
            font-size: 1rem;
            line-height: 1.7;
            color: #666;
            text-align: justify;
        }

        /* ======= Footer ======= */
        footer {
            background: linear-gradient(90deg, #c79330 0%, #ea8b76 100%);
            color: #fff;
            text-align: center;
            padding: 30px 20px;
            font-size: 0.95rem;
            margin-top: 60px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        footer a {
            color: #f0f0f0;
            text-decoration: none;
            font-weight: 500;
        }

        footer a:hover {
            color: #fff;
            text-decoration: underline;
        }

        /* ======= Responsive Design ======= */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px 16px;
            }

            .sidebar {
                width: 100%;
            }

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .navbar nav {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .navbar nav a {
                margin: 0;
                padding: 6px 12px;
                font-size: 0.9rem;
            }

            .card {
                padding: 24px;
            }

            .card h2 {
                font-size: 2rem;
            }

            .card h3 {
                font-size: 1.3rem;
            }

            .card p,
            .card li {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 16px 12px;
            }

            .card {
                padding: 20px;
            }

            .card h2 {
                font-size: 1.8rem;
            }

            .navbar h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <div class="navbar">
            <h1>MyLogPribadi</h1>
            <nav>
                <a href="index.php">Beranda</a>
                <a href="tentang.php" class="active">Tentang</a>
                <a href="kontak.php">Kontak</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <main class="main-content">
            <div class="konten-artikel">
                <div class="card">
                    <h2>Tentang Kami</h2>
                    <p>Selamat datang di <strong>MyLogPribadi</strong>! Situs ini dibuat untuk memberikan informasi dan inspirasi tentang destinasi wisata menarik di Indonesia, khususnya keindahan kota Malang dan sekitarnya.</p>
                    
                    <p>Kami percaya bahwa setiap tempat memiliki cerita unik yang layak untuk dibagikan. Melalui blog ini, kami ingin membagikan keindahan alam, kekayaan budaya, dan keunikan kuliner dari berbagai daerah di Nusantara, terutama yang berada di Jawa Timur.</p>

                    <h3>Visi Kami</h3>
                    <p>Menjadi platform referensi utama untuk informasi wisata dan budaya lokal di Indonesia, dengan fokus khusus pada eksplorasi keindahan Malang dan Jawa Timur.</p>

                    <h3>Misi Kami</h3>
                    <ul>
                        <li>Menyediakan informasi wisata yang akurat, lengkap, dan menarik untuk para traveler</li>
                        <li>Memberikan tips dan panduan bermanfaat untuk perjalanan yang berkesan</li>
                        <li>Mendukung dan mempromosikan budaya lokal serta ekonomi kreatif di berbagai daerah</li>
                        <li>Menginspirasi lebih banyak orang untuk mengeksplorasi keindahan Indonesia</li>
                    </ul>

                    <p>Blog ini dikelola dengan penuh dedikasi oleh <strong>Hasna Nadiyah</strong>, seorang content writer dan travel enthusiast yang memiliki passion mendalam dalam mengeksplorasi dan mendokumentasikan keindahan Nusantara. Dengan pengalaman bertahun-tahun dalam dunia penulisan dan fotografi perjalanan, kami berkomitmen untuk terus memberikan konten berkualitas tinggi.</p>

                    <p>Mari bergabung dengan kami dalam perjalanan menjelajahi Indonesia yang penuh warna dan kekayaan budaya!</p>
                </div>
            </div>
        </main>

        <aside class="sidebar">
            <div class="tentang-box">
                <h4>Profil Singkat</h4>
                <p><strong>MyLogPribadi</strong> adalah media digital yang berdedikasi untuk berbagi cerita, pengalaman, dan rekomendasi menarik tentang destinasi wisata di seluruh penjuru Indonesia. Kami menghadirkan konten autentik yang menginspirasi para pembaca untuk menjelajahi keindahan tanah air.</p>
            </div>
        </aside>
    </div>

    <footer>
        <p>&copy; 2025 MyLogPribadi. Dibuat dengan ❤️ untuk Indonesia. Semua hak cipta dilindungi.</p>
    </footer>

</body>
</html>