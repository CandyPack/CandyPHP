<?php

$config->displayError();   // Oluşan hataları gösterir
$config->languageDetect(); // Dil dosyalarını yükler (/lang)
$config->cronJobs(true);   // Zamanlanmış görevleri aktifleştirir
$config->autoBackup();     // Otomatik yedeklemeyi aktifleştirir

//$config->mysqlDatabase(''); // MySql veritabanı adı
//$config->mysqlUsername(''); // MySql kullanıcı adı
//$config->mysqlPassword(''); // MySql Parolası
//$config->mysqlConnection(); // Otomatik Mysql bağlantısı
