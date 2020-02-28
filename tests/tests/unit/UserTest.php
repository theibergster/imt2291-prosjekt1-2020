<?php 
require_once '../html/classes/DB.php';
require_once '../html/classes/User.php';

class UserTest extends \Codeception\Test\Unit {
    /**
     * @var \UnitTester
     */
    private $db;
    private $user;
    private $testUser;
    private $testId = 1;
    
    protected function _before() {
        $db = DB::getDBConnection();

        $this->testUser['name'] = substr(md5(rand()), 0, 10);
        $this->testUser['email'] = substr(md5(rand()), 0, 10) . '@test.no';
        $this->testUser['pwd'] = 'test';
        $this->testUser['type'] = 'student';

        $this->user = new User($db);
    }

    protected function _after() {}

    // tests
    public function testAddUser() {
        $result = $this->user->addUser($this->testUser);
        $this->assertStringContainsString('Created user', $result['status']);
        $userId = $result['id'];
        $this->assertTrue($userId > 0);
    }

    public function testCheckUserType() {
        $result = $this->user->checkUserType($this->testId);
        $this->assertEquals('admin', $result['type']);
    }

    public function testValidateUserSignup() {
        $result = $this->user->validateUserSignup($this->testUser['name'], $this->testUser['email'], $this->testUser['pwd'], $this->testUser['pwd']);
        $this->assertEquals('Success', $result['status']);
    }
}