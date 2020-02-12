<?php

namespace InfinityFree\MofhClient\Message;

class GetDomainUserResponse extends AbstractResponse
{
    protected $status = null;
    protected $domain = null;
    protected $documentRoot = null;
    protected $username = null;

    public function parseResponse()
    {
        $this->data = (string)$this->response->getBody();

        $data = json_decode($this->data, true);

        if ($data && count($data) == 4) {
            list($this->status, $this->domain, $this->documentRoot, $this->username) = $data;
        }
    }

    /**
     * Get the error message, if defined.
     *
     * @return array|null|string
     */
    public function getMessage()
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Check if the request was successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return strpos($this->getData(), '[') === 0 || trim($this->getData()) == 'null';
    }

    /**
     * Get the domain name which was searched for.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the status of the account (ACTIVE or SUSPENDED).
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the full document root of the domain name.
     *
     * For example:
     * /home/volXX_X/epizy.com/host_12345678/example.com/htdocs
     *
     * @return string|null
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * Get the username of the account to which this domain name belongs.
     *
     * For example: host_12345678
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }
}
