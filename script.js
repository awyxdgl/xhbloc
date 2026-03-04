/**
 * 公司官网前端交互脚本
 * 版本：11.6 - 后台管理逻辑升级与多比例布局重构版
 * 
 * 【V11.6 更新内容】
 * - 联系方式弹窗：支持动态背景色和发光阴影
 * - 新增颜色解析逻辑：十六进制转RGBA发光效果
 * 
 * 【V11.5 更新内容】
 * - 新增 renderIcon 函数：支持内置图标名、SVG代码、网络链接、本地路径
 * - 联系方式弹窗：使用新的图标渲染逻辑
 * 
 * 【V11.3 更新内容】
 * - 公司详情弹窗：横向标题栏 + 分栏布局 + 标签化展示
 * - 支持详情项 isTag 属性区分标签和普通字段
 * 
 * 【V11.2 更新内容】
 * - 配合CSS弹窗交互优化：旋转动画 + 横向标题栏
 * 
 * 【V11.1 更新内容】
 * - 配合CSS弹窗优化：绝对定位关闭按钮
 * 
 * 【V11.0 更新内容】
 * - 弹窗智能高度算法：80/50准则（内容>80%视口时锁定50vh）
 * - 公司卡片点击事件：仅按钮触发弹窗 + stopPropagation
 * - 滚动事件优化：requestAnimationFrame 处理
 */

document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initScrollAnimations();
    initContactModal();
    initMediaModal();
    initAboutModal();
    initFilingModal();
    initQrcodeModal();
    initContactForm();
    initSmoothScroll();
    initNavbarScroll();
    initGlobalModalClose();
    initCompanyModal();
});

/**
 * 初始化导航栏功能
 */
function initNavigation() {
    var navToggle = document.querySelector('.nav-toggle');
    var navMenu = document.querySelector('.nav-menu');
    var navLinks = document.querySelectorAll('.nav-menu a');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar')) {
            if (navToggle) navToggle.classList.remove('active');
            if (navMenu) navMenu.classList.remove('active');
        }
    });
}

/**
 * 初始化导航栏滚动效果（使用 requestAnimationFrame 优化）
 */
function initNavbarScroll() {
    var navbar = document.querySelector('.navbar');
    var scrollContainer = document.querySelector('.scroll-container');
    var ticking = false;
    
    if (scrollContainer && navbar) {
        scrollContainer.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    if (scrollContainer.scrollTop > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
}

/**
 * 初始化入场动画 - 使用 Intersection Observer
 */
function initScrollAnimations() {
    var observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };
    
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);
    
    var animatedElements = document.querySelectorAll('.animate-on-scroll, .animate-scale, .animate-left, .animate-right');
    animatedElements.forEach(function(el) {
        observer.observe(el);
    });
}

/**
 * 初始化联系方式详情弹窗功能
 */
function initContactModal() {
    var modal = document.getElementById('contact-modal');
    var modalClose = modal ? modal.querySelector('.modal-close') : null;
    var contactCards = document.querySelectorAll('.contact-card-item');
    
    contactCards.forEach(function(card) {
        card.addEventListener('click', function() {
            var icon = this.dataset.icon || this.dataset.type || 'default';
            var color = this.dataset.color || '#6366f1';
            var name = this.dataset.name || '联系方式';
            var label = this.dataset.label || '号码/账号';
            var value = this.dataset.value || '';
            var qrcode = this.dataset.qrcode || '';
            var remark = this.dataset.remark || '';
            
            // 计算发光阴影颜色（50%透明度）
            var r = parseInt(color.slice(1, 3), 16);
            var g = parseInt(color.slice(3, 5), 16);
            var b = parseInt(color.slice(5, 7), 16);
            var glowColor = 'rgba(' + r + ', ' + g + ', ' + b + ', 0.5)';
            
            var iconContainer = document.getElementById('contact-modal-icon');
            if (iconContainer) {
                iconContainer.innerHTML = renderIcon(icon);
                iconContainer.style.backgroundColor = color;
                iconContainer.style.boxShadow = '0 8px 20px ' + glowColor;
            }
            
            document.getElementById('contact-modal-title').textContent = name;
            document.getElementById('contact-modal-name').textContent = name;
            document.getElementById('contact-modal-label-text').textContent = label;
            document.getElementById('contact-modal-value').textContent = value;
            
            var remarkRow = document.getElementById('contact-modal-remark-row');
            if (remark) {
                document.getElementById('contact-modal-remark').textContent = remark;
                remarkRow.style.display = 'flex';
            } else {
                remarkRow.style.display = 'none';
            }
            
            var qrcodeWrapper = document.getElementById('contact-modal-qrcode-wrapper');
            if (qrcode) {
                document.getElementById('contact-modal-qrcode').src = qrcode;
                qrcodeWrapper.style.display = 'block';
            } else {
                qrcodeWrapper.style.display = 'none';
            }
            
            openModalWithSmartHeight(modal);
        });
    });
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal(modal);
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }
}

