describe("User management", () => {
    before(() => {
        cy.login()
        cy.fixture('users')
            .as('users');
    })
    it('Create users', () => {
        cy.get('@users')
            .then((users) => {
                users.forEach(user => {
                    name = user.name
                    console.log(user)
                    cy.wait(1000)
                    cy.contains("Add user").click()
                    cy.get("input[name='mail']")
                        .clear()
                        .type(user.email)
                        .should('have.value', user.email)
                    cy.get("input[name='name']")
                        .clear()
                        .type(user.name)
                        .should('have.value', user.name)
                    cy.get("input[name='username']")
                        .clear()
                        .type(user.username)
                        .should('have.value', user.username)
                    cy.get("input[name='password']")
                        .clear()
                        .type(user.password)
                        .should('have.value', user.password)
                    cy.get("input[name='birthday']")
                        .clear()
                        .type(user.birthday)
                        .should('have.value', user.birthday)
                    if(user.foreman){
                        cy.get("input[name='capo']")
                            .check({force: true})
                    }
                    if(user.driver){
                        cy.get("input[name='autista']")
                            .check({force: true})
                    }
                    cy.contains("Submit").click()
                    cy.contains(user.name)
                })
            })
    });
})