CFFie
=====

CFFie query SBB/CFF/FFS (http://www.sbb.ch) connections.

![CFFie in action!](cffie.png)

Installation
------------

```
$ sudo curl -LsS https://github.com/maidmaid/cffie/releases/download/v0.2.0/cffie.phar -o /usr/local/bin/cffie
$ sudo chmod a+x /usr/local/bin/cffie
```

Tips
----

Use ``--notify`` option to show desktop notification :

![notification](doc/notification.png)

Create a ``cff`` alias :

```
alias cff='cffie query --notify'
```

License
-------

CFFie is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.