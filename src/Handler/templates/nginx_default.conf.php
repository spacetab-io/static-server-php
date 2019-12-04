# you must set worker processes based on your CPU cores, nginx does not benefit from setting more than that
worker_processes auto;

# number of file descriptors used for nginx
# the limit for the maximum FDs on the server is usually set by the OS.
# if you don't set FD's then OS settings will be used which is by default 2000
worker_rlimit_nofile 10000;

# only log critical errors
error_log /dev/stderr warn;

# provides the configuration file context in which the directives that affect connection processing are specified.
events {
    # determines how much clients will be served per worker
    # max clients = worker_connections * worker_processes
    # max clients is also limited by the number of socket connections available on the system (~64k)
    worker_connections 4000;

    # optimized to serve many clients with each thread, essential for linux -- for testing environment
    use <?=$connProcMethod?>;

    # accept as many connections as possible, may flood worker connections if set too low -- for testing environment
    multi_accept on;
}

http {
    types {
        text/html                                        html htm shtml;
        text/css                                         css;
        text/xml                                         xml;
        image/gif                                        gif;
        image/jpeg                                       jpeg jpg;
        application/javascript                           js;
        application/atom+xml                             atom;
        application/rss+xml                              rss;

        text/mathml                                      mml;
        text/plain                                       txt;
        text/vnd.sun.j2me.app-descriptor                 jad;
        text/vnd.wap.wml                                 wml;
        text/x-component                                 htc;

        image/png                                        png;
        image/svg+xml                                    svg svgz;
        image/tiff                                       tif tiff;
        image/vnd.wap.wbmp                               wbmp;
        image/webp                                       webp;
        image/x-icon                                     ico;
        image/x-jng                                      jng;
        image/x-ms-bmp                                   bmp;

        font/woff                                        woff;
        font/woff2                                       woff2;

        application/java-archive                         jar war ear;
        application/json                                 json;
        application/mac-binhex40                         hqx;
        application/msword                               doc;
        application/pdf                                  pdf;
        application/postscript                           ps eps ai;
        application/rtf                                  rtf;
        application/vnd.apple.mpegurl                    m3u8;
        application/vnd.google-earth.kml+xml             kml;
        application/vnd.google-earth.kmz                 kmz;
        application/vnd.ms-excel                         xls;
        application/vnd.ms-fontobject                    eot;
        application/vnd.ms-powerpoint                    ppt;
        application/vnd.oasis.opendocument.graphics      odg;
        application/vnd.oasis.opendocument.presentation  odp;
        application/vnd.oasis.opendocument.spreadsheet   ods;
        application/vnd.oasis.opendocument.text          odt;
        application/vnd.openxmlformats-officedocument.presentationml.presentation
        pptx;
        application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        xlsx;
        application/vnd.openxmlformats-officedocument.wordprocessingml.document
        docx;
        application/vnd.wap.wmlc                         wmlc;
        application/x-7z-compressed                      7z;
        application/x-cocoa                              cco;
        application/x-java-archive-diff                  jardiff;
        application/x-java-jnlp-file                     jnlp;
        application/x-makeself                           run;
        application/x-perl                               pl pm;
        application/x-pilot                              prc pdb;
        application/x-rar-compressed                     rar;
        application/x-redhat-package-manager             rpm;
        application/x-sea                                sea;
        application/x-shockwave-flash                    swf;
        application/x-stuffit                            sit;
        application/x-tcl                                tcl tk;
        application/x-x509-ca-cert                       der pem crt;
        application/x-xpinstall                          xpi;
        application/xhtml+xml                            xhtml;
        application/xspf+xml                             xspf;
        application/zip                                  zip;

        application/octet-stream                         bin exe dll;
        application/octet-stream                         deb;
        application/octet-stream                         dmg;
        application/octet-stream                         iso img;
        application/octet-stream                         msi msp msm;

        audio/midi                                       mid midi kar;
        audio/mpeg                                       mp3;
        audio/ogg                                        ogg;
        audio/x-m4a                                      m4a;
        audio/x-realaudio                                ra;

        video/3gpp                                       3gpp 3gp;
        video/mp2t                                       ts;
        video/mp4                                        mp4;
        video/mpeg                                       mpeg mpg;
        video/quicktime                                  mov;
        video/webm                                       webm;
        video/x-flv                                      flv;
        video/x-m4v                                      m4v;
        video/x-mng                                      mng;
        video/x-ms-asf                                   asx asf;
        video/x-ms-wmv                                   wmv;
        video/x-msvideo                                  avi;
    }

    # cache informations about FDs, frequently accessed files
    # can boost performance, but you need to test those values
    open_file_cache max=200000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;

    # to boost I/O on HDD we can disable access logs
    access_log off;

    # copies data between one FD and other from within the kernel
    # faster than read() + write()
    sendfile on;

    # send headers in one piece, it is better than sending them one by one
    tcp_nopush on;

    # don't buffer data sent, good for small data bursts in real time
    tcp_nodelay on;

    charset utf-8;

    server_tokens off;

    # reduce the data that needs to be sent over network -- for testing environment
    gzip on;
    gzip_min_length 10240;
    gzip_comp_level 1;
    gzip_vary on;
    gzip_disable msie6;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types
        # text/html is always compressed by HttpGzipModule
        text/css
        text/javascript
        text/xml
        text/plain
        text/x-component
        application/javascript
        application/x-javascript
        application/json
        application/xml
        application/rss+xml
        application/atom+xml
        font/truetype
        font/opentype
        application/vnd.ms-fontobject
        image/svg+xml;

    # allow the server to close connection on non responding client, this will free up memory
    reset_timedout_connection on;

    # request timed out -- default 60
    client_body_timeout 10;

    # if client stop responding, free up memory -- default 60
    send_timeout 5;

    # server will close connection after this time -- default 75
    keepalive_timeout 30;

    # number of requests client can make over keep-alive -- for testing environment
    keepalive_requests 100000;

    server {
        listen <?=$serverPort?>;
        server_name <?=$serverHost?>;

        root   "<?=$serverRoot?>";
        index  "<?=$serverIndex?>";

        location /healthcheck {
            add_header Content-Type text/plain;
            return 200 "ok";
        }

        <?php if ($prerenderEnabled):?>
            location / {
                try_files $uri @prerender;
            }

            location @prerender {
                set $prerender 0;
                if ($http_user_agent ~* "googlebot|bingbot|yandex|baiduspider|twitterbot|facebookexternalhit|rogerbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest\/0\.|pinterestbot|slackbot|vkShare|W3C_Validator|whatsapp") {
                    set $prerender 1;
                }
                if ($args ~ "_escaped_fragment_") {
                    set $prerender 1;
                }

                if ($http_user_agent ~ "Prerender") {
                    set $prerender 0;
                }

                if ($uri ~* "\.(js|css|xml|png|jpg|jpeg|gif|pdf|doc|docx|txt|ico|rss|zip|mp3|rar|exe|wmv|avi|ppt|pptx|mpg|mpeg|tif|wav|mov|psd|ai|xls|xlsx|mp4|m4a|swf|dat|dmg|iso|flv|m4v|torrent|ttf|woff|svg|eot)") {
                    set $prerender 0;
                }

                # resolve using Google's DNS/Cloudflare server to force DNS resolution and prevent caching of IPs
                resolver 8.8.8.8, 8.8.4.4, 1.1.1.1, 1.0.0.1;

                if ($prerender = 1) {
                    #setting prerender as a variable forces DNS resolution since nginx caches IPs and doesnt play well with load balancing
                    set $prerender "<?=$prerenderUrl?>";
                    rewrite .* /$scheme://$host$request_uri? break;
                    proxy_pass http://$prerender;
                }

                if ($prerender = 0) {
                    rewrite .* /index.html break;
                }
            }
        <?php else:?>
            location / {
                <?php foreach($headers as $name => $value):?>
                    <?php if ($name !== 'Cache-Control' || $name !== 'Pragma'):?>
                        add_header "<?=$name?>" "<?=addslashes($value)?>";
                    <?php endif;?>
                <?php endforeach;?>
                try_files $uri $uri/ @rewrites;
            }

            location @rewrites {
                rewrite ^(.+)$ /index.html last;
            }

            location ~* \.(js|css|json|xml|webm|png|jpg|jpeg|gif|pdf|doc|docx|txt|ico|rss|zip|mp3|rar|exe|wmv|avi|ppt|pptx|mpg|mpeg|tif|wav|mov|psd|ai|xls|xlsx|mp4|m4a|swf|dat|dmg|iso|flv|m4v|torrent|ttf|woff|svg|eot) {
                expires 24h;
                add_header Pragma "<?=addslashes($headers['Pragma'])?>";
                add_header Cache-Control "<?=addslashes($headers['Cache-Control'])?>";
            }
        <?php endif;?>
    }
}