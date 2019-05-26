#!/usr/bin/env bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
#
# Auto install JNOJ
#
# Copyright (C) 2017-2019 Shiyang <dr@shiyang.me>
#
# System Required:  CentOS 6+, Debian7+, Ubuntu16+
#
# Reference URL:
# https://github.com/shi-yang/jnoj
#

red='\033[0;31m'
green='\033[0;32m'
yellow='\033[0;33m'
plain='\033[0m'

[[ $EUID -ne 0 ]] && echo -e "[${red}Error${plain}] This script must be run as root!" && exit 1

disable_selinux(){
    if [ -s /etc/selinux/config ] && grep 'SELINUX=enforcing' /etc/selinux/config; then
        sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config
        setenforce 0
    fi
}

check_sys(){
    local checkType=$1
    local value=$2

    local release=''
    local systemPackage=''

    if [[ -f /etc/redhat-release ]]; then
        release="centos"
        systemPackage="yum"
    elif grep -Eqi "debian|raspbian" /etc/issue; then
        release="debian"
        systemPackage="apt"
    elif grep -Eqi "ubuntu" /etc/issue; then
        release="ubuntu"
        systemPackage="apt"
    elif grep -Eqi "centos|red hat|redhat" /etc/issue; then
        release="centos"
        systemPackage="yum"
    elif grep -Eqi "debian|raspbian" /proc/version; then
        release="debian"
        systemPackage="apt"
    elif grep -Eqi "ubuntu" /proc/version; then
        release="ubuntu"
        systemPackage="apt"
    elif grep -Eqi "centos|red hat|redhat" /proc/version; then
        release="centos"
        systemPackage="yum"
    fi

    if [[ "${checkType}" == "sysRelease" ]]; then
        if [ "${value}" == "${release}" ]; then
            return 0
        else
            return 1
        fi
    elif [[ "${checkType}" == "packageManager" ]]; then
        if [ "${value}" == "${systemPackage}" ]; then
            return 0
        else
            return 1
        fi
    fi
}

getversion(){
    if [[ -s /etc/redhat-release ]]; then
        grep -oE  "[0-9.]+" /etc/redhat-release
    else
        grep -oE  "[0-9.]+" /etc/issue
    fi
}

centosversion(){
    if check_sys sysRelease centos; then
        local code=$1
        local version="$(getversion)"
        local main_ver=${version%%.*}
        if [ "$main_ver" == "$code" ]; then
            return 0
        else
            return 1
        fi
    else
        return 1
    fi
}

debianversion(){
    if check_sys sysRelease debian;then
        local version=$( get_opsy )
        local code=${1}
        local main_ver=$( echo ${version} | sed 's/[^0-9]//g')
        if [ "${main_ver}" == "${code}" ];then
            return 0
        else
            return 1
        fi
    else
        return 1
    fi
}

error_detect_depends(){
    local command=$1
    local depend=`echo "${command}" | awk '{print $4}'`
    echo -e "[${green}Info${plain}] Starting to install package ${depend}"
    ${command} > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo -e "[${red}Error${plain}] Failed to install ${red}${depend}${plain}"
        echo "Please visit: https://github.com/shi-yang/jnoj/wiki and contact."
        exit 1
    fi
}


