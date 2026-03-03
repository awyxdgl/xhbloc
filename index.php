<?php
/**
 * 公司官网主页面
 * 版本：9.0 - UI深度精简与交互优化版
 * 
 * 【V9.0 更新内容】
 * - 联系方式支持自定义标签名称
 * - 弹窗动态显示自定义标签
 */

$config_file = __DIR__ . '/data/config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];

$subsidiaries_file = __DIR__ . '/data/subsidiaries.json';
$subsidiaries = file_exists($subsidiaries_file) ? json_decode(file_get_contents($subsidiaries_file), true) : [];

$business_file = __DIR__ . '/data/business.json';
$business = file_exists($business_file) ? json_decode(file_get_contents($business_file), true) : [];

$contact_list_file = __DIR__ . '/data/contact_list.json';
$contact_list = file_exists($contact_list_file) ? json_decode(file_get_contents($contact_list_file), true) : [];

$media_file = __DIR__ . '/data/media.json';
$media = file_exists($media_file) ? json_decode(file_get_contents($media_file), true) : [];

$links_file = __DIR__ . '/data/links.json';
$links = file_exists($links_file) ? json_decode(file_get_contents($links_file), true) : [];

$filing_file = __DIR__ . '/data/filing.json';
$filing = file_exists($filing_file) ? json_decode(file_get_contents($filing_file), true) : [];

$tab_title = $config['tab_title'] ?? $config['site_title'] ?? '公司官网';
$site_title = $config['site_title'] ?? '公司官网';
$site_logo = $config['site_logo'] ?? '';
$favicon = $config['favicon'] ?? '';
$hero_title = $config['hero_title'] ?? '欢迎来到我们的公司';
$hero_subtitle = $config['hero_subtitle'] ?? '专业、创新、共赢';
$about_title = $config['about_title'] ?? '关于我们';
$about_content = $config['about_content'] ?? '我们是一家专注于创新的企业...';
$about_image = $config['about_image'] ?? 'uploads/about.jpg';
$about_image_ratio = $config['about_image_ratio'] ?? '16:9';
$about_details = $config['about_details'] ?? [];
$footer_text = $config['footer_text'] ?? '© 2024 公司名称 版权所有';
$icp_number = $config['icp_number'] ?? '京ICP备XXXXXXXX号';

