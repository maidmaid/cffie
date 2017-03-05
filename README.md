<p align="center">
   <img src="cffie.png" width="400">
</p>

**CFFie** query SBB/CFF/FFS ([cff.ch](https://www.cff.ch)) connections.

Installation
------------

```
$ sudo curl -LsS https://github.com/maidmaid/cffie/releases/download/v0.3.0/cffie.phar -o /usr/local/bin/cffie
$ sudo chmod a+x /usr/local/bin/cffie
```

Tips
----

- Use ``--notify`` option to show desktop notification:
  <p align="center">
     <img src="doc/notification.png" width="400">
  </p>

- *Watch* connections in continuous:
  ```
  watch -ctn 30 cffie query --ansi Lausanne Zurich
  ```

- Create useful aliases:
  ```
  alias cff='cffie query --notify'
  alias cffw='watch -ctn 30 cffie query --ansi'
  ```


License
-------

CFFie is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.