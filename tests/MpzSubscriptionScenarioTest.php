<?php

use TestCommon\BrowserTest;

/**
 * read the README for a basic description of such a selenium test class principles;
 * read also comment of class BankFileTransferTest as this test class shares the same design base.
 * this test class covers the basic life cycle of an mpz member :
 * subscription / unsubscription / anonymization (one test method for each);
 * default setting is designed to run each of the three corresponding test method independently (yet in the proper order);
 * the goal is to be able to sequence these test methods with other scenarios (like bank file transfer and in the future
 * surveys answering) in order to automate full members activities scenarios executions;
 * yet one can also automate a sequential execution of these methods by adding the @ to their 'depends' comment.
 */
class MpzSubscriptionScenarioTest extends BrowserTest
{
    use MpzSharedScenario\MpzSharedScenarioTrait;

    protected $fixtures;

    protected $testStates = array();

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        fwrite(STDERR, "!!! EMAILS IN DATASET IN SHOULD BE EXISTING GMAIL ACCOUNTS\n!!!YOU HAVE TO LOGIN FIRST TO GMAIL AND CLOSE THE BROWSER JUST BEFORE RUNNING THIS TEST\n(this will be fixed in a next version)\n");
    }

    public function setUp(): void
    {
        $this->setDefaultWebDriver();
        //loads features and set testDefaultPendingTransferRequestCreationScenario as the next test method
        $this->loadFixtures('MpzSubscriptionScenarioTest',
            'testSubscription',
            'testSubscription');
    }

    public function tearDown(): void
    {
        if (!empty($this->webDriver) && empty($this->fixtures['keep_browser']))
            $this->webDriver->close();
    }


    /**
     *
     */
    public function testSubscription()
    {
        $dataSetInIndex = 0;
        $dataSetIn = $this->fixtures['dataset_in']['testSubscription'];
        foreach ($this->fixtures["scenarios"]['testSubscription'] as $scenario) {
            if (empty($dataSetIn[$dataSetInIndex]['skip'])) {
                $this
                   ->page_fill($scenario['page_home'])
                    //to catchup  after page_home step : comment it and uncomment page_login step,
                    // then do the same in MpzSubscriptionScenarioTest/testSubscription.json
                    //->page_login($scenario['page_login'])
                    ->page_debug_bar($scenario['page_debug_bar'])
                    ->page_fill($scenario['page_registration_step_1'])
                    ->page_wait($scenario['page_registration_step_1_wait_for_city'])
                    ->page_fill($scenario['page_registration_step_1_continue'])
                    ->page_fill($scenario['page_registration_step_2'])
                    ->page_nav($scenario['page_confirm_email'])
                    ->wait(10)
                    ->page_fill($scenario['page_gmail'])
                    ->page_nav($scenario['page_gmail_result'])
                    ->page_nav($scenario['page_gmail_mail'])
                    ->page_nav($scenario['page_home_back'])
                    ->page_nav($scenario['page_logout'])
                ;
            }
            $dataSetInIndex++;
        }
    }

    /**
     * depends testSubscription
     */
    public function testUnsubscription()
    {
        $this->loadFixtures('MpzSubscriptionScenarioTest',
            'testUnsubscription',
            'testSubscription');
        $dataSetInIndex = 0;
        $dataSetIn = $this->fixtures['dataset_in']['testSubscription'];
        foreach ($this->fixtures["scenarios"]['testUnsubscription'] as $scenario) {
            if (empty($dataSetIn[$dataSetInIndex]['skip'])) {
                $this
                    ->page_login($scenario['page_login'])
                    ->page_debug_bar($scenario['page_debug_bar'])
                    ->page_nav($scenario['page_home'])
                    ->page_nav($scenario['page_member_menu'])
                    ->page_nav($scenario['page_personnal_data'])
                    ->page_fill($scenario['page_unsubscribe'])
                    ->page_nav($scenario['page_unsubscribe_confirm'])
                ;
            }
            $dataSetInIndex++;
        }
    }

    /**
     * depends testUnsubscription
     */
    public function testAnonymisation()
    {
        $this->loadFixtures('MpzSubscriptionScenarioTest',
            'testAnonymisation',
            'testSubscription');
        $dataSetInIndex = 0;
        $dataSetIn = $this->fixtures['dataset_in']['testSubscription'];
        foreach ($this->fixtures["scenarios"]['testAnonymisation'] as $scenario) {
            if (empty($dataSetIn[$dataSetInIndex]['skip'])) {
                if (empty($scenario['cron_anonymize']['skip'])) {
                    $country = $scenario['cron_anonymize']['country'];
                    $dbCon = $this->getDbCon($this->fixtures, $country);
                    $email = $scenario['cron_anonymize']['member_email'];
                    $idmembre = $dbCon->query(
                        "select idmembre from identite where email = '$email'  and actif='F'
order by idmembre desc
limit 1; ")->fetch()['idmembre'];
                    $cron = sprintf($scenario['cron_anonymize']['command'], $country, $idmembre);
                    exec($cron, $output, $return);
                    $this->assertEquals(0, $return,"execution of the following anonymize cron failed :\n$cron");
                }
            }
            $dataSetInIndex++;
        }
    }

}
