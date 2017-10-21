# Projet-Yona


## Configuration
A few environment variables are needed to run the app :
* **EASYRTC_SERVER**: the url to the webrtc server
* **MYSQL_DB**: The mysql databse name
* **MYSQL_HOST**: The mysql hostname
* **MYSQL_PASSWORD**: The mysql password for the user defined in `MYSQL_USER`
* **MYSQL_USER**: The mysql user

Also, you need to update `.htaccess` file:
on the line `AuthUserFile /app/.htpasswd`, replace `/app` by the absolute path to the folder
where you deployed the application