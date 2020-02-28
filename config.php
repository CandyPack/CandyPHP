<?php

$config->displayError();   // Oluşan hataları gösterir
$config->languageDetect(); // Dil dosyalarını yükler (/lang)
$config->cronJobs(true);   // Zamanlanmış görevleri aktifleştirir
$config->autoBackup();     // Otomatik yedeklemeyi aktifleştirir
$config->composer(false);  // True veya autoload.php dizini girilebilir.

//$config->mysqlDatabase(''); // MySql veritabanı adı
//$config->mysqlUsername(''); // MySql kullanıcı adı
//$config->mysqlPassword(''); // MySql Parolası
//$config->mysqlConnection(); // Otomatik Mysql bağlantısı

$config->autoBackup();  // Günlük yedek alır
$config->autoUpdate();  // Candy PHP yeni sürümü çıktığında günceller
