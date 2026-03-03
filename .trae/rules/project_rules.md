# 🚀 项目开发与 AI 协作规约 (Project_Development_Standards.md)

## 0. 基本原则 (Core Principles)
- **模块化优先**：所有样式与脚本必须按功能块划分。严禁在全局文件中堆砌无意义的代码。
- **禁止冗余**：彻底移除项目中未使用的代码逻辑（如：已废弃的 Cropper.js 图片裁切）。禁止残留 `console.log`。
- **全中文注释**：所有生成的 CSS 选择器、JS 逻辑函数、变量定义，必须配备清晰的中文注释，说明其实现逻辑。
- **结构稳定性**：禁止随意修改 HTML DOM 结构。所有视觉优化必须通过 CSS 布局技巧或 JS 动态计算完成。
- **编写服从**：所有代码的编写严格按照我给的指令编写对应的代码。

---

## 1. 布局与视觉模块 (Layout & Vision)

### 1.1 硬件加速与性能优化
- **消除卡顿**：在具有高斯模糊 (`backdrop-filter`) 或大幅背景图的板块（如“关于我们”）应用 `will-change: transform;` 以启用 GPU 加速。
- **背景属性**：确保所有大背景图使用 `background-attachment: scroll;`，避免在移动端出现严重的滚动掉帧。

### 1.2 物理级中心对齐
- **对齐协议**：所有圆形或正方形交互元素（如关闭按钮、控制点）必须统一使用 `display: flex; align-items: center; justify-content: center;`。
- **重心纠偏**：针对 "×" 等特殊字符，必须设置 `line-height: 0 !important;` 并在必要时使用 `transform: translateY(-Xpx);` 进行像素级视觉对齐，确保绝对居中。

---

## 2. 弹窗组件模块 (Modal Component)

### 2.1 外置关闭按钮 (Outer Close Button)
- **视觉位置**：按钮必须固定在弹窗容器 `.modal-content` 的外部右上角（不占用弹窗内部空间）。
- **防滑逻辑**：按钮必须与弹窗内容解耦，在 `.modal-content` 开启内部滚动时，按钮必须维持在固定坐标，不得随内容滑走。
- **动画继承**：按钮必须作为 `.modal-content` 的子元素，以确保完美执行入场动画（如 `modalIn`），严禁使用 `fixed` 导致动画丢失。

### 2.2 智能高度算法 (80/50 Rule)
- **动态适配**：弹窗激活时，使用 JavaScript 动态检测内容真实高度。
- **判定阈值**：
    - 若 `scrollHeight > 视口高度的 80%`：强制设定高度为 `50vh`，并激活内部滚动。
    - 若 `scrollHeight <= 视口高度的 80%`：保持 `height: auto;`。
- **滚动条美化**：
    - 宽度锁定为 `6px`，轨道（Track）设为透明。
    - 滑块（Thumb）必须使用主题渐变色，并添加 `box-shadow` 发光特效。

---

## 3. 旗下公司模块 (Company Cards)

### 3.1 底部按钮对齐
- **锁定基准线**：`.company-card` 容器必须设置 `position: relative; padding-bottom: 80px;`。
- **按钮定位**：详情按钮设为 `position: absolute; bottom: 25px; left: 50%; transform: translateX(-50%);`。
- **禁止位移动效**：按钮禁止在 `hover` 时添加任何导致物理位置偏移的动画，确保横向排列的所有卡片按钮始终处于绝对水平线。

### 3.2 交互拦截协议
- **事件绑定**：弹窗触发事件必须仅绑定在“查看详情”按钮上。
- **冒泡处理**：在点击回调中使用 `event.stopPropagation()`，严禁点击卡片其他空白区域触发弹窗。

---

## 4. 代码整洁度与模块化 (Code Hygiene)

### 4.1 CSS 管理
- 采用 **BEM** 或 **模块化命名**。
- 禁止重复编写相同的渐变色、圆角或阴影值，必须统一调用全局 CSS 变量（如 `var(--glass-bg)`）。

### 4.2 JS 函数封装
- 功能代码必须封装在独立的对象或闭包中，严禁在全局作用域编写松散的函数。
- 每次修改后，AI 必须自检并删除失效的函数引用。

---

## 5. 错误预防清单 (Anti-Error Checklist)
- **防止切边**：开启容器滚动时，必须检查 `overflow-x: visible !important;`，防止外部按钮被父级裁剪。
- **防止层级冲突**：关闭按钮 `z-index` 必须设为最高层级（如 `9999`），防止被内容遮挡。
- **防止样式失效**：生成的样式必须考虑优先级，必要时使用特定选择器确保覆盖默认样式。