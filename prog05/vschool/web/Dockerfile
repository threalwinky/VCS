FROM ubuntu:22.04
ENV DEBIAN_FRONTEND=noninteractive

RUN apt update && apt install -y software-properties-common
RUN apt install -y php8.1 mysql-server apache2
RUN apt install -y php8.1-mysqli
RUN apt install php8.1-curl

RUN rm -rf /var/www/html \
    mkdir /var/www/html
WORKDIR /var/www/html
COPY src .
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD service apache2 start && \
    tail -f /dev/null