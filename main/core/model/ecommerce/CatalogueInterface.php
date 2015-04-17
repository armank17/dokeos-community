<?php

interface CatalogueInterface
{
   public function getListForStudentPortal();
   public function registerItemsForUser(array $session, array $transactionResult , $userId);
   public function getShoppingCartList();  
}
