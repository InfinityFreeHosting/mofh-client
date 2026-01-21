<?php

namespace InfinityFree\MofhClient\Message;

class ListPackagesResponse extends AbstractResponse
{
    use HasJsonPayload;

    public function getPackages(): ?array
    {
        return $this->isSuccessful() ? $this->getData()['packages'] : null;
    }
}
