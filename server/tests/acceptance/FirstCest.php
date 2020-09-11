<?php

class FirstCest
{
    public function installWorks(AcceptanceTester $I)
    {
        $I->haveServerParameter('HTTP_ACCEPT_LANGUAGE', 'en-US;q=0.5,en;q=0.3');
        $I->amOnPage('/install/install.php');
        $I->click('Submit');
        $I->seeCurrentURLEquals('/install/install.php');
        $I->fillField('dbhost', '127.0.0.1');
        $I->fillField('uname', 'root');
        $I->fillField('pwd', 'password');
        $I->click('Submit');
        
        $I->click('Populate DB');

        $I->fillField('user_name', 'admin');
        $I->fillField('admin_password', 'password');
        $I->checkOption('admin_visible');
        $I->fillField('admin_email', 'allerta@example.com');
        $I->fillField('owner', 'owner');
        $I->click('Install Allerta');
        $I->see('Login');
        $I->click('Login');
        $I->fillField('name', 'admin');
        $I->fillField('password', 'password');
        $I->click('Login');
        $I->seeCurrentURLEquals('/list.php');
        $I->see('admin');
    }

    /**
     * @depends installWorks
     */
    public function logsWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/list.php');
        $I->click('Active');
        $I->click('Log');
        $I->seeCurrentURLEquals('/log.php');
        $I->see('Attivazione disponibilita\'');

        $I->click('Lista Disponibilità');
        $I->seeCurrentURLEquals('/list.php');
        $I->click('Not Active');
        $I->seeCurrentURLEquals('/list.php');
        $I->click('Log');
        $I->seeCurrentURLEquals('/log.php');
        $I->see('Rimozione disponibilita\'');
    }

    /**
     * @depends installWorks
     */
    public function addUsersWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/list.php');
        $I->click('Add user');
        $I->seeCurrentURLEquals('/edit_user.php?add');
        /* TODO
        $I->click('Lista Disponibilità');
        $I->seeCurrentURLEquals('/list.php');
        $I->click('Not Active');
        $I->seeCurrentURLEquals('/list.php');
        $I->click('Log');
        $I->seeCurrentURLEquals('/log.php');
        $I->see('Rimozione disponibilita\'');
        */
    }

    //public function servicesWorks(AcceptanceTester $I)
    //{
        /**
        * @var FakerGenerator
        */
        /* TODO: Add more users
        $faker = \Faker\Factory::create();

        $I->amOnPage('/list.php');
        $I->click('Services');
        $I->seeCurrentURLEquals('/services.php');
        $I->click('add service');
        $I->seeCurrentURLEquals('/edit_service.php');
        $I->fillField('data', '2020-01-01');
        $I->fillField('uscita', '12:12');
        $I->fillField('rientro', '14:14');
        //TODO: check options
        $I->type('luogo', $faker->word);
        $I->type('note', $faker->word);
        $I->click('invia');
        $I->seeCurrentURLEquals('/services.php');
        $I->see('type2');
        */
    //}
}
                
