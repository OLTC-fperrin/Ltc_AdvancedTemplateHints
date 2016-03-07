<?php

namespace Ltc\AdvancedTemplateHints\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const TYPE_CACHED = 'cached';
    const TYPE_NOTCACHED = 'notcached';
    const TYPE_IMPLICITLYCACHED = 'implicitlycached';

    /**
     * @var \Magento\Framework\View\LayoutInterface $layout
     */
    protected $layout;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Framework\View\LayoutInterface $layout)
    {
        $this->layout = $layout;
        parent::__construct($context);
    }

    /**
     * Retrieve block's info
     *
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @param bool $fullInfo
     * @return array
     */
    public function getBlockInfo(
        \Magento\Framework\View\Element\BlockInterface $block,
        $fullInfo = true
    ){
        $info = array(
            'name' => $block->getNameInLayout(),
            'alias' => $this->layout->getElementAlias($block->getNameInLayout())
        );

        $info['parent'] = $this->layout->getParentName($info['name']);

        if (!$fullInfo) {
            return $info;
        }

        $info['class'] = get_class($block);

        $info['module'] = $block->getModuleName();

        if ($block instanceof \Magento\Cms\Model\Block || $block instanceof \Magento\Cms\Block\Widget\Block) {
            $info['cms-blockId'] = $block->getData('block_id');
        }

        if ($block instanceof \Magento\Cms\Model\Page) {
            $info['cms-pageId'] = $block->getData('page_id'); // Is that working ??
        }

        $templateFile = $block->getTemplateFile();
        $info['template'] = $templateFile;

        /**
         * Remote call to PhpStorm
         * TODO: add config options for remote call method
         */
        $info['template'] = '<a href="http://localhost:8091/?message='.$info['template'].':1">'.$info['template'].'</a>';

        $info['cache-status'] = self::TYPE_NOTCACHED;

        $cacheLifeTime = $block->getCacheLifetime();

        if (!is_null($cacheLifeTime)) {
            $info['cache-lifetime'] = (intval($cacheLifeTime) == 0) ? 'forever' : intval($cacheLifeTime) . ' sec';
            $info['cache-key'] = $block->getCacheKey();
            $info['cache-key-info'] = is_array($block->getCacheKeyInfo())
                ? implode(', ', $block->getCacheKeyInfo())
                : $block->getCacheKeyInfo()
            ;
            $info['tags'] = implode(',', $block->getCacheTags());
            $info['cache-status'] = self::TYPE_CACHED;
        } elseif ($this->isWithinCachedBlock($block)) {
            $info['cache-status'] = self::TYPE_IMPLICITLYCACHED; // not cached, but within cached
        }

        $info['methods'] = get_class_methods(get_class($block));
        return $info;

    }

    /**
     * Get path information of a block
     *
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @return string
     */
    public function getBlockPath(\Magento\Framework\View\Element\BlockInterface $block) {
        $blockPath = array();
        $step = $block->getParentBlock();

        $i = 0;
        while ($i++ < 20 && $step instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $blockPath[] = $this->getBlockInfo($step, false);
            $step = $step->getParentBlock();
        }
        return $blockPath;
    }

    /**
     * Check if a block is within another one that is cached
     *
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @return bool
     */
    public function isWithinCachedBlock(\Magento\Framework\View\Element\BlockInterface $block) {
        $step = $block;
        $i = 0;
        while ($i++ < 20 && $step instanceof \Magento\Framework\View\Element\AbstractBlock) {
            if (!is_null($step->getCacheLifetime())) {
                return true;
            }
            $step = $step->getParentBlock();
        }
        return false;
    }

    /**
     * Render title
     *
     * @param array $info
     * @return string
     */
    public function renderTitle(array $info) {
        $title = $info['name'];
        if ($info['name'] != $info['alias'] && $info['alias']) {
            $title .= ' <small>(alias: ' . $info['alias'] . ')</small>';
        }
        return $title;
    }

    /**
     * Render box
     *
     * @param array $info
     * @param array $path
     * @return string
     */
    public function renderBox(array $info, array $path) {

        $output = '';
        $output .= '<dl>';
        $output .= $this->arrayToDtDd($info, array('name', 'alias'));
        if (count($path) > 0) {
            $output .= '<dt>'.__('Block nesting').':</dt><dd>';
            $output .= '<ul class="path">';
            foreach ($path as $step) {
                $output .= '<li>'.$this->renderTitle($step).'</li>';
            }
            $output .= '</ul>';
            $output .= '</dd>';
        }
        $output .= '</dl>';
        return $output;
    }

    /**
     * Render array as description list HTML
     *
     * @param array $array
     * @param array $skipKeys
     * @return string
     */
    public function arrayToDtDd(array $array, array $skipKeys=array()) {
        $output = '<dl>';
        foreach ($array as $key => $value) {
            if (in_array($key, $skipKeys)) {
                continue;
            }
            if (is_array($value)) {
                $value = $this->arrayToDtDd($value);
            }
            if (is_int($key)) {
                $output .= $value . '<br />';
            } else {
                $output .= '<dt>'.ucfirst($key).':</dt><dd>';
                $output .= $value;
                $output .= '</dd>';
            }
        }
        $output .= '</dl>';
        return $output;
    }

    /**
     * Get CSS class for the hint
     *
     * @return string
     */
    public function getHintClass()
    {
        return 'tpl-hint tpl-hint-border';
    }

}