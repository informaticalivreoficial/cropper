<?php

namespace Renato\Cropper;

use Exception;
use WebPConvert\Convert\Exceptions\ConversionFailedException;

/**
 * Class Cropper
 *
 * @author Renato Montanari <https://informaticalivre.com.br>
 * @author Robson V. Leite <https://github.com/robsonvleite> (original)
 * @package Renato\Cropper
 */
class Cropper
{
    private string $imagePath;
    private string $imageName;
    private string $imageMime;

    private static array $allowedExt = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Cropper constructor.
     *
     * @param string $cachePath  Path to cache folder
     * @param int    $quality    Image quality 1-100 (default 75)
     * @param int    $compressor PNG compressor 1-9 (default 5)
     * @param bool   $webP       Convert output to WebP (default false)
     *
     * @throws Exception
     */
    public function __construct(
        private readonly string $cachePath,  // 👈 PHP 8.x constructor promotion
        private readonly int $quality = 75,
        private readonly int $compressor = 5,
        private readonly bool $webP = false
    ) {
        if (!is_dir($this->cachePath) && !mkdir($this->cachePath, 0755, true)) {
            throw new Exception("Could not create cache folder: {$this->cachePath}");
        }
    }

    /**
     * Make a thumbnail image
     *
     * @param string   $imagePath
     * @param int      $width
     * @param int|null $height
     * @return string|null
     */
    public function make(string $imagePath, int $width, ?int $height = null): ?string
    {
        if (!file_exists($imagePath)) {
            return null;
        }

        $mime = mime_content_type($imagePath);

        if (!in_array($mime, self::$allowedExt, true)) {
            return null;
        }

        $this->imagePath = $imagePath;
        $this->imageMime = $mime;
        $this->imageName = $this->name($imagePath, $width, $height);

        return $this->image($width, $height);
    }

    /**
     * Check cache or generate thumbnail
     */
    private function image(int $width, ?int $height = null): ?string
    {
        $webpPath = "{$this->cachePath}/{$this->imageName}.webp";
        $ext      = pathinfo($this->imagePath, PATHINFO_EXTENSION);
        $extPath  = "{$this->cachePath}/{$this->imageName}.{$ext}";

        if ($this->webP && is_file($webpPath)) {
            return $webpPath;
        }

        if (is_file($extPath)) {
            return $extPath;
        }

        return $this->imageCache($width, $height);
    }

    /**
     * Generate thumbnail name based on filename, size and hash
     */
    protected function name(string $name, ?int $width = null, ?int $height = null): string
    {
        $filename   = pathinfo($name, PATHINFO_FILENAME);
        $filtered   = mb_strtolower($filename);
        $filtered   = mb_convert_encoding(htmlspecialchars($filtered), 'ISO-8859-1', 'UTF-8');

        $formats = mb_convert_encoding(
            'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª',
            'ISO-8859-1',
            'UTF-8'
        );
        $replace  = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr                                 ';

        $slug = trim(strtr($filtered, $formats, $replace));
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/-{2,}/', '-', $slug);

        $hash       = $this->hash($this->imagePath);
        $widthPart  = $width  ? "-{$width}"  : '';
        $heightPart = $height ? "x{$height}" : '';

        return "{$slug}{$widthPart}{$heightPart}-{$hash}";
    }

    /**
     * Generate hash from image filename
     */
    protected function hash(string $path): string
    {
        return hash('crc32', pathinfo($path, PATHINFO_BASENAME));
    }

    /**
     * Flush cache for a specific image or entire cache folder
     *
     * @param string|null $imagePath
     */
    public function flush(?string $imagePath = null): void
    {
        $files = scandir($this->cachePath);

        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            $filePath = "{$this->cachePath}/{$file}";

            if ($imagePath) {
                if (str_contains($file, $this->hash($imagePath))) {
                    $this->imageDestroy($filePath);
                }
            } else {
                $this->imageDestroy($filePath);
            }
        }
    }

    /**
     * Generate and cache the thumbnail
     */
    private function imageCache(int $width, ?int $height = null): ?string
    {
        [$src_w, $src_h] = getimagesize($this->imagePath);

        $height = (int) ($height ?? ($width * $src_h) / $src_w);

        $src_x = 0;
        $src_y = 0;

        $cmp_x = $src_w / $width;
        $cmp_y = $src_h / $height;

        if ($cmp_x > $cmp_y) {
            $src_x = (int) round(($src_w - ($src_w / $cmp_x * $cmp_y)) / 2);
            $src_w = (int) round($src_w / $cmp_x * $cmp_y);
        } elseif ($cmp_y > $cmp_x) {
            $src_y = (int) round(($src_h - ($src_h / $cmp_y * $cmp_x)) / 2);
            $src_h = (int) round($src_h / $cmp_y * $cmp_x);
        }

        return match ($this->imageMime) {
            'image/jpeg' => $this->fromJpg($width, $height, $src_x, $src_y, $src_w, $src_h),
            'image/png'  => $this->fromPng($width, $height, $src_x, $src_y, $src_w, $src_h),
            'image/webp' => $this->fromWebP($width, $height, $src_x, $src_y, $src_w, $src_h),
            default      => null,
        };
    }

    /**
     * Delete a file safely
     */
    private function imageDestroy(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Generate thumbnail from JPEG
     */
    private function fromJpg(int $width, int $height, int $src_x, int $src_y, int $src_w, int $src_h): string
    {
        $thumb  = imagecreatetruecolor($width, $height);
        $source = imagecreatefromjpeg($this->imagePath);

        imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);

        $path = "{$this->cachePath}/{$this->imageName}.jpg";
        imagejpeg($thumb, $path, $this->quality);

        return $this->webP ? $this->toWebP($path) : $path;
    }

    /**
     * Generate thumbnail from PNG
     */
    private function fromPng(int $width, int $height, int $src_x, int $src_y, int $src_w, int $src_h): string
    {
        $thumb  = imagecreatetruecolor($width, $height);
        $source = imagecreatefrompng($this->imagePath);

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);

        $path = "{$this->cachePath}/{$this->imageName}.png";
        imagepng($thumb, $path, $this->compressor);

        return $this->webP ? $this->toWebP($path) : $path;
    }

    /**
     * Generate thumbnail from WebP
     */
    private function fromWebP(int $width, int $height, int $src_x, int $src_y, int $src_w, int $src_h): string
    {
        $thumb  = imagecreatetruecolor($width, $height);
        $source = imagecreatefromwebp($this->imagePath);

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);

        $path = "{$this->cachePath}/{$this->imageName}.webp";
        imagewebp($thumb, $path, $this->quality);

        return $path;
    }

    /**
     * Convert image to WebP using WebPConvert
     *
     * @param string $image        Source image path
     * @param bool   $unlinkImage  Delete source after conversion
     * @return string              Path to WebP or original on failure
     */
    public function toWebP(string $image, bool $unlinkImage = true): string
    {
        $webPPath = pathinfo($image, PATHINFO_DIRNAME)
            . '/'
            . pathinfo($image, PATHINFO_FILENAME)
            . '.webp';

        $mime   = mime_content_type($image);
        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($image),
            'image/png'  => imagecreatefrompng($image),
            'image/webp' => imagecreatefromwebp($image),
            default      => null,
        };

        if (!$source) {
            return $image;
        }

        imagewebp($source, $webPPath, $this->quality);

        if ($unlinkImage && is_file($image)) {
            unlink($image);
        }

        return $webPPath;
    }
}