$seo_title = $config['seo_title'] ?? $tab_title;
$seo_keywords = $config['seo_keywords'] ?? '公司官网,企业服务,专业团队';
$seo_description = $config['seo_description'] ?? '欢迎访问我们的公司官网，我们提供专业的服务和解决方案。';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title><?php echo htmlspecialchars($seo_title); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta name="robots" content="index, follow">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="zh_CN">
    
    <?php if (!empty($favicon)): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($favicon); ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo htmlspecialchars($favicon); ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar" role="navigation" aria-label="主导航">
        <?php if (!empty($site_logo)): ?>
        <div class="nav-brand-logo">
            <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_title); ?>">
        </div>
        <?php else: ?>
        <div class="nav-brand"><?php echo htmlspecialchars($site_title); ?></div>
        <?php endif; ?>
        <button class="nav-toggle" aria-label="菜单" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul class="nav-menu" role="menubar">
            <li role="none"><a href="#hero" role="menuitem">首页</a></li>
            <li role="none"><a href="#about" role="menuitem">关于我们</a></li>
            <li role="none"><a href="#companies" role="menuitem">旗下公司</a></li>
            <li role="none"><a href="#business" role="menuitem">主要业务</a></li>
            <li role="none"><a href="#contact" role="menuitem">联系我们</a></li>
        </ul>
    </nav>

    <main class="scroll-container" role="main">
        
        <section id="hero" class="section hero-section" aria-label="首页">
            <div class="hero-content">
                <h1 class="animate-on-scroll"><?php echo htmlspecialchars($hero_title); ?></h1>
                <p class="subtitle animate-on-scroll animate-delay-1"><?php echo htmlspecialchars($hero_subtitle); ?></p>
                <a href="#about" class="scroll-hint animate-on-scroll animate-delay-2">
                    <span>向下滚动</span>
                    <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                        <path fill="currentColor" d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                    </svg>
                </a>
            </div>
        </section>

        <section id="about" class="section about-section" aria-label="关于我们">
            <div class="container">
                <h2 class="section-title animate-on-scroll"><?php echo htmlspecialchars($about_title); ?></h2>
                <p class="section-subtitle animate-on-scroll animate-delay-1">了解我们的故事与愿景</p>
                
                <div class="about-wrapper">
                    <div class="about-image-wrapper animate-left">
                        <div class="about-image glass-card" style="--about-image-ratio: <?php echo str_replace(':', '/', $about_image_ratio); ?>">
                            <?php
                            if (!empty($about_image)):
                                $trimmedSource = trim($about_image);
                                if (stripos($trimmedSource, '<svg') === 0):
                                    echo $trimmedSource;
                                elseif (stripos($trimmedSource, 'http://') === 0 || stripos($trimmedSource, 'https://') === 0):
                            ?>
                            <img src="<?php echo htmlspecialchars($trimmedSource); ?>" alt="关于我们">
                            <?php
                                else:
                            ?>
                            <img src="<?php echo htmlspecialchars($trimmedSource); ?>" alt="关于我们" onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22><rect fill=%22%23333%22 width=%22400%22 height=%22300%22/><text fill=%22%23999%22 x=%22200%22 y=%22150%22 text-anchor=%22middle%22 dy=%22.3em%22>暂无图片</text></svg>';">
                            <?php
                                endif;
                            else:
                            ?>
                            <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                                <rect fill="#333" width="400" height="300"/>
                                <text fill="#999" x="200" y="150" text-anchor="middle" dy=".3em">暂无图片</text>
                            </svg>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="about-content glass-card animate-right">
                        <h3>公司简介</h3>
                        <p><?php echo nl2br(htmlspecialchars($about_content)); ?></p>
                        <?php if (!empty($about_details)): ?>
                        <button type="button" class="btn btn-sm about-detail-btn" onclick="openAboutModal()">
                            查看详情
                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                                <path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section id="companies" class="section companies-section" aria-label="旗下公司">
            <div class="container">
                <h2 class="section-title animate-on-scroll">旗下公司</h2>
                <p class="section-subtitle animate-on-scroll animate-delay-1">我们的业务版图</p>
                
                <div class="company-grid">
                    <?php if (!empty($subsidiaries)): ?>
                        <?php foreach ($subsidiaries as $index => $item): ?>
                        <article class="company-card animate-scale animate-delay-<?php echo ($index % 4) + 1; ?>"
                                 data-name="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                 data-description="<?php echo htmlspecialchars($item['description'] ?? ''); ?>"
                                 data-card-image="<?php echo htmlspecialchars($item['card_image'] ?? $item['image_source'] ?? $item['image'] ?? ''); ?>"
                                 data-modal-logo="<?php echo htmlspecialchars($item['modal_logo'] ?? ''); ?>"
                                 data-logo-color="<?php echo htmlspecialchars($item['logo_color'] ?? '#667eea'); ?>"
                                 data-link="<?php echo htmlspecialchars($item['link'] ?? ''); ?>"
                                 data-details="<?php echo htmlspecialchars(json_encode($item['details'] ?? [])); ?>">
                            <div class="company-card-image">
                                <?php 
                                $cardImage = $item['card_image'] ?? $item['image_source'] ?? $item['image'] ?? '';
                                if (!empty($cardImage)):
                                    $trimmedSource = trim($cardImage);
                                    if (stripos($trimmedSource, '<svg') === 0):
                                        echo $trimmedSource;
                                    elseif (stripos($trimmedSource, 'http://') === 0 || stripos($trimmedSource, 'https://') === 0):
                                ?>
                                <img src="<?php echo htmlspecialchars($trimmedSource); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                     loading="lazy">
                                <?php
                                    else:
                                ?>
                                <img src="<?php echo htmlspecialchars($trimmedSource); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                     loading="lazy"
                                     onerror="this.style.display='none'; this.parentElement.querySelector('svg')?.remove(); this.parentElement.innerHTML='<svg viewBox=\'0 0 24 24\'><path fill=\'currentColor\' d=\'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z\'/></svg>';">
                                <?php
                                    endif;
                                else:
                                ?>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div class="company-card-content">
                                <h3><?php echo htmlspecialchars($item['name'] ?? '公司名称'); ?></h3>
                                <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                <button type="button" class="btn btn-sm">查看详情</button>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">暂无公司信息</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="business" class="section business-section" aria-label="主要业务">
            <div class="container">
                <h2 class="section-title animate-on-scroll">主要业务</h2>
                <p class="section-subtitle animate-on-scroll animate-delay-1">我们提供的专业服务</p>
                
                <div class="card-grid">
                    <?php if (!empty($business)): ?>
                        <?php foreach ($business as $index => $item): ?>
                        <article class="card business-card animate-scale animate-delay-<?php echo ($index % 4) + 1; ?>">
                            <div class="card-icon">
                                <?php if (!empty($item['icon'])): ?>
                                <img src="<?php echo htmlspecialchars($item['icon']); ?>" alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                                <?php else: ?>
                                <svg viewBox="0 0 24 24" width="40" height="40" aria-hidden="true">
                                    <path fill="currentColor" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($item['title'] ?? '业务名称'); ?></h3>
                                <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">暂无业务信息</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="contact" class="section contact-section" aria-label="联系我们">
            <div class="contact-inner">
                <div class="contact-main">
                    <div class="container">
                        <h2 class="section-title animate-on-scroll">联系我们</h2>
                        <p class="section-subtitle animate-on-scroll animate-delay-1">期待与您的每一次沟通</p>
                        
                        <div class="contact-wrapper">
                            <div class="contact-info animate-left">
                                <h3 class="contact-info-title">联系方式</h3>
                                
                                <div class="contact-cards-grid">
                                    <?php if (!empty($contact_list)): ?>
                                        <?php foreach ($contact_list as $index => $item): ?>
                                        <?php 
                                        $iconSource = $item['icon'] ?? $item['type'] ?? 'default';
                                        $iconColor = $item['color'] ?? '#6366f1';
                                        // 验证颜色格式
                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $iconColor)) {
                                            $iconColor = '#6366f1';
                                        }
                                        // 计算发光阴影颜色（50%透明度）
                                        $r = hexdec(substr($iconColor, 1, 2));
                                        $g = hexdec(substr($iconColor, 3, 2));
                                        $b = hexdec(substr($iconColor, 5, 2));
                                        $glowColor = "rgba({$r}, {$g}, {$b}, 0.5)";
                                        ?>
                                        <div class="contact-card-item animate-scale animate-delay-<?php echo ($index % 4) + 1; ?>" 
                                             data-icon="<?php echo htmlspecialchars($iconSource); ?>"
                                             data-color="<?php echo htmlspecialchars($iconColor); ?>"
                                             data-qrcode="<?php echo htmlspecialchars($item['qrcode'] ?? ''); ?>"
                                             data-name="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                             data-label="<?php echo htmlspecialchars($item['label'] ?? '号码/账号'); ?>"
                                             data-value="<?php echo htmlspecialchars($item['value'] ?? ''); ?>"
                                             data-remark="<?php echo htmlspecialchars($item['remark'] ?? ''); ?>">
                                            <div class="contact-card-icon" style="background-color: <?php echo $iconColor; ?>; box-shadow: 0 4px 15px <?php echo $glowColor; ?>;">
                                                <?php echo renderIconHtml($iconSource); ?>
                                            </div>
                                            <div class="contact-card-content">
                                                <div class="contact-card-name"><?php echo htmlspecialchars($item['name'] ?? ''); ?></div>
                                                <div class="contact-card-hint">
                                                    点击查看详情
                                                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                                                        <path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="contact-empty">
                                            <svg viewBox="0 0 24 24" width="48" height="48">
                                                <path fill="rgba(255,255,255,0.3)" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                            </svg>
                                            <p>暂无联系方式</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="contact-form-wrapper animate-right">
                                <h3 class="contact-form-title">留言建议</h3>
                                <form id="suggestion-form" novalidate>
                                    <div class="form-group">
                                        <label for="name">您的姓名</label>
                                        <input type="text" id="name" name="name" required placeholder="请输入您的姓名" autocomplete="name">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">联系方式</label>
                                        <input type="text" id="phone" name="phone" required placeholder="请输入手机号或邮箱" autocomplete="tel email">
                                    </div>
                                    <div class="form-group">
                                        <label for="message">您的建议</label>
                                        <textarea id="message" name="message" rows="4" required placeholder="请输入您的建议或意见"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="btn-text">提交建议</span>
                                        <span class="btn-loading" style="display: none;">提交中...</span>
                                    </button>
                                </form>
                                
                                <?php if (!empty($media)): ?>
                                <div class="media-matrix">
                                    <h4>关注我们</h4>
                                    <div class="media-icons">
                                        <?php foreach ($media as $index => $item): ?>
                                        <div class="media-icon-item"
                                             data-type="<?php echo htmlspecialchars($item['type'] ?? 'default'); ?>"
                                             data-name="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                             data-nickname="<?php echo htmlspecialchars($item['nickname'] ?? ''); ?>"
                                             data-uid="<?php echo htmlspecialchars($item['uid'] ?? ''); ?>"
                                             data-link="<?php echo htmlspecialchars($item['link'] ?? ''); ?>"
                                             data-svg-icon="<?php echo htmlspecialchars($item['svg_icon'] ?? ''); ?>"
                                             title="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                                            <?php 
                                            if (!empty($item['svg_icon'])) {
                                                echo $item['svg_icon'];
                                            } else {
                                                echo getMediaIcon($item['type'] ?? 'default'); 
                                            }
                                            ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <footer class="contact-footer">
                    <div class="footer-inner">
                        <?php if (!empty($links)): ?>
                        <div class="footer-links">
                            <span class="footer-links-label">友情链接：</span>
                            <div class="footer-links-list">
                                <?php foreach ($links as $link): ?>
                                <a href="<?php echo htmlspecialchars($link['url'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo htmlspecialchars($link['name'] ?? ''); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="footer-copyright">
                            <p><?php echo htmlspecialchars($footer_text); ?></p>
                        </div>
                        
                        <?php if (!empty($filing)): ?>
                        <div class="footer-filing">
                            <?php foreach ($filing as $index => $item): ?>
                                <?php if ($index > 0): ?>
                                <span class="footer-filing-separator">|</span>
                                <?php endif; ?>
                                <?php if (!empty($item['link'])): ?>
                                <a href="<?php echo htmlspecialchars($item['link']); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="footer-filing-item">
                                    <?php if (!empty($item['icon'])): ?>
                                    <?php echo $item['icon']; ?>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($item['name'] ?? ''); ?>
                                </a>
                                <?php elseif (!empty($item['modal_content'])): ?>
                                <span class="footer-filing-item" 
                                      data-modal-title="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"
                                      data-modal-content="<?php echo htmlspecialchars($item['modal_content'] ?? ''); ?>"
                                      onclick="openFilingModal(this)">
                                    <?php if (!empty($item['icon'])): ?>
                                    <?php echo $item['icon']; ?>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($item['name'] ?? ''); ?>
                                </span>
                                <?php else: ?>
                                <span class="footer-filing-item">
                                    <?php if (!empty($item['icon'])): ?>
                                    <?php echo $item['icon']; ?>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($item['name'] ?? ''); ?>
                                </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php elseif (!empty($icp_number)): ?>
                        <div class="footer-filing">
                            <span class="footer-filing-item"><?php echo htmlspecialchars($icp_number); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </footer>
            </div>
        </section>
    </main>

    <div id="contact-modal" class="modal contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
        <div class="modal-content contact-modal-content">
            <span class="modal-close" role="button" aria-label="关闭">&times;</span>
            <div class="contact-detail-header">
                <div class="contact-detail-icon" id="contact-modal-icon"></div>
                <h3 id="contact-modal-title">联系方式详情</h3>
            </div>
            <div class="contact-detail-body">
                <div class="contact-detail-row">
                    <span class="contact-detail-label">名称</span>
                    <span class="contact-detail-value" id="contact-modal-name"></span>
                </div>
                <div class="contact-detail-row">
                    <span class="contact-detail-label" id="contact-modal-label-text">号码/账号</span>
                    <span class="contact-detail-value" id="contact-modal-value"></span>
                </div>
                <div class="contact-detail-row" id="contact-modal-remark-row" style="display: none;">
                    <span class="contact-detail-label">备注</span>
                    <span class="contact-detail-value" id="contact-modal-remark"></span>
                </div>
            </div>
            <div class="contact-detail-qrcode" id="contact-modal-qrcode-wrapper" style="display: none;">
                <img id="contact-modal-qrcode" src="" alt="二维码">
            </div>
        </div>
    </div>

    <div id="media-modal" class="modal media-modal" role="dialog" aria-modal="true" aria-labelledby="media-modal-title">
        <div class="modal-content media-modal-content">
            <span class="modal-close" role="button" aria-label="关闭">&times;</span>
            <div class="media-detail-header">
                <div class="media-detail-icon" id="media-modal-icon"></div>
                <h3 id="media-modal-title">平台详情</h3>
            </div>
            <div class="media-detail-body">
                <div class="media-detail-row">
                    <span class="media-detail-label">平台名称</span>
                    <span class="media-detail-value" id="media-modal-name"></span>
                </div>
                <div class="media-detail-row" id="media-modal-nickname-row" style="display: none;">
                    <span class="media-detail-label">账号昵称</span>
                    <span class="media-detail-value" id="media-modal-nickname"></span>
                </div>
                <div class="media-detail-row" id="media-modal-uid-row" style="display: none;">
                    <span class="media-detail-label">账号UID</span>
                    <span class="media-detail-value" id="media-modal-uid"></span>
                </div>
            </div>
            <div class="media-detail-actions">
                <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary" id="media-modal-link">
                    前往主页
                    <svg viewBox="0 0 24 24" width="16" height="16" style="margin-left: 6px;">
                        <path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div id="filing-modal" class="modal filing-modal" role="dialog" aria-modal="true" aria-labelledby="filing-modal-title">
        <div class="modal-content filing-modal-content">
            <span class="modal-close" role="button" aria-label="关闭">&times;</span>
            <h3 id="filing-modal-title">备案信息</h3>
            <div id="filing-modal-body" style="margin-top: var(--spacing-md); color: var(--text-secondary); line-height: 1.8;"></div>
        </div>
    </div>

    <div id="qrcode-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-content">
            <span class="modal-close" role="button" aria-label="关闭">&times;</span>
            <h3 id="modal-title">二维码</h3>
            <img id="modal-qrcode" src="" alt="二维码">
        </div>
    </div>

    <div id="about-modal" class="modal about-modal" role="dialog" aria-modal="true" aria-labelledby="about-modal-title">
        <div class="modal-content about-modal-content">
            <span class="modal-close" role="button" aria-label="关闭">&times;</span>
            <h3 id="about-modal-title">详细信息</h3>
            <div class="about-detail-list">
                <?php if (!empty($about_details)): ?>
                    <?php foreach ($about_details as $detail): ?>
                    <div class="about-detail-item">
                        <span class="about-detail-label"><?php echo htmlspecialchars($detail['label'] ?? ''); ?></span>
                        <span class="about-detail-value"><?php echo htmlspecialchars($detail['value'] ?? ''); ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="company-modal" class="modal company-modal" role="dialog" aria-modal="true" aria-labelledby="company-modal-title">
        <div class="modal-content company-modal-content">
            <span class="modal-close" role="button" aria-label="关闭">&times;</span>
            <div class="company-modal-header">
                <div class="company-modal-logo" id="company-modal-logo"></div>
                <div class="company-modal-header-text">
                    <h3 class="company-modal-title" id="company-modal-title">公司名称</h3>
                </div>
            </div>
            <div class="company-modal-body">
                <div class="company-modal-section" id="company-modal-description-section">
                    <div class="company-modal-section-title">公司简介</div>
                    <p class="company-modal-description" id="company-modal-description"></p>
                </div>
                <div class="company-modal-section" id="company-modal-tags-section">
                    <div class="company-modal-section-title">基本信息</div>
                    <div class="company-modal-tags" id="company-modal-tags"></div>
                </div>
                <div class="company-modal-section" id="company-modal-details-section">
                    <div class="company-modal-section-title">详细信息</div>
                    <div class="company-modal-details" id="company-modal-details"></div>
                </div>
                <div class="company-modal-actions">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm" id="company-modal-link">
                        前往官网
                        <svg viewBox="0 0 24 24" width="16" height="16">
                            <path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast" role="alert" aria-live="polite"></div>

    <script src="script.js"></script>
