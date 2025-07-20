<?php

namespace SimpleHcaptcha;

/**
 * Class for easy integration of hCaptcha into PHP applications
 * 
 * @version 1.0.0
 * @license MIT
 */
class Hcaptcha {
    /**
     * URL of the hCaptcha verification API
     */
    private const VERIFY_URL = 'https://hcaptcha.com/siteverify';
    
    /**
     * URL of the hCaptcha script
     */
    private const SCRIPT_URL = 'https://js.hcaptcha.com/1/api.js';
    
    /**
     * Site key
     *
     * @var string
     */
    private $siteKey;
    
    /**
     * Secret key
     *
     * @var string
     */
    private $secretKey;
    
    /**
     * Last error encountered
     *
     * @var string|null
     */
    private $error;
    
    /**
     * Configuration options
     *
     * @var array
     */
    private $options = [
        'theme' => 'light', // light or dark
        'size' => 'normal', // normal, compact or invisible
        'tabindex' => 0,
        'callback' => null,
        'expired-callback' => null,
        'error-callback' => null,
        'language' => 'en',
    ];
    
    /**
     * Custom error messages
     *
     * @var array
     */
    private $errorMessages = [
        'missing-input-secret' => 'Your secret key is missing.',
        'invalid-input-secret' => 'Your secret key is invalid or malformed.',
        'missing-input-response' => 'Please complete the CAPTCHA verification.',
        'invalid-input-response' => 'The CAPTCHA verification failed.',
        'bad-request' => 'The request is invalid or malformed.',
        'invalid-or-already-seen-response' => 'The response has already been checked or is invalid.',
        'not-using-dummy-passcode' => 'You used a test secret key in production.',
        'sitekey-secret-mismatch' => 'The site key is not registered with the provided secret key.',
        'unknown' => 'An unknown error occurred during CAPTCHA verification.'
    ];
    
    /**
     * Constructor
     *
     * @param string $siteKey Site key
     * @param string $secretKey Secret key
     * @param array $options Additional options
     */
    public function __construct(string $siteKey, string $secretKey, array $options = [])
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Generate HTML code to include the hCaptcha script
     *
     * @return string HTML code
     */
    public function getScript(): string
    {
        $config = [
            'hl' => $this->options['language'],
        ];

        $html = '<script src="' . self::SCRIPT_URL . '?' . http_build_query($config) . '" async defer></script>';
        
        return $html;
    }
    
    /**
     * Generate HTML code to display the hCaptcha widget
     *
     * @return string HTML code
     */
    public function display(): string
    {
        $config = [
            'sitekey' => $this->siteKey,
            'theme' => $this->options['theme'],
            'size' => $this->options['size'],
        ];

        $params = array_map(function ($key, $value) {
            return 'data-' . $key . '="' . $value . '"';
        }, array_keys($config), array_values($config));

        $html = '<div class="h-captcha" '.implode(" ", $params).'></div>';


        return $html;
    }
    
    /**
     * Verify if the CAPTCHA response is valid
     *
     * @param string $response CAPTCHA response (generally $_POST['h-captcha-response'])
     * @param string|null $remoteIp User IP address (optional)
     * @return bool True if the response is valid, false otherwise
     */
    public function isValid(string $response, ?string $remoteIp = null): bool
    {
        if (empty($response)) {
            $this->error = 'missing-input-response';
            return false;
        }
        
        $data = [
            'secret' => $this->secretKey,
            'response' => $response,
            'sitekey' => $this->siteKey,
        ];
        
        if ($remoteIp !== null) {
            $data['remoteip'] = $remoteIp;
        }
        
        $ch = curl_init(self::VERIFY_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $this->error = 'request-error';
            return false;
        }
        
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error = 'invalid-json';
            return false;
        }
        
        if (isset($responseData['success']) && $responseData['success'] === true) {
            return true;
        }
        
        if (isset($responseData['error-codes']) && is_array($responseData['error-codes'])) {
            $this->error = $responseData['error-codes'][0] ?? 'unknown';
        } else {
            $this->error = 'unknown';
        }
        
        return false;
    }
    
    /**
     * Set the theme of the hCaptcha widget
     *
     * @param string $theme 'light' or 'dark'
     * @return self
     */
    public function setTheme(string $theme): self
    {
        $theme = strtolower($theme);
        if (in_array($theme, ['light', 'dark'], true)) {
            $this->options['theme'] = $theme;
        }
        return $this;
    }
    
    /**
     * Set the size of the hCaptcha widget
     *
     * @param string $size 'normal', 'compact'
     * @return self
     */
    public function setSize(string $size): self
    {
        $size = strtolower($size);
        if (in_array($size, ['normal', 'compact'], true)) {
            $this->options['size'] = $size;
        }
        return $this;
    }
    
    /**
     * Set the language of the hCaptcha widget
     *
     * @param string $language Language code (ex: 'fr', 'en', 'es', etc.)
     * @return self
     */
    public function setLanguage(string $language): self
    {
        $this->options['language'] = $language;
        return $this;
    }
    
    /**
     * Set a custom option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return self
     */
    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }
    
    /**
     * Get the error message of the last operation
     *
     * @return string Error message
     */
    public function getError(): string
    {
        return $this->errorMessages[$this->error] ?? $this->errorMessages['unknown'];
    }
    
    /**
     * Get the error code of the last operation
     *
     * @return string|null Error code or null if no error
     */
    public function getErrorCode(): ?string
    {
        return $this->error;
    }
}
