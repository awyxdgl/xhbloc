<?php
/**
 * API 处理文件
 * 版本：3.0 - 交互细节打磨版
 * 处理所有后台数据请求，包括配置保存、图片上传、留言提交等
 * 
 * 【上传路径说明】
 * - 图片上传目录：uploads/
 * - 数据存储目录：data/
 * - 请确保这两个目录有写入权限
 */

header('Content-Type: application/json; charset=utf-8');

define('DATA_DIR', __DIR__ . '/data/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

/**
 * 返回 JSON 响应
 */
function jsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

/**
 * 保存 JSON 数据到文件
 */
function saveJsonFile($filename, $data) {
    $filepath = DATA_DIR . $filename;
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents($filepath, $json) !== false;
}

/**
 * 加载 JSON 数据
 */
function loadJsonFile($filename) {
    $filepath = DATA_DIR . $filename;
    return file_exists($filepath) ? json_decode(file_get_contents($filepath), true) : [];
}

/**
 * 处理图片上传
 */
function handleImageUpload($file, $prefix = 'img') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/' . $filename;
    }
    
    return false;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'submit_suggestion':
        handleSuggestionSubmit();
        break;
        
    case 'save_config':
        handleConfigSave();
        break;
        
    case 'save_contact':
        handleContactSave();
        break;
        
    case 'save_contact_list':
        handleContactListSave();
        break;
        
    case 'save_subsidiaries':
        handleListSave('subsidiaries');
        break;
        
    case 'save_branches':
        handleListSave('branches');
        break;
        
    case 'save_business':
        handleBusinessSave();
        break;
        
    case 'save_media':
        handleMediaSave();
        break;
        
    case 'save_links':
        handleLinksSave();
        break;
        
    case 'save_filing':
        handleFilingSave();
        break;
        
    default:
        jsonResponse(false, '无效的操作');
    }

/**
 * 处理用户建议提交
 */
function handleSuggestionSubmit() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $message = trim($input['message'] ?? '');
    
    if (empty($name) || empty($phone) || empty($message)) {
        jsonResponse(false, '请填写完整信息');
    }
    
    $suggestions = loadJsonFile('suggestions.json');
    
    $suggestions[] = [
        'name' => $name,
        'phone' => $phone,
        'message' => $message,
        'time' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    if (saveJsonFile('suggestions.json', $suggestions)) {
        jsonResponse(true, '提交成功，感谢您的建议！');
    } else {
        jsonResponse(false, '提交失败，请稍后重试');
    }
}

/**
 * 处理网站配置保存
 */
function handleConfigSave() {
    $data = json_decode($_POST['data'] ?? '{}', true);
    
    $config = loadJsonFile('config.json');
    
    foreach ($data as $key => $value) {
        $config[$key] = $value;
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageField = $_POST['image_field'] ?? 'image';
        $imagePath = handleImageUpload($_FILES['image'], $imageField);
        if ($imagePath) {
            $config[$imageField] = $imagePath;
        }
    }
    
    if (saveJsonFile('config.json', $config)) {
        jsonResponse(true, '配置保存成功');
    } else {
        jsonResponse(false, '配置保存失败');
    }
}

/**
 * 处理联系方式保存（旧版兼容）
 */
function handleContactSave() {
    $data = json_decode($_POST['data'] ?? '{}', true);
    
    $contact = loadJsonFile('contact.json');
    
    foreach ($data as $key => $value) {
        $contact[$key] = $value;
    }
    
    if (isset($_FILES['qq_qrcode']) && $_FILES['qq_qrcode']['error'] === UPLOAD_ERR_OK) {
        $imagePath = handleImageUpload($_FILES['qq_qrcode'], 'qq_qrcode');
        if ($imagePath) {
            $contact['qq_qrcode'] = $imagePath;
        }
    }
    
    if (isset($_FILES['wechat_qrcode']) && $_FILES['wechat_qrcode']['error'] === UPLOAD_ERR_OK) {
        $imagePath = handleImageUpload($_FILES['wechat_qrcode'], 'wechat_qrcode');
        if ($imagePath) {
            $contact['wechat_qrcode'] = $imagePath;
        }
    }
    
    if (saveJsonFile('contact.json', $contact)) {
        jsonResponse(true, '联系方式保存成功');
    } else {
        jsonResponse(false, '联系方式保存失败');
    }
}

/**
 * 处理联系方式列表保存
 */
function handleContactListSave() {
    $data = json_decode($_POST['data'] ?? '[]', true);
    
    $processedData = [];
    foreach ($data as $index => $item) {
        $processedItem = [
            'type' => $item['type'] ?? 'default',
            'name' => $item['name'] ?? '',
            'label' => $item['label'] ?? '号码/账号',
            'value' => $item['value'] ?? '',
            'remark' => $item['remark'] ?? '',
            'qrcode' => $item['qrcode'] ?? ''
        ];
        
        if (!empty($processedItem['name']) || !empty($processedItem['value'])) {
            $processedData[] = $processedItem;
        }
    }
    
    if (saveJsonFile('contact_list.json', $processedData)) {
        jsonResponse(true, '联系方式保存成功');
    } else {
        jsonResponse(false, '联系方式保存失败');
    }
}

