<?php

//Config::mysql(/*identified name(optional)*/)->database(/*Database*/)->user(/*User*/)->password(/*Password*/)->backup()->abort(/*Code*/)->default(); // Define a database

//Config::dev(true) // If true, activates developer mode.
//      ->errors()  // Displays errors that have occurred
//      ->mail(/*E-Mail Address*/) // It reports important problems by mail.
//      ->version('2020-01-30');   // Runs the version of the site on the date entered.

//Config::auth() // Auto user detection
      //->db('mysql','name') // Database type and name (Optional)
      //->table('tb_user') // User table name
      //->key('id') // Primary key column in the user table (Optional)
      //->token(); // Cookie table name (Optional)

Config::language(/* Default Language (en) */); // Activates language files (/lang)
Config::composer(); // True or autoload.php directory can be entered.
Config::backup();  // Takes daily backups
Config::update(); // Candy PHP updates when new version is released
Config::brute(); // Brute Force protection
Config::cron(); // Activates scheduled tasks
Config::key(/*Key*/); // A key must be entered for the encryption method. It can only be decrypted with the same key.
