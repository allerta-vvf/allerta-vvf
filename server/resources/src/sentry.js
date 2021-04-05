import * as Sentry from "@sentry/browser";
import { Integrations } from "@sentry/tracing";

if (process.env.config && process.env.config.sentry_enabled) {
  if ("connection" in navigator && navigator.connection.saveData) {
    console.log("Skipping Sentry init because data save is enabled");
  } else {
    Sentry.init({
      dsn: process.env.config.sentry_dsn,
      integrations: [new Integrations.BrowserTracing()],
      tracesSampleRate: 0.6,
      release: "allerta-vvf-frontend@" + process.env.GIT_VERSION,
      environment: process.env.config.sentry_environment
    });
  }
}
