// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
Cypress.Commands.add("login", (username="admin", password="correcthorsebatterystaple") => {
    cy.server().route('GET', '/resources/ajax/ajax_*').as('ajax');
    cy.visit("/");
    cy.getCookie('acceptCookies')
        .then((c) => {
            if(c == undefined) cy.get(".acceptcookies").click({force: true})
        })
    cy.get("input[name='name']")
        .clear()
        .type(username)
        .should('have.value', username)

    cy.get("input[name='password']")
        .clear()
        .type(password)
        .should('have.value', password)

    cy.get("input[name='login']").click()
})

Cypress.Commands.add("getApiKey", (username="admin", password="correcthorsebatterystaple") => {
    cy.request({ method: 'POST', url: '/api.php/login', form: true, body: { username: username, password: password }})
        .then((response) => {
            expect(response.status).to.eq(200)
            expect(response.body).to.have.property('apiKey')
            console.log(response.body)
            return response.body.apiKey
        })
})
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })
