<?php

use TestCommon\BrowserTest;

/**
 * read the README for a basic description of such a selenium test class principles;
 * read also comment of class BankFileTransferTest as this test class shares the same design base.
 * this test class covers the (Old BMC) leads scenarios :
 * - "call me back"
 * - ... other lead scenarios to come soon...
 */
class OBMCLeadsScenarioTest extends BrowserTest
{
    protected $fixtures;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function setUp(): void
    {
        $this->setDefaultWebDriver();
        $this->loadFixtures('OBMCLeadsScenarioTest',
            'testCallMeBack',
            'testCallMeBack');
    }

    public function tearDown(): void
    {
        if (!empty($this->webDriver) && empty($this->fixtures['keep_browser']))
            $this->webDriver->close();
    }


    /**
     *
     */
    public function testCallMeBack()
    {
        $dataSetInIndex = 0;
        $dataSetIn = $this->fixtures['dataset_in']['testCallMeBack'];
        foreach ($this->fixtures["scenarios"]['testCallMeBack'] as $scenario) {
            if (empty($dataSetIn[$dataSetInIndex]['skip'])) {
                $this
                    ->page_nav($scenario['page_home'])
                    ->page_nav($scenario['page_vignettes'])
                    ->page_vehicle($scenario['page_vehicle'])
                    ->page_wait($scenario['page_wait_lead_creation'])
                    ->page_fill($scenario['page_salesforce'])
                    ->gotoUrl($scenario['page_salesforce_leads']['url'])
                    ->wait(15)
                    ->page_salesforce_leads_last_row_with_phone_date_check($scenario)
                    ->gotoUrl($scenario['page_salesforce_logout']['url'])
                    ->wait(10)
                ;
            }
            $dataSetInIndex++;
        }
    }

    private function page_vehicle(&$page_vehicle_scenario)
    {
        $page_vehicle_scenario['date_click'] = date('d/m/Y H:i');
        $this->page_fill($page_vehicle_scenario,false,true, $this->formatDate);
        return $this;
    }

    private function page_salesforce_leads_last_row_with_phone_date_check(&$scenario)
    {
        $phone_value = substr($scenario['page_vehicle']["xpath_input_value"]['calculated_value'],1);
        $scenario['page_salesforce_leads_last_row_with_phone_date_check']['xpath_to_get'] = str_replace('{$param1}', $phone_value, $scenario['page_salesforce_leads_last_row_with_phone_date_check']['xpath_to_get']);
        $element = $this->getElement($scenario['page_salesforce_leads_last_row_with_phone_date_check']);
        $dateLead = DateTimeImmutable::createFromFormat("d/m/Y H:i", $element->getText());
        $dateClick = DateTimeImmutable::createFromFormat("d/m/Y H:i",$scenario['page_vehicle']['date_click']);
        $this->assertGreaterThanOrEqual($dateClick->getTimestamp(), $dateLead->getTimestamp(),"lead creation date : {$element->getText()} is not greater or equal than 'lead send' click date : {$scenario['page_vehicle']['date_click']}");
        return $this;
    }

}
