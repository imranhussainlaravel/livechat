# LiveChat Enterprise Production Guide

This document outlines the required infrastructure configuration to deploy the LiveChat application into a highly available, enterprise-grade production environment.

## 1. Process Management (Supervisor)

Since Windows does not natively support `ext-pcntl` required for Laravel Horizon, production deployments should utilize a Linux-based server (Ubuntu/Debian) managed by **Supervisor**.

### Queue Workers Configuration (`/etc/supervisor/conf.d/livechat-worker.conf`)
You need multiple queue workers to handle concurrent chat assignments, follow-up emails, and quotation sending.

```ini
[program:livechat-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/livechat/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8 ; Run 8 concurrent worker processes
redirect_stderr=true
stdout_logfile=/path/to/livechat/storage/logs/worker.log
stopwaitsecs=3600
```

### Laravel Reverb Configuration (`/etc/supervisor/conf.d/livechat-reverb.conf`)
High availability WebSockets require Reverb to be constantly running.

```ini
[program:livechat-reverb]
process_name=%(program_name)s
command=php /path/to/livechat/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/livechat/storage/logs/reverb.log
stopwaitsecs=3600
```

## 2. Cron Scheduler

Ensure the Laravel scheduler is triggered every minute by setting up the crontab for the `www-data` user:

```bash
* * * * * cd /path/to/livechat && php artisan schedule:run >> /dev/null 2>&1
```
*(This manages the Queue Auto-Assignment sweep and the Follow-up Reminders).*

## 3. Caching & Database Failover

- **Redis**: Acts as the central nervous system for LiveChat. Used for cache (settings), queue (FIFO queue mechanism), and real-time load tracking (`AgentLoadService`). A managed Redis instance (e.g., AWS ElastiCache or Redis Enterprise) is recommended to prevent data loss.
- **MySQL**: Deploy with a primary-replica cluster setup to handle heavy write operations (Message Logging, Activity Tracking).

## 4. Reverse Proxy & WebSocket Scaling (Nginx)

When scaling to thousands of concurrent users, Nginx must be configured to upgrade WebSocket connections properly.

```nginx
server {
    listen 80;
    server_name chat.yourdomain.com;

    location / {
        proxy_pass http://127.0.0.1:8080; # Reverb Port
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
    }
}
```

## 5. Automated Backups

Utilize `spatie/laravel-backup` to automate database and configuration backups to AWS S3. 
Run the backup daily via the scheduler.

## 6. Logs & Monitoring

- **Logs**: We have configured `LOG_CHANNEL=daily` to prevent massive log files. These should be ingested via a service like Datadog or AWS CloudWatch.
- **Monitoring**: 
  - Install Laravel Telescope (Local/Staging only).
  - Use Sentry or Flare (Bugsnag) for real-time unhandled exception monitoring in Production.
  - Expose `/api/admin/reports` internally to monitor live chat performance and agent load limits.
