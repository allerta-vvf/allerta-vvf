/// <reference types="cypress" />
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off loading
// the plugins file with the 'pluginsFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************

// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

/**
 * @type {Cypress.PluginConfig}
 */
module.exports = (on, config) => {
  on('before:browser:launch', (browser = {}, launchOptions) => {
    console.log(launchOptions) // print all current args

    if (browser.family === 'chromium' && browser.name !== 'electron') {
      launchOptions.preferences.default.intl = { accept_languages: "en" }
    }

    if (browser.family === 'firefox') {
      launchOptions.preferences['intl.accept_languages'] = 'en'
    }

    if (browser.name === 'electron') {
      launchOptions.args.push('--lang=en')
      launchOptions.preferences.darkTheme = true
    }

    return launchOptions
  })
}