<?php
// admin/edit_category.php
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
$category_id = '';

// Get category ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: manage_categories.php');
    exit;
}

$category_id = (int)$_GET['id'];

// Get existing category data
try {
    $query = "SELECT * FROM category WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        $error = 'Kategori tidak ditemukan!';
    } else {
        $name = $category['name'];
        $description = $category['description'] ?? '';  // Handle null description
    }
} catch (PDOException $e) {
    error_log("Database error in edit_category.php: " . $e->getMessage());
    $error = 'Terjadi kesalahan saat mengambil data kategori.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
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
            // Check if category name already exists (exclude current category)
            $checkQuery = "SELECT COUNT(*) FROM category WHERE LOWER(name) = LOWER(:name) AND id != :id";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name, PDO::PARAM_STR);
            $checkStmt->bindParam(':id', $category_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = 'Kategori dengan nama tersebut sudah ada!';
            } else {
                // Update category
                $updateQuery = "UPDATE category SET name = :name, description = :description WHERE id = :id";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(':name', $name, PDO::PARAM_STR);
                $updateStmt->bindParam(':description', $description, PDO::PARAM_STR);
                $updateStmt->bindParam(':id', $category_id, PDO::PARAM_INT);
                
                if ($updateStmt->execute()) {
                    $message = 'Kategori berhasil diperbarui!';
                } else {
                    $error = 'Gagal memperbarui kategori!';
                }
            }
        } catch (PDOException $e) {
            // Log error for debugging (don't show to user)
            error_log("Database error in edit_category.php: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        } catch (Exception $e) {
            error_log("General error in edit_category.php: " . $e->getMessage());
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
    <title>Edit Kategori - MyBeauty Admin</title>
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

        .form-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
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
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #c79330;
        }

        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.3s;
        }

        .cancel-btn:hover {
            background: #5a6268;
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

        .category-info {
            background: #e9ecef;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            color: #495057;
        }

        .category-info strong {
            color: #333;
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

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .form-actions .cancel-btn {
                text-align: center;
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
            <h1 class="page-title">Edit Kategori</h1>
            <a href="manage_categories.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo escape($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo escape($error); ?></div>
        <?php endif; ?>

        <?php if (!$error && $category): ?>
            <div class="form-container">
                <div class="category-info">
                    <strong>ID Kategori:</strong> <?php echo $category_id; ?><br>
                    <strong>Dibuat:</strong> <?php echo isset($category['created_at']) ? date('d/m/Y H:i', strtotime($category['created_at'])) : 'Tidak tersedia'; ?>
                    <?php if (isset($category['updated_at']) && $category['updated_at']): ?>
                        <br><strong>Terakhir diperbarui:</strong> <?php echo date('d/m/Y H:i', strtotime($category['updated_at'])); ?>
                    <?php endif; ?>
                </div>

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

                    <div class="form-actions">
                        <button type="submit" class="submit-btn" id="submitBtn">Perbarui Kategori</button>
                        <a href="manage_categories.php" class="cancel-btn">Batal</a>
                    </div>
                </form>
            </div>
        <?php elseif ($error && !$category): ?>
            <div class="form-container">
                <p style="text-align: center; padding: 2rem;">
                    <a href="manage_categories.php" class="back-btn">← Kembali ke Daftar Kategori</a>
                </p>
            </div>
        <?php endif; ?>
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

    // Initialize counters if inputs exist
    if (nameInput && nameCounter) {
        updateCounter(nameInput, nameCounter, 100);
        nameInput.addEventListener('input', function() {
            updateCounter(this, nameCounter, 100);
        });
    }

    if (descInput && descCounter) {
        updateCounter(descInput, descCounter, 500);
        descInput.addEventListener('input', function() {
            updateCounter(this, descCounter, 500);
        });
    }

    // Form validation
    const categoryForm = document.getElementById('categoryForm');
    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            const name = nameInput.value.trim();
            
            if (name.length < 2) {
                e.preventDefault();
                alert('Nama kategori minimal 2 karakter!');
                nameInput.focus();
                return;
            }

            // Disable submit button to prevent double submission
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memperbarui...';
            }
        });
    }

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

    // Confirm navigation away if form has changes
    let originalName = nameInput ? nameInput.value : '';
    let originalDesc = descInput ? descInput.value : '';
    
    window.addEventListener('beforeunload', function(e) {
        if (nameInput && descInput) {
            if (nameInput.value !== originalName || descInput.value !== originalDesc) {
                e.preventDefault();
                e.returnValue = '';
            }
        }
    });

    // Don't show confirm dialog when form is submitted
    if (categoryForm) {
        categoryForm.addEventListener('submit', function() {
            window.removeEventListener('beforeunload', arguments.callee);
        });
    }
});
</script>

</body>
</html>