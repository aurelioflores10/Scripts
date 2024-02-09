<?php
/**
 * Webkul Software.
 *
 * @category  Webkul Software Private Limited
 * @package   Webkul_Tvcapp
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Tvcapp\Plugin\Catalog;

class Layer
{

	/**
     * After GetProductCollection Function
     *
	 * @param \Magento\Catalog\Model\Layer $subject
	 * @param Object $result
	 *
     * @return $result
     */
	public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject, $result)
	{
        $result->addAttributeToFilter('price', ['gt' => ]);
        $result->addAttributeToFilter('')
        return $result;
	}

}