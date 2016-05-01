Script files for managing Bolt installs
=======================================

### Compiling PHAR

```
./bin/compile
```

### Configuration File

#### Location

The configuration file can either be specified with the `--config=filename.yml` 
option on the command line.
 
If no option is specified on the command line, `.deploy.yml` will be looked for
first in the current directory, followed by `/etc/deploy.yml`, and finally 
`.deploy.yml` in the users home directory.

#### Layout

```
binaries:
    git: /usr/bin/git
    rsync: /usr/bin/rsync
    setfacl: /usr/bin/setfacl

permissions:
    user: nginx
    group: nginx

acls:
    users:
        - nginx
        - gawain
    groups:
        - nginx
        - gawain

sites:
    example:
        paths:
            site: /var/www/sites/example.com
            source: /data/example.com
            backup: /backup/example.com
        backup: true
        exclude:
            - vendor
            - README.md
```

The deployment for `example` can then be triggered by running:

```
php deploy.phar example
```
