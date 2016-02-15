<?php

namespace Ltc\AdvancedTemplateHints\Model;

use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\App\State as AppState;
use Psr\Log\LoggerInterface as Logger;

class Layout extends \Magento\Framework\View\Layout implements \Magento\Framework\View\LayoutInterface {

    protected $request;
    protected $devHelper;
    private $_ath = false;

    public function __construct(
        \Magento\Framework\View\Layout\ProcessorFactory $processorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\Layout\Data\Structure $structure,
        MessageManagerInterface $messageManager,
        \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver,
        \Magento\Framework\View\Layout\ReaderPool $readerPool,
        \Magento\Framework\View\Layout\GeneratorPool $generatorPool,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\View\Layout\Reader\ContextFactory $readerContextFactory,
        \Magento\Framework\View\Layout\Generator\ContextFactory $generatorContextFactory,
        AppState $appState,
        Logger $logger,
        \Magento\Developer\Helper\Data $devHelper,
        \Magento\Framework\App\Request\Http $request,
        $cacheable = true)
    {
        $this->devHelper = $devHelper;
        $this->request = $request;

        $this->_ath = filter_var($this->request->getParam('ath'), FILTER_VALIDATE_BOOLEAN);

        parent::__construct($processorFactory, $eventManager, $structure, $messageManager, $themeResolver, $readerPool, $generatorPool, $cache, $readerContextFactory, $generatorContextFactory, $appState, $logger, $cacheable);
    }

    /**
     * Gets HTML of container element
     *
     * @param string $name
     * @return string
     */
    protected function _renderContainer($name)
    {


        $html = parent::_renderContainer($name);

        if($this->_ath && $this->devHelper->isDevAllowed()) {

            $id = uniqid();

            $info = array(
                'name' => $name,
                'alias' => $this->getElementAlias($name)
            );

            $info['htmlId'] = $this->structure->getAttribute($name, \Magento\Framework\View\Layout\Element::CONTAINER_OPT_HTML_ID);
            $info['htmlClass'] = $this->structure->getAttribute($name, \Magento\Framework\View\Layout\Element::CONTAINER_OPT_HTML_CLASS);
            $info['htmlTag'] = $this->structure->getAttribute($name, \Magento\Framework\View\Layout\Element::CONTAINER_OPT_HTML_TAG);

            $wrappedHtml = sprintf(
                '<div id="tpl-hint-%1$s" class="%2$s">
                    %3$s
                    <div id="tpl-hint-%1$s-title" style="display: none;">%4$s</div>
                    <div id="tpl-hint-%1$s-infobox" style="display: none;">%5$s</div>
                </div>',
                $id,
                'tpl-hint tpl-hint-border layout-container',
                $html,
                $this->renderTitle($info),
                $this->renderBox($info)
            );

            return $wrappedHtml;

        } else {
            return $html;
        }
    }

    public function renderBox(array $info) {

        $output = '';
        $output .= '<dl>';
        $output .= $this->arrayToDtDd($info, array('name', 'alias'));
        $output .= '</dl>';
        return $output;
    }

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

    public function renderTitle(array $info) {
        $title = $info['name'];
        if ($info['name'] != $info['alias'] && $info['alias']) {
            $title .= ' <small>(alias: ' . $info['alias'] . ')</small>';
        }
        return $title;
    }

}