<?php

class FirstCest
{
    public function installWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/install/install.php');
        $I->click('Invia');
        $I->seeCurrentURLEquals('/install/install.php');
        $I->fillField('dbhost', '127.0.0.1');
        $I->fillField('uname', 'root');
        $I->fillField('pwd', 'password');
        $I->click('submit');
        
        $I->click('Popolare il database');

        $I->fillField('user_name', 'admin_user');
        $I->fillField('admin_password', 'password');
        $I->checkOption('admin_visible');
        $I->fillField('admin_email', 'admin_mail@allertavvf.local');
        $I->fillField('owner', 'owner');
        $I->click('Submit');
        $I->see('execre il login');
        $I->click('execre il login');
        $I->fillField('name', 'admin_user');
        $I->fillField('password', 'password');
        $I->click('login');
        $I->seeCurrentURLEquals('/lista.php');
        $I->see('admin_user');
    }

    public function logsWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/lista.php');
        //$I->click('Attivo');
        $I->click('Log');
        /*$I->seeCurrentURLEquals('/log.php');
        $I->see('Attivazione disponibilita\'');

        $I->click('Lista Disponibilità');
        $I->seeCurrentURLEquals('/lista.php');
        $I->click('Non Attivo');
        $I->seeCurrentURLEquals('/lista.php');
        $I->click('Log');
        $I->seeCurrentURLEquals('/log.php');
        $I->see('Rimozione disponibilita\'');*/
    }

    public function interventiWorks(AcceptanceTester $I)
    {
        /**
        * @var FakerGenerator
        */
        /* TODO: Add more users
        $faker = \Faker\Factory::create();

        $I->amOnPage('/lista.php');
        $I->click('Interventi');
        $I->seeCurrentURLEquals('/interventi.php');
        $I->click('add intervento');
        $I->seeCurrentURLEquals('/modifica_intervento.php');
        $I->fillField('data', '2020-01-01');
        $I->fillField('uscita', '12:12');
        $I->fillField('rientro', '14:14');
        //TODO: check options
        $I->type('luogo', $faker->word);
        $I->type('note', $faker->word);
        $I->click('invia');
        $I->seeCurrentURLEquals('/interventi.php');
        $I->see('type2');
        */
    }
}
                
