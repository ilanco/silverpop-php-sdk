<?php

/**
 * Silverpop API
 *
 * @author Ilan Cohen <ilanco@gmail.com>
 */
class Silverpop
{
    /**
     * Default options for curl.
     */
    public static $CURL_OPTS = array(
        CURLOPT_POST => 1,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'silverpop-php-sdk',
    );

    protected $endpoint;

    protected $username;

    protected $password;

    protected $sessionId;


    public function __construct($config = array())
    {
        $this->endpoint = $config['endpoint'];

        if (!empty($config['username']) && !empty($config['password'])) {
            $this->username = $config['username'];
            $this->password = $config['password'];
        }
    }

    public function login()
    {
        $xml = "<Login>";
        $xml .= "<USERNAME>" . $this->username . "</USERNAME>";
        $xml .= "<PASSWORD>" . $this->password . "</PASSWORD>";
        $xml .= "</Login>";

        $response = $this->request($xml, false);

        if (is_object($response) && isset($response->getElementsByTagName('SESSIONID')->item(0)->nodeValue)) {
            $this->sessionId = $response->getElementsByTagName('SESSIONID')->item(0)->nodeValue;

            return true;
        }

        return false;
    }

    public function addRecipient($email, $databaseId, $createdFrom = 1)
    {
        $xml = "<AddRecipient>";
        $xml .= "<LIST_ID>$databaseId</LIST_ID>";
        $xml .= "<CREATED_FROM>$createdFrom</CREATED_FROM>";
        $xml .= "<UPDATE_IF_FOUND>true</UPDATE_IF_FOUND>";
        $xml .= "<COLUMN>";
        $xml .= "<NAME>EMAIL</NAME>";
        $xml .= "<VALUE>$email</VALUE>";
        $xml .= "</COLUMN>";
        $xml .= "</AddRecipient>";

        $result = $this->request($xml);
    }

    public function addContactToContactList($email, $listId)
    {
        $xml = "<AddContactToContactList>";
        $xml .= "<CONTACT_LIST_ID>$listId</CONTACT_LIST_ID>";
        $xml .= "<COLUMN>";
        $xml .= "<NAME>EMAIL</NAME>";
        $xml .= "<VALUE>$email</VALUE>";
        $xml .= "</COLUMN>";
        $xml .= "</AddContactToContactList>";

        $result = $this->request($xml);
    }

    public function removeRecipient($email, $listId)
    {
        $xml = "<RemoveRecipient>";
        $xml .= "<LIST_ID>$listId</LIST_ID>";
        $xml .= "<EMAIL>$email</EMAIL>";
        $xml .= "</RemoveRecipient>";

        $result = $this->request($xml);
    }

    protected function getUrl()
    {
        $url = $this->endpoint;

        if (!empty($this->sessionId)) {
            $url .= ";jsessionid=" . $this->sessionId;
        }

        return $url;
    }

    protected function request($xml, $requiresLogin = true)
    {
        if ($requiresLogin) {
            $this->login();
        }

        $xmlRequest = "xml=<?xml version=\"1.0\"?><Envelope><Body>" . $xml . "</Body></Envelope>";

        $ch = curl_init();

        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = $this->getUrl();
        $opts[CURLOPT_POSTFIELDS] = $xmlRequest;
        curl_setopt_array($ch, $opts);

        $result = curl_exec($ch);

        if (($pos = stripos($result, '<Envelope>')) === NULL) {
            echo "Unspecified error diagnostic. Output dump: $result";

            return array();
        }

        $epos = stripos($result, '</Envelope>') + strlen('</Envelope>');
        $resultBody = substr($result, $pos, $epos - $pos);

        $response = new DOMDocument();
        $response->loadXML($resultBody);

        $success = $response->getElementsByTagName('SUCCESS');
        $fault = $response->getElementsByTagName('FaultString');
        $errorid = $response->getElementsByTagName('errorid');

        if ($success->length && strtoupper($response->getElementsByTagName('SUCCESS')->item(0)->nodeValue) == 'FALSE') {
            $err_arr = array();
            $msg = 'API call failed.';

            // did we get an error message?
            if ($fault->length) {
                $err_arr['@fault'] = $response->getElementsByTagName('FaultString')->item(0)->nodeValue;
                $msg .= ' Diagnostic: ' . $err_arr['@fault'];
            }

            // did we get an error number?
            if ($errorid->length) {
                $err_arr['@errorid'] = $response->getElementsByTagName('errorid')->item(0)->nodeValue;
                $msg .= ' Error code: ' . $err_arr['@errorid'];
            }

            echo $msg;

            return array(false, $response);
        }

        return $response;
    }
}

