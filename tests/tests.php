<?php

class SilverpopPhpSdkTestCase extends PHPUnit_Framework_TestCase
{
    public static $config = array(
        'endpoint' => 'http://api1.silverpop.com/XMLAPI', // set to api1, api2, ...
        'username' => '',                                 // api username
        'password' => ''                                  // api password
    );

    public static $fixtures = array(
        'email' => 'test.case@test.com',                  // set test email address
        'database_id' => 0,                               // test database id
        'list_id' => 0,                                   // test list id
        'fields' => array(
            'First Name' => 'NoFirst',
            'Last Name' => 'NoLast',
            'Full Name' => 'NoFull',
            'Address' => 'NoAddress',
            'City' => 'NoCity',
            'State' => 'NoState',
            'Country' => 'NoCountry',
            'Postal Code' => 12345,
            'Phone' => '555-555-5555'
        )
    );

    private function getSilverpopInstance()
    {
        return new Silverpop(self::$config);
    }

    public function testConstructor()
    {
        $silverpop = $this->getSilverpopInstance();
    }

    /**
     * @group create
     */
    public function testAddRecipient()
    {
        $silverpop = $this->getSilverpopInstance();

        $silverpop->addRecipient(self::$fixtures['email'], self::$fixtures['database_id']);
    }

    /**
     * @group update
     */
    public function testUpdateRecipient()
    {
        $silverpop = $this->getSilverpopInstance();

        $silverpop->updateRecipient(self::$fixtures['email'], self::$fixtures['database_id'], self::$fixtures['fields']);
    }

    /**
     * @group create
     */
    public function testAddContactToContactList()
    {
        $silverpop = $this->getSilverpopInstance();

        $silverpop->addContactToContactList(self::$fixtures['email'], self::$fixtures['list_id']);
    }

    /**
     * @group delete
     */
    public function testRemoveRecipient()
    {
        $silverpop = $this->getSilverpopInstance();

        $silverpop->removeRecipient(self::$fixtures['email'], self::$fixtures['database_id']);
    }
}

