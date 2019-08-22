FROM alpine:3.10.1
RUN addgroup -S app && adduser -S app -G app
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