/**
 * 初始化流媒体详情弹窗功能
 */
function initMediaModal() {
    var modal = document.getElementById('media-modal');
    var modalClose = modal ? modal.querySelector('.modal-close') : null;
    var mediaIcons = document.querySelectorAll('.media-icon-item');
    
    mediaIcons.forEach(function(icon) {
        icon.addEventListener('click', function() {
            var type = this.dataset.type || 'default';
            var name = this.dataset.name || '平台';
            var nickname = this.dataset.nickname || '';
            var idName = this.dataset.id_name || '';
            var idValue = this.dataset.id_value || '';
            var link = this.dataset.link || '';
            
            var iconContainer = document.getElementById('media-modal-icon');
            var iconSvg = this.querySelector('svg');
            if (iconContainer && iconSvg) {
                iconContainer.innerHTML = iconSvg.outerHTML;
            }
            
            document.getElementById('media-modal-title').textContent = name;
            document.getElementById('media-modal-name').textContent = name;
            
            var nicknameRow = document.getElementById('media-modal-nickname-row');
            if (nickname) {
                document.getElementById('media-modal-nickname').textContent = nickname;
                nicknameRow.style.display = 'flex';
            } else {
                nicknameRow.style.display = 'none';
            }
            
            var idRow = document.getElementById('media-modal-id-row');
            if (idName && idValue) {
                document.getElementById('media-modal-id-label').textContent = idName;
                document.getElementById('media-modal-id-value').textContent = idValue;
                idRow.style.display = 'flex';
            } else {
                idRow.style.display = 'none';
            }
            
            var linkBtn = document.getElementById('media-modal-link');
            if (link) {
                linkBtn.href = link;
                linkBtn.style.display = 'inline-flex';
            } else {
                linkBtn.style.display = 'none';
            }
            
            openModalWithSmartHeight(modal);
        });
    });
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal(modal);
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }
}

/**
 * 初始化关于我们详情弹窗
 */
function initAboutModal() {
    var aboutModal = document.getElementById('about-modal');
    var modalClose = aboutModal ? aboutModal.querySelector('.modal-close') : null;
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal(aboutModal);
        });
    }
    
    if (aboutModal) {
        aboutModal.addEventListener('click', function(e) {
            if (e.target === aboutModal) {
                closeModal(aboutModal);
            }
        });
    }
}

/**
 * 初始化备案号弹窗
 */
function initFilingModal() {
    var modal = document.getElementById('filing-modal');
    var modalClose = modal ? modal.querySelector('.modal-close') : null;
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal(modal);
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }
}

/**
 * 初始化二维码弹窗
 */
function initQrcodeModal() {
    var modal = document.getElementById('qrcode-modal');
    var modalClose = modal ? modal.querySelector('.modal-close') : null;
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal(modal);
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }
}

/**
 * 初始化全局弹窗关闭功能（ESC键和点击遮罩）
 */
function initGlobalModalClose() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var modals = ['about-modal', 'contact-modal', 'media-modal', 'filing-modal', 'qrcode-modal', 'company-modal'];
            modals.forEach(function(modalId) {
                var modal = document.getElementById(modalId);
                if (modal && modal.classList.contains('active')) {
                    closeModal(modal);
                }
            });
        }
    });
}

/**
 * 初始化公司详情弹窗（精准点击事件绑定）
 * 核心修改：仅按钮触发弹窗，使用 stopPropagation 阻止冒泡
 */
