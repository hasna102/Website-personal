<?php
// admin/edit_author.php
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

// Get author ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: manage_authors.php');
    exit;
}

$author_id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    
    // Validation
    if (empty($nickname)) {
        $error = 'Nama/Nickname tidak boleh kosong';
    } elseif (empty($email)) {
        $error = 'Email tidak boleh kosong';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Check if email already exists (excluding current author)
        $check_query = "SELECT id FROM author WHERE email = :email AND id != :id";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->bindParam(':id', $author_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Email sudah digunakan oleh penulis lain';
        } else {
            // Update author
            $update_query = "UPDATE author SET nickname = :nickname, email = :email, bio = :bio WHERE id = :id";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bindParam(':nickname', $nickname);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':bio', $bio);
            $update_stmt->bindParam(':id', $author_id);
            
            if ($update_stmt->execute()) {
                $message = 'Penulis berhasil diperbarui!';
            } else {
                $error = 'Terjadi kesalahan saat memperbarui penulis';
            }
        }
    }
}

// Get current author data
$query = "SELECT * FROM author WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $author_id);
$stmt->execute();
$author = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$author) {
    header('Location: manage_authors.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penulis - MyBeauty Admin</title>
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
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #c79330;
        }

        .form-textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            min-height: 120px;
            resize: vertical;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #c79330;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .submit-btn {
            background: #c79330;
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #b8821c;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
            padding: 0.8rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.3s;
        }

        .cancel-btn:hover {
            background: #5a6268;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
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

            .btn-group {
                flex-direction: column;
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
            <h1 class="page-title">Edit Penulis</h1>
            <a href="manage_authors.php" class="back-btn">← Kembali</a>
        </div>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nickname" class="form-label">Nama/Nickname *</label>
                    <input type="text" id="nickname" name="nickname" class="form-input" 
                           value="<?php echo htmlspecialchars($author['nickname']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($author['email']); ?>" required>
                </div>

                <div class="btn-group">
                    <button type="submit" class="submit-btn">Perbarui Penulis</button>
                    <a href="manage_authors.php" class="cancel-btn">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>