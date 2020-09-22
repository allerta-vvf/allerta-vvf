describe("Availability", () => {
    beforeEach(() => {
        cy.login()
    })
    it('Change availability to available', function () {
        cy.contains('Active').click()
        cy.on('window:alert',(txt)=>{
            expect(txt).to.contains('Thanks, admin, you have given your availability in case of alert.');
        })
        cy.get(".fa-check").should('be.visible')
        cy.contains("Logs").click()
        cy.contains("Attivazione disponibilita'")
        cy.visit("/")
    })

    it('Change availability to not available', function () {
        cy.contains('Not Active').click()
        cy.on('window:alert',(txt)=>{
            expect(txt).to.contains('Thanks, admin, you have removed your availability in case of alert.');
        })
        cy.get(".fa-times").should('be.visible')
        cy.contains("Logs").click()
        cy.contains("Rimozione disponibilita'")
        cy.visit("/")
    })
});