function initCompanyModal() {
    var modal = document.getElementById('company-modal');
    var modalClose = modal ? modal.querySelector('.modal-close') : null;
    var companyCards = document.querySelectorAll('.company-card');
    
    companyCards.forEach(function(card) {
        var detailBtn = card.querySelector('.btn-sm');
        
        if (detailBtn) {
            detailBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                
                var name = card.dataset.name || '公司名称';
                var description = card.dataset.description || '';
                var modalLogo = card.dataset.modalLogo || '';
                var logoColor = card.dataset.logoColor || '#667eea';
                var link = card.dataset.link || '';
                var detailsJson = card.dataset.details || '[]';
                
                var details = [];
                try {
                    details = JSON.parse(detailsJson);
                } catch (err) {
                    details = [];
                }
                
                var logoEl = document.getElementById('company-modal-logo');
                if (logoEl) {
                    logoEl.innerHTML = renderImageOrSvg(modalLogo, name);
                    var r = parseInt(logoColor.slice(1, 3), 16);
                    var g = parseInt(logoColor.slice(3, 5), 16);
                    var b = parseInt(logoColor.slice(5, 7), 16);
                    var glowColor = 'rgba(' + r + ', ' + g + ', ' + b + ', 0.5)';
                    logoEl.style.backgroundColor = logoColor;
                    logoEl.style.boxShadow = '0 12px 30px ' + glowColor;
                }
                
                document.getElementById('company-modal-title').textContent = name;
                
                var descEl = document.getElementById('company-modal-description');
                if (descEl) {
                    if (description) {
                        descEl.textContent = description;
                        descEl.parentElement.style.display = 'block';
                    } else {
                        descEl.parentElement.style.display = 'none';
                    }
                }
                
                var tagsContainer = document.getElementById('company-modal-tags');
                if (tagsContainer) {
                    var tagsHtml = '';
                    var tagDetails = details.filter(function(d) { return d.isTag; });
                    tagDetails.forEach(function(detail) {
                        if (detail.value) {
                            tagsHtml += '<span class="company-modal-tag">' + (detail.value || '') + '</span>';
                        }
                    });
                    tagsContainer.innerHTML = tagsHtml;
                    tagsContainer.parentElement.style.display = tagsHtml ? 'block' : 'none';
                }
                
                var detailsContainer = document.getElementById('company-modal-details');
                if (detailsContainer) {
                    var detailsHtml = '';
                    var normalDetails = details.filter(function(d) { return !d.isTag; });
                    normalDetails.forEach(function(detail) {
                        if (detail.label || detail.value) {
                            detailsHtml += '<div class="company-modal-detail-item">' +
                                '<span class="company-modal-detail-label">' + (detail.label || '') + '</span>' +
                                '<span class="company-modal-detail-value">' + (detail.value || '') + '</span>' +
                            '</div>';
                        }
                    });
                    detailsContainer.innerHTML = detailsHtml;
                    detailsContainer.parentElement.style.display = detailsHtml ? 'block' : 'none';
                }
                
                var linkBtn = document.getElementById('company-modal-link');
                if (linkBtn) {
                    if (link) {
                        linkBtn.href = link;
                        linkBtn.style.display = 'inline-flex';
                    } else {
                        linkBtn.style.display = 'none';
                    }
                }
                
                openModalWithSmartHeight(modal);
            });
        }
    });
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal(modal);
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }
}

/**
 * 弹窗智能高度算法（80/80准则）
 * - 内容高度 > 视口高度80%：固定高度80vh，开启滚动
 * - 内容高度 < 视口高度80%：高度auto
 */
function openModalWithSmartHeight(modal) {
    if (!modal) return;
    
    var modalContent = modal.querySelector('.modal-content');
    if (!modalContent) return;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    requestAnimationFrame(function() {
        var viewportHeight = window.innerHeight;
        var eightyPercentVh = viewportHeight * 0.8;
        var contentScrollHeight = modalContent.scrollHeight;
        
        if (contentScrollHeight > eightyPercentVh) {
            modalContent.style.height = '80vh';
            modalContent.style.overflowY = 'auto';
        } else {
            modalContent.style.height = 'auto';
            modalContent.style.overflowY = 'auto';
        }
    });
}

/**
 * 渲染图片或SVG代码
 */
function renderImageOrSvg(source, alt) {
    if (!source) {
        return '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>';
    }
    
    var trimmedSource = source.trim();
    
    if (trimmedSource.toLowerCase().startsWith('<svg')) {
        return trimmedSource;
    }
    
    if (trimmedSource.toLowerCase().startsWith('http://') || trimmedSource.toLowerCase().startsWith('https://')) {
        return '<img src="' + trimmedSource + '" alt="' + (alt || '') + '">';
    }
    
    if (trimmedSource.toLowerCase().startsWith('assets/') || trimmedSource.toLowerCase().startsWith('uploads/')) {
        return '<img src="' + trimmedSource + '" alt="' + (alt || '') + '">';
    }
    
    return '<img src="' + trimmedSource + '" alt="' + (alt || '') + '">';
}

/**
 * 渲染图标（支持内置图标名、SVG代码、网络链接、本地路径）
 */
