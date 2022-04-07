<?php

use TestCommon\BrowserTest;
use TestCommon\TestFixtures;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

class SubscriptionScenarioTest extends BrowserTest
{
    protected $fixtures;

    public function setUp() :void
    {
        $this->setDefaultWebDriver();
        $this->fixtures = TestFixtures::get('SubscriptionScenarioTest.btf');
    }

    public function tearDown() : void
    {
        if (!empty($this->webDriver))
            $this->webDriver->close();
    }

    public function processStepByStepMode($page) {
        if (array_key_exists("step_by_step",$page) && $page["step_by_step"]==true) {
            $this->waitForUserInput();
        } else {
            if (!array_key_exists("step_by_step",$page) &&
                array_key_exists("step_by_step",$this->fixtures) &&
                $this->fixtures["step_by_step"]==true) {
                $this->waitForUserInput();
            }
        }
    }

    public function testDefaultSubscriptionScenario()
    {
        foreach ( $this->fixtures["senarios"] as $senarioId ) {
            $senario = $this->fixtures[$senarioId];
            $this->assertNotEmpty($senario["page2"]["email"], TestFixtures::getNonConfiguredEmailMessage());
            $this->pageHome($senario['pageHome'])
                ->pageOffers($senario["pageOffers"])
                ->page1_1($senario["page1_1"])
                ->page1_2($senario["page1_2"])
                ->page2($senario["page2"])
                ->page3($senario["page3"])
                ->page4($senario["page4"])
                ->page5($senario["page5"])
                ->page6($senario["page6"])
                ->page6_2($senario["page6_2"]);
        }
    }

