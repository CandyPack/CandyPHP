## Candy
PHP dosyalarınız içerisinde kullanabileceğiniz genel Candy fonksiyonlarıdır.

```php
Candy::import($class);
// sys/import/import_ * .php sınıfını ekler
```
```php
Candy::set();
// controller içerisinden değişken tanımlamak için kullanılabilir.
```
```php
Candy::get();
// view içerisinden tanımlanmış değişken getirmek için kullanılabilir.
```
```php
Candy::token($check);
// POST veya GET olayları için token oluşturur.
// $check yerine 'input' yazılırsa ekrana gizli token girdisi ekler
// $check boş bırakılırsa token verisi döndürür
// $check yerine post edilen token yazılırsa token geçerliliği sonucunu döndürür (True/False)
```
```php
Candy::postCheck($post, $token, $redirect); // Post olayı kontrolü için kullanılır
// $post -> Virgül ile ayırarak birden fazla post adı girilebilir
// $token -> True girilirse token kontrolü de yapar. (Varsayılan: true)
// $redirect -> True girilirse yönlendirme kontrolü de yapar. (Varsayılan: true)
```
```php
Candy::getCheck($get, $token); // Post olayı kontrolü için kullanılır
// $get -> Virgül ile ayırarak birden fazla get adı girilebilir
// $token -> True girilirse token kontrolü de yapar. (Varsayılan: true)
```
```php
Candy::isNumeric($variable, $method); // POST veya GET verilerinin nunmerik değer olup olmadığını kontrol eder
// $variable -> String değerdir. Virgül ile ayırarak birden fazla girebilirsiniz
// $method   -> İstek methodu ('POST' / 'GET')
```
```php
Candy::direct($url); // Yönlendirme yapar
// $url -> Yönlendirilecek URL adresi
// URL boş bırakılırsa bir önceki sayfaya yönlendirir.
```
```php
Candy::uploadImage($postname,$target,$filename,$maxsize=500000); // Gönderilen resmi yükler
// $postname -> Gönderilen dosyanın POST adı
// $target   -> Dosyanın yükleneceği dizin
// $filename -> Dosyanın yüklendikten sonraki adı
// $maxsize  -> Dosyanın maksimum boyutu
```
```php
Candy::slugify($text); // $text içindeki özel karakter ve boşlukları dönüştürür
```
```php
Candy::generateFilename($filename,$extension,$path); // Dizine uygun dosya adı oluşturur
// $filename -> Dosya Adı
// $extension -> Dosya Uzantısı (.jpg)
// $path -> Dosya yolu
```
```php
Candy::arrayElementDelete($array,$element); // Diziden bir elemanı kaldırır
// $array -> Dizi
// $element -> Kaldıralacak Eleman
```
