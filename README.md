# Laravel Project Setup and Deployment on AWS

### Install admin (do not update config file):
php artisan adminlte:install

### Run Schedules Locally:
php artisan schedule:work

### Scheduled Tasks Setup:
#### Create a new Supervisor configuration file:
sudo apt-get install supervisor
#### Create a new Supervisor configuration file:
sudo nano /etc/supervisor/conf.d/laravel-scheduler.conf

#### Add the following content:
###### [program:laravel-scheduler]
###### process_name=%(program_name)s_%(process_num)02d
###### command=/usr/bin/php /path/to/your/laravel/artisan schedule:work
###### autostart=true
###### autorestart=true
###### user=www-data
###### numprocs=1
###### redirect_stderr=true
###### stdout_logfile=/path/to/your/laravel/storage/logs/scheduler.log

#### Update Supervisor and start the process:
###### sudo supervisorctl reread
###### sudo supervisorctl update
#### sudo supervisorctl start laravel-scheduler:*

### Useful Commands
#### View Apache error logs: 
###### sudo tail -f /var/log/apache2/error.log
#### View Laravel logs: 
###### tail -f /home/ubuntu/forecasts-parser/storage/logs/scheduler.log