</body>
</html>

<?php
function renderIconHtml($source) {
    if (empty($source)) {
        $source = 'default';
    }
    
    $source = trim($source);
    
    // SVG 代码
    if (stripos($source, '<svg') === 0) {
        return $source;
    }
    
    // 网络资源
    if (stripos($source, 'http://') === 0 || stripos($source, 'https://') === 0) {
        return '<img src="' . htmlspecialchars($source) . '" alt="图标" style="width:24px;height:24px;object-fit:contain;">';
    }
    
    // 本地资源路径
    if (stripos($source, 'assets/') === 0 || stripos($source, '/assets/') === 0 || stripos($source, 'uploads/') === 0) {
        return '<img src="' . htmlspecialchars($source) . '" alt="图标" style="width:24px;height:24px;object-fit:contain;">';
    }
    
    // 内置图标库
    return getContactIcon($source);
}

function getContactIcon($type) {
    $icons = [
        'qq' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
        'wechat' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M9.5 4C5.36 4 2 6.69 2 10c0 1.89 1.08 3.56 2.78 4.66L4 17l2.5-1.5c.89.31 1.87.5 2.5.5.17 0 .33-.01.5-.02C9.18 15.02 9 14.03 9 13c0-3.87 3.58-7 8-7 .31 0 .62.02.92.05C16.72 4.84 13.36 4 9.5 4zm-3 4.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm5 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2zM17 8c-3.87 0-7 2.69-7 6s3.13 6 7 6c.89 0 1.73-.14 2.5-.38L22 21l-.78-2.33C22.47 17.26 24 15.26 24 14c0-3.31-3.13-6-7-6zm-2 4.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm4 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
        'douyin' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>',
        'bilibili' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 0 1-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 0 1 .16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>',
        'weibo' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M20.194 14.197c0 3.248-4.003 5.876-8.947 5.876-4.944 0-8.947-2.628-8.947-5.876 0-3.248 4.003-5.876 8.947-5.876 4.944 0 8.947 2.628 8.947 5.876zm-1.667-.045c-.248-1.63-2.418-2.731-4.851-2.461-2.433.27-4.23 1.794-3.982 3.424.248 1.63 2.418 2.731 4.851 2.461 2.433-.27 4.23-1.794 3.982-3.424z"/></svg>',
        'default' => '<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>'
    ];
    return $icons[$type] ?? $icons['default'];
}

function getMediaIcon($type) {
    $icons = [
        'douyin' => '<svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>',
        'bilibili' => '<svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 0 1-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 0 1 .16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373z"/></svg>',
        'weibo' => '<svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M20.194 14.197c0 3.248-4.003 5.876-8.947 5.876-4.944 0-8.947-2.628-8.947-5.876 0-3.248 4.003-5.876 8.947-5.876 4.944 0 8.947 2.628 8.947 5.876zm-1.667-.045c-.248-1.63-2.418-2.731-4.851-2.461-2.433.27-4.23 1.794-3.982 3.424.248 1.63 2.418 2.731 4.851 2.461 2.433-.27 4.23-1.794 3.982-3.424z"/></svg>',
        'xiaohongshu' => '<svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>',
        'default' => '<svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93z"/></svg>'
    ];
    return $icons[$type] ?? $icons['default'];
}
?>
