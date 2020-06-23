<?php

Config::displayError();   // Oluşan hataları gösterir
Config::languageDetect(); // Dil dosyalarını yükler (/lang)
Config::cronJobs();       // Zamanlanmış görevleri aktifleştirir
Config::composer();       // True veya autoload.php dizini girilebilir.
Config::masterMail(/*E-Mail Adresi*/); // Oluşan önemli sorunları mail ile raporlar.
  
//Config::mysql(/*tanımlanan isim(opsiyonel)*/)->database(/*Veritabanı*/)->user(/*Kullanıcı*/)->password(/*Parola*/); // Veritabanı tanımlar
//Config::mysqlConnection(); // Otomatik Mysql bağlantısı

Config::autoBackup();  // Günlük yedek alır
Config::autoUpdate();  // Candy PHP yeni sürümü çıktığında günceller
