describe("Installation", () => {
    before(() => {
        cy.exec("rm config.old.php", {failOnNonZeroExit: false});
        cy.exec("mv config.php config.old.php", {failOnNonZeroExit: false});
        cy.visit("/");
        cy.get(".button").click();
    })

    beforeEach(() => {
      cy.setCookie("forceLanguage", "en");
    })

    it('Write wrong DB pwd and user', function () {
        cy.get("input[name='dbname']")
          .clear()
          .type("allerta_db_"+Date.now())

        cy.get("input[name='uname']")
          .clear()
          .type("root_wrongpwd_"+Date.now())

        cy.get("input[name='pwd']")
          .clear()
          .should('have.value', '')

        cy.get(".button").click();
        cy.contains("Error establishing a database connection");
        cy.visit("/");
        cy.get(".button").click();
    })

    it('Write correct DB pwd and user', function () {
        cy.get("input[name='dbname']")
          .clear()
          .type("allerta_db_"+Date.now())

        cy.get("input[name='uname']")
          .clear()
          .type("root")
          .should('have.value', 'root')

        cy.get("input[name='pwd']")
          .clear()
          .type(Cypress.env("DB_PASSWORD"))
          .should('have.value', Cypress.env("DB_PASSWORD"))

        cy.get(".button").click();
        cy.contains("Great job, man!");
        cy.get(".button").click();
    })

    it('Finish installation', function () {
        cy.get("input[name='user_name']")
          .clear()
          .type("admin")
          .should('have.value', 'admin')

        cy.get("input[name='admin_password']")
          .clear()
          .type("password")
          .should('have.value', 'password')
        cy.get("#pass-strength-result")
          .should('have.text', 'Very weak')
          .should('have.class', 'short')

        cy.get("input[name='admin_password']")
          .clear()
          .type("passsword")
          .should('have.value', 'passsword')
        cy.get("#pass-strength-result")
          .should('have.text', 'Weak')
          .should('have.class', 'bad')

        cy.get("input[name='admin_password']")
          .clear()
          .type("Tr0ub4dour&3")
          .should('have.value', 'Tr0ub4dour&3')
        cy.get("#pass-strength-result")
          .should('have.text', 'Good')
          .should('have.class', 'good')

        cy.get("input[name='admin_password']")
          .clear()
          .type("#Tr0ub4dour&3#")
          .should('have.value', '#Tr0ub4dour&3#')
        cy.get("#pass-strength-result")
          .should('have.text', 'Strong')
          .should('have.class', 'strong')

        cy.get("input[name='admin_password']")
          .clear()
          .type("correcthorsebatterystaple")
          .should('have.value', 'correcthorsebatterystaple')
        cy.get("#pass-strength-result")
          .should('have.text', 'Very strong')
          .should('have.class', 'strong')

        cy.get("input[name='admin_visible']").check()

        cy.get("input[name='admin_email']")
          .clear()
          .type("admin_email@mail.local")
          .should('have.value', 'admin_email@mail.local')

        cy.get("input[name='owner']")
          .clear()
          .type("owner")
          .should('have.value', 'owner')

        cy.get(".button").click();
        cy.contains("Great job, man!");
        cy.exec("touch install/runInstall.php", {failOnNonZeroExit: false});
        cy.get(".login").click();
        cy.contains("Login");
    })
});