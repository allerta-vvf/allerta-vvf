describe("Login and logout", () => {
    it('Login', function () {
        cy.login()
        cy.contains("Logs")
        cy.contains("Logs").click()
        cy.wait('@ajax').its('status').should('eq', 200)
        cy.wait(500) //table rendering timeout
        cy.get("#list").contains("Login")
    })

    it('Logout', function () {
        cy.login()
        cy.visit("/logout.php")
        cy.contains("Login")
        cy.login()
        cy.contains("Logs")
        cy.contains("Logs").click()
        cy.wait('@ajax').its('status').should('eq', 200)
        cy.wait(500) //table rendering timeout
        cy.get("#list").contains("Logout")
    })
});