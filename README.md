
**NB :** 
- this README is not up-to-date with the current releases of the integrated components (php webdriver, selinium web driver, and native browser webdrivers);
- it will be fixed in the near future.

# Understanding the basic concepts of this "web driver based" testing framework

- "web driver" is a standardized API supported by unit testing solutions and browser solutions of the market;
- this API provides "verbs" to "drive" a browser, that is making the same actions as human would do with the browser, but the human is replaced by an automated test script;
- so the automated test script integrates this API to do so;
- the implementation of this api is called a "web driver implementaion", and it is different for the different browsers of the market as it integrates the browsers native APIs to execute the web driver api's standard verbs/actions (think of it like the DOM interface and an implementation of it in the different browsers);
- below is described how to setup "firefox web driver" and "chrome web driver";
- another feature of "web driver" testing frameworks, is to support different programming language for the automated test script : php here;
- yet another feature of those frameworks, it to support parallel executions of the automated test script on several browsers of different client machines on the network;
- in the end the component stack of such a framework is the following :
- a standard testing framework in the target language : phpunit;
- an implementation of the "web driver" API compliant with this framework : Facebook's "php web driver";
- a server receiving the API calls from this driver, to transmit them to the "browser native" drivers of the target browsers of the test : selenium server;
- the "browser native" drivers executed by this server to interact with the target browser : geckodriver for firefox, chromedriver for chrome.

# To deploy this testing framework

- this section describes a local deploy, that is, all components are executed by the computer running the browser (other deploy configuration may be worked out and be added if needed, check the docs in the "Reference docs" section below);
- clone this repo and run "composer install" : this deploys (in vendor) : the php web driver package, phpunit, and selenimu server;
- NB : the current java stable release has to be deployed as selenium server is a java server;
- the last (but not least) component(s) to deploy is(are) the browser(s) native driver(s) : this is described below;

## To deploy firefox driver (aka geckodriver)

- ... if firefox is not installed, just do it;
- if you have installed a "recent" firefox release (let's say around or above version 111), there are chances geckodriver is installed by default :
- check it by running :
```
  > geckodriver --version
  geckodriver 0.32.2 ( 2023-03-10)
  ...
```
- if it is not already installed then :
- install/upgrade geckodriver for linux arm 64 bit processors: get it from https://launchpad.net/ubuntu/bionic/arm64/firefox-geckodriver;
- or install/upgrade geckodriver for other available os/processors: get it from https://github.com/mozilla/geckodriver/releases;
- grab the last stable release for your os and check compliance with your firefox browser release otherwise upgrade or downgrade your browser or your driver;
- install it in your os;
- test the install by checking the result of "geckodriver --help";
- the webdriver server can then be run from selenium repo root, with the following command :

```
java -Dwebdriver.gecko.driver=./geckodriver.sh -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar standalone
```

- check for any error returned by this server in its console;

## To deploy chrome driver

(NB : this has not been tested yet, you are welcome !)

- install/upgrade chromedriver for linux arm 64 bit processors: get it from https://launchpad.net/ubuntu/bionic/arm64/chromium-chromedriver;
- install/upgrade chromedriver for other available os/processors: get it from https://github.com/mozilla/geckodriver/releases/download;
- grab the last stable release for your os and check compliance with your firefox browser release otherwise upgrade or downgrade your browser or you driver;
- install it in your os;
- test the install by checking the result of "chromedriver --help";
- the webdriver server can then be run from selenium repo root, with the following command :

```
java -Dwebdriver.chrome.driver=./chromedriver.sh -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar standalone
```

- check for any error returned by this server in its console;

# To code and run phpunit+webdriver test scripts

- browse existing TU (by default tests/BankFileTransferTest.php : it creates pending transfer requests for any country from a json file containing the datas (member login,password, transfer request amount ...), clic the btn to generate the bank file (per country), and check that created pending transfer requests are in the proper bank file with the proper amount;
- check the (class level) comment of this class telling how to run it, yet this requires today a local access to /home/shared and to the member anonymization (yet adding remote usage of these resources is possible);
- one can browse also MpzSubscriptionScenarioTest.php : it tests the basic life cycle of an mpz member : subscription / unsubscription / anonymization;
- one can browse also SubscriptionScenarioTest.php, which is obsolete now, but covers a complete (yet now obsolete) UGC subscription scenario : it contains an old example of js code execution (for a particular visual component automation), an example of file download un upload;
- note the usage of the step_by_step switch enabling a step by step execution of a scenario, for example : to grab the xpath of the dom element to be then used in the TU (with firefox : right-clic the element / "Inspect Element" / Copy / Xpath); (step_by_step mode can be switched for the whole scenario or just some steps);
- note also the possible usage of a skip switch, to skip part(s)) of a scenario and/or datas of the fixtures datasets : this is convenient for example to catch up a fialed test from a givent step;
- NB : the TU can also be debugged (or run) in an IDE (phpstorm tested ok) : in such a case it is strongly recommended not to debug/run the TU with in step by step mode (= set to true in the conf) : it is bug prone and not really useful as the debug mode already supports step by step executions on its own;
- NB 2 : the TU can be run headless (= without the browser window displayed), enabling a batch execution of it : this is achieved by adding the proper browser launch argument (-headless for firefox and chrome) in default.btc, the same way the profile argument is added (this has not been tested yet, you are welcome);
- NB 3 : as the browser response time depends on the network trafic etc... a default time out of the actions requested from the browsers is set to 30s, but this can be tuned for each request (check the existing code/docs);
- NB 4 : other candidate solutions are proposed on the web to code and run web driver tests : they integrate value added third parties like IDE that generate the code of the test script etc... but the study made at design phase is that : first : they don't support the full coding flexibility / advanced capabilities a pure phpunit solution supports, and second : the code generated to access the page elements actions have to be executed on, is less stable across page evolutions than the same code implemented php code in a phpunit TU (enabling more flexible implementation strategies depending on each case) : in the end this pure phpunit solution has been estimated globally better to manage in the long run, a large TU code base that covers a large application platform (already using phpunit for code level tests);
- one can browse also this post : https://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html;
- one can browse also this demo : https://github.com/DavertMik/php-webdriver-demo/blob/master/tests/GitHubTest.php;
- one can browse also these api usage examples : https://github.com/php-webdriver/php-webdriver/wiki/Example-command-reference;

## Running a test script

- it is run as standard phpunit TU (with the desired phpunit options) : vendor/phpunit/phpunit/phpunit --verbose tests/BankFileTransferTest.php
- the selenium server has to be launched first (see above).

# Reference docs

https://github.com/php-webdriver/php-webdriver

https://www.selenium.dev/documentation/webdriver

https://firefox-source-docs.mozilla.org/testing/marionette/index.html

https://sites.google.com/a/chromium.org/chromedriver/capabilities

