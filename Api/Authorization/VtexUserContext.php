<?php

namespace Vtex\VtexMagento\Api\Authorization;

use Magento\Webapi\Model\Authorization\TokenUserContext;

/**
 * Guest user context
 */
class VtexUserContext extends TokenUserContext
{
    protected function processRequest()
    {
        if ($this->isRequestProcessed) {
            return;
        }

        $bearerToken = $this->request->getParam('token');
        if (!$bearerToken) {
            $this->isRequestProcessed = true;
            return;
        }

        $token = $this->tokenFactory->create()->loadByToken($bearerToken);

        if (!$token->getId() || $token->getRevoked()) {
            $this->isRequestProcessed = true;
            return;
        }

        $this->setUserDataViaToken($token);
        $this->isRequestProcessed = true;
    }
}
