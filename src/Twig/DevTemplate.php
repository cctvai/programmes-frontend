<?php

namespace App\Twig;

abstract class DevTemplate extends \Twig\Template
{
    private const CONTROL_GLOBAL = 'debug_print_template_labels';
    private const HIDE_NAMESPACES = [
        '@WebProfiler',
        '@CsaGuzzle',
    ];

    public function display(array $context, array $blocks = [])
    {
        $name = $this->getTemplateName();
        $globals = $this->env->getGlobals();

        echo '<!-- START: ' . $name . ' -->' . PHP_EOL;

        if ($globals[self::CONTROL_GLOBAL] ?? false) {
            echo self::createLabel($name);
        }

        $this->displayWithErrorHandling($this->env->mergeGlobals($context), array_merge($this->blocks, $blocks));

        echo '<!-- END: ' . $name . ' -->' . PHP_EOL;
    }

    private function createLabel($name): string
    {
        $parts = explode('/', $name);
        $ns = array_shift($parts);
        $short = array_pop($parts);
        if (in_array($ns, self::HIDE_NAMESPACES)) {
            return '';
        }
        $short = str_replace('.html.twig', '.html', $short);
        $color = substr(md5($name), 0, 6);
        return <<<_ML
    <span style="float: left; height: 0; max-height: 1px; width: 0; position: relative; z-index: 10; overflow: visible;">
        <pre 
            title="{$name}"
            onmouseover="this.parentElement.style.zIndex = 100;this.style.opacity=1;" 
            onmouseout="this.parentElement.style.zIndex=10;this.style.opacity=0.8;" 
            style='min-height: 1.2em; line-height: 1.2em; left: 0; position: absolute; display: inline-block; padding: 2px; font-size: 9px; font-weight: bold; background: #{$color}; color: #fff; opacity: 0.8;'
            >{$short}</pre>
    </span>
_ML;
    }
}
