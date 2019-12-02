### Config
Ana dizinde config.php içerisinde yapabileceğiniz site ön ayarlarıdır.

```php
$config->displayError(boolean); // True veya False değer girilebilir (Varsayılan True)
// Oluşan hataları ekranda görüntüler.
```
```php
$config->languageDetect();
// Ziyaretçi için uygun dil dosyalarını yükler (lang dizini içerisindeki)
```
```php
$config->cronJobs(boolean); // True veya False değer girilebilir (Varsayılan True)
// Zamanlanmış görevleri aktifleştirir. Ön ayar yapmaya gerek yoktur.
```
```php
$config->autoBackup(boolean, dizin); // Boolean değer ve özel dizin girilebilir
// Otomatik dosya ve mysql yedeklemesini etkinleştirir. (Varsayılan dizin ../backup/)
```
```php
$config->autoUpdate(); // 
// Candy PHP sistem dosyalarını otomatik güncelleştirir.
```
```php
$config->mysqlDatabase(''); // Otomatik mysql bağlantısı için zorunludur!
// Bağlantı kurulacak mysql database adı
```
```php
$config->mysqlUsername(''); // Otomatik mysql bağlantısı için zorunludur!
// Bağlantı kurulacak mysql kullanıcı adı
```
```php
$config->mysqlPassword(''); // Otomatik mysql bağlantısı için zorunludur!
// Bağlantı kurulacak mysql parolası
```
```php
$config->mysqlConnection();
// Otomatik mysql bağlantısı gerçekleştirir
```
