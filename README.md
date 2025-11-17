# Simple hCaptcha

A simple PHP library to integrate [hCaptcha](https://hcaptcha.com) in your PHP applications.

## Requirements

- PHP 7.4 or higher
- cURL extension
- JSON extension

## Installation

Install via Composer:

```bash
composer require shevabam/hcaptcha-php
```

## Usage

### Basic Usage

```php
<?php
require 'vendor/autoload.php';

use SimpleHcaptcha\Hcaptcha;

// Initialize with your site key and secret key
$hcaptcha = new Hcaptcha('your-site-key', 'your-secret-key');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hcaptchaResponse = $_POST['h-captcha-response'] ?? '';
    
    // Remote IP is optional, but recommended
    if ($hcaptcha->isValid($hcaptchaResponse, $_SERVER['REMOTE_ADDR'])) {
        // CAPTCHA validation passed, process the form
        echo "Form submitted successfully!";
    } else {
        // CAPTCHA validation failed
        echo "CAPTCHA verification failed: " . $hcaptcha->getError();
    }
}
?>

<!-- Your HTML form -->
<html>
<head>
    <?php echo $hcaptcha->getScript(); ?>
</head>
<body>
    <form method="post">
        <!-- Your form fields -->
        
        <!-- Display hCaptcha widget -->
        <?php echo $hcaptcha->display(); ?>
        
        <button type="submit">Submit</button>
    </form>
</body>
</html>
```

### Available Methods

#### `getScript(): string`
Generate the HTML code to include the hCaptcha script.

#### `display(): string`
Generate the HTML code to display the hCaptcha widget.

#### `isValid(string $response, ?string $remoteIp = null): bool`
Verify if the CAPTCHA response is valid.

#### `getError(): string`
Get the error message of the last operation.

#### `getErrorCode(): ?string`
Get the error code of the last operation.

#### `setTheme(string $theme): self`
Set the theme of the hCaptcha widget ('light' or 'dark').

#### `setSize(string $size): self`
Set the size of the hCaptcha widget ('normal' or 'compact').

#### `setLanguage(string $language): self`
Set the language of the hCaptcha widget.

#### `setOption(string $key, $value): self`
Set a custom option.


## Configuration

You can configure the hCaptcha widget by passing an array of options to the constructor or using the setter methods:

```php
$hcaptcha = new Hcaptcha('your-site-key', 'your-secret-key', [
    'theme' => 'dark',
    'size' => 'normal',
    'language' => 'fr'
]);

// Or using setter methods
$hcaptcha
    ->setTheme('dark')
    ->setSize('normal')
    ->setLanguage('fr');
```


## Usage

To generate the *script* tag, use :

```php
$hcaptcha->getScript();
```

To display the *widget* in your form, use :

```php
$hcaptcha->display();
```

To verify the CAPTCHA response, use :

```php
if ($hcaptcha->isValid($_POST['h-captcha-response'], $_SERVER['REMOTE_ADDR'])) {
    // CAPTCHA validation passed, process the form
    echo "Form submitted successfully!";
} else {
    // CAPTCHA validation failed
    echo "CAPTCHA verification failed: " . $hcaptcha->getError();
}
```


## Error Handling

When `isValid()` returns `false`, you can get the error message using `getError()`:

```php
if (!$hcaptcha->isValid($response)) {
    $error = $hcaptcha->getError();
    // Handle the error
}
```


## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.


## Author

Created by [@shevabam](https://github.com/shevabam)

- [X](https://x.com/shevabam)
- [Bluesky](https://bsky.app/profile/shevabam.bsky.social)
- [Mastodon](https://mastodon.social/web/@shevabam)
- [Projects](https://shevabam.fr)
