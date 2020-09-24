describe("User management", () => {
    before(() => {
        cy.login()
        cy.fixture('users')
            .as('users');
    })
    it('Create users', () => {
        cy.get('@users')
            .then((users) => {
                cy.getApiKey().then((apiKey) => {
                    var i = 1
                    users.forEach(user => {
                        console.log("User '"+user.name+"' number "+i);
                        if(i == 1){
                            console.log("Adding user via gui...");
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
                            cy.visit("/log.php")
                            cy.contains("User created")
                            cy.contains(user.name)
                            cy.visit("/")
                        } else {
                            console.log("Adding user via api...");
                            cy.request({ method: 'POST', url: '/api.php/user', form: true,
                            body: {
                                apiKey: apiKey,
                                mail: user.email,
                                name: user.name,
                                username: user.username,
                                password: user.password,
                                birthday: user.birthday,
                                capo: user.foreman | 0,
                                autista: user.driver | 0,
                                hidden: 0,
                                disabled: 0
                            }})
                                .then((response) => {
                                    console.log(response.body)
                                    expect(response.status).to.eq(200)
                                    expect(response.body).to.have.property('userId')
                                    cy.visit("/log.php")
                                    cy.contains("User created")
                                    cy.contains(user.name)
                                })
                        }
                        i+=1;
                    })
                });
            })
    });
})