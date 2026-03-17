<?php 

namespace Renato\Cropper\Tests;

use Renato\Cropper\Cropper;
use PHPUnit\Framework\TestCase;

class CropperTest extends TestCase
{
    private string $cachePath = __DIR__ . '/cache';
    private string $imagesPath = __DIR__ . '/images';
    private Cropper $cropper;

    protected function setUp(): void
    {
        $this->cropper = new Cropper($this->cachePath, 75, 5, true);
    }

    protected function tearDown(): void
    {
        $this->cropper->flush();
    }

    public function testMakeJpgThumb(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.jpg", 200);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakeJpgThumbWithHeight(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.jpg", 400, 400);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakePngThumb(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.png", 200);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakePngThumbWithHeight(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.png", 400, 400);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakeWebPThumb(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.webp", 200);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakeWebPThumbWithHeight(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.webp", 400, 400);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakeWebPThumbLandscape(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.webp", 1200, 628);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testMakeWebPThumbPortrait(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.webp", 200, 600);
        $this->assertFileExists($result);
        $this->assertStringEndsWith('.webp', $result);
    }

    public function testCacheIsReusedOnSecondCall(): void
    {
        $first  = $this->cropper->make("{$this->imagesPath}/image.webp", 200);
        $second = $this->cropper->make("{$this->imagesPath}/image.webp", 200);
        $this->assertEquals($first, $second);
    }

    public function testImageNotFound(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/naoexiste.webp", 200);
        $this->assertNull($result);
    }

    public function testInvalidMimeType(): void
    {
        $result = $this->cropper->make("{$this->imagesPath}/image.gif", 200);
        $this->assertNull($result);
    }

    public function testFlushSpecificImage(): void
    {
        $this->cropper->make("{$this->imagesPath}/image.webp", 200);
        $this->cropper->flush("{$this->imagesPath}/image.webp");
        $files = glob("{$this->cachePath}/*.webp");
        $this->assertEmpty($files);
    }

    public function testFlushAll(): void
    {
        $this->cropper->make("{$this->imagesPath}/image.jpg", 200);
        $this->cropper->make("{$this->imagesPath}/image.png", 200);
        $this->cropper->make("{$this->imagesPath}/image.webp", 200);
        $this->cropper->flush();
        $files = glob("{$this->cachePath}/*.webp");
        $this->assertEmpty($files);
    }
}