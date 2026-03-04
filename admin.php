<?php
/**
 * 后台管理系统
 * 版本：10.0 - 性能重构与精准定位版
 * 
 * 【修改密码说明】
 * 在下方 ADMIN_USERNAME 和 ADMIN_PASSWORD 常量处修改
 * 默认账号：admin
 * 默认密码：123456
 * 
 * 【V10.0 更新内容】
 * - 同步前端性能优化
 * - 滚动条样式美化
 * - 后台UI紧凑化保持
 */

/* ==================== 登录配置（在此修改密码） ==================== */
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '123456');

/* ==================== 系统初始化 ==================== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DATA_DIR', __DIR__ . '/data/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0755, true);
}
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

/* ==================== Session 处理 ==================== */
$session_error = '';
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        $session_error = 'Session 启动失败，请检查 PHP 配置';
    }
}

/* ==================== 登录诊断信息 ==================== */
$login_error = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (empty($_POST['username'])) {
        $login_error = '请输入用户名';
        $debug_info = 'POST 数据：username 为空';
    } elseif (empty($_POST['password'])) {
        $login_error = '请输入密码';
        $debug_info = 'POST 数据：password 为空';
    } elseif ($session_error) {
        $login_error = '系统错误：' . $session_error;
    } else {
        $input_username = trim($_POST['username']);
        $input_password = $_POST['password'];
        
        if ($input_username !== ADMIN_USERNAME) {
            $login_error = '用户名不存在';
            $debug_info = "输入: {$input_username}, 配置: " . ADMIN_USERNAME;
        } elseif ($input_password !== ADMIN_PASSWORD) {
            $login_error = '密码错误';
            $debug_info = "密码不匹配，请检查配置";
        } else {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $input_username;
            $_SESSION['login_time'] = time();
            
            if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                $login_error = 'Session 写入失败';
                $debug_info = '请检查 session.save_path 是否可写';
            } else {
                header('Location: admin.php');
                exit;
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

/* ==================== 数据加载函数 ==================== */
function loadJsonData($filename) {
    $filepath = DATA_DIR . $filename;
    if (!file_exists($filepath)) {
        return [];
    }
    $content = @file_get_contents($filepath);
    if ($content === false) {
        return [];
    }
    $decoded = json_decode($content, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
}

/* ==================== 图标渲染函数 ==================== */
function renderIconPreview($source) {
    if (empty($source)) {
        return '';
    }
    
    $source = trim($source);
    
    // SVG 代码
    if (stripos($source, '<svg') === 0) {
        return '<div class="icon-preview-svg">' . $source . '</div>';
    }
    
    // 网络资源
    if (stripos($source, 'http://') === 0 || stripos($source, 'https://') === 0) {
        return '<img src="' . htmlspecialchars($source) . '" class="icon-preview-img" alt="图标">';
    }
    
    // 本地资源路径
    if (stripos($source, 'assets/') === 0 || stripos($source, '/assets/') === 0 || stripos($source, 'uploads/') === 0) {
        return '<img src="' . htmlspecialchars($source) . '" class="icon-preview-img" alt="图标">';
    }
    
    // 内置图标库
    $builtinIcons = [
        'qq' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>',
        'wechat' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 01.213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 00.167-.054l1.903-1.114a.864.864 0 01.717-.098 10.16 10.16 0 002.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178A1.17 1.17 0 014.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178 1.17 1.17 0 01-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 01.598.082l1.584.926a.272.272 0 00.14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 01-.023-.156.49.49 0 01.201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.036 2.96c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982z"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
        'douyin' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>',
        'bilibili' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 01-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 01.16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>',
        'weibo' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.194.573zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.579-.18-.405-.649.381-1.017.422-1.896-.001-2.52-.789-1.165-2.943-1.102-5.387-.03 0 0-.772.334-.575-.272.383-1.217.324-2.236-.271-2.823-1.349-1.336-4.938-.04-8.018 2.896C1.102 10.878 0 13.022 0 14.898c0 3.586 4.604 5.767 9.109 5.767 5.905 0 9.835-3.424 9.835-6.149 0-1.643-1.388-2.577-2.855-3.067zm2.003-5.376c-.632-.756-1.565-1.143-2.479-1.143-.311 0-.623.046-.924.14-.317.104-.498.429-.396.746.103.317.427.498.745.396.191-.063.391-.094.591-.094.564 0 1.146.239 1.543.713.396.474.535 1.083.391 1.635-.089.322.099.656.421.745.322.089.656-.099.745-.421.233-.886.013-1.86-.737-2.817zm1.931-2.31c-1.265-1.514-3.131-2.29-4.959-2.29-.623 0-1.248.089-1.856.271-.317.095-.5.427-.405.744.095.317.427.5.744.405.493-.148 1.006-.221 1.517-.221 1.511 0 3.047.641 4.086 1.886 1.039 1.245 1.401 2.861 1.056 4.339-.076.324.125.649.449.725.324.076.649-.125.725-.449.442-1.895-.024-3.971-1.357-5.41z"/></svg>',
        'default' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>'
    ];
    
    if (isset($builtinIcons[$source])) {
        return '<div class="icon-preview-svg icon-builtin" data-icon="' . htmlspecialchars($source) . '">' . $builtinIcons[$source] . '</div>';
    }
    
    // 默认返回文本
    return '<span class="icon-preview-text">' . htmlspecialchars($source) . '</span>';
}

/* ==================== 万能资源预览渲染函数 ==================== */
function renderResourcePreview($source, $alt = '预览') {
    if (empty($source)) {
        return '<div class="resource-preview-empty">暂无预览</div>';
    }
    
    $source = trim($source);
    
    // SVG 代码
    if (stripos($source, '<svg') === 0) {
        return '<div class="resource-preview-svg">' . $source . '</div>';
    }
    
    // 网络资源或本地路径
    if (stripos($source, 'http://') === 0 || stripos($source, 'https://') === 0 || 
        stripos($source, 'assets/') === 0 || stripos($source, '/assets/') === 0 || 
        stripos($source, 'uploads/') === 0) {
        return '<img src="' . htmlspecialchars($source) . '" alt="' . htmlspecialchars($alt) . '" class="resource-preview-img">';
    }
    
    // 图标类名
    return '<div class="resource-preview-icon"><i class="' . htmlspecialchars($source) . '"></i><span>' . htmlspecialchars($source) . '</span></div>';
}

/* ==================== 带发光外壳的图标渲染函数 ==================== */
function renderIconPreviewWithGlow($source, $color = '#6366f1') {
    if (empty($source)) {
        $source = 'default';
    }
    
    $source = trim($source);
    $color = trim($color);
    
    // 验证颜色格式
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        $color = '#6366f1';
    }
    
    // 将十六进制颜色转换为带透明度的 rgba
    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));
    $glowColor = "rgba({$r}, {$g}, {$b}, 0.5)";
    
    // 内置图标库
    $builtinIcons = [
        'qq' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>',
        'wechat' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 01.213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 00.167-.054l1.903-1.114a.864.864 0 01.717-.098 10.16 10.16 0 002.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178A1.17 1.17 0 014.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178 1.17 1.17 0 01-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 01.598.082l1.584.926a.272.272 0 00.14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 01-.023-.156.49.49 0 01.201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.036 2.96c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982z"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
        'douyin' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>',
        'bilibili' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 01-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 01.16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>',
        'weibo' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.194.573zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.579-.18-.405-.649.381-1.017.422-1.896-.001-2.52-.789-1.165-2.943-1.102-5.387-.03 0 0-.772.334-.575-.272.383-1.217.324-2.236-.271-2.823-1.349-1.336-4.938-.04-8.018 2.896C1.102 10.878 0 13.022 0 14.898c0 3.586 4.604 5.767 9.109 5.767 5.905 0 9.835-3.424 9.835-6.149 0-1.643-1.388-2.577-2.855-3.067zm2.003-5.376c-.632-.756-1.565-1.143-2.479-1.143-.311 0-.623.046-.924.14-.317.104-.498.429-.396.746.103.317.427.498.745.396.191-.063.391-.094.591-.094.564 0 1.146.239 1.543.713.396.474.535 1.083.391 1.635-.089.322.099.656.421.745.322.089.656-.099.745-.421.233-.886.013-1.86-.737-2.817zm1.931-2.31c-1.265-1.514-3.131-2.29-4.959-2.29-.623 0-1.248.089-1.856.271-.317.095-.5.427-.405.744.095.317.427.5.744.405.493-.148 1.006-.221 1.517-.221 1.511 0 3.047.641 4.086 1.886 1.039 1.245 1.401 2.861 1.056 4.339-.076.324.125.649.449.725.324.076.649-.125.725-.449.442-1.895-.024-3.971-1.357-5.41z"/></svg>',
        'default' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>'
    ];
    
    // 获取图标内容
    $iconContent = '';
    
    // SVG 代码
    if (stripos($source, '<svg') === 0) {
        $iconContent = $source;
    }
    // 网络资源
    elseif (stripos($source, 'http://') === 0 || stripos($source, 'https://') === 0) {
        $iconContent = '<img src="' . htmlspecialchars($source) . '" alt="图标" style="width:60%;height:60%;object-fit:contain;">';
    }
    // 本地资源路径
    elseif (stripos($source, 'assets/') === 0 || stripos($source, '/assets/') === 0 || stripos($source, 'uploads/') === 0) {
        $iconContent = '<img src="' . htmlspecialchars($source) . '" alt="图标" style="width:60%;height:60%;object-fit:contain;">';
    }
    // 内置图标
    elseif (isset($builtinIcons[$source])) {
        $iconContent = $builtinIcons[$source];
    }
    // 默认图标
    else {
        $iconContent = $builtinIcons['default'];
    }
    
    // 生成带发光外壳的 HTML
    return '<div class="icon-glow-wrapper" style="background-color:' . $color . ';box-shadow:0 4px 15px ' . $glowColor . ';">' . $iconContent . '</div>';
}