    private function pageHome($fixtures)
    {
        $this->webDriver->get($fixtures['url']);
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));
        $this->webDriver->wait()->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath($fixtures['xpath_to_click'])));
        $element = $this->webDriver->findElement(WebDriverBy::xpath($fixtures['xpath_to_click']));
        $this->processStepByStepMode($fixtures);
        $element->click();
        return $this;
    }
    private function pageOffers($fixtures)
    {
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));
        $this->webDriver->wait()->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath($fixtures['xpath_to_click'])));
        $element = $this->webDriver->findElement(WebDriverBy::xpath($fixtures['xpath_to_click']));
        $this->processStepByStepMode($fixtures);
        $element->click();
        return $this;
    }
    /**
     * page 1
     * Select UI contract
     * Select UI1 price (birth date set to 25/10/1982
     *
     * @return $this
     */
    private function page1_1($fixtures) {



        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        // check a modal is there and close if necessary
        if (count($this->webDriver->findElements(WebDriverBy::className("abtasty-modal__close"))) != 0) {
            $elements = $this->webDriver->findElements(WebDriverBy::className("abtasty-modal__close"));
            foreach ($elements as $elt) {
                $elt->click();
            }
        }


        // Select UI1
        $elt = $this->webDriver->findElement(WebDriverBy::id($fixtures['ui']));
        $elt->click();

        // Wait new part of form is displayed
        $this->waitForElementById('FirstSelectDay');

        // fill with birth date
        $select = $this->webDriver->findElement(WebDriverBy::id('FirstSelectDay'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='{$fixtures['birth_day']}']"));
        $elt->click();

        $select = $this->webDriver->findElement(WebDriverBy::id('FirstSelectMonth'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='{$fixtures['birth_month']}']"));
        $elt->click();

        $select = $this->webDriver->findElement(WebDriverBy::id('FirstSelectYear'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='{$fixtures['birth_year']}']"));
        $elt->click();

        // Wait valid button is displayed
        $this->waitForElementById($fixtures['ui']);

        // validate form
        $elt = $this->webDriver->findElement(WebDriverBy::id($fixtures['ui']));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }


    /**
     * page 2
     * Select no sponsorship
     * Select offer : Bilendi
     *
     * @return $this
     */
    private function page1_2($fixtures) {

        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures["titleContains"]));
        //$this->webDriver->wait()->until(WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath($fixtures['xpath_to_click'])));
        //$this->waitForElementById("validForm_subscriptionSponsorshipBean_sponsored{$fixtures['sponsorship']}");
        // no sponsorship
        $elt = $this->webDriver->findElement(WebDriverBy::id("validForm_subscriptionSponsorshipBean_sponsored{$fixtures['sponsorship']}"));
        $elt->click();
        //$this->webDriver->switchTo()->alert()->accept();

        // Bilendi test offer
        //$elt = $this->webDriver->findElement(WebDriverBy::id("validForm_subscriptionSponsorshipBean_offerId{$fixtures['offerId']}"));
        //$elt->click();

        // Wait valid button is displayed
        $this->waitForElementById('_valid');

        $elt = $this->webDriver->findElement(WebDriverBy::id('_valid'));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }

    /**
     * page 3
     * Select and upload picture
     * Set identity
     *
     * @return $this
     */
    private function page2($fixtures) {

        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        // uplad picture
        $script = file_get_contents(dirname(__FILE__) . '/upload_picture.js');
        $this->webDriver->executeScript($script, array());
        $this->waitForUserInput();
        $this->webDriver->switchTo()->alert()->accept();
        $this->waitForUserInput();

        // gender
        $elt = $this->webDriver->findElement(WebDriverBy::id("civility{$fixtures['civility']}"));
        $elt->click();
        $elt->sendKeys(' ');

        // Lastname
        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_identityBean_lastname'));
        $elt->sendKeys($fixtures['lastname']);

        // Firstname
        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_identityBean_firstname'));
        $elt->sendKeys($fixtures['firstname']);

        // fill with birth date
        $select = $this->webDriver->findElement(WebDriverBy::id('FirstSelectDay'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='{$fixtures['birth_day']}']"));
        $elt->click();

        $select = $this->webDriver->findElement(WebDriverBy::id('FirstSelectMonth'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='{$fixtures['birth_month']}']"));
        $elt->click();

        $select = $this->webDriver->findElement(WebDriverBy::id('FirstSelectYear'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='{$fixtures['birth_year']}']"));
        $elt->click();

        // Phone
        if (!empty($fixtures['fixe'])) {
            $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_identityBean_fixe'));
            $elt->sendKeys($fixtures['fixe']);
        }

        // Mobile Phone
        if (!empty($fixtures['mobile'])) {
            $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_identityBean_mobile'));
            $elt->sendKeys($fixtures['mobile']);
        }

        // email
        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_identityBean_email'));
        $elt->sendKeys($fixtures['email']);

        //Addresse
        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_addressBean_streetNumber'));
        $elt->sendKeys($fixtures['streetNumber']);

        $select = $this->webDriver->findElement(WebDriverBy::id('streetTypeId'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='" . $fixtures['streetTypeId'] . "']")); // Rue has id 1
        $elt->click();

        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_addressBean_streetName'));
        $elt->sendKeys($fixtures['streetName']);

        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_addressBean_zipCode'));
        $elt->sendKeys($fixtures['zipCode']);

        $elt = $this->webDriver->findElement(WebDriverBy::id('subscriptionSubscriberBean_addressBean_city'));
        $elt->sendKeys($fixtures['city']);

        $select = $this->webDriver->findElement(WebDriverBy::id('countryId'));
        $elt = $select->findElement(WebDriverBy::xpath("option[@value='" . $fixtures['countryId'] . "']")); // France has id 1
        $elt->click();

        $elt = $this->webDriver->findElement(WebDriverBy::id('validForm__valid'));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }

    /**
     * page 4
     * Select prélèvement
     * Select payeur = porteur
     *
     * @return $this
     */
    private function page3($fixtures) {
        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        // Mode de paiement
        $elt = $this->webDriver->findElement(WebDriverBy::id("validForm_subscriptionPayerBean_paymentType{$fixtures['paymentType']}"));
        $elt->click();

        // holder = payeur
        $elt = $this->webDriver->findElement(WebDriverBy::id("validForm_subscriptionPayerBean_subscriberIsPayer{$fixtures['subscriberIsPayer']}"));
        $elt->click();

        // Wait valid button is displayed
        $this->waitForElementById('_valid');

        $elt = $this->webDriver->findElement(WebDriverBy::id('_valid'));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }

    /**
     * page 5
     * Valid CGU
     *
     * @return $this
     */
    private function page4($fixtures) {
        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        // Accept conditions
        $elt = $this->webDriver->findElement(WebDriverBy::id('conditions'));
        $elt->click();

        $elt = $this->webDriver->findElement(WebDriverBy::id('validForm__valid'));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }

    /**
     * page 6
     * Rib
     *
     * @return $this
     */
    private function page5($fixtures) {

        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        // uplad rib
        $script = file_get_contents(dirname(__FILE__) . '/upload_rib.js');
        $this->webDriver->executeScript($script, array());
        $this->waitForUserInput();
        $this->webDriver->switchTo()->alert()->accept();
        $this->waitForUserInput();

        $elt = $this->webDriver->findElement(WebDriverBy::id('_valid'));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }

    /**
     * page 7
     * Signature
     *
     * @return $this
     */
    private function page6($fixtures) {

        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        sleep(5);

        $elt = $this->webDriver->findElement(WebDriverBy::id('validForm__valid'));
        $this->processStepByStepMode($fixtures);
        $elt->click();

        return $this;
    }

    /**
     * page 8
     * Signature
     *
     * @return $this
     */
    private function page6_2($fixtures) {

        // Wait new page is loaded
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));
        $this->processStepByStepMode($fixtures);
        return $this;
    }


}
