FROM nginx:1.16
ADD ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /var/www/public
ADD ./src/public/ /var/www/public
     
