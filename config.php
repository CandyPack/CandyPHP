<?php

Config::displayError();   // Oluşan hataları gösterir
Config::languageDetect(); // Dil dosyalarını yükler (/lang)
Config::cronJobs();       // Zamanlanmış görevleri aktifleştirir
Config::composer();       // True veya autoload.php dizini girilebilir.
Config::masterMail(/*E-Mail Adresi*/); // Oluşan önemli sorunları mail ile raporlar.
  
//Config::mysql(/*tanımlanan isim(opsiyonel)*/)->database(/*Veritabanı*/)->user(/*Kullanıcı*/)->password(/*Parola*/)->default(); // Veritabanı tanımlar

//Config::devmode(true) // True ise geliştirici modunu aktifleştirir
//        ->errors() // Oluşan hataları görüntüler
//        ->version('2020-01-30'); // Sitenin girilen tarihteki versiyonunu çalıştırır.

Config::autoBackup();  // Günlük yedek alır
Config::autoUpdate();  // Candy PHP yeni sürümü çıktığında günceller
