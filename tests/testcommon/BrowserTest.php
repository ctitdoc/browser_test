<?php

namespace TestCommon;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\TestCase;
use PDO;

class BrowserTest extends TestCase
{

    protected RemoteWebDriver $webDriver;

    /**
     * @var $capabilities array
     */
    protected $capabilities;

    protected $normalizeCurrencyDecimal;

    protected $formatDate;


    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        date_default_timezone_set('Europe/Paris');
        parent::__construct($name, $data, $dataName);
        $this->normalizeCurrencyDecimal = function (?string $moneyValue) {
            return (empty($moneyValue)) ? $moneyValue : str_replace([',', '.'], '.', $moneyValue);
        };

        $this->formatDate = function (?string $valueOrDateFormat) {
            return
                (!empty($valueOrDateFormat) && str_starts_with($valueOrDateFormat,'date_format://')) ?
                    date(str_replace('date_format://', '', $valueOrDateFormat))
                    :
                    $valueOrDateFormat ;
        };

    }

    private function _setDriver()
    {
        $this->webDriver = RemoteWebDriver::create('http://127.0.0.1:4444', $this->capabilities);
        //$this->webDriver = RemoteWebDriver::create('http://127.0.0.1:4444', DesiredCapabilities::firefox());
    }

    public function setDefaultWebDriver()
    {

        $capabilitesSettings = BrowserConf::get('default.btc');
        $profilePath = BrowserConf::getProfile($capabilitesSettings);
        $this->assertNotEmpty($profilePath, BrowserConf::getNonConfiguredProfileMessage());
        //let's set firefox as the default testing browser ...
        if (BrowserConf::getBrowserName($capabilitesSettings)=='firefox') {
            $firefoxOptions = new FirefoxOptions();
            $profile = new FirefoxProfile();
            $profile->setPreference('profile', $profilePath);
            $firefoxOptions->setProfile($profile);
            $firefoxOptions->addArguments(BrowserConf::getBrowserArgs($capabilitesSettings));
            $this->capabilities = DesiredCapabilities::firefox();
            $this->capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
        }
        $this->_setDriver();
    }

    protected function waitForUserInput()
    {
        //put this message in stderr instead of stdout as phpunit TestCase class buffers stdout to enable asserts on it
        //but does not buffers stderr to enable asserts on it
        fwrite(STDERR, "press ENTER to continue execution...\n");
        if (trim(fgets(fopen("php://stdin", "r"))) != chr(13)) return;
    }

    protected function waitForElementById($id)
    {
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($id))
        );
        $elt = $this->webDriver->findElement(WebDriverBy::id($id));
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOf($elt)
        );
        return $elt;
    }

    protected function waitForXpathElt($xpathElt)
    {
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath($xpathElt))
        );
        $elt = $this->webDriver->findElement(WebDriverBy::xpath($xpathElt));
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOf($elt)
        );
        return $elt;
    }

    protected function waitForXpathElts($xpathElts)
    {
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath($xpathElts))
        );
        $elts = $this->webDriver->findElements(WebDriverBy::xpath($xpathElts));
        foreach ($elts as $elt) {
            $this->webDriver->wait()->until(
                WebDriverExpectedCondition::visibilityOf($elt)
            );
        }
        return $elts;
    }

    protected function getElement($fixtures) : RemoteWebElement
    {
        return $this->waitForXpathElt($fixtures['xpath_to_get']);
    }

    protected function getElements($fixtures) : array
    {
        return $this->waitForXpathElts($fixtures['xpath_to_get']);
    }

    protected function makeClick($fixtures)
    {

        if (!empty($fixtures['double_wait'])) {
            try {
                $elt = $this->waitForXpathElt($fixtures['xpath_to_click']);
            } catch (\Throwable $e) {
                $elt = $this->waitForXpathElt($fixtures['xpath_to_click']);
            }
        } else {
            $elt = $this->waitForXpathElt($fixtures['xpath_to_click']);
        }
        if (!empty($fixtures['move_to_click'])) {
            $action = $this->webDriver->action();
            $action->moveToElement($elt)->perform();
        }

        $this->processStepByStepMode($fixtures);
        //if this click opens a new window then one should add key "new_window" => true
        //to $fixtures if one wants the focus to be on the new window after the click
        $elt->click();

        return $this;
    }

    protected function gotoUrl($url)
    {
        try {
            $this->webDriver->get($url);
        } catch (WebDriverCurlException $e) {
            $this->webDriver->get($url);
        }
        return $this;
    }

    protected function pollCheckChoice($fixtures)
    {
        $check_completed = false;
        while (!$check_completed) {
            $this->wait(1);
            try {
                $this->waitForXpathElt($fixtures['xpath'])->click();
            } catch (NoSuchElementException $exception) {
                $check_completed = true;
            } catch (TimeoutException $exception) {
                $check_completed = true;

            } catch (StaleElementReferenceException $exception) {
                $check_completed = false;
            }
        }
        return $this;
    }

    protected function assertXpathInputValue($fixtures, $assertIsMandatory = true, $normaliseFunction = null)
    {
        if ($assertIsMandatory || !empty($fixtures['assert_xpath_input_value'])) {
            $inputElt = $this->waitForXpathElt($fixtures["assert_xpath_input_value"]["xpath"]);
            $expectedValue = (!empty($normaliseFunction)) ? $normaliseFunction($fixtures["assert_xpath_input_value"]['value']) : $fixtures["assert_xpath_input_value"]['value'];
            $assertedValue = (!empty($normaliseFunction)) ? $normaliseFunction($inputElt->getAttribute('value')) : $inputElt->getAttribute('value');
            $this->assertEquals($expectedValue, $assertedValue,
                "Input field value $assertedValue is not equal to expected value $expectedValue");
        }
    }


    protected function xpathInputValue(&$fixtures, $fillIsMandatory = true, $normaliseFunction = null)
    {
        if ($fillIsMandatory || !empty($fixtures['xpath_input_value'])) {
            $inputElt = $this->waitForXpathElt($fixtures["xpath_input_value"]["xpath"]);
            if (!empty($fixtures["xpath_input_value"]['value'])) {
                $inputElt->clear();
                $toFillValue = (!empty($normaliseFunction)) ? $normaliseFunction($fixtures["xpath_input_value"]['value']) : $fixtures["xpath_input_value"]['value'];
                $inputElt->sendKeys($toFillValue ); //. WebDriverKeys::RETURN_KEY);
                $fixtures["xpath_input_value"]['calculated_value'] = $toFillValue;
            }
            if (!empty($fixtures["xpath_input_value"]['check'])) {
                $inputElt->click();
                if (!empty($fixtures["xpath_input_value"]['poll'])) {
                    $this->pollCheckChoice($fixtures["xpath_input_value"]);
                }
            }
            if (!empty($fixtures["xpath_input_value"]['enter'])) {
                $this->webDriver->getKeyboard()->sendKeys(WebDriverKeys::RETURN_KEY);
            }
        } else {
            return null;
        }
    }

    protected function xpathInputValues($fixtures, $fillIsMandatory = true)
    {
        if ($fillIsMandatory || !empty($fixtures['xpath_input_values'])) {
            foreach ($fixtures['xpath_input_values'] as $inputValue) {
                $fixture = ['xpath_input_value' => $inputValue];
                $this->xpathInputValue($fixture);
            }
        }

    }

    public function processStepByStepMode($page)
    {
        if (array_key_exists("step_by_step", $page) && $page["step_by_step"] == true) {
            $this->waitForUserInput();
        } else {
            if (!array_key_exists("step_by_step", $page) &&
                array_key_exists("step_by_step", $this->fixtures) &&
                $this->fixtures["step_by_step"] == true) {
                $this->waitForUserInput();
            }
        }
    }

    protected function assertPageTitle($fixtures, $assertTitleIsMandatory = true)
    {
        if ($assertTitleIsMandatory || !empty($fixtures['titleContains'])) {
            $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));
        }
    }

    protected function loadFixtures(string $testName, string $testMethod, $testDataSet)
    {
        $scValuesTpl = TestFixtures::getContent("$testName/scenario_values_template.ent");
        $scDatasetIn = TestFixtures::getJson("$testName/scenario_dataset_in_$testDataSet.json");
        foreach ($scDatasetIn as $scData) {
            $scDataEntities = $scValuesTpl;
            foreach ($scData as $scDataField => $scDataFieldValue) {
                $scDataEntities = str_replace("#$scDataField#", "$scDataFieldValue", $scDataEntities);
            }
            file_put_contents(
                TestFixtures::getFilePath("$testName/scenario_values.ent"),
                $scDataEntities
            );
            $testConfig = TestFixtures::get("$testName.btf");
            if (empty($this->fixtures)) {
                $this->fixtures = $testConfig;
                if (!empty($this->fixtures['dsn'])) {
                    foreach ($this->fixtures['dsn'] as $dbCon => $dsn) {
                        $this->fixtures['dbcon'][$dbCon] = $this->dbCon($dsn);
                    }
                }
            }
            if (empty($this->fixtures['dataset_in'][$testMethod])) {
                $this->fixtures['dataset_in'][$testMethod] = $scDatasetIn;
            }
            foreach ($this->getScenarioKeys($testConfig) as $scenarioKey) {
                $keyTestMethod = $this->getTestMethodFromScenarioKey($scenarioKey);
                if ($testMethod == $keyTestMethod) {
                    $this->fixtures["scenarios"][$testMethod][] = $testConfig[$scenarioKey];
                }
            }
        }
    }

    protected function saveDataSetOut(string $testName, $testDataSetId, $dataSet)
    {
        file_put_contents(
            TestFixtures::getFilePath("$testName/scenario_dataset_out_$testDataSetId.json"),
            json_encode($dataSet)
        );

    }

    protected function loadDataSetOut(&$fixtures, string $testName, $testDataSetId): void
    {
        $fixtures['dataset_out'][$testDataSetId] = json_decode(
            file_get_contents(
                TestFixtures::getFilePath("$testName/scenario_dataset_out_$testDataSetId.json"),
            ),
            true);

    }

    protected function getScenarioKeys(array $testConfig): ?array
    {
        $scenarioKeys = array();
        foreach (array_keys($testConfig) as $key) {
            if (preg_match('/^scenario_.*/', $key)) $scenarioKeys[] = $key;
        }
        return $scenarioKeys;
    }

    protected function getTestMethodFromScenarioKey(?string $scenarioKey): ?string
    {
        if (empty($scenarioKey)) return null;
        if (preg_match('/^scenario_.*/', $scenarioKey)) {
            return preg_replace('/^scenario_/', '', $scenarioKey);
        } else {
            return null;
        }
    }

    protected function &initDataSetOut(array &$fixtures, string $testMethod): array
    {
        $dataSetOut = [
            $testMethod => []
        ];
        $fixtures['dataset_out'] = $dataSetOut;
        return $fixtures['dataset_out'][$testMethod];
    }

    protected function wait($numberOfSeconds)
    {
        sleep($numberOfSeconds);
        return $this;
    }

    protected function page_nav($fixtures, $assertTitleIsMandatory = false, $assertInputIsMandatory = false, $normaliseInputValueFunction = null)
    {
        if ($this->skip($fixtures)) return $this;

        if (!empty($fixtures['url'])) {
            $this->gotoUrl($fixtures['url']);
            $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));
        } else {
            $this->assertPageTitle($fixtures, $assertTitleIsMandatory);
        }

        $this->assertXpathInputValue($fixtures, $assertInputIsMandatory, $normaliseInputValueFunction);

        if (!empty($fixtures['xpath_of_href'])) {
            //$attempts = 0;
            //while ($attempts < 10) {
            $elt = $this->waitForXpathElt($fixtures['xpath_of_href']);
            $href=null;
            try {
                $href = $elt->getAttribute('href');
                } catch (StaleElementReferenceException $e) {
                }
            $this->gotoUrl($href);

       } else {
            $this->makeClick($fixtures);
        }

        return $this;
    }

    protected function page_fill(&$fixtures, $assertTitleIsMandatory = false, $fillInputIsMandatory = true, $normaliseInputValueFunction = null)
    {
        if ($this->skip($fixtures)) return $this;

        if (!empty($fixtures['url'])) {
            $this->gotoUrl($fixtures['url']);
            $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));
        } else {
            $this->assertPageTitle($fixtures, $assertTitleIsMandatory);
        }

        if (!empty($fixtures['xpath_input_value'])) {
            $this->xpathInputValue($fixtures, $fillInputIsMandatory, $normaliseInputValueFunction);
        }

        if (!empty($fixtures['xpath_input_values'])) {
            $this->xpathInputValues($fixtures, $fillInputIsMandatory);
        }

        if (!empty($fixtures['xpath_to_click'])) {
            $this->makeClick($fixtures);
        }

        return $this;
    }

    protected function page_wait($fixtures)
    {
        if ($this->skip($fixtures))  return $this;

        $this->wait($fixtures['number_of_seconds']);
        return $this;
    }

    protected function skip($fixtures)
    {
        if (!empty($fixtures['skip']) && $fixtures['skip'] === true)
            return true;
        else
            return false;
    }

    protected function dbCon($dsn)
    {
        return new PDO($dsn);
    }

    protected function getDbCon($fixtures, $country) : ?PDO
    {
        return $fixtures['dbcon']["dbcon_mpz_$country"];
    }
}
