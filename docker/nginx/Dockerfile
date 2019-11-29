FROM nginx:1.16
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/public
COPY ./src/public/ /var/www/html/public

     
