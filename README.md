# iModules_emailAPI
Collection to get email data from iModules, all email headers and recipients/opens/clicks and store in oracle tables.

Assumes POSTMAN.

Depends on PHP/ORACLE/OCI8/CURL

LIMIT returned rows for api is 1000. 

written to run once a day

Comments/critiques welcome

First, review https://support.imodules.com/hc/en-us/articles/228929707-Email-Metric-API

Then get an api key from imodules

import jason into postman.

using the api key, now referred to as client_secret, and your client_id, get the access_token via "get JSON key for email stats"

update the bearer token in the rest of the api calls to be the access_token you got from "get JSON key for email stats" by search and replacing REPLACE_ME_WITH_access_token with the returned access_token.

in create_tables_views.pdc change pitt_advance to your schema and alter the tablespaces and grants appropriately then run to create oracle structure.

load up the package located in iModules_emailAPI.pck into your oracle schema.

download ksh and php into a directory.  Update:
  REPLACE_ORACLE_HOST to host name where the DB is located
  REPLACE_ORACLE_SID to the sid
  REPLACE_ORACLE_USER to user id
  REPLACE_ORACLE_USER_PW to user pw

Test and let me know.

