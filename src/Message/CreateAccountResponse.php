<?php

namespace InfinityFree\MofhClient\Message;

class CreateAccountResponse extends AbstractResponse
{
    use HasXmlPayload;

    /**
     * Get the VistaPanel username of the account (like test_123456789)
     */
    public function getVpUsername(): ?string
    {
        return $this->getData()['result']['options']['vpusername'] ?? null;
    }
}
