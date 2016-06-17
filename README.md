# Cachet in Cloud Foundry

[Cachet](https://cachethq.io/) is an open source status page system, for everyone.

This is a for from this application but with some changes to be ran on Cloud Foundry.

## Run in Cloud Foundry

1. Download a `cachet-cf-bundled.zip` release from [release page](https://github.com/ArthurHlt/Cachet-cf/releases)
2. Extract it
3. Change the `manifest.yml` file to suit your needs
4. Push to Cloud Foundry (commands `cf push`)
5. Open in your browser and use `DEFAULT_ADMIN_USERNAME` and `DEFAULT_ADMIN_PASSWORD` to login (need to do this only for the first deploy)

## (**Recommended**) Use another database

**By default cachet will be run with a sqlite database, it's recommended to use another one.**

1. Create a database service from your marketplace in Cloud Foundry (commands: `cf m` and `cf cs [service name] [plan] [service instance name]`)
2. Bind the service to your cachet (commands: `cf bs cachet [service instance name]`)
3. Restage your cachet (commands: `cf restage cachet`)

## (**Higly Recommended**) Use a smtp server

Cachet can send notification to your email address, by default mail will not be send.
To use this functionality bind a smtp service to your app:

1. Create a smtp service from your marketplace in Cloud Foundry (commands: `cf m` and `cf cs [service name] [plan] [service instance name]`)
2. Bind the service to your cachet (commands: `cf bs cachet [service instance name]`)
3. Restage your cachet (commands: `cf restage cachet`)


## (**Recommended For Multi-Instances**) Use a redis server

Cachet can use a redis server for caching and save session.

1. Create a redis service from your marketplace in Cloud Foundry (commands: `cf m` and `cf cs [service name] [plan] [service instance name]`)
2. Bind the service to your cachet (commands: `cf bs cachet [service instance name]`)
3. Restage your cachet (commands: `cf restage cachet`)

