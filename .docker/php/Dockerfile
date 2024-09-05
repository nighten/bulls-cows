FROM php:8.3.7-fpm

RUN apt-get update \
    && apt-get install -y \
        zlib1g-dev \
        libicu-dev g++ \
        libzip-dev  \
        zip  \
        supervisor \
        openssh-server \
        graphviz \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install pdo pdo_mysql intl zip gd \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer\
    && yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug-common.ini \
    #&& echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/xdebug-common.ini \
    && echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/xdebug-common.ini \
    && echo "xdebug.extended_info = On" >> /usr/local/etc/php/conf.d/xdebug-common.ini \
    # > SSH > #
    && mkdir /var/run/sshd \
    && echo 'root:jw' | chpasswd \
    && sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd \
    && echo "export VISIBLE=now" >> /etc/profile \
    && rm -rf /var/lib/apt/lists/*

#RUN docker-php-ext-install opcache

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

#RUN sed -i 's/PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config
# SSH login fix. Otherwise user is kicked off after login
#RUN sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd
ENV NOTVISIBLE "in users profile"
#RUN echo "export VISIBLE=now" >> /etc/profile
COPY sshd_config /etc/ssh/sshd_config
EXPOSE 22
#CMD ["/usr/sbin/sshd", "-D"]
# < SSH < #

#ADD zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN mkdir /var/www/html-ssh

#CMD ["php-fpm"]
CMD ["/usr/bin/supervisord"]
