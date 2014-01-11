GMModular
=========

Modular support for Game-Maker Studio projects

Usage
=====
This script is written in PHP.
The best way to execute this script is by using cygwin on Windows, or just use the terminal if you're using Linux / Mac.

The basic usage of the script is shown if you run it without arguments, or if you run it with the "--help" flag.

    #Example:
    php gmmodular.php --help

''Cyqwin''
# Download cygwin from here; http://cygwin.com/install.html
# If you have PHP installed on your windows machine, you can skip the next step.
# When installing, make sure you have selected all the "PHP" related packages. This installs PHP for CygWin.

''Linux''
# Make sure you have PHP binaries installed. This is easily done by using yum or aptitude. Examples;

    apt-get install php5

    yum install php5

''Mac OS''
# I have no idea to be honest. Be sure to have installed PHP on your mac so you can use it, then run the script with PHP.

Adding submodules
=================
If you want to add submodules to your game, you should create a folder names "submodules" in the project root. (The same level as the .gmx file). In that folder you can put other project folders. You can simply copy them, but I suggest that you use "git submodules" for this. Ofcourse you could also use composer or some other tool of choise. As long as all the project files are a 1 on 1 copy of the original Game-Maker source.

How does this work?
===================
This script copies all assets and asset-settings from your submodules into specific folders in your main project. It basically merges all projects, and keeps track of changes.

BE WARNED
=========
As this script edits your main project, just be sure to ''ALWAYS HAVE A BACKUP''. Using version control software is the best way. The other way is to copy your project to DropBox, USB, DVD, CD, 3,5" floppies, Tape, ZIP-Drive... or whatever you'd like.

Just be aware that this script MAY NOT be fault-proof and MAY mess up your project. May this ever happen, please leave an issue in the issuetracker.