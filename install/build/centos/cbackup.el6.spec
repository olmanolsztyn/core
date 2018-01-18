%define _\_os\_install_post %{nil}
%define __jar_repack %{nil}
%define _tdir /opt/cbackup
%define _libdir /usr/lib

Summary:        cBackup Network Equipment Configuration Backup
Name:           cbackup
Provides:       cbackup
Version:        1.0.0
Release:        1%{?dist}
License:        AGPLv3
URL:            http://cbackup.me
Group:          Archiving/Backup
Requires:       httpd
Requires:       git
Requires:       jre >= 1.8.0
Requires:       php >= 7.0.0
Requires:       mysql >= 5.5
Requires:       php-mbstring, php-intl, php-snmp, php-ssh2, php-zip, php-curl, php-gmp, php-mysqlnd
Source0:        %{name}-%{version}.tar.gz
BuildArch:      noarch

%description
cBackup is an Open source network equipment configuration backup software
for multiple platforms and devices. It provides not only backing up data
but also version control for tracking changes in devices' configurations.
Our team provides support on forums http://cbackup.me. Also we are adding
new devices to supported equipment list.

%prep
%setup -q

%install
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

mkdir -p ${RPM_BUILD_ROOT}%{_tdir}
mkdir -p ${RPM_BUILD_ROOT}%{_sysconfdir}/httpd/conf.d
mkdir -p ${RPM_BUILD_ROOT}%{_sysconfdir}/logrotate.d
mkdir -p ${RPM_BUILD_ROOT}%{_sysconfdir}/sudoers.d

cp -R . ${RPM_BUILD_ROOT}%{_tdir}
mv "${RPM_BUILD_ROOT}%{_tdir}/install/system/centos/initd/etc/logrotate.d/cbackup"  "${RPM_BUILD_ROOT}%{_sysconfdir}/logrotate.d"
mv "${RPM_BUILD_ROOT}%{_tdir}/install/system/centos/initd/etc/sudoers.d/cbackup"  "${RPM_BUILD_ROOT}%{_sysconfdir}/sudoers.d"
mv "${RPM_BUILD_ROOT}%{_tdir}/install/system/ubuntu/etc/apache2/sites-available/cbackup.conf" "${RPM_BUILD_ROOT}%{_sysconfdir}/httpd/conf.d/cbackup.conf"
rm -rf "${RPM_BUILD_ROOT}%{_tdir}/install/system"
rm -rf "${RPM_BUILD_ROOT}%{_tdir}/install/build"
rm -rf "%{_builddir}%{_tdir}/install/system"
rm -rf "%{_builddir}%{_tdir}/install/build"

%pre
if [ -d /opt/cbackup ]; then
    if [ ! -z "$(ls -A %{_tdir})" ] && [ "$1" = 1 ]; then
        echo "%{_tdir} is not empty, unable to proceed" > /dev/stderr
        exit 1
    fi
fi

# Group
if getent group %{name} > /dev/null 2>&1; then
    echo "System usergroup '%{name}' already exists"
else
    echo "Adding system usergroup '%{name}'"
    groupadd -r %{name}
fi

# User
if getent passwd %{name} > /dev/null 2>&1; then
    echo "System user '%{name}' already exists"
else
    echo "Adding system user '%{name}'"
    # update password after RPM installation (system restriction)
    # manipulating with /dev/tty is generally a bad idea
    useradd -r -g %{name} -G apache -d %{_tdir} -s /bin/bash -c "cBackup System User" %{name}
fi

# If upgrade
case "$1" in
    2)
        if pkill -0 -f cbackup 2>/dev/null ; then
            /sbin/service cbackup stop
        fi
    ;;
esac

%post
# clean assets
if [ -d %{_tdir}/web/assets ]; then
    find %{_tdir}/web/assets/. -mindepth 1 -maxdepth 1 -type d -exec rm -rf '{}' ';'
fi

# Chmod jar file and app/bin
chmod +x %{_tdir}/bin/cbackup.jar
chmod +x %{_tdir}/yii
chmod 775 %{_tdir}/bin

# Create sysV symlink
ln -s %{_tdir}/bin/cbackup.jar /etc/init.d/cbackup

case "$1" in
    # if install
    1)
        HOST=`hostname -f 2> /dev/null || hostname`
        echo "Next steps: "
        echo ""
        echo "[set password for cbackup user]"
        echo " sudo passwd %{name}"
        echo ""
        echo "[restart web server]"
        echo " sudo service httpd restart"
        echo ""
        echo "[finish cBackup web core installation]"
        echo " http://$HOST/cbackup/index.php"
        echo ""
        echo "[start and enable cbackup daemon]"
        echo " sudo service cbackup start"
        echo " sudo chkconfig cbackup on"
    ;;
    # if upgrade
    2)
        /sbin/service cbackup start
    ;;
esac

%preun
chkconfig cbackup off &> /dev/null

if pkill -0 -f cbackup 2>/dev/null ; then
    /sbin/service cbackup stop &> /dev/null
fi

%postun
/sbin/service httpd graceful

if [ -f %{_tdir}/install.lock ]; then
    rm -f %{_tdir}/install.lock
fi

if [ -L %{_sysconfdir}/init.d/%{name} ]; then
    rm -f %{_sysconfdir}/init.d/%{name}
fi

find /etc/rc.d/. -name 'K*cbackup' -exec rm -f '{}' ';'

if [ -d %{_tdir}/.git ]; then
    rm -rf %{_tdir}/.git
fi

if [ -d /var/run/%{name} ]; then
    rm -rf /var/run/%{name}
fi

%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT
[ %{_builddir} != "" ] && [ %{_builddir} != "/" ] && rm -rf %{_builddir}/*

%files
%defattr(-,apache,apache,-)
%{_tdir}/.
%config(noreplace) %{_tdir}/config/db.php
%defattr(-,root,root,-)
%{_sysconfdir}/httpd/conf.d/*
%{_sysconfdir}/logrotate.d/*
%{_sysconfdir}/sudoers.d/*
