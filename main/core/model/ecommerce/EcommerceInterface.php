<?php
interface EcommerceInterface
{
    public function getForm();
    public function save( array $post, array $files );
    
    public function getGatewaySettings();
    public function getGateway();
    public function setGateway($_gateway);
    public function isEnabled();
    public function setEnabled($enabled);
    public function getGatewayData();
    public function setGatewayData( $_gatewayData );
    public function getSettingsFromDb();
    public function proccessPayment($request);
}
