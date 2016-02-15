<?php

namespace Ltc\AdvancedTemplateHints\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const TYPE_CACHED = 'cached';
    const TYPE_NOTCACHED = 'notcached';
    const TYPE_IMPLICITLYCACHED = 'implicitlycached';

    protected $layout;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Framework\View\LayoutInterface $layout)
    {
        $this->layout = $layout;
        parent::__construct($context);
    }

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

        //TODO: Add remote call to class file in phpstorm

        $info['module'] = $block->getModuleName();

        if ($block instanceof \Magento\Cms\Model\Block) {
            $info['cms-blockId'] = $block->getIdentifier();
        }
        if ($block instanceof \Magento\Cms\Model\Page) {
            $info['cms-pageId'] = $block->getIdentifier(); // Is that working ??
        }

        $templateFile = $block->getTemplateFile();
        $info['template'] = $templateFile;
        //TODO: Add remote call to template file in phpstorm

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
     * @author Fabrizio Branca
     * @since 2011-01-24
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
            $title .= ' (alias: ' . $info['alias'] . ')';
        }
        return $title;
    }

}