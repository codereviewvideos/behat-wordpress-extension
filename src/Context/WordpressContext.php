<?php
namespace PaulGibbs\WordpressBehatExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementTextException;

/**
 * Provides step definitions for a range of common tasks. Recommended for all test suites.
 */
class WordpressContext extends RawWordpressContext
{
    use PageObjectContextTrait;

    /**
     * Clear object cache.
     *
     * @AfterScenario
     */
    public function clearCache()
    {
        parent::clearCache();
    }

    /**
     * Clear Mink's browser environment.
     *
     * @AfterScenario
     */
    public function resetBrowser()
    {
        parent::resetBrowser();
    }

    /**
     * When using the Selenium driver, position the admin bar to the top of the page, not fixed to the screen.
     * Otherwise the admin toolbar can hide clickable elements.
     *
     * @BeforeStep
     */
    public function fixToolbar()
    {
        $driver = $this->getSession()->getDriver();
        if (! $driver instanceof Selenium2Driver || ! $driver->getWebDriverSession()) {
            return;
        }

        try {
            $this->getSession()->getDriver()->executeScript(
                'if (document.getElementById("wpadminbar")) {
                    document.getElementById("wpadminbar").style.position="absolute";
                    if (document.getElementsByTagName("body")[0].className.match(/wp-admin/)) {
                        document.getElementById("wpadminbar").style.top="-32px";
                    }
                };'
            );
        } catch (\Exception $e) {
            /*
             * If a browser is not open, then Selenium2Driver::executeScript() will throw an exception.
             * In this case, our toolbar workaround obviously isn't required, so fail quietly.
             */
        }
    }

    /**
     * Open the dashboard.
     *
     * Example: Given I am on the dashboard
     * Example: Given I am in wp-admin
     * Example: When I go to the dashboard
     * Example: When I go to wp-admin
     *
     * @Given /^(?:I am|they are) on the dashboard/
     * @Given /^(?:I am|they are) in wp-admin/
     * @When /^(?:I|they) go to the dashboard/
     * @When /^(?:I|they) go to wp-admin/
     */
    public function iAmOnDashboard()
    {
        $this->visitPath('wp-admin/');
    }

    /**
     * Searches for a term using the toolbar search field
     *
     * Example: When I search for "Hello World" in the toolbar
     *
     * @When I search for :search in the toolbar
     *
     * @param $search
     */
    public function iSearchUsingTheToolbar($search)
    {
        $this->getElement('Toolbar')->search($search);
    }

    /**
     * Clicks the specified link in the toolbar.
     *
     * Example: Then I should see "Howdy, admin" in the toolbar
     *
     * @Then I should see :text in the toolbar
     *
     * @param string $text
     *
     * @throws ElementTextException
     */
    public function iShouldSeeTextInToolbar($text)
    {
        $toolbar = $this->getElement('Toolbar');
        $actual = $toolbar->getText();
        $regex = '/' . preg_quote($text, '/') . '/ui';

        if (! preg_match($regex, $actual)) {
            $message = sprintf('The text "%s" was not found in the toolbar', $text);
            throw new ElementTextException($message, $this->getSession()->getDriver(), $toolbar);
        }
    }

    /**
     * Clicks the specified link in the toolbar.
     *
     * Example: When I follow the toolbar link "New > Page"
     * Example: When I follow the toolbar link "Updates"
     * Example: When I follow the toolbar link "Howdy, admin > Edit My Profile"
     *
     * @When I follow the toolbar link :link
     *
     * @param string $link
     */
    public function iFollowTheToolbarLink($link)
    {
        $this->getElement('Toolbar')->clickToolbarLink($link);
    }
}