install_dependencies(){
    if check_sys packageManager yum; then
        echo -e "[${green}Info${plain}] Checking the EPEL repository..."
        if [ ! -f /etc/yum.repos.d/epel.repo ]; then
            yum install -y epel-release > /dev/null 2>&1
        fi
        [ ! -f /etc/yum.repos.d/epel.repo ] && echo -e "[${red}Error${plain}] Install EPEL repository failed, please check it." && exit 1
        [ ! "$(command -v yum-config-manager)" ] && yum install -y yum-utils > /dev/null 2>&1
        [ x"$(yum-config-manager epel | grep -w enabled | awk '{print $3}')" != x"True" ] && yum-config-manager --enable epel > /dev/null 2>&1
        rpm -Uvh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm
        rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
        echo -e "[${green}Info${plain}] Checking the EPEL repository complete..."

        yum_depends=(
            nginx
            php72w-cli php72w-fpm php72w-gd php72w-mbstring php72w-mysqlnd php72w-xml
            mariadb mariadb-devel mariadb-server
            gcc-c++ mysql-devel glibc-static libstdc++-static git
            java-1.8.0-openjdk java-1.8.0-openjdk-devel
            python36
        )
        for depend in ${yum_depends[@]}; do
            error_detect_depends "yum -y install ${depend}"
        done
        ln -s /usr/bin/python3.6 /usr/bin/python3
    elif check_sys packageManager apt; then
        apt_depends=(
            nginx
            mysql-server
            php-fpm php-mysql php-common php-gd php-zip php-mbstring php-xml
            libmysqlclient-dev libmysql++-dev git
        )
        ver=`echo "$(getversion)" | awk -F '.' '{print $1}'`
        if [ $ver -le 16 ]; then
           apt_depends[${#apt_depends[@]}]="openjdk-8-jdk"
        else
           apt_depends[${#apt_depends[@]}]="openjdk-11-jdk"
        fi

        apt -y update
        for depend in ${apt_depends[@]}; do
            error_detect_depends "apt -y install ${depend}"
        done
    fi
}

install_check(){
    if (! check_sys packageManager yum && ! check_sys packageManager apt) || centosversion 5; then
        echo -e "[${red}Error${plain}] Your OS is not supported to run it!"
        echo "Please change to CentOS 6+/Debian 7+/Ubuntu 16+ and try again."
        exit 1
    fi
}

config_jnoj(){
    DBNAME="jnoj"
    DBUSER="root"
    DBPASS="123456"
    if check_sys sysRelease centos; then
        DBUSER="root"
        DBPASS=`tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1`
        service mariadb start
        mysqladmin -u root password $DBPASS
        sed -i "s/post_max_size = 8M/post_max_size = 64M/g" /etc/php.ini
        sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 64M/g" /etc/php.ini
    else
        DBUSER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk  '{print $3}'`
        DBPASS=`cat /etc/mysql/debian.cnf |grep password|head -1|awk  '{print $3}'`
        PHP_VERSION=7.`php -v>&1|awk '{print $2}'|awk -F '.' '{print $2}'`
        sed -i "s/post_max_size = 8M/post_max_size = 128M/g" /etc/php/${PHP_VERSION}/fpm/php.ini
        sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 128M/g" /etc/php/${PHP_VERSION}/fpm/php.ini

        sed -i "s:root /var/www/html;:root /home/judge/jnoj/web;:g" /etc/nginx/sites-enabled/default
        sed -i "s:try_files \$uri \$uri/ =404;:try_files \$uri \$uri/ /index.php?\$args;:g" /etc/nginx/sites-enabled/default
        sed -i "s:index index.html:index index.php:g" /etc/nginx/sites-enabled/default
        sed -i "s:#location ~ \\\.php\\$:location ~ \\\.php\\$:g" /etc/nginx/sites-enabled/default
        sed -i "s:#\tinclude snippets:\tinclude snippets:g" /etc/nginx/sites-enabled/default
        sed -i "s|#\tfastcgi_pass unix|\tfastcgi_pass unix|g" /etc/nginx/sites-enabled/default
        sed -i "s:}#added_by_jnoj::g" /etc/nginx/sites-enabled/default
        sed -i "s:php7.0:php${PHP_VERSION}:g" /etc/nginx/sites-enabled/default
        sed -i "s|# deny access to .htaccess files|}#added by jnoj\n\n\n\t# deny access to .htaccess files|g" /etc/nginx/sites-enabled/default
        /etc/init.d/nginx restart
        sed -i "s/post_max_size = 8M/post_max_size = 80M/g" /etc/php/${PHP_VERSION}/fpm/php.ini
        sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 80M/g" /etc/php/${PHP_VERSION}/fpm/php.ini
    fi

    # Modify database information
    sed -i "s/root/$DBUSER/g" /home/judge/jnoj/config/db.php
    sed -i "s/123456/$DBPASS/g" /home/judge/jnoj/config/db.php

    mysql -h localhost -u$DBUSER -p$DBPASS -e "create database jnoj;"
}

config_firewall(){
    # open http/https services.
    firewall-cmd --permanent --add-service=http --add-service=https --zone=public

    # reload firewall config
    firewall-cmd --reload
}

enable_server(){
    PHP_VERSION=7.`php -v>&1|awk '{print $2}'|awk -F '.' '{print $2}'`
    # startup service
    systemctl start nginx
    systemctl start mysql
    systemctl start php${PHP_VERSION}-fpm

    # startup service when booting.
    systemctl enable nginx
    systemctl enable mysql
    systemctl enable php${PHP_VERSION}-fpm
}

install_jnoj(){
    disable_selinux
    install_check
    install_dependencies

    /usr/sbin/useradd -m -u 1536 judge
    cd /home/judge/
    git clone https://gitee.com/shi-yang/jnoj.git

    config_jnoj
    if check_sys packageManager yum; then
        config_firewall
    fi
    enable_server

    cd /home/judge/jnoj
    adminPass=`cat /proc/sys/kernel/random/uuid | sed 's/[^a-zA-Z0-9]//g'`
    adminPass=${adminPass:1:9}
    echo -e "yes" "\n" "admin" "\n" "${adminPass}" "\n" "admin@jnoj.org" | ./yii install
    cd /home/judge/jnoj/judge
    make
    ./dispatcher
    cd /home/judge/jnoj/polygon
    make
    ./polygon

    ip=`curl ip.6655.com/ip.aspx`
    echo
    echo "Successful installation"
    echo "App running at:"
    echo "http://${ip}"
    echo
    echo -e "[${green}Administrator account${plain}] admin"
    echo -e "[${green}Password${plain}] ${adminPass}"
    echo
    echo "Enjoy it!"
    echo "Welcome to visit: https://www.jnoj.org"
    echo
}

install_jnoj
