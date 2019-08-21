FROM alpine:3.10.1
CMD ["boot"]
RUN apk --update add \
    nano \
    supervisor \
    nginx \
    php-fpm \
    composer \
    php-dom \
    php-session \
    php-tokenizer \
    php-xml \
    php-xmlwriter 
WORKDIR /app
COPY ./fs/ /
