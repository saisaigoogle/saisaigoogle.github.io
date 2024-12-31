<?php
require_once 'config.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 生成唯一ID
        $id = md5(uniqid(rand(), true));
        
        // 获取并处理内容（保留换行符但去除首尾空格）
        $content = trim($_POST['content'] ?? '');
        // 统一换行符
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        // 最多允许连续2个换行符
        $content = preg_replace("/\n{3,}/", "\n\n", $content);
        
        $views_limit = (int)($_POST['views_limit'] ?? 1);
        
        // 验证查看次数
        if ($views_limit < 1) {
            throw new Exception('查看次数必须大于0');
        }
        
        $image_path = '';
        
// 处理图片上传
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    // 验证文件类型
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('只允许上传 JPG, PNG 或 GIF 格式的图片');
    }
    
    // 验证文件大小（最大 5MB）
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        throw new Exception('图片大小不能超过 5MB');
    }
    
    // 准备上传目录
    $upload_dir = __DIR__ . '/uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // 获取文件扩展名
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        throw new Exception('不支持的文件格式');
    }
    
    // 生成文件名和路径
    $image_filename = $id . '.' . $file_ext;
    $image_path = 'uploads/' . $image_filename; // 数据库中存储相对路径
    $full_path = $upload_dir . $image_filename; // 完整物理路径
    
    // 移动上传的文件
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
        throw new Exception('文件上传失败');
    }
    
    // 设置文件权限
    chmod($full_path, 0644);
}

        
        // 验证至少有内容或图片之一
        if (empty($content) && empty($image_path)) {
            throw new Exception('请输入文字内容或上传图片');
        }
        
        // 保存到数据库
        $stmt = $pdo->prepare("INSERT INTO burn_messages (id, content, image_path, views_limit, views_count) VALUES (?, ?, ?, ?, 0)");
        if (!$stmt->execute([$id, $content, $image_path, $views_limit])) {
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
            throw new Exception('保存失败');
        }
        
        header("Location: result.php?id=" . urlencode($id));
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
