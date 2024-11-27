<?php
use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Plugin;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Widget\Helper\Form\Element\Text;

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * 代码高亮插件，支持深色模式
 * 
 * @package HighlightJS
 * @author Nobu121
 * @version 1.0.0
 * @link https://github.com/nobu121/typecho-highlightjs
 */
class HighlightJS_Plugin implements PluginInterface
{
    public static function activate()
    {
        Plugin::factory('Widget_Archive')->header = array('HighlightJS_Plugin', 'header');
        return _t('插件启用成功');
    }

    public static function deactivate()
    {
        return _t('插件禁用成功');
    }

    public static function personalConfig(Form $form)
    {
    }

    public static function config(Form $form)
    {
        // 是否启用深色模式
        $enableDarkMode = new \Typecho\Widget\Helper\Form\Element\Radio(
            'enableDarkMode',
            ['1' => _t('启用'), '0' => _t('禁用')],
            '1',
            _t('是否启用深色模式'),
            _t('启用后将支持明暗主题切换，禁用则只使用浅色主题')
        );
        $form->addInput($enableDarkMode);

        // 深色模式判断表达式
        $darkModeExpr = new Textarea(
            'darkModeExpr',
            null,
            'document.documentElement.classList.contains("dark")',
            _t('深色模式判断表达式'),
            _t('输入一个返回 true/false 的 JavaScript 表达式，用于判断当前是否为深色模式')
        );
        $darkModeExpr->setAttribute('style', 'width: 100%; max-width: 500px;');
        $form->addInput($darkModeExpr);

        // 监听的属性配置
        $watchAttributes = new Text(
            'watchAttributes',
            null,
            'class,data-theme,dark,theme',
            _t('深色模式监听属性'),
            _t('多个属性用逗号分隔，用于监听深色模式变化')
        );
        $form->addInput($watchAttributes);

        // 浅色主题配置
        $lightTheme = new Text(
            'lightTheme',
            null,
            '',
            _t('浅色主题CSS链接'),
            _t('输入浅色主题的CSS链接，留空使用默认主题')
        );
        $form->addInput($lightTheme);

        // 深色主题配置
        $darkTheme = new Text(
            'darkTheme',
            null,
            '',
            _t('深色主题CSS链接'),
            _t('输入深色主题的CSS链接，留空使用默认主题')
        );
        $form->addInput($darkTheme);
    }

    public static function header()
    {
        $options = Helper::options();
        $pluginOptions = $options->plugin('HighlightJS');
        $baseUrl = $options->pluginUrl . '/HighlightJS/assets';

        $enableDarkMode = $pluginOptions->enableDarkMode;
        $darkModeCheck = $pluginOptions->darkModeExpr;
        $watchAttributes = explode(',', $pluginOptions->watchAttributes);
        $lightTheme = $pluginOptions->lightTheme;
        $darkTheme = $pluginOptions->darkTheme;

        $lightThemeUrl = $lightTheme ?: $baseUrl . '/css/atom-one-light.min.css';
        $darkThemeUrl = $darkTheme ?: $baseUrl . '/css/atom-one-dark.min.css';
        $iconPath = $baseUrl . '/icons';

        // 使用 Widget_Archive 检查代码块
        $hasCodeBlock = false;
        $archive = \Typecho\Widget::widget('Widget_Archive');

        if ($archive->is('single') || $archive->is('page')) {
            $content = $archive->content;
            // 检查是否包含代码块
            $hasCodeBlock = preg_match('/(<pre>[\s]*<code|```)/i', $content);

            // 调试输出（可以在测试完成后删除）
            error_log('Page type: ' . ($archive->is('single') ? 'post' : 'page'));
            error_log('Has code block: ' . ($hasCodeBlock ? 'true' : 'false'));
        }

        // 只在有代码块时输出相关资源
        if ($hasCodeBlock):
            ?>
            <script src="<?php echo $baseUrl . '/js/highlight.min.js'; ?>"></script>
            <script src="<?php echo $baseUrl . '/js/helper.js'; ?>"></script>
            <link rel="stylesheet" href="<?php echo $lightThemeUrl; ?>" id="light-highlight-theme">
            <?php if ($enableDarkMode): ?>
                <link rel="stylesheet" href="<?php echo $darkThemeUrl; ?>" id="dark-highlight-theme">
            <?php endif; ?>
            <link rel="stylesheet" href="<?php echo $baseUrl . '/css/style.css'; ?>">

            <script>
                window.HighlightJSHelper.init({
                    enableDarkMode: <?php echo json_encode((bool) $enableDarkMode); ?>,
                    darkModeCheck: <?php echo json_encode($darkModeCheck); ?>,
                    watchAttributes: <?php echo json_encode($watchAttributes); ?>,
                    iconPath: <?php echo json_encode($iconPath); ?>
                });
            </script>
            <?php
        endif;
    }
}
