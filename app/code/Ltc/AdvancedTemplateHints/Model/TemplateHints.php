<?php
/**
 * Copyright © 2016 ONLY Lyon Tourism et congrès. All rights reserved.
 * Created by fperrin
 * 10/2/16 10:30
 */
 
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
     * @param \Magento\Framework\View\TemplateEngineInterface
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
     * Render the named template in the context of a particular block and with
     * the data provided in $vars.
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
    )
    {
        $blockContent = $this->_subject->render($block, $templateFile, $dictionary);

        $id = uniqid();

        $path = $this->helper->getBlockPath($block);

        $blockInfo = $this->helper->getBlockInfo($block);

        $wrappedHtml = sprintf(
            '<div id="tpl-hint-%1$s" class="%2$s">
                %3$s
                <div id="tpl-hint-%1$s-title" style="display: none;">%4$s</div>
                <div id="tpl-hint-%1$s-infobox" style="display: none;">%5$s</div>
            </div>',
            $id,
            $this->getHintClass() . ' ' . $blockInfo['cache-status'],
            $blockContent,
            $this->helper->renderTitle($blockInfo),
            $this->renderBox($blockInfo, $path)
        );


        return $wrappedHtml;
    }

    /**
     * Get CSS class for the hint
     *
     * @return string
     */
    protected function getHintClass()
    {
        return 'tpl-hint tpl-hint-border';
    }

    /**
     * Render box
     *
     * @param array $info
     * @param array $path
     * @return string
     */
    protected function renderBox(array $info, array $path) {

        $output = '';
        $output .= '<dl>';
        $output .= $this->arrayToDtDd($info, array('name', 'alias'));
        if (count($path) > 0) {
            $output .= '<dt>'.__('Block nesting').':</dt><dd>';
            $output .= '<ul class="path">';
            foreach ($path as $step) {
                $output .= '<li>'.$this->helper->renderTitle($step).'</li>';
            }
            $output .= '</ul>';
            $output .= '</dd>';
        }
        $output .= '</dl>';
        return $output;
    }
    /**
     * Render array as <dl>
     *
     * @param array $array
     * @param array $skipKeys
     * @return string
     */
    protected function arrayToDtDd(array $array, array $skipKeys=array()) {
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
}