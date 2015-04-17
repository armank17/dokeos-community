<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceInterface.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceAbstract.php';

class EcommerceNone extends EcommerceAbstract implements EcommerceInterface
{
	/* (non-PHPdoc)
     * @see EcommerceInterface::getForm()
     */
    public function getForm()
    {
        return null;
    }

	/* (non-PHPdoc)
	 * 
	 * 
     * @see EcommerceInterface::save()
     */
    public function save( array $post , array $files )
    {
        throw  new Exception();
    }

	/* (non-PHPdoc)
     * @see EcommerceInterface::getData()
     */
    public function getData()
    {
        return null;
    }
	/* (non-PHPdoc)
     * @see EcommerceInterface::proccessPayment()
     */
    public function proccessPayment( $request )
    {
        // TODO Auto-generated method stub
        
    }



}
