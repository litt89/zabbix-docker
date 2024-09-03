FROM alpine:3.20

ARG ZBX_SOURCES=https://git.zabbix.com/scm/zbx/zabbix.git
ARG ZBX_BRANCH=release/7.0

RUN set -eux && \
    ARCH_SUFFIX="$(cat /etc/apk/arch)"; \
    case "$ARCH_SUFFIX" in \
    ppc64le) \
    openjdk_packages='openjdk11 openjdk11-jre-headless'; \
    ;; \
    *) openjdk_packages='openjdk17 openjdk17-jre-headless'; \
    esac; \
    INSTALL_PKGS="bash \
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
    linux-headers \
    mysql-dev \
    net-snmp-dev \
    openipmi-dev \
    openldap-dev \
    openssl-dev \
    patch \
    pcre2-dev \
    libpq-dev \
    gettext \
    git \
    g++ \
    go \
    make \
    $openjdk_packages \
    unixodbc-dev \
    zlib-dev" && \
    apk add \
    --no-cache \
    --clean-protected \
    ${INSTALL_PKGS}


RUN set -eux && \
    cd /tmp/ && \
    mkdir -p /tmp/zabbix-output/proxy/sbin/ && \
    mkdir -p /tmp/zabbix-output/proxy/conf/ && \
    mkdir -p /tmp/zabbix-output/proxy/database/postgresql/ && \
    mkdir -p /tmp/zabbix-output/server/database/postgresql/option-patches/with-compression/ && \
    mkdir -p /tmp/zabbix-output/server/database/postgresql/option-patches/without-compression/ && \
    mkdir -p /tmp/zabbix-output/server/sbin/ && \
    mkdir -p /tmp/zabbix-output/server/conf/ && \
    mkdir -p /tmp/zabbix-output/server/database/postgresql/ && \
    mkdir -p /tmp/zabbix-output/general/sbin/ && \
    mkdir -p /tmp/zabbix-output/general/bin/ && \
    mkdir -p /tmp/zabbix-output/general/conf/ && \
    if [ -f "/tmp/src/bootstrap.sh" ]; then \
    cp -R /tmp/src /tmp/zabbix; \
    else \
    git -c advice.detachedHead=false clone ${ZBX_SOURCES} --branch ${ZBX_BRANCH} --depth 1 --single-branch /tmp/zabbix; \
    fi && \
    cd /tmp/zabbix && \
    zabbix_revision=`git rev-parse --short HEAD` && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" include/version.h && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/go/pkg/version/version.go && \
    sed -i "s/{ZABBIX_REVISION}/$zabbix_revision/g" src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java && \
    ./bootstrap.sh && \
    ./configure \
    --datadir=/usr/lib \
    --libdir=/usr/lib/zabbix \
    --prefix=/usr \
    --sysconfdir=/etc/zabbix \
    --enable-ipv6 \
    --enable-server \
    --with-ldap \
    --with-libcurl \
    --with-libmodbus \
    --with-libpcre2 \
    --with-libxml2 \
    --with-postgresql \
    --with-net-snmp \
    --with-openipmi \
    --with-openssl \
    --with-ssh \
    --with-unixodbc \
    --silent && \
    make -j"$(nproc)" -s dbschema && \
    make -j"$(nproc)" -s && \
    make -j"$(nproc)" -s gettext && \
    cat database/postgresql/schema.sql > database/postgresql/create.sql && \
    gzip -c database/postgresql/create.sql > /tmp/zabbix-output/proxy/database/postgresql/create.sql.gz && \
    cat database/postgresql/images.sql >> database/postgresql/create.sql && \
    cat database/postgresql/data.sql >> database/postgresql/create.sql && \
    gzip -c database/postgresql/create.sql > /tmp/zabbix-output/server/database/postgresql/create.sql.gz && \
    rm -rf database/postgresql/create.sql && \
    cp -R /tmp/zabbix/database/postgresql/timescaledb/schema.sql /tmp/zabbix-output/server/database/postgresql/timescaledb.sql && \
    cp -R /tmp/zabbix/database/postgresql/timescaledb/option-patches/with-compression/*.sql /tmp/zabbix-output/server/database/postgresql/option-patches/with-compression/ && \
    cp -R /tmp/zabbix/database/postgresql/timescaledb/option-patches/without-compression/*.sql /tmp/zabbix-output/server/database/postgresql/option-patches/without-compression/ && \
    mkdir /tmp/fonts/ && \
    curl --tlsv1.2 -sSf -L "https://noto-website-2.storage.googleapis.com/pkgs/NotoSansCJKjp-hinted.zip" -o /tmp/fonts/NotoSansCJKjp-hinted.zip && \
    unzip /tmp/fonts/NotoSansCJKjp-hinted.zip -d /tmp/fonts/ && \
    cp /tmp/fonts/NotoSansCJKjp-Regular.otf /tmp/zabbix/ui/assets/fonts/NotoSansCJKjp-Regular.ttf && \
    cp /tmp/fonts/LICENSE_OFL.txt /tmp/zabbix/ui/assets/fonts/ && \
    chmod o+r /tmp/zabbix/ui/assets/fonts/* && \
    strip /tmp/zabbix/src/zabbix_server/zabbix_server && \
    cp /tmp/zabbix/src/zabbix_server/zabbix_server /tmp/zabbix-output/server/sbin/ && \
    cp /tmp/zabbix/conf/zabbix_server.conf /tmp/zabbix-output/server/conf/ && \
    make -s distclean

STOPSIGNAL SIGTERM

COPY ["conf/etc/", "/etc/"]

RUN set -eux && \
    INSTALL_PKGS="bash \
    tzdata \
    apache2 \
    curl \
    php83-apache2 \
    php83-bcmath \
    php83-ctype \
    php83-curl \
    php83-gd \
    php83-gettext \
    php83-json \
    php83-ldap \
    php83-pgsql \
    php83-mbstring \
    php83-session \
    php83-simplexml \
    php83-sockets \
    php83-fileinfo \
    php83-xmlreader \
    php83-xmlwriter \
    php83-openssl \
    postgresql16-client" && \
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
    mkdir -p /etc/zabbix && \
    mkdir -p /etc/zabbix/web && \
    mkdir -p /etc/zabbix/web/certs && \
    rm -f "/etc/apache2/conf.d/default.conf" && \
    rm -f "/etc/apache2/conf.d/ssl.conf" && \
    sed -ri \
    -e 's!^(\s*CustomLog)\s+\S+!\1 /proc/self/fd/1!g' \
    -e 's!^(\s*ErrorLog)\s+\S+!\1 /proc/self/fd/2!g' \
    "/etc/apache2/httpd.conf" && \
    sed -ri \
    -e 's!^(\s*PidFile)\s+\S+!\1 "/tmp/httpd.pid"!g' \
    "/etc/apache2/conf.d/mpm.conf" && \
    sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/httpd.conf && \
    rm -rf "/var/run/apache2/" && \
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
    chown --quiet -R zabbix:root /etc/apache2/ /etc/php83/ && \
    chgrp -R 0 /etc/apache2/ /etc/php83/ && \
    chmod -R g=u /etc/apache2/ /etc/php83/

EXPOSE 8080/TCP 8443/TCP

WORKDIR /usr/share/zabbix

COPY ["docker-entrypoint.sh", "/usr/bin/"]

USER 1997

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["/usr/sbin/httpd", "-D", "FOREGROUND"]