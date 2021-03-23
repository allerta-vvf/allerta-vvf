Cypress.on('uncaught:exception', (err, runnable) => {
    // for some reasons, the test fails without this in cartain conditions...
    return false
})

//TODO: login remember me and better language support
Cypress.Commands.add("login", (username="admin", password="correcthorsebatterystaple") => {
    cy.setCookie("forceLanguage", "en");
    cy.setCookie('disableServiceWorkerInstallation', '1');

    cy.intercept(Cypress.config('baseUrl')+'resources/ajax/ajax_add_type.php').as('ajax_add_type');
    cy.intercept(Cypress.config('baseUrl')+'resources/ajax/ajax_change_availability.php').as('ajax_change_availability');
    cy.intercept(Cypress.config('baseUrl')+'resources/ajax/ajax_list.php').as('ajax_list');
    cy.intercept(Cypress.config('baseUrl')+'resources/ajax/ajax_log.php').as('ajax_log');
    cy.intercept(Cypress.config('baseUrl')+'resources/ajax/ajax_services.php').as('ajax_services');
    cy.intercept(Cypress.config('baseUrl')+'resources/ajax/ajax_trainings.php').as('ajax_trainings');

    cy.visit("/");
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
