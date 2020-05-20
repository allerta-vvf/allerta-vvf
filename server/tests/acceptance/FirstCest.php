<?php

class FirstCest
{
    public function frontpageWorks(AcceptanceTester $I)
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
        $I->fillField('distaccamento', 'Distaccamento');
        $I->click('Submit');
        $I->see('Eseguire il login');
        $I->click('Eseguire il login');
        $I->fillField('nome', 'admin_user');
        $I->fillField('password', 'password');
        $I->click('login');
        $I->seeCurrentURLEquals('/lista.php');
        $I->see('admin_user');
    }
}
                
