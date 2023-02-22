<?php

namespace InfinityFree\MofhClient\Message;

class GetDomainUserResponse extends AbstractResponse
{
    protected $status;

    protected $domain;

    protected $username;

    protected $documentRoot;

    protected function parseResponse()
    {
        $responseBody = (string) $this->response->getBody();

        if (strpos($responseBody, '[') === 0) {
            $this->data = json_decode($responseBody, true);

            if ($this->data && count($this->data) == 4) {
                [$this->status, $this->domain, $this->documentRoot, $this->username] = $this->data;
            }
        } elseif ($responseBody === 'null') {
            $this->data = [];
        } else {
            $this->data = trim($responseBody);
        }
    }

    /**
     * Get the error message, if defined.
     */
    public function getMessage(): ?string
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Check if the request was successful.
     */
    public function isSuccessful(): bool
    {
        return is_array($this->data);
    }

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
