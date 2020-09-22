describe("Login and logout", () => {
    it('Login', function () {
        cy.login()
        cy.contains("Logs").click()
        cy.get("#list").contains("Login")
        cy.visit("/logout.php")
        cy.contains("Login")
    })

    it('Logout', function () {
        cy.login()
        cy.contains("Logs").click()
        cy.get("#list").contains("Logout")
    })
});