/**
 * 处理列表数据保存（子公司/子机构）
 * 支持全能图片引用（本地路径、网络地址、SVG代码）和动态详情配置
 */
function handleListSave($filename) {
    $data = json_decode($_POST['data'] ?? '[]', true);
    
    $processedData = [];
    foreach ($data as $index => $item) {
        $processedItem = [
            'name' => $item['name'] ?? '',
            'link' => $item['link'] ?? '',
            'description' => $item['description'] ?? '',
            'card_image' => $item['card_image'] ?? $item['image_source'] ?? '',
            'modal_logo' => $item['modal_logo'] ?? '',
            'logo_color' => $item['logo_color'] ?? '#667eea',
            'details' => []
        ];
        
        if (isset($item['details']) && is_array($item['details'])) {
            foreach ($item['details'] as $detail) {
                if (!empty($detail['label']) || !empty($detail['value'])) {
                    $processedItem['details'][] = [
                        'label' => $detail['label'] ?? '',
                        'value' => $detail['value'] ?? ''
                    ];
                }
            }
        }
        
        if (!empty($processedItem['name']) || !empty($processedItem['description'])) {
            $processedData[] = $processedItem;
        }
    }
    
    if (saveJsonFile($filename . '.json', $processedData)) {
        jsonResponse(true, '数据保存成功');
    } else {
        jsonResponse(false, '数据保存失败');
    }
}

/**
 * 处理业务数据保存
 */
function handleBusinessSave() {
    $data = json_decode($_POST['data'] ?? '[]', true);
    
    $processedData = [];
    foreach ($data as $index => $item) {
        $processedItem = [
            'title' => $item['title'] ?? '',
            'description' => $item['description'] ?? '',
            'icon' => $item['icon'] ?? ''
        ];
        
        if (!empty($processedItem['title']) || !empty($processedItem['description'])) {
            $processedData[] = $processedItem;
        }
    }
    
    if (saveJsonFile('business.json', $processedData)) {
        jsonResponse(true, '业务数据保存成功');
    } else {
        jsonResponse(false, '业务数据保存失败');
    }
}

/**
 * 处理流媒体矩阵保存
 */
function handleMediaSave() {
    $data = json_decode($_POST['data'] ?? '[]', true);
    
    $processedData = [];
    foreach ($data as $item) {
        $processedItem = [
            'type' => $item['type'] ?? 'default',
            'name' => $item['name'] ?? '',
            'nickname' => $item['nickname'] ?? '',
            'uid' => $item['uid'] ?? '',
            'link' => $item['link'] ?? '',
            'remark' => $item['remark'] ?? '',
            'svg_icon' => $item['svg_icon'] ?? ''
        ];
        
        if (!empty($processedItem['name']) || !empty($processedItem['link'])) {
            $processedData[] = $processedItem;
        }
    }
    
    if (saveJsonFile('media.json', $processedData)) {
        jsonResponse(true, '流媒体矩阵保存成功');
    } else {
        jsonResponse(false, '流媒体矩阵保存失败');
    }
}

/**
 * 处理友情链接保存
 */
function handleLinksSave() {
    $data = json_decode($_POST['data'] ?? '[]', true);
    
    $processedData = [];
    foreach ($data as $item) {
        $processedItem = [
            'name' => $item['name'] ?? '',
            'url' => $item['url'] ?? ''
        ];
        
        if (!empty($processedItem['name']) || !empty($processedItem['url'])) {
            $processedData[] = $processedItem;
        }
    }
    
    if (saveJsonFile('links.json', $processedData)) {
        jsonResponse(true, '友情链接保存成功');
    } else {
        jsonResponse(false, '友情链接保存失败');
    }
}

/**
 * 处理备案号保存
 */
function handleFilingSave() {
    $data = json_decode($_POST['data'] ?? '[]', true);
    
    $processedData = [];
    foreach ($data as $item) {
        $processedItem = [
            'name' => $item['name'] ?? '',
            'display_type' => $item['display_type'] ?? 'text',
            'link' => $item['link'] ?? '',
            'modal_content' => $item['modal_content'] ?? '',
            'icon' => $item['icon'] ?? ''
        ];
        
        if (!empty($processedItem['name'])) {
            $processedData[] = $processedItem;
        }
    }
    
    if (saveJsonFile('filing.json', $processedData)) {
        jsonResponse(true, '备案号保存成功');
    } else {
        jsonResponse(false, '备案号保存失败');
    }
}
