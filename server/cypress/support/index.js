//TODO: login remember me and better language support
Cypress.Commands.add("login", (username="admin", password="correcthorsebatterystaple") => {
    cy.setCookie("forceLanguage", "en");
    cy.reload()
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