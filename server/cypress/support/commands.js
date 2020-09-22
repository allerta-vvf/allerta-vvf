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
