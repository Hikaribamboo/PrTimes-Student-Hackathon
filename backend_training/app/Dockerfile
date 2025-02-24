FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# 必要な拡張機能をインストールします（必要に応じて調整してください）。
RUN apk update && apk add --no-cache postgresql-dev $PHPIZE_DEPS
RUN docker-php-ext-install pdo pdo_pgsql

# Composerをインストールします（オプションですが、依存関係の管理に推奨されます）。
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# アプリケーションのコードをコピーします（'src'サブディレクトリにあると仮定します）。
COPY src ./

# パーミッションを設定します（必要に応じて調整してください）。
RUN chown -R www-data:www-data /var/www/html

RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf
