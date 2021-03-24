describe("Service management", () => {
    beforeEach(() => {
        cy.login();
    })

    it('Add Service with new type', function () {
        cy.get('tr:has(> td:has(> a[id="username-11"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-4"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-9"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-7"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-2"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-6"])) > :nth-child(6)').should('contain', '0');
        cy.visit("/edit_service.php?add", {
            onBeforeLoad(win) {
                cy.stub(win, 'prompt').returns('test');
            }
        });
        cy.get('.form-control').clear();
        cy.get('.form-control').type('07/12/2020');
        cy.window().then(win => win.$('.datepicker').remove());
        cy.get('#progressivo').clear();
        cy.get('#progressivo').type('1234/5');
        cy.get('#timePicker1').clear();
        cy.get('#timePicker1').type('10:10');
        cy.get('#timePicker2').clear();
        cy.get('#timePicker2').type('23:59');
        cy.get('.chief-11').check();
        cy.get('.drivers-7').check();
        cy.get('.drivers-6').check();
        cy.get('.crew-2').check();
        cy.get('.crew-4').check();
        cy.get('.crew-9').check();
        cy.get('#addr').clear();
        cy.get('#addr').type('brescia');
        cy.get('.btn').click();
        cy.get('#search').click();
        cy.get('.results-list > :nth-child(1) > a').click();
        cy.get('#notes').click();
        cy.get('.types').select('add_new');
        cy.wait('@ajax_add_type');
        cy.get('[type="submit"]').click();
        cy.wait('@ajax_services');
        cy.contains("2020-07-12");
        cy.contains("1234/5");
        cy.visit("/log.php");
        cy.wait('@ajax_log');
        cy.contains("Aggiunto intervento");
        cy.visit("/list.php");
        cy.wait('@ajax_list');
        cy.get('tr:has(> td:has(> a[id="username-11"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-4"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-9"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-7"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-2"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-6"])) > :nth-child(6)').should('contain', '1');
    });

    it('Edit service', function() {
        cy.visit("/services.php");
        cy.wait('@ajax_services');
        cy.get('#row-0 > .dtr-control').click();
        cy.get('.dtr-details a[data-action="edit"]').click();
        cy.get('#progressivo').clear();
        cy.get('#progressivo').type('4321/5');
        cy.get('.chief-11').uncheck();
        cy.get('.chief-8').check();
        cy.get('.crew-4').uncheck();
        cy.get('.crew-9').uncheck();
        cy.get('.crew-3').check();
        cy.get('.crew-5').check();
        cy.get('#addr').clear();
        cy.get('#addr').type('milano');
        cy.get('.btn').click();
        cy.get('.results-list > :nth-child(1) > a').click();
        cy.get('[type="submit"]').click();
        cy.wait('@ajax_services');
        cy.contains("2020-07-12");
        cy.contains("4321/5");
        cy.visit("/log.php");
        cy.wait('@ajax_log');
        cy.contains("Modificato intervento");
        cy.visit("/list.php");
        cy.wait('@ajax_list');
        cy.get('tr:has(> td:has(> a[id="username-11"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-8"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-4"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-9"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-3"])) > :nth-child(6)').should('contain', '1');
        cy.get('tr:has(> td:has(> a[id="username-5"])) > :nth-child(6)').should('contain', '1');
    });

    it('Delete Service', function() {
        cy.visit("/services.php");
        cy.wait('@ajax_services');
        cy.get('#row-0 > .dtr-control').click();
        cy.get('.dtr-details a[data-action="delete"]').click();
        cy.get('#remove').click();
        cy.wait('@ajax_services');
        cy.visit("/log.php");
        cy.wait('@ajax_log');
        cy.contains("Rimosso intervento");
        cy.visit("/list.php");
        cy.wait('@ajax_list');
        cy.get('tr:has(> td:has(> a[id="username-8"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-3"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-5"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-7"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-2"])) > :nth-child(6)').should('contain', '0');
        cy.get('tr:has(> td:has(> a[id="username-6"])) > :nth-child(6)').should('contain', '0');
    });

})