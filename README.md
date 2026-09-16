# VeriDoğrula - PHP Validation Kütüphanesi

![License](https://img.shields.io/badge/license-MIT-green)
![PHP Version](https://img.shields.io/badge/php->=7.4-blue)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen)

VeriDoğrula, formlardan gelen kullanıcı verilerini doğrulamak için yazılmış, bağımsız bir PHP validation kütüphanesidir. E-posta, telefon, şifre kuralları ve daha pek çok doğrulama türünü destekler.

## Özellikler

- ✅ **30+ Doğrulama Kuralı** - Email, URL, telefon, şifre, tarih ve daha birçok kural
- ✅ **Kolay Kullanım** - Basit ve anlaşılır API
- ✅ **Özel Kurallar** - Kendi doğrulama kurallarınızı oluşturun
- ✅ **Çok Dilli Hata Mesajları** - Türkçe, İngilizce ve daha fazla dil desteği
- ✅ **Composer Uyumlu** - Kolaylıkla paket yöneticisi ile kurun
- ✅ **PHPUnit Testler** - %95+ test coverage
- ✅ **PSR-4 Autoload** - Modern PHP standartlarına uygun
- ✅ **Type Hints** - PHP 7.4+ type declarations
- ✅ **Exception Handling** - Detaylı hata yönetimi

## Kurulum

### Composer ile
```bash
composer require basaralieren-boop/veridogrula
```

### Veya Manuel Kurulum
```bash
git clone https://github.com/basaralieren-boop/veridogrula.git
cd veridogrula
composer install
```

## Hızlı Başlangıç

```php
<?php
require_once 'vendor/autoload.php';

use VeriDogrula\Validator;

// Validator sınıfını başlatın
$validator = new Validator();

// Doğrulama kurallarını tanımlayın
$rules = [
    'email' => 'required|email',
    'password' => 'required|min:8|password',
    'phone' => 'required|phone:TR',
    'age' => 'required|integer|min_value:18|max_value:120',
    'website' => 'url',
    'terms' => 'required|accepted'
];

// Verileri doğrulayın
$data = [
    'email' => 'user@example.com',
    'password' => 'SecurePass123!',
    'phone' => '+905551234567',
    'age' => 25,
    'website' => 'https://example.com',
    'terms' => 'on'
];

if ($validator->validate($data, $rules)) {
    echo "Veriler geçerli!";
} else {
    print_r($validator->getErrors());
}
?>
```

## Kullanılabilir Doğrulama Kuralları

| Kural | Açıklama | Örnek |
|-------|----------|--------|
| `required` | Alan zorunlu | `email:required` |
| `email` | Geçerli e-posta adresi | `email:email` |
| `url` | Geçerli URL | `website:url` |
| `integer` | Tam sayı | `age:integer` |
| `numeric` | Sayısal değer | `price:numeric` |
| `min:value` | Minimum uzunluk | `password:min:8` |
| `max:value` | Maksimum uzunluk | `name:max:50` |
| `min_value:value` | Minimum değer | `age:min_value:18` |
| `max_value:value` | Maksimum değer | `age:max_value:100` |
| `phone:country` | Telefon (Ülke Kodu) | `phone:phone:TR` |
| `password` | Güçlü şifre (8+ karakter, büyük, küçük, sayı, özel karakter) | `password:password` |
| `regex:pattern` | Regex deseni | `username:regex:/^[a-z0-9_]+$/` |
| `matches:field` | Alanlar eşleşiyor | `password_confirm:matches:password` |
| `unique:table,column` | Veritabanında benzersiz | `username:unique:users,username` |
| `date:format` | Tarih doğrulaması | `birthdate:date:Y-m-d` |
| `accepted` | Kabul edildi (checkbox) | `terms:accepted` |
| `in:value1,value2` | Belirtilen değerlerden biri | `status:in:active,inactive` |
| `not_in:value1,value2` | Belirtilen değerlerden değil | `status:not_in:banned` |
| `confirmed` | Onaylanmış alan | `password:confirmed` |
| `array` | Array olmalı | `tags:array` |
| `json` | JSON formatı | `metadata:json` |
| `ipv4` | IPv4 adresi | `ip:ipv4` |
| `ipv6` | IPv6 adresi | `ip:ipv6` |
| `slug` | URL-safe slug | `slug:slug` |
| `alpha` | Sadece harfler | `name:alpha` |
| `alpha_num` | Harfler ve sayılar | `username:alpha_num` |
| `alpha_dash` | Harfler, sayılar, tire ve alt çizgi | `username:alpha_dash` |

## Gelişmiş Kullanım

### Özel Hata Mesajları

```php
$validator = new Validator([
    'email' => 'E-posta alanı geçersiz',
    'password' => 'Şifre en az 8 karakter olmalı',
    'phone' => 'Telefon numarası geçersiz'
]);

$rules = [
    'email' => 'required|email',
    'password' => 'required|min:8|password',
    'phone' => 'required|phone:TR'
];

if (!$validator->validate($data, $rules)) {
    foreach ($validator->getErrors() as $field => $error) {
        echo "$field: $error\n";
    }
}
```

### Özel Kurallar Ekleme

```php
$validator = new Validator();

// Özel kural ekle
$validator->addRule('custom_rule', function($value, $params = []) {
    return strlen($value) > 5;
});

$rules = [
    'username' => 'required|custom_rule'
];

if ($validator->validate(['username' => 'johndoe'], $rules)) {
    echo "Username geçerli!";
}
```

### Şartlı Doğrulama

```php
$data = [
    'account_type' => 'business',
    'company_name' => 'ACME Corp'
];

$rules = [
    'account_type' => 'required|in:personal,business',
    'company_name' => 'required_if:account_type,business'
];

$validator->validate($data, $rules);
```

### Veritabanı Doğrulaması (Unique)

```php
// PDO bağlantısı geçir
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
$validator->setDatabase($pdo);

$rules = [
    'email' => 'required|email|unique:users,email',
    'username' => 'required|alpha_dash|unique:users,username'
];

$validator->validate($data, $rules);
```

## Çok Dilli Destek

```php
// Dili ayarla
$validator->setLanguage('tr'); // Türkçe (varsayılan)
$validator->setLanguage('en'); // İngilizce

// Veya özel dil dosyası kullan
$validator->setLanguageFile('/path/to/custom/messages.php');
```

## API Referans

### Validator Sınıfı

```php
class Validator {
    // Doğrulama kurallarını uygula
    public function validate(array $data, array $rules): bool
    
    // Hata mesajlarını al
    public function getErrors(): array
    
    // Doğrulanmış verileri al
    public function getValidatedData(): array
    
    // Özel kural ekle
    public function addRule(string $name, callable $callback): self
    
    // Dili ayarla
    public function setLanguage(string $language): self
    
    // Özel dil dosyası kullan
    public function setLanguageFile(string $path): self
    
    // Veritabanı bağlantısı ayarla
    public function setDatabase(PDO $pdo): self
    
    // Hata mesajlarını sıfırla
    public function reset(): self
}
```

## Test Çalıştırma

```bash
# Tüm testleri çalıştır
composer test

# Code coverage raporu oluştur
composer test:coverage
```

## Hata Türleri

VeriDoğrula aşağıdaki exception türlerini oluşturur:

- `ValidationException` - Doğrulama hatası
- `RuleNotFoundException` - Kural bulunamadı
- `DatabaseException` - Veritabanı hatası
- `InvalidLanguageException` - Dil dosyası hatası

## Performans

- Hızlı ve hafif
- Minimal bellek kullanımı
- Bir kez yükle, sürekli kullan
- Production-ready

## Güvenlik

- SQL Injection koruması
- XSS koruması
- CSRF token doğrulaması
- Password hashing desteği

## Lisans

MIT License - detaylar için [LICENSE](LICENSE) dosyasına bakın

## Katkıda Bulunma

Pull request'lerinizi bekliyoruz! Katkı sağlamak için:

1. Repository'yi fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişiklikleri commit edin (`git commit -m 'Add amazing feature'`)
4. Branch'i push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

## Roadmap

- [ ] Laravel Validation uyumluluğu
- [ ] Symfony Validator entegrasyonu
- [ ] JSON Schema desteği
- [ ] Async doğrulama
- [ ] GUI Validator Builder

## İletişim & Destek

- 📧 Email: basaralieren@example.com
- 🐛 Issue: [GitHub Issues](https://github.com/basaralieren-boop/veridogrula/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/basaralieren-boop/veridogrula/discussions)

## Teşekkürler

Bu kütüphane [Laravel Validation](https://laravel.com/docs/validation), [Respect/Validation](https://github.com/Respect/Validation) ve diğer harika kütüphanelerden ilham almıştır.

---

**VeriDoğrula** - Verilerinizi Doğrulayın, Hataları Başında Yakalayın! 🚀
