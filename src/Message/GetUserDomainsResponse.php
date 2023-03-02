<?php

namespace InfinityFree\MofhClient\Message;

use InfinityFree\MofhClient\Exception\MismatchedStatusesException;

class GetUserDomainsResponse extends AbstractResponse
{
    protected function parseResponse()
    {
        $responseBody = (string) $this->response->getBody();

        if (strpos($responseBody, '[') === 0) {
            $this->data = json_decode($responseBody, true);
        } elseif ($responseBody === 'null') {
            $this->data = [];
        } else {
            $this->data = trim($responseBody);
        }
    }

    /**
     * Get the error message, if defined.
     *
     * @return array|null|string
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
     * Get the list of domains on the account.
     */
    public function getDomains(): array
    {
        return array_map(function ($item) {
            return $item[1];
        }, is_array($this->data) ? $this->data : []);
    }

    /**
     * Get the status of the account, either ACTIVE or SUSPENDED.
     *
     * @throws MismatchedStatusesException
     */
    public function getStatus(): ?string
    {
        if (is_array($this->data)) {
            $statuses = array_unique(array_map(function ($item) {
                return $item[0];
            }, $this->data));

            if (count($statuses) == 1) {
                return $statuses[0];
            } elseif (count($statuses) > 1) {
                throw new MismatchedStatusesException('The account domains have different statuses: '.implode($statuses));
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
