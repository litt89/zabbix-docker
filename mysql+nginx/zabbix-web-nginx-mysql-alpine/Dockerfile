FROM alpine:3.18

ARG ZBX_SOURCES=https://git.zabbix.com/scm/zbx/zabbix.git
ARG ZBX_BRANCH=release/6.0

RUN apk add \
    --no-cache \
    --clean-protected \
    bash \
    autoconf \
    automake \
    coreutils \
    curl \
    curl-dev \
    libevent-dev \
    libssh-dev \
    sqlite-dev \
    libmodbus-dev \
    libxml2-dev \
    mysql-dev \
    net-snmp-dev \
    openipmi-dev \
    openldap-dev \
    openssl-dev \
    patch \
    pcre2-dev \
    postgresql-dev \
    openjdk8 \
    gettext \
    git \
    g++ \
    go \
    make \
    openjdk8-jre-base \
    unixodbc-dev
RUN rm -rf /var/cache/apk/*

RUN cd /tmp/ && \
    git -c advice.detachedHead=false clone ${ZBX_SOURCES} --branch ${ZBX_BRANCH} --depth 1 --single-branch /tmp/zabbix-branch && \
    cd /tmp/zabbix-branch && \
    zabbix_revision=`git rev-parse --short HEAD` && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" include/version.h && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/go/pkg/version/version.go && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java && \
    ./bootstrap.sh && \
    export CFLAGS="-fPIC -pie -Wl,-z,relro,-z,now,-z,defs" && \
    export CFLAGS="$CFLAGS -D_FORTIFY_SOURCE=2 -fexceptions -O2 -pipe" && \
    ./configure \
            --datadir=/usr/lib \
            --libdir=/usr/lib/zabbix \
            --prefix=/usr \
            --sysconfdir=/etc/zabbix \
            --enable-agent \
            --enable-agent2 \
            --enable-ipv6 \
            --enable-proxy \
            --enable-server \
            --enable-webservice \
            --with-ldap \
            --with-libcurl \
            --with-libmodbus \
            --with-libpcre2 \
            --with-libxml2 \
            --with-mysql \
            --with-net-snmp \
            --with-openipmi \
            --with-openssl \
            --with-ssh \
            --with-unixodbc \
            $additional_components \
            --silent && \
    make -j"$(nproc)" -s dbschema && \
    make -j"$(nproc)" -s && \
    make -j"$(nproc)" -s gettext && \
    cat database/mysql/schema.sql > database/mysql/create.sql && \
    cat database/mysql/images.sql >> database/mysql/create.sql && \
    cat database/mysql/data.sql >> database/mysql/create.sql && \
    gzip -c database/mysql/create.sql > database/mysql/create_server.sql.gz && \
    rm -rf database/mysql/create.sql && \
    cat database/mysql/schema.sql > database/mysql/create.sql && \
    gzip -c database/mysql/create.sql > database/mysql/create_proxy.sql.gz && \
    rm -rf database/mysql/create.sql && \
    strip /tmp/zabbix-branch/src/zabbix_agent/zabbix_agentd && \
    strip /tmp/zabbix-branch/src/zabbix_server/zabbix_server && \
    strip /tmp/zabbix-branch/src/zabbix_proxy/zabbix_proxy && \
    strip /tmp/zabbix-branch/src/go/bin/zabbix_agent2 && \
    strip /tmp/zabbix-branch/src/zabbix_get/zabbix_get && \
    strip /tmp/zabbix-branch/src/zabbix_sender/zabbix_sender && \
    strip /tmp/zabbix-branch/src/go/bin/zabbix_web_service && \
    rm -rf $(go env GOCACHE)

STOPSIGNAL SIGTERM

RUN cp -R /tmp/zabbix-branch/ui /usr/share/zabbix
COPY ["conf/etc/", "/etc/"]

RUN set -eux && \
    INSTALL_PKGS="bash \
            curl \
            mariadb-client \
            mariadb-connector-c \
            nginx \
            php81-bcmath \
            php81-ctype \
            php81-fpm \
            php81-gd \
            php81-gettext \
            php81-json \
            php81-ldap \
            php81-mbstring \
            php81-mysqli \
            php81-session \
            php81-simplexml \
            php81-sockets \
            php81-fileinfo \
            php81-xmlreader \
            php81-xmlwriter \
            php81-openssl \
            supervisor" && \
    apk add \
            --no-cache \
            --clean-protected \
        ${INSTALL_PKGS} && \
    apk add \
            --clean-protected \
            --no-cache \
            --no-scripts \
        apache2-ssl && \
    addgroup \
            --system \
            --gid 1995 \
        zabbix && \
    adduser \
            --system \
            --gecos "Zabbix monitoring system" \
            --disabled-password \
            --uid 1997 \
            --ingroup zabbix \
            --shell /sbin/nologin \
            --home /var/lib/zabbix/ \
        zabbix && \
    adduser zabbix root && \
    mkdir -p /etc/zabbix && \
    mkdir -p /etc/zabbix/web && \
    mkdir -p /etc/zabbix/web/certs && \
    mkdir -p /var/lib/php/session && \
    rm -rf /etc/php81/php-fpm.d/www.conf && \
    rm -f /etc/nginx/http.d/*.conf && \
    ln -sf /dev/fd/2 /var/lib/nginx/logs/error.log && \
    cd /usr/share/zabbix/ && \
    rm -f conf/zabbix.conf.php conf/maintenance.inc.php conf/zabbix.conf.php.example && \
    rm -rf tests && \
    rm -f locale/add_new_language.sh locale/update_po.sh locale/make_mo.sh && \
    find /usr/share/zabbix/locale -name '*.po' | xargs rm -f && \
    find /usr/share/zabbix/locale -name '*.sh' | xargs rm -f && \
    ln -s "/etc/zabbix/web/zabbix.conf.php" "/usr/share/zabbix/conf/zabbix.conf.php" && \
    ln -s "/etc/zabbix/web/maintenance.inc.php" "/usr/share/zabbix/conf/maintenance.inc.php" && \
    chown --quiet -R zabbix:root /etc/zabbix/ /usr/share/zabbix/include/defines.inc.php /usr/share/zabbix/modules/ && \
    chgrp -R 0 /etc/zabbix/ /usr/share/zabbix/include/defines.inc.php /usr/share/zabbix/modules/ && \
    chmod -R g=u /etc/zabbix/ /usr/share/zabbix/include/defines.inc.php /usr/share/zabbix/modules/ && \
    chown --quiet -R zabbix:root /etc/nginx/ /etc/php81/php-fpm.d/ /etc/php81/php-fpm.conf && \
    chgrp -R 0 /etc/nginx/ /etc/php81/php-fpm.d/ /etc/php81/php-fpm.conf && \
    chmod -R g=u /etc/nginx/ /etc/php81/php-fpm.d/ /etc/php81/php-fpm.conf && \
    chown --quiet -R zabbix:root /var/lib/php/session/ /var/lib/nginx/ && \
    chgrp -R 0 /var/lib/php/session/ /var/lib/nginx/ && \
    chmod -R g=u /var/lib/php/session/ /var/lib/nginx/ && \
    rm -rf /var/cache/apk/*

EXPOSE 8080/TCP 8443/TCP

WORKDIR /usr/share/zabbix

COPY ["docker-entrypoint.sh", "/usr/bin/"]

RUN ["chmod", "+x", "/usr/bin/docker-entrypoint.sh"]

USER 1997

ENTRYPOINT ["docker-entrypoint.sh"]