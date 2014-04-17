GMModular
=========

Modular support for Game-Maker Studio projects

Usage
=====
This script is written in PHP.
The best way to execute this script is by using cygwin on Windows, or just use the terminal if you're using Linux / Mac.

The basic usage of the script is shown if you run it without arguments, or if you run it with the "--help" flag.

```
#Example:
php gmmodular.php --help
```

### Cyqwin / Windows
1. Download cygwin from here; http://cygwin.com/install.html
2. If you have PHP installed on your windows machine, you can skip the next step.
3. When installing, make sure you have selected all the "PHP" related packages. This installs PHP for CygWin.

### Linux
1. Make sure you have PHP binaries installed. This is easily done by using yum or aptitude. Examples;

```
apt-get install php5
```

```
yum install php5
```

### Mac OS
1. I have no idea to be honest. Be sure to have installed PHP on your mac so you can use it, then run the script with PHP.

Adding submodules
=================
If you want to add submodules to your game, you should create a folder names "submodules" in the project root. (The same level as the .gmx file). In that folder you can put other project folders. You can simply copy them, but I suggest that you use "git submodules" for this. Ofcourse you could also use composer or some other tool of choise. As long as all the project files are a 1 on 1 copy of the original Game-Maker source.

After throwing your project files into the submodules directory (like this;)


     + MyProject.gmx
      \_ sprites
      |_ scripts
      |_ submodules
        |_ Shaderpack.gmx
          |_ Shaderpack.project.gmx (file)
        |_ GMOculus.gmx
          |_ GMOculus.project.gmx (file)
      |_ MyProject.project.gmx (file)

You start up the app;
``./gmmodular.php /path/to/Myproject.gmx/``

Now you're in the menu. Choose "install" and then choose the modules you want to install.
You are now done! The modules will be included in your main project and you're ready to use them.


How does this work?
===================
This script copies all assets and asset-settings from your submodules into specific folders in your main project. It basically merges all projects, and keeps track of changes.

Creating submodules
===================
As this combines everything... I suggest you prefix all your game assets if you want others to use them. Don't use names like sprite0, or obj_controller - prefix your object with something related to your module, eg;

NOTES
=====
1. When copying constants - we only copy the "All Configurations" constants from a submodule. We can't know which one to use if you have multiple configs. When creating something thats supposed to be used as a module, please put all the needed constants into the "All Configurations" part of constants.
2. We will not copy any game config (from the config folder) - only all the assets you see in the left bar when you open GM.
3. You can leave the GM:S IDE opened while installing the module - but it can cause broken files (i've seen this in rare cases) - so, just in case, its better to just close your GM:S window.

BE WARNED
=========
As this script edits your main project, just be sure to **ALWAYS HAVE A BACKUP**. Using version control software is the best way. The other way is to copy your project to DropBox, USB, DVD, CD, 3,5" floppies, Tape, ZIP-Drive... or whatever you'd like.

Just be aware that this script _MAY NOT_ be fault-proof and _MAY_ mess up your project. May this ever happen, please leave an issue in the issuetracker.
