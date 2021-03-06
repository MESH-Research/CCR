/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />
// Use `cy.dataCy` custom command for more robust tests
// See https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements

import 'cypress-axe';

describe('Admin User Details', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  it('restricts access based on role', () => {
    cy.login({ email: "regularuser@ccrproject.dev" });
    cy.visit('/admin/users');
    cy.url().should('include', '/error403');
  });

  it('allows access based on role', () => {
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('/admin/users');
    cy.url().should('not.include', '/error403');
  });

  it('should assert the User Details page of an admin is accessible', () => {
    // Inject the axe-core libraray
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('/admin/user/2');
    cy.injectAxe();
    cy.dataCy('userDetailsHeading');
    cy.checkA11y();
  });

  it('should assert the User Details page of an non-admin is accessible', () => {
    // Inject the axe-core libraray
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('/admin/user/1');
    cy.injectAxe();
    cy.dataCy('userDetailsHeading');
    cy.checkA11y();
  });

});
