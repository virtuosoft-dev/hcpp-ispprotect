# hcpp-ispprotect
A plugin for Hestia Control Panel (via hestiacp-pluginable) that installs ISPProtect and BanDaemon.

## Installation
HCPP-ISPProtect requires an Ubuntu or Debian based installation of [Hestia Control Panel](https://hestiacp.com) in addition to an installation of [HestiaCP-Pluginable](https://github.com/virtuosoft-dev/hestiacp-pluginable) to function; please ensure that you have first installed pluginable on your Hestia Control Panel before proceeding. Switch to a root user and simply clone this project to the /usr/local/hestia/plugins folder. It should appear as a subfolder with the name `ispprotect`, i.e. `/usr/local/hestia/plugins/ispprotect`.

First, switch to root user:
```
sudo -s
```

Then simply clone the repo to your plugins folder, with the name `ispprotect`:

```
cd /usr/local/hestia/plugins
git clone https://github.com/virtuosoft-dev/hcpp-ispprotect ispprotect
```

Note: It is important that the destination plugin folder name is `ispprotect`.

## TODO:
* Install BanDaemon
* Configure script to cycle through clients and perform optimized scan/ban analysis.
