<?php
// admin/add_category.php
session_start();

// Security: Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$database = new Database();
$conn = $database->getConnection();

$message = '';
$error = '';
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if (empty($name)) {
        $error = 'Nama kategori harus diisi!';
    } elseif (strlen($name) < 2) {
        $error = 'Nama kategori minimal 2 karakter!';
    } elseif (strlen($name) > 100) {
        $error = 'Nama kategori maksimal 100 karakter!';
    } elseif (strlen($description) > 500) {
        $error = 'Deskripsi maksimal 500 karakter!';
    } else {
        try {
            // Check if category name already exists
            $checkQuery = "SELECT COUNT(*) FROM category WHERE LOWER(name) = LOWER(:name)";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name, PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = 'Kategori dengan nama tersebut sudah ada!';
            } else {
                // Insert new category
                $query = "INSERT INTO category (name, description, created_at) VALUES (:name, :description, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    $message = 'Kategori berhasil ditambahkan!';
                    // Clear form data after successful submission
                    $name = '';
                    $description = '';
                } else {
                    $error = 'Gagal menambahkan kategori!';
                }
            }
        } catch (PDOException $e) {
            // Log error for debugging (don't show to user)
            error_log("Database error in add_category.php: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        } catch (Exception $e) {
            error_log("General error in add_category.php: " . $e->getMessage());
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

// Escape output for security
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - MyBeauty Admin</title>
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
            max-width: 800px;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
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
        .form-group textarea:focus {
            border-color: #c79330;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
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

        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            animation: fadeIn 0.5s ease-in;
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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

            .form-container {
                padding: 1rem;
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
            <h1 class="page-title">Tambah Kategori Baru</h1>
            <a href="manage_categories.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo escape($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo escape($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" id="categoryForm">
                <div class="form-group">
                    <label for="name">Nama Kategori *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo escape($name); ?>"
                           placeholder="Masukkan nama kategori"
                           maxlength="100">
                    <div class="char-counter" id="nameCounter">0/100</div>
                    <div class="help-text">Contoh: Perawatan Kulit, Makeup, dll.</div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" 
                              placeholder="Deskripsi kategori (opsional)"
                              maxlength="500"><?php echo escape($description); ?></textarea>
                    <div class="char-counter" id="descCounter">0/500</div>
                    <div class="help-text">Jelaskan tentang kategori ini (maksimal 500 karakter)</div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">Simpan Kategori</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descInput = document.getElementById('description');
    const nameCounter = document.getElementById('nameCounter');
    const descCounter = document.getElementById('descCounter');
    const submitBtn = document.getElementById('submitBtn');

    // Update character counter
    function updateCounter(input, counter, maxLength) {
        const length = input.value.length;
        counter.textContent = length + '/' + maxLength;
        
        if (length > maxLength * 0.9) {
            counter.classList.add('danger');
            counter.classList.remove('warning');
        } else if (length > maxLength * 0.8) {
            counter.classList.add('warning');
            counter.classList.remove('danger');
        } else {
            counter.classList.remove('warning', 'danger');
        }
    }

    // Initialize counters
    updateCounter(nameInput, nameCounter, 100);
    updateCounter(descInput, descCounter, 500);

    // Add event listeners
    nameInput.addEventListener('input', function() {
        updateCounter(this, nameCounter, 100);
    });

    descInput.addEventListener('input', function() {
        updateCounter(this, descCounter, 500);
    });

    // Form validation
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (name.length < 2) {
            e.preventDefault();
            alert('Nama kategori minimal 2 karakter!');
            nameInput.focus();
            return;
        }

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
    });

    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message, .error');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>

</body>
</html>