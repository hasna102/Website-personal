<?php
// config.php - Database Configuration
class Database {
    private $host = 'localhost';
    private $db_name = 'database';
    private $username = 'root';
    private $password = 'Nadiyah2911#';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// functions.php - Helper Functions
function getLatestArticles($conn, $limit = 7) {
    $query = "SELECT a.*, GROUP_CONCAT(c.name) as categories 
              FROM article a 
              LEFT JOIN article_category ac ON a.id = ac.article_id 
              LEFT JOIN category c ON ac.category_id = c.id 
              GROUP BY a.id 
              ORDER BY a.date DESC 
              LIMIT :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories($conn) {
    $query = "SELECT * FROM category ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticleById($conn, $id) {
    $query = "SELECT a.*, GROUP_CONCAT(c.name) as categories,
              GROUP_CONCAT(au.nickname) as authors
              FROM article a 
              LEFT JOIN article_category ac ON a.id = ac.article_id 
              LEFT JOIN category c ON ac.category_id = c.id 
              LEFT JOIN article_author aa ON a.id = aa.article_id
              LEFT JOIN author au ON aa.author_id = au.id
              WHERE a.id = :id 
              GROUP BY a.id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getArticlesByCategory($conn, $category_id) {
    $query = "SELECT a.*, c.name as category_name 
              FROM article a 
              JOIN article_category ac ON a.id = ac.article_id 
              JOIN category c ON ac.category_id = c.id 
              WHERE c.id = :category_id 
              ORDER BY a.date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchArticles($conn, $keyword) {
    $query = "SELECT a.*, GROUP_CONCAT(c.name) as categories 
              FROM article a 
              LEFT JOIN article_category ac ON a.id = ac.article_id 
              LEFT JOIN category c ON ac.category_id = c.id 
              WHERE a.title LIKE :keyword OR a.content LIKE :keyword 
              GROUP BY a.id 
              ORDER BY a.date DESC";
    $stmt = $conn->prepare($query);
    $keyword = "%$keyword%";
    $stmt->bindParam(':keyword', $keyword);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>