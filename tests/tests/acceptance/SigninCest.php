<?php 

class SigninCest
{
    public function signinSuccess(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->fillField('email', 'admin@admin.no');
        $I->fillField('pwd', 'admin');
        $I->click('login-submit');
        $I->see('Welcome Admin User');
        
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }
}