$config = loadJsonData('config.json');
$subsidiaries = loadJsonData('subsidiaries.json');
$business = loadJsonData('business.json');
$suggestions = loadJsonData('suggestions.json');
$media = loadJsonData('media.json');
$links = loadJsonData('links.json');
$contact_list = loadJsonData('contact_list.json');
$filing = loadJsonData('filing.json');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理系统</title>
    <style>
        :root {
            --bg-gradient-primary: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            --accent-primary: #667eea;
            --accent-secondary: #764ba2;
            --accent-tertiary: #f093fb;
            --accent-gradient: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 50%, var(--accent-tertiary) 100%);
            --glow-primary: rgba(102, 126, 234, 0.4);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.85);
            --text-muted: rgba(255, 255, 255, 0.6);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-bg-hover: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-border-hover: rgba(102, 126, 234, 0.4);
            --glass-blur: blur(20px);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.4);
            --shadow-glow: 0 0 20px var(--glow-primary);
            --transition-normal: 0.3s;
        }
        
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            border-radius: 10px;
            box-shadow: 0 0 10px var(--glow-primary);
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, var(--accent-secondary) 0%, var(--accent-tertiary) 100%);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', sans-serif;
            background: var(--bg-gradient-primary);
            min-height: 100vh;
            color: var(--text-primary);
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }
        
        .login-container::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.2) 0%, transparent 50%);
            animation: bgFloat 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }
        
        @keyframes bgFloat {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(2%, 2%) rotate(1deg); }
        }
        
        .login-box {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            padding: 40px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            border: 1px solid var(--glass-border);
        }
        
        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }
        
        .login-box h1 {
            text-align: center;
            margin-bottom: 30px;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group.compact {
            margin-bottom: 10px;
        }
        
        .form-group.mini {
            margin-bottom: 8px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--glass-border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all var(--transition-normal);
            font-family: inherit;
            color: var(--text-primary);
        }
        
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
        
        .form-group select option {
            background: #1a1a2e;
            color: var(--text-primary);
            padding: 10px;
        }
        
        .form-group select:hover {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            background: var(--accent-gradient);
            color: var(--text-primary);
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all var(--transition-normal);
            text-decoration: none;
        }
        
        .btn:hover {
            box-shadow: var(--shadow-md), var(--shadow-glow);
        }
        
        .btn-full { width: 100%; }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }
        .btn-xs { padding: 4px 8px; font-size: 0.75rem; }
        .btn-success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
        .btn-danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
        .btn-warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        
        .error-msg {
            background: linear-gradient(145deg, rgba(255, 107, 107, 0.3) 0%, rgba(231, 76, 60, 0.2) 100%);
            color: #ff8a8a;
            padding: 12px 15px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 107, 107, 0.4);
        }
        
        .debug-info {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            padding: 10px 15px;
            border-radius: var(--radius-md);
            margin-bottom: 15px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 193, 7, 0.4);
            word-break: break-all;
        }
        
        .login-hint {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            color: var(--text-muted);
            border: 1px solid var(--glass-border);
        }
        
        .login-hint strong { color: var(--text-secondary); }
        
        .admin-container { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 250px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-right: 1px solid var(--glass-border);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .sidebar-header h2 {
            font-size: 1.3rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar-header p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 12px 20px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all var(--transition-normal);
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: var(--glass-bg-hover);
            color: var(--text-primary);
            border-left-color: var(--accent-primary);
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logout-btn {
            color: #ff6b6b;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid rgba(255, 107, 107, 0.4);
            border-radius: var(--radius-md);
            transition: all var(--transition-normal);
        }
        
        .logout-btn:hover { background: rgba(255, 107, 107, 0.2); }
        
        .content-section {
            display: none;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            padding: 24px;
            border-radius: var(--radius-xl);
            border: 1px solid var(--glass-border);
        }
        
        .content-section.active { display: block; }
        
        .section-title {
            font-size: 1.2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--glass-border);
        }
        
        .section-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-row.compact {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .form-row.inline-3 {
            grid-template-columns: 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: var(--radius-md);
            border: 2px solid var(--glass-border);
        }
        
        .image-preview.small {
            max-width: 64px;
            max-height: 64px;
            border-radius: var(--radius-sm);
        }
        
        .hint-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: normal;
        }
        
        .icon-input-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .icon-input-group input {
            flex: 1;
        }
        
        .icon-preview-box {
            margin-top: 8px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-sm);
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon-preview-svg {
            width: 32px;
            height: 32px;
            color: var(--text-primary);
        }
        
        .icon-preview-svg svg {
            width: 100%;
            height: 100%;
        }
        
        .icon-preview-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 4px;
        }
        
        .icon-preview-text {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        
        .icon-picker-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .icon-picker-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 20px;
            max-width: 400px;
            width: 90%;
        }
        
        .icon-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .icon-picker-header span {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .icon-picker-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .icon-picker-close:hover {
            color: var(--text-primary);
        }
        
        .icon-picker-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        
        .icon-picker-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 12px 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .icon-picker-item:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        
        .icon-picker-item svg {
            width: 28px;
            height: 28px;
            color: var(--text-primary);
        }
        
        .icon-picker-item span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .color-input-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .color-picker {
            width: 40px;
            height: 32px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            padding: 0;
            background: transparent;
        }
        
        .color-picker::-webkit-color-swatch-wrapper {
            padding: 2px;
        }
        
        .color-picker::-webkit-color-swatch {
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .color-input-group input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: monospace;
        }
        
        .icon-glow-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .icon-glow-wrapper svg {
            width: 60%;
            height: 60%;
            color: #ffffff;
        }
        
        .icon-glow-wrapper:hover {
            transform: scale(1.05);
        }
        
        /* 万能资源输入框样式 */
        .resource-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .resource-input-group input {
            flex: 1;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .resource-input-group input:focus {
            outline: none;
            border-color: var(--glass-border-hover);
        }
        
        .resource-preview-box {
            margin-top: 12px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .resource-preview-img {
            max-width: 100%;
            max-height: 200px;
            border-radius: var(--radius-sm);
            object-fit: contain;
        }
        
        .resource-preview-svg {
            width: 100%;
            max-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .resource-preview-svg svg {
            width: 100px;
            height: 100px;
            color: var(--text-primary);
        }
        
        .resource-preview-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
        }
        
        .resource-preview-icon i {
            font-size: 48px;
        }
        
        .resource-preview-empty {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .card-list { display: grid; gap: 15px; }
        
        .card-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 16px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--glass-border);
        }
        
        .card-item.compact {
            padding: 12px;
        }
        
        .card-item.compact .card-item-header {
            margin-bottom: 10px;
        }
        
        .card-item.compact .form-group {
            margin-bottom: 10px;
        }
        
        .card-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-item-header h4 { color: var(--text-primary); }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .file-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background: var(--accent-gradient);
            color: var(--text-primary);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-normal);
        }
        
        .file-label:hover {
            box-shadow: var(--shadow-glow);
        }
        
        .file-name {
            margin-left: 10px;
            color: var(--text-muted);
        }
        
        .suggestion-list { max-height: 500px; overflow-y: auto; }
        
        .suggestion-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 15px;
            border-left: 4px solid var(--accent-primary);
        }
        
        .suggestion-item .meta {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        
        .suggestion-item .name {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .suggestion-item .content {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: var(--radius-md);
            color: var(--text-primary);
            z-index: 9999;
            display: none;
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
        }
        
        .toast.success {
            background: linear-gradient(145deg, rgba(46, 204, 113, 0.3) 0%, rgba(39, 174, 96, 0.2) 100%);
            border-color: rgba(46, 204, 113, 0.4);
        }
        
        .toast.error {
            background: linear-gradient(145deg, rgba(255, 107, 107, 0.3) 0%, rgba(231, 76, 60, 0.2) 100%);
            border-color: rgba(255, 107, 107, 0.4);
        }
        
        .toast.show {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .seo-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
        }
        
        .seo-section h4 { color: var(--text-primary); margin-bottom: 15px; }
        
        .contact-type-select {
            padding: 10px;
            border-radius: var(--radius-md);
            border: 2px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            width: 100%;
            margin-bottom: 10px;
        }
        
        .contact-type-select option {
            background: #1a1a2e;
            color: var(--text-primary);
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 1fr 2fr auto;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-md);
        }
        
        .detail-row input {
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .detail-row input:focus {
            outline: none;
            border-color: var(--glass-border-hover);
        }
        
        .logo-preview-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .logo-preview {
            width: 120px;
            height: 120px;
            border-radius: var(--radius-md);
            border: 2px solid var(--glass-border);
            object-fit: contain;
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
        }
        
        .favicon-preview {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-sm);
            border: 2px solid var(--glass-border);
            object-fit: contain;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px;
        }
        
        .upload-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .upload-group .preview-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; }
            .form-row { grid-template-columns: 1fr; }
            .detail-row { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 576px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; }
            .admin-container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
    <div class="login-container">
        <div class="login-box">
            <h1>后台管理系统</h1>
            
            <?php if (!empty($login_error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($debug_info)): ?>
            <div class="debug-info">诊断信息：<?php echo htmlspecialchars($debug_info); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($session_error)): ?>
            <div class="debug-info">Session 错误：<?php echo htmlspecialchars($session_error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required placeholder="请输入用户名" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required placeholder="请输入密码">
                </div>
                <button type="submit" class="btn btn-full">登录</button>
            </form>
            <div class="login-hint">
                <strong>默认账号：</strong>admin<br>
                <strong>默认密码：</strong>123456<br>
                <strong style="color: #ffc107;">修改密码请编辑 admin.php 顶部的常量定义</strong>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>后台管理</h2>
                <p>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#" data-section="site-config" class="active">网站配置</a></li>
                <li><a href="#" data-section="seo-config">SEO设置</a></li>
                <li><a href="#" data-section="about-config">关于我们</a></li>
                <li><a href="#" data-section="subsidiaries">旗下公司管理</a></li>
                <li><a href="#" data-section="business">业务管理</a></li>
                <li><a href="#" data-section="contact-list">联系方式管理</a></li>
                <li><a href="#" data-section="media">流媒体矩阵</a></li>
                <li><a href="#" data-section="filing">备案号管理</a></li>
                <li><a href="#" data-section="links">友情链接</a></li>
                <li><a href="#" data-section="suggestions">留言管理</a></li>
                <li><a href="admin.php?action=logout" class="logout-btn" style="margin-top: 20px; display: block; text-align: center;">退出登录</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="page-header">
                <h1>后台管理系统</h1>
            </div>
            
            <section id="site-config" class="content-section active">
                <h3 class="section-title">网站基本配置</h3>
                <p class="section-subtitle">配置网站标题、Logo 和 Favicon</p>
                <form id="site-config-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tab_title">浏览器标签页标题 (Tab Title)</label>
                            <input type="text" id="tab_title" name="tab_title" value="<?php echo htmlspecialchars($config['tab_title'] ?? $config['site_title'] ?? ''); ?>" placeholder="显示在浏览器标签页的名称">
                        </div>
                        <div class="form-group">
                            <label for="site_title">网页内部标题 (Site Title)</label>
                            <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($config['site_title'] ?? ''); ?>" placeholder="显示在导航栏的名称">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>网站 Logo</label>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px;">用于导航栏或首页显示，建议尺寸：200x60px</p>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                支持：①本地路径(如 assets/logo.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)
                            </p>
                            <textarea name="site_logo" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"><?php echo htmlspecialchars($config['site_logo'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>标签页图标 (Favicon)</label>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px;">浏览器选项卡小图标，建议尺寸：32x32px 或 64x64px</p>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                支持：①本地路径(如 assets/favicon.ico) ②网络地址(https://...)
                            </p>
                            <input type="text" name="favicon" value="<?php echo htmlspecialchars($config['favicon'] ?? ''); ?>" placeholder="输入本地路径或网络地址">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hero_title">首页大标题</label>
                            <input type="text" id="hero_title" name="hero_title" value="<?php echo htmlspecialchars($config['hero_title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="hero_subtitle">首页副标题</label>
                            <input type="text" id="hero_subtitle" name="hero_subtitle" value="<?php echo htmlspecialchars($config['hero_subtitle'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>首页背景图</label>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                支持：①本地路径(如 assets/bg.jpg) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)
                            </p>
                            <textarea name="hero_bg" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"><?php echo htmlspecialchars($config['hero_bg'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="footer_text">页脚版权信息</label>
                            <input type="text" id="footer_text" name="footer_text" value="<?php echo htmlspecialchars($config['footer_text'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn">保存配置</button>
                </form>
            </section>
            
            <section id="seo-config" class="content-section">
                <h3 class="section-title">SEO设置</h3>
                <div class="seo-section">
                    <h4>搜索引擎优化配置</h4>
                    <form id="seo-config-form">
                        <div class="form-group">
                            <label for="seo_title">SEO标题</label>
                            <input type="text" id="seo_title" name="seo_title" value="<?php echo htmlspecialchars($config['seo_title'] ?? ''); ?>" placeholder="网站在搜索引擎中显示的标题">
                        </div>
                        <div class="form-group">
                            <label for="seo_keywords">SEO关键词</label>
                            <input type="text" id="seo_keywords" name="seo_keywords" value="<?php echo htmlspecialchars($config['seo_keywords'] ?? ''); ?>" placeholder="多个关键词用逗号分隔">
                        </div>
                        <div class="form-group">
                            <label for="seo_description">SEO描述</label>
                            <textarea id="seo_description" name="seo_description" rows="3" placeholder="网站在搜索引擎中显示的描述文字"><?php echo htmlspecialchars($config['seo_description'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn">保存SEO配置</button>
                    </form>
                </div>
            </section>
            
            <section id="about-config" class="content-section">
                <h3 class="section-title">关于我们配置</h3>
                <p class="section-subtitle">配置公司简介、详细信息列表和图片</p>
                <form id="about-config-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="about_title">板块标题</label>
                            <input type="text" id="about_title" name="about_title" value="<?php echo htmlspecialchars($config['about_title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>关于我们图片 <span class="hint-text">(路径/链接/SVG)</span></label>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px;">支持：本地路径、网络链接、SVG代码</p>
                            <div class="resource-input-group">
                                <input type="text" id="about_image" name="about_image" value="<?php echo htmlspecialchars($config['about_image'] ?? ''); ?>" placeholder="如：/assets/img/about.png、https://...、<svg>...">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="showResourcePicker('about_image')">选择资源</button>
                            </div>
                            <div class="resource-preview-box" id="about_image_preview_box">
                                <?php if (!empty($config['about_image'])): ?>
                                <?php echo renderResourcePreview($config['about_image'], '关于我们图片'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>图片显示比例</label>
                            <select name="about_image_ratio" id="about_image_ratio" style="max-width: 200px;">
                                <option value="16:9" <?php echo (isset($config['about_image_ratio']) && $config['about_image_ratio'] === '16:9') ? 'selected' : ''; ?>>16:9 (宽屏)</option>
                                <option value="4:3" <?php echo (isset($config['about_image_ratio']) && $config['about_image_ratio'] === '4:3') ? 'selected' : ''; ?>>4:3 (标准)</option>
                                <option value="1:1" <?php echo (isset($config['about_image_ratio']) && $config['about_image_ratio'] === '1:1') ? 'selected' : ''; ?>>1:1 (正方形)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="about_content">公司简介内容（支持换行）</label>
                        <textarea id="about_content" name="about_content" rows="6" placeholder="简短介绍，显示在页面主体"><?php echo htmlspecialchars($config['about_content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="seo-section" style="margin-top: 20px;">
                        <h4>详细信息列表（动态管理）</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px;">点击"查看详情"按钮后弹窗显示的内容，如社会信用代码、地址等</p>
                        
                        <div id="about-details-container">
                            <?php 
                            $about_details = $config['about_details'] ?? [];
                            if (!empty($about_details)):
                                foreach ($about_details as $index => $detail):
                            ?>
                            <div class="detail-row" data-index="<?php echo $index; ?>">
                                <input type="text" name="detail_label[]" value="<?php echo htmlspecialchars($detail['label'] ?? ''); ?>" placeholder="信息项名称（如：社会信用代码）">
                                <input type="text" name="detail_value[]" value="<?php echo htmlspecialchars($detail['value'] ?? ''); ?>" placeholder="具体内容">
                                <button type="button" class="btn btn-danger btn-xs" onclick="removeDetailRow(this)">删除</button>
                            </div>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </div>
                        
                        <button type="button" class="btn btn-success btn-sm" onclick="addDetailRow()" style="margin-top: 10px;">添加信息行</button>
                    </div>
                    
                    <button type="submit" class="btn" style="margin-top: 20px;">保存配置</button>
                </form>
            </section>
            
            <section id="subsidiaries" class="content-section">
                <h3 class="section-title">旗下公司管理</h3>
                <p class="section-subtitle">支持本地资源、网络地址、SVG代码作为图片源</p>
                <button class="btn btn-success btn-sm" onclick="addItem('subsidiaries')">添加公司</button>
                <div id="subsidiaries-list" class="card-list" style="margin-top: 20px;">
                    <?php if (!empty($subsidiaries)): ?>
                        <?php foreach ($subsidiaries as $index => $item): ?>
                        <div class="card-item" data-index="<?php echo $index; ?>">
                            <div class="card-item-header">
                                <h4>公司 #<?php echo $index + 1; ?></h4>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(this, 'subsidiaries')">删除</button>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>公司名称</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>官网链接（可选）</label>
                                    <input type="text" name="link" value="<?php echo htmlspecialchars($item['link'] ?? ''); ?>" placeholder="https://">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>公司简介</label>
                                <textarea name="description" rows="2" style="resize: both;"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>列表卡片图（4:3）</label>
                                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                        支持：①本地路径(如 assets/logo.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)
                                    </p>
                                    <textarea name="card_image" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"><?php echo htmlspecialchars($item['card_image'] ?? $item['image_source'] ?? $item['image'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>详情弹窗 Logo（1:1）</label>
                                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                        支持：①本地路径(如 assets/logo.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)
                                    </p>
                                    <textarea name="modal_logo" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"><?php echo htmlspecialchars($item['modal_logo'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Logo 发光颜色（十六进制）</label>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                    用于详情弹窗 Logo 的背景色和发光阴影，如：#667eea
                                </p>
                                <input type="text" name="logo_color" value="<?php echo htmlspecialchars($item['logo_color'] ?? '#667eea'); ?>" placeholder="#667eea" style="max-width: 200px;">
                            </div>
                            <div class="seo-section" style="margin-top: 15px;">
                                <h4>详细信息（动态配置）</h4>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">点击"查看详情"按钮后弹窗显示的内容</p>
                                <div class="subsidiary-details-container" data-company-index="<?php echo $index; ?>">
                                    <?php 
                                    $details = $item['details'] ?? [];
                                    if (!empty($details)):
                                        foreach ($details as $dIndex => $detail):
                                    ?>
                                    <div class="detail-row subsidiary-detail-row">
                                        <input type="text" name="detail_label[]" value="<?php echo htmlspecialchars($detail['label'] ?? ''); ?>" placeholder="信息项名称（如：成立日期）">
                                        <input type="text" name="detail_value[]" value="<?php echo htmlspecialchars($detail['value'] ?? ''); ?>" placeholder="具体内容">
                                        <button type="button" class="btn btn-danger btn-xs" onclick="removeSubsidiaryDetailRow(this)">删除</button>
                                    </div>
                                    <?php 
                                        endforeach;
                                    endif; 
                                    ?>
                                </div>
                                <button type="button" class="btn btn-success btn-xs" onclick="addSubsidiaryDetailRow(this)" style="margin-top: 10px;">添加信息行</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn" onclick="saveList('subsidiaries')">保存所有公司</button>
            </section>
            
            <section id="business" class="content-section">
                <h3 class="section-title">业务管理</h3>
                <button class="btn btn-success btn-sm" onclick="addItem('business')">添加业务</button>
                <div id="business-list" class="card-list" style="margin-top: 20px;">
                    <?php if (!empty($business)): ?>
                        <?php foreach ($business as $index => $item): ?>
                        <div class="card-item" data-index="<?php echo $index; ?>">
                            <div class="card-item-header">
                                <h4>业务 #<?php echo $index + 1; ?></h4>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(this, 'business')">删除</button>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>业务名称</label>
                                    <input type="text" name="title" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>业务图标</label>
                                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                        支持：①本地路径(如 assets/icon.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)
                                    </p>
                                    <textarea name="icon" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"><?php echo htmlspecialchars($item['icon'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>业务描述</label>
                                <textarea name="description" rows="2"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn" onclick="saveBusiness()">保存所有业务</button>
            </section>
            
            <section id="contact-list" class="content-section">
                <h3 class="section-title">联系方式管理</h3>
                <p class="section-subtitle">管理联系方式，支持自定义图标、颜色和内容</p>
                <button class="btn btn-success btn-sm" onclick="addContactItem()">添加联系方式</button>
                <div id="contact-list-container" class="card-list" style="margin-top: 15px;">
                    <?php if (!empty($contact_list)): ?>
                        <?php foreach ($contact_list as $index => $item): ?>
                        <div class="card-item compact" data-index="<?php echo $index; ?>">
                            <div class="card-item-header">
                                <h4>联系方式 #<?php echo $index + 1; ?></h4>
                                <button type="button" class="btn btn-danger btn-xs" onclick="deleteItem(this, 'contact_list')">删除</button>
                            </div>
                            <div class="form-row compact">
                                <div class="form-group mini">
                                    <label>图标资源 <span class="hint-text">(路径/链接/SVG/图标名)</span></label>
                                    <div class="icon-input-group">
                                        <input type="text" name="icon" value="<?php echo htmlspecialchars($item['icon'] ?? $item['type'] ?? ''); ?>" placeholder="如：qq、wechat、https://...、<svg>...">
                                        <button type="button" class="btn btn-xs btn-secondary" onclick="showIconPicker(this)">内置图标</button>
                                    </div>
                                </div>
                                <div class="form-group mini">
                                    <label>背景填充色 <span class="hint-text">(十六进制，如：#6366f1)</span></label>
                                    <div class="color-input-group">
                                        <input type="color" name="color_picker" value="<?php echo htmlspecialchars($item['color'] ?? '#6366f1'); ?>" class="color-picker" onchange="this.nextElementSibling.value = this.value">
                                        <input type="text" name="color" value="<?php echo htmlspecialchars($item['color'] ?? '#6366f1'); ?>" placeholder="#6366f1" pattern="^#[0-9A-Fa-f]{6}$" oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) this.previousElementSibling.value = this.value">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row compact">
                                <div class="form-group mini">
                                    <label>图标预览</label>
                                    <div class="icon-preview-box" id="contact_icon_preview_<?php echo $index; ?>" data-color="<?php echo htmlspecialchars($item['color'] ?? '#6366f1'); ?>">
                                        <?php 
                                        $iconSource = $item['icon'] ?? $item['type'] ?? '';
                                        $iconColor = $item['color'] ?? '#6366f1';
                                        if (!empty($iconSource)) {
                                            echo renderIconPreviewWithGlow($iconSource, $iconColor);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group mini">
                                    <label>显示名称</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" placeholder="如：客服QQ、官方微信">
                                </div>
                            </div>
                            <div class="form-row compact">
                                <div class="form-group mini">
                                    <label>标签名称</label>
                                    <input type="text" name="label" value="<?php echo htmlspecialchars($item['label'] ?? '号码/账号'); ?>" placeholder="如：QQ号、微信号、电话">
                                </div>
                                <div class="form-group mini">
                                    <label>详细内容</label>
                                    <input type="text" name="value" value="<?php echo htmlspecialchars($item['value'] ?? ''); ?>" placeholder="具体号码或账号">
                                </div>
                            </div>
                            <div class="form-row compact">
                                <div class="form-group mini">
                                    <label>备注（可选）</label>
                                    <input type="text" name="remark" value="<?php echo htmlspecialchars($item['remark'] ?? ''); ?>" placeholder="如：工作时间 9:00-18:00">
                                </div>
                                <div class="form-group mini">
                                    <label>二维码图片（可选）</label>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 6px;">
                                        支持：①本地路径(如 assets/qrcode.png) ②网络地址(https://...)
                                    </p>
                                    <input type="text" name="qrcode" value="<?php echo htmlspecialchars($item['qrcode'] ?? ''); ?>" placeholder="输入本地路径或网络地址">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn" onclick="saveContactList()">保存所有联系方式</button>
            </section>
            
            <section id="media" class="content-section">
                <h3 class="section-title">流媒体矩阵</h3>
                <button class="btn btn-success btn-sm" onclick="addMediaItem()">添加平台</button>
                <div id="media-list-container" class="card-list" style="margin-top: 20px;">
                    <?php if (!empty($media)): ?>
                        <?php foreach ($media as $index => $item): ?>
                        <div class="card-item" data-index="<?php echo $index; ?>">
                            <div class="card-item-header">
                                <h4>平台 #<?php echo $index + 1; ?></h4>
                                <button type="button" class="btn btn-danger btn-xs" onclick="deleteItem(this, 'media')">删除</button>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>平台类型</label>
                                    <select name="type" class="contact-type-select">
                                        <option value="douyin" <?php echo ($item['type'] ?? '') === 'douyin' ? 'selected' : ''; ?>>抖音</option>
                                        <option value="bilibili" <?php echo ($item['type'] ?? '') === 'bilibili' ? 'selected' : ''; ?>>B站</option>
                                        <option value="weibo" <?php echo ($item['type'] ?? '') === 'weibo' ? 'selected' : ''; ?>>微博</option>
                                        <option value="xiaohongshu" <?php echo ($item['type'] ?? '') === 'xiaohongshu' ? 'selected' : ''; ?>>小红书</option>
                                        <option value="custom" <?php echo ($item['type'] ?? '') === 'custom' ? 'selected' : ''; ?>>自定义</option>
                                        <option value="default" <?php echo ($item['type'] ?? '') === 'default' ? 'selected' : ''; ?>>其他</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>平台名称</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" placeholder="如：官方抖音号">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>账号昵称（可选）</label>
                                    <input type="text" name="nickname" value="<?php echo htmlspecialchars($item['nickname'] ?? ''); ?>" placeholder="如：企业官方号">
                                </div>
                                <div class="form-group">
                                    <label>账号UID（可选）</label>
                                    <input type="text" name="uid" value="<?php echo htmlspecialchars($item['uid'] ?? ''); ?>" placeholder="如：12345678">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>跳转链接</label>
                                <input type="text" name="link" value="<?php echo htmlspecialchars($item['link'] ?? ''); ?>" placeholder="https://">
                            </div>
                            <div class="form-group">
                                <label>自定义SVG图标代码（可选，留空使用内置图标）</label>
                                <textarea name="svg_icon" rows="3" placeholder="<svg viewBox='0 0 24 24' ...>...</svg>"><?php echo htmlspecialchars($item['svg_icon'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn" onclick="saveMedia()">保存流媒体矩阵</button>
            </section>
            
            <section id="filing" class="content-section">
                <h3 class="section-title">备案号管理</h3>
                <p class="section-subtitle">管理底部备案号显示，支持跳转链接或弹窗显示</p>
                <button class="btn btn-success btn-sm" onclick="addFilingItem()">添加备案项</button>
                <div id="filing-list-container" class="card-list" style="margin-top: 15px;">
                    <?php if (!empty($filing)): ?>
                        <?php foreach ($filing as $index => $item): ?>
                        <div class="card-item compact" data-index="<?php echo $index; ?>">
                            <div class="card-item-header">
                                <h4>备案项 #<?php echo $index + 1; ?></h4>
                                <button type="button" class="btn btn-danger btn-xs" onclick="deleteItem(this, 'filing')">删除</button>
                            </div>
                            <div class="form-row compact">
                                <div class="form-group mini">
                                    <label>名称</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" placeholder="如：京ICP备XXXXXXXX号">
                                </div>
                                <div class="form-group mini">
                                    <label>显示类型</label>
                                    <select name="display_type" class="contact-type-select" onchange="toggleFilingFields(this)">
                                        <option value="link" <?php echo ($item['display_type'] ?? '') === 'link' ? 'selected' : ''; ?>>跳转链接</option>
                                        <option value="modal" <?php echo ($item['display_type'] ?? '') === 'modal' ? 'selected' : ''; ?>>弹窗显示</option>
                                        <option value="text" <?php echo ($item['display_type'] ?? '') === 'text' || empty($item['display_type']) ? 'selected' : ''; ?>>仅文本</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mini filing-link-field" style="<?php echo ($item['display_type'] ?? '') !== 'link' ? 'display: none;' : ''; ?>">
                                <label>跳转链接</label>
                                <input type="text" name="link" value="<?php echo htmlspecialchars($item['link'] ?? ''); ?>" placeholder="https://">
                            </div>
                            <div class="form-group mini filing-modal-field" style="<?php echo ($item['display_type'] ?? '') !== 'modal' ? 'display: none;' : ''; ?>">
                                <label>弹窗内容（支持换行）</label>
                                <textarea name="modal_content" rows="3" placeholder="弹窗显示的详细内容"><?php echo htmlspecialchars($item['modal_content'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group mini">
                                <label>自定义图标SVG（可选）</label>
                                <textarea name="icon" rows="2" placeholder="<svg viewBox='0 0 24 24' ...>...</svg>"><?php echo htmlspecialchars($item['icon'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn" onclick="saveFiling()">保存备案号</button>
            </section>
            
            <section id="links" class="content-section">
                <h3 class="section-title">友情链接</h3>
                <button class="btn btn-success btn-sm" onclick="addLinkItem()">添加链接</button>
                <div id="links-list-container" class="card-list" style="margin-top: 20px;">
                    <?php if (!empty($links)): ?>
                        <?php foreach ($links as $index => $item): ?>
                        <div class="card-item" data-index="<?php echo $index; ?>">
                            <div class="card-item-header">
                                <h4>链接 #<?php echo $index + 1; ?></h4>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(this, 'links')">删除</button>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>网站名称</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>网站链接</label>
                                    <input type="text" name="url" value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>" placeholder="https://">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn" onclick="saveLinks()">保存友情链接</button>
            </section>
            
            <section id="suggestions" class="content-section">
                <h3 class="section-title">用户留言管理</h3>
                <div class="suggestion-list">
                    <?php if (!empty($suggestions)): ?>
                        <?php foreach (array_reverse($suggestions) as $item): ?>
                        <div class="suggestion-item">
                            <div class="meta">
                                <span class="name"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span> | </span>
                                <span><?php echo htmlspecialchars($item['phone']); ?></span>
                                <span> | </span>
                                <span><?php echo htmlspecialchars($item['time'] ?? ''); ?></span>
                            </div>
                            <div class="content"><?php echo nl2br(htmlspecialchars($item['message'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted); text-align: center; padding: 30px;">暂无用户留言</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
    
    <div id="toast" class="toast"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSidebar();
            initForms();
            initIconInputListeners();
            initResourceInputListeners();
        });
        
        function initSidebar() {
            var menuLinks = document.querySelectorAll('.sidebar-menu a[data-section]');
            var sections = document.querySelectorAll('.content-section');
            
            menuLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var sectionId = this.dataset.section;
                    
                    menuLinks.forEach(function(l) { l.classList.remove('active'); });
                    this.classList.add('active');
                    
                    sections.forEach(function(s) { s.classList.remove('active'); });
                    document.getElementById(sectionId).classList.add('active');
                });
            });
        }
        
        function initForms() {
            document.getElementById('site-config-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveConfig(new FormData(this), ['tab_title', 'site_title', 'hero_title', 'hero_subtitle', 'footer_text', 'site_logo', 'favicon', 'hero_bg'], null);
            });
            
            document.getElementById('seo-config-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveConfig(new FormData(this), ['seo_title', 'seo_keywords', 'seo_description'], null);
            });
            
            document.getElementById('about-config-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveAboutConfig();
            });
        }
        
        // 内置图标列表
        var builtinIcons = {
            'qq': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>',
            'wechat': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 01.213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 00.167-.054l1.903-1.114a.864.864 0 01.717-.098 10.16 10.16 0 002.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178A1.17 1.17 0 014.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178 1.17 1.17 0 01-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 01.598.082l1.584.926a.272.272 0 00.14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 01-.023-.156.49.49 0 01.201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.036 2.96c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982z"/></svg>',
            'phone': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
            'email': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
            'douyin': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>',
            'bilibili': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 01-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 01.16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>',
            'weibo': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.194.573zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.579-.18-.405-.649.381-1.017.422-1.896-.001-2.52-.789-1.165-2.943-1.102-5.387-.03 0 0-.772.334-.575-.272.383-1.217.324-2.236-.271-2.823-1.349-1.336-4.938-.04-8.018 2.896C1.102 10.878 0 13.022 0 14.898c0 3.586 4.604 5.767 9.109 5.767 5.905 0 9.835-3.424 9.835-6.149 0-1.643-1.388-2.577-2.855-3.067zm2.003-5.376c-.632-.756-1.565-1.143-2.479-1.143-.311 0-.623.046-.924.14-.317.104-.498.429-.396.746.103.317.427.498.745.396.191-.063.391-.094.591-.094.564 0 1.146.239 1.543.713.396.474.535 1.083.391 1.635-.089.322.099.656.421.745.322.089.656-.099.745-.421.233-.886.013-1.86-.737-2.817zm1.931-2.31c-1.265-1.514-3.131-2.29-4.959-2.29-.623 0-1.248.089-1.856.271-.317.095-.5.427-.405.744.095.317.427.5.744.405.493-.148 1.006-.221 1.517-.221 1.511 0 3.047.641 4.086 1.886 1.039 1.245 1.401 2.861 1.056 4.339-.076.324.125.649.449.725.324.076.649-.125.725-.449.442-1.895-.024-3.971-1.357-5.41z"/></svg>',
            'default': '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>'
        };
        
        // 显示内置图标选择器
        function showIconPicker(btn) {
            var inputGroup = btn.closest('.icon-input-group');
            var input = inputGroup.querySelector('input[name="icon"]');
            var cardItem = btn.closest('.card-item');
            var index = cardItem.dataset.index;
            var previewBox = document.getElementById('contact_icon_preview_' + index);
            
            // 创建图标选择弹窗
            var existingModal = document.getElementById('icon-picker-modal');
            if (existingModal) existingModal.remove();
            
            var modalHtml = '<div id="icon-picker-modal" class="icon-picker-modal">' +
                '<div class="icon-picker-content">' +
                    '<div class="icon-picker-header">' +
                        '<span>选择内置图标</span>' +
                        '<button type="button" class="icon-picker-close" onclick="closeIconPicker()">&times;</button>' +
                    '</div>' +
                    '<div class="icon-picker-grid">';
            
            Object.keys(builtinIcons).forEach(function(key) {
                var iconNames = {
                    'qq': 'QQ', 'wechat': '微信', 'phone': '电话', 'email': '邮箱',
                    'douyin': '抖音', 'bilibili': 'B站', 'weibo': '微博', 'default': '默认'
                };
                modalHtml += '<div class="icon-picker-item" data-icon="' + key + '" onclick="selectBuiltinIcon(\'' + key + '\', \'' + index + '\')">' +
                    builtinIcons[key] +
                    '<span>' + (iconNames[key] || key) + '</span>' +
                '</div>';
            });
            
            modalHtml += '</div></div></div>';
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // 存储当前输入框引用
            window.currentIconInput = input;
            window.currentIconPreview = previewBox;
        }
        
        function closeIconPicker() {
            var modal = document.getElementById('icon-picker-modal');
            if (modal) modal.remove();
        }
        
        function selectBuiltinIcon(iconKey, index) {
            var input = document.querySelector('#contact-list-container .card-item[data-index="' + index + '"] input[name="icon"]');
            var previewBox = document.getElementById('contact_icon_preview_' + index);
            
            if (input) {
                input.value = iconKey;
            }
            if (previewBox) {
                previewBox.innerHTML = '<div class="icon-preview-svg icon-builtin">' + builtinIcons[iconKey] + '</div>';
            }
            closeIconPicker();
        }
        
        // 初始化图标输入框监听
        function initIconInputListeners() {
            var iconInputs = document.querySelectorAll('input[name="icon"]');
            iconInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    updateIconPreview(this);
                });
            });
        }
        
        function updateIconPreview(input) {
            var cardItem = input.closest('.card-item');
            var index = cardItem.dataset.index;
            var previewBox = document.getElementById('contact_icon_preview_' + index);
            var value = input.value.trim();
            
            if (!previewBox) return;
            
            if (!value) {
                previewBox.innerHTML = '';
                return;
            }
            
            // SVG 代码
            if (value.toLowerCase().indexOf('<svg') === 0) {
                previewBox.innerHTML = '<div class="icon-preview-svg">' + value + '</div>';
                return;
            }
            
            // 网络资源
            if (value.toLowerCase().indexOf('http://') === 0 || value.toLowerCase().indexOf('https://') === 0) {
                previewBox.innerHTML = '<img src="' + value + '" class="icon-preview-img" alt="图标">';
                return;
            }
            
            // 本地资源路径
            if (value.toLowerCase().indexOf('assets/') === 0 || value.toLowerCase().indexOf('/assets/') === 0 || value.toLowerCase().indexOf('uploads/') === 0) {
                previewBox.innerHTML = '<img src="' + value + '" class="icon-preview-img" alt="图标">';
                return;
            }
            
            // 内置图标
            if (builtinIcons[value]) {
                previewBox.innerHTML = '<div class="icon-preview-svg icon-builtin">' + builtinIcons[value] + '</div>';
                return;
            }
            
            // 默认显示文本
            previewBox.innerHTML = '<span class="icon-preview-text">' + value + '</span>';
        }
        
        // 万能资源选择器
        function showResourcePicker(inputId) {
            var input = document.getElementById(inputId);
            if (!input) return;
            
            // 创建资源选择弹窗
            var existingModal = document.getElementById('resource-picker-modal');
            if (existingModal) existingModal.remove();
            
            var modalHtml = '<div id="resource-picker-modal" class="icon-picker-modal">' +
                '<div class="icon-picker-content" style="max-width: 500px;">' +
                    '<div class="icon-picker-header">' +
                        '<span>选择资源类型</span>' +
                        '<button type="button" class="icon-picker-close" onclick="closeResourcePicker()">&times;</button>' +
                    '</div>' +
                    '<div style="padding: 20px;">' +
                        '<div class="form-group" style="margin-bottom: 15px;">' +
                            '<label>资源路径或链接</label>' +
                            '<input type="text" id="resource-picker-input" placeholder="如：/assets/img/about.png、https://...、<svg>..." style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); color: var(--text-primary);">' +
                        '</div>' +
                        '<div class="form-group" style="margin-bottom: 20px;">' +
                            '<label>或选择内置图标</label>' +
                            '<div class="icon-picker-grid" style="margin-top: 10px;">';
            
            Object.keys(builtinIcons).forEach(function(key) {
                var iconNames = {
                    'qq': 'QQ', 'wechat': '微信', 'phone': '电话', 'email': '邮箱',
                    'douyin': '抖音', 'bilibili': 'B站', 'weibo': '微博', 'default': '默认'
                };
                modalHtml += '<div class="icon-picker-item" onclick="selectResourceIcon(\'' + key + '\')">' +
                    builtinIcons[key] +
                    '<span>' + (iconNames[key] || key) + '</span>' +
                '</div>';
            });
            
            modalHtml += '</div></div>' +
                        '<div style="display: flex; gap: 10px; justify-content: flex-end;">' +
                            '<button type="button" class="btn btn-secondary" onclick="closeResourcePicker()">取消</button>' +
                            '<button type="button" class="btn btn-primary" onclick="confirmResourceSelection(\'' + inputId + '\')">确定</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // 设置当前值
            var currentValue = input.value;
            document.getElementById('resource-picker-input').value = currentValue;
        }
        
        function closeResourcePicker() {
            var modal = document.getElementById('resource-picker-modal');
            if (modal) modal.remove();
        }
        
        function selectResourceIcon(iconKey) {
            document.getElementById('resource-picker-input').value = iconKey;
        }
        
        function confirmResourceSelection(inputId) {
            var input = document.getElementById(inputId);
            var value = document.getElementById('resource-picker-input').value.trim();
            
            if (input) {
                input.value = value;
                // 触发预览更新
                updateResourcePreview(inputId, value);
            }
            
            closeResourcePicker();
        }
        
        function updateResourcePreview(inputId, value) {
            var previewBox = document.getElementById(inputId + '_preview_box');
            if (!previewBox) return;
            
            if (!value) {
                previewBox.innerHTML = '<div class="resource-preview-empty">暂无预览</div>';
                return;
            }
            
            // SVG 代码
            if (value.toLowerCase().indexOf('<svg') === 0) {
                previewBox.innerHTML = '<div class="resource-preview-svg">' + value + '</div>';
                return;
            }
            
            // 网络资源或本地路径
            if (value.toLowerCase().indexOf('http://') === 0 || value.toLowerCase().indexOf('https://') === 0 ||
                value.toLowerCase().indexOf('assets/') === 0 || value.toLowerCase().indexOf('/assets/') === 0 ||
                value.toLowerCase().indexOf('uploads/') === 0) {
                previewBox.innerHTML = '<img src="' + value + '" alt="预览" class="resource-preview-img">';
                return;
            }
            
            // 图标类名
            previewBox.innerHTML = '<div class="resource-preview-icon"><i class="' + value + '"></i><span>' + value + '</span></div>';
        }
        
        // 初始化资源输入框监听
        function initResourceInputListeners() {
            var resourceInputs = document.querySelectorAll('.resource-input-group input');
            resourceInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    updateResourcePreview(this.id, this.value);
                });
            });
        }
        
        function addDetailRow() {
            var container = document.getElementById('about-details-container');
            var index = container.children.length;
            var html = '<div class="detail-row" data-index="' + index + '">' +
                '<input type="text" name="detail_label[]" value="" placeholder="信息项名称（如：社会信用代码）">' +
                '<input type="text" name="detail_value[]" value="" placeholder="具体内容">' +
                '<button type="button" class="btn btn-danger btn-xs" onclick="removeDetailRow(this)">删除</button>' +
            '</div>';
            container.insertAdjacentHTML('beforeend', html);
        }
        
        function removeDetailRow(btn) {
            btn.closest('.detail-row').remove();
        }
        
        function saveConfig(formData, textFields, imageFields) {
            var data = {};
            textFields.forEach(function(field) {
                data[field] = formData.get(field);
            });
            
            var saveData = new FormData();
            saveData.append('action', 'save_config');
            saveData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: saveData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function saveAboutConfig() {
            var form = document.getElementById('about-config-form');
            var formData = new FormData(form);
            
            var data = {
                about_title: formData.get('about_title'),
                about_content: formData.get('about_content'),
                about_image: formData.get('about_image'),
                about_details: []
            };
            
            var labels = form.querySelectorAll('input[name="detail_label[]"]');
            var values = form.querySelectorAll('input[name="detail_value[]"]');
            
            labels.forEach(function(labelInput, index) {
                var label = labelInput.value.trim();
                var value = values[index] ? values[index].value.trim() : '';
                if (label || value) {
                    data.about_details.push({ label: label, value: value });
                }
            });
            
            var saveData = new FormData();
            saveData.append('action', 'save_config');
            saveData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: saveData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function addItem(type) {
            var list = document.getElementById(type + '-list');
            var index = list.children.length;
            var html = '';
            
            if (type === 'subsidiaries') {
                html = '<div class="card-item" data-index="' + index + '">' +
                    '<div class="card-item-header">' +
                        '<h4>公司 #' + (index + 1) + '</h4>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(this, \'subsidiaries\')">删除</button>' +
                    '</div>' +
                    '<div class="form-row">' +
                        '<div class="form-group">' +
                            '<label>公司名称</label>' +
                            '<input type="text" name="name" value="">' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>官网链接（可选）</label>' +
                            '<input type="text" name="link" value="" placeholder="https://">' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>公司简介</label>' +
                        '<textarea name="description" rows="2" style="resize: both;"></textarea>' +
                    '</div>' +
                    '<div class="form-row">' +
                        '<div class="form-group">' +
                            '<label>列表卡片图（4:3）</label>' +
                            '<p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">支持：①本地路径(如 assets/logo.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)</p>' +
                            '<textarea name="card_image" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"></textarea>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>详情弹窗 Logo（1:1）</label>' +
                            '<p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">支持：①本地路径(如 assets/logo.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)</p>' +
                            '<textarea name="modal_logo" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"></textarea>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>Logo 发光颜色（十六进制）</label>' +
                        '<p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">用于详情弹窗 Logo 的背景色和发光阴影，如：#667eea</p>' +
                        '<input type="text" name="logo_color" value="#667eea" placeholder="#667eea" style="max-width: 200px;">' +
                    '</div>' +
                    '<div class="seo-section" style="margin-top: 15px;">' +
                        '<h4>详细信息（动态配置）</h4>' +
                        '<p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">点击"查看详情"按钮后弹窗显示的内容</p>' +
                        '<div class="subsidiary-details-container" data-company-index="' + index + '">' +
                        '</div>' +
                        '<button type="button" class="btn btn-success btn-xs" onclick="addSubsidiaryDetailRow(this)" style="margin-top: 10px;">添加信息行</button>' +
                    '</div>' +
                '</div>';
            } else if (type === 'business') {
                html = '<div class="card-item" data-index="' + index + '">' +
                    '<div class="card-item-header">' +
                        '<h4>业务 #' + (index + 1) + '</h4>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(this, \'business\')">删除</button>' +
                    '</div>' +
                    '<div class="form-row">' +
                        '<div class="form-group">' +
                            '<label>业务名称</label>' +
                            '<input type="text" name="title" value="">' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<label>业务图标</label>' +
                            '<p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">支持：①本地路径(如 assets/icon.png) ②网络地址(https://...) ③SVG代码(&lt;svg&gt;...&lt;/svg&gt;)</p>' +
                            '<textarea name="icon" rows="2" placeholder="输入本地路径、网络地址或粘贴SVG代码" style="resize: both;"></textarea>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>业务描述</label>' +
                        '<textarea name="description" rows="2"></textarea>' +
                    '</div>' +
                '</div>';
            }
            
            list.insertAdjacentHTML('beforeend', html);
        }
        
        function addSubsidiaryDetailRow(btn) {
            var container = btn.closest('.seo-section').querySelector('.subsidiary-details-container');
            var html = '<div class="detail-row subsidiary-detail-row">' +
                '<input type="text" name="detail_label[]" value="" placeholder="信息项名称（如：成立日期）">' +
                '<input type="text" name="detail_value[]" value="" placeholder="具体内容">' +
                '<button type="button" class="btn btn-danger btn-xs" onclick="removeSubsidiaryDetailRow(this)">删除</button>' +
            '</div>';
            container.insertAdjacentHTML('beforeend', html);
        }
        
        function removeSubsidiaryDetailRow(btn) {
            btn.closest('.detail-row').remove();
        }
        
        function addContactItem() {
            var list = document.getElementById('contact-list-container');
            var index = list.children.length;
            var html = '<div class="card-item compact" data-index="' + index + '">' +
                '<div class="card-item-header">' +
                    '<h4>联系方式 #' + (index + 1) + '</h4>' +
                    '<button type="button" class="btn btn-danger btn-xs" onclick="deleteItem(this, \'contact_list\')">删除</button>' +
                '</div>' +
                '<div class="form-row compact">' +
                    '<div class="form-group mini">' +
                        '<label>图标资源 <span class="hint-text">(路径/链接/SVG/图标名)</span></label>' +
                        '<div class="icon-input-group">' +
                            '<input type="text" name="icon" value="" placeholder="如：qq、wechat、https://...、<svg>...">' +
                            '<button type="button" class="btn btn-xs btn-secondary" onclick="showIconPicker(this)">内置图标</button>' +
                        '</div>' +
                        '<div class="icon-preview-box" id="contact_icon_preview_' + index + '"></div>' +
                    '</div>' +
                    '<div class="form-group mini">' +
                        '<label>显示名称</label>' +
                        '<input type="text" name="name" value="" placeholder="如：客服QQ、官方微信">' +
                    '</div>' +
                '</div>' +
                '<div class="form-row compact">' +
                    '<div class="form-group mini">' +
                        '<label>标签名称</label>' +
                        '<input type="text" name="label" value="号码/账号" placeholder="如：QQ号、微信号、电话">' +
                    '</div>' +
                    '<div class="form-group mini">' +
                        '<label>详细内容</label>' +
                        '<input type="text" name="value" value="" placeholder="具体号码或账号">' +
                    '</div>' +
                '</div>' +
                '<div class="form-row compact">' +
                    '<div class="form-group mini">' +
                        '<label>备注（可选）</label>' +
                        '<input type="text" name="remark" value="" placeholder="如：工作时间 9:00-18:00">' +
                    '</div>' +
                    '<div class="form-group mini">' +
                        '<label>二维码图片（可选）</label>' +
                        '<p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 6px;">支持：①本地路径(如 assets/qrcode.png) ②网络地址(https://...)</p>' +
                        '<input type="text" name="qrcode" value="" placeholder="输入本地路径或网络地址">' +
                    '</div>' +
                '</div>' +
            '</div>';
            list.insertAdjacentHTML('beforeend', html);
            initIconInputListeners();
        }
        
        function addMediaItem() {
            var list = document.getElementById('media-list-container');
            var index = list.children.length;
            var html = '<div class="card-item" data-index="' + index + '">' +
                '<div class="card-item-header">' +
                    '<h4>平台 #' + (index + 1) + '</h4>' +
                    '<button type="button" class="btn btn-danger btn-xs" onclick="deleteItem(this, \'media\')">删除</button>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label>平台类型</label>' +
                    '<div class="icon-picker-grid" style="grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 10px;">' +
                        '<div class="icon-picker-item" data-type="douyin" onclick="selectMediaType(this, \'media_type_' + index + '\')">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>' +
                            '<span>抖音</span>' +
                        '</div>' +
                        '<div class="icon-picker-item" data-type="bilibili" onclick="selectMediaType(this, \'media_type_' + index + '\')">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 01-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 01.16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>' +
                            '<span>B站</span>' +
                        '</div>' +
                        '<div class="icon-picker-item" data-type="weibo" onclick="selectMediaType(this, \'media_type_' + index + '\')">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.194.573zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.579-.18-.405-.649.381-1.017.422-1.896-.001-2.52-.789-1.165-2.943-1.102-5.387-.03 0 0-.772.334-.575-.272.383-1.217.324-2.236-.271-2.823-1.349-1.336-4.938-.04-8.018 2.896C1.102 10.878 0 13.022 0 14.898c0 3.586 4.604 5.767 9.109 5.767 5.905 0 9.835-3.424 9.835-6.149 0-1.643-1.388-2.577-2.855-3.067zm2.003-5.376c-.632-.756-1.565-1.143-2.479-1.143-.311 0-.623.046-.924.14-.317.104-.498.429-.396.746.103.317.427.498.745.396.191-.063.391-.094.591-.094.564 0 1.146.239 1.543.713.396.474.535 1.083.391 1.635-.089.322.099.656.421.745.322.089.656-.099.745-.421.233-.886.013-1.86-.737-2.817zm1.931-2.31c-1.265-1.514-3.131-2.29-4.959-2.29-.623 0-1.248.089-1.856.271-.317.095-.5.427-.405.744.095.317.427.5.744.405.493-.148 1.006-.221 1.517-.221 1.511 0 3.047.641 4.086 1.886 1.039 1.245 1.401 2.861 1.056 4.339-.076.324.125.649.449.725.324.076.649-.125.725-.449.442-1.895-.024-3.971-1.357-5.41z"/></svg>' +
                            '<span>微博</span>' +
                        '</div>' +
                        '<div class="icon-picker-item" data-type="xiaohongshu" onclick="selectMediaType(this, \'media_type_' + index + '\')">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>' +
                            '<span>小红书</span>' +
                        '</div>' +
                        '<div class="icon-picker-item" data-type="custom" onclick="selectMediaType(this, \'media_type_' + index + '\')">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2v-4H8v-2h4V7h2v4h4v2h-4v4z"/></svg>' +
                            '<span>自定义</span>' +
                        '</div>' +
                        '<div class="icon-picker-item" data-type="default" onclick="selectMediaType(this, \'media_type_' + index + '\')">' +
                            '<svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>' +
                            '<span>其他</span>' +
                        '</div>' +
                    '</div>' +
                    '<input type="hidden" name="type" id="media_type_' + index + '" value="douyin">' +
                '</div>' +
                '<div class="form-row">' +
                    '<div class="form-group">' +
                        '<label>平台名称</label>' +
                        '<input type="text" name="name" value="" placeholder="如：官方抖音号">' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>账号昵称（可选）</label>' +
                        '<input type="text" name="nickname" value="" placeholder="如：企业官方号">' +
                    '</div>' +
                '</div>' +
                '<div class="form-row">' +
                    '<div class="form-group">' +
                        '<label>标识名称</label>' +
                        '<input type="text" name="id_name" value="" placeholder="如：抖音号/视频号 ID/UID">' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>账号数值</label>' +
                        '<input type="text" name="id_value" value="" placeholder="具体的账号号码">' +
                    '</div>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label>跳转链接</label>' +
                    '<input type="text" name="link" value="" placeholder="https://">' +
                '</div>' +
                '<div class="form-group svg-icon-field" style="display: none;">' +
                    '<label>自定义SVG图标代码</label>' +
                    '<textarea name="svg_icon" rows="3" placeholder="<svg viewBox=\'0 0 24 24\' ...>...</svg>"></textarea>' +
                '</div>' +
            '</div>';
            list.insertAdjacentHTML('beforeend', html);
        }
        
        function selectMediaType(item, inputId) {
            // 移除所有选中状态
            var siblings = item.parentElement.querySelectorAll('.icon-picker-item');
            siblings.forEach(function(sibling) {
                sibling.style.background = 'rgba(255, 255, 255, 0.05)';
                sibling.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            });
            // 设置当前选中状态
            item.style.background = 'rgba(102, 126, 234, 0.2)';
            item.style.borderColor = 'rgba(102, 126, 234, 0.4)';
            // 设置隐藏输入框的值
            document.getElementById(inputId).value = item.getAttribute('data-type');
            // 显示/隐藏自定义SVG输入框
            var type = item.getAttribute('data-type');
            var svgField = item.closest('.card-item').querySelector('.svg-icon-field');
            if (type === 'custom') {
                svgField.style.display = 'block';
            } else {
                svgField.style.display = 'none';
            }
        }
        
        function addLinkItem() {
            var list = document.getElementById('links-list-container');
            var index = list.children.length;
            var html = '<div class="card-item" data-index="' + index + '">' +
                '<div class="card-item-header">' +
                    '<h4>链接 #' + (index + 1) + '</h4>' +
                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteItem(this, \'links\')">删除</button>' +
                '</div>' +
                '<div class="form-row">' +
                    '<div class="form-group">' +
                        '<label>网站名称</label>' +
                        '<input type="text" name="name" value="">' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>网站链接</label>' +
                        '<input type="text" name="url" value="" placeholder="https://">' +
                    '</div>' +
                '</div>' +
            '</div>';
            list.insertAdjacentHTML('beforeend', html);
        }
        
        function deleteItem(btn, type) {
            if (confirm('确定要删除此项吗？')) {
                btn.closest('.card-item').remove();
            }
        }
        
        function saveList(type) {
            var listId = type + '-list';
            var items = document.querySelectorAll('#' + listId + ' .card-item');
            var data = [];
            var formData = new FormData();
            
            items.forEach(function(item, index) {
                var obj = {
                    name: item.querySelector('input[name="name"]').value,
                    link: item.querySelector('input[name="link"]').value,
                    description: item.querySelector('textarea[name="description"]').value,
                    details: []
                };
                
                // 旗下公司特有的字段
                if (type === 'subsidiaries') {
                    obj.card_image = item.querySelector('textarea[name="card_image"]') ? item.querySelector('textarea[name="card_image"]').value : '';
                    obj.modal_logo = item.querySelector('textarea[name="modal_logo"]') ? item.querySelector('textarea[name="modal_logo"]').value : '';
                    obj.logo_color = item.querySelector('input[name="logo_color"]') ? item.querySelector('input[name="logo_color"]').value : '#667eea';
                }
                
                var detailsContainer = item.querySelector('.subsidiary-details-container');
                if (detailsContainer) {
                    var detailRows = detailsContainer.querySelectorAll('.subsidiary-detail-row');
                    detailRows.forEach(function(row) {
                        var labelInput = row.querySelector('input[name="detail_label[]"]');
                        var valueInput = row.querySelector('input[name="detail_value[]"]');
                        if (labelInput && valueInput) {
                            var label = labelInput.value.trim();
                            var value = valueInput.value.trim();
                            if (label || value) {
                                obj.details.push({ label: label, value: value });
                            }
                        }
                    });
                }
                
                if (obj.name || obj.description) {
                    data.push(obj);
                }
            });
            
            formData.append('action', 'save_' + type);
            formData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(function() { location.reload(); }, 1000);
                }
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function saveBusiness() {
            var items = document.querySelectorAll('#business-list .card-item');
            var data = [];
            var formData = new FormData();
            
            items.forEach(function(item, index) {
                var obj = {
                    title: item.querySelector('input[name="title"]').value,
                    description: item.querySelector('textarea[name="description"]').value,
                    icon: ''
                };
                
                var preview = item.querySelector('.image-preview');
                if (preview && preview.src && !preview.src.includes('data:image/svg+xml') && preview.style.display !== 'none') {
                    obj.icon = preview.getAttribute('src').split('?')[0];
                }
                
                var fileInput = item.querySelector('input[type="file"]');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append('image_' + index, fileInput.files[0]);
                }
                
                if (obj.title || obj.description) {
                    data.push(obj);
                }
            });
            
            formData.append('action', 'save_business');
            formData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(function() { location.reload(); }, 1000);
                }
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function saveContactList() {
            var items = document.querySelectorAll('#contact-list-container .card-item');
            var data = [];
            var formData = new FormData();
            
            items.forEach(function(item, index) {
                var iconInput = item.querySelector('input[name="icon"]');
                var obj = {
                    icon: iconInput ? iconInput.value : '',
                    name: item.querySelector('input[name="name"]').value,
                    label: item.querySelector('input[name="label"]') ? item.querySelector('input[name="label"]').value : '号码/账号',
                    value: item.querySelector('input[name="value"]').value,
                    remark: item.querySelector('input[name="remark"]') ? item.querySelector('input[name="remark"]').value : '',
                    qrcode: ''
                };
                
                var preview = item.querySelector('.image-preview');
                if (preview && preview.src && !preview.src.includes('data:image/svg+xml') && preview.style.display !== 'none') {
                    obj.qrcode = preview.getAttribute('src').split('?')[0];
                }
                
                var fileInput = item.querySelector('input[type="file"]');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append('qrcode_' + index, fileInput.files[0]);
                }
                
                if (obj.name || obj.value) {
                    data.push(obj);
                }
            });
            
            formData.append('action', 'save_contact_list');
            formData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(function() { location.reload(); }, 1000);
                }
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function saveMedia() {
            var items = document.querySelectorAll('#media-list-container .card-item');
            var data = [];
            
            items.forEach(function(item) {
                var obj = {
                    type: item.querySelector('input[name="type"]').value,
                    name: item.querySelector('input[name="name"]').value,
                    nickname: item.querySelector('input[name="nickname"]') ? item.querySelector('input[name="nickname"]').value : '',
                    id_name: item.querySelector('input[name="id_name"]') ? item.querySelector('input[name="id_name"]').value : '',
                    id_value: item.querySelector('input[name="id_value"]') ? item.querySelector('input[name="id_value"]').value : '',
                    link: item.querySelector('input[name="link"]').value,
                    svg_icon: item.querySelector('textarea[name="svg_icon"]') ? item.querySelector('textarea[name="svg_icon"]').value : ''
                };
                
                if (obj.name || obj.link) {
                    data.push(obj);
                }
            });
            
            var formData = new FormData();
            formData.append('action', 'save_media');
            formData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(function() { location.reload(); }, 1000);
                }
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function saveLinks() {
            var items = document.querySelectorAll('#links-list-container .card-item');
            var data = [];
            
            items.forEach(function(item) {
                var obj = {
                    name: item.querySelector('input[name="name"]').value,
                    url: item.querySelector('input[name="url"]').value
                };
                
                if (obj.name || obj.url) {
                    data.push(obj);
                }
            });
            
            var formData = new FormData();
            formData.append('action', 'save_links');
            formData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(function() { location.reload(); }, 1000);
                }
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function addFilingItem() {
            var list = document.getElementById('filing-list-container');
            var index = list.children.length;
            var html = '<div class="card-item compact" data-index="' + index + '">' +
                '<div class="card-item-header">' +
                    '<h4>备案项 #' + (index + 1) + '</h4>' +
                    '<button type="button" class="btn btn-danger btn-xs" onclick="deleteItem(this, \'filing\')">删除</button>' +
                '</div>' +
                '<div class="form-row compact">' +
                    '<div class="form-group mini">' +
                        '<label>名称</label>' +
                        '<input type="text" name="name" value="" placeholder="如：京ICP备XXXXXXXX号">' +
                    '</div>' +
                    '<div class="form-group mini">' +
                        '<label>显示类型</label>' +
                        '<select name="display_type" class="contact-type-select" onchange="toggleFilingFields(this)">' +
                            '<option value="link">跳转链接</option>' +
                            '<option value="modal">弹窗显示</option>' +
                            '<option value="text">仅文本</option>' +
                        '</select>' +
                    '</div>' +
                '</div>' +
                '<div class="form-group mini filing-link-field" style="display: none;">' +
                    '<label>跳转链接</label>' +
                    '<input type="text" name="link" value="" placeholder="https://">' +
                '</div>' +
                '<div class="form-group mini filing-modal-field" style="display: none;">' +
                    '<label>弹窗内容（支持换行）</label>' +
                    '<textarea name="modal_content" rows="3" placeholder="弹窗显示的详细内容"></textarea>' +
                '</div>' +
                '<div class="form-group mini">' +
                    '<label>自定义图标SVG（可选）</label>' +
                    '<textarea name="icon" rows="2" placeholder="<svg viewBox=\'0 0 24 24\' ...>...</svg>"></textarea>' +
                '</div>' +
            '</div>';
            list.insertAdjacentHTML('beforeend', html);
        }
        
        function toggleFilingFields(select) {
            var cardItem = select.closest('.card-item');
            var linkField = cardItem.querySelector('.filing-link-field');
            var modalField = cardItem.querySelector('.filing-modal-field');
            var value = select.value;
            
            if (linkField) {
                linkField.style.display = value === 'link' ? 'block' : 'none';
            }
            if (modalField) {
                modalField.style.display = value === 'modal' ? 'block' : 'none';
            }
        }
        
        function saveFiling() {
            var items = document.querySelectorAll('#filing-list-container .card-item');
            var data = [];
            
            items.forEach(function(item) {
                var displayType = item.querySelector('select[name="display_type"]').value;
                var obj = {
                    name: item.querySelector('input[name="name"]').value,
                    display_type: displayType,
                    link: item.querySelector('input[name="link"]') ? item.querySelector('input[name="link"]').value : '',
                    modal_content: item.querySelector('textarea[name="modal_content"]') ? item.querySelector('textarea[name="modal_content"]').value : '',
                    icon: item.querySelector('textarea[name="icon"]') ? item.querySelector('textarea[name="icon"]').value : ''
                };
                
                if (obj.name) {
                    data.push(obj);
                }
            });
            
            var formData = new FormData();
            formData.append('action', 'save_filing');
            formData.append('data', JSON.stringify(data));
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(function() { location.reload(); }, 1000);
                }
            })
            .catch(function(error) {
                showToast('保存失败，请稍后重试', 'error');
            });
        }
        
        function showToast(message, type) {
            var toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
    <?php endif; ?>
</body>
</html>
