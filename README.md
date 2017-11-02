# Projet-Yona
![Yona Logo](./images/yona-with-background.png)

This application targets to offer an easy-to-use service to ease better Town Hall exchanges.
Basically with our Yona application you can easily 
** Write questions
** See all audience questions
** Vote for questions
So that Town Hall speakers can answer to most rated ones.

But our first intend was to replace microphones and use the ones from audience smartphone.
In that way our application also on-board a webrtc server that is able to manage audio flow.

## Configuration
A few environment variables are needed to run the app :
* **EASYRTC_SERVER**: the url to the webrtc server
* **MYSQL_DB**: The mysql database name
* **MYSQL_HOST**: The mysql hostname
* **MYSQL_PASSWORD**: The mysql password for the user defined in `MYSQL_USER`
* **MYSQL_USER**: The mysql user

Also, you need to update `.htaccess` file:
on the line `AuthUserFile /app/.htpasswd`, replace `/app` by the absolute path to the folder
where you deployed the application