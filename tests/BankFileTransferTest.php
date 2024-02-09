<?php

use Facebook\WebDriver\Remote\RemoteWebElement;
use TestCommon\BrowserTest;
use Facebook\WebDriver\WebDriverExpectedCondition;
use TestCommon\DocumentProcessor\DocumentProcessor;

/**
 * check the README for a basic description of such a selenium test class principles;
 * selenium test classes main purpose is to automate the tests of business level scenarios rather than tests of code;
 * for this reason, they are, by design, some kind of phpunit "workflows" :
 * - they are standard phpunit classes as they extend the TestCase phpunit class (thus making assert etc...);
 * - but they do not execute as the default phpunit execution paradigm : every test method should be
 * "execution independent" and played in any orders, with start/end methods (setUp/tearDown etc...) ensuring the proper
 * fixtures for any method whatever its execution order etc...;
 * - they heavily use the phpunit @depend to design test methods execution sequences : test methods are the execution
 * steps of the workflow;
 * - every test method has its own (business process engines like), fixtures management : it (optionally) loads "in datasets"
 * (created by hand, and/or data generation code, and/or preceding methods, and/or a mix of them), to execute its logic,
 * and (optionally) creates "out datasets" (for next methods executions);
 * the workflow of this BankFileTransferTest class is :
 * - testDefaultPendingTransferRequestCreationScenario() creates pending transfer requests from the members datas,
 * of any countries, loaded from its "in" dataset;
 * - testDefaultBankfileGenerationScenario() depends on testDefaultPendingTransferRequestCreationScenario(), generates
 * the bank files of today's term (for any country site), and outputs the generated bank files infos in its out dataset;
 * - testGeneratedXmlBankFile() depends on testDefaultBankfileGenerationScenario(), loads testDefaultBankfileGenerationScenario()'s
 * out dataset, and testDefaultPendingTransferRequestCreationScenario()'s in dataset, and :
 * - loads from this out dataset, the payment datas found in the bank files (per country),
 * - then loads from this in dataset, the expected payment datas for these countries,
 * - and finally checks, for each expected payment data of each expected country, that this payment data is found in the proper file.
 * here the required manual test setup actions to be done before launching this test:
 * - the in dataset of testDefaultPendingTransferRequestCreationScenario() contains member datas of the dev database valid
 * for the creation of a pending transfer request : if one wants to add to them (or replace them with), other datas, then one
 * has to ensure they are valid for the creation of a pending transfer request : active member, included in a lottery,
 * with more than 15 points, not having an existing pending transfer request, and not blacklisted and bank info blacklisted;
 * - NB : amounts in this in dataset should not have decimals (15.00 or 9253.00 is okay but 15.45 may not work),
 * see method checkValidAmountValuesToTest for more info about it;
 * - in case a previous execution of this test has created datas for the same payment term period :
 * move the date columns of the rows created in the following tables, to dates in the past not overlapping with this
 * payment term period : meinungsplatz.bank_file_execution_request, meinungsplatz.cash_payment_term;
 * - recommended : reset the app:payment:file-execution-request crons before running this test : comment them in the crontab,
 * then run them by hand with the removelock option, then uncomment them in the crontab and check in the log that they
 * don't make any errors ("tail -f the_log_file" is your friendly command for that);
 * - in case of failure of a previous execution, and one would like to catch up this execution without rerunning all the steps,
 * of the scenarios, and/or without rerunning the steps for all the datas of the in datasets, then one can also add
 * "skip":true, to the scenarios steps in the json scenarios, and/or to the datas in the "dataset in" json files.
 */
class BankFileTransferTest extends BrowserTest
{
    use MpzSharedScenario\MpzSharedScenarioTrait;

    protected $fixtures;

    protected $testStates = array();

    public function setUp(): void
    {
        $this->setDefaultWebDriver();
        //loads features and set testDefaultPendingTransferRequestCreationScenario as the next test method
        $this->loadFixtures('BankFileTransferTest',
            'testDefaultPendingTransferRequestCreationScenario',
            'testDefaultPendingTransferRequestCreationScenario');
    }

    public function tearDown(): void
    {
        if (!empty($this->webDriver))
            $this->webDriver->close();
    }


    public function testDefaultPendingTransferRequestCreationScenario()
    {
        $dataSetInIndex = 0;
        $dataSetIn = $this->fixtures['dataset_in']['testDefaultPendingTransferRequestCreationScenario'];
        foreach ($this->fixtures["scenarios"]['testDefaultPendingTransferRequestCreationScenario'] as $scenario) {
            if (empty($dataSetIn[$dataSetInIndex]['skip'])) {
                $this->page_login($scenario['page_login'])
                    ->page_nav($scenario['page_dashboard'])
                    ->page_nav($scenario['page_bank_details'])
                    ->page_request_transfer($scenario['page_request_transfer'])
                    ->page_nav($scenario['page_bank_details_check'], false, false, $this->normalizeCurrencyDecimal)
                    ->page_nav($scenario['page_logout']);
            }
            $dataSetInIndex++;
        }
    }



