<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - MyLogPribadi</title>
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
            margin-bottom: 24px;
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
            color: #555;
        }

        .card hr {
            border: none;
            height: 2px;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            margin: 30px 0;
            border-radius: 2px;
        }

        /* ======= Contact Form ======= */
        .contact-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin: 24px 0;
            border: 1px solid #e9ecef;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
            font-size: 1rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #c79330;
            box-shadow: 0 0 0 3px rgba(199, 147, 48, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-submit {
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(199, 147, 48, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 147, 48, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ======= Contact Info ======= */
        .contact-info {
            background: #f8f9fa;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            border-color: #c79330;
            box-shadow: 0 2px 8px rgba(199, 147, 48, 0.1);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(to right, #c79330 0%, #ea8b76 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            color: white;
            font-weight: bold;
        }

        .contact-details {
            flex: 1;
        }

        .contact-details strong {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .contact-details a {
            color: #c79330;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-details a:hover {
            color: #ea8b76;
            text-decoration: underline;
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

            .form-row {
                grid-template-columns: 1fr;
            }

            .contact-form {
                padding: 20px;
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

            .contact-item {
                flex-direction: column;
                text-align: center;
            }

            .contact-icon {
                margin: 0 0 12px 0;
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
                <a href="tentang.php">Tentang</a>
                <a href="kontak.php" class="active">Kontak</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <main class="main-content">
            <div class="konten-artikel">
                <div class="card">
                    <h2>Hubungi Kami</h2>
                    <p>Kami senang mendengar dari Anda! Jika Anda memiliki pertanyaan, kritik, saran, atau ingin berbagi cerita perjalanan, jangan ragu untuk menghubungi kami melalui formulir di bawah ini atau kontak langsung.</p>

                    <!-- Formulir Kontak -->
                    <div class="contact-form">
                        <h3 style="margin-top: 0; border: none; padding: 0;">Kirim Pesan</h3>
                        <form action="#" method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nama">Nama Lengkap *</label>
                                    <input type="text" id="nama" name="nama" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="telepon">Nomor Telepon</label>
                                    <input type="tel" id="telepon" name="telepon">
                                </div>
                                <div class="form-group">
                                    <label for="subjek">Subjek *</label>
                                    <select id="subjek" name="subjek" required>
                                        <option value="">Pilih Subjek</option>
                                        <option value="pertanyaan">Pertanyaan Umum</option>
                                        <option value="saran">Saran & Kritik</option>
                                        <option value="kolaborasi">Kolaborasi</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="pesan">Pesan *</label>
                                <textarea id="pesan" name="pesan" placeholder="Tulis pesan Anda di sini..." required></textarea>
                            </div>

                            <button type="submit" class="btn-submit">Kirim Pesan</button>
                        </form>
                    </div>

                    <hr>

                    <!-- Informasi Kontak -->
                    <div class="contact-info">
                        <h3 style="margin-top: 0; border: none; padding: 0; margin-bottom: 20px;">Kontak Langsung</h3>
                        
                        <div class="contact-item">
                            <div class="contact-icon">@</div>
                            <div class="contact-details">
                                <strong>Email</strong>
                                <a href="mailto:hasnanadiyah1605@gmail.com">hasnanadiyah1605@gmail.com</a>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">IG</div>
                            <div class="contact-details">
                                <strong>Instagram</strong>
                                <a href="https://instagram.com/hahasnong" target="_blank">@hahasnong</a>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">WA</div>
                            <div class="contact-details">
                                <strong>WhatsApp</strong>
                                <a href="https://wa.me/6281330727585" target="_blank">+62 813-3072-7585</a>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div class="contact-details">
                                <strong>Lokasi</strong>
                                <span>Malang, Jawa Timur, Indonesia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <aside class="sidebar">
            <div class="tentang-box">
                <h4>Info Respon</h4>
                <p>Kami sangat menghargai setiap masukan dan pertanyaan dari Anda. Tim kami akan berusaha merespons pesan Anda dalam waktu maksimal 24 jam. Untuk pertanyaan mendesak, silakan hubungi kami melalui WhatsApp.</p>
            </div>
            
            <div class="tentang-box" style="margin-top: 20px;">
                <h4>Jam Operasional</h4>
                <p><strong>Senin - Jumat:</strong><br>09:00 - 17:00 WIB</p>
                <p><strong>Sabtu - Minggu:</strong><br>10:00 - 15:00 WIB</p>
            </div>
        </aside>
    </div>

    <footer>
        <p>&copy; 2025 MyLogPribadi. Dibuat dengan ❤️ untuk Indonesia. Semua hak cipta dilindungi.</p>
    </footer>

</body>
</html>