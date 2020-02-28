<?php 

class SignupCest {
    public function _before(AcceptanceTester $I) {
        $I->amOnPage('/signup');

        $I->fillField('name', 'Test Test');
        $I->fillField('#email', 'test@test.test');
        $I->fillField('#pwd', 'test');
        $I->selectOption('user-type', 'student');
    }

    // tests
    public function signupSuccess(AcceptanceTester $I) {
        $I->fillField('pwd-repeat', 'test');
        $I->click('signup-submit');
        $I->see('Created user: test@test.test');
    }

    public function signupUserAlreadyExist(AcceptanceTester $I) {
        $I->fillField('pwd-repeat', 'test');
        $I->click('signup-submit');
        $I->see('Failed - User already exists');
    }

    public function signupEmptyFields(AcceptanceTester $I) {
        $I->click('signup-submit');
        $I->see('Failed - Empty fields');
    }

    public function SignupPasswordsDontMatch(AcceptanceTester $I) {
        $I->fillField('pwd-repeat', 'notTest');
        $I->click('signup-submit');
        $I->see('Failed - Passwords does not match');
    }
}
