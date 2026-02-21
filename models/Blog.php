<?php
// models/Blog.php
require_once 'BaseModel.php';

class Blog extends BaseModel {
    protected $table = 'blog_posts';

    public function create($data) {
        $slug = $this->createSlug($data['title']);
        
        $query = "INSERT INTO " . $this->table . "
                  (title, slug, excerpt, content, featured_image, category, tags, author_id, status, published_at, meta_title, meta_description, meta_keywords, is_featured)
                  VALUES (:title, :slug, :excerpt, :content, :featured_image, :category, :tags, :author_id, :status, :published_at, :meta_title, :meta_description, :meta_keywords, :is_featured)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $slug,
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':featured_image' => $data['featured_image'] ?? null,
            ':category' => $data['category'],
            ':tags' => json_encode($data['tags'] ?? []),
            ':author_id' => $data['author_id'],
            ':status' => $data['status'],
            ':published_at' => $data['status'] == 'published' ? date('Y-m-d H:i:s') : null,
            ':meta_title' => $data['meta_title'] ?? $data['title'],
            ':meta_description' => $data['meta_description'] ?? $data['excerpt'],
            ':meta_keywords' => $data['meta_keywords'] ?? '',
            ':is_featured' => $data['is_featured'] ?? 0
        ]);
    }

    public function update($id, $data) {
        $slug = $this->createSlug($data['title']);
        
        $query = "UPDATE " . $this->table . " SET
                  title = :title,
                  slug = :slug,
                  excerpt = :excerpt,
                  content = :content,
                  featured_image = :featured_image,
                  category = :category,
                  tags = :tags,
                  status = :status,
                  published_at = CASE WHEN :status = 'published' AND published_at IS NULL THEN NOW() ELSE published_at END,
                  meta_title = :meta_title,
                  meta_description = :meta_description,
                  meta_keywords = :meta_keywords,
                  is_featured = :is_featured
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $slug,
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':featured_image' => $data['featured_image'],
            ':category' => $data['category'],
            ':tags' => json_encode($data['tags'] ?? []),
            ':status' => $data['status'],
            ':meta_title' => $data['meta_title'] ?? $data['title'],
            ':meta_description' => $data['meta_description'] ?? $data['excerpt'],
            ':meta_keywords' => $data['meta_keywords'] ?? '',
            ':is_featured' => $data['is_featured'] ?? 0,
            ':id' => $id
        ]);
    }

    public function getPublished() {
        $query = "SELECT p.*, u.username as author_name 
                  FROM " . $this->table . " p
                  LEFT JOIN users u ON p.author_id = u.id
                  WHERE p.status = 'published' 
                  ORDER BY p.published_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function incrementViews($id) {
        $query = "UPDATE " . $this->table . " SET views = views + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    private function createSlug($title) {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($title)));
        $slug = trim($slug, '-');
        
        // Check if slug exists
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE slug = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }
        
        return $slug;
    }
}
?>