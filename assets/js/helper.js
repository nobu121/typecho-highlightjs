// 复制功能的辅助函数
function copyToClipboard(text) {
    // 首先尝试使用 navigator.clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    }

    // 后备方案：创建临时文本区域
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    return new Promise((resolve, reject) => {
        try {
            document.execCommand('copy');
            textArea.remove();
            resolve();
        } catch (error) {
            textArea.remove();
            reject(error);
        }
    });
}

// 添加复制按钮到代码块
function addCopyButtons(baseUrl) {
    document.querySelectorAll('pre code').forEach((el) => {
        const button = document.createElement('button');
        button.className = 'copy-button';
        
        // 创建图标元素
        const icon = document.createElement('img');
        icon.src = `${baseUrl}/copy.svg`;
        button.appendChild(icon);
        
        button.addEventListener('click', async () => {
            try {
                await copyToClipboard(el.textContent);
                icon.src = `${baseUrl}/copy-checked.svg`;
                button.classList.add('copied');
                
                setTimeout(() => {
                    icon.src = `${baseUrl}/copy.svg`;
                    button.classList.remove('copied');
                }, 2000);
            } catch (err) {
                console.error('复制失败:', err);
                // 复制失败时仍然使用原始图标
                setTimeout(() => {
                    icon.src = `${baseUrl}/copy.svg`;
                }, 2000);
            }
        });
        
        el.parentElement.appendChild(button);
    });
}

// 添加新的主题切换相关功能
window.HighlightJSHelper = {
    copyToClipboard,
    addCopyButtons,
    
    // 新增 init 方法
    init(config) {
        // 初始化深色模式
        this.initDarkModeObserver(
            config.enableDarkMode,
            config.darkModeCheck,
            config.watchAttributes
        );
        
        // 初始化代码高亮
        this.initHighlight(config.iconPath);
    },
    
    // 更新高亮主题
    updateHighlightTheme(enableDarkMode, darkModeCheck) {
        if (enableDarkMode) {
            const isDark = eval(darkModeCheck);
            document.getElementById('light-highlight-theme').disabled = isDark;
            document.getElementById('dark-highlight-theme').disabled = !isDark;
        }
    },

    // 初始化深色模式观察器
    initDarkModeObserver(enableDarkMode, darkModeCheck, watchAttributes) {
        // 初始化时执行一次
        this.updateHighlightTheme(enableDarkMode, darkModeCheck);

        if (enableDarkMode) {
            // 使用 MutationObserver 监听 HTML 属性变化
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes') {
                        this.updateHighlightTheme(enableDarkMode, darkModeCheck);
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: watchAttributes
            });
        }
    },

    // 初始化代码高亮
    initHighlight(iconPath) {
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('pre code').forEach((el) => {
                hljs.highlightElement(el);
                // 添加初始化完成的类名
                el.classList.add('hljs-init');
            });
            // 添加复制按钮
            this.addCopyButtons(iconPath);
        });
    }
};
