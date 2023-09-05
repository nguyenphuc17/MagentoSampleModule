<?php
namespace Frednguyen\TagLine\ViewModel;

use Magento\Catalog\Model\Product;
class TagLine implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;


    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $_escaper
    ) {
        $this->_coreRegistry = $registry;
        $this->_escaper = $_escaper;
    }

    /**
     * @return string
     */
    public function getTagLine()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        $tagLine = $this->_product->getResource()->getAttribute('tag_line')->getFrontend()->getValue($this->_product);
        return $this->_escaper->escapeHtml($tagLine);
    }
}
