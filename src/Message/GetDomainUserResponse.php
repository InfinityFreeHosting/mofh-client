<?php

namespace InfinityFree\MofhClient\Message;

class GetDomainUserResponse extends AbstractResponse
{
    use HasPlainPayload;

    protected $status;

    protected $domain;

    protected $username;

    protected $documentRoot;

    /**
     * Check if the domain was found.
     */
    public function isFound(): bool
    {
        return is_array($this->data) && $this->data !== [];
    }

    /**
     * Get the domain name which was searched for.
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Get the status of the account (ACTIVE or SUSPENDED).
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Get the full document root of the domain name.
     *
     * For example:
     * /home/volXX_X/epizy.com/host_12345678/example.com/htdocs
     */
    public function getDocumentRoot(): ?string
    {
        return $this->documentRoot;
    }

    /**
     * Get the username of the account to which this domain name belongs.
     *
     * For example: host_12345678
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }
}
