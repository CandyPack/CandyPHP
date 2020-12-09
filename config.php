<?php

Config::languageDetect(/* Default Language (en) */); // Activates language files (/lang)
Config::cronJobs();       // Activates scheduled tasks
Config::composer();       // True or autoload.php directory can be entered.
Config::key(/*Key*/);     // A key must be entered for the encryption method. It can only be decrypted with the same key.
  
//Config::mysql(/*identified name(optional)*/)->database(/*Database*/)->user(/*User*/)->password(/*Password*/)->default(); // Define a database

//Config::devmode(true) // If true, activates developer mode.
//        ->errors()    // Displays errors that have occurred
//        ->mail(/*E-Mail Address*/) // It reports important problems by mail.
//        ->version('2020-01-30');   // Runs the version of the site on the date entered.

Config::autoBackup();  // Takes daily backups
Config::autoUpdate();  // Candy PHP updates when new version is released
