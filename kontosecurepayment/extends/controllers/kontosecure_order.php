<?php

class kontosecure_order extends kontosecure_order_parent
{
    public function continueOrder()
    {
        $oConfig = $this->getConfig();
        $sOrderId = $oConfig->getRequestParameter('orderid');

        $oSession = new oxSession();

        // additional check if we really really have a user now
        if (!$oUser = $this->getUser()) {
            return 'user';
        }

        // get basket contents
        $oBasket = $this->getSession()->getBasket();
        if ($oBasket->getProductsCount()) {
            try {
                $oOrder = $oSession->getVariable('kontosecureoxorder');
                $iSuccess = $oOrder->kontosecureFinalizeOrder($oBasket, $oUser);

                // performing special actions after user finishes order (assignment to special user groups)
                $oUser->onOrderExecute($oBasket, $iSuccess);

                // proceeding to next view
                return $this->_getNextStep($iSuccess);
            }
            catch (oxOutOfStockException $oEx) {
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx, false, true, 'basket');
            }
            catch (oxNoArticleException $oEx) {
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
            }
            catch (oxArticleInputException $oEx) {
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
            }
        }
    }
}
