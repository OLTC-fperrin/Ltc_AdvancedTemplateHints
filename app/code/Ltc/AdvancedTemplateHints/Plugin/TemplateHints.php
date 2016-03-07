<?php

namespace Ltc\AdvancedTemplateHints\Plugin;

class TemplateHints {

    /**
     * @var \Magento\Developer\Helper\Data $devHelper
     */
    protected $devHelper;

    /**
     * @var \Ltc\AdvancedTemplateHints\Model\TemplateHintsFactory $templateHintsFactory
     */
    protected $templateHintsFactory;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Context $context
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Asset\Repository $assetRepository
     */
    protected $assetRepository;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection $assetCollection
     */
    protected $assetCollection;

    /**
     * @var bool|mixed $_ath
     */
    private $_ath = false;

    /**
     * TemplateHints constructor.
     *
     * @param \Magento\Developer\Helper\Data $devHelper
     * @param \Ltc\AdvancedTemplateHints\Model\TemplateHintsFactory $templateHintsFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\View\Context $context
     * @param \Magento\Framework\View\Asset\Repository $assetRepository,
     * @param \Magento\Framework\View\Asset\GroupedCollection $assetCollection
     */
    public function __construct(
        \Magento\Developer\Helper\Data $devHelper,
        \Ltc\AdvancedTemplateHints\Model\TemplateHintsFactory $templateHintsFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\View\Asset\GroupedCollection $assetCollection
    ) {
        $this->devHelper = $devHelper;
        $this->templateHintsFactory = $templateHintsFactory;
        $this->logger = $logger;
        $this->request = $request;
        $this->context = $context;
        $this->assetRepository = $assetRepository;
        $this->assetCollection = $assetCollection;

        $this->_ath = filter_var($this->request->getParam('ath'), FILTER_VALIDATE_BOOLEAN);

        if($this->_ath && $this->devHelper->isDevAllowed()) {
            /** Adding Module Assets if ath=1 and is allowed */
            /** Adding Opentip native script because not optimized for requirejs */

            $idOpentipCss = 'Ltc_AdvancedTemplateHints/css/opentip.css';
            $idCommonCss = 'Ltc_AdvancedTemplateHints/css/style.css';
            $idOpentipJs = 'Ltc_AdvancedTemplateHints/js/opentip-native.min.js';

            $opentipCss = $this->assetRepository->createAsset($idOpentipCss);
            $commonCss = $this->assetRepository->createAsset($idCommonCss);
            $opentipJs = $this->assetRepository->createAsset($idOpentipJs);

            $this->assetCollection->add('Ltc_AdvancedTemplateHints/css/opentip', $opentipCss, ['type' => \Magento\Framework\UrlInterface::URL_TYPE_LINK, 'rel' => 'stylesheet', 'media' => 'all']);
            $this->assetCollection->add('Ltc_AdvancedTemplateHints/css/style', $commonCss, ['type' => \Magento\Framework\UrlInterface::URL_TYPE_LINK, 'rel' => 'stylesheet', 'media' => 'all']);
            $this->assetCollection->add('Ltc_AdvancedTemplateHints/js/opentip', $opentipJs, ['type' => \Magento\Framework\UrlInterface::URL_TYPE_JS]);
        }
    }

    /**
     * Process template after creation
     *
     * @param \Magento\Framework\View\TemplateEngineFactory $subject
     * @param \Magento\Framework\View\TemplateEngineInterface $invocationResult
     * @return \Magento\Framework\View\TemplateEngineInterface
     */
    public function afterCreate(
        \Magento\Framework\View\TemplateEngineFactory $subject,
        \Magento\Framework\View\TemplateEngineInterface $invocationResult
    ) {


        if($this->_ath && $this->devHelper->isDevAllowed()) {
            return $this->templateHintsFactory->create([
                'subject' => $invocationResult
            ]);
        }
        return $invocationResult;
    }
}