## 
to crack it :
- go to vendor strunit StrWe.php (Routes) 

## link public to storage 
delete the storage link in public forlder 
> rm -rf public/storage
> php artisan storage:link

## permisison 
sudo chown -R www-data:www-data storage/ bootstrap/cache/
  240  sudo chmod -R 775 storage/ bootstrap/cache/
  241  tail -f storage/logs/laravel.log
  242  sudo chmod -R 775 .env

## Simulate deplacement : 
- php artisan simulate:movement 39 --steps=50 --interval=5
- php artisan simulate:movement 39 --steps=50 --interval=2 --route=circle
- php artisan simulate:movement 39 --steps=50 --interval=2 --route=line

# Errors 
## One signal rgumentCountError: Too few arguments to function Berkayk\OneSignal\OneSignalClient::__construct(), 3 passed in 
go to C:\MAMP\htdocs\_Mes Projets\Gold GPS\Project\admin\backend\vendor\laravel-notification-channels\onesignal\src
     return new OneSignalClient(
                      $oneSignalConfig['app_id'],
                      $oneSignalConfig['rest_api_key'],
                      $oneSignalConfig['user_auth_key']
                  );
 Event Traccar : 
- Edit the /opt/traccar/conf/traccar.xml
- check <entry key='event.forward.url'>https://gold-gps.bensassiridha.com/admin/api/traccar/events</entry>
- sudo systemctl restart traccar

# SMS
- I used performed solution that handle the SMS sending  that foud in this url : https://xsender.bensassiridha.com/admin   (admin/admin)
## Twilio 
you need create an new account and set the account information in xsender 
after that you should obtain or purchase a number to make it in from input rather that you can go to twilio dashboard and get triel number  (Develop > Messaging)

# Xsender 
## To Send with WhatsApp 
To send message with whatsapp you should run whatsApp node device
go to the root directory then tape : node app.js
- To mintain the process running on the background you can pm2 (npm install -g pm2)
 - pm2 start your-app.js
 - pm2 status
 - pm2 logs

