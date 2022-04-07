<?php

namespace MpzSharedScenario;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverExpectedCondition;

trait MpzSharedScenarioTrait
{

    private function page_login($fixtures)
    {
        if ($this->skip($fixtures))  return $this;

        $this->webDriver->get($fixtures['url']);
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleContains($fixtures['titleContains']));

        $elt = $this->waitForElementById('username');
        $elt->sendKeys($fixtures['username']);

        $elt = $this->waitForElementById('password');
        $elt->sendKeys($fixtures['password']);

        $this->makeClick($fixtures);

        return $this;
    }

    /**
     * hides the debug bar if present in the page (to avoid hiding elements one wants to act on).
     */
    private function page_debug_bar($fixtures)
    {
        if ($this->skip($fixtures))  return $this;

        try {
            $this->waitForXpathElt("//*[contains(@id,'sfToolbarHideButton')]")->click();
        } catch (NoSuchElementException $exception) {

        } catch (TimeoutException $exception) {

        } catch (StaleElementReferenceException $exception) {

        }
        return $this;

    }

    private function scenario_survey($fixtures)
    {

    }

}