function renderIcon(source) {
    if (!source) {
        return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>';
    }
    
    var trimmedSource = source.trim();
    
    // SVG 代码
    if (trimmedSource.toLowerCase().indexOf('<svg') === 0) {
        return trimmedSource;
    }
    
    // 网络资源
    if (trimmedSource.toLowerCase().indexOf('http://') === 0 || trimmedSource.toLowerCase().indexOf('https://') === 0) {
        return '<img src="' + trimmedSource + '" alt="图标" style="width:100%;height:100%;object-fit:contain;">';
    }
    
    // 本地资源路径
    if (trimmedSource.toLowerCase().indexOf('assets/') === 0 || trimmedSource.toLowerCase().indexOf('/assets/') === 0 || trimmedSource.toLowerCase().indexOf('uploads/') === 0) {
        return '<img src="' + trimmedSource + '" alt="图标" style="width:100%;height:100%;object-fit:contain;">';
    }
    
    // 内置图标库
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
    
    if (builtinIcons[trimmedSource]) {
        return builtinIcons[trimmedSource];
    }
    
    // 默认返回默认图标
    return builtinIcons['default'];
}

/**
 * 打开关于我们详情弹窗
 */
function openAboutModal() {
    var aboutModal = document.getElementById('about-modal');
    if (aboutModal) {
        openModalWithSmartHeight(aboutModal);
    }
}

/**
 * 打开备案号弹窗
 */
function openFilingModal(element) {
    var modal = document.getElementById('filing-modal');
    var title = element.dataset.modalTitle || '备案信息';
    var content = element.dataset.modalContent || '';
    
    document.getElementById('filing-modal-title').textContent = title;
    document.getElementById('filing-modal-body').innerHTML = content.replace(/\n/g, '<br>');
    
    if (modal) {
        openModalWithSmartHeight(modal);
    }
}

/**
 * 关闭弹窗
 */
function closeModal(modal) {
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        var modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.height = '';
            modalContent.style.overflowY = '';
        }
    }
}

/**
 * 初始化联系表单
 */
function initContactForm() {
    var form = document.getElementById('suggestion-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(form);
            var data = {
                name: formData.get('name'),
                phone: formData.get('phone'),
                message: formData.get('message')
            };
            
            if (!validateForm(data)) {
                return;
            }
            
            submitSuggestion(data, form);
        });
    }
}

/**
 * 验证表单数据
 */
function validateForm(data) {
    if (!data.name || data.name.trim() === '') {
        showToast('请输入您的姓名', 'error');
        highlightField('name');
        return false;
    }
    
    if (!data.phone || data.phone.trim() === '') {
        showToast('请输入联系方式', 'error');
        highlightField('phone');
        return false;
    }
    
    if (!data.message || data.message.trim() === '') {
        showToast('请输入您的建议', 'error');
        highlightField('message');
        return false;
    }
    
    return true;
}

/**
 * 高亮显示错误字段
 */
function highlightField(fieldId) {
    var field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = 'rgba(255, 107, 107, 0.8)';
        field.style.boxShadow = '0 0 0 4px rgba(255, 107, 107, 0.2)';
        field.focus();
        
        setTimeout(function() {
            field.style.borderColor = '';
            field.style.boxShadow = '';
        }, 2000);
    }
}

/**
 * 提交建议表单
 */
function submitSuggestion(data, form) {
    var submitBtn = form.querySelector('button[type="submit"]');
    var btnText = submitBtn.querySelector('.btn-text');
    var btnLoading = submitBtn.querySelector('.btn-loading');
    
    if (btnText && btnLoading) {
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
    } else {
        submitBtn.textContent = '提交中...';
    }
    submitBtn.disabled = true;
    
    fetch('api.php?action=submit_suggestion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        if (result.success) {
            showToast('感谢您的建议，我们会尽快处理！', 'success');
            form.reset();
        } else {
            showToast(result.message || '提交失败，请稍后重试', 'error');
        }
    })
    .catch(function(error) {
        console.error('提交错误:', error);
        showToast('网络错误，请稍后重试', 'error');
    })
    .finally(function() {
        if (btnText && btnLoading) {
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        } else {
            submitBtn.textContent = '提交建议';
        }
        submitBtn.disabled = false;
    });
}

/**
 * 显示提示消息
 */
function showToast(message, type) {
    var toast = document.getElementById('toast');
    
    if (toast) {
        toast.textContent = message;
        toast.className = 'toast show ' + type;
        
        setTimeout(function() {
            toast.classList.remove('show');
        }, 3500);
    }
}

/**
 * 初始化平滑滚动
 */
function initSmoothScroll() {
    var links = document.querySelectorAll('a[href^="#"]');
    var scrollContainer = document.querySelector('.scroll-container');
    
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            var href = this.getAttribute('href');
            
            if (href && href !== '#') {
                var target = document.querySelector(href);
                
                if (target) {
                    e.preventDefault();
                    
                    if (scrollContainer) {
                        scrollContainer.scrollTo({
                            top: target.offsetTop,
                            behavior: 'smooth'
                        });
                    } else {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            }
        });
    });
}
