# Collect CloudFlare errors to StatsD

This small script can be used to collect CloudFlare 500 and 1000 errors to StatsD using CloudFlare custom error page. Reason why this script was created is that you cannot get real-time error stats from CloudFlare any other way.

How it works is that you include tiny transparent GIF to CloudFlare custom error page and load it from your own server, for example: https://myowncollectorhost.com/522_478149ad1570291.gif

When /public/index.php gets this request, it increases ```cloudflare.errors``` and ```cloudflare.errors.522``` counters and sends transparent GIF as response.

## Installation

Clone this repository for example to dir ```/var/www/cloudflare-errors-to-statsd-php```

Run composer install to get StatsD library installed.

Copy ```config.php.example``` to ```config.php``` and configure it to suit your needs. Default values should work most of the times.

## Web server configuration (nginx)

Create virtual host for this data collector and make sure all requests are handled with /public/index.php, like this:

```
server {
    listen              80;
    server_name         myowncollectorhost.com;

    root /var/www/cloudflare-errors-to-statsd-php/public;

    location / {
        index  index.php index.html;
        try_files $uri $uri/ /index.php;
    }

    # pass the PHP scripts to FastCGI server
    #
    location ~ \.php$ {
        fastcgi_pass    127.0.0.1:9000;
        fastcgi_index   index.php;
        include         fastcgi_params;
        fastcgi_param   SCRIPT_FILENAME /var/www/cloudflare-errors-to-statsd-php/public/index.php;
    }
}
```


## Example CloudFlare error page

```js

<html>
<head>
<title>Test</title>
<script type="text/javascript">

        document.addEventListener("DOMContentLoaded", function() {
                var rayId = '';
                var errorCode = '';
                var elements = document.querySelectorAll('li');
                Array.prototype.forEach.call(elements, function(el, i){
                        if (el.textContent.indexOf('Ray ID:') >= 0) {
                                rayId = el.textContent.replace('Ray ID: ','')
                        }
                        if (el.textContent.indexOf('Error reference number:') >= 0) {
                                errorCode = el.textContent.replace('Error reference number: ','')
                        }
                });

                var img = document.createElement('img');
                img.src = 'http://myowncollectorhost.com/' + errorCode + '_' + rayId + '.gif';
                document.body.appendChild(img);
        });

</script>
</style>
</head>
<body>
::CLOUDFLARE_ERROR_500S_BOX::
</body>
</html>
```
