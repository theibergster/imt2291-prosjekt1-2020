<?php 

class SigninCest {
    public function _before(AcceptanceTester $I) {
        $I->amOnPage('/');
    }
    
    // tests
    public function signinSuccess(AcceptanceTester $I) {
        $I->fillField('email', 'admin@admin.no');
        $I->fillField('pwd', 'admin');
        $I->click('login-submit');
        $I->see('Welcome Admin User');
    }
}