    private function page_request_transfer($fixtures)
    {
        if ($this->skip($fixtures))  return $this;

        // transfer_request_amount
        $elt = $this->waitForElementById('transfer_request_amount');
        $elt->clear();
        $elt->sendKeys($fixtures['transfer_request_amount']);

        $this->makeClick($fixtures);

        return $this;
    }


    /**
     * NB : @ has been removed from the depends above as the current version of the tested scenario does not support it yet;
     * please read more about it above in the class comment.
     * @depends testDefaultPendingTransferRequestCreationScenario
     * @return $this
     */
    public function testDefaultBankfileGenerationScenario()
    {
        $this->loadFixtures('BankFileTransferTest',
            'testDefaultBankfileGenerationScenario',
            'testDefaultBankfileGenerationScenario');
        $dataSetIn = $this->fixtures['dataset_in']['testDefaultBankfileGenerationScenario'];
        $dataSetOut = &$this->initDataSetOut($this->fixtures,'testDefaultBankfileGenerationScenario');
        $this->saveDataSetOut('BankFileTransferTest','testDefaultBankfileGenerationScenario', $dataSetOut);
        $dataSetInIndex = 0;
        foreach ($this->fixtures["scenarios"]['testDefaultBankfileGenerationScenario'] as $scenario) {
            $dataIn = $dataSetIn[$dataSetInIndex];
            if (empty($dataIn['skip'])) {
                $this->page_login($scenario['page_bo_login'])
                    ->page_nav($scenario['page_admin_menu'])
                    ->page_nav($scenario['page_blacklist_bank_info'])
                    ->wait(15)
                    ->page_nav($scenario['page_slider_panel'])
                    ->page_nav($scenario['page_slider_panel_generate_bankfiles'])
                    ->page_wait($scenario['page_slider_panel_wait_for_bankfiles'])
                    ->page_slider_panel_get_generated_bankfiles_infos($scenario, $dataIn, $dataSetOut)
                    ->page_nav($scenario['page_logout']);
            }
            $dataSetInIndex++;
        }
        $this->saveDataSetOut('BankFileTransferTest','testDefaultBankfileGenerationScenario', $dataSetOut);
        return $this;
    }

