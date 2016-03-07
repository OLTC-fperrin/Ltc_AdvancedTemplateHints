<?php

namespace Ltc\AdvancedTemplateHints\Model;

class TemplateHints implements \Magento\Framework\View\TemplateEngineInterface
{

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\View\TemplateEngineInterface $_subject
     */
    private $_subject;

    /**
     * @var \Ltc\AdvancedTemplateHints\Helper\Data $helper
     */
    protected $helper;

    /**
     * TemplateHints constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\TemplateEngineInterface $subject
     * @param \Ltc\AdvancedTemplateHints\Helper\Data $helper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\TemplateEngineInterface $subject,
        \Ltc\AdvancedTemplateHints\Helper\Data $helper
    )
    {
        $this->logger = $logger;
        $this->_subject = $subject;
        $this->helper = $helper;
    }

    /**
     * Render template
     *
     * Render template with wrapper for template hints
     *
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @param string $templateFile
     * @param array $dictionary
     * @return string rendered template
     */
    public function render(
        \Magento\Framework\View\Element\BlockInterface $block,
        $templateFile,
        array $dictionary = []
    ) {
        $blockContent = $this->_subject->render($block, $templateFile, $dictionary);

        $id = uniqid();

        $path = $this->helper->getBlockPath($block);

        $blockInfo = $this->helper->getBlockInfo($block);

        $wrappedHtml = sprintf(
            '<div id="tpl-hint-%1$s" class="%2$s" data-mage-init=\'{"ltcath":{}}\'>
                %3$s
                <div id="tpl-hint-%1$s-title" style="display: none;">%4$s</div>
                <div id="tpl-hint-%1$s-infobox" style="display: none;">%5$s</div>
            </div>',
            $id,
            $this->helper->getHintClass() . ' ' . $blockInfo['cache-status'],
            $blockContent,
            $this->helper->renderTitle($blockInfo),
            $this->helper->renderBox($blockInfo, $path)
        );

        return $wrappedHtml;
    }
}