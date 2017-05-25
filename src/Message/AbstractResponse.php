<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 24-5-17
 * Time: 13:15
 */

namespace HansAdema\MofhClient\Message;


abstract class AbstractResponse
{
    /**
     * The embodied request object.
     *
     * @var AbstractRequest
     */
    protected $request;

    /**
     * The data contained in the response.
     *
     * @var mixed
     */
    protected $data;

    /**
     * The response interface
     *
     * @var mixed
     */
    protected $response;

    /**
     * Constructor
     *
     * @param mixed $request the initiating request.
     * @param mixed $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;

        $data = (string)$this->response->getBody();

        if (strpos(trim($data), '<') !== 0) {
            $this->data = null;
        } else {
            $this->data = $this->xmlToArray((array)simplexml_load_string($data));
        }
    }

    /**
     * Get the response data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Recursively convert a SimpleXMLElement array to regular arrays
     *
     * @param array $input
     * @return array
     */
    protected function xmlToArray($input)
    {
        foreach ($input as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $value = (array)$value;
            }

            if (is_array($value)) {
                $input[$key] = $this->xmlToArray($value);
            }
        }

        return $input;
    }

    /**
     * Response Message
     *
     * @return null|string A response message from the payment gateway
     */
    public function getMessage()
    {
        if ($this->getData() && isset($this->getData()['result']['statusmsg'])) {
            return trim($this->getData()['result']['statusmsg']);
        } else {
            return (string)$this->response->getBody();
        }
    }

    /**
     * Response code
     *
     * @return null|string A response code from the payment gateway
     */
    public function getCode()
    {
        $message = $this->getMessage();

        foreach ($this->getMessageRules() as $rule => $code) {
            if (substr($rule, 0, 1) == '/') {
                if (preg_match($rule, $message)) {
                    return $code;
                }
            } else {
                if (strpos($message, $rule) !== false) {
                    return $code;
                }
            }
        }

        return null;
    }

    protected function getMessageRules()
    {
        return [
            'The API key you are using appears to be invalid' => 'invalid_api_key',
            'The API username you are using appears to be invalid' => 'invalid_api_username',
            'does not match the allowed ip address' => 'invalid_api_ip',
            'No account mathcing this username' => 'unknown_username',
            'choosen password is to short' => 'password_too_short',
            'username is invalid' => 'invalid_username',
            'choosen password contains illegal characters' => 'password_invalid_characters',
            'domdin name appears invalid (to long !)' => 'domain_too_long',
            'domain name appears invalid (to short !)' => 'domain_too_short',
            'domain name choosen does not appear to be valid / allowed' => 'domain_blacklisted_keyword',
            'The domain name choosen is not allowd' => 'domain_blacklisted_keyword',
            'we do not support IDN domains' => 'idn_domain',
            'Sorry we do not support hosting .tk domains on free hosting' => 'tk_domain',
            'http:// should NOT be added to the domain name' => 'domain_http_prefix',
            'Illegal charachters in domain name' => 'domain_invalid_characters',
        ];
    }

    /**
     * Whether the action was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->getData() && isset($this->getData()['result']['status'])) {
            return $this->getData()['result']['status'] == 1;
        } else {
            return false;
        }
    }
}