    private function page_slider_panel_get_generated_bankfiles_infos($scenario,$dataIn, &$dataSetOut)
    {
        if ($this->skip($scenario['page_slider_panel_get_generated_bankfiles_infos']))  return $this;

        $this->webDriver->navigate()->refresh();
        $this->page_nav($scenario['page_slider_panel']);
        $this->wait(5);
        /**
         * @var $elements RemoteWebElement[]
         */
        $elements = $this->getElements($scenario['page_slider_panel_get_generated_bankfiles_infos']);
        /**
         * @var $dbCon PDO
         */
        $dbCon = $this->getDbCon($this->fixtures, $dataIn['country']);
        foreach ($elements as $element) {
            $fileName = $element->getText();
            $fileFormat = !empty(strstr($fileName, 'SEPA')) ? 'SEPA' : (!empty(strstr($fileName, 'SPS')) ? 'SPS' : '');
            $currency = ($fileFormat=='SPS')? 'CHF' : 'EUR';
            $currencyRates = $dbCon->query("select currency_exchange_rates from meinungsplatz.bank_file bf
join meinungsplatz.cash_payment_term using (id_cash_payment_term)
where file_name = '{$element->getText()}'
order by created_at desc
limit 1; ")->fetch()['currency_exchange_rates'];
            $currencyRates = json_decode($currencyRates,true);
            $currencyRate = array_values($currencyRates)[0][$currency];
                $dataSetOut[] = array(
                "bank_file" => $fileName,
                "bank_file_format" => $fileFormat,
                "currency_rate" => $currencyRate,
                "country" => $dataIn['country'],
                "bank_file_to_send_dir" => $scenario['page_slider_panel_get_generated_bankfiles_infos']['bank_file_to_send_dir'],
                "bank_file_sent_dir" => $scenario['page_slider_panel_get_generated_bankfiles_infos']['bank_file_sent_dir']
            );
        }
        return $this;
    }


    /**
     * @depends testDefaultBankfileGenerationScenario
     * @return $this
     */
    public function testGeneratedXmlBankFile()
    {

        //mock code to avoid depending on testDefaultBankfileGenerationScenario (so remove the @ of @depends when
        //uncommenting this mock code)
        //on has also to check and adapt if needed the datas of the two mock datasets ou and in below
        //according to the test result one expects

/*        $this->fixtures['dataset_out']['testDefaultBankfileGenerationScenario'][] = array(
            "bank_file" => 'MPC_2020-07-24_653.SPS.xml',
            "bank_file_format" => 'SPS',
            "country" => 'ch',
            //this should be the path to the file in bank_file entry above
            "bank_file_to_send_dir" => '/home/parallels/dev/apps/mpz/vendor/bilendi/banking-iso20022-bundle/tests/DataFixtures/Service/DocumentProcessor',
            "bank_file_sent_dir" => '/foo'
        );
        // to make the test pass put below the same iban, bic and credit_amount as in the file in the bank_file entry just above
        // to make the test fail put different values
        $this->fixtures['dataset_in']['testDefaultPendingTransferRequestCreationScenario'] = array([
            "url_prefix" => "&url_prefix_fo_ch;",
            "country" => 'ch',
            "idmembre" => "110133574726",
            "member_login" => "f.delahaye+mpz@bilendi.com",
            "iban" => "CH1700235235477364M1C",
            "bic" => "POFICHBEXXX",
            "credit_amount" => "15",
            "credit_amount_with_decimal" => "15.00",
            "bank_file_format" => 'SPS'
        ]);*/
        //end mock code


        $this->loadDataSetOut($this->fixtures,'BankFileTransferTest','testDefaultBankfileGenerationScenario');
        $arraysToCheck = [];
        foreach ($this->fixtures['dataset_out']['testDefaultBankfileGenerationScenario'] as $scenarioDataOut) {
            $bankFileExistsInToSendDir = file_exists("{$scenarioDataOut["bank_file_to_send_dir"]}/{$scenarioDataOut["bank_file"]}");
            $bankFileExistsInSentDir = file_exists("{$scenarioDataOut["bank_file_sent_dir"]}/{$scenarioDataOut["bank_file"]}");
            $this->assertTrue($bankFileExistsInToSendDir || $bankFileExistsInSentDir, "file {$scenarioDataOut["bank_file"]} found neither in {$scenarioDataOut["bank_file_to_send_dir"]}\nnor in {$scenarioDataOut["bank_file_sent_dir"]}");
            $xmlFile = ($bankFileExistsInToSendDir) ? "{$scenarioDataOut["bank_file_to_send_dir"]}/{$scenarioDataOut["bank_file"]}" : "{$scenarioDataOut["bank_file_sent_dir"]}/{$scenarioDataOut["bank_file"]}";
            $processor = new DocumentProcessor();
            $xsltFile = 'ISO20022/getCreditDatas.xsl';
            $object = new \ArrayObject(array());
            $processor->processDocumentWithObject($xmlFile, $xsltFile, $object, false, 'debug');
            $currencyRate = $scenarioDataOut["currency_rate"];
            $bankFileFormat = $scenarioDataOut["bank_file_format"];
            $country = $scenarioDataOut['country'];
            $newArray = array_filter(array_map(
                function ($value)  use (&$currencyRate, &$bankFileFormat) {
                    $amount = intval(floor(floatval($value['credit_amount'])/$currencyRate));
                    return "{$value['iban']}/{$value['bic']}/$amount/$bankFileFormat";
                },
                $object->getArrayCopy()
            ));
            $arraysToCheck[$country] = (!isset($arraysToCheck[$country])) ? $newArray : array_merge($arraysToCheck[$country],$newArray);
        }
        $expectedArrays = [];
        array_map(
            function ($value) use (&$expectedArrays) {
                $expectedArrays[$value['country']][] = "{$value['iban']}/{$value['bic']}/{$value['credit_amount']}/{$value['bank_file_format']}";
            },
            $this->fixtures['dataset_in']['testDefaultPendingTransferRequestCreationScenario']
        );
        foreach ($arraysToCheck as $country => $arrayToCheck) {
            $expectedArray = $expectedArrays[$country];
            $intersection = array_intersect($expectedArray, $arrayToCheck);
            $this->assertEqualsCanonicalizing($expectedArray, $intersection,
                "iban/bic/credit_amount assert fail : the intersection between the expected result and the result
                should be equal to the expected result, but this intersection is :
                " . var_export($intersection, true) . "
                whereas the expected result is :
                " . var_export($expectedArray, true));
            $arrayToCheckCount = count($arrayToCheck);
            sort($arrayToCheck);
            sort($intersection);
            $arrayToCheckWithoutIntersectCount = count(
                array_udiff($arrayToCheck,
                    $intersection,
                    function ($a, $b) {
                        if ($a == $b) return 0; else return -1;
                    })
            );
            $expectedArrayCount = count($expectedArray);
            sort($expectedArray);
            $expectedArrayWithoutIntersectCount = count(
                array_udiff($expectedArray,
                    $intersection,
                    function ($a, $b) {
                        if ($a == $b) return 0; else return -1;
                    })
            );
            $this->assertEquals(
                $expectedArrayCount - $expectedArrayWithoutIntersectCount,
                $arrayToCheckCount - $arrayToCheckWithoutIntersectCount,
                "iban/bic/credit_amount count assert fail : the count of common lines between the expected result and the result
                should be equal, but this is not the case : the expected result is :
                " . var_export($expectedArray, true) . "
                whereas the result is :
                " . var_export($arrayToCheck, true));
            unset($expectedArrays[$country]);
        }
        $this->assertEmpty($expectedArrays,"the expected credits of this/these country site(s) have not been found in any generated bank files :
        " . var_export(array_keys($expectedArrays), true)
        );
        return $this;
    }

    /**
     * This methods explains the constraint to choose proper amount values for the test,
     * run my code with the php CLI or in a scratch pad like http://phptester.net
     */
    private function checkValidAmountValuesToTest() {
        //This test show that one cannot test amount with decimal values because of precision issues
//so testing integer amount like 15.00 is okay yet testing 14.45 may not work depending on the rate used
//nb : testing bigger values like 9253.00 seems less "rate variation sensitive" than lower value like 15.00
//$chAmount = 9253.00;
        $chAmount = 15.00;
//change the rate to check rates returning $reversedChAmountWithSixDecimal lower than the expected value $chAmount value
//non realistic big value
//$euroRate = 999.693;
//non realistic low value
//$euroRate = 0.003;
//realistic value
        $euroRate = 0.969;
//one can also try PHP_ROUND_HALF_DOWN with round default is PHP_ROUND_HALF_UP)
        $euroAmount = round($chAmount * $euroRate,2);
        $reversedChAmountWithSixDecimal = round($euroAmount / $euroRate,6);
        $reversedChAmountWithMoneyDecimal = round($euroAmount / $euroRate,2);
        $reversedChAmountWithoutMoneyDecimal = round($euroAmount / $euroRate);
        echo "inputed amount (chAmount) : $chAmount\n";
        echo "CHF to EURO rate : $euroRate\n";
        echo "euroAmount : $euroAmount\n";
        echo "reversedChAmountWithSixDecimal : $reversedChAmountWithSixDecimal\n";
//this shows that one does not get 15.00 back but 15.01 so check wof amounts with 2 decimals (so money deciaml is not reliable)
        echo "reversedChAmountWithMoneyDecimal : $reversedChAmountWithMoneyDecimal\n";
//this shows that testing only amounts without decimals like 15.00 in the test input
//should always work as it always resolves to this inputed amount
        echo "reversedChAmountWithoutMoneyDecimal : $reversedChAmountWithoutMoneyDecimal\n";

    }

    public function testVerbolia()
    {
        $xmlFile = '/home/franck/dev/browser_test/tmp/test1.xml';
        $xsltFile = '/home/franck/dev/browser_test/tmp/verboliaProcessing.xsl';
        $xmlResultFile = '/home/franck/dev/browser_test/tmp/test1_out.xml';
        $processor = new DocumentProcessor();
        $categories = new \ArrayObject(array());
/*        $categories['a7b317be-e86f-4524-bbab-f8ec9582110b']['es'] = 'Tipo de vehiculo';
        $categories['a7b317be-e86f-4524-bbab-f8ec9582110b']['ca'] = 'Tipa de vehicula';
        $categories['a7b317be-e86f-4524-bbab-f8ec9582110c']['es'] = 'Otro Tipo de vehiculo';
        $categories['a7b317be-e86f-4524-bbab-f8ec9582110c']['ca'] = 'Otra Tipa de vehicula';*/

        $categories['es'] = 'Tipo de vehiculo';
        $categories['ca'] = 'Tipa de vehicula';
        /*
         $featureTraductions['Tipo de vehiculo'] = 'category';
         $featureTraductions['Idiomes'] = 'lang';
         $featureTraductions['Tipa de vehicula'] = 'category';
         $featureTraductions['Idiomas'] = 'lang';
         */
        $processor->processDocumentWithObject($xmlFile, $xsltFile, $categories, false, 'debug', $xmlResultFile);

